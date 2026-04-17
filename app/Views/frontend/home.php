<?= $this->include('frontend/layouts/header') ?>

<!-- 消息提示 -->
<div class="container mt-3">
    <!-- 错误消息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
        </div>
    <?php endif; ?>

    <!-- 成功消息 -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
        </div>
    <?php endif; ?>
</div>

<!-- 主体内容 -->
<div class="container mt-5">
    <div class="row">
        <!-- 左侧内容区 -->
        <div class="col-md-8">
            <?php foreach ($posts as $post): ?>
                <div class="card mb-5">
                    <?php if ($post['featured_image']): ?>
                        <?php
                        $fileExtension = strtolower(pathinfo($post['featured_image'], PATHINFO_EXTENSION));
                        $filePath = '/uploads/' . $post['featured_image'];
                        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            // 显示图片
                            echo '<img src="' . base_url('uploads/' . $post['featured_image']) . '" data-src="' . base_url('uploads/' . $post['featured_image']) . '" class="card-img-top lazyload" alt="' . $post['title'] . '" loading="lazy">';
                        } else if ($fileExtension == 'pdf') {
                            // 显示 PDF 链接
                            echo '<div class="card-img-top p-4 bg-light text-center">
                                    <i class="fas fa-file-pdf text-danger fa-4x mb-2"></i>
                                    <p class="mb-2">PDF 文件</p>
                                    <a href="' . $filePath . '" target="_blank" class="btn btn-primary btn-sm">查看 PDF</a>
                                </div>';
                        } else {
                            // 显示其他文件链接
                            echo '<div class="card-img-top p-4 bg-light text-center">
                                    <i class="fas fa-file-alt text-secondary fa-4x mb-2"></i>
                                    <p class="mb-2">附件文件</p>
                                    <a href="' . $filePath . '" target="_blank" class="btn btn-secondary btn-sm">下载文件</a>
                                </div>';
                        }
                        ?>
                    <?php endif; ?>
                    <div class="card-body">
                        <h2 class="card-title">
                            <a href="<?= base_url('post/' . $post['slug']) ?>"
                                class="hover-primary"><?= $post['title'] ?></a>
                        </h2>
                        <p class="card-text text-muted">
                            <i class="fas fa-user mr-1"></i> 作者: <a href="#"
                                class="text-muted hover-primary"><?= $post['author_name'] ?></a> |
                            <i class="fas fa-folder mr-1"></i> 分类: <a
                                href="<?= base_url('category/' . $post['category_slug']) ?>"
                                class="text-muted hover-primary"><?= $post['category_name'] ?></a>
                            |
                            <i class="fas fa-calendar mr-1"></i> 发布时间:
                            <?= date('Y-m-d', strtotime($post['published_at'])) ?> |
                            <i class="fas fa-eye mr-1"></i> 浏览: <?= $post['views'] ?> |
                            <i class="fas fa-comment mr-1"></i> 评论: <?= $post['comments_count'] ?>
                        </p>
                        <p class="card-text"><?= $post['excerpt'] ?></p>
                        <a href="<?= base_url('post/' . $post['slug']) ?>" class="btn btn-primary"><i
                                class="fas fa-arrow-right mr-2"></i>阅读更多</a>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- 分页导航 -->
            <?php if (isset($pager) && $totalPosts > $perPage): ?>
                <div class="mt-5">
                    <?php $pagerLinks = $pager->makeLinks($currentPage, $perPage, $totalPosts, 'bootstrap_full'); ?>
                    <?php if ($pagerLinks): ?>
                        <?= $pagerLinks ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 右侧边栏 -->
        <?= $this->include('frontend/layouts/sidebar') ?>
    </div>
</div>

<script>
    // 图片懒加载
    document.addEventListener('DOMContentLoaded', function () {
        const lazyImages = document.querySelectorAll('.lazyload');

        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        const image = entry.target;
                        image.src = image.dataset.src;
                        image.classList.remove('lazyload');
                        image.classList.add('loaded');
                        imageObserver.unobserve(image);
                    }
                });
            });

            lazyImages.forEach(function (image) {
                imageObserver.observe(image);
            });
        } else {
            // 降级方案
            lazyImages.forEach(function (image) {
                image.src = image.dataset.src;
                image.classList.remove('lazyload');
                image.classList.add('loaded');
            });
        }
    });
</script>



<?= $this->include('frontend/layouts/footer') ?>