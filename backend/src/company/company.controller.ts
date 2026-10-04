import { Controller, Get, UseGuards, Request } from '@nestjs/common';
import { CompanyService } from './company.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('company')
export class CompanyController {
  constructor(private companyService: CompanyService) {}

  @Get('profile')
  getCompany(@Request() req) {
    return this.companyService.getCompany(req.user.companyId);
  }

  @Get('branches')
  getBranches(@Request() req) {
    return this.companyService.getBranches(req.user.companyId);
  }

  @Get('financial-years')
  getFinancialYears(@Request() req) {
    return this.companyService.getFinancialYears(req.user.companyId);
  }

  @Get('users')
  getUsers(@Request() req) {
    return this.companyService.getUsers(req.user.companyId);
  }

  @Get('audit-logs')
  getAuditLogs(@Request() req) {
    return this.companyService.getAuditLogs(req.user.companyId);
  }
}
