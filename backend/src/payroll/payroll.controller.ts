import { Controller, Get, Post, Body, UseGuards, Request } from '@nestjs/common';
import { PayrollService } from './payroll.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('payroll')
export class PayrollController {
  constructor(private payrollService: PayrollService) {}

  @Get('employees')
  getEmployees(@Request() req) {
    return this.payrollService.getEmployees(req.user.companyId);
  }

  @Post('employees')
  createEmployee(@Request() req, @Body() body: any) {
    return this.payrollService.createEmployee(req.user.companyId, body);
  }

  @Post('attendance')
  recordAttendance(@Body() body: any) {
    return this.payrollService.recordAttendance(body);
  }

  @Post('run')
  runPayroll(@Request() req, @Body() body: { month: number; year: number; branchId?: string; financialYearId: string }) {
    return this.payrollService.runPayroll(
      req.user.companyId,
      body.branchId || req.user.branchId,
      body.financialYearId,
      body.month,
      body.year,
      req.user.userId,
    );
  }

  @Get('runs')
  getPayrollRuns(@Request() req) {
    return this.payrollService.getPayrollRuns(req.user.companyId);
  }
}
