@extends('layout')

@section('title', 'Analysis Result | AI Resume Analyzer')
@section('page', 'result')

@section('nav')
<a href="/dashboard" class="btn btn-outline-secondary btn-sm">Dashboard</a>
<a href="/history" class="btn btn-outline-secondary btn-sm">History</a>
<a href="/analyzer" class="btn btn-primary btn-sm">New Analysis</a>
<button class="btn btn-outline-danger btn-sm logout-btn">Logout</button>
@endsection

@section('content')
<div id="result-alert"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 fw-bold mb-1">Analysis Result</h1>
        <p class="text-secondary mb-0" id="analysis-source">Loading...</p>
    </div>
    <a href="/analyzer" class="btn btn-primary">Analyze another</a>
</div>

<div id="result-content" class="d-none">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body p-4">
                    <div class="display-5 fw-bold" id="match-percentage">0%</div>
                    <div class="text-secondary">Resume match</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold">Matched skills</h2>
                    <div id="matched-skills"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold">Missing skills</h2>
                    <div id="missing-skills"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold">Strengths</h2>
                    <div id="strengths"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold">Weaknesses</h2>
                    <div id="weaknesses"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold">Recommendations</h2>
                    <div id="recommendations"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold">Interview questions</h2>
                    <div id="interview-questions"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold">Resume</h2>
            <pre class="result-text" id="resume-output"></pre>
            <hr>
            <h2 class="h5 fw-bold">Job description</h2>
            <pre class="result-text" id="jd-output"></pre>
        </div>
    </div>
</div>
@endsection
