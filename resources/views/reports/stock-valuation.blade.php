@extends('layouts.app')

@section('title', 'Stock Valuation & Inventory Ledger')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Stock Valuation & Inventory Asset Register</h1>
            <p class="text-muted small mb-0">Live stock valuation based on purchase cost, reorder level alerts, and godown asset quantities.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fa-solid fa-print me-1"></i> Print Valuation Sheet
            </button>
        </div>
    </div>

    <!-- Summary Total Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Inventory Asset Value</div>
                <div class="display-6 fw-bold text-success mt-1">₹ {{ number_format($report['total_valuation'], 2) }}</div>
                <div class="small text-muted mt-1">Asset valuation on Balance Sheet</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Inventory Items Count</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $report['total_items'] }} SKUs</div>
                <div class="small text-muted mt-1">Electronics, Solar, Batteries & Raw Materials</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Low Stock SKUs</div>
                <div class="fs-4 fw-bold text-danger mt-1">{{ collect($report['rows'])->where('is_low_stock', true)->count() }} Items</div>
                <div class="small text-danger mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Reorder Recommended</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Stock Valuation Breakdown</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product SKU / Code</th>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Purchase Cost (₹)</th>
                            <th>Selling Price (₹)</th>
                            <th>Inventory Valuation (₹)</th>
                            <th>Stock Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['rows'] as $row)
                            <tr>
                                <td><span class="badge bg-secondary font-monospace">{{ $row['product']->sku ?? 'SKU-'.$row['product']->id }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $row['product']->name }}</div>
                                    <div class="small text-muted">
                                        HSN: {{ $row['product']->hsn_code ?? 'N/A' }}
                                        @if($row['product']->has_inventory_components && $row['product']->inventoryComponents->count())
                                            &bull; <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Kit: {{ $row['product']->inventoryComponents->count() }} Components</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="fw-bold fs-6">{{ $row['quantity'] }}</td>
                                <td>{{ $row['unit'] }}</td>
                                <td>₹ {{ number_format($row['purchase_rate'], 2) }}</td>
                                <td>₹ {{ number_format($row['selling_rate'], 2) }}</td>
                                <td class="fw-bold text-success fs-6">₹ {{ number_format($row['valuation'], 2) }}</td>
                                <td>
                                    @if($row['is_low_stock'])
                                        <span class="badge bg-danger">LOW STOCK</span>
                                    @else
                                        <span class="badge bg-success">NORMAL</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No stock items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Total Valuation:</td>
                            <td class="text-success fs-5">₹ {{ number_format($report['total_valuation'], 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
