<!-- 右侧边栏 -->
<div class="col-md-4">
    <!-- 分类列表 -->
    <div class="sidebar-card mb-4">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-folder mr-2"></i>文章分类</h5>
        </div>
        <ul class="list-group list-group-flush">
            <?php foreach ($categories as $category): ?>
                <li class="list-group-item">
                    <a href="<?= base_url('category/' . $category['slug']) ?>" class="hover-primary">
                        <i class="fas fa-folder-open mr-2"></i><?= $category['name'] ?>
                        <span class="badge badge-secondary float-right"><?= $category['posts_count'] ?? 0 ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- 标签云 -->
    <div class="sidebar-card mb-4">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-tags mr-2"></i>标签云</h5>
        </div>
        <div class="card-body">
            <?php foreach ($tags as $tag): ?>
                <a href="<?= base_url('tag/' . $tag['slug']) ?>" class="badge mb-1 hover-primary">
                    <i class="fas fa-tag mr-1"></i><?= $tag['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 友情链接 -->
    <div class="sidebar-card mb-4">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-link mr-2"></i>友情链接</h5>
        </div>
        <ul class="list-group list-group-flush">
            <?php
            $linkModel = new \App\Models\LinkModel();
            $links = $linkModel->getActiveLinks();
            foreach ($links as $link):
                ?>
                <li class="list-group-item">
                    <a href="<?= $link['url'] ?>" target="_blank" title="<?= $link['description'] ?>" class="hover-primary">
                        <i class="fas fa-external-link-alt mr-2"></i><?= $link['name'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- 归档列表 -->
    <div class="sidebar-card mb-4">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-archive mr-2"></i>文章归档</h5>
        </div>
        <ul class="list-group list-group-flush">
            <?php
            $postModel = new \App\Models\PostModel();
            $archiveMonths = $postModel->getArchiveMonths();
            foreach ($archiveMonths as $month):
                $monthName = $month['year'] . '年' . $month['month'] . '月';
                ?>
                <li class="list-group-item">
                    <a href="<?= base_url('archive/' . $month['year'] . '/' . $month['month']) ?>" class="hover-primary">
                        <i class="fas fa-calendar-alt mr-2"></i><?= $monthName ?>
                        <span class="badge badge-secondary float-right"><?= $month['count'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>