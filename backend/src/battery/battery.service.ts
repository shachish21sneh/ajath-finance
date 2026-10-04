import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class BatteryService {
  constructor(private prisma: PrismaService) {}

  async getBatteryModels(companyId: string) {
    return this.prisma.batteryModel.findMany({
      where: { companyId },
      include: { product: true },
    });
  }

  async getBatterySerials(companyId: string, modelId?: string) {
    const where: any = { batteryModel: { companyId } };
    if (modelId) where.batteryModelId = modelId;

    return this.prisma.batterySerial.findMany({
      where,
      include: {
        batteryModel: { include: { product: true } },
        tests: true,
        warranty: { include: { customer: true } },
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  // Record QC Test
  async recordQCTest(data: {
    batterySerialId: string;
    openCircuitVoltage: number;
    internalResistance: number;
    actualCapacityAh: number;
    specificGravity?: number;
    qcResult: 'PASS' | 'FAIL';
    inspectorName?: string;
  }) {
    return this.prisma.$transaction(async (tx) => {
      const serial = await tx.batterySerial.findUnique({
        where: { id: data.batterySerialId },
      });
      if (!serial) throw new NotFoundException('Battery serial not found');

      const test = await tx.batteryTest.create({
        data: {
          batterySerialId: data.batterySerialId,
          testDate: new Date(),
          openCircuitVoltage: data.openCircuitVoltage,
          internalResistance: data.internalResistance,
          actualCapacityAh: data.actualCapacityAh,
          specificGravity: data.specificGravity,
          qcResult: data.qcResult,
          inspectorName: data.inspectorName || 'Lead QC Inspector',
        },
      });

      await tx.batterySerial.update({
        where: { id: data.batterySerialId },
        data: {
          qcStatus: data.qcResult === 'PASS' ? 'PASSED' : 'FAILED',
        },
      });

      return test;
    });
  }

  // Register Battery Warranty
  async registerWarranty(data: {
    batterySerialId: string;
    customerId: string;
    invoiceNumber?: string;
    purchaseDate: string | Date;
  }) {
    const serial = await this.prisma.batterySerial.findUnique({
      where: { id: data.batterySerialId },
      include: { batteryModel: true },
    });

    if (!serial) throw new NotFoundException('Battery serial not found');

    const pDate = new Date(data.purchaseDate);
    const endDate = new Date(pDate);
    endDate.setMonth(endDate.getMonth() + serial.batteryModel.warrantyMonths);

    return this.prisma.$transaction(async (tx) => {
      const warranty = await tx.batteryWarranty.create({
        data: {
          batterySerialId: data.batterySerialId,
          customerId: data.customerId,
          invoiceNumber: data.invoiceNumber,
          purchaseDate: pDate,
          warrantyEndDate: endDate,
        },
      });

      await tx.batterySerial.update({
        where: { id: data.batterySerialId },
        data: { currentStatus: 'SOLD' },
      });

      return warranty;
    });
  }

  // File & Process Warranty Claim
  async processWarrantyClaim(data: {
    warrantyId: string;
    claimType: 'REPLACEMENT' | 'PRO_RATA' | 'REPAIR';
    reportedIssue: string;
    testFindings?: string;
    approvalStatus: 'APPROVED' | 'REJECTED';
    replacementSerial?: string;
  }) {
    const claimNumber = `CLM-${Date.now().toString().slice(-6)}`;
    return this.prisma.warrantyClaim.create({
      data: {
        warrantyId: data.warrantyId,
        claimNumber,
        claimType: data.claimType,
        reportedIssue: data.reportedIssue,
        testFindings: data.testFindings,
        approvalStatus: data.approvalStatus,
        replacementSerial: data.replacementSerial,
        resolvedDate: data.approvalStatus === 'APPROVED' ? new Date() : null,
      },
    });
  }

  async getWarrantyClaims(companyId: string) {
    return this.prisma.warrantyClaim.findMany({
      where: {
        warranty: {
          customer: { companyId },
        },
      },
      include: {
        warranty: {
          include: {
            customer: true,
            batterySerial: { include: { batteryModel: { include: { product: true } } } },
          },
        },
      },
      orderBy: { claimDate: 'desc' },
    });
  }
}
