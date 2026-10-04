import { Controller, Get, Post, Body, Query, Param, UseGuards, Request } from '@nestjs/common';
import { InventoryService } from './inventory.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('inventory')
export class InventoryController {
  constructor(private inventoryService: InventoryService) {}

  @Get('products')
  getProducts(@Request() req, @Query('search') search?: string, @Query('type') type?: string) {
    return this.inventoryService.getProducts(req.user.companyId, search, type);
  }

  @Get('products/:id')
  getProductById(@Param('id') id: string) {
    return this.inventoryService.getProductById(id);
  }

  @Post('products')
  createProduct(@Request() req, @Body() body: any) {
    return this.inventoryService.createProduct(req.user.companyId, body);
  }

  @Get('warehouses')
  getWarehouses(@Request() req) {
    return this.inventoryService.getWarehouses(req.user.companyId);
  }

  @Get('serials')
  getSerialNumbers(@Request() req, @Query('productId') productId?: string, @Query('status') status?: string) {
    return this.inventoryService.getSerialNumbers(req.user.companyId, productId, status);
  }

  @Post('stock-adjust')
  adjustStock(@Request() req, @Body() body: any) {
    return this.inventoryService.recordStockTransaction({
      ...body,
      companyId: req.user.companyId,
    });
  }

  @Get('stock-summary')
  getStockSummary(@Request() req) {
    return this.inventoryService.getStockSummary(req.user.companyId);
  }
}
