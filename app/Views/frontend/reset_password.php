<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- Bootstrap CSS -->
    <link href="<?= base_url('css/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }

        .reset-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            font-size: 16px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="reset-container">
        <h2>重置密码</h2>

        <!-- 错误消息 -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
            </div>
        <?php endif; ?>

        <!-- 如果令牌有效，显示重置表单 -->
        <?php if (!empty($token)): ?>
            <form action="<?= base_url('home/resetPassword?token=' . $token) ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="password">新密码</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="请输入新密码">
                </div>
                <div class="form-group">
                    <label for="confirm_password">确认密码</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="请再次输入密码">
                </div>
                <button type="submit" class="btn btn-primary">重置密码</button>
            </form>
        <?php else: ?>
            <!-- 如果令牌无效，显示提示信息 -->
            <div class="text-center">
                <p>无法完成密码重置</p>
                <a href="<?= base_url('home/forgotPassword') ?>" class="btn btn-link">重新获取重置链接</a>
            </div>
        <?php endif; ?>

        <div class="login-link">
            <p>记住密码了？<a href="<?= base_url('home/login') ?>">立即登录</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= base_url('js/bootstrap/bootstrap.bundle.min.js') ?>"></script>

</body>

</html>