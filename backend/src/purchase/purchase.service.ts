import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

export interface CreatePurchaseInvoiceDto {
  companyId: string;
  branchId: string;
  financialYearId: string;
  billNumber: string;
  billDate: string | Date;
  supplierId: string;
  warehouseId: string;
  createdById: string;
  items: {
    productId: string;
    quantity: number;
    unitPrice: number;
    batchNumber?: string;
  }[];
}

@Injectable()
export class PurchaseService {
  constructor(private prisma: PrismaService) {}

  async getSuppliers(companyId: string) {
    return this.prisma.supplier.findMany({
      where: { companyId },
      include: { ledger: true },
      orderBy: { tradeName: 'asc' },
    });
  }

  async createSupplier(companyId: string, data: any) {
    return this.prisma.$transaction(async (tx) => {
      const creditorGroup = await tx.accountGroup.findFirst({
        where: { companyId, code: 'SC' },
      });

      if (!creditorGroup) {
        throw new BadRequestException('Sundry Creditors account group not found');
      }

      const ledger = await tx.ledger.create({
        data: {
          companyId,
          accountGroupId: creditorGroup.id,
          name: data.tradeName,
          code: `SUPP-${Date.now().toString().slice(-4)}`,
          gstin: data.gstin,
          pan: data.pan,
          stateCode: data.stateCode || '09',
          isPartyLedger: true,
          currentBalance: 0,
        },
      });

      return tx.supplier.create({
        data: {
          companyId,
          ledgerId: ledger.id,
          tradeName: data.tradeName,
          legalName: data.legalName || data.tradeName,
          gstin: data.gstin,
          pan: data.pan,
          phone: data.phone,
          email: data.email,
          address: data.address,
          state: data.state || 'Uttar Pradesh',
          stateCode: data.stateCode || '09',
          creditDays: data.creditDays ?? 30,
        },
        include: { ledger: true },
      });
    });
  }

  async createPurchaseInvoice(dto: CreatePurchaseInvoiceDto) {
    if (!dto.items || dto.items.length === 0) {
      throw new BadRequestException('Purchase invoice must contain at least one item.');
    }

    return this.prisma.$transaction(async (tx) => {
      const company = await tx.company.findUnique({ where: { id: dto.companyId } });
      const branch = await tx.branch.findUnique({ where: { id: dto.branchId } });
      const supplier = await tx.supplier.findUnique({
        where: { id: dto.supplierId },
        include: { ledger: true },
      });

      if (!company || !branch || !supplier) {
        throw new NotFoundException('Company, branch, or supplier not found.');
      }

      const isInterState = branch.stateCode !== supplier.stateCode;

      let subTotal = 0;
      let cgstTotal = 0;
      let sgstTotal = 0;
      let igstTotal = 0;

      const processedItems: any[] = [];

      for (const item of dto.items) {
        const product = await tx.product.findUnique({ where: { id: item.productId } });
        if (!product) throw new NotFoundException(`Product ${item.productId} not found.`);

        const taxableAmount = item.quantity * item.unitPrice;
        const gstRate = product.gstRate;

        let taxAmount = 0;
        if (isInterState) {
          taxAmount = (taxableAmount * gstRate) / 100;
          igstTotal += taxAmount;
        } else {
          const halfTax = (taxableAmount * (gstRate / 2)) / 100;
          cgstTotal += halfTax;
          sgstTotal += halfTax;
          taxAmount = halfTax * 2;
        }

        const totalAmount = taxableAmount + taxAmount;
        subTotal += taxableAmount;

        processedItems.push({
          productId: product.id,
          quantity: item.quantity,
          unitPrice: item.unitPrice,
          taxableAmount,
          gstRate,
          taxAmount,
          totalAmount,
        });

        // 1. Stock Inward
        await tx.stockTransaction.create({
          data: {
            companyId: dto.companyId,
            warehouseId: dto.warehouseId,
            productId: product.id,
            transactionType: 'PURCHASE',
            quantity: item.quantity,
            unitCost: item.unitPrice,
            totalCost: taxableAmount,
            referenceType: 'PURCHASE_INVOICE',
            referenceId: dto.billNumber,
            batchNumber: item.batchNumber,
          },
        });

        // Update product stock and last purchase price
        await tx.product.update({
          where: { id: product.id },
          data: {
            currentStock: product.currentStock + item.quantity,
            purchasePrice: item.unitPrice,
          },
        });
      }

      const grandTotal = Math.round(subTotal + cgstTotal + sgstTotal + igstTotal);

      // 2. Post Double-Entry Purchase Voucher
      const purLedger = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '4001' },
      });
      const inputCgst = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '2101' },
      });
      const inputSgst = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '2102' },
      });
      const inputIgst = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '2103' },
      });

      if (!purLedger) throw new BadRequestException('Purchase Ledger not configured');

      const voucherEntries: any[] = [
        { ledgerId: purLedger.id, type: 'DEBIT', amount: subTotal, narration: 'Goods Inward Taxable Value' },
        { ledgerId: supplier.ledgerId, type: 'CREDIT', amount: grandTotal, narration: `Bill from ${supplier.tradeName}` },
      ];

      if (cgstTotal > 0 && inputCgst) {
        voucherEntries.push({ ledgerId: inputCgst.id, type: 'DEBIT', amount: cgstTotal, narration: 'Input CGST' });
      }
      if (sgstTotal > 0 && inputSgst) {
        voucherEntries.push({ ledgerId: inputSgst.id, type: 'DEBIT', amount: sgstTotal, narration: 'Input SGST' });
      }
      if (igstTotal > 0 && inputIgst) {
        voucherEntries.push({ ledgerId: inputIgst.id, type: 'DEBIT', amount: igstTotal, narration: 'Input IGST' });
      }

      const voucher = await tx.voucher.create({
        data: {
          companyId: dto.companyId,
          branchId: dto.branchId,
          financialYearId: dto.financialYearId,
          voucherNumber: `PUR-${dto.billNumber}`,
          voucherType: 'PURCHASE',
          date: new Date(dto.billDate),
          narration: `Purchase Bill ${dto.billNumber} from ${supplier.tradeName}`,
          totalAmount: grandTotal,
          createdById: dto.createdById,
          referenceType: 'PURCHASE_INVOICE',
          referenceId: dto.billNumber,
          entries: {
            create: voucherEntries,
          },
        },
      });

      // Update supplier running balance & payable
      await tx.ledger.update({
        where: { id: supplier.ledgerId },
        data: { currentBalance: supplier.ledger.currentBalance + grandTotal },
      });
      await tx.supplier.update({
        where: { id: supplier.id },
        data: { outstanding: supplier.outstanding + grandTotal },
      });

      // 3. Create Purchase Invoice Record
      return tx.purchaseInvoice.create({
        data: {
          companyId: dto.companyId,
          branchId: dto.branchId,
          billNumber: dto.billNumber,
          billDate: new Date(dto.billDate),
          supplierId: supplier.id,
          voucherId: voucher.id,
          isInterState,
          subTotal,
          cgstTotal,
          sgstTotal,
          igstTotal,
          grandTotal,
          paymentStatus: 'UNPAID',
          items: {
            create: processedItems,
          },
        },
        include: {
          items: { include: { product: true } },
          supplier: true,
        },
      });
    });
  }

  async getInvoices(companyId: string) {
    return this.prisma.purchaseInvoice.findMany({
      where: { companyId },
      include: {
        supplier: true,
        items: { include: { product: true } },
      },
      orderBy: { billDate: 'desc' },
    });
  }
}
