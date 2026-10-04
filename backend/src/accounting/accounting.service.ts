import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

export interface CreateVoucherDto {
  companyId: string;
  branchId: string;
  financialYearId: string;
  voucherNumber: string;
  voucherType: string; // CONTRA, PAYMENT, RECEIPT, JOURNAL, SALES, PURCHASE, CREDIT_NOTE, DEBIT_NOTE
  date: string | Date;
  narration?: string;
  createdById: string;
  entries: {
    ledgerId: string;
    type: 'DEBIT' | 'CREDIT';
    amount: number;
    narration?: string;
  }[];
}

@Injectable()
export class AccountingService {
  constructor(private prisma: PrismaService) {}

  // 1. Double-Entry Voucher Creation with Invariant Validation
  async createVoucher(dto: CreateVoucherDto) {
    if (!dto.entries || dto.entries.length < 2) {
      throw new BadRequestException('A valid voucher must contain at least 2 entries.');
    }

    let totalDebit = 0;
    let totalCredit = 0;

    for (const entry of dto.entries) {
      if (entry.amount <= 0) {
        throw new BadRequestException('Voucher entry amount must be strictly greater than zero.');
      }
      if (entry.type === 'DEBIT') {
        totalDebit += Number(entry.amount);
      } else if (entry.type === 'CREDIT') {
        totalCredit += Number(entry.amount);
      } else {
        throw new BadRequestException(`Invalid entry type: ${entry.type}. Must be DEBIT or CREDIT.`);
      }
    }

    // Invariant check: Debit == Credit
    const diff = Math.abs(totalDebit - totalCredit);
    if (diff > 0.01) {
      throw new BadRequestException(
        `Double-entry invariant violated: Total Debits (₹${totalDebit.toFixed(2)}) must equal Total Credits (₹${totalCredit.toFixed(2)}). Difference: ₹${diff.toFixed(2)}`
      );
    }

    // Execute atomic transaction
    return this.prisma.$transaction(async (tx) => {
      // 1. Create the Voucher
      const voucher = await tx.voucher.create({
        data: {
          companyId: dto.companyId,
          branchId: dto.branchId,
          financialYearId: dto.financialYearId,
          voucherNumber: dto.voucherNumber,
          voucherType: dto.voucherType,
          date: new Date(dto.date),
          narration: dto.narration,
          totalAmount: totalDebit,
          createdById: dto.createdById,
          status: 'POSTED',
          entries: {
            create: dto.entries.map((e) => ({
              ledgerId: e.ledgerId,
              type: e.type,
              amount: e.amount,
              narration: e.narration,
            })),
          },
        },
        include: {
          entries: {
            include: { ledger: true },
          },
        },
      });

      // 2. Update Ledger Running Balances atomically
      for (const entry of dto.entries) {
        const ledger = await tx.ledger.findUnique({
          where: { id: entry.ledgerId },
          include: { accountGroup: true },
        });

        if (!ledger) {
          throw new NotFoundException(`Ledger with id ${entry.ledgerId} not found.`);
        }

        const isAssetOrExpense = ['ASSET', 'EXPENSE'].includes(ledger.accountGroup.nature);
        let balanceDelta = 0;

        if (isAssetOrExpense) {
          balanceDelta = entry.type === 'DEBIT' ? entry.amount : -entry.amount;
        } else {
          balanceDelta = entry.type === 'CREDIT' ? entry.amount : -entry.amount;
        }

        const newBalance = ledger.currentBalance + balanceDelta;

        await tx.ledger.update({
          where: { id: ledger.id },
          data: {
            currentBalance: newBalance,
            balanceType: isAssetOrExpense
              ? newBalance >= 0 ? 'DEBIT' : 'CREDIT'
              : newBalance >= 0 ? 'CREDIT' : 'DEBIT',
          },
        });
      }

      return voucher;
    });
  }

