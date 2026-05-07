@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="fw-bold mb-0" id="editor-title">
                        <i class="fa-solid fa-pen-to-square me-2"></i>
                        {{ isset($id) ? 'Edit Blog' : 'New Blog' }}
                    </h4>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary btn-sm" href="{{ url('/admin/blogs') }}">
                            <i class="fa-solid fa-arrow-left me-1"></i>Back
                        </a>
                        <button id="btn-logout" class="btn btn-outline-danger btn-sm" type="button">
                            <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
                        </button>
                    </div>
                </div>

                <div id="blogs-edit-error" class="alert alert-danger d-none"></div>

                <form id="blog-editor-form" enctype="multipart/form-data">
                    <input type="hidden" id="blog-id" value="{{ isset($id) ? $id : '' }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input id="blog-title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug (auto)</label>
                        <input id="blog-slug" class="form-control" disabled>
                        <div class="form-text">Unique slug is generated from title.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Short Description</label>
                        <textarea id="blog-short-desc" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select id="blog-category-id" class="form-select" required>
                            <option value="">Select category</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Featured Image</label>
                        <input id="blog-image" class="form-control" type="file" accept="image/*">
                        <div class="mt-2">
                            <img id="blog-image-preview" class="rounded-3 d-none" style="max-height: 160px;" alt="Preview">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content</label>
                        <div id="editor"></div>
                        <input type="hidden" id="blog-content" name="content">
                        <div class="form-text">Stored as HTML (LONGTEXT).</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button id="btn-save-blog" class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-floppy-disk me-2"></i>
                            {{ isset($id) ? 'Update Blog' : 'Create Blog' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Notes</h5>
                <ul class="text-muted small mb-0">
                    <li>Slug generated from Title (backend ensures uniqueness).</li>
                    <li>CKEditor supports tables, links, lists, images.</li>
                    <li>Image uploads go through the Laravel API endpoint.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    /* Ensure the editor has a good height and text is always visible */
    .ck-editor__editable_inline {
        min-height: 300px;
        color: #000000 !important;
    }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/super-build/ckeditor.js"></script>

<script>
    window.__page = 'admin.blogs_edit';

    (function () {
        const apiBase = window.AppConfig.apiBaseUrl;
        const token = localStorage.getItem('admin_token');

        const blogId = document.getElementById('blog-id').value || null;

        function apiFetchAdmin(path, { method = 'GET', body = null, isFormData = false } = {}) {
            const headers = {
                'Accept': 'application/json'
            };
            if (!isFormData) headers['Content-Type'] = 'application/json';
            if (token) headers['Authorization'] = 'Bearer ' + token;

            return fetch(apiBase + path, {
                method,
                headers,
                body: isFormData ? body : (body ? JSON.stringify(body) : null),
            }).then(async (res) => {
                const text = await res.text();
                let json = null;
                try { json = text ? JSON.parse(text) : null; } catch (e) {}
                if (res.status === 401) { window.location.href='/admin/login'; throw new Error('Unauthorized'); }
                if (!res.ok) throw new Error((json && (json.message||json.error)) ? (json.message||json.error) : 'Admin API error');
                return json;
            });
        }

        function esc(s){ return $('<div>').text(s||'').html(); }

        async function loadCategories() {
            const json = await apiFetchAdmin('/admin/categories');
            const select = $('#blog-category-id').empty();
            select.append('<option value="">Select category</option>');
            (json.data || []).forEach((c) => {
            const count = c.blogs_count || 0;
            select.append(`<option value="${c.id}">▸ ${esc(c.name)} (${count})</option>`);
            });
        }

        async function loadBlogIfEditing() {
            if (!blogId) return;
            const json = await apiFetchAdmin('/admin/blogs/' + blogId);
            const b = json.data || {};

            $('#blog-title').val(b.title || '');
            // slug is auto; show generated preview only (backend enforced)
            $('#blog-slug').val(b.slug || '');
            $('#blog-short-desc').val(b.short_description || '');
            $('#blog-category-id').val(String(b.category_id || ''));
            if (b.image) {
                $('#blog-image-preview').removeClass('d-none').attr('src', b.image);
            }
            // editor content
            window.__initialEditorHTML = b.content || '';
        }

        // slug preview from title (format only)
        $('#blog-title').on('input', function () {
            const v = $(this).val() || '';
            const slug = v.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');
            $('#blog-slug').val(slug);
        });

        $('#btn-logout').on('click', async function(){
            try { await apiFetchAdmin('/admin/logout', { method: 'POST', body: {} }); } catch(e){}
            localStorage.removeItem('admin_token');
            window.location.href='/admin/login';
        });

        function getCKUploadAdapterUrl() {
            return apiBase + '/admin/ckeditor/upload';
        }

        function buildCKEditor() {
            return CKEDITOR.ClassicEditor.create(document.querySelector('#editor'), {
                toolbar: [
                    'heading', '|', 'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'bold', 'italic', 'underline', '|',
                    'link', 'bulletedList', 'numberedList', '|',
                    'insertTable', '|',
                    'imageUpload', 'blockQuote', 'undo', 'redo'
                ],
                removePlugins: [
                    'CKBox', 'CKFinder', 'EasyImage', 'RealTimeCollaborativeComments', 
                    'RealTimeCollaborativeTrackChanges', 'RealTimeCollaborativeRevisionHistory', 
                    'PresenceList', 'Comments', 'TrackChanges', 'TrackChangesData', 
                    'RevisionHistory', 'Pagination', 'WProofreader', 'MathType',
                    'DocumentOutline', 'Template', 'TableOfContents', 'PasteFromOfficeEnhanced', 'SlashCommand',
                    'AIAssistant', 'MultiLevelList', 'FormatPainter', 'CaseChange'
                ],
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', title: 'Heading 2', class: 'ck-heading_heading2' }
                    ]
                },
                image: {
                    toolbar: ['imageTextAlternative', 'imageStyle:full', 'imageStyle:side'],
                }
            }).then(editor => {
                window.__ckeditor = editor;

                editor.model.document.on('change:data', () => {
                    $('#blog-content').val(editor.getData());
                });

                // patch in image upload adapter (file->POST->JSON {url})
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                    return {
                        upload: () => {
                            return loader.file.then(file => new Promise(async (resolve, reject) => {
                                try {
                                    const formData = new FormData();
                                    formData.append('upload', file);

                                    const res = await fetch(getCKUploadAdapterUrl(), {
                                        method: 'POST',
                                        headers: {
                                            'Authorization': 'Bearer ' + token,
                                            'Accept': 'application/json'
                                        },
                                        body: formData
                                    });

                                    const text = await res.text();
                                    let json = null;
                                    try { json = text ? JSON.parse(text) : null; } catch(e){}
                                    if (!res.ok) throw new Error((json && (json.message||json.error)) ? (json.message||json.error) : 'Upload failed');

                                    // CKEditor expects { default: url }
                                    resolve({ default: json.url });
                                } catch (e) {
                                    reject(e);
                                }
                            }));
                        }
                    };
                };

                // Set initial HTML if editing
                if (window.__initialEditorHTML) {
                    editor.setData(window.__initialEditorHTML);
                    $('#blog-content').val(editor.getData());
                }

                return editor;
            });
        }

        $('#blog-editor-form').on('submit', async function(e){
            e.preventDefault();
            $('#blogs-edit-error').addClass('d-none').text('');

            try{
                const title = $('#blog-title').val() || '';
                const shortDesc = $('#blog-short-desc').val() || '';
                const categoryId = $('#blog-category-id').val();
                const content = window.__ckeditor ? window.__ckeditor.getData() : ($('#blog-content').val() || '');

                if (!title || !shortDesc || !categoryId || !content) {
                    throw new Error('Please fill all required fields.');
                }

                const formData = new FormData();
                formData.append('title', title);
                formData.append('short_description', shortDesc);
                formData.append('content', content);
                formData.append('category_id', categoryId);

                const fileInput = $('#blog-image')[0];
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append('image', fileInput.files[0]);
                }

                if (blogId) {
                    formData.append('_method', 'PUT');
                    await apiFetchAdmin('/admin/blogs/' + blogId, { method: 'POST', body: formData, isFormData: true });
                } else {
                    await apiFetchAdmin('/admin/blogs', { method: 'POST', body: formData, isFormData: true });
                }

                window.location.href='/admin/blogs';
            } catch(err){
                $('#blogs-edit-error').removeClass('d-none').text(err.message || 'Save failed');
            }
        });

        $(document).ready(async function(){
            if (!token) {
                window.location.href='/admin/login';
                return;
            }
            await loadCategories();
            await loadBlogIfEditing();
            await buildCKEditor();
        });
    })();
</script>
@endpush
