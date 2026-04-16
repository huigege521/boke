<?php
$activePage = 'categories';
$pageTitle = '编辑分类';
$styles = '<style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
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

    <!-- 编辑分类表单 -->
    <div class="card">
        <div class="card-header">
            编辑分类
        </div>
        <div class="card-body">
            <form action="/admin/categories/<?= $category['id'] ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <div class="form-group">
                    <label for="name">分类名称</label>
                    <input type="text" id="name" name="name" value="<?= old('name', $category['name']) ?>">
                    <?php if (isset($errors['name'])): ?>
                        <div class="error-message"><?= $errors['name'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="slug">别名</label>
                    <input type="text" id="slug" name="slug" value="<?= old('slug', $category['slug']) ?>">
                    <?php if (isset($errors['slug'])): ?>
                        <div class="error-message"><?= $errors['slug'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">描述</label>
                    <textarea id="description" name="description" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;"><?= old('description', $category['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="parent_id">父分类</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">无</option>
                        <?php foreach ($parentCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= old('parent_id', $category['parent_id']) == $cat['id'] ? 'selected' : '' ?>>
                                <?= str_repeat('-', $cat['level'] ?? 0) ?><?= $cat['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                <label for="order">排序</label>
                <input type="text" id="order" name="order" value="<?= old('order', $category['order']) ?>">
                <?php if (isset($errors['order'])): ?>
                    <div class="error-message"><?= $errors['order'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="icon">图标（Font Awesome 类名）</label>
                <input type="text" id="icon" name="icon" value="<?= old('icon', $category['icon'] ?? '') ?>" placeholder="例如：fas fa-folder">
                <small class="form-text text-muted">
                    请输入 Font Awesome 图标类名，例如：fas fa-folder、fas fa-code、fas fa-life-ring 等。
                </small>
            </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">更新</button>
                    <a href="/admin/categories" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>

<?= view('admin/layouts/footer') ?>