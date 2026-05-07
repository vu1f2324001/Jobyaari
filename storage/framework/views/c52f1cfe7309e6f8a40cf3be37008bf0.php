

<?php $__env->startSection('content'); ?>
<div id="blog-detail-root" data-slug="<?php echo e($slug); ?>">
    <div class="row g-4">
        <div class="col-lg-8">
            <div id="blog-main" class="card shadow-sm border-0 p-4">
                <div class="text-muted">Loading...</div>
            </div>

            <div class="mt-4" id="related-section">
                <h4 class="fw-bold mb-3"><i class="fa-solid fa-people-group me-2"></i>Related Blogs</h4>
                <div id="related-blogs" class="row g-4">
                    <!-- AJAX injected -->
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Search blogs</label>
                        <input id="detail-search" class="form-control" type="text" placeholder="Type to search...">
                        <div class="form-text">Live search powered by AJAX.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select id="detail-search-category" class="form-select">
                            <option value="">All Categories</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Publish Date</label>
                        <input id="detail-search-date" type="date" class="form-control">
                    </div>

                    <button id="detail-search-btn" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter me-2"></i>Search
                    </button>

                    <hr class="my-4">

                    <div class="text-muted small">
                        Tip: Use the search box to fetch matching posts instantly.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    window.__page = 'blogs.show';
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\varsha\Desktop\god\jobyaari\resources\views/blogs/show.blade.php ENDPATH**/ ?>