@extends('layouts.auth')

@section('content')
<div class="card card-modern shadow-lg border-0">
    <div class="card-body p-4 p-sm-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 mb-3 shadow" style="width: 52px; height: 52px; font-size: 1.5rem;">
                <i class="fa-solid fa-shapes"></i>
            </div>
            <h4 class="fw-bold text-main mb-1">Ajath Cloud ERP</h4>
            <p class="text-muted small">Sign in to your accounting workspace</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control border-start-0" value="{{ old('email', 'admin@ajath.com') }}" required autofocus placeholder="name@company.com">
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label small fw-semibold mb-0">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0" value="password" required placeholder="••••••••">
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                <label class="form-check-label small text-muted" for="remember">
                    Remember my session
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Log In
            </button>
        </form>

        <div class="pt-3 border-top text-center">
            <span class="text-muted small d-block mb-2">Quick Demo Accounts:</span>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillDemo('admin@ajath.com', 'password')">
                    <i class="fa-solid fa-user-shield me-1"></i> Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillDemo('accountant@ajath.com', 'password')">
                    <i class="fa-solid fa-calculator me-1"></i> Accountant
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endsection
