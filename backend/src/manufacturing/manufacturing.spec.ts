import { Test, TestingModule } from '@nestjs/testing';
import { ManufacturingService } from './manufacturing.service';
import { PrismaService } from '../prisma/prisma.service';

describe('ManufacturingService Production Invariants', () => {
  let service: ManufacturingService;
  let prisma: PrismaService;

  beforeAll(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [ManufacturingService, PrismaService],
    }).compile();

    service = module.get<ManufacturingService>(ManufacturingService);
    prisma = module.get<PrismaService>(PrismaService);
  });

  afterAll(async () => {
    await prisma.$disconnect();
  });

  it('1. Production MUST decrease raw materials and increase finished goods', async () => {
    const bom = await prisma.bOM.findFirst({
      where: { code: 'BOM-LFP48100' },
      include: { product: true, items: { include: { product: true } } },
    });
    expect(bom).toBeDefined();

    const rawItem = bom!.items[0];
    const initialRawStock = rawItem.product.currentStock;
    const initialFgStock = bom!.product.currentStock;

    const qtyToProduce = 2;
    const expectedRawDeduction = rawItem.quantity * qtyToProduce; // 16 * 2 = 32

    const result = await service.runProduction('comp_fuzurra_01', {
      branchId: 'branch_mfg_01',
      bomId: bom!.id,
      warehouseRawId: 'wh_raw_01',
      warehouseFgId: 'wh_fg_01',
      quantity: qtyToProduce,
    });

    expect(result).toBeDefined();
    expect(result.quantityProduced).toBe(qtyToProduce);
    expect(result.serialsGenerated.length).toBe(qtyToProduce);

    // Verify stock changes
    const updatedRaw = await prisma.product.findUnique({ where: { id: rawItem.productId } });
    const updatedFg = await prisma.product.findUnique({ where: { id: bom!.productId } });

    expect(updatedRaw!.currentStock).toBe(initialRawStock - expectedRawDeduction);
    expect(updatedFg!.currentStock).toBe(initialFgStock + qtyToProduce);
  });
});
