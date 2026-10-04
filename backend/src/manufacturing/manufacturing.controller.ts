import { Controller, Get, Post, Body, UseGuards, Request } from '@nestjs/common';
import { ManufacturingService } from './manufacturing.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('manufacturing')
export class ManufacturingController {
  constructor(private manufacturingService: ManufacturingService) {}

  @Get('boms')
  getBOMs(@Request() req) {
    return this.manufacturingService.getBOMs(req.user.companyId);
  }

  @Post('boms')
  createBOM(@Request() req, @Body() body: any) {
    return this.manufacturingService.createBOM(req.user.companyId, body);
  }

  @Get('orders')
  getOrders(@Request() req) {
    return this.manufacturingService.getProductionOrders(req.user.companyId);
  }

  @Post('run-production')
  runProduction(@Request() req, @Body() body: any) {
    return this.manufacturingService.runProduction(req.user.companyId, {
      ...body,
      branchId: body.branchId || req.user.branchId,
    });
  }
}
