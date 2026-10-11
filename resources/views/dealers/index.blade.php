@extends('layouts.app')

@section('title', 'Dealer & Distributor Management')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Dealer & Channel Partner Management</h1>
            <p class="text-muted small mb-0">Manage regional distributors, tier pricing (Distributor, Dealer, Retail), commissions, and receivables.</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDealerModal">
                <i class="fa-solid fa-user-plus me-1"></i> Add Channel Partner
            </button>
        </div>
    </div>

    <!-- Metrics Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Partners</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $dealers->count() }} Distributors/Dealers</div>
                <div class="small text-success mt-1">{{ $dealers->where('status', 'active')->count() }} Active Channels</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Distributor Network</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $dealers->where('price_tier', 'DISTRIBUTOR')->count() }} Regional Hubs</div>
                <div class="small text-muted mt-1">Tier-1 Wholesale</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Dealer Outstanding</div>
                <div class="fs-4 fw-bold text-danger mt-1">₹ {{ number_format($dealers->sum('outstanding_balance'), 2) }}</div>
                <div class="small text-muted mt-1">Credit Monitored</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Commissions Settled</div>
                <div class="fs-4 fw-bold text-dark mt-1">₹ {{ number_format($commissions->sum('commission_amount'), 2) }}</div>
                <div class="small text-muted mt-1">{{ $commissions->count() }} Payout Records</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Authorized Dealer & Distributor Registry</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Partner / Company Name</th>
                            <th>Territory</th>
                            <th>Price Tier</th>
                            <th>Contact / Phone</th>
                            <th>Credit Terms</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dealers as $d)
                            <tr>
                                <td><span class="badge bg-secondary font-monospace">{{ $d->dealer_code }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $d->name }}</div>
                                    <div class="small text-muted">{{ $d->company_name ?? 'Sole Proprietor' }} | GSTIN: {{ $d->gstin ?? 'N/A' }}</div>
                                </td>
                                <td>{{ $d->territory ?? 'All India' }}</td>
                                <td>
                                    <span class="badge bg-{{ $d->price_tier === 'DISTRIBUTOR' ? 'primary' : 'info text-dark' }}">
                                        {{ $d->price_tier }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $d->phone ?? '—' }}</div>
                                    <small class="text-muted">{{ $d->email ?? '' }}</small>
                                </td>
                                <td>
                                    <div>Limit: ₹ {{ number_format($d->credit_limit, 2) }}</div>
                                    <small class="text-muted">{{ $d->credit_days }} Days Credit</small>
                                </td>
                                <td class="fw-bold text-danger">₹ {{ number_format($d->outstanding_balance, 2) }}</td>
                                <td><span class="badge bg-{{ ($d->status ?? 'active') === 'active' ? 'success' : 'secondary' }} text-uppercase">{{ $d->status ?? 'active' }}</span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-primary" data-bs-toggle="modal" data-bs-target="#editDealerModal{{ $d->id }}" title="Edit Partner">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Edit Dealer Modal -->
                                    <div class="modal fade text-start" id="editDealerModal{{ $d->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <form action="{{ route('dealers.update', $d->id) }}" method="POST" class="modal-content">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Channel Partner: {{ $d->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Dealer Code</label>
                                                        <input type="text" class="form-control" value="{{ $d->dealer_code }}" disabled>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label small fw-bold">Contact Person Name *</label>
                                                        <input type="text" name="name" class="form-control" required value="{{ old('name', $d->name) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Firm / Company Name</label>
                                                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $d->company_name) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Price Tier Level *</label>
                                                        <select name="price_tier" class="form-select" required>
                                                            <option value="DISTRIBUTOR" {{ $d->price_tier === 'DISTRIBUTOR' ? 'selected' : '' }}>Distributor (Wholesale / Master Stockist)</option>
                                                            <option value="DEALER" {{ $d->price_tier === 'DEALER' ? 'selected' : '' }}>Authorized Dealer (Retail Supply)</option>
                                                            <option value="RETAILER" {{ $d->price_tier === 'RETAILER' ? 'selected' : '' }}>Direct Retailer</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Sales Territory / State</label>
                                                        <input type="text" name="territory" class="form-control" value="{{ old('territory', $d->territory) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">GSTIN Number</label>
                                                        <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $d->gstin) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Phone Number</label>
                                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $d->phone) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Email</label>
                                                        <input type="email" name="email" class="form-control" value="{{ old('email', $d->email) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="active" {{ ($d->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ ($d->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Credit Limit (₹)</label>
                                                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $d->credit_limit) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Credit Days</label>
                                                        <input type="number" name="credit_days" class="form-control" value="{{ old('credit_days', $d->credit_days) }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Update Partner</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No channel partners registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-muted">Showing {{ $dealers->firstItem() ?? ($dealers->total() > 0 ? 1 : 0) }} to {{ $dealers->lastItem() ?? $dealers->total() }} of {{ $dealers->total() }} partners</span>
                <div>
                    @if($dealers->hasPages())
                        {{ $dealers->links() }}
                    @else
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                            <li class="page-item active"><span class="page-link">1</span></li>
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Dealer Modal -->
<div class="modal fade" id="addDealerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('dealers.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-primary me-2"></i>Add Channel Partner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Dealer Code *</label>
                    <input type="text" name="dealer_code" class="form-control" required value="DLR-{{ rand(100, 999) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Contact Person Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Vikas Gupta">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Firm / Company Name</label>
                    <input type="text" name="company_name" class="form-control" placeholder="e.g. Gupta Electronics & Solar Power">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Price Tier Level *</label>
                    <select name="price_tier" class="form-select" required>
                        <option value="DISTRIBUTOR">Distributor (Wholesale / Master Stockist)</option>
                        <option value="DEALER" selected>Authorized Dealer (Retail Supply)</option>
                        <option value="RETAILER">Direct Retailer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Sales Territory / State</label>
                    <input type="text" name="territory" class="form-control" placeholder="e.g. Delhi NCR / Haryana">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">GSTIN Number</label>
                    <input type="text" name="gstin" class="form-control" placeholder="07AAAAA0000A1Z5">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 98111 22334">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Credit Limit (₹)</label>
                    <input type="number" step="0.01" name="credit_limit" class="form-control" value="200000">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Credit Days</label>
                    <input type="number" name="credit_days" class="form-control" value="30">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Register Partner</button>
            </div>
        </form>
    </div>
</div>
@endsection
