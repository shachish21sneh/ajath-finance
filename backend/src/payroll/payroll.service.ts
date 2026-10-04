import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class PayrollService {
  constructor(private prisma: PrismaService) {}

  async getEmployees(companyId: string) {
    return this.prisma.employee.findMany({
      where: { companyId },
      include: {
        department: true,
        designation: true,
        salaryStructure: true,
      },
      orderBy: { firstName: 'asc' },
    });
  }

  async createEmployee(companyId: string, data: any) {
    return this.prisma.$transaction(async (tx) => {
      const emp = await tx.employee.create({
        data: {
          companyId,
          departmentId: data.departmentId,
          designationId: data.designationId,
          employeeCode: data.employeeCode,
          firstName: data.firstName,
          lastName: data.lastName,
          email: data.email,
          phone: data.phone,
          panNumber: data.panNumber,
          bankAccountNo: data.bankAccountNo,
          ifscCode: data.ifscCode,
          dateOfJoining: new Date(data.dateOfJoining || Date.now()),
          monthlySalary: data.monthlySalary || 0,
        },
      });

      if (data.salaryStructure) {
        await tx.salaryStructure.create({
          data: {
            employeeId: emp.id,
            basicSalary: data.salaryStructure.basicSalary,
            hra: data.salaryStructure.hra || 0,
            conveyance: data.salaryStructure.conveyance || 0,
            specialAllowance: data.salaryStructure.specialAllowance || 0,
            pfDeduction: data.salaryStructure.pfDeduction || 0,
            esiDeduction: data.salaryStructure.esiDeduction || 0,
            professionalTax: data.salaryStructure.professionalTax || 0,
          },
        });
      }

      return emp;
    });
  }

  async recordAttendance(data: { employeeId: string; date: string | Date; status: string; hoursWorked?: number }) {
    return this.prisma.attendance.upsert({
      where: {
        employeeId_date: {
          employeeId: data.employeeId,
          date: new Date(data.date),
        },
      },
      update: {
        status: data.status,
        hoursWorked: data.hoursWorked ?? 8.0,
      },
      create: {
        employeeId: data.employeeId,
        date: new Date(data.date),
        status: data.status,
        hoursWorked: data.hoursWorked ?? 8.0,
      },
    });
  }

  // Process Payroll Run with Automatic Double-Entry Accounting Voucher
  async runPayroll(companyId: string, branchId: string, financialYearId: string, month: number, year: number, createdById: string) {
    return this.prisma.$transaction(async (tx) => {
      // Check if already processed
      const existing = await tx.payrollRun.findUnique({
        where: {
          companyId_month_year: { companyId, month, year },
        },
      });
      if (existing) {
        throw new BadRequestException(`Payroll for ${month}/${year} has already been processed.`);
      }

      const employees = await tx.employee.findMany({
        where: { companyId, isActive: true },
        include: { salaryStructure: true },
      });

      if (employees.length === 0) {
        throw new BadRequestException('No active employees found to process payroll.');
      }

      let totalGross = 0;
      let totalDeductions = 0;
      let totalNet = 0;

      const payslipData: any[] = [];

      for (const emp of employees) {
        const struct = emp.salaryStructure || {
          basicSalary: emp.monthlySalary * 0.5,
          hra: emp.monthlySalary * 0.3,
          conveyance: 0,
          specialAllowance: emp.monthlySalary * 0.2,
          pfDeduction: 1800,
          esiDeduction: 0,
          professionalTax: 200,
        };

        const allowances = (struct.conveyance || 0) + (struct.specialAllowance || 0);
        const grossSalary = struct.basicSalary + struct.hra + allowances;
        const deductions = (struct.pfDeduction || 0) + (struct.esiDeduction || 0) + (struct.professionalTax || 0);
        const netSalary = grossSalary - deductions;

        totalGross += grossSalary;
        totalDeductions += deductions;
        totalNet += netSalary;

        payslipData.push({
          employeeId: emp.id,
          basicSalary: struct.basicSalary,
          hra: struct.hra,
          allowances,
          grossSalary,
          pfDeduction: struct.pfDeduction || 0,
          esiDeduction: struct.esiDeduction || 0,
          taxDeductions: struct.professionalTax || 0,
          netSalary,
        });
      }

      // Find Salary Expense & Salary Payable Ledgers
      const salExpLedger = await tx.ledger.findFirst({
        where: { companyId, code: '5001' },
      });
      const salPayLedger = await tx.ledger.findFirst({
        where: { companyId, code: '2201' },
      });

      if (!salExpLedger || !salPayLedger) {
        throw new BadRequestException('Salary Expense or Payable ledger missing in Chart of Accounts.');
      }

      // Post Double-Entry Journal Voucher
      // Debit: Salary Expense (totalGross)
      // Credit: Salary Payable (totalNet)
      // Invariant: Debit == Credit (if no statutory liabilities split, net + deductions balances gross)
      const voucherEntries = [
        { ledgerId: salExpLedger.id, type: 'DEBIT', amount: totalGross, narration: `Staff Salary Provision for ${month}/${year}` },
        { ledgerId: salPayLedger.id, type: 'CREDIT', amount: totalGross, narration: `Salaries Net & Statutory Payable for ${month}/${year}` },
      ];

      const voucher = await tx.voucher.create({
        data: {
          companyId,
          branchId,
          financialYearId,
          voucherNumber: `PAY-${year}-${month.toString().padStart(2, '0')}`,
          voucherType: 'JOURNAL',
          date: new Date(),
          narration: `Monthly Payroll for ${month}/${year} (${employees.length} employees)`,
          totalAmount: totalGross,
          createdById,
          referenceType: 'PAYROLL',
          entries: {
            create: voucherEntries,
          },
        },
      });

      // Update Ledger balances
      await tx.ledger.update({
        where: { id: salExpLedger.id },
        data: { currentBalance: salExpLedger.currentBalance + totalGross },
      });
      await tx.ledger.update({
        where: { id: salPayLedger.id },
        data: { currentBalance: salPayLedger.currentBalance + totalGross },
      });

      // Create Payroll Run Record
      const payrollRun = await tx.payrollRun.create({
        data: {
          companyId,
          month,
          year,
          totalGross,
          totalDeductions,
          totalNet,
          status: 'COMPLETED',
          voucherId: voucher.id,
          payslips: {
            create: payslipData,
          },
        },
        include: {
          payslips: { include: { employee: true } },
        },
      });

      return payrollRun;
    });
  }

  async getPayrollRuns(companyId: string) {
    return this.prisma.payrollRun.findMany({
      where: { companyId },
      include: {
        payslips: { include: { employee: true } },
      },
      orderBy: { createdAt: 'desc' },
    });
  }
}
