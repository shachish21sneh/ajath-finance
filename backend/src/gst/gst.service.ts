import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class GstService {
  constructor(private prisma: PrismaService) {}

  // HSN/SAC Summary
  async getHsnSummary(companyId: string, fromDate?: string, toDate?: string) {
    const where: any = { saleInvoice: { companyId } };
    if (fromDate || toDate) {
      where.saleInvoice.invoiceDate = {};
      if (fromDate) where.saleInvoice.invoiceDate.gte = new Date(fromDate);
      if (toDate) where.saleInvoice.invoiceDate.lte = new Date(toDate);
    }

    const items = await this.prisma.saleItem.findMany({
      where,
      include: {
        product: true,
        saleInvoice: true,
      },
    });

    const hsnMap: Record<string, any> = {};

    for (const item of items) {
      const hsn = item.product.hsnSacCode || 'GENERAL';
      if (!hsnMap[hsn]) {
        hsnMap[hsn] = {
          hsnCode: hsn,
          description: item.product.name,
          unit: item.product.unit,
          totalQuantity: 0,
          totalTaxableValue: 0,
          totalCgst: 0,
          totalSgst: 0,
          totalIgst: 0,
          totalAmount: 0,
        };
      }

      hsnMap[hsn].totalQuantity += item.quantity;
      hsnMap[hsn].totalTaxableValue += item.taxableAmount;
      hsnMap[hsn].totalCgst += item.cgstAmount;
      hsnMap[hsn].totalSgst += item.sgstAmount;
      hsnMap[hsn].totalIgst += item.igstAmount;
      hsnMap[hsn].totalAmount += item.totalAmount;
    }

    return Object.values(hsnMap);
  }

  // GSTR-1 Outward Summary
  async getGstr1Summary(companyId: string, fromDate?: string, toDate?: string) {
    const where: any = { companyId };
    if (fromDate || toDate) {
      where.invoiceDate = {};
      if (fromDate) where.invoiceDate.gte = new Date(fromDate);
      if (toDate) where.invoiceDate.lte = new Date(toDate);
    }

    const invoices = await this.prisma.saleInvoice.findMany({
      where,
      include: {
        customer: true,
        items: { include: { product: true } },
      },
    });

    const b2bInvoices: any[] = [];
    const b2cInvoices: any[] = [];

    let totalTaxable = 0;
    let totalCgst = 0;
    let totalSgst = 0;
    let totalIgst = 0;
    let totalInvoiceValue = 0;

    for (const inv of invoices) {
      totalTaxable += inv.subTotal;
      totalCgst += inv.cgstTotal;
      totalSgst += inv.sgstTotal;
      totalIgst += inv.igstTotal;
      totalInvoiceValue += inv.grandTotal;

      if (inv.customer.gstin && inv.customer.gstin !== 'URP') {
        b2bInvoices.push(inv);
      } else {
        b2cInvoices.push(inv);
      }
    }

    return {
      period: { fromDate, toDate },
      totals: {
        totalInvoices: invoices.length,
        totalTaxable,
        totalCgst,
        totalSgst,
        totalIgst,
        totalTax: totalCgst + totalSgst + totalIgst,
        totalInvoiceValue,
      },
      b2bCount: b2bInvoices.length,
      b2cCount: b2cInvoices.length,
      b2bInvoices,
    };
  }

  // GSTR-3B Computation
  async getGstr3b(companyId: string) {
    // 3.1 Outward Taxable Supplies
    const outward = await this.prisma.saleInvoice.aggregate({
      where: { companyId },
      _sum: {
        subTotal: true,
        cgstTotal: true,
        sgstTotal: true,
        igstTotal: true,
      },
    });

    // 4. Eligible ITC from Purchase Invoices
    const inward = await this.prisma.purchaseInvoice.aggregate({
      where: { companyId },
      _sum: {
        subTotal: true,
        cgstTotal: true,
        sgstTotal: true,
        igstTotal: true,
      },
    });

    const outwardTaxable = outward._sum.subTotal || 0;
    const outwardCgst = outward._sum.cgstTotal || 0;
    const outwardSgst = outward._sum.sgstTotal || 0;
    const outwardIgst = outward._sum.igstTotal || 0;

    const itcTaxable = inward._sum.subTotal || 0;
    const itcCgst = inward._sum.cgstTotal || 0;
    const itcSgst = inward._sum.sgstTotal || 0;
    const itcIgst = inward._sum.igstTotal || 0;

    return {
      outwardSupplies: {
        taxableValue: outwardTaxable,
        cgst: outwardCgst,
        sgst: outwardSgst,
        igst: outwardIgst,
        totalTax: outwardCgst + outwardSgst + outwardIgst,
      },
      eligibleItc: {
        taxableValue: itcTaxable,
        cgst: itcCgst,
        sgst: itcSgst,
        igst: itcIgst,
        totalItc: itcCgst + itcSgst + itcIgst,
      },
      netTaxPayable: {
        cgst: Math.max(0, outwardCgst - itcCgst),
        sgst: Math.max(0, outwardSgst - itcSgst),
        igst: Math.max(0, outwardIgst - itcIgst),
      },
    };
  }
}
