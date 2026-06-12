<?php
$activePage = 'contacts';
$pageTitle = '联系消息管理';

$styles = '';
$scripts = '';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 联系消息列表 -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">联系消息列表</h5>
        <div>
            <button type="button" class="btn btn-sm btn-primary" onclick="loadContacts('all')">全部</button>
            <button type="button" class="btn btn-sm btn-warning" onclick="loadContacts('pending')">未处理</button>
            <button type="button" class="btn btn-sm btn-success" onclick="loadContacts('processed')">已处理</button>
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
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>姓名</th>
                            <th>邮箱</th>
                            <th>主题</th>
                            <th>状态</th>
                            <th>提交时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="contacts-tbody">
                        <!-- 数据将通过AJAX加载 -->
                    </tbody>
                </table>
            </div>

            <!-- 空数据提示 -->
            <div id="empty-alert" class="alert alert-info" style="display: none;">
                暂无联系消息
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
    let currentStatus = 'all';
    let currentPage = 1;
    let currentRequest = null;  // 当前正在进行的请求
    let isLoading = false;      // 是否正在加载中

    document.addEventListener('DOMContentLoaded', function () {
        loadContacts('all');
    });

    function loadContacts(status, page = 1) {
        // 如果正在加载中，取消之前的请求
        if (isLoading) {
            if (currentRequest) {
                currentRequest.abort();
            }
        }

        currentStatus = status;
        currentPage = page;

        showLoading();
        hideError();

        const url = `<?= base_url('api/contacts') ?>?status=${status}&page=${page}`;

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
                        renderContacts(result.data.list);
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

    function renderContacts(contacts) {
        const tbody = document.getElementById('contacts-tbody');
        const emptyAlert = document.getElementById('empty-alert');

        // 检查元素是否存在
        if (!tbody) {
            return;
        }

        if (!contacts || contacts.length === 0) {
            tbody.innerHTML = '';
            if (emptyAlert) emptyAlert.style.display = 'block';
            return;
        }

        if (emptyAlert) emptyAlert.style.display = 'none';

        tbody.innerHTML = contacts.map(contact => `
        <tr>
            <td>${contact.id}</td>
            <td>${escapeHtml(contact.name)}</td>
            <td>${escapeHtml(contact.email)}</td>
            <td>${escapeHtml(contact.subject)}</td>
            <td>
                <span class="badge bg-${contact.status_class}">${contact.status_text}</span>
            </td>
            <td>${contact.created_at}</td>
            <td>
                <a href="${contact.show_url}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> 查看
                </a>
                ${contact.status === 'pending' ? `
                    <button type="button" class="btn btn-sm btn-success" onclick="processContact(${contact.id})">
                        <i class="fas fa-check"></i> 处理
                    </button>
                ` : ''}
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteContact(${contact.id})">
                    <i class="fas fa-trash"></i> 删除
                </button>
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
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page > 1 ? `loadContacts('${currentStatus}', ${pagination.page - 1})` : ''}">上一页</a>
        </li>
    `;

        for (let i = 1; i <= pagination.total_pages; i++) {
            html += `
            <li class="page-item ${i === pagination.page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadContacts('${currentStatus}', ${i})">${i}</a>
            </li>
        `;
        }

        html += `
        <li class="page-item ${pagination.page >= pagination.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${pagination.page < pagination.total_pages ? `loadContacts('${currentStatus}', ${pagination.page + 1})` : ''}">下一页</a>
        </li>
    `;

        ul.innerHTML = html;
    }

    function deleteContact(id) {
        if (!confirm('确定要删除这条联系消息吗？')) {
            return;
        }

        const url = '<?= base_url('api/contacts') ?>/' + id;

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
                    loadContacts(currentStatus, currentPage);
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

    function processContact(id) {
        const url = '<?= base_url('api/contacts/process') ?>/' + id;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    toastr.success('处理成功');
                    loadContacts(currentStatus, currentPage);
                } else {
                    toastr.error(result.message || '处理失败');
                }
            })
            .catch(error => {
                console.error('处理失败:', error);
                toastr.error('处理失败，请稍后重试');
            });
    }

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
        loadContacts(currentStatus, currentPage);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?= view('admin/layouts/footer') ?>