@extends('layout')

@section('title', 'Register | AI Resume Analyzer')
@section('page', 'register')

@section('nav')
<a href="/login" class="btn btn-outline-primary btn-sm">Login</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <h1 class="h3 fw-bold mb-2">Create your account</h1>
                <p class="text-secondary mb-4">Start analyzing resumes against job descriptions.</p>

                <div id="form-alert"></div>

                <form id="register-form" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" minlength="8" required>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="password_confirmation" minlength="8" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit" id="submit-btn">Create account</button>
                </form>

                <p class="text-center text-secondary mt-4 mb-0">
                    Already registered? <a href="/login">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
