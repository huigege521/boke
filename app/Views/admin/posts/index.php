<?php
$activePage = 'posts';
$pageTitle = '文章管理';
$styles = '<style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .status-draft {
            background-color: #ffc107;
            color: #212529;
        }

        .status-published {
            background-color: #28a745;
            color: #fff;
        }

        .status-pending {
            background-color: #17a2b8;
            color: #fff;
        }

        .visibility-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .visibility-public {
            background-color: #6c757d;
            color: #fff;
        }

        .visibility-private {
            background-color: #dc3545;
            color: #fff;
        }

        .tag-badge {
            display: inline-block;
            padding: 2px 6px;
            background-color: #007bff;
            color: #fff;
            border-radius: 10px;
            font-size: 11px;
            margin-right: 3px;
            margin-bottom: 2px;
            transition: all 0.3s ease;
        }

        .tag-badge:hover {
            background-color: #0069d9;
            transform: translateY(-1px);
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-hover tbody tr {
            transition: all 0.3s ease;
        }

        .btn-sm {
            transition: all 0.3s ease;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .batch-actions {
            margin-bottom: 15px;
        }

        .sortable-header {
            cursor: pointer;
            user-select: none;
        }

        .sortable-header:hover {
            text-decoration: underline;
        }

        .sort-icon {
            margin-left: 5px;
            font-size: 10px;
        }
    </style>';
?>


<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 批量操作和搜索 -->
<div class="mb-4">
    <div class="row align-items-center">
        <div class="col-md-4">
            <form action="/admin/posts" method="post" class="batch-actions d-flex align-items-center">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="POST">
                <div class="form-group mr-2 mb-0">
                    <select name="action" class="form-control"
                        style="height: 40px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); border-radius: 6px; min-width: 150px;">
                        <option value="">批量操作</option>
                        <option value="publish">批量发布</option>
                        <option value="draft">设为草稿</option>
                        <option value="pending">设为待审核</option>
                        <option value="scheduled">设为定时发布</option>
                        <option value="delete">批量删除</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-primary mr-4"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2); border-radius: 6px; transition: all 0.3s ease;"
                    onclick="return confirm('确定要执行此操作吗？');">执行</button>
                <a href="/admin/posts/create" class="btn btn-primary"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3); border-radius: 6px; transition: all 0.3s ease;">创建文章</a>
            </form>
        </div>
        <div class="col-md-8">
            <form action="/admin/posts" method="get" class="d-flex justify-content-end">
                <div class="input-group" style="width: 100%; max-width: 600px;">
                    <input type="text" name="search" class="form-control rounded-l-lg border-right-0"
                        placeholder="搜索文章标题、内容、作者..." value="<?= isset($search) ? $search : '' ?>"
                        style="height: 40px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary rounded-r-lg border-left-0"
                            style="height: 40px; box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3); transition: all 0.3s ease;">
                            <i class="fas fa-search mr-1"></i> 搜索
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr class="my-3">
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="filter-form">
        <form action="/admin/posts" method="get" class="d-flex align-items-end">
            <div class="form-group" style="flex: 1; margin-right: 20px;">
                <label for="category" class="form-label">分类</label>
                <select name="category" id="category" class="form-control"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); border-radius: 6px; width: 100%;">
                    <option value="">全部分类</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= isset($categoryId) && $categoryId == $category['id'] ? 'selected' : '' ?>><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1; margin-right: 20px;">
                <label for="status" class="form-label">状态</label>
                <select name="status" id="status" class="form-control"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); border-radius: 6px; width: 100%;">
                    <option value="">全部状态</option>
                    <option value="published" <?= isset($status) && $status == 'published' ? 'selected' : '' ?>>已发布
                    </option>
                    <option value="draft" <?= isset($status) && $status == 'draft' ? 'selected' : '' ?>>草稿</option>
                    <option value="pending" <?= isset($status) && $status == 'pending' ? 'selected' : '' ?>>待审核</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1; margin-right: 20px;">
                <label for="order_by" class="form-label">排序</label>
                <select name="order_by" id="order_by" class="form-control"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); border-radius: 6px; width: 100%;">
                    <option value="created_at" <?= isset($orderBy) && $orderBy == 'created_at' ? 'selected' : '' ?>>创建时间
                    </option>
                    <option value="published_at" <?= isset($orderBy) && $orderBy == 'published_at' ? 'selected' : '' ?>>
                        发布时间</option>
                    <option value="views" <?= isset($orderBy) && $orderBy == 'views' ? 'selected' : '' ?>>浏览量</option>
                    <option value="comments_count" <?= isset($orderBy) && $orderBy == 'comments_count' ? 'selected' : '' ?>>评论数</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1; margin-right: 20px;">
                <label for="order_direction" class="form-label">排序方向</label>
                <select name="order_direction" id="order_direction" class="form-control"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); border-radius: 6px; width: 100%;">
                    <option value="desc" <?= isset($orderDirection) && $orderDirection == 'desc' ? 'selected' : '' ?>>降序
                    </option>
                    <option value="asc" <?= isset($orderDirection) && $orderDirection == 'asc' ? 'selected' : '' ?>>升序
                    </option>
                </select>
            </div>
            <div class="form-group" style="margin-right: 15px;">
                <button type="submit" class="btn btn-primary"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3); border-radius: 6px; transition: all 0.3s ease; min-width: 100px;">应用筛选</button>
            </div>
            <div class="form-group">
                <a href="/admin/posts" class="btn btn-outline-secondary"
                    style="height: 40px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border-radius: 6px; transition: all 0.3s ease; min-width: 100px;">重置</a>
            </div>
        </form>
    </div>
