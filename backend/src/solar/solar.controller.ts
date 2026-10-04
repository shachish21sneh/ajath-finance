import { Controller, Get, Post, Body, Param, Put, UseGuards, Request } from '@nestjs/common';
import { SolarService } from './solar.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('solar')
export class SolarController {
  constructor(private solarService: SolarService) {}

  @Get('projects')
  getProjects(@Request() req) {
    return this.solarService.getProjects(req.user.companyId);
  }

  @Post('projects')
  createProject(@Request() req, @Body() body: any) {
    return this.solarService.createProject(req.user.companyId, body);
  }

  @Post('survey')
  recordSurvey(@Body() body: any) {
    return this.solarService.recordSurvey(body);
  }

  @Put('projects/:id/status')
  updateStatus(@Param('id') id: string, @Body('status') status: string) {
    return this.solarService.updateProjectStatus(id, status);
  }

  @Post('amc')
  createAMC(@Body() body: any) {
    return this.solarService.createAMC(body);
  }
}
