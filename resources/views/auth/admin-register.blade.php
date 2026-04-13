@extends('layouts.app')

@section('title', 'Admin Registration')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 100vh; background: linear-gradient(135deg, #1f1c2c, #928dab);">
    <div class="col-md-5">
        <div class="card shadow-lg border-0" style="border-radius: 12px;">
            <div class="card-body p-5" style="background-color: #2c2c54; color: #fff; border-radius: 12px;">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus" style="font-size: 4rem; color: #f9ca24;"></i>
                    <h2 class="mt-3">Create Admin Account</h2>
                    <p class="text-muted" style="color: #dcdde1 !important;">Fill in the details to register a new admin</p>
                </div>

                <form method="POST" action="{{ route('admin.register.submit') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label text-light">
                            <i class="bi bi-person"></i> Full Name
                        </label>
                        <input type="text" class="form-control bg-dark text-light border-0 @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label text-light">
                            <i class="bi bi-shield-check"></i> Confirm Password
                        </label>
                        <input type="password" class="form-control bg-dark text-light border-0" 
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 mb-3" style="font-weight: bold;">
                        <i class="bi bi-person-check"></i> Register Admin
                    </button>
                    
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-decoration-none" style="color: #f9ca24;">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection