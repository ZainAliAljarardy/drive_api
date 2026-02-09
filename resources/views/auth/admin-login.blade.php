@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 100vh; background: linear-gradient(135deg, #1f1c2c, #928dab);">
    <div class="col-md-5">
        <div class="card shadow-lg border-0" style="border-radius: 12px;">
            <div class="card-body p-5" style="background-color: #2c2c54; color: #fff; border-radius: 12px;">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock" style="font-size: 4rem; color: #f9ca24;"></i>
                    <h2 class="mt-3">Admin Login</h2>
                    <p class="text-muted" style="color: #dcdde1 !important;">Administrator access only</p>
                </div>

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label text-light">
                            <i class="bi bi-envelope"></i> Email Address
                        </label>
                        <input type="email" class="form-control bg-dark text-light border-0 @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-light">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <input type="password" class="form-control bg-dark text-light border-0 @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($errors->has('error'))
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> {{ $errors->first('error') }}
                        </div>
                    @endif

                    <button type="submit" class="btn btn-warning w-100 mb-3" style="font-weight: bold;">
                        <i class="bi bi-box-arrow-in-right"></i> Login as Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
