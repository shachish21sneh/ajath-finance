import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class ReportsService {
  constructor(private prisma: PrismaService) {}

  async getExecutiveDashboard(companyId: string) {
    // 1. Total Invoiced Sales & GST
    const salesAgg = await this.prisma.saleInvoice.aggregate({
      where: { companyId },
      _sum: { grandTotal: true, subTotal: true },
      _count: true,
    });

    // 2. Total Purchases
    const purchaseAgg = await this.prisma.purchaseInvoice.aggregate({
      where: { companyId },
      _sum: { grandTotal: true },
      _count: true,
    });

    // 3. Receivables & Payables
    const customerAgg = await this.prisma.customer.aggregate({
      where: { companyId },
      _sum: { outstanding: true },
    });

    const supplierAgg = await this.prisma.supplier.aggregate({
      where: { companyId },
      _sum: { outstanding: true },
    });

    // 4. Products & Stock Value
    const products = await this.prisma.product.findMany({
      where: { companyId },
    });

    const totalStockValue = products.reduce((sum, p) => sum + p.currentStock * p.purchasePrice, 0);
    const lowStockCount = products.filter((p) => p.currentStock <= p.minStockAlert).length;

    // 5. Active Solar Projects & Battery Warranties
    const activeProjects = await this.prisma.solarProject.count({
      where: { companyId, status: { not: 'COMMISSIONED' } },
    });

    const activeBatteriesInField = await this.prisma.batteryWarranty.count({
      where: { customer: { companyId } },
    });

    const openServiceTickets = await this.prisma.serviceTicket.count({
      where: { companyId, status: { in: ['OPEN', 'IN_PROGRESS'] } },
    });

    // 6. Recent 5 Vouchers
    const recentVouchers = await this.prisma.voucher.findMany({
      where: { companyId },
      take: 5,
      orderBy: { date: 'desc' },
      include: {
        entries: { include: { ledger: true } },
      },
    });

    return {
      revenue: salesAgg._sum.grandTotal || 0,
      salesCount: salesAgg._count,
      purchases: purchaseAgg._sum.grandTotal || 0,
      purchaseCount: purchaseAgg._count,
      receivables: customerAgg._sum.outstanding || 0,
      payables: supplierAgg._sum.outstanding || 0,
      inventoryValue: totalStockValue,
      lowStockAlerts: lowStockCount,
      activeSolarProjects: activeProjects,
      activeBatteriesInField,
      openServiceTickets,
      recentTransactions: recentVouchers,
    };
  }
}
