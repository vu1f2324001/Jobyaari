@extends('layouts.app')

@section('content')
<div id="blogs-listing-root" class="row g-4">
    <aside class="col-lg-3">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-filter me-2"></i>Filters</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category</label>
                    <select id="filter-category" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Publish Date</label>
                    <input id="filter-date" type="date" class="form-control">
                </div>

                <div class="mb-1">
                    <label class="form-label fw-semibold">Live Search</label>
                    <input id="filter-search" type="text" class="form-control" placeholder="Search blogs...">
                </div>

                <div class="mt-3 text-muted small">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                    Updates instantly via AJAX (no reload).
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" id="sidebar-categories-card">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-layer-group me-2"></i>Categories</h5>
                <div id="sidebar-categories" class="d-flex flex-column gap-2">
                    <div class="text-muted">Loading...</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fw-bold mb-0"><i class="fa-solid fa-book me-2"></i>Blogs</h2>
            <div class="text-muted small" id="blogs-meta-hint"></div>
        </div>

        <div id="blogs-empty" class="text-center text-muted py-5 d-none">
            No blogs found. Try changing filters.
        </div>

        <div id="blogs-cards" class="row g-4">
            <!-- AJAX injected -->
        </div>

        <div class="mt-4">
            <div id="blogs-pagination" class="d-flex flex-wrap"></div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    window.__page = 'blogs.index';
</script>
@endpush
