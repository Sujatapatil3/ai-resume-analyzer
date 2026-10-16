@extends('layout')

@section('title', 'Dashboard | AI Resume Analyzer')
@section('page', 'dashboard')

@section('nav')
<a href="/history" class="btn btn-outline-secondary btn-sm">History</a>
<button class="btn btn-outline-danger btn-sm logout-btn">Logout</button>
@endsection

@section('content')
<div class="row align-items-center g-4">
    <div class="col-lg-7">
        <span class="badge text-bg-primary mb-3">AI-powered resume analysis</span>
        <h1 class="display-6 fw-bold">See how closely your resume matches a job.</h1>
        <p class="lead text-secondary mt-3">
            Compare your skills with a job description, identify gaps, and get practical interview preparation.
        </p>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="/analyzer" class="btn btn-primary btn-lg">Analyze Resume</a>
            <a href="/history" class="btn btn-outline-secondary btn-lg">View History</a>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold">Your account</h2>
                <p class="mb-2"><strong>Name:</strong> <span id="user-name">Loading...</span></p>
                <p class="mb-0"><strong>Email:</strong> <span id="user-email">Loading...</span></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-4">
    <div class="col-md-4">
        <div class="feature-card h-100">
            <h3 class="h6 fw-bold">1. Add your resume</h3>
            <p class="text-secondary mb-0">Paste the text of your current resume.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="feature-card h-100">
            <h3 class="h6 fw-bold">2. Add the job description</h3>
            <p class="text-secondary mb-0">Paste the requirements from the job posting.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="feature-card h-100">
            <h3 class="h6 fw-bold">3. Get your analysis</h3>
            <p class="text-secondary mb-0">See matching skills, gaps and interview questions.</p>
        </div>
    </div>
</div>
@endsection
