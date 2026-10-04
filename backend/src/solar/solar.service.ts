import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class SolarService {
  constructor(private prisma: PrismaService) {}

  async getProjects(companyId: string) {
    return this.prisma.solarProject.findMany({
      where: { companyId },
      include: {
        customer: true,
        surveys: true,
        amcContracts: true,
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  async createProject(companyId: string, data: {
    customerId: string;
    projectCode: string;
    projectType: string;
    capacityKw: number;
    inverterCapacityKw?: number;
    totalContractValue: number;
  }) {
    return this.prisma.solarProject.create({
      data: {
        companyId,
        customerId: data.customerId,
        projectCode: data.projectCode,
        projectType: data.projectType,
        capacityKw: data.capacityKw,
        inverterCapacityKw: data.inverterCapacityKw,
        totalContractValue: data.totalContractValue,
        status: 'LEAD',
      },
      include: { customer: true },
    });
  }

  async recordSurvey(data: {
    solarProjectId: string;
    surveyDate?: string | Date;
    surveyorName: string;
    roofType: string;
    roofAreaSqFt: number;
    shadowFreeSqFt: number;
    sanctionedLoadKw: number;
    proposedCapacityKw: number;
    notes?: string;
  }) {
    return this.prisma.$transaction(async (tx) => {
      const survey = await tx.solarSurvey.create({
        data: {
          solarProjectId: data.solarProjectId,
          surveyDate: data.surveyDate ? new Date(data.surveyDate) : new Date(),
          surveyorName: data.surveyorName,
          roofType: data.roofType,
          roofAreaSqFt: data.roofAreaSqFt,
          shadowFreeSqFt: data.shadowFreeSqFt,
          sanctionedLoadKw: data.sanctionedLoadKw,
          proposedCapacityKw: data.proposedCapacityKw,
          notes: data.notes,
        },
      });

      await tx.solarProject.update({
        where: { id: data.solarProjectId },
        data: { status: 'SURVEY_DONE' },
      });

      return survey;
    });
  }

  async updateProjectStatus(projectId: string, status: string) {
    return this.prisma.solarProject.update({
      where: { id: projectId },
      data: { status },
    });
  }

  async createAMC(data: {
    solarProjectId: string;
    contractNumber: string;
    startDate: string | Date;
    endDate: string | Date;
    annualVisits?: number;
    annualAmount: number;
  }) {
    return this.prisma.solarAMC.create({
      data: {
        solarProjectId: data.solarProjectId,
        contractNumber: data.contractNumber,
        startDate: new Date(data.startDate),
        endDate: new Date(data.endDate),
        annualVisits: data.annualVisits || 4,
        annualAmount: data.annualAmount,
        status: 'ACTIVE',
      },
    });
  }
}
