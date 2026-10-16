@extends('layout')

@section('title', 'Analyze Resume | AI Resume Analyzer')
@section('page', 'analyzer')

@section('nav')
<a href="/dashboard" class="btn btn-outline-secondary btn-sm">Dashboard</a>
<a href="/history" class="btn btn-outline-secondary btn-sm">History</a>
<button class="btn btn-outline-danger btn-sm logout-btn">Logout</button>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="mb-4">
            <h1 class="h2 fw-bold">Analyze your resume</h1>
            <p class="text-secondary">Paste your resume and the target job description below.</p>
        </div>

        <div id="form-alert"></div>

        <form id="analyzer-form">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <label for="resume_text" class="form-label fw-semibold">Resume text</label>
                            <textarea class="form-control" id="resume_text" rows="18" maxlength="20000" required placeholder="Paste your resume here..."></textarea>
                            <div class="form-text">Maximum 20,000 characters.</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <label for="job_description" class="form-label fw-semibold">Job description</label>
                            <textarea class="form-control" id="job_description" rows="18" maxlength="20000" required placeholder="Paste the job description here..."></textarea>
                            <div class="form-text">Maximum 20,000 characters.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-primary btn-lg px-5" type="submit" id="analyze-btn">Analyze Resume</button>
            </div>
        </form>
    </div>
</div>
@endsection
