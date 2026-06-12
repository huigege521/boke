<?php
$activePage = 'links';
$pageTitle = '编辑链接';
$styles = '<style>.form-group{margin-bottom:1rem}.form-group label{display:block;margin-bottom:0.5rem;font-weight:500}.form-group input,.form-group textarea{width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px}.form-actions{margin-top:1.5rem}.error-message{color:#dc3545;font-size:0.875rem;margin-top:0.25rem}.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">编辑链接</h5>
        <a href="<?= base_url('admin/links') ?>" class="btn btn-outline-secondary btn-sm"><i
                class="fas fa-arrow-left"></i> 返回</a>
    </div>
    <div class="card-body">
        <form id="linkForm">
            <?= csrf_field() ?>
            <input type="hidden" id="link_id" value="<?= $linkId ?? '' ?>">
            <div class="form-group">
                <label for="name">链接名称 <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="url">链接地址 <span class="text-danger">*</span></label>
                <input type="url" id="url" name="url" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="logo">Logo URL</label>
                <input type="text" id="logo" name="logo" class="form-control">
            </div>
            <div class="form-group">
                <label for="order">排序</label>
                <input type="number" id="order" name="order" class="form-control">
            </div>
            <div class="form-group">
                <label for="status">状态</label>
                <select id="status" name="status" class="form-control">
                    <option value="active">活跃</option>
                    <option value="inactive">禁用</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                <button type="button" class="btn btn-outline-danger" onclick="deleteLink()"><i class="fas fa-trash"></i>
                    删除</button>
                <a href="<?= base_url('admin/links') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    let linkId = '<?= $linkId ?? '' ?>';
    document.addEventListener('DOMContentLoaded', function () {
        if (!linkId) { toastr.error('链接ID无效'); setTimeout(() => { window.location.href = '<?= base_url('admin/links') ?>'; }, 1500); return; }
        loadLinkData();
    });

    function loadLinkData() {
        showLoading();
        fetch('<?= base_url('api/links/') ?>' + linkId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(result => {
                hideLoading();
                if (result.success) {
                    const link = result.data;
                    document.getElementById('name').value = link.name || '';
                    document.getElementById('url').value = link.url || '';
                    document.getElementById('description').value = link.description || '';
                    document.getElementById('logo').value = link.logo || '';
                    document.getElementById('order').value = link.order || 0;
                    document.getElementById('status').value = link.status || 'active';
                } else { toastr.error(result.message || '加载失败'); }
            }).catch(() => { hideLoading(); toastr.error('加载数据失败'); });
    }

    document.getElementById('linkForm').addEventListener('submit', function (e) {
        e.preventDefault();
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', document.getElementById('name').value);
        formData.append('url', document.getElementById('url').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('logo', document.getElementById('logo').value);
        formData.append('order', document.getElementById('order').value);
        formData.append('status', document.getElementById('status').value);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('api/links/') ?>' + linkId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) { toastr.success('链接更新成功'); loadLinkData(); }
            else { toastr.error(result.message || '更新失败'); }
        }).catch(() => { hideLoading(); toastr.error('提交失败'); });
    });

    function deleteLink() {
        if (!confirm('确定要删除这个链接吗？')) return;
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch('<?= base_url('api/links/') ?>' + linkId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) { toastr.success('链接删除成功'); setTimeout(() => { window.location.href = '<?= base_url('admin/links') ?>'; }, 1000); }
            else { toastr.error(result.message || '删除失败'); }
        }).catch(() => { hideLoading(); toastr.error('删除失败'); });
    }

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>