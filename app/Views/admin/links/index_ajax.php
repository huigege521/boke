<?php
$activePage = 'links';
$pageTitle = '友情链接管理';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle')) ?>

<!-- 操作按钮 -->
<div class="mb-4">
    <a href="<?= base_url('admin/links/create') ?>" class="btn btn-primary">添加友情链接</a>
</div>

<!-- 友情链接列表 -->
<div class="card">
    <div class="card-header">
        友情链接列表
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
                            <th>URL</th>
                            <th>描述</th>
                            <th>Logo</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="links-tbody">
                        <!-- 数据将通过AJAX加载 -->
                    </tbody>
                </table>
            </div>

            <!-- 空数据提示 -->
            <div id="empty-alert" class="alert alert-info" style="display: none;">
                暂无友情链接
            </div>
        </div>
    </div>
</div>

<script>
    let currentRequest = null;  // 当前正在进行的请求
    let isLoading = false;      // 是否正在加载中

    document.addEventListener('DOMContentLoaded', function () {
        loadLinks();
    });

    function loadLinks() {
        // 如果正在加载中，取消之前的请求
        if (isLoading) {
            if (currentRequest) {
                currentRequest.abort();
            }
        }

        showLoading();
        hideError();

        const url = '<?= base_url('api/links') ?>';

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
                        renderLinks(result.data.list);
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

    function renderLinks(links) {
        const tbody = document.getElementById('links-tbody');
        const emptyAlert = document.getElementById('empty-alert');

        if (!links || links.length === 0) {
            tbody.innerHTML = '';
            emptyAlert.style.display = 'block';
            return;
        }

        emptyAlert.style.display = 'none';

        tbody.innerHTML = links.map(link => `
        <tr>
            <td>${link.id}</td>
            <td>${escapeHtml(link.name)}</td>
            <td><a href="${escapeHtml(link.url)}" target="_blank">${escapeHtml(link.url)}</a></td>
            <td>${escapeHtml(link.description) || '-'}</td>
            <td>${link.logo ? `<img src="${escapeHtml(link.logo)}" width="50" height="50" class="img-thumbnail" />` : '-'}</td>
            <td>${link.status === 'active' ? '<span class="badge bg-success">活跃</span>' : '<span class="badge bg-danger">禁用</span>'}</td>
            <td>${link.created_at}</td>
            <td>
                <a href="${link.edit_url}" class="btn btn-sm btn-primary">编辑</a>
                <button type="button" class="btn btn-sm btn-danger ml-2" onclick="deleteLink(${link.id})">删除</button>
            </td>
        </tr>
    `).join('');
    }

    function deleteLink(id) {
        if (!confirm('确定要删除这个友情链接吗？')) {
            return;
        }

        showLoading();

        const url = '<?= base_url('api/links') ?>/' + id;

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
                    loadLinks();
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
        loadLinks();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?= view('admin/layouts/footer') ?>