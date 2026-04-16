<?= $this->include('frontend/layouts/header') ?>

<!-- 主要内容 -->
<div class="container mt-5">
    <div class="row">
        <!-- 左侧内容 -->
        <div class="col-md-8">
            <h1 class="mb-4"><i class="fas fa-info-circle mr-2"></i><?= $about_title ?></h1>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title"><i class="fas fa-history mr-2"></i>博客系统简介</h2>
                    <p class="card-text">
                        <?= $about_description ?>
                    </p>
                    <p class="card-text">
                        我们的博客系统具有以下特点：
                    </p>
                    <?= $about_features ?>
                    <p class="card-text">
                        我们致力于打造一个优质的内容分享平台，欢迎您的加入和使用！
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title"><i class="fas fa-users mr-2"></i>团队介绍</h2>
                    <p class="card-text">
                        <?= $team_intro ?>
                    </p>
                    <div class="row mt-4">
                        <?php if (!empty($team_members)): ?>
                            <?php foreach ($team_members as $member): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="text-center">
                                        <div class="rounded-circle <?= $member['color'] ?> text-white p-4 mb-3 mx-auto"
                                            style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user fa-3x"></i>
                                        </div>
                                        <h3 class="h5"><?= $member['name'] ?></h3>
                                        <p class="text-muted"><?= $member['position'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-md-4 mb-4">
                                <div class="text-center">
                                    <div class="rounded-circle bg-info text-white p-4 mb-3 mx-auto"
                                        style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user fa-3x"></i>
                                    </div>
                                    <h3 class="h5">张三</h3>
                                    <p class="text-muted">系统架构师</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="text-center">
                                    <div class="rounded-circle bg-success text-white p-4 mb-3 mx-auto"
                                        style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user fa-3x"></i>
                                    </div>
                                    <h3 class="h5">李四</h3>
                                    <p class="text-muted">前端开发</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="text-center">
                                    <div class="rounded-circle bg-warning text-white p-4 mb-3 mx-auto"
                                        style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user fa-3x"></i>
                                    </div>
                                    <h3 class="h5">王五</h3>
                                    <p class="text-muted">后端开发</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧边栏 -->
        <?= $this->include('frontend/layouts/sidebar') ?>
    </div>
</div>

<?= $this->include('frontend/layouts/footer') ?>