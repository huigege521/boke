<?php
$activePage = 'categories';
$pageTitle = '分类管理';
$styles = '<style>
        .category-level {
            display: inline-block;
            width: 20px;
        }
    </style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 操作按钮 -->
<div class="mb-4">
    <a href="<?= base_url('admin/categories/create') ?>" class="btn btn-primary">创建分类</a>
</div>

<!-- 分类列表 -->
<div class="card">
    <div class="card-header">
        分类列表
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
                            <th>图标</th>
                            <th>名称</th>
                            <th>别名</th>
                            <th>父分类</th>
                            <th>文章数</th>
                            <th>排序</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="categories-tbody">
                        <!-- 数据将通过AJAX加载 -->
                    </tbody>
                </table>
            </div>

            <!-- 空数据提示 -->
            <div id="empty-alert" class="alert alert-info" style="display: none;">
                暂无分类
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
    let currentRequest = null;  // 当前正在进行的请求
    let isLoading = false;      // 是否正在加载中

    document.addEventListener('DOMContentLoaded', function () {
        loadCategories(1);
    });

    function loadCategories(page = 1) {
        // 如果正在加载中，取消之前的请求
        if (isLoading) {
            if (currentRequest) {
                currentRequest.abort();
            }
        }
        
        currentPage = page;
        showLoading();
        hideError();

        const url = `<?= base_url('api/categories') ?>?page=${page}`;

        // 创建新请求
        currentRequest = new XMLHttpRequest();
        currentRequest.open('GET', url, true);
        currentRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        currentRequest.onload = function() {
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
                        renderCategories(result.data.list);
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
        
        currentRequest.onerror = function() {
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

    function renderCategories(categories) {
        const tbody = document.getElementById('categories-tbody');
        const emptyAlert = document.getElementById('empty-alert');

        if (!categories || categories.length === 0) {
            tbody.innerHTML = '';
            emptyAlert.style.display = 'block';
            return;
        }

        emptyAlert.style.display = 'none';

        tbody.innerHTML = categories.map(category => `
        <tr>
            <td>${category.id}</td>
            <td>
                ${category.icon ? `<i class="${category.icon}" style="font-size: 1.2rem;"></i>` :
                '<i class="fas fa-folder" style="font-size: 1.2rem; color: #6c757d;"></i>'}
            </td>
            <td>
                <span class="category-level">${'—'.repeat(category.level || 0)}</span>
                ${escapeHtml(category.name)}
            </td>
            <td>${escapeHtml(category.slug)}</td>
            <td>${escapeHtml(category.parent_name || '无')}</td>
            <td>${category.posts_count}</td>
            <td>${category.order}</td>
            <td>${category.created_at}</td>
            <td>
                <a href="${category.edit_url}" class="btn btn-sm btn-primary">编辑</a>
                <button type="button" class="btn btn-sm btn-danger ml-2" onclick="deleteCategory(${category.id})">删除</button>
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
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page > 1 ? `loadCategories(${pagination.page - 1})` : ''}">上一页</a>
        </li>
    `;

        for (let i = 1; i <= pagination.total_pages; i++) {
            html += `
            <li class="page-item ${i === pagination.page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadCategories(${i})">${i}</a>
            </li>
        `;
        }

        html += `
        <li class="page-item ${pagination.page >= pagination.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page < pagination.total_pages ? `loadCategories(${pagination.page + 1})` : ''}">下一页</a>
        </li>
    `;

        ul.innerHTML = html;
    }

    function deleteCategory(id) {
        if (!confirm('确定要删除这个分类吗？')) {
            return;
        }

        const url = '<?= base_url('api/categories') ?>/' + id;

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
                    loadCategories(currentPage);
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
        loadCategories(currentPage);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?= view('admin/layouts/footer') ?>