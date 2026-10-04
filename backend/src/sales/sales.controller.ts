import { Controller, Get, Post, Body, Query, UseGuards, Request } from '@nestjs/common';
import { SalesService, CreateSaleInvoiceDto } from './sales.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('sales')
export class SalesController {
  constructor(private salesService: SalesService) {}

  @Get('customers')
  getCustomers(@Request() req, @Query('search') search?: string) {
    return this.salesService.getCustomers(req.user.companyId, search);
  }

  @Post('customers')
  createCustomer(@Request() req, @Body() body: any) {
    return this.salesService.createCustomer(req.user.companyId, body);
  }

  @Get('invoices')
  getInvoices(@Request() req) {
    return this.salesService.getInvoices(req.user.companyId);
  }

  @Post('invoices')
  createInvoice(@Request() req, @Body() dto: CreateSaleInvoiceDto) {
    return this.salesService.createSaleInvoice({
      ...dto,
      companyId: req.user.companyId,
      branchId: dto.branchId || req.user.branchId,
      createdById: req.user.userId,
    });
  }

  @Post('pos/checkout')
  posCheckout(@Request() req, @Body() body: any) {
    return this.salesService.posCheckout({
      ...body,
      companyId: req.user.companyId,
      branchId: body.branchId || req.user.branchId,
      createdById: req.user.userId,
    });
  }
}
