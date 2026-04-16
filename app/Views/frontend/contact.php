<?= $this->include('frontend/layouts/header') ?>

    <!-- 主要内容 -->
    <div class="container mt-5">
        <div class="row">
            <!-- 左侧内容 -->
            <div class="col-md-8">
                <h1 class="mb-4"><i class="fas fa-envelope mr-2"></i>联系我们</h1>

                <!-- 消息提示 -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><i class="fas fa-comment-alt mr-2"></i>发送消息</h2>
                        <form method="post" action="<?= base_url('contact') ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf_hash() ?>">
                            <div class="form-group">
                                <label for="name">姓名</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">邮箱</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="subject">主题</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="message">消息</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-paper-plane mr-2"></i>发送消息</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右侧边栏 -->
            <?= $this->include('frontend/layouts/sidebar') ?>
        </div>
    </div>

<?= $this->include('frontend/layouts/footer') ?>