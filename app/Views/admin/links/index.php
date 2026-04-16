<?php
$activePage = 'links';
$pageTitle = '友情链接管理';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle')) ?>

<!-- 操作区域 -->
<div class="mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="/admin/links/create" class="btn btn-primary"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3); border-radius: 6px; transition: all 0.3s ease;">添加友情链接</a>
            </div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
    </div>
<?php endif; ?>

<!-- 友情链接列表 -->
<div class="card shadow">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">友情链接列表</h6>
            <span class="text-muted">共 <?= count($links) ?> 个友情链接</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>名称</th>
                        <th>URL</th>
                        <th>描述</th>
                        <th style="width: 80px;">排序</th>
                        <th style="width: 100px;">状态</th>
                        <th style="width: 150px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links as $link): ?>
                        <tr>
                            <td class="text-center"><?= $link['id'] ?></td>
                            <td><?= $link['name'] ?></td>
                            <td><a href="<?= $link['url'] ?>" target="_blank"><?= $link['url'] ?></a></td>
                            <td><?= $link['description'] ?></td>
                            <td class="text-center"><?= $link['sort_order'] ?></td>
                            <td class="text-center">
                                <span class="status-badge status-<?= $link['status'] ?>">
                                    <?= $link['status'] == 'active' ? '活跃' : '禁用' ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/links/edit/<?= $link['id'] ?>" class="btn btn-sm btn-primary">编辑</a>
                                <a href="/admin/links/delete/<?= $link['id'] ?>" class="btn btn-sm btn-danger ml-2"
                                    onclick="return confirm('确定要删除吗？');">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($links)): ?>
            <div class="text-center py-5">
                <p class="text-muted">暂无友情链接</p>
                <a href="/admin/links/create" class="btn btn-primary mt-3">添加友情链接</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= view('admin/layouts/footer') ?>