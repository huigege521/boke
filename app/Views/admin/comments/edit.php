<?php
$activePage = 'comments';
$pageTitle = '编辑评论';
$styles = '<style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group textarea {
            height: 200px;
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
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

    <!-- 编辑评论表单 -->
    <div class="card">
        <div class="card-header">
            编辑评论
        </div>
        <div class="card-body">
            <form action="/admin/comments/<?= $comment['id'] ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <div class="form-group">
                    <label for="content">评论内容</label>
                    <textarea id="content" name="content"><?= old('content', $comment['content']) ?></textarea>
                    <?php if (isset($errors['content'])): ?>
                        <div class="error-message"><?= $errors['content'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="status">状态</label>
                    <select id="status" name="status">
                        <option value="pending" <?= old('status', $comment['status']) == 'pending' ? 'selected' : '' ?>>待审核</option>
                        <option value="approved" <?= old('status', $comment['status']) == 'approved' ? 'selected' : '' ?>>已通过</option>
                        <option value="spam" <?= old('status', $comment['status']) == 'spam' ? 'selected' : '' ?>>垃圾</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">更新</button>
                    <a href="/admin/comments" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>

<?= view('admin/layouts/footer') ?>