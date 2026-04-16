<?= $this->include('frontend/layouts/header') ?>

<!-- 主要内容 -->
<div class="container mt-5">
    <div class="row">
        <!-- 左侧内容 -->
        <div class="col-md-8">
            <h1 class="mb-4"><i class="fas fa-calendar-alt mr-2"></i><?= $year ?>年<?= $month ?>月归档</h1>

            <?php if (empty($posts)): ?>
                <div class="alert alert-info">
                    该月份暂无文章
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2 class="card-title"><a href="/post/<?= $post['slug'] ?>"
                                    class="hover-primary"><?= $post['title'] ?></a></h2>
                            <p class="card-text text-muted">
                                <small>
                                    <i class="fas fa-calendar mr-1"></i> 发布于: <?= $post['created_at'] ?> |
                                    <i class="fas fa-folder mr-1"></i> 分类: <a href="/category/<?= $post['category_slug'] ?>"
                                        class="hover-primary"><?= $post['category_name'] ?></a>

                                </small>
                            </p>
                            <p class="card-text"><?= mb_substr(strip_tags($post['content']), 0, 200) ?>...</p>
                            <a href="/post/<?= $post['slug'] ?>" class="btn btn-primary"><i
                                    class="fas fa-book mr-2"></i>阅读更多</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 右侧边栏 -->
        <?= $this->include('frontend/layouts/sidebar') ?>
    </div>
</div>

<?= $this->include('frontend/layouts/footer') ?>