<?php
$activePage = 'posts';
$pageTitle = '文章管理';
$styles = '<style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .status-draft { background-color: #ffc107; color: #212529; }
        .status-published { background-color: #28a745; color: #fff; }
        .status-pending { background-color: #17a2b8; color: #fff; }
        .visibility-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .visibility-public { background-color: #6c757d; color: #fff; }
        .visibility-private { background-color: #dc3545; color: #fff; }
        .tag-badge {
            display: inline-block;
            padding: 2px 6px;
            background-color: #007bff;
            color: #fff;
            border-radius: 10px;
            font-size: 11px;
            margin-right: 3px;
            margin-bottom: 2px;
        }
        .filter-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .batch-actions { margin-bottom: 15px; }
    </style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 批量操作和搜索 -->
<div class="mb-4">
    <div class="row align-items-center">
        <div class="col-md-4">
            <form class="batch-actions d-flex align-items-center" id="batch-form">
                <?= csrf_field() ?>
                <div class="form-group mr-2 mb-0">
                    <select name="action" class="form-control" style="height: 40px; min-width: 150px;">
                        <option value="">批量操作</option>
                        <option value="publish">批量发布</option>
                        <option value="draft">设为草稿</option>
                        <option value="pending">设为待审核</option>
                        <option value="delete">批量删除</option>
                    </select>
                </div>
                <button type="button" class="btn btn-outline-primary mr-4" onclick="executeBatch()">执行</button>
                <a href="<?= base_url('admin/posts/create') ?>" class="btn btn-primary">创建文章</a>
            </form>
        </div>
        <div class="col-md-8">
            <div class="input-group" style="width: 100%; max-width: 600px;">
                <input type="text" id="search-input" class="form-control rounded-l-lg border-right-0"
                    placeholder="搜索文章标题、内容..." value="">
                <div class="input-group-append">
                    <button type="button" class="btn btn-primary rounded-r-lg border-left-0" onclick="searchPosts()">
                        <i class="fas fa-search mr-1"></i> 搜索
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="filter-form">
        <div class="d-flex align-items-end" id="filter-form">
            <div class="form-group mr-4">
                <label for="filter-category" class="form-label">分类</label>
                <select id="filter-category" class="form-control" style="height: 40px;">
                    <option value="">全部分类</option>
                </select>
            </div>
            <div class="form-group mr-4">
                <label for="filter-status" class="form-label">状态</label>
                <select id="filter-status" class="form-control" style="height: 40px;">
                    <option value="">全部状态</option>
                    <option value="published">已发布</option>
                    <option value="draft">草稿</option>
                    <option value="pending">待审核</option>
                </select>
            </div>
            <div class="form-group mr-4">
                <label for="filter-order" class="form-label">排序</label>
                <select id="filter-order" class="form-control" style="height: 40px;">
                    <option value="created_at">创建时间</option>
                    <option value="published_at">发布时间</option>
                    <option value="views">浏览量</option>
                    <option value="comments_count">评论数</option>
                </select>
            </div>
            <div class="form-group mr-4">
                <label for="filter-direction" class="form-label">排序方向</label>
                <select id="filter-direction" class="form-control" style="height: 40px;">
                    <option value="desc">降序</option>
                    <option value="asc">升序</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary mr-2" onclick="applyFilters()">应用筛选</button>
            <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">重置</button>
        </div>
    </div>
</div>

<!-- 文章列表 -->
<div class="card shadow">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">文章列表</h6>
            <span class="text-muted" id="total-count">共 0 篇文章</span>
        </div>
    </div>
    <div class="card-body">
        <!-- 加载状态 -->
        <div id="loading" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">加载中...</span>
            </div>
            <p class="mt-2 text-muted">数据加载中...</p>
        </div>

        <!-- 错误提示 -->
        <div id="error-alert" class="alert alert-danger" style="display: none;">
            <i class="fas fa-exclamation-circle"></i> <span id="error-message"></span>
            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="retryLoad()">重试</button>
        </div>

        <!-- 数据表格 -->
        <div id="data-container">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th style="width: 60px;">ID</th>
                            <th>标题</th>
                            <th style="width: 120px;">分类</th>
                            <th>标签</th>
                            <th style="width: 100px;">状态</th>
                            <th style="width: 100px;">可见性</th>
                            <th style="width: 80px;">浏览量</th>
                            <th style="width: 80px;">评论数</th>
                            <th style="width: 150px;">发布时间</th>
                            <th style="width: 120px;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="posts-tbody">
                        <!-- 数据将通过AJAX加载 -->
                    </tbody>
                </table>
            </div>

            <!-- 空数据提示 -->
            <div id="empty-alert" class="alert alert-info" style="display: none;">
                <p class="text-muted">暂无文章</p>
                <a href="<?= base_url('admin/posts/create') ?>" class="btn btn-primary mt-3">创建文章</a>
            </div>

            <!-- 分页 -->
            <nav id="pagination-nav" aria-label="Page navigation" style="display: none;">
                <ul class="pagination justify-content-center" id="pagination-ul">
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentFilters = {
        search: '',
        status: '',
        category: '',
        order_by: 'created_at',
        order_direction: 'desc'
    };
    let categories = [];
    let currentRequest = null;  // 当前正在进行的请求
    let isLoading = false;      // 是否正在加载中

    document.addEventListener('DOMContentLoaded', function () {
        loadPosts(1);
    });

    function buildQueryString() {
        let params = [];
        if (currentFilters.search) params.push(`search=${encodeURIComponent(currentFilters.search)}`);
        if (currentFilters.status) params.push(`status=${currentFilters.status}`);
        if (currentFilters.category) params.push(`category=${currentFilters.category}`);
        if (currentFilters.order_by) params.push(`order_by=${currentFilters.order_by}`);
        if (currentFilters.order_direction) params.push(`order_direction=${currentFilters.order_direction}`);
        return params.length ? '?' + params.join('&') : '';
    }

    function loadPosts(page = 1) {
        // 如果正在加载中，取消之前的请求
        if (isLoading) {
            if (currentRequest) {
                currentRequest.abort();
        }
    }

    currentPage = page;
    showLoading();
    hideError();

    const url = `<?= base_url('api/posts') ?>?page=${page}${buildQueryString()}`;

    // 创建新请求
    currentRequest = new XMLHttpRequest();
    currentRequest.open('GET', url, true);
    currentRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    currentRequest.onload = function () {
        isLoading = false;
        currentRequest = null;
        
        // 如果请求被取消，不做任何处理
        if (this.status === 0) {
            return;
        }

        if (this.status >= 200 && this.status < 400) {
            try {
                const result = JSON.parse(this.responseText);
                hideLoading();

                if (result.success) {
                    renderPosts(result.data.list);
                    renderPagination(result.data.pagination);
                    const totalCountEl = document.getElementById('total-count');
                    if (totalCountEl) {
                        totalCountEl.textContent = `共 ${result.data.pagination.total} 篇文章`;
                    }

                    if (result.data.filters && result.data.filters.categories) {
                        categories = result.data.filters.categories;
                        populateCategories(categories);
                    }
                } else {
                    showError(result.message || '数据加载失败');
                }
            } catch (e) {
                hideLoading();
                showError('数据解析失败');
            }
        } else {
            hideLoading();
            showError('请求失败');
        }
    };

    currentRequest.onerror = function () {
        isLoading = false;
        currentRequest = null;
        // 如果请求被取消，不显示错误
        if (this.status !== 0) {
            hideLoading();
            showError('网络请求失败，请检查网络连接');
        }
    };

    isLoading = true;
    currentRequest.send();
    }

    function populateCategories(cats) {
        const select = document.getElementById('filter-category');
        select.innerHTML = '<option value="">全部分类</option>';
        cats.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            select.appendChild(option);
        });
    }

    function renderPosts(posts) {
        const tbody = document.getElementById('posts-tbody');
        const emptyAlert = document.getElementById('empty-alert');
        
        // 检查元素是否存在
        if (!tbody) {
            return;
        }

        if (!posts || posts.length === 0) {
            tbody.innerHTML = '';
            if (emptyAlert) emptyAlert.style.display = 'block';
            return;
        }

        if (emptyAlert) emptyAlert.style.display = 'none';

        tbody.innerHTML = posts.map(post => `
        <tr>
            <td><input type="checkbox" name="selected_ids[]" value="${post.id}" class="form-check-input post-checkbox"></td>
            <td class="text-center font-medium">${post.id}</td>
            <td>${escapeHtml(post.title)}</td>
            <td>${escapeHtml(post.category_name || '未分类')}</td>
            <td>
                ${post.tags && post.tags.length > 0 ?
                post.tags.map(tag => `<span class="tag-badge">${escapeHtml(tag.name)}</span>`).join('') :
                '<span class="text-muted">无标签</span>'}
            </td>
            <td><span class="status-badge status-${post.status}">${post.status_text}</span></td>
            <td><span class="visibility-badge visibility-${post.visibility}">${post.visibility_text}</span></td>
            <td class="text-center">${post.views || 0}</td>
            <td class="text-center">${post.comments_count || 0}</td>
            <td>${post.published_at || '未发布'}</td>
            <td>
                <a href="${post.edit_url}" class="btn btn-sm btn-primary">编辑</a>
                <button type="button" class="btn btn-sm btn-danger ml-2" onclick="deletePost(${post.id})">删除</button>
            </td>
        </tr>
    `).join('');
    }

    function renderPagination(pagination) {
        const nav = document.getElementById('pagination-nav');
        const ul = document.getElementById('pagination-ul');
        
        // 检查元素是否存在
        if (!nav || !ul) {
            return;
        }

        if (pagination.total_pages <= 1) {
            nav.style.display = 'none';
            return;
        }

        nav.style.display = 'block';

        let html = '';

        html += `
        <li class="page-item ${pagination.page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page > 1 ? `loadPosts(${pagination.page - 1})` : ''}">上一页</a>
        </li>
    `;

        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.total_pages, startPage + 4);

        for (let i = startPage; i <= endPage; i++) {
            html += `
            <li class="page-item ${i === pagination.page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadPosts(${i})">${i}</a>
            </li>
        `;
        }

        html += `
        <li class="page-item ${pagination.page >= pagination.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page < pagination.total_pages ? `loadPosts(${pagination.page + 1})` : ''}">下一页</a>
        </li>
    `;

        ul.innerHTML = html;
    }

    function deletePost(id) {
        if (!confirm('确定要删除这篇文章吗？')) {
            return;
        }

        const url = '<?= base_url('api/posts') ?>/' + id;

        const formData = new FormData();
        formData.append('_method', 'DELETE');
        
        fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    toastr.success('删除成功');
                    loadPosts(currentPage);
                } else {
                    toastr.error(result.message || '删除失败');
                }
            })
            .catch(error => {
                console.error('删除失败:', error);
                toastr.error('删除失败，请稍后重试');
            });
    }

    function searchPosts() {
        currentFilters.search = document.getElementById('search-input').value;
        loadPosts(1);
    }

    function applyFilters() {
        currentFilters.status = document.getElementById('filter-status').value;
        currentFilters.category = document.getElementById('filter-category').value;
        currentFilters.order_by = document.getElementById('filter-order').value;
        currentFilters.order_direction = document.getElementById('filter-direction').value;
        loadPosts(1);
    }

    function resetFilters() {
        currentFilters = {
            search: '',
            status: '',
            category: '',
            order_by: 'created_at',
            order_direction: 'desc'
        };
        document.getElementById('search-input').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-category').value = '';
        document.getElementById('filter-order').value = 'created_at';
        document.getElementById('filter-direction').value = 'desc';
        loadPosts(1);
    }

    function executeBatch() {
        const action = document.querySelector('#batch-form select[name="action"]').value;
        const checkboxes = document.querySelectorAll('.post-checkbox:checked');

        if (!action) {
            toastr.error('请选择操作');
            return;
        }

        if (checkboxes.length === 0) {
            toastr.error('请选择要操作的文章');
            return;
        }

        if (action === 'delete' && !confirm('确定要删除选中的文章吗？')) {
            return;
        }

        const selectedIds = Array.from(checkboxes).map(cb => cb.value);

        const url = '<?= base_url('api/posts/batch') ?>';
        const formData = new FormData();
        formData.append('action', action);
        formData.append('ids', JSON.stringify(selectedIds));
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    toastr.success(result.message);
                    document.getElementById('select-all').checked = false;
                    loadPosts(currentPage);
                } else {
                    toastr.error(result.message || '操作失败');
                }
            })
            .catch(error => {
                console.error('操作失败:', error);
                toastr.error('操作失败，请稍后重试');
            });
    }

    document.getElementById('select-all').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.post-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    function showLoading() {
        const loadingEl = document.getElementById('loading');
        const containerEl = document.getElementById('data-container');
        if (loadingEl) loadingEl.style.display = 'block';
        if (containerEl) containerEl.style.opacity = '0.5';
    }

    function hideLoading() {
        const loadingEl = document.getElementById('loading');
        const containerEl = document.getElementById('data-container');
        if (loadingEl) loadingEl.style.display = 'none';
        if (containerEl) containerEl.style.opacity = '1';
    }

    function showError(message) {
        const alert = document.getElementById('error-alert');
        const errorMsg = document.getElementById('error-message');
        if (errorMsg) errorMsg.textContent = message;
        if (alert) alert.style.display = 'block';
    }

    function hideError() {
        const alert = document.getElementById('error-alert');
        if (alert) alert.style.display = 'none';
    }

    function retryLoad() {
        loadPosts(currentPage);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?= view('admin/layouts/footer') ?>