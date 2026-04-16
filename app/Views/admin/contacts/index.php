<?php
$activePage = 'contacts';
$pageTitle = '联系消息管理';

$styles = '';
$scripts = '';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 联系消息列表 -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">联系消息列表</h5>
        <div>
            <a href="/admin/contacts" class="btn btn-sm <?= ($filter_status ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">全部</a>
            <a href="/admin/contacts?status=pending" class="btn btn-sm <?= ($filter_status ?? '') === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">未处理</a>
            <a href="/admin/contacts?status=processed" class="btn btn-sm <?= ($filter_status ?? '') === 'processed' ? 'btn-success' : 'btn-outline-success' ?>">已处理</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>姓名</th>
                        <th>邮箱</th>
                        <th>主题</th>
                        <th>状态</th>
                        <th>提交时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td><?= $contact['id'] ?></td>
                            <td><?= $contact['name'] ?></td>
                            <td><?= $contact['email'] ?></td>
                            <td><?= $contact['subject'] ?></td>
                            <td>
                                <?php if ($contact['status'] === 'processed'): ?>
                                    <span class="badge bg-success">已处理</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">未处理</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $contact['created_at'] ?></td>
                            <td>
                                <a href="/admin/contacts/show/<?= $contact['id'] ?>" class="btn btn-sm btn-info"><i
                                        class="fas fa-eye"></i> 查看</a>
                                <form action="/admin/contacts/delete/<?= $contact['id'] ?>" method="POST"
                                    style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('确定要删除这条联系消息吗？')"><i class="fas fa-trash"></i> 删除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($contacts)): ?>
            <div class="alert alert-info">
                暂无联系消息
            </div>
        <?php endif; ?>
    </div>
</div>

<?= view('admin/layouts/footer', compact('scripts')) ?>