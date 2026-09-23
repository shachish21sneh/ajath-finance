@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card card-modern p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-circle me-2 text-primary"></i> Account Profile</h5>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address (Read-only)</label>
                    <input type="email" class="form-control" value="{{ $user->email }}" disabled readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Assigned Role</label>
                    <input type="text" class="form-control" value="{{ $user->role->name ?? 'User' }}" disabled readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+91...">
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3">Change Password</h6>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-modern p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recent Activity Logs</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Timestamp</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $act)
                            <tr>
                                <td class="small text-muted">{{ $act->created_at->format('d-M-Y H:i') }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary">{{ $act->module }}</span></td>
                                <td class="fw-semibold small">{{ $act->action }}</td>
                                <td class="small">{{ $act->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No recent activity logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
