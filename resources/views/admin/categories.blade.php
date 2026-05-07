@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-list me-2"></i>Categories</h4>
                    <button id="btn-new-category" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-plus me-1"></i>New
                    </button>
                </div>

                <div id="categories-error" class="alert alert-danger d-none"></div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categories-tbody">
                            <tr><td colspan="3" class="text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3" id="categories-form-title">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Add Category
                </h5>

                <form id="category-form">
                    <input type="hidden" id="category-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input id="category-name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug (auto)</label>
                        <input id="category-slug" class="form-control" disabled>
                        <div class="form-text">Generated from title and kept unique.</div>
                    </div>

                    <button id="btn-save-category" class="btn btn-primary w-100" type="submit">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Save
                    </button>
                    <button id="btn-cancel-category" class="btn btn-link w-100 d-none" type="button">
                        Cancel
                    </button>
                </form>

                <hr class="my-4">

                <div class="text-muted small">
                    Slug uniqueness is enforced by backend.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.__page = 'admin.categories';
</script>
<script>
(function(){
    const token = localStorage.getItem('admin_token');

    function apiAuthHeaders() {
        return { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };
    }

    async function apiFetchAdmin(path, { method='GET', body=null } = {}) {
        const res = await fetch(window.AppConfig.apiBaseUrl + path, {
            method,
            headers: Object.assign({
                'Accept': 'application/json'
            }, token ? { 'Authorization': 'Bearer ' + token } : {}),
            body: body
        });
        const text = await res.text();
        let json = null;
        try { json = text ? JSON.parse(text) : null; } catch(e){}

        if (res.status === 401) { window.location.href='/admin/login'; throw new Error('Unauthorized'); }
        if (!res.ok) throw new Error((json && (json.message||json.error)) ? (json.message||json.error) : 'Admin API error');
        return json;
    }

    function setFormMode(mode) {
        if (mode === 'new') {
            $('#category-id').val('');
            $('#category-form-title').text('Add Category');
        }
    }

    function renderCategories(categories){
        const tbody = $('#categories-tbody').empty();
        if (!categories || !categories.length) {
            tbody.append('<tr><td colspan="3" class="text-muted">No categories found.</td></tr>');
            return;
        }

        categories.forEach((c) => {
            tbody.append(`
                <tr>
                    <td>${$('<div>').text(c.name||'').html()}</td>
                    <td>${$('<div>').text(c.slug||'').html()}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary btn-edit-category" data-id="${c.id}" data-name="${$('<div>').text(c.name||'').html()}" data-slug="${$('<div>').text(c.slug||'').html()}">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-del-category" data-id="${c.id}">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    async function loadCategories(){
        $('#categories-error').addClass('d-none').text('');
        const tbody = $('#categories-tbody').html('<tr><td colspan="3" class="text-muted">Loading...</td></tr>');
        const json = await apiFetchAdmin('/admin/categories');
        renderCategories(json.data || []);
    }

    function resetEditor(){
        $('#category-id').val('');
        $('#category-name').val('');
        $('#category-slug').val('');
        $('#btn-save-category').html('<i class="fa-solid fa-floppy-disk me-2"></i>Save');
        $('#btn-cancel-category').addClass('d-none');
        $('#categories-form-title').html('<i class="fa-solid fa-pen-to-square me-2"></i>Add Category');
    }

    async function saveCategory(e){
        e.preventDefault();
        const id = $('#category-id').val();
        const payload = {
            name: $('#category-name').val()
        };

        if (!payload.name) return;

        const isEdit = !!id;

        try {
            const url = isEdit ? '/admin/categories/' + id : '/admin/categories';
            const method = isEdit ? 'PUT' : 'POST';
            const json = await apiFetchAdmin(url, { method, body: JSON.stringify(payload) , headers: {} });
        } catch(err) {
            // fallback: use correct headers approach below
        }
    }

    // Real save with fetch headers
    $('#category-form').on('submit', async function(e){
        e.preventDefault();

        const id = $('#category-id').val();
        const name = $('#category-name').val();
        $('#categories-error').addClass('d-none');

        try{
            const url = id ? '/admin/categories/' + id : '/admin/categories';
            const method = id ? 'PUT' : 'POST';

            const res = await fetch(window.AppConfig.apiBaseUrl + url, {
                method,
                headers: Object.assign({
                    'Accept':'application/json',
                    'Content-Type':'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                }),
                body: JSON.stringify({ name })
            });

            const text = await res.text();
            let json = null;
            try { json = text ? JSON.parse(text) : null; } catch(e){}

            if (res.status === 401) { window.location.href='/admin/login'; return; }
            if (!res.ok) throw new Error((json && (json.message||json.error)) ? (json.message||json.error) : 'Save failed');

            // backend returns category object in json.data
            resetEditor();
            await loadCategories();
        } catch(err){
            $('#categories-error').removeClass('d-none').text(err.message || 'Save failed');
        }
    });

    $(document).on('click', '.btn-edit-category', function(){
        const id = $(this).data('id');
        const name = $(this).data('name');
        const slug = $(this).data('slug');

        $('#category-id').val(id);
        $('#category-name').val(name);
        $('#category-slug').val(slug);

        $('#categories-form-title').html('<i class="fa-solid fa-pen-to-square me-2"></i>Edit Category');
        $('#btn-save-category').html('<i class="fa-solid fa-arrows-rotate me-2"></i>Update');
        $('#btn-cancel-category').removeClass('d-none');
    });

    $(document).on('click', '.btn-del-category', async function(){
        if (!confirm('Delete this category? Blogs under it will be affected depending on DB constraints.')) return;

        const id = $(this).data('id');

        try{
            const res = await fetch(window.AppConfig.apiBaseUrl + '/admin/categories/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept':'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                }
            });
            const text = await res.text();
            let json = null;
            try { json = text ? JSON.parse(text) : null; } catch(e){}
            if (res.status === 401) { window.location.href='/admin/login'; return; }
            if (!res.ok) throw new Error((json && (json.message||json.error)) ? (json.message||json.error) : 'Delete failed');

            await loadCategories();
            if ($('#category-id').val() == id) resetEditor();
        } catch(err){
            $('#categories-error').removeClass('d-none').text(err.message || 'Delete failed');
        }
    });

    $('#btn-new-category').on('click', function(){ resetEditor(); });

    $('#btn-cancel-category').on('click', function(){ resetEditor(); });

    // slug preview auto (format only; backend enforces uniqueness)
    $('#category-name').on('input', function(){
        const v = $(this).val() || '';
        const slug = v.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');
        $('#category-slug').val(slug);
    });

    $(document).ready(function(){
        resetEditor();
        loadCategories().catch(console.error);
    });
})();
</script>
@endpush
