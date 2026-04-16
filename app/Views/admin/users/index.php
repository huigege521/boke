<?php
$activePage = 'users';
$pageTitle = '用户管理';
$styles = '<style>
        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .role-admin {
            background-color: #dc3545;
            color: #fff;
        }

        .role-author {
            background-color: #28a745;
            color: #fff;
        }

        .role-subscriber {
            background-color: #17a2b8;
            color: #fff;
        }
    </style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

    <!-- 操作按钮 -->
    <div class="mb-4">
        <a href="/admin/users/create" class="btn btn-primary">创建用户</a>
    </div>

    <!-- 用户列表 -->
    <div class="card">
        <div class="card-header">
            用户列表
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户名</th>
                            <th>邮箱</th>
                            <th>角色</th>
                            <th>注册时间</th>
                            <th>最后登录</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['username'] ?></td>
                                <td><?= $user['email'] ?></td>
                                <td>
                                    <span class="role-badge role-<?= $user['role'] ?>">
                                        <?= $user['role'] == 'admin' ? '管理员' : ($user['role'] == 'author' ? '作者' : '订阅者') ?>
                                    </span>
                                </td>
                                <td><?= $user['created_at'] ?></td>
                                <td><?= $user['last_login'] ?? '未登录' ?></td>
                                <td>
                                    <a href="/admin/users/<?= $user['id'] ?>/edit"
                                        class="btn btn-sm btn-primary">编辑</a>
                                    <form action="/admin/users/<?= $user['id'] ?>" method="post" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('确定要删除这个用户吗？');">删除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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

<?= view('admin/layouts/footer') ?>