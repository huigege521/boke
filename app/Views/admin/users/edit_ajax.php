<?php
$activePage = 'users';
$pageTitle = '编辑用户';
$styles = '<style>.form-group{margin-bottom:1rem}.form-group label{display:block;margin-bottom:0.5rem;font-weight:500}.form-group input,.form-group select{width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px}.form-actions{margin-top:1.5rem}.error-message{color:#dc3545;font-size:0.875rem;margin-top:0.25rem}.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">编辑用户</h5>
        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm"><i
                class="fas fa-arrow-left"></i> 返回</a>
    </div>
    <div class="card-body">
        <form id="userForm">
            <?= csrf_field() ?>
            <input type="hidden" id="user_id" value="<?= $userId ?? '' ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">用户名 <span class="text-danger">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">真实姓名</label>
                        <input type="text" id="name" name="name" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">邮箱 <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required>
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
                        <label for="password">新密码</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="留空则不修改">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirm">确认密码</label>
                        <input type="password" id="password_confirm" name="password_confirm">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>用户信息</label>
                <ul class="list-unstyled text-muted small">
                    <li>注册时间: <span id="created_at">-</span></li>
                    <li>最后登录: <span id="last_login">-</span></li>
                </ul>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                <button type="button" class="btn btn-outline-danger" onclick="deleteUser()"><i class="fas fa-trash"></i>
                    删除</button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    let userId = '<?= $userId ?? '' ?>';
    document.addEventListener('DOMContentLoaded', function () {
        if (!userId) { toastr.error('用户ID无效'); setTimeout(() => { window.location.href = '<?= base_url('admin/users') ?>'; }, 1500); return; }
        loadUserData();
    });

    function loadUserData() {
        showLoading();
        fetch('<?= base_url('api/users/') ?>' + userId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(result => {
                hideLoading();
                if (result.success) {
                    const user = result.data;
                    document.getElementById('username').value = user.username || '';
                    document.getElementById('name').value = user.name || '';
                    document.getElementById('email').value = user.email || '';
                    document.getElementById('role').value = user.role || 'user';
                    document.getElementById('created_at').textContent = user.created_at || '-';
                    document.getElementById('last_login').textContent = user.last_login || '-';
                } else { toastr.error(result.message || '加载失败'); }
            }).catch(() => { hideLoading(); toastr.error('加载数据失败'); });
    }

    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();
        showLoading();
        const formData = new FormData();
        formData.append('username', document.getElementById('username').value);
        formData.append('name', document.getElementById('name').value);
        formData.append('_method', 'PUT');
        formData.append('email', document.getElementById('email').value);
        formData.append('role', document.getElementById('role').value);
        if (document.getElementById('password').value) {
            formData.append('password', document.getElementById('password').value);
            formData.append('password_confirm', document.getElementById('password_confirm').value);
        }
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('api/users/') ?>' + userId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) { toastr.success('用户更新成功'); loadUserData(); }
            else { toastr.error(result.message || '更新失败'); }
        }).catch(() => { hideLoading(); toastr.error('提交失败'); });
    });

    function deleteUser() {
        if (!confirm('确定要删除这个用户吗？')) return;
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fetch('<?= base_url('api/users/') ?>' + userId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) { toastr.success('用户删除成功'); setTimeout(() => { window.location.href = '<?= base_url('admin/users') ?>'; }, 1000); }
            else { toastr.error(result.message || '删除失败'); }
        }).catch(() => { hideLoading(); toastr.error('删除失败'); });
    }

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>