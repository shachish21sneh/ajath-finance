import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class CompanyService {
  constructor(private prisma: PrismaService) {}

  async getCompany(companyId: string) {
    const comp = await this.prisma.company.findUnique({
      where: { id: companyId },
      include: {
        branches: true,
        financialYears: true,
      },
    });
    if (!comp) throw new NotFoundException('Company not found');
    return comp;
  }

  async getBranches(companyId: string) {
    return this.prisma.branch.findMany({
      where: { companyId },
      include: { warehouses: true },
    });
  }

  async getFinancialYears(companyId: string) {
    return this.prisma.financialYear.findMany({
      where: { companyId },
      orderBy: { startDate: 'desc' },
    });
  }

  async getUsers(companyId: string) {
    return this.prisma.user.findMany({
      where: { companyId },
      select: {
        id: true,
        username: true,
        fullName: true,
        email: true,
        phone: true,
        isActive: true,
        role: true,
        branch: true,
      },
    });
  }

  async getAuditLogs(companyId: string) {
    return this.prisma.auditLog.findMany({
      where: { companyId },
      include: {
        user: { select: { fullName: true, username: true } },
      },
      orderBy: { createdAt: 'desc' },
      take: 50,
    });
  }
}
