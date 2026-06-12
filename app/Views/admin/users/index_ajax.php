<?php
$activePage = 'users';
$pageTitle = '用户管理';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle')) ?>

<!-- 操作按钮 -->
<div class="mb-4">
    <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary">创建用户</a>
</div>

<!-- 用户列表 -->
<div class="card">
    <div class="card-header">
        用户列表
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
                            <th>用户名</th>
                            <th>邮箱</th>
                            <th>昵称</th>
                            <th>角色</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <!-- 数据将通过AJAX加载 -->
                    </tbody>
                </table>
            </div>

            <!-- 空数据提示 -->
            <div id="empty-alert" class="alert alert-info" style="display: none;">
                暂无用户
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

document.addEventListener('DOMContentLoaded', function() {
    loadUsers(1);
});

function loadUsers(page = 1) {
    // 如果正在加载中，取消之前的请求
    if (isLoading) {
        if (currentRequest) {
            currentRequest.abort();
        }
    }
    
    currentPage = page;
    showLoading();
    hideError();

    const url = `<?= base_url('api/users') ?>?page=${page}`;

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
                    renderUsers(result.data.list);
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

function renderUsers(users) {
    const tbody = document.getElementById('users-tbody');
    const emptyAlert = document.getElementById('empty-alert');

    if (!users || users.length === 0) {
        tbody.innerHTML = '';
        emptyAlert.style.display = 'block';
        return;
    }

    emptyAlert.style.display = 'none';

    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(user.name) || '-'}</td>
            <td>
                <span class="badge bg-${getRoleBadgeColor(user.role)}">
                    ${escapeHtml(user.role_text || '普通用户')}
                </span>
            </td>
            <td>
                <span class="badge bg-${user.status === 'active' ? 'success' : 'danger'}">
                    ${user.status === 'active' ? '活跃' : '禁用'}
                </span>
            </td>
            <td>${user.created_at}</td>
            <td>
                <a href="${user.edit_url}" class="btn btn-sm btn-primary">编辑</a>
                <button type="button" class="btn btn-sm btn-danger ml-2" onclick="deleteUser(${user.id})">删除</button>
            </td>
        </tr>
    `).join('');
}

function getRoleBadgeColor(role) {
    switch (role) {
        case 'admin':
            return 'danger'; // 红色
        case 'editor':
            return 'warning'; // 黄色/橙色
        default:
            return 'primary'; // 蓝色
    }
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
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page > 1 ? `loadUsers(${pagination.page - 1})` : ''}">上一页</a>
        </li>
    `;

    for (let i = 1; i <= pagination.total_pages; i++) {
        html += `
            <li class="page-item ${i === pagination.page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadUsers(${i})">${i}</a>
            </li>
        `;
    }

    html += `
        <li class="page-item ${pagination.page >= pagination.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page < pagination.total_pages ? `loadUsers(${pagination.page + 1})` : ''}">下一页</a>
        </li>
    `;

    ul.innerHTML = html;
}

function deleteUser(id) {
    if (!confirm('确定要删除这个用户吗？')) {
        return;
    }

    const url = '<?= base_url('api/users') ?>/' + id;

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
            loadUsers(currentPage);
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
    loadUsers(currentPage);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?= view('admin/layouts/footer') ?>