</div>

<!-- 文章列表 -->
<div class="card shadow">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">文章列表</h6>
            <span class="text-muted">共 <?= isset($pagination) ? $pagination['total_items'] : 0 ?> 篇文章</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" id="select-all" class="form-check-input">
                        </th>
                        <th style="width: 60px;">ID</th>
                        <th>标题</th>
                        <th style="width: 120px;">作者</th>
                        <th style="width: 120px;">分类</th>
                        <th>标签</th>
                        <th style="width: 100px;">状态</th>
                        <th style="width: 100px;">可见性</th>
                        <th style="width: 80px;" class="sortable-header">
                            <a
                                href="/admin/posts?order_by=views&order_direction=<?= isset($orderDirection) && $orderDirection == 'desc' ? 'asc' : 'desc' ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?><?= isset($status) ? '&status=' . $status : '' ?><?= isset($categoryId) ? '&category=' . $categoryId : '' ?>">
                                浏览量
                                <?php if (isset($orderBy) && $orderBy == 'views'): ?>
                                    <span
                                        class="sort-icon"><?= isset($orderDirection) && $orderDirection == 'desc' ? '↓' : '↑' ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th style="width: 80px;" class="sortable-header">
                            <a
                                href="/admin/posts?order_by=comments_count&order_direction=<?= isset($orderDirection) && $orderDirection == 'desc' ? 'asc' : 'desc' ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?><?= isset($status) ? '&status=' . $status : '' ?><?= isset($categoryId) ? '&category=' . $categoryId : '' ?>">
                                评论数
                                <?php if (isset($orderBy) && $orderBy == 'comments_count'): ?>
                                    <span
                                        class="sort-icon"><?= isset($orderDirection) && $orderDirection == 'desc' ? '↓' : '↑' ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th style="width: 150px;" class="sortable-header">
                            <a
                                href="/admin/posts?order_by=published_at&order_direction=<?= isset($orderDirection) && $orderDirection == 'desc' ? 'asc' : 'desc' ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?><?= isset($status) ? '&status=' . $status : '' ?><?= isset($categoryId) ? '&category=' . $categoryId : '' ?>">
                                发布时间
                                <?php if (isset($orderBy) && $orderBy == 'published_at'): ?>
                                    <span
                                        class="sort-icon"><?= isset($orderDirection) && $orderDirection == 'desc' ? '↓' : '↑' ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th style="width: 120px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_ids[]" value="<?= $post['id'] ?>"
                                        class="form-check-input post-checkbox">
                                </td>
                                <td class="text-center font-medium"><?= $post['id'] ?></td>
                                <td><?= $post['title'] ?></td>
                                <td><?= $post['username'] ?? '未知' ?></td>
                                <td><?= $post['category_name'] ?? '未分类' ?></td>
                                <td>
                                    <?php if (!empty($post['tags'])): ?>
                                        <?php foreach ($post['tags'] as $tag): ?>
                                            <span class="tag-badge"><?= $tag['name'] ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">无标签</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $post['status'] ?>">
                                        <?= $post['status'] == 'draft' ? '草稿' : ($post['status'] == 'published' ? '已发布' : '待审核') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="visibility-badge visibility-<?= $post['visibility'] ?>">
                                        <?= $post['visibility'] == 'public' ? '公开' : '私有' ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $post['views'] ?? 0 ?></td>
                                <td class="text-center"><?= $post['comments_count'] ?? 0 ?></td>
                                <td><?= $post['published_at'] ?? '未发布' ?></td>
                                <td>
                                    <a href="/admin/posts/<?= $post['id'] ?>/edit" class="btn btn-sm btn-primary">编辑</a>
                                    <form action="/admin/posts/<?= $post['id'] ?>" method="post" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-sm btn-danger ml-3"
                                            onclick="return confirm('确定要删除这篇文章吗？');">删除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="text-center py-5">
                                <p class="text-muted">暂无文章</p>
                                <a href="/admin/posts/create" class="btn btn-primary mt-3">创建文章</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 分页 -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div class="card-footer bg-white">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= $pagination['base_url'] ?><?= strpos($pagination['base_url'], '?') !== false ? '&' : '?' ?>page=<?= $pagination['current_page'] - 1 ?>">上一页</a>
                        </li>
                    <?php endif; ?>

                    <?php
                    // 计算显示的页码范围
                    $startPage = max(1, $pagination['current_page'] - 2);
                    $endPage = min($pagination['total_pages'], $startPage + 4);
                    if ($endPage - $startPage < 4) {
                        $startPage = max(1, $endPage - 4);
                    }

                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link"
                                href="<?= $pagination['base_url'] ?><?= strpos($pagination['base_url'], '?') !== false ? '&' : '?' ?>page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= $pagination['base_url'] ?><?= strpos($pagination['base_url'], '?') !== false ? '&' : '?' ?>page=<?= $pagination['current_page'] + 1 ?>">下一页</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
    // 全选/取消全选
    document.getElementById('select-all').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.post-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // 批量操作表单提交前检查
    document.querySelector('.batch-actions').addEventListener('submit', function (e) {
        const action = this.querySelector('select[name="action"]').value;
        const checkboxes = document.querySelectorAll('.post-checkbox:checked');

        if (!action) {
            e.preventDefault();
            alert('请选择操作');
            return;
        }

        if (checkboxes.length === 0) {
            e.preventDefault();
            alert('请选择要操作的文章');
            return;
        }
    });
</script>

<?= view('admin/layouts/footer') ?>