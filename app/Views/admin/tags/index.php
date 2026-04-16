<?php
$activePage = 'tags';
$pageTitle = '标签管理';
$styles = '<style>
        .tag-badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border-radius: 15px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 操作按钮 -->
<div class="mb-4">
    <a href="/admin/tags/create" class="btn btn-primary">创建标签</a>
</div>

<!-- 标签列表 -->
<div class="card">
    <div class="card-header">
        标签列表
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>别名</th>
                        <th>文章数</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $tag): ?>
                        <tr>
                            <td><?= $tag['id'] ?></td>
                            <td><?= $tag['name'] ?></td>
                            <td><?= $tag['slug'] ?></td>
                            <td><?= $tag['posts_count'] ?></td>
                            <td><?= $tag['created_at'] ?></td>
                            <td>
                                <a href="/admin/tags/<?= $tag['id'] ?>/edit" class="btn btn-sm btn-primary">编辑</a>
                                <form action="/admin/tags/<?= $tag['id'] ?>" method="post" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('确定要删除这个标签吗？');">删除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 热门标签 -->
<div class="card mt-4">
    <div class="card-header">
        热门标签
    </div>
    <div class="card-body">
        <?php foreach ($tags as $tag): ?>
            <?php if ($tag['posts_count'] > 5): ?>
                <span class="tag-badge">
                    <?= $tag['name'] ?> (<?= $tag['posts_count'] ?>)
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <!-- 分页 -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= $pagination['base_url'] ?>?page=<?= $pagination['current_page'] - 1 ?>">上一页</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $pagination['base_url'] ?>?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= $pagination['base_url'] ?>?page=<?= $pagination['current_page'] + 1 ?>">下一页</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?= view('admin/layouts/footer') ?>