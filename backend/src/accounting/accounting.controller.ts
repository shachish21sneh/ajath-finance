import { Controller, Get, Post, Body, Query, Param, UseGuards, Request } from '@nestjs/common';
import { AccountingService, CreateVoucherDto } from './accounting.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('accounting')
export class AccountingController {
  constructor(private accountingService: AccountingService) {}

  @Post('vouchers')
  async createVoucher(@Request() req, @Body() dto: CreateVoucherDto) {
    return this.accountingService.createVoucher({
      ...dto,
      companyId: req.user.companyId,
      branchId: dto.branchId || req.user.branchId,
      createdById: req.user.userId,
    });
  }

  @Get('vouchers')
  async getVouchers(
    @Request() req,
    @Query('voucherType') voucherType?: string,
    @Query('fromDate') fromDate?: string,
    @Query('toDate') toDate?: string,
    @Query('branchId') branchId?: string,
  ) {
    return this.accountingService.getVouchers(req.user.companyId, {
      voucherType,
      fromDate,
      toDate,
      branchId,
    });
  }

  @Get('ledgers')
  async getLedgers(@Request() req) {
    return this.accountingService.getLedgers(req.user.companyId);
  }

  @Get('groups')
  async getAccountGroups(@Request() req) {
    return this.accountingService.getAccountGroups(req.user.companyId);
  }

  @Get('day-book')
  async getDayBook(@Request() req, @Query('date') date?: string) {
    const targetDate = date || new Date().toISOString().split('T')[0];
    return this.accountingService.getDayBook(req.user.companyId, targetDate);
  }

  @Get('trial-balance')
  async getTrialBalance(@Request() req) {
    return this.accountingService.getTrialBalance(req.user.companyId);
  }

  @Get('profit-loss')
  async getProfitAndLoss(@Request() req) {
    return this.accountingService.getProfitAndLoss(req.user.companyId);
  }

  @Get('balance-sheet')
  async getBalanceSheet(@Request() req) {
    return this.accountingService.getBalanceSheet(req.user.companyId);
  }

  @Get('statement/:ledgerId')
  async getLedgerStatement(
    @Param('ledgerId') ledgerId: string,
    @Query('fromDate') fromDate?: string,
    @Query('toDate') toDate?: string,
  ) {
    return this.accountingService.getLedgerStatement(ledgerId, fromDate, toDate);
  }
}
