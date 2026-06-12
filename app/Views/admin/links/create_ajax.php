<?php
$activePage = 'links';
$pageTitle = '创建链接';
$styles = '<style>.form-group{margin-bottom:1rem}.form-group label{display:block;margin-bottom:0.5rem;font-weight:500}.form-group input,.form-group textarea,.form-group select{width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px}.form-actions{margin-top:1.5rem}.error-message{color:#dc3545;font-size:0.875rem;margin-top:0.25rem}.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">创建新链接</h5>
    </div>
    <div class="card-body">
        <form id="linkForm">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">链接名称 <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required>
                <div class="error-message" id="name-error"></div>
            </div>
            <div class="form-group">
                <label for="url">链接地址 <span class="text-danger">*</span></label>
                <input type="url" id="url" name="url" class="form-control" required placeholder="https://">
                <div class="error-message" id="url-error"></div>
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="logo">Logo URL</label>
                <input type="text" id="logo" name="logo" class="form-control" placeholder="https://">
            </div>
            <div class="form-group">
                <label for="order">排序</label>
                <input type="number" id="order" name="order" class="form-control" value="0">
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
                <a href="<?= base_url('admin/links') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('linkForm').addEventListener('submit', function (e) {
        e.preventDefault();
        showLoading();
        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('url', document.getElementById('url').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('logo', document.getElementById('logo').value);
        formData.append('order', document.getElementById('order').value);
        formData.append('status', document.getElementById('status').value);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('api/links') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success('链接创建成功');
                setTimeout(() => { window.location.href = '<?= base_url('admin/links') ?>'; }, 1000);
            } else {
                toastr.error(result.message || '创建失败');
            }
        }).catch(() => { hideLoading(); toastr.error('提交失败'); });
    });

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>