<?php
$activePage = 'tags';
$pageTitle = '创建标签';
$styles = '<style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"] {
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

    <!-- 创建标签表单 -->
    <div class="card">
        <div class="card-header">
            创建新标签
        </div>
        <div class="card-body">
            <form action="/admin/tags" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name">标签名称</label>
                    <input type="text" id="name" name="name" value="<?= old('name') ?>">
                    <?php if (isset($errors['name'])): ?>
                        <div class="error-message"><?= $errors['name'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="slug">别名</label>
                    <input type="text" id="slug" name="slug" value="<?= old('slug') ?>">
                    <?php if (isset($errors['slug'])): ?>
                        <div class="error-message"><?= $errors['slug'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="/admin/tags" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>

<?= view('admin/layouts/footer') ?>