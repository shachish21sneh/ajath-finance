import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { PrismaModule } from './prisma/prisma.module';
import { AuthModule } from './auth/auth.module';
import { CompanyModule } from './company/company.module';
import { AccountingModule } from './accounting/accounting.module';
import { InventoryModule } from './inventory/inventory.module';
import { SalesModule } from './sales/sales.module';
import { PurchaseModule } from './purchase/purchase.module';
import { ManufacturingModule } from './manufacturing/manufacturing.module';
import { BatteryModule } from './battery/battery.module';
import { SolarModule } from './solar/solar.module';
import { PayrollModule } from './payroll/payroll.module';
import { CrmModule } from './crm-dealer/crm.module';
import { GstModule } from './gst/gst.module';
import { ReportsModule } from './reports/reports.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    PrismaModule,
    AuthModule,
    CompanyModule,
    AccountingModule,
    InventoryModule,
    SalesModule,
    PurchaseModule,
    ManufacturingModule,
    BatteryModule,
    SolarModule,
    PayrollModule,
    CrmModule,
    GstModule,
    ReportsModule,
  ],
})
export class AppModule {}
