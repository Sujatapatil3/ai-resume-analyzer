@extends('layout')

@section('title', 'History | AI Resume Analyzer')
@section('page', 'history')

@section('nav')
<a href="/dashboard" class="btn btn-outline-secondary btn-sm">Dashboard</a>
<a href="/analyzer" class="btn btn-primary btn-sm">New Analysis</a>
<button class="btn btn-outline-danger btn-sm logout-btn">Logout</button>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 fw-bold mb-1">Analysis History</h1>
        <p class="text-secondary mb-0">Your previous resume comparisons.</p>
    </div>
    <a href="/analyzer" class="btn btn-primary">New Analysis</a>
</div>

<div id="history-alert"></div>
<div id="history-list"></div>
@endsection
