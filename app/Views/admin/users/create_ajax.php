<?php
$activePage = 'users';
$pageTitle = '创建用户';
$styles = '<style>.form-group{margin-bottom:1rem}.form-group label{display:block;margin-bottom:0.5rem;font-weight:500}.form-group input,.form-group select{width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px}.form-actions{margin-top:1.5rem}.error-message{color:#dc3545;font-size:0.875rem;margin-top:0.25rem}.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">创建新用户</h5>
    </div>
    <div class="card-body">
        <form id="userForm">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">用户名 <span class="text-danger">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" required>
                        <div class="error-message" id="username-error"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">真实姓名 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required>
                        <div class="error-message" id="name-error"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">邮箱 <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required>
                        <div class="error-message" id="email-error"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role">角色</label>
                        <select id="role" name="role" class="form-control">
                            <option value="user">普通用户</option>
                            <option value="editor">编辑</option>
                            <option value="admin">管理员</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">密码 <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirm">确认密码</label>
                        <input type="password" id="password_confirm" name="password_confirm">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();
        showLoading();
        const formData = new FormData();
        formData.append('username', document.getElementById('username').value);
        formData.append('name', document.getElementById('name').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('password', document.getElementById('password').value);
        formData.append('password_confirm', document.getElementById('password_confirm').value);
        formData.append('role', document.getElementById('role').value);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('api/users') ?>', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success('用户创建成功');
                setTimeout(() => { window.location.href = '<?= base_url('admin/users') ?>'; }, 1000);
            } else {
                toastr.error(result.message || '创建失败');
                if (result.errors) {
                    for (const [field, msg] of Object.entries(result.errors)) {
                        const el = document.getElementById(field + '-error');
                        if (el) el.textContent = msg;
                    }
                }
            }
        }).catch(() => { hideLoading(); toastr.error('提交失败'); });
    });

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>