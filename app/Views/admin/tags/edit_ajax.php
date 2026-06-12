<?php
$activePage = 'tags';
$pageTitle = '编辑标签';
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">编辑标签</h5>
        <a href="<?= base_url('admin/tags') ?>" class="btn btn-outline-secondary btn-sm"><i
                class="fas fa-arrow-left"></i> 返回</a>
    </div>
    <div class="card-body">
        <form id="tagForm">
            <?= csrf_field() ?>
            <input type="hidden" id="tag_id" value="<?= $tagId ?? '' ?>">

            <div class="form-group">
                <label for="name">标签名称 <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required>
                <div class="error-message" id="name-error"></div>
            </div>

            <div class="form-group">
                <label for="slug">别名</label>
                <input type="text" id="slug" name="slug" class="form-control">
            </div>

            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>标签信息</label>
                <ul class="list-unstyled text-muted small">
                    <li>文章数: <span id="posts_count">0</span></li>
                    <li>创建时间: <span id="created_at">-</span></li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                <button type="button" class="btn btn-outline-danger" onclick="deleteTag()"><i class="fas fa-trash"></i>
                    删除</button>
                <a href="<?= base_url('admin/tags') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    let tagId = '<?= $tagId ?? '' ?>';

    document.addEventListener('DOMContentLoaded', function () {
        if (!tagId) {
            toastr.error('标签ID无效');
            setTimeout(() => { window.location.href = '<?= base_url('admin/tags') ?>'; }, 1500);
            return;
        }
        loadTagData();
    });

    function loadTagData() {
        showLoading();
        fetch('<?= base_url('api/tags/') ?>' + tagId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                if (result.success) {
                    const tag = result.data;
                    document.getElementById('name').value = tag.name || '';
                    document.getElementById('slug').value = tag.slug || '';
                    document.getElementById('description').value = tag.description || '';
                    document.getElementById('posts_count').textContent = tag.posts_count || 0;
                    document.getElementById('created_at').textContent = tag.created_at || '-';
                } else {
                    toastr.error(result.message || '加载失败');
                    setTimeout(() => { window.location.href = '<?= base_url('admin/tags') ?>'; }, 1500);
                }
            }).catch(error => {
                hideLoading();
                toastr.error('加载数据失败');
            });
    }

    document.getElementById('tagForm').addEventListener('submit', function (e) {
        e.preventDefault();
        submitTag();
    });

    function submitTag() {
        clearErrors();
        showLoading();

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', document.getElementById('name').value);
        formData.append('slug', document.getElementById('slug').value);
        formData.append('description', document.getElementById('description').value);

        fetch('<?= base_url('api/tags/') ?>' + tagId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                if (result.success) {
                    toastr.success('标签更新成功');
                    loadTagData();
                } else {
                    toastr.error(result.message || '更新失败');
                    if (result.errors) showErrors(result.errors);
                }
            }).catch(error => {
                hideLoading();
                toastr.error('提交失败，请稍后重试');
            });
    }

    function deleteTag() {
        if (!confirm('确定要删除这个标签吗？')) return;
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch('<?= base_url('api/tags/') ?>' + tagId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                if (result.success) {
                    toastr.success('标签删除成功');
                    setTimeout(() => { window.location.href = '<?= base_url('admin/tags') ?>'; }, 1000);
                } else {
                    toastr.error(result.message || '删除失败');
                }
            }).catch(error => {
                hideLoading();
                toastr.error('删除失败，请稍后重试');
            });
    }

    function showErrors(errors) {
        for (const [field, message] of Object.entries(errors)) {
            const errorEl = document.getElementById(field + '-error');
            if (errorEl) errorEl.textContent = message;
        }
    }

    function clearErrors() { document.querySelectorAll('.error-message').forEach(el => el.textContent = ''); }
    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>