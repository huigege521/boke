<?php
$activePage = 'tags';
$pageTitle = '标签管理';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle')) ?>

<!-- 操作按钮 -->
<div class="mb-4">
    <a href="<?= base_url('admin/tags/create') ?>" class="btn btn-primary">创建标签</a>
</div>

<!-- 标签列表 -->
<div class="card">
    <div class="card-header">
        标签列表
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
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名称</th>
                            <th>别名</th>
                            <th>描述</th>
                            <th>文章数</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="tags-tbody">
                        <!-- 数据将通过AJAX加载 -->
                    </tbody>
                </table>
            </div>

            <!-- 空数据提示 -->
            <div id="empty-alert" class="alert alert-info" style="display: none;">
                暂无标签
            </div>

            <!-- 分页 -->
            <nav id="pagination-nav" aria-label="Page navigation" style="display: none;">
                <ul class="pagination justify-content-center" id="pagination-ul">
                </ul>
            </nav>
        </div>

        <!-- 热门标签 -->
        <div id="hot-tags-container" class="mt-4" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">热门标签</h5>
                </div>
                <div class="card-body">
                    <div id="hot-tags-list" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentRequest = null;  // 当前正在进行的请求
    let isLoading = false;      // 是否正在加载中

    document.addEventListener('DOMContentLoaded', function () {
        loadTags(1);
        loadHotTags();
        
        // 设置toastr提示位置为中间
        toastr.options.positionClass = 'toast-center-center';
    });

    function loadTags(page = 1) {
        // 如果正在加载中，取消之前的请求
        if (isLoading) {
            if (currentRequest) {
                currentRequest.abort();
            }
        }

        currentPage = page;
        showLoading();
        hideError();

        const url = `<?= base_url('api/tags') ?>?page=${page}`;

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
                        renderTags(result.data.list);
                        renderPagination(result.data.pagination);
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

    function renderTags(tags) {
        const tbody = document.getElementById('tags-tbody');
        const emptyAlert = document.getElementById('empty-alert');

        if (!tags || tags.length === 0) {
            tbody.innerHTML = '';
            emptyAlert.style.display = 'block';
            return;
        }

        emptyAlert.style.display = 'none';

        tbody.innerHTML = tags.map(tag => `
        <tr>
            <td>${tag.id}</td>
            <td>${escapeHtml(tag.name)}</td>
            <td>${escapeHtml(tag.slug)}</td>
            <td>${escapeHtml(tag.description) || '-'}</td>
            <td>${tag.posts_count}</td>
            <td>${tag.created_at}</td>
            <td>
                <a href="${tag.edit_url}" class="btn btn-sm btn-primary">编辑</a>
                <button type="button" class="btn btn-sm btn-danger ml-2" onclick="deleteTag(${tag.id})">删除</button>
            </td>
        </tr>
    `).join('');
    }

    function renderPagination(pagination) {
        const nav = document.getElementById('pagination-nav');
        const ul = document.getElementById('pagination-ul');

        if (pagination.total_pages <= 1) {
            nav.style.display = 'none';
            return;
        }

        nav.style.display = 'block';

        let html = '';

        html += `
        <li class="page-item ${pagination.page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page > 1 ? `loadTags(${pagination.page - 1})` : ''}">上一页</a>
        </li>
    `;

        for (let i = 1; i <= pagination.total_pages; i++) {
            html += `
            <li class="page-item ${i === pagination.page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadTags(${i})">${i}</a>
            </li>
        `;
        }

        html += `
        <li class="page-item ${pagination.page >= pagination.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page < pagination.total_pages ? `loadTags(${pagination.page + 1})` : ''}">下一页</a>
        </li>
    `;

        ul.innerHTML = html;
    }

    function deleteTag(id) {
        if (!confirm('确定要删除这个标签吗？')) {
            return;
        }

        const url = '<?= base_url('api/tags') ?>/' + id;

        showLoading();

        fetch(url, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('网络请求失败');
                }
                return response.json();
            })
            .then(result => {
                hideLoading();
                if (result.success) {
                    toastr.success('删除成功');
                    loadTags(currentPage);
                } else {
                    toastr.error(result.message || '删除失败');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('删除失败:', error);
                toastr.error('删除失败，请稍后重试');
            });
    }

    function showLoading() {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('data-container').style.opacity = '0.5';
    }

    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('data-container').style.opacity = '1';
    }

    function showError(message) {
        const alert = document.getElementById('error-alert');
        document.getElementById('error-message').textContent = message;
        alert.style.display = 'block';
    }

    function hideError() {
        document.getElementById('error-alert').style.display = 'none';
    }

    function retryLoad() {
        loadTags(currentPage);
    }

    function loadHotTags() {
        fetch('<?= base_url('api/tags/hot') ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            const container = document.getElementById('hot-tags-container');
            const list = document.getElementById('hot-tags-list');
            
            if (result.success && result.data && result.data.length > 0) {
                container.style.display = 'block';
                list.innerHTML = result.data.map(tag => `
                    <span class="badge bg-secondary">
                        ${escapeHtml(tag.name)} (${tag.posts_count})
                    </span>
                `).join('');
            } else {
                container.style.display = 'none';
            }
        })
        .catch(() => {
            document.getElementById('hot-tags-container').style.display = 'none';
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?= view('admin/layouts/footer') ?>