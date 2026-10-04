import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

export interface CreateSaleInvoiceDto {
  companyId: string;
  branchId: string;
  financialYearId: string;
  invoiceNumber: string;
  invoiceDate: string | Date;
  dueDate?: string | Date;
  customerId: string;
  warehouseId: string;
  placeOfSupply: string;
  notes?: string;
  createdById: string;
  items: {
    productId: string;
    quantity: number;
    unitPrice: number;
    discountPercent?: number;
    serialNumbers?: string[];
  }[];
}

@Injectable()
export class SalesService {
  constructor(private prisma: PrismaService) {}

  // Customers
  async getCustomers(companyId: string, search?: string) {
    const where: any = { companyId };
    if (search) {
      where.OR = [
        { tradeName: { contains: search } },
        { gstin: { contains: search } },
        { phone: { contains: search } },
      ];
    }
    return this.prisma.customer.findMany({
      where,
      include: { ledger: true },
      orderBy: { tradeName: 'asc' },
    });
  }

  async createCustomer(companyId: string, data: any) {
    return this.prisma.$transaction(async (tx) => {
      // Create ledger first under Sundry Debtors
      const debtorGroup = await tx.accountGroup.findFirst({
        where: { companyId, code: 'SD' },
      });

      if (!debtorGroup) {
        throw new BadRequestException('Sundry Debtors account group not configured');
      }

      const ledger = await tx.ledger.create({
        data: {
          companyId,
          accountGroupId: debtorGroup.id,
          name: data.tradeName,
          code: `CUST-${Date.now().toString().slice(-4)}`,
          gstin: data.gstin,
          pan: data.pan,
          stateCode: data.stateCode || '07',
          isPartyLedger: true,
          currentBalance: 0,
        },
      });

      return tx.customer.create({
        data: {
          companyId,
          ledgerId: ledger.id,
          tradeName: data.tradeName,
          legalName: data.legalName || data.tradeName,
          gstin: data.gstin,
          pan: data.pan,
          phone: data.phone,
          email: data.email,
          billingAddress: data.billingAddress,
          shippingAddress: data.shippingAddress || data.billingAddress,
          state: data.state,
          stateCode: data.stateCode || '07',
          creditLimit: data.creditLimit ?? 0,
          creditDays: data.creditDays ?? 30,
        },
        include: { ledger: true },
      });
    });
  }

  // Complete Integrated Sales Invoicing with GST, Stock Deduction, and Double-Entry Voucher
  async createSaleInvoice(dto: CreateSaleInvoiceDto) {
    if (!dto.items || dto.items.length === 0) {
      throw new BadRequestException('Invoice must contain at least one line item.');
    }

    return this.prisma.$transaction(async (tx) => {
      const company = await tx.company.findUnique({ where: { id: dto.companyId } });
      const branch = await tx.branch.findUnique({ where: { id: dto.branchId } });
      const customer = await tx.customer.findUnique({
        where: { id: dto.customerId },
        include: { ledger: true },
      });

      if (!company || !branch || !customer) {
        throw new NotFoundException('Company, branch, or customer record not found.');
      }

      // Check Inter-State vs Intra-State: Compare Branch stateCode vs Customer stateCode
      const isInterState = branch.stateCode !== customer.stateCode;

      let subTotal = 0;
      let cgstTotal = 0;
      let sgstTotal = 0;
      let igstTotal = 0;

      const processedItems: any[] = [];

      for (const item of dto.items) {
        const product = await tx.product.findUnique({ where: { id: item.productId } });
        if (!product) throw new NotFoundException(`Product ${item.productId} not found.`);

        // Stock check
        if (product.currentStock < item.quantity) {
          throw new BadRequestException(
            `Insufficient stock for product ${product.name}. Available: ${product.currentStock}, Requested: ${item.quantity}`
          );
        }

        const discPercent = item.discountPercent || 0;
        const lineTotal = item.quantity * item.unitPrice;
        const discountAmount = (lineTotal * discPercent) / 100;
        const taxableAmount = lineTotal - discountAmount;
        const gstRate = product.gstRate;

        let cgstAmount = 0;
        let sgstAmount = 0;
        let igstAmount = 0;

        if (isInterState) {
          igstAmount = (taxableAmount * gstRate) / 100;
          igstTotal += igstAmount;
        } else {
          cgstAmount = (taxableAmount * (gstRate / 2)) / 100;
          sgstAmount = (taxableAmount * (gstRate / 2)) / 100;
          cgstTotal += cgstAmount;
          sgstTotal += sgstAmount;
        }

        const totalAmount = taxableAmount + cgstAmount + sgstAmount + igstAmount;
        subTotal += taxableAmount;

        processedItems.push({
          productId: product.id,
          quantity: item.quantity,
          unitPrice: item.unitPrice,
          discountPercent: discPercent,
          discountAmount,
          taxableAmount,
          gstRate,
          cgstAmount,
          sgstAmount,
          igstAmount,
          totalAmount,
          serialNumbers: item.serialNumbers,
        });

        // 1. Stock Deduction
        await tx.stockTransaction.create({
          data: {
            companyId: dto.companyId,
            warehouseId: dto.warehouseId,
            productId: product.id,
            transactionType: 'SALE',
            quantity: -item.quantity,
            unitCost: product.purchasePrice,
            totalCost: product.purchasePrice * item.quantity,
            referenceType: 'SALE_INVOICE',
            referenceId: dto.invoiceNumber,
          },
        });

        await tx.product.update({
          where: { id: product.id },
          data: { currentStock: product.currentStock - item.quantity },
        });

        // If serialized items dispatched
        if (item.serialNumbers && item.serialNumbers.length > 0) {
          for (const sn of item.serialNumbers) {
            await tx.serialNumber.updateMany({
              where: { serialNumber: sn },
              data: {
                status: 'DISPATCHED',
                warehouseId: null,
                dispatchDate: new Date(dto.invoiceDate),
              },
            });
          }
        }
      }

      const rawGrandTotal = subTotal + cgstTotal + sgstTotal + igstTotal;
      const grandTotal = Math.round(rawGrandTotal);
      const roundOff = grandTotal - rawGrandTotal;

      // 2. Locate Ledgers for Double-Entry Voucher
      const salesLedger = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '3001' },
      });
      const cgstLedger = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '2104' },
      });
      const sgstLedger = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '2105' },
      });
      const igstLedger = await tx.ledger.findFirst({
        where: { companyId: dto.companyId, code: '2106' },
      });

      if (!salesLedger) throw new BadRequestException('Sales Ledger not configured');

      // 3. Post Double-Entry Voucher
      // Debit: Customer (grandTotal)
      // Credit: Sales Account (subTotal)
      // Credit: Taxes (cgst, sgst, igst)
      const voucherEntries: any[] = [
        { ledgerId: customer.ledgerId, type: 'DEBIT', amount: grandTotal, narration: `Sale to ${customer.tradeName}` },
        { ledgerId: salesLedger.id, type: 'CREDIT', amount: subTotal, narration: 'Taxable Goods Revenue' },
      ];

      if (cgstTotal > 0 && cgstLedger) {
        voucherEntries.push({ ledgerId: cgstLedger.id, type: 'CREDIT', amount: cgstTotal, narration: 'Output CGST' });
      }
      if (sgstTotal > 0 && sgstLedger) {
        voucherEntries.push({ ledgerId: sgstLedger.id, type: 'CREDIT', amount: sgstTotal, narration: 'Output SGST' });
      }
      if (igstTotal > 0 && igstLedger) {
        voucherEntries.push({ ledgerId: igstLedger.id, type: 'CREDIT', amount: igstTotal, narration: 'Output IGST' });
      }

      const voucher = await tx.voucher.create({
        data: {
          companyId: dto.companyId,
          branchId: dto.branchId,
          financialYearId: dto.financialYearId,
          voucherNumber: `SL-${dto.invoiceNumber}`,
          voucherType: 'SALES',
          date: new Date(dto.invoiceDate),
          narration: `Tax Invoice ${dto.invoiceNumber} to ${customer.tradeName}`,
          totalAmount: grandTotal,
          createdById: dto.createdById,
          referenceType: 'SALE_INVOICE',
          referenceId: dto.invoiceNumber,
          entries: {
            create: voucherEntries,
          },
        },
      });

      // Update customer ledger running balance & outstanding
      await tx.ledger.update({
        where: { id: customer.ledgerId },
        data: {
          currentBalance: customer.ledger.currentBalance + grandTotal,
        },
      });
      await tx.customer.update({
        where: { id: customer.id },
        data: {
          outstanding: customer.outstanding + grandTotal,
        },
      });

      // 4. Create Sale Invoice
      const invoice = await tx.saleInvoice.create({
        data: {
          companyId: dto.companyId,
          branchId: dto.branchId,
          invoiceNumber: dto.invoiceNumber,
          invoiceDate: new Date(dto.invoiceDate),
          dueDate: dto.dueDate ? new Date(dto.dueDate) : null,
          customerId: customer.id,
          voucherId: voucher.id,
          placeOfSupply: dto.placeOfSupply || customer.state,
          isInterState,
          subTotal,
          cgstTotal,
          sgstTotal,
          igstTotal,
          roundOff,
          grandTotal,
          paymentStatus: 'UNPAID',
          notes: dto.notes,
          items: {
            create: processedItems.map((item) => ({
              productId: item.productId,
              quantity: item.quantity,
              unitPrice: item.unitPrice,
              discountPercent: item.discountPercent,
              discountAmount: item.discountAmount,
              taxableAmount: item.taxableAmount,
              gstRate: item.gstRate,
              cgstAmount: item.cgstAmount,
              sgstAmount: item.sgstAmount,
              igstAmount: item.igstAmount,
              totalAmount: item.totalAmount,
            })),
          },
        },
        include: {
          items: { include: { product: true } },
          customer: true,
        },
      });

      // 5. Generate Native Indian E-Invoice IRN & Signed QR Code Mock
      const irnHash = `IRN-${Date.now()}-${Math.random().toString(36).substring(2, 10).toUpperCase()}`;
      await tx.eInvoice.create({
        data: {
          saleInvoiceId: invoice.id,
          irn: irnHash,
          ackNo: `ACK${Date.now().toString().slice(-8)}`,
          ackDate: new Date(),
          signedQrCode: `https://einvoice.nic.in/verify?irn=${irnHash}&seller=${company.gstin}`,
          status: 'GENERATED',
        },
      });

      // 6. Generate E-Way Bill if grandTotal > 50000
      if (grandTotal >= 50000) {
        await tx.ewayBill.create({
          data: {
            saleInvoiceId: invoice.id,
            ewbNumber: `EWB${Date.now().toString().slice(-12)}`,
            ewbDate: new Date(),
            validUpto: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000), // 3 days validity
            vehicleNumber: 'UP-16-AB-1234',
            status: 'ACTIVE',
          },
        });
      }

      return invoice;
    });
  }

  // List Invoices
  async getInvoices(companyId: string) {
    return this.prisma.saleInvoice.findMany({
      where: { companyId },
      include: {
        customer: true,
        items: { include: { product: true } },
        eInvoice: true,
        ewayBill: true,
      },
      orderBy: { invoiceDate: 'desc' },
    });
  }

  // Fast POS Checkout
  async posCheckout(dto: {
    companyId: string;
    branchId: string;
    financialYearId: string;
    warehouseId: string;
    customerId?: string;
    tenderedAmount: number;
    paymentMode: 'CASH' | 'CARD' | 'UPI';
    items: { productId: string; quantity: number; unitPrice: number }[];
    createdById: string;
  }) {
    // If no customer given, get or create default Cash Walk-in Customer
    let customer = await this.prisma.customer.findFirst({
      where: { companyId: dto.companyId, tradeName: 'Counter Cash Customer' },
    });

    if (!customer) {
      customer = await this.createCustomer(dto.companyId, {
        tradeName: 'Counter Cash Customer',
        gstin: 'URP',
        state: 'Delhi',
        stateCode: '07',
      });
    }

    const invoiceNumber = `POS-${Date.now().toString().slice(-6)}`;
    const invoice = await this.createSaleInvoice({
      companyId: dto.companyId,
      branchId: dto.branchId,
      financialYearId: dto.financialYearId,
      invoiceNumber,
      invoiceDate: new Date(),
      customerId: customer.id,
      warehouseId: dto.warehouseId,
      placeOfSupply: 'Delhi',
      notes: `POS Retail Sale [${dto.paymentMode}]`,
      createdById: dto.createdById,
      items: dto.items,
    });

    // Mark as paid immediately with Receipt voucher
    const cashLedger = await this.prisma.ledger.findFirst({
      where: { companyId: dto.companyId, code: dto.paymentMode === 'CASH' ? '1001' : '1002' },
    });

    if (cashLedger) {
      await this.prisma.$transaction(async (tx) => {
        await tx.voucher.create({
          data: {
            companyId: dto.companyId,
            branchId: dto.branchId,
            financialYearId: dto.financialYearId,
            voucherNumber: `REC-${invoiceNumber}`,
            voucherType: 'RECEIPT',
            date: new Date(),
            narration: `POS Instant Tender against Invoice ${invoiceNumber}`,
            totalAmount: invoice.grandTotal,
            createdById: dto.createdById,
            entries: {
              create: [
                { ledgerId: cashLedger.id, type: 'DEBIT', amount: invoice.grandTotal, narration: 'Payment Received' },
                { ledgerId: customer.ledgerId, type: 'CREDIT', amount: invoice.grandTotal, narration: 'Customer Settlement' },
              ],
            },
          },
        });

        await tx.saleInvoice.update({
          where: { id: invoice.id },
          data: { paidAmount: invoice.grandTotal, paymentStatus: 'PAID' },
        });

        await tx.customer.update({
          where: { id: customer.id },
          data: { outstanding: 0 },
        });
      });
    }

    return {
      ...invoice,
      tenderedAmount: dto.tenderedAmount,
      changeAmount: Math.max(0, dto.tenderedAmount - invoice.grandTotal),
    };
  }
}
