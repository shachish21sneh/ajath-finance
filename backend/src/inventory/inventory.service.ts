import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class InventoryService {
  constructor(private prisma: PrismaService) {}

  // Products
  async getProducts(companyId: string, search?: string, type?: string) {
    const where: any = { companyId };
    if (type) where.productType = type;
    if (search) {
      where.OR = [
        { name: { contains: search } },
        { sku: { contains: search } },
        { hsnSacCode: { contains: search } },
      ];
    }
    return this.prisma.product.findMany({
      where,
      include: {
        productGroup: true,
        batteryModel: true,
      },
      orderBy: { name: 'asc' },
    });
  }

  async getProductById(id: string) {
    const product = await this.prisma.product.findUnique({
      where: { id },
      include: {
        productGroup: true,
        batteryModel: true,
        stockTransactions: {
          take: 20,
          orderBy: { createdAt: 'desc' },
          include: { warehouse: true },
        },
      },
    });
    if (!product) throw new NotFoundException('Product not found');
    return product;
  }

  async createProduct(companyId: string, data: any) {
    return this.prisma.product.create({
      data: {
        companyId,
        productGroupId: data.productGroupId,
        sku: data.sku,
        name: data.name,
        description: data.description,
        unit: data.unit || 'NOS',
        hsnSacCode: data.hsnSacCode,
        gstRate: data.gstRate ?? 18.0,
        purchasePrice: data.purchasePrice ?? 0,
        sellingPrice: data.sellingPrice ?? 0,
        minStockAlert: data.minStockAlert ?? 5,
        isSerialized: data.isSerialized ?? false,
        productType: data.productType ?? 'STANDARD',
      },
      include: { productGroup: true },
    });
  }

  // Warehouses
  async getWarehouses(companyId: string) {
    return this.prisma.warehouse.findMany({
      where: { companyId },
      include: { branch: true },
      orderBy: { name: 'asc' },
    });
  }

  // Serial Numbers
  async getSerialNumbers(companyId: string, productId?: string, status?: string) {
    const where: any = { product: { companyId } };
    if (productId) where.productId = productId;
    if (status) where.status = status;

    return this.prisma.serialNumber.findMany({
      where,
      include: {
        product: true,
        warehouse: true,
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  // Stock Transaction (Stock Adjustment / Transfer)
  async recordStockTransaction(data: {
    companyId: string;
    warehouseId: string;
    productId: string;
    transactionType: string;
    quantity: number;
    unitCost?: number;
    referenceType?: string;
    referenceId?: string;
    batchNumber?: string;
    serialNumber?: string;
  }) {
    return this.prisma.$transaction(async (tx) => {
      const product = await tx.product.findUnique({ where: { id: data.productId } });
      if (!product) throw new NotFoundException('Product not found');

      const unitCost = data.unitCost ?? product.purchasePrice;
      const totalCost = unitCost * Math.abs(data.quantity);

      const txn = await tx.stockTransaction.create({
        data: {
          companyId: data.companyId,
          warehouseId: data.warehouseId,
          productId: data.productId,
          transactionType: data.transactionType,
          quantity: data.quantity,
          unitCost,
          totalCost,
          referenceType: data.referenceType,
          referenceId: data.referenceId,
          batchNumber: data.batchNumber,
          serialNumber: data.serialNumber,
        },
      });

      // Update current stock
      await tx.product.update({
        where: { id: data.productId },
        data: {
          currentStock: product.currentStock + data.quantity,
        },
      });

      // If serialized, update serial status
      if (data.serialNumber) {
        await tx.serialNumber.upsert({
          where: { serialNumber: data.serialNumber },
          update: {
            warehouseId: data.quantity > 0 ? data.warehouseId : null,
            status: data.quantity > 0 ? 'IN_STOCK' : 'DISPATCHED',
          },
          create: {
            productId: data.productId,
            warehouseId: data.warehouseId,
            serialNumber: data.serialNumber,
            batchNumber: data.batchNumber,
            status: data.quantity > 0 ? 'IN_STOCK' : 'DISPATCHED',
          },
        });
      }

      return txn;
    });
  }

  // Stock Summary Report
  async getStockSummary(companyId: string) {
    const products = await this.prisma.product.findMany({
      where: { companyId },
      include: {
        productGroup: true,
      },
      orderBy: { name: 'asc' },
    });

    return products.map((p) => ({
      id: p.id,
      sku: p.sku,
      name: p.name,
      group: p.productGroup.name,
      unit: p.unit,
      currentStock: p.currentStock,
      purchasePrice: p.purchasePrice,
      sellingPrice: p.sellingPrice,
      stockValue: p.currentStock * p.purchasePrice,
      isLowStock: p.currentStock <= p.minStockAlert,
      minStockAlert: p.minStockAlert,
    }));
  }
}
