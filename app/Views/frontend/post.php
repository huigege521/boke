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
            <article class="card mb-5">
                <?php if ($post['featured_image']): ?>
                    <?php
                    $fileExtension = strtolower(pathinfo($post['featured_image'], PATHINFO_EXTENSION));
                    $filePath = '/uploads/' . $post['featured_image'];
                    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        // 显示图片
                        echo '<img src="' . $filePath . '" data-src="' . $filePath . '" class="card-img-top lazyload" alt="' . $post['title'] . '" loading="lazy">';
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
                    <h1 class="card-title"><?= $post['title'] ?></h1>
                    <p class="card-text text-muted">
                        <i class="fas fa-user mr-1"></i> 作者: <a href="#"
                            class="text-muted hover-primary"><?= $post['author_name'] ?></a> |
                        <i
                            class="<?= !empty($post['category_icon']) ? $post['category_icon'] : 'fas fa-folder' ?> mr-1"></i>
                        分类: <a href="<?= base_url('category/' . $post['category_slug']) ?>"
                            class="text-muted hover-primary"><?= $post['category_name'] ?></a>
                        |
                        <i class="fas fa-calendar mr-1"></i> 发布时间:
                        <?= date('Y-m-d H:i', strtotime($post['published_at'])) ?> |
                        <i class="fas fa-eye mr-1"></i> 浏览: <?= $post['views'] ?> |
                        <i class="fas fa-comment mr-1"></i> 评论: <?= $post['comments_count'] ?>
                    </p>
                    <div class="card-text">
                        <?= $post['content'] ?>
                    </div>
                </div>
            </article>


            <!-- 评论列表 -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-comments mr-2"></i>评论列表</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted">暂无评论，快来发表第一条评论吧！</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="media mb-4">
                                <img src="<?= base_url('images/icon_android.jpg') ?>" alt="评论者头像" loading="lazy">
                                <div class="media-body">
                                    <h6 class="mt-0"><?= $comment['user_name'] ?></h6>
                                    <p><?= $comment['content'] ?></p>
                                    <p class="text-muted text-sm">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 评论区 -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-comments mr-2"></i>评论区</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comment') ?>" method="post" id="commentForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_hash() ?>">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="comment_token" value="<?= md5(uniqid(rand(), true)) ?>">
                        <div class="form-group">
                            <label for="commentContent">写下你的评论</label>
                            <textarea class="form-control" id="commentContent" name="content" rows="3"
                                required></textarea>
                        </div>
                        <?php if (!session()->get('logged_in')): ?>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="name">姓名</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email">邮箱</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="captcha">验证码</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="captcha" name="captcha" required
                                    placeholder="请输入验证码">
                                <div class="input-group-append">
                                    <img src="<?= base_url('captcha') ?>" alt="验证码" class="captcha-image"
                                        style="height: 40px; cursor: pointer;"
                                        onclick="this.src = '<?= base_url('captcha') ?>?t=' + new Date().getTime()">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3" id="submitCommentBtn"><i
                                class="fas fa-paper-plane mr-2"></i>提交评论</button>
                    </form>

                    <script>
                        // 防重复提交 - 前端禁用按钮
                        document.getElementById('commentForm').addEventListener('submit', function (e) {
                            const submitBtn = document.getElementById('submitCommentBtn');
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>提交中...';
                        });
                    </script>
                </div>
            </div>
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
            });
        }
    });
</script>

<?= $this->include('frontend/layouts/footer') ?>