<?= $this->include('frontend/layouts/header') ?>

<!-- 主体内容 -->
<div class="container mt-5">
    <div class="row">
        <!-- 左侧内容区 -->
        <div class="col-md-8">
            <h1 class="mb-4"><i class="fas fa-user mr-2"></i>个人资料</h1>

            <!-- 错误消息 -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                </div>
            <?php endif; ?>

            <!-- 验证错误消息 -->
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <div><?= $error ?></div>
                    <?php endforeach; ?>
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

            <div class="card mb-5">
                <div class="card-body">
                    <form action="/home/updateProfile" method="post">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="username">用户名</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?= $user['username'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">邮箱</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= $user['email'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="name">真实姓名</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?= $user['name'] ?? '' ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-save mr-2"></i>保存修改</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="mb-3"><i class="fas fa-key mr-2"></i>修改密码</h3>
                    <form action="/home/changePassword" method="post">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="current_password">当前密码</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">新密码</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required
                                minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">确认新密码</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary mt-2"><i
                                class="fas fa-exchange-alt mr-2"></i>修改密码</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 右侧边栏 -->
        <?= $this->include('frontend/layouts/sidebar') ?>
    </div>
</div>

<?= $this->include('frontend/layouts/footer') ?>