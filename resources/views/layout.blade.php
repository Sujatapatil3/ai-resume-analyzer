<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AI Resume Analyzer')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body data-page="@yield('page')">
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/dashboard">AI Resume Analyzer</a>
            <div class="ms-auto d-flex align-items-center gap-2" id="nav-actions">
                @yield('nav')
            </div>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
        @yield('content')
    </main>

    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
    <script src="/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
