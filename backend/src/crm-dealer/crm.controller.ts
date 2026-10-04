import { Controller, Get, Post, Body, Param, Put, UseGuards, Request } from '@nestjs/common';
import { CrmService } from './crm.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('crm')
export class CrmController {
  constructor(private crmService: CrmService) {}

  @Get('leads')
  getLeads(@Request() req) {
    return this.crmService.getLeads(req.user.companyId);
  }

  @Post('leads')
  createLead(@Request() req, @Body() body: any) {
    return this.crmService.createLead(req.user.companyId, body);
  }

  @Get('dealers')
  getDealers(@Request() req) {
    return this.crmService.getDealers(req.user.companyId);
  }

  @Post('dealers')
  createDealer(@Request() req, @Body() body: any) {
    return this.crmService.createDealer(req.user.companyId, body);
  }

  @Get('tickets')
  getTickets(@Request() req) {
    return this.crmService.getServiceTickets(req.user.companyId);
  }

  @Post('tickets')
  createTicket(@Request() req, @Body() body: any) {
    return this.crmService.createServiceTicket(req.user.companyId, body);
  }

  @Put('tickets/:id/resolve')
  resolveTicket(@Param('id') id: string, @Body('resolutionNotes') notes: string) {
    return this.crmService.resolveServiceTicket(id, notes);
  }
}
