import { Controller, Get, Post, Body, Query, UseGuards, Request } from '@nestjs/common';
import { BatteryService } from './battery.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('battery')
export class BatteryController {
  constructor(private batteryService: BatteryService) {}

  @Get('models')
  getModels(@Request() req) {
    return this.batteryService.getBatteryModels(req.user.companyId);
  }

  @Get('serials')
  getSerials(@Request() req, @Query('modelId') modelId?: string) {
    return this.batteryService.getBatterySerials(req.user.companyId, modelId);
  }

  @Post('qc-test')
  recordQC(@Body() body: any) {
    return this.batteryService.recordQCTest(body);
  }

  @Post('warranty/register')
  registerWarranty(@Body() body: any) {
    return this.batteryService.registerWarranty(body);
  }

  @Get('claims')
  getClaims(@Request() req) {
    return this.batteryService.getWarrantyClaims(req.user.companyId);
  }

  @Post('claims')
  processClaim(@Body() body: any) {
    return this.batteryService.processWarrantyClaim(body);
  }
}
