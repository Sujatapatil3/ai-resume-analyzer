@extends('layout')

@section('title', 'Login | AI Resume Analyzer')
@section('page', 'login')

@section('nav')
<a href="/register" class="btn btn-outline-primary btn-sm">Create account</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <h1 class="h3 fw-bold mb-2">Welcome back</h1>
                <p class="text-secondary mb-4">Login to compare your resume with a job description.</p>

                <div id="form-alert"></div>

                <form id="login-form" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit" id="submit-btn">Login</button>
                </form>

                <p class="text-center text-secondary mt-4 mb-0">
                    Don't have an account? <a href="/register">Register</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
