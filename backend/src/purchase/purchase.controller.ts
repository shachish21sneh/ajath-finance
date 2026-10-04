import { Controller, Get, Post, Body, UseGuards, Request } from '@nestjs/common';
import { PurchaseService, CreatePurchaseInvoiceDto } from './purchase.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('purchase')
export class PurchaseController {
  constructor(private purchaseService: PurchaseService) {}

  @Get('suppliers')
  getSuppliers(@Request() req) {
    return this.purchaseService.getSuppliers(req.user.companyId);
  }

  @Post('suppliers')
  createSupplier(@Request() req, @Body() body: any) {
    return this.purchaseService.createSupplier(req.user.companyId, body);
  }

  @Get('invoices')
  getInvoices(@Request() req) {
    return this.purchaseService.getInvoices(req.user.companyId);
  }

  @Post('invoices')
  createInvoice(@Request() req, @Body() dto: CreatePurchaseInvoiceDto) {
    return this.purchaseService.createPurchaseInvoice({
      ...dto,
      companyId: req.user.companyId,
      branchId: dto.branchId || req.user.branchId,
      createdById: req.user.userId,
    });
  }
}
