@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3"><i class="fa-solid fa-user-lock me-2"></i>Admin Login</h4>

                <div id="admin-login-error" class="alert alert-danger d-none"></div>

                <form id="admin-login-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input id="admin-email" type="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input id="admin-password" type="password" class="form-control" required>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                    </button>

                    <div class="text-muted small mt-3">
                        Login uses AJAX + token stored in browser localStorage.
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.__page = 'admin.login';
</script>
@endpush