  // 2. List Vouchers with filters
  async getVouchers(companyId: string, filters: { voucherType?: string; fromDate?: string; toDate?: string; branchId?: string }) {
    const where: any = { companyId };
    if (filters.voucherType) where.voucherType = filters.voucherType;
    if (filters.branchId) where.branchId = filters.branchId;
    if (filters.fromDate || filters.toDate) {
      where.date = {};
      if (filters.fromDate) where.date.gte = new Date(filters.fromDate);
      if (filters.toDate) where.date.lte = new Date(filters.toDate);
    }

    return this.prisma.voucher.findMany({
      where,
      orderBy: { date: 'desc' },
      include: {
        branch: true,
        createdBy: { select: { fullName: true, username: true } },
        entries: {
          include: { ledger: true },
        },
      },
    });
  }

  // 3. Day Book Report
  async getDayBook(companyId: string, date: string) {
    const targetDate = new Date(date);
    const startOfDay = new Date(targetDate.setHours(0, 0, 0, 0));
    const endOfDay = new Date(targetDate.setHours(23, 59, 59, 999));

    return this.prisma.voucher.findMany({
      where: {
        companyId,
        date: {
          gte: startOfDay,
          lte: endOfDay,
        },
      },
      include: {
        branch: true,
        entries: {
          include: { ledger: true },
        },
      },
      orderBy: { createdAt: 'asc' },
    });
  }

  // 4. Trial Balance (Group-wise and Ledger-wise Parity)
  async getTrialBalance(companyId: string) {
    const groups = await this.prisma.accountGroup.findMany({
      where: { companyId },
      include: {
        ledgers: true,
      },
    });

    let totalDebit = 0;
    let totalCredit = 0;

    const reportGroups = groups.map((grp) => {
      let grpDebit = 0;
      let grpCredit = 0;

      const ledgers = grp.ledgers.map((l) => {
        let debit = 0;
        let credit = 0;
        const bal = Math.abs(l.currentBalance);

        if (['ASSET', 'EXPENSE'].includes(grp.nature)) {
          if (l.currentBalance >= 0) debit = bal;
          else credit = bal;
        } else {
          if (l.currentBalance >= 0) credit = bal;
          else debit = bal;
        }

        grpDebit += debit;
        grpCredit += credit;

        return {
          id: l.id,
          name: l.name,
          code: l.code,
          debit,
          credit,
        };
      });

      totalDebit += grpDebit;
      totalCredit += grpCredit;

      return {
        id: grp.id,
        name: grp.name,
        code: grp.code,
        nature: grp.nature,
        totalDebit: grpDebit,
        totalCredit: grpCredit,
        ledgers,
      };
    });

    return {
      companyId,
      totalDebit,
      totalCredit,
      isBalanced: Math.abs(totalDebit - totalCredit) < 0.01,
      difference: Math.abs(totalDebit - totalCredit),
      groups: reportGroups,
    };
  }

  // 5. Profit & Loss Statement
  async getProfitAndLoss(companyId: string) {
    const incomeGroups = await this.prisma.accountGroup.findMany({
      where: { companyId, nature: 'INCOME' },
      include: { ledgers: true },
    });

    const expenseGroups = await this.prisma.accountGroup.findMany({
      where: { companyId, nature: 'EXPENSE' },
      include: { ledgers: true },
    });

    let totalIncome = 0;
    const incomes = incomeGroups.map((g) => {
      const total = g.ledgers.reduce((sum, l) => sum + Math.abs(l.currentBalance), 0);
      totalIncome += total;
      return { groupName: g.name, total, ledgers: g.ledgers };
    });

    let totalExpense = 0;
    const expenses = expenseGroups.map((g) => {
      const total = g.ledgers.reduce((sum, l) => sum + Math.abs(l.currentBalance), 0);
      totalExpense += total;
      return { groupName: g.name, total, ledgers: g.ledgers };
    });

    const netProfit = totalIncome - totalExpense;

    return {
      totalIncome,
      totalExpense,
      netProfit,
      isProfitable: netProfit >= 0,
      incomes,
      expenses,
    };
  }

