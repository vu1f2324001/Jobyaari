

<?php $__env->startSection('content'); ?>
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold mb-0"><i class="fa-solid fa-newspaper me-2"></i>Blog Management</h4>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary btn-sm" href="<?php echo e(route('admin.blogs.new')); ?>">
                    <i class="fa-solid fa-plus me-1"></i>New Blog
                </a>
            </div>
        </div>

        <div id="blogs-error" class="alert alert-danger d-none"></div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Content</th>
                                <th style="width: 240px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="blogs-tbody">
                            <tr><td colspan="6" class="text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button id="btn-logout" class="btn btn-outline-danger btn-sm">
                <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    window.__page = 'admin.blogs';

(function(){
    async function callAdmin(path, {method='GET', body=null} = {}){
        const token = localStorage.getItem('admin_token');
        const res = await fetch(window.AppConfig.apiBaseUrl + path, {
            method,
            headers: {
                'Accept':'application/json',
                'Content-Type': body ? 'application/json' : 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: body ? JSON.stringify(body) : null
        });

        const text = await res.text();
        let json = null;
        try { json = text ? JSON.parse(text) : null; } catch(e){}

        if (res.status === 401) { window.location.href='/admin/login'; throw new Error('Unauthorized'); }
        if (!res.ok) throw new Error((json && (json.message||json.error)) ? (json.message||json.error) : 'Admin API error');

        return json;
    }

    function esc(s){ return $('<div>').text(s||'').html(); }

    function renderBlogs(blogs){
        const tbody = $('#blogs-tbody').empty();
        if (!blogs || !blogs.length){
            tbody.append('<tr><td colspan="5" class="text-muted">No blogs found.</td></tr>');
            return;
        }

        blogs.forEach((b) => {
            const title = esc(b.title);
            const cat = esc(b.category ? b.category : 'Uncategorized');
            
            // Format the created_at date to display in the new Date column
            let dateVal = 'N/A';
            if (b.created_at) {
                const d = new Date(b.created_at);
                dateVal = esc(d.toLocaleDateString());
            }

            const content = esc(b.short_description).substring(0, 60) + (b.short_description.length > 60 ? '...' : '');

            const imgTag = b.image
                ? `<img src="${b.imageUrl}" width="60" height="60" style="object-fit: cover; border-radius: 4px;" alt="image">`
                : '<span class="text-muted small">No Image</span>';

            tbody.append(`
                <tr>
                    <td>${imgTag}</td>
                    <td><strong>${title}</strong></td>
                    <td>${cat}</td>
                    <td>${dateVal}</td>
                    <td><small class="text-muted">${content}</small></td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <a class="btn btn-sm btn-outline-primary" href="/admin/blogs/${b.id}/edit">
                                <i class="fa-solid fa-pen me-1"></i>Edit
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-del-blog" data-id="${b.id}">
                                <i class="fa-solid fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    async function loadBlogs(){
        $('#blogs-error').addClass('d-none').text('');
        $('#blogs-tbody').html('<tr><td colspan="5" class="text-muted">Loading...</td></tr>');

        const json = await callAdmin('/admin/blogs');
        renderBlogs(json.data || []);
    }

    $(document).on('click', '.btn-del-blog', async function(){
        const id = $(this).data('id');
        if (!confirm('Delete this blog?')) return;

        try{
            await callAdmin('/admin/blogs/' + id, {method:'DELETE'});
            await loadBlogs();
        }catch(err){
            $('#blogs-error').removeClass('d-none').text(err.message || 'Delete failed');
        }
    });

    $('#btn-logout').on('click', async function(){
        try{
            await callAdmin('/admin/logout', {method:'POST', body:{}});
        }catch(e){}
        localStorage.removeItem('admin_token');
        window.location.href='/admin/login';
    });

    $(document).ready(function(){
        loadBlogs().catch(err => {
            $('#blogs-error').removeClass('d-none').text(err.message || 'Failed to load blogs');
        });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\varsha\Desktop\god\jobyaari\resources\views/admin/blogs.blade.php ENDPATH**/ ?>