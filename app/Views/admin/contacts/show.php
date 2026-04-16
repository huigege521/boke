<?php
$activePage = 'contacts';
$pageTitle = '联系消息详情';

$styles = '';
$scripts = '';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 联系消息详情 -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">联系消息详情</h5>
        <a href="/admin/contacts" class="btn btn-sm btn-secondary float-end"><i class="fas fa-arrow-left"></i> 返回列表</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">姓名</label>
                    <p class="form-control-plaintext"><?= $contact['name'] ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">邮箱</label>
                    <p class="form-control-plaintext"><?= $contact['email'] ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">主题</label>
                    <p class="form-control-plaintext"><?= $contact['subject'] ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">提交时间</label>
                    <p class="form-control-plaintext"><?= $contact['created_at'] ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">处理状态</label>
                    <p class="form-control-plaintext">
                        <?php if ($contact['status'] === 'processed'): ?>
                            <span class="badge bg-success">已处理</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">未处理</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">消息内容</label>
                    <div class="border rounded p-3 bg-light">
                        <?= nl2br($contact['message']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <?php if ($contact['status'] !== 'processed'): ?>
                <form action="/admin/contacts/process/<?= $contact['id'] ?>" method="POST" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> 标记为已处理</button>
                </form>
            <?php endif; ?>
            <form action="/admin/contacts/delete/<?= $contact['id'] ?>" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除这条联系消息吗？')"><i
                        class="fas fa-trash"></i> 删除</button>
            </form>
        </div>
    </div>
</div>

<?= view('admin/layouts/footer', compact('scripts')) ?>