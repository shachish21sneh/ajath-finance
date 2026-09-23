@extends('layouts.app')

@section('title', 'Users & RBAC')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">User Management & Permissions</h4>
        <p class="text-muted small mb-0">Role-based access control (Super Admin, Accountant, Sales Manager, Auditor)</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm fw-semibold">
        <i class="fa-solid fa-user-plus me-1"></i> Add User
    </a>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Default Company</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 34px; height: 34px;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <span class="fw-semibold">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="small">{{ $u->email }}</td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fw-semibold">
                                <i class="fa-solid fa-shield-halved me-1"></i> {{ $u->role->name ?? 'No Role' }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $u->company->name ?? 'All Companies' }}</td>
                        <td>
                            @if($u->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Edit user">
                                <i class="fa-solid fa-pen text-muted"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