  // 6. Balance Sheet
  async getBalanceSheet(companyId: string) {
    const pl = await this.getProfitAndLoss(companyId);

    const assetGroups = await this.prisma.accountGroup.findMany({
      where: { companyId, nature: 'ASSET' },
      include: { ledgers: true },
    });

    const liabilityGroups = await this.prisma.accountGroup.findMany({
      where: { companyId, nature: 'LIABILITY' },
      include: { ledgers: true },
    });

    const equityGroups = await this.prisma.accountGroup.findMany({
      where: { companyId, nature: 'EQUITY' },
      include: { ledgers: true },
    });

    let totalAssets = 0;
    const assets = assetGroups.map((g) => {
      const total = g.ledgers.reduce((sum, l) => sum + l.currentBalance, 0);
      totalAssets += total;
      return { groupName: g.name, total, ledgers: g.ledgers };
    });

    let totalLiabilities = 0;
    const liabilities = liabilityGroups.map((g) => {
      const total = g.ledgers.reduce((sum, l) => sum + l.currentBalance, 0);
      totalLiabilities += total;
      return { groupName: g.name, total, ledgers: g.ledgers };
    });

    let totalEquity = 0;
    const equity = equityGroups.map((g) => {
      const total = g.ledgers.reduce((sum, l) => sum + l.currentBalance, 0);
      totalEquity += total;
      return { groupName: g.name, total, ledgers: g.ledgers };
    });

    // Net Profit added to Equity
    const totalLiabilitiesAndEquity = totalLiabilities + totalEquity + pl.netProfit;

    return {
      totalAssets,
      totalLiabilities,
      totalEquity,
      netProfit: pl.netProfit,
      totalLiabilitiesAndEquity,
      isBalanced: Math.abs(totalAssets - totalLiabilitiesAndEquity) < 0.01,
      difference: Math.abs(totalAssets - totalLiabilitiesAndEquity),
      assets,
      liabilities,
      equity,
    };
  }

  // 7. Ledger Statement
  async getLedgerStatement(ledgerId: string, fromDate?: string, toDate?: string) {
    const ledger = await this.prisma.ledger.findUnique({
      where: { id: ledgerId },
      include: { accountGroup: true },
    });

    if (!ledger) throw new NotFoundException('Ledger not found');

    const where: any = { ledgerId };
    if (fromDate || toDate) {
      where.voucher = { date: {} };
      if (fromDate) where.voucher.date.gte = new Date(fromDate);
      if (toDate) where.voucher.date.lte = new Date(toDate);
    }

    const entries = await this.prisma.voucherEntry.findMany({
      where,
      include: {
        voucher: {
          include: { branch: true },
        },
      },
      orderBy: { voucher: { date: 'asc' } },
    });

    let runningBalance = ledger.openingBalance;
    const isAssetOrExpense = ['ASSET', 'EXPENSE'].includes(ledger.accountGroup.nature);

    const statementEntries = entries.map((e) => {
      const debit = e.type === 'DEBIT' ? e.amount : 0;
      const credit = e.type === 'CREDIT' ? e.amount : 0;

      if (isAssetOrExpense) {
        runningBalance += debit - credit;
      } else {
        runningBalance += credit - debit;
      }

      return {
        id: e.id,
        voucherNumber: e.voucher.voucherNumber,
        voucherType: e.voucher.voucherType,
        date: e.voucher.date,
        narration: e.narration || e.voucher.narration,
        debit,
        credit,
        runningBalance,
      };
    });

    return {
      ledger: {
        id: ledger.id,
        name: ledger.name,
        code: ledger.code,
        group: ledger.accountGroup.name,
        nature: ledger.accountGroup.nature,
        openingBalance: ledger.openingBalance,
        openingType: ledger.openingType,
        currentBalance: ledger.currentBalance,
      },
      entries: statementEntries,
    };
  }

  // 8. Master Account Groups and Ledgers
  async getLedgers(companyId: string) {
    return this.prisma.ledger.findMany({
      where: { companyId },
      include: { accountGroup: true },
      orderBy: { name: 'asc' },
    });
  }

  async getAccountGroups(companyId: string) {
    return this.prisma.accountGroup.findMany({
      where: { companyId },
      include: { ledgers: true },
      orderBy: { sequence: 'asc' },
    });
  }
}
