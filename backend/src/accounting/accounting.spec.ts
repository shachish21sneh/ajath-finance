import { Test, TestingModule } from '@nestjs/testing';
import { AccountingService } from './accounting.service';
import { PrismaService } from '../prisma/prisma.service';
import { BadRequestException } from '@nestjs/common';

describe('AccountingService Core Invariants', () => {
  let service: AccountingService;
  let prisma: PrismaService;

  beforeAll(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [AccountingService, PrismaService],
    }).compile();

    service = module.get<AccountingService>(AccountingService);
    prisma = module.get<PrismaService>(PrismaService);
  });

  afterAll(async () => {
    await prisma.$disconnect();
  });

  it('1. MUST reject any voucher where Debit != Credit (Double-Entry Invariant)', async () => {
    const unbalancedDto = {
      companyId: 'comp_fuzurra_01',
      branchId: 'branch_ho_01',
      financialYearId: 'fy_2024_25',
      voucherNumber: `TEST-UNBAL-${Date.now()}`,
      voucherType: 'JOURNAL',
      date: new Date(),
      createdById: 'user_admin_01',
      entries: [
        { ledgerId: 'led_cash', type: 'DEBIT' as const, amount: 1000 },
        { ledgerId: 'led_capital', type: 'CREDIT' as const, amount: 900 }, // 1000 != 900
      ],
    };

    await expect(service.createVoucher(unbalancedDto)).rejects.toThrow(BadRequestException);
  });

  it('2. MUST accept and atomically post balanced vouchers where Debit === Credit', async () => {
    const balancedDto = {
      companyId: 'comp_fuzurra_01',
      branchId: 'branch_ho_01',
      financialYearId: 'fy_2024_25',
      voucherNumber: `TEST-BAL-${Date.now()}`,
      voucherType: 'CONTRA',
      date: new Date(),
      narration: 'Cash deposit into bank',
      createdById: 'user_admin_01',
      entries: [
        { ledgerId: 'led_hdfc', type: 'DEBIT' as const, amount: 5000 },
        { ledgerId: 'led_cash', type: 'CREDIT' as const, amount: 5000 },
      ],
    };

    const voucher = await service.createVoucher(balancedDto);
    expect(voucher).toBeDefined();
    expect(voucher.totalAmount).toBe(5000);
    expect(voucher.status).toBe('POSTED');
  });

  it('3. Trial Balance MUST have totalDebit === totalCredit', async () => {
    const tb = await service.getTrialBalance('comp_fuzurra_01');
    expect(tb.isBalanced).toBe(true);
    expect(tb.difference).toBeLessThan(0.01);
  });

  it('4. Balance Sheet MUST balance: Assets === Liabilities + Equity + Net Profit', async () => {
    const bs = await service.getBalanceSheet('comp_fuzurra_01');
    expect(bs.isBalanced).toBe(true);
    expect(bs.difference).toBeLessThan(0.01);
  });
});
