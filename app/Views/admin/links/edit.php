<?php
$activePage = 'links';
$pageTitle = '编辑友情链接';
$styles = '<style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group textarea {
            resize: vertical;
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
$scripts = '';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles', 'scripts')) ?>

<!-- 编辑友情链接表单 -->
<div class="card">
    <div class="card-header">
        编辑友情链接
    </div>
    <div class="card-body">
        <form action="/admin/links/update/<?= $link['id'] ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">链接名称</label>
                <input type="text" id="name" name="name" value="<?= old('name', $link['name']) ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <div class="error-message"><?= $errors['name'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="url">链接URL</label>
                <input type="url" id="url" name="url" value="<?= old('url', $link['url']) ?>" required>
                <?php if (isset($errors['url'])): ?>
                    <div class="error-message"><?= $errors['url'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" rows="3"><?= old('description', $link['description']) ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="error-message"><?= $errors['description'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="logo">Logo URL</label>
                <input type="text" id="logo" name="logo" value="<?= old('logo', $link['logo']) ?>">
                <?php if (isset($errors['logo'])): ?>
                    <div class="error-message"><?= $errors['logo'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="sort_order">排序</label>
                <input type="number" id="sort_order" name="sort_order" value="<?= old('sort_order', $link['sort_order']) ?>">
                <?php if (isset($errors['sort_order'])): ?>
                    <div class="error-message"><?= $errors['sort_order'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="status">状态</label>
                <select id="status" name="status">
                    <option value="active" <?= old('status', $link['status']) == 'active' ? 'selected' : '' ?>>活跃</option>
                    <option value="inactive" <?= old('status', $link['status']) == 'inactive' ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="/admin/links" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<?= view('admin/layouts/footer') ?>
