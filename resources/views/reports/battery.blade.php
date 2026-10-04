@extends('layouts.app')

@section('title', 'Battery ERP Analytics Report')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-car-battery text-danger me-2"></i>Battery ERP & Warranty Intelligence Report</h1>
            <p class="text-muted small mb-0">Production statistics, serialized inventory distribution, laboratory QC certification, and warranty expiration.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fa-solid fa-print me-1"></i> Print Report
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Serialized Packs</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $report['total_serials'] }} Packs</div>
                <div class="small text-muted mt-1">{{ $report['total_models'] }} Model Designs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Stock in Godowns</div>
                <div class="fs-4 fw-bold text-success mt-1">{{ $report['in_stock'] }} In Stock</div>
                <div class="small text-muted mt-1">Ready for Dispatch</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Installed with Customers</div>
                <div class="fs-4 fw-bold text-primary mt-1">{{ $report['installed'] }} Installed</div>
                <div class="small text-muted mt-1">Field Operating</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Warranties Expiring &lt;30d</div>
                <div class="fs-4 fw-bold text-warning mt-1">{{ $report['expiring_soon'] }} Units</div>
                <div class="small text-muted mt-1">Pro-rata Transition</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-shield-halved text-success me-2"></i>Comprehensive Warranty & Quality Compliance</h5>
        <p class="text-muted">Total active warranty contracts registered under Fuzurra ERP: <strong>{{ $report['active_warranties'] }}</strong>. All batteries undergo laboratory QC testing with voltage, internal resistance (mΩ), and capacity Ah certification prior to factory release.</p>
        <div class="mt-3">
            <a href="{{ route('battery.index') }}" class="btn btn-danger">
                <i class="fa-solid fa-barcode me-1"></i> Open Battery Serial Registry
            </a>
        </div>
    </div>
</div>
@endsection
