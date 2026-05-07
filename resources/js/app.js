(function () {
  const Spinner = {
    el: null,
    ensure() {
      if (!this.el) this.el = document.getElementById('ajax-spinner');
      return this.el;
    },
    show() {
      const el = this.ensure();
      if (el) el.classList.remove('d-none');
    },
    hide() {
      const el = this.ensure();
      if (el) el.classList.add('d-none');
    }
  };

  function getApiBaseUrl() {
    return (window.AppConfig && window.AppConfig.apiBaseUrl) ? window.AppConfig.apiBaseUrl : '';
  }

  async function apiFetch(path, { method = 'GET', token = null, params = null, body = null } = {}) {
    const base = getApiBaseUrl();
    let url = base + path;

    if (params && typeof params === 'object') {
      const qs = new URLSearchParams();
      Object.keys(params).forEach((k) => {
        const v = params[k];
        if (v === undefined || v === null || v === '') return;
        qs.append(k, v);
      });
      const sep = url.includes('?') ? '&' : '?';
      url = url + sep + qs.toString();
    }

    const headers = {
      'Accept': 'application/json'
    };

    if (token) headers['Authorization'] = 'Bearer ' + token;

    let payload = body;

    // If plain object provided as body, JSON-encode it.
    if (body && typeof body === 'object' && !(body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      payload = JSON.stringify(body);
    }

    const res = await fetch(url, { method, headers, body: payload });

    let json = null;
    const text = await res.text();
    try {
      json = text ? JSON.parse(text) : null;
    } catch (e) {
      json = null;
    }

    if (!res.ok) {
      const err = new Error('API request failed');
      err.status = res.status;
      err.data = json;
      throw err;
    }

    return json;
  }

  function setLocalToken(token) {
    try {
      localStorage.setItem('admin_token', token);
    } catch (e) {}
  }

  function getLocalToken() {
    try {
      return localStorage.getItem('admin_token');
    } catch (e) {
      return null;
    }
  }

  function adminAjaxAuthGuard(err) {
    if (err && err.status === 401) {
      window.location.href = '/admin/login';
      return true;
    }
    return false;
  }

  function renderCard(blog) {
    const img = blog.image ? `<img src="${blog.image}" class="blog-card-img" alt="${blog.title}">` : '';
    const date = blog.publish_date || '';
    const desc = blog.short_description || '';

    const safeDesc = $('<div>').text(desc).html();

    return `
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          ${img ? `<div class="p-3 pb-0">${img}</div>` : ''}
          <div class="card-body">
            <div class="chip mb-2"><i class="fa-solid fa-layer-group"></i> ${blog.category ? blog.category : ''}</div>
            <h5 class="card-title fw-bold mb-2">${blog.title}</h5>
            <p class="card-text text-muted" style="min-height: 3.6em;">${safeDesc}</p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <small class="text-muted"><i class="fa-regular fa-clock me-1"></i>${date}</small>
              <a class="btn btn-primary btn-sm" href="/blogs/${blog.slug}">Read More <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async function loadHome() {
    try {
      Spinner.show();

      // categories
      const catRes = await apiFetch('/categories', { method: 'GET' });
      const categories = catRes.data || catRes.categories || [];

      const homeCats = $('#home-categories').empty();
      if (!categories.length) {
        homeCats.append(`<div class="text-muted">No categories</div>`);
      } else {
        categories.forEach((c) => {
          homeCats.append(`
            <a class="text-decoration-none text-light" href="/blogs?category=${encodeURIComponent(c.slug)}">
              <div class="chip">
                <i class="fa-solid fa-tag"></i> ${c.name}
              </div>
            </a>
          `);
        });
      }

      // latest blogs
      const blogRes = await apiFetch('/blogs', {
        method: 'GET',
        params: { page: 1, per_page: 6 }
      });

      const blogs = blogRes.data || [];
      const wrap = $('#home-latest-blogs').empty();

      if (!blogs.length) {
        $('#home-latest-empty').removeClass('d-none');
        return;
      }

      $('#home-latest-empty').addClass('d-none');

      blogs.forEach((b) => wrap.append(renderCard(b)));
    } catch (err) {
      console.error(err);
    } finally {
      Spinner.hide();
    }
  }

  function parseFilters() {
    const url = new URL(window.location.href);
    return {
      category: url.searchParams.get('category') || '',
      date: url.searchParams.get('date') || '',
      search: url.searchParams.get('search') || ''
    };
  }

  async function loadBlogsListing({ page = 1 } = {}) {
    const filters = parseFilters();

    const params = {
      page,
      per_page: 6
    };
    if (filters.category) params.category = filters.category;
    if (filters.date) params.date = filters.date;
    if (filters.search) params.search = filters.search;

    try {
      Spinner.show();
      const res = await apiFetch('/blogs', { method: 'GET', params });
      const blogs = res.data || [];
      const meta = res.meta || {};

      const wrap = $('#blogs-cards').empty();

      if (!blogs.length) {
        $('#blogs-empty').removeClass('d-none');
      } else {
        $('#blogs-empty').addClass('d-none');
        blogs.forEach((b) => wrap.append(renderCard(b)));
      }

      // pagination
      const pag = $('#blogs-pagination').empty();
      if (meta.last_page && meta.last_page > 1) {
        for (let p = 1; p <= meta.last_page; p++) {
          const active = p === meta.current_page;
          pag.append(`
            <button class="btn btn-sm ${active ? 'btn-primary' : 'btn-outline-primary'} me-2 mb-2 blog-page-btn" data-page="${p}">
              ${p}
            </button>
          `);
        }
      }
    } catch (err) {
      console.error(err);
    } finally {
      Spinner.hide();
    }
  }

  function setupBlogListingUI() {
    // sidebar filters and live search
    const url = new URL(window.location.href);
    const initialCategory = url.searchParams.get('category') || '';
    const initialDate = url.searchParams.get('date') || '';
    const initialSearch = url.searchParams.get('search') || '';

    $('#filter-category').val(initialCategory);
    $('#filter-date').val(initialDate);
    $('#filter-search').val(initialSearch);

    let searchTimer = null;
    const doSearch = () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        const u = new URL(window.location.href);
        const val = $('#filter-search').val() || '';
        if (val) u.searchParams.set('search', val);
        else u.searchParams.delete('search');

        u.searchParams.set('page', '1');

        window.history.replaceState({}, '', u.toString());
        loadBlogsListing({ page: 1 });
      }, 350);
    };

    $('#filter-category').on('change', () => {
      const u = new URL(window.location.href);
      const val = $('#filter-category').val();
      if (val) u.searchParams.set('category', val);
      else u.searchParams.delete('category');
      u.searchParams.set('page', '1');
      window.history.replaceState({}, '', u.toString());
      loadBlogsListing({ page: 1 });
    });

    $('#filter-date').on('change', () => {
      const u = new URL(window.location.href);
      const val = $('#filter-date').val();
      if (val) u.searchParams.set('date', val);
      else u.searchParams.delete('date');
      u.searchParams.set('page', '1');
      window.history.replaceState({}, '', u.toString());
      loadBlogsListing({ page: 1 });
    });

    $('#filter-search').on('input', doSearch);

    // pagination click
    $(document).on('click', '.blog-page-btn', function () {
      const page = Number($(this).data('page'));
      const u = new URL(window.location.href);
      u.searchParams.set('page', String(page));
      window.history.replaceState({}, '', u.toString());
      loadBlogsListing({ page });
    });

    // categories sidebar
    apiFetch('/categories', { method: 'GET' })
      .then((res) => {
        const cats = res.data || [];

        // Populate dropdown filter
        const sel = $('#filter-category');
        sel.empty();
        sel.append(`<option value="">All Categories</option>`);

        // Populate sidebar list (e.g., ▸ Jobs (189))
        // Assumes you have a <div id="category-list-sidebar"> in your HTML
        const listWrap = $('#category-list-sidebar');
        if (listWrap.length) listWrap.empty();

        cats.forEach((c) => {
          // Assuming the backend provides 'blogs_count'
          const count = c.blogs_count || 0;

          // Add to dropdown
          sel.append(`<option value="${c.slug}">${c.name} (${count})</option>`);

          // Add to sidebar list
          if (listWrap.length) listWrap.append(`<a href="/blogs?category=${c.slug}" class="list-group-item list-group-item-action border-0 py-1">▸ ${c.name} (${count})</a>`);
        });
      })
      .catch(console.error);
  }

  function renderCardsIntoRelated(blogs) {
    const relatedWrap = $('#related-blogs').empty();
    if (!blogs || !blogs.length) {
      relatedWrap.append(`<div class="text-muted">No blogs found.</div>`);
      return;
    }
    blogs.forEach((b) => relatedWrap.append(renderCard(b)));
  }

  async function loadBlogDetail() {
    const slug = $('#blog-detail-root').data('slug');
    try {
      Spinner.show();
      const res = await apiFetch('/blogs/' + encodeURIComponent(slug), { method: 'GET' });
      const blog = res.data || {};

      // related (initial)
      renderCardsIntoRelated(blog.related || []);

      // main
      const main = $('#blog-main');
      main.html(`
        <div class="d-flex gap-2 flex-wrap mb-3">
          <span class="chip"><i class="fa-solid fa-tag"></i> ${blog.category ? blog.category : ''}</span>
          <span class="chip"><i class="fa-regular fa-clock"></i> ${blog.publish_date || ''}</span>
        </div>
        <h1 class="fw-bold mb-3">${blog.title || ''}</h1>
        ${blog.image ? `<img class="rounded-4 mb-4 blog-detail-img" src="${blog.image}" alt="${blog.title || ''}">` : ''}
        <div class="blog-content">${blog.content || ''}</div>
      `);

      $('.blog-content img').addClass('img-fluid rounded-3');

      // populate categories for detail filter
      const cats = await apiFetch('/categories', { method: 'GET' });
      const categories = cats.data || [];
      const sel = $('#detail-search-category');
      sel.empty().append(`<option value="">All Categories</option>`);
      categories.forEach((c) => sel.append(`<option value="${c.slug}">${c.name}</option>`));

      // detail search button (AJAX -> related list)
      $('#detail-search-btn').off('click').on('click', async function () {
        try {
          Spinner.show();

          const cat = $('#detail-search-category').val() || '';
          const dateVal = $('#detail-search-date').val() || '';
          const s = $('#detail-search').val() || '';

          const params = { page: 1, per_page: 6 };
          if (cat) params.category = cat;
          if (dateVal) params.date = dateVal;
          if (s) params.search = s;

          const r = await apiFetch('/blogs', { method: 'GET', params });
          renderCardsIntoRelated(r.data || []);
        } catch (e) {
          console.error(e);
          renderCardsIntoRelated([]);
        } finally {
          Spinner.hide();
        }
      });
    } catch (err) {
      console.error(err);
      $('#blog-main').html(`<div class="alert alert-danger">Failed to load blog.</div>`);
    } finally {
      Spinner.hide();
    }
  }

  function getCKEditorImageAdapterUrl() {
    return getApiBaseUrl() + '/admin/ckeditor/upload';
  }

  async function loadAdminLogin() {
    const isLoginPage = $('#admin-login-form').length > 0;
    if (!isLoginPage) return;

    $('#admin-login-form').on('submit', async function (e) {
      e.preventDefault();

      const email = $('#admin-email').val();
      const password = $('#admin-password').val();
      const errEl = $('#admin-login-error').addClass('d-none').text('');

      try {
        Spinner.show();
        const res = await apiFetch('/admin/login', {
          method: 'POST',
          body: { email, password }
        });

        if (res && res.token) {
          setLocalToken(res.token);
          window.location.href = '/admin/blogs';
          return;
        }

        errEl.text('Login failed. Token not returned.').removeClass('d-none');
      } catch (err) {
        const msg = (err && err.data && (err.data.message || err.data.error)) ? (err.data.message || err.data.error) : 'Login failed';
        errEl.text(msg).removeClass('d-none');
      } finally {
        Spinner.hide();
      }
    });
  }

  async function loadAdminCommonAuth() {
    // ensure token exists on admin pages (except login)
    if (window.location.pathname === '/admin/login') return;
    const token = getLocalToken();
    if (!token) window.location.href = '/admin/login';
  }

  async function bootstrapPages() {
    // route dispatch
    if (window.__page === 'home') {
      await loadHome();
      return;
    }

    if ($('#blogs-listing-root').length > 0) {
      setupBlogListingUI();
      const url = new URL(window.location.href);
      const page = Number(url.searchParams.get('page') || 1);
      await loadBlogsListing({ page });
      return;
    }

    if ($('#blog-detail-root').length > 0) {
      await loadBlogDetail();
      return;
    }

    await loadAdminCommonAuth();
    await loadAdminLogin();
  }

  $(document).ready(function () {
    bootstrapPages().catch(console.error);
  });

  // Expose helpers (optional)
  window.__apiFetch = apiFetch;
  window.__adminToken = getLocalToken;
})();
