import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class ManufacturingService {
  constructor(private prisma: PrismaService) {}

  async getBOMs(companyId: string) {
    return this.prisma.bOM.findMany({
      where: { companyId },
      include: {
        product: true,
        items: { include: { product: true } },
      },
    });
  }

  async createBOM(companyId: string, data: {
    productId: string;
    code: string;
    name: string;
    outputQuantity?: number;
    items: { productId: string; quantity: number; scrapAllowance?: number }[];
  }) {
    return this.prisma.bOM.create({
      data: {
        companyId,
        productId: data.productId,
        code: data.code,
        name: data.name,
        outputQuantity: data.outputQuantity || 1.0,
        items: {
          create: data.items.map((i) => ({
            productId: i.productId,
            quantity: i.quantity,
            scrapAllowance: i.scrapAllowance || 0.0,
          })),
        },
      },
      include: {
        product: true,
        items: { include: { product: true } },
      },
    });
  }

  async getProductionOrders(companyId: string) {
    return this.prisma.productionOrder.findMany({
      where: { companyId },
      include: {
        branch: true,
        bom: {
          include: {
            product: true,
            items: { include: { product: true } },
          },
        },
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  // Run Real Production: Raw Material Decreases & Finished Goods Increase
  async runProduction(companyId: string, data: {
    branchId: string;
    bomId: string;
    warehouseRawId: string;
    warehouseFgId: string;
    quantity: number;
    batchNumber?: string;
  }) {
    if (data.quantity <= 0) {
      throw new BadRequestException('Production quantity must be positive.');
    }

    return this.prisma.$transaction(async (tx) => {
      const bom = await tx.bOM.findUnique({
        where: { id: data.bomId },
        include: {
          product: { include: { batteryModel: true } },
          items: { include: { product: true } },
        },
      });

      if (!bom) throw new NotFoundException('BOM not found');

      const batchNum = data.batchNumber || `BATCH-${Date.now().toString().slice(-6)}`;

      // 1. Check & Deduct Raw Materials
      for (const item of bom.items) {
        const requiredQty = item.quantity * data.quantity;
        if (item.product.currentStock < requiredQty) {
          throw new BadRequestException(
            `Insufficient raw material: ${item.product.name}. Required: ${requiredQty}, Available: ${item.product.currentStock}`
          );
        }

        // Stock transaction Outward
        await tx.stockTransaction.create({
          data: {
            companyId,
            warehouseId: data.warehouseRawId,
            productId: item.productId,
            transactionType: 'PRODUCTION_OUT',
            quantity: -requiredQty,
            unitCost: item.product.purchasePrice,
            totalCost: item.product.purchasePrice * requiredQty,
            referenceType: 'PRODUCTION_RUN',
            batchNumber: batchNum,
          },
        });

        await tx.product.update({
          where: { id: item.productId },
          data: { currentStock: item.product.currentStock - requiredQty },
        });
      }

      // 2. Add Finished Goods to Warehouse
      const fgCost = bom.items.reduce(
        (sum, item) => sum + item.quantity * item.product.purchasePrice,
        0
      );

      await tx.stockTransaction.create({
        data: {
          companyId,
          warehouseId: data.warehouseFgId,
          productId: bom.productId,
          transactionType: 'PRODUCTION_IN',
          quantity: data.quantity,
          unitCost: fgCost,
          totalCost: fgCost * data.quantity,
          referenceType: 'PRODUCTION_RUN',
          batchNumber: batchNum,
        },
      });

      await tx.product.update({
        where: { id: bom.productId },
        data: { currentStock: bom.product.currentStock + data.quantity },
      });

      // 3. Create Production Order Record
      const order = await tx.productionOrder.create({
        data: {
          companyId,
          branchId: data.branchId,
          bomId: data.bomId,
          orderNumber: `PRD-${Date.now().toString().slice(-6)}`,
          plannedQuantity: data.quantity,
          producedQuantity: data.quantity,
          status: 'COMPLETED',
          startDate: new Date(),
          completionDate: new Date(),
        },
      });

      // 4. If product is a serialized battery, generate BatterySerial numbers for QC
      const generatedSerials: string[] = [];
      if (bom.product.batteryModel) {
        for (let i = 1; i <= data.quantity; i++) {
          const serialNo = `FUZ-${bom.product.batteryModel.batteryType.substring(0, 3)}-${Date.now().toString().slice(-5)}${i.toString().padStart(2, '0')}`;
          
          await tx.batterySerial.create({
            data: {
              batteryModelId: bom.product.batteryModel.id,
              serialNumber: serialNo,
              cellBatchNumber: batchNum,
              qcStatus: 'PENDING',
              currentStatus: 'IN_FACTORY',
            },
          });

          await tx.serialNumber.create({
            data: {
              productId: bom.productId,
              warehouseId: data.warehouseFgId,
              serialNumber: serialNo,
              batchNumber: batchNum,
              status: 'IN_STOCK',
              manufacturingDate: new Date(),
            },
          });

          generatedSerials.push(serialNo);
        }
      }

      return {
        productionOrder: order,
        batchNumber: batchNum,
        quantityProduced: data.quantity,
        finishedGood: bom.product.name,
        totalRawCost: fgCost * data.quantity,
        serialsGenerated: generatedSerials,
      };
    });
  }
}
