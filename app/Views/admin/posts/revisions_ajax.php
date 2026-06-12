<?php
/**
 * 文章修订历史 AJAX视图
 */
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">修订历史</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">首页</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/posts') ?>">文章管理</a></li>
                    <li class="breadcrumb-item active">修订历史</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content" id="revisionsContent">
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <p>加载中...</p>
    </div>
    <div id="revisionsData" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="postTitle"></h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover" id="revisionsTable">
                    <thead>
                        <tr>
                            <th>版本号</th>
                            <th>修改时间</th>
                            <th>修改内容</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="revisionsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
var postId = <?= $postId ?>;

function loadRevisions() {
    showLoading();
    fetch('<?= base_url('api/posts/revisions/') ?>' + postId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        }
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            document.getElementById('postTitle').textContent = '修订历史 - ' + result.data.post.title;
            renderRevisions(result.data.revisions);
            document.getElementById('revisionsData').style.display = 'block';
        } else {
            toastr.error(result.message || '加载失败');
            setTimeout(() => {
                window.location.href = '<?= base_url('admin/posts') ?>';
            }, 1500);
        }
    })
    .catch(error => {
        hideLoading();
        toastr.error('加载数据失败');
    });
}

function renderRevisions(revisions) {
    var tbody = document.getElementById('revisionsBody');
    tbody.innerHTML = '';
    
    revisions.forEach(function(revision) {
        var row = document.createElement('tr');
        row.innerHTML = `
            <td>${revision.version}</td>
            <td>${revision.created_at}</td>
            <td>${revision.summary || '未知修改'}</td>
            <td>
                <button class="btn btn-info btn-sm" onclick="viewRevision(${postId}, ${revision.version})">查看</button>
                <button class="btn btn-warning btn-sm" onclick="restoreRevision(${postId}, ${revision.version})">恢复</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function viewRevision(postId, version) {
    showLoading();
    fetch('<?= base_url('api/posts/revisions/') ?>' + postId + '/' + version, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        }
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            var modalContent = `
                <div class="form-group">
                    <label>标题:</label>
                    <input type="text" class="form-control" value="${result.data.title || ''}" readonly>
                </div>
                <div class="form-group">
                    <label>内容:</label>
                    <textarea class="form-control" rows="10" readonly>${result.data.content || ''}</textarea>
                </div>
            `;
            showModal('版本 ' + version + ' 详情', modalContent);
        } else {
            toastr.error(result.message || '查看失败');
        }
    })
    .catch(error => {
        hideLoading();
        toastr.error('操作失败');
    });
}

function restoreRevision(postId, version) {
    if (!confirm('确定要恢复到版本 ' + version + ' 吗？')) {
        return;
    }
    
    showLoading();
    fetch('<?= base_url('api/posts/revisions/') ?>' + postId + '/' + version + '/restore', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            toastr.success('恢复成功');
            loadRevisions();
        } else {
            toastr.error(result.message || '恢复失败');
        }
    })
    .catch(error => {
        hideLoading();
        toastr.error('操作失败');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadRevisions();
});
</script>
<?= $this->endSection() ?>