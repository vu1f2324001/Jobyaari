@extends('layouts.app')

@section('content')
<div class="row g-4 align-items-center">
    <div class="col-lg-7">
        <div class="p-4 p-lg-5 hero-card">
            <h1 class="display-5 fw-bold mb-3">Find your next role. Read the latest stories.</h1>
            <p class="lead text-white-50 mb-4">
                A modern job portal + news blog experience. Filter by categories, search instantly, and read full posts—no page reload.
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ url('/blogs') }}" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-book-open me-2"></i>Browse Blogs
                </a>
                <a href="{{ url('/blogs') }}#categories" class="btn btn-outline-light btn-lg">
                    <i class="fa-solid fa-layer-group me-2"></i>Categories
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3"><i class="fa-solid fa-tags me-2"></i>Categories</h5>
                <div id="home-categories" class="d-flex flex-column gap-2">
                    <div class="text-muted">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-5">

<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="fw-bold mb-0"><i class="fa-solid fa-clock me-2"></i>Latest Blogs</h2>
    <a href="{{ url('/blogs') }}" class="text-decoration-none">
        View all <i class="fa-solid fa-arrow-right ms-1"></i>
    </a>
</div>

<div id="home-latest-blogs" class="row g-4">
    <!-- AJAX cards -->
</div>

<div id="home-latest-empty" class="text-center text-muted py-5 d-none">
    No blogs found.
</div>
@endsection

@push('scripts')
<script>
    window.__page = 'home';
</script>
@endpush
