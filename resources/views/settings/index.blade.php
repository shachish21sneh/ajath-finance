@extends('layouts.app')

@section('title', 'System Settings & Maintenance')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-sliders text-primary me-2"></i> System Settings & Shared Hosting Setup</h4>
        <p class="text-muted small mb-0">System configuration, performance parameters, and cPanel maintenance</p>
    </div>
    <a href="{{ route('settings.backup') }}" class="btn btn-success btn-sm fw-semibold shadow-sm">
        <i class="fa-solid fa-download me-1"></i> Download Company Backup (JSON)
    </a>
</div>

<div class="row g-4">
    <!-- Environment & Diagnostic Checks -->
    <div class="col-lg-6">
        <div class="card card-modern p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-server text-primary me-2"></i> Hosting Environment Diagnostics</h6>
            <div class="list-group list-group-flush small">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">PHP Version:</span>
                    <span class="badge bg-success-subtle text-success fw-bold">{{ $phpVersion }} (Optimal)</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Database Engine:</span>
                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase">{{ $dbConnection }} (MySQL 8 Supported)</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Session Driver:</span>
                    <span class="badge bg-success-subtle text-success">{{ $sessionDriver }} (Shared Hosting Ready)</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Cache Store:</span>
                    <span class="badge bg-success-subtle text-success">{{ $cacheDriver }} (Zero Redis Overhead)</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Queue Driver:</span>
                    <span class="badge bg-info-subtle text-info-emphasis">Database Queue (cPanel Cron Ready)</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Laravel Framework:</span>
                    <span class="fw-bold">v12.x</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Company Data Metrics -->
    <div class="col-lg-6">
        <div class="card card-modern p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-database text-primary me-2"></i> Workspace Data Statistics</h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="text-muted small">General Ledgers</div>
                        <div class="fs-4 fw-bold text-primary">{{ $stats['total_ledgers'] }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="text-muted small">Inventory Products</div>
                        <div class="fs-4 fw-bold text-success">{{ $stats['total_products'] }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="text-muted small">Total Vouchers</div>
                        <div class="fs-4 fw-bold text-warning-emphasis">{{ $stats['total_vouchers'] }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="text-muted small">Sales Invoices</div>
                        <div class="fs-4 fw-bold text-info-emphasis">{{ $stats['total_invoices'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- cPanel Shared Hosting Cron Job Instructions -->
    <div class="col-12">
        <div class="card card-modern p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock text-primary me-2"></i> cPanel Cron Job Configuration</h6>
            <p class="small text-muted mb-2">To automate queue processing, scheduled reports, and ledger integrity checks on cPanel without root access, add this standard cron entry in your cPanel <strong>Cron Jobs</strong> tool:</p>
            <div class="bg-dark text-white p-3 rounded-3 font-monospace small mb-3">
                * * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
            </div>
            <div class="small text-muted">
                <i class="fa-solid fa-circle-check text-success me-1"></i> No daemon, Docker, Redis, or Node.js background process required. Runs 100% within shared hosting constraints.
            </div>
        </div>
    </div>
</div>
@endsection
