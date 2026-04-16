<?php
$activePage = 'comments';
$pageTitle = '评论管理';
$styles = '<style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-approved {
            background-color: #28a745;
            color: #fff;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-spam {
            background-color: #dc3545;
            color: #fff;
        }

        .comment-content {
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>';
$scripts = '<script>
        // 全选/取消全选
        document.getElementById("select-all").addEventListener("change", function () {
            var checkboxes = document.querySelectorAll("input[name=\"comment_ids[]\"]");
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = this.checked;
            }.bind(this));
        });
    </script>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

    <!-- 评论状态筛选和搜索 -->
    <div class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?= !isset($status) ? 'active' : '' ?>" href="/admin/comments">全部</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($status) && $status == 'pending' ? 'active' : '' ?>"
                            href="/admin/comments/pending">待审核</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($status) && $status == 'approved' ? 'active' : '' ?>"
                            href="/admin/comments/approved">已通过</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($status) && $status == 'spam' ? 'active' : '' ?>"
                            href="/admin/comments/spam">垃圾</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-8">
                <form action="<?= isset($status) ? '/admin/comments/' . $status : '/admin/comments' ?>" method="get" class="form-inline float-right">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="搜索评论..." value="<?= isset($search) ? $search : '' ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-outline-secondary">搜索</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 批量操作 -->
    <form action="/admin/comments/batchAction" method="post" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <select class="form-control" name="action" required>
                    <option value="">批量操作</option>
                    <option value="approve">通过</option>
                    <option value="spam">标记为垃圾</option>
                    <option value="delete">删除</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">应用</button>
            </div>
        </div>

        <!-- 评论列表 -->
        <div class="card">
            <div class="card-header">
                评论列表
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>ID</th>
                                <th>内容</th>
                                <th>作者</th>
                                <th>文章</th>
                                <th>状态</th>
                                <th>IP地址</th>
                                <th>时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><input type="checkbox" name="comment_ids[]"
                                            value="<?= $comment['id'] ?>"></td>
                                    <td><?= $comment['id'] ?></td>
                                    <td class="comment-content">
                                        <?= strlen($comment['content']) > 100 ? substr($comment['content'], 0, 100) . '...' : $comment['content'] ?>
                                    </td>
                                    <td><?= $comment['username'] ?? $comment['author_name'] ?></td>
                                    <td><a href="/post/<?= $comment['slug'] ?? '' ?>"
                                            target="_blank"><?= $comment['post_title'] ?></a></td>
                                    <td>
                                        <span class="status-badge status-<?= $comment['status'] ?>">
                                            <?= $comment['status'] == 'approved' ? '已通过' : ($comment['status'] == 'pending' ? '待审核' : '垃圾') ?>
                                        </span>
                                    </td>
                                    <td><?= $comment['author_ip'] ?></td>
                                    <td><?= $comment['created_at'] ?></td>
                                    <td>
                                        <a href="/admin/comments/<?= $comment['id'] ?>/edit"
                                            class="btn btn-sm btn-primary">编辑</a>
                                        <form action="/admin/comments/<?= $comment['id'] ?>" method="post" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('确定要删除这条评论吗？');">删除</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- 分页 -->
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="card-footer">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $pagination['base_url'] ?>?page=<?= $pagination['current_page'] - 1 ?>">上一页</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $pagination['base_url'] ?>?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $pagination['base_url'] ?>?page=<?= $pagination['current_page'] + 1 ?>">下一页</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </form>

<?= view('admin/layouts/footer', compact('scripts')) ?>