<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record a stock movement and refresh the product's live stock balance.
     */
    public function recordStockMovement(
        int $companyId,
        int $productId,
        ?int $warehouseId,
        ?int $voucherId,
        string $movementType,
        float $quantity,
        float $rate,
        string $movementDate,
        ?string $narration = null
    ): StockMovement {
        return DB::transaction(function () use (
            $companyId, $productId, $warehouseId, $voucherId, $movementType, $quantity, $rate, $movementDate, $narration
        ) {
            $movement = StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'voucher_id' => $voucherId,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'rate' => $rate,
                'total_amount' => round($quantity * $rate, 2),
                'movement_date' => $movementDate,
                'narration' => $narration,
            ]);

            $product = Product::find($productId);
            if ($product) {
                $this->recalculateProductStock($product);
            }

            return $movement;
        });
    }

    /**
     * Recalculates product current stock from opening stock and all recorded movements.
     */
    public function recalculateProductStock(Product $product): float
    {
        $opening = (float) $product->opening_stock;

        $inward = (float) StockMovement::where('product_id', $product->id)
            ->whereIn('movement_type', ['inward', 'transfer_in'])
            ->sum('quantity');

        $outward = (float) StockMovement::where('product_id', $product->id)
            ->whereIn('movement_type', ['outward', 'transfer_out'])
            ->sum('quantity');

        $adjustments = (float) StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'adjustment')
            ->sum('quantity');

        $netStock = $opening + $inward - $outward + $adjustments;

        $product->update(['current_stock' => $netStock]);

        return $netStock;
    }

    /**
     * Transfer stock between godowns.
     */
    public function transferStock(
        int $companyId,
        int $productId,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        string $date,
        ?string $narration = null
    ): void {
        DB::transaction(function () use ($companyId, $productId, $fromWarehouseId, $toWarehouseId, $quantity, $date, $narration) {
            $product = Product::findOrFail($productId);
            $rate = (float) $product->purchase_price;

            // Outward from source warehouse
            StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'movement_type' => 'transfer_out',
                'quantity' => $quantity,
                'rate' => $rate,
                'total_amount' => round($quantity * $rate, 2),
                'movement_date' => $date,
                'narration' => $narration ?? 'Inter-godown transfer out',
            ]);

            // Inward to destination warehouse
            StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'movement_type' => 'transfer_in',
                'quantity' => $quantity,
                'rate' => $rate,
                'total_amount' => round($quantity * $rate, 2),
                'movement_date' => $date,
                'narration' => $narration ?? 'Inter-godown transfer in',
            ]);

            $this->recalculateProductStock($product);
        });
    }
}
