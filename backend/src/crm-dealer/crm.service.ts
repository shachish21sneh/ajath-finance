import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class CrmService {
  constructor(private prisma: PrismaService) {}

  // Leads
  async getLeads(companyId: string) {
    return this.prisma.lead.findMany({
      where: { companyId },
      orderBy: { createdAt: 'desc' },
    });
  }

  async createLead(companyId: string, data: any) {
    return this.prisma.lead.create({
      data: {
        companyId,
        name: data.name,
        companyName: data.companyName,
        phone: data.phone,
        email: data.email,
        category: data.category,
        expectedValue: data.expectedValue ?? 0,
        status: data.status || 'NEW',
        assignedTo: data.assignedTo,
      },
    });
  }

  // Dealers & Commission
  async getDealers(companyId: string) {
    return this.prisma.dealer.findMany({
      where: { companyId },
      orderBy: { businessName: 'asc' },
    });
  }

  async createDealer(companyId: string, data: any) {
    return this.prisma.dealer.create({
      data: {
        companyId,
        dealerCode: data.dealerCode || `DLR-${Date.now().toString().slice(-4)}`,
        businessName: data.businessName,
        ownerName: data.ownerName,
        phone: data.phone,
        email: data.email,
        commissionPercent: data.commissionPercent ?? 5.0,
      },
    });
  }

  // Service Tickets
  async getServiceTickets(companyId: string) {
    return this.prisma.serviceTicket.findMany({
      where: { companyId },
      orderBy: { createdAt: 'desc' },
    });
  }

  async createServiceTicket(companyId: string, data: any) {
    return this.prisma.serviceTicket.create({
      data: {
        companyId,
        ticketNumber: `SRV-${Date.now().toString().slice(-6)}`,
        customerName: data.customerName,
        customerPhone: data.customerPhone,
        productType: data.productType,
        serialNumber: data.serialNumber,
        issueReported: data.issueReported,
        priority: data.priority || 'MEDIUM',
        status: 'OPEN',
      },
    });
  }

  async resolveServiceTicket(id: string, resolutionNotes: string) {
    return this.prisma.serviceTicket.update({
      where: { id },
      data: {
        resolutionNotes,
        status: 'RESOLVED',
        resolvedAt: new Date(),
      },
    });
  }
}
