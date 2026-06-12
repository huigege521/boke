<?php
$activePage = 'tags';
$pageTitle = '创建标签';
$styles = '<style>
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
    .form-group input[type="text"],
    .form-group textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .form-actions { margin-top: 1.5rem; }
    .error-message { color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; }
    .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: none; justify-content: center; align-items: center; }
</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">创建新标签</h5>
    </div>
    <div class="card-body">
        <form id="tagForm">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">标签名称 <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required>
                <div class="error-message" id="name-error"></div>
            </div>

            <div class="form-group">
                <label for="slug">别名</label>
                <input type="text" id="slug" name="slug" class="form-control" placeholder="留空则自动生成">
            </div>

            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                <a href="<?= base_url('admin/tags') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('name').addEventListener('blur', function () {
            const slug = document.getElementById('slug');
            if (!slug.value && this.value) {
                slug.value = this.value.toLowerCase().replace(/[^\w\u4e00-\u9fa5]+/g, '-').replace(/^-+|-+$/g, '');
            }
        });
    });

    document.getElementById('tagForm').addEventListener('submit', function (e) {
        e.preventDefault();
        submitTag();
    });

    function submitTag() {
        clearErrors();
        showLoading();

        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('slug', document.getElementById('slug').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('api/tags') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                if (result.success) {
                    toastr.success('标签创建成功');
                    setTimeout(() => { window.location.href = '<?= base_url('admin/tags') ?>'; }, 1000);
                } else {
                    toastr.error(result.message || '创建失败');
                    if (result.errors) showErrors(result.errors);
                }
            })
            .catch(error => {
                hideLoading();
                toastr.error('提交失败，请稍后重试');
            });
    }

    function showErrors(errors) {
        for (const [field, message] of Object.entries(errors)) {
            const errorEl = document.getElementById(field + '-error');
            if (errorEl) errorEl.textContent = message;
        }
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    }

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>