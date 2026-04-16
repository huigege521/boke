<?php
$activePage = 'categories';
$pageTitle = '分类管理';
$styles = '<style>
        .category-level {
            display: inline-block;
            width: 20px;
        }
    </style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 操作按钮 -->
<div class="mb-4">
    <a href="/admin/categories/create" class="btn btn-primary">创建分类</a>
</div>

<!-- 分类列表 -->
<div class="card">
    <div class="card-header">
        分类列表
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>图标</th>
                        <th>名称</th>
                        <th>别名</th>
                        <th>父分类</th>
                        <th>文章数</th>
                        <th>排序</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= $category['id'] ?></td>
                            <td>
                                <?php if (!empty($category['icon'])): ?>
                                    <i class="<?= $category['icon'] ?>" style="font-size: 1.2rem;"></i>
                                <?php else: ?>
                                    <i class="fas fa-folder" style="font-size: 1.2rem; color: #6c757d;"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="category-level"><?= str_repeat('-', $category['level'] ?? 0) ?></span>
                                <?= $category['name'] ?>
                            </td>
                            <td><?= $category['slug'] ?></td>
                            <td><?= $category['parent_name'] ?? '无' ?></td>
                            <td><?= $category['posts_count'] ?></td>
                            <td><?= $category['order'] ?></td>
                            <td><?= $category['created_at'] ?></td>
                            <td>
                                <a href="/admin/categories/<?= $category['id'] ?>/edit"
                                    class="btn btn-sm btn-primary">编辑</a>
                                <form action="/admin/categories/<?= $category['id'] ?>" method="post"
                                    style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('确定要删除这个分类吗？');">删除</button>
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