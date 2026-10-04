import { Controller, Get, Query, UseGuards, Request } from '@nestjs/common';
import { GstService } from './gst.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('gst')
export class GstController {
  constructor(private gstService: GstService) {}

  @Get('hsn-summary')
  getHsnSummary(@Request() req, @Query('fromDate') fromDate?: string, @Query('toDate') toDate?: string) {
    return this.gstService.getHsnSummary(req.user.companyId, fromDate, toDate);
  }

  @Get('gstr-1')
  getGstr1(@Request() req, @Query('fromDate') fromDate?: string, @Query('toDate') toDate?: string) {
    return this.gstService.getGstr1Summary(req.user.companyId, fromDate, toDate);
  }

  @Get('gstr-3b')
  getGstr3b(@Request() req) {
    return this.gstService.getGstr3b(req.user.companyId);
  }
}
