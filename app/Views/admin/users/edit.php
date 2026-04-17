<?php
$activePage = 'users';
$pageTitle = '编辑用户';
$styles = '<style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-actions {
            margin-top: 1.5rem;
        }

        .form-actions button {
            margin-right: 10px;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 编辑用户表单 -->
<div class="card">
    <div class="card-header">
        编辑用户
    </div>
    <div class="card-body">
        <form action="/admin/users/<?= $user['id'] ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">

            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" value="<?= old('username', $user['username']) ?>">
                <?php if (isset($errors['username'])): ?>
                    <div class="error-message"><?= $errors['username'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" value="<?= old('email', $user['email']) ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">密码（留空表示不修改）</label>
                <input type="password" id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">确认密码</label>
                <input type="password" id="password_confirm" name="password_confirm">
                <?php if (isset($errors['password_confirm'])): ?>
                    <div class="error-message"><?= $errors['password_confirm'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="role">角色</label>
                <select id="role" name="role">
                    <option value="user" <?= old('role', $user['role']) == 'user' ? 'selected' : '' ?>>用户</option>
                    <option value="editor" <?= old('role', $user['role']) == 'editor' ? 'selected' : '' ?>>编辑</option>
                    <option value="admin" <?= old('role', $user['role']) == 'admin' ? 'selected' : '' ?>>管理员</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">更新</button>
                <a href="/admin/users" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<?= view('admin/layouts/footer') ?>