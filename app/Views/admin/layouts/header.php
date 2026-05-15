<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?>
    </title>
    <!-- Bootstrap CSS -->
    <link href="<?= base_url('css/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/all.min.css') ?>">
    <!-- jQuery -->
    <script src="<?= base_url('js/jquery.min.js') ?>"></script>
    <!-- Bootstrap JS -->
    <script src="<?= base_url('js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <!-- Toastr -->
    <link rel="stylesheet" href="<?= base_url('css/toastr/toastr.min.css') ?>">
    <script src="<?= base_url('js/toastr/toastr.min.js') ?>"></script>
    <!-- Font Awesome -->
    <style>
        body {
            background-color: #f5f6f8;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
            padding: 0;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            width: 240px;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: #2c3e50;
        }

        .sidebar-header h3 {
            font-size: 1.3rem;
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
            color: #fff;
        }

        .sidebar-menu {
            padding: 15px 12px;
        }

        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .sidebar-menu .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .sidebar-menu .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(3px);
        }

        .sidebar-menu .nav-link.active {
            color: #fff;
            background-color: #3498db;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
            font-weight: 600;
        }

        .sidebar-menu .logout-link {
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
        }

        .content-wrapper {
            margin-left: 240px;
            padding: 30px;
            min-height: 100vh;
            width: calc(100% - 240px);
        }

        .content {
            max-width: 100%;
            overflow-x: hidden;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            .content-wrapper {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
            padding: 18px 25px;
        }

        .card-body {
            background-color: #fff;
            border-radius: 0 0 8px 8px;
            padding: 25px;
        }

        .stats-card .card-body {
            background-color: transparent !important;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
            padding: 15px 12px;
            border-bottom: 2px solid #e9ecef;
        }

        .table td {
            padding: 15px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table tr:hover {
            background-color: rgba(52, 152, 219, 0.03);
        }

        .table img {
            max-width: 80px;
            height: auto;
            border-radius: 6px;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }

        .tox {
            border-radius: 4px !important;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-draft {
            background-color: #f39c12;
            color: #fff;
        }

        .status-published {
            background-color: #27ae60;
            color: #fff;
        }

        .status-pending {
            background-color: #3498db;
            color: #fff;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .visibility-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .visibility-public {
            background-color: #7f8c8d;
            color: #fff;
        }

        .visibility-private {
            background-color: #e74c3c;
            color: #fff;
        }

        .tag-badge {
            display: inline-block;
            padding: 3px 10px;
            background-color: #3498db;
            color: #fff;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 5px;
            margin-bottom: 4px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        /* Toastr 样式覆盖 */
        .toast-success {
            background-color: #27ae60 !important;
        }

        .toast-error {
            background-color: #e74c3c !important;
        }

        .toast-warning {
            background-color: #f39c12 !important;
        }

        .toast-info {
            background-color: #3498db !important;
        }
    </style>
    <?= $styles ?? '' ?>
    <?= $scripts ?? '' ?>
</head>

<body>
    <!-- 侧边栏 -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-blog"></i> 后台管理</h3>
        </div>
        <div class="sidebar-menu">
            <a class="nav-link <?= $activePage == 'dashboard' ? 'active' : '' ?>"
                href="<?= base_url('admin/dashboard') ?>">
                <i class="fas fa-tachometer-alt"></i> 仪表盘
            </a>
            <a class="nav-link <?= $activePage == 'posts' ? 'active' : '' ?>" href="<?= base_url('admin/posts') ?>">
                <i class="fas fa-file-alt"></i> 文章管理
            </a>
            <a class="nav-link <?= $activePage == 'categories' ? 'active' : '' ?>" href="<?= base_url('admin/categories') ?>">
                <i class="fas fa-folder"></i> 分类管理
            </a>
            <a class="nav-link <?= $activePage == 'tags' ? 'active' : '' ?>" href="<?= base_url('admin/tags') ?>">
                <i class="fas fa-tags"></i> 标签管理
            </a>
            <a class="nav-link <?= $activePage == 'comments' ? 'active' : '' ?>" href="<?= base_url('admin/comments') ?>">
                <i class="fas fa-comments"></i> 评论管理
            </a>
            <a class="nav-link <?= $activePage == 'media' ? 'active' : '' ?>" href="<?= base_url('admin/media') ?>">
                <i class="fas fa-image"></i> 媒体库
            </a>
            <a class="nav-link <?= $activePage == 'users' ? 'active' : '' ?>" href="<?= base_url('admin/users') ?>">
                <i class="fas fa-users"></i> 用户管理
            </a>
            <a class="nav-link <?= $activePage == 'links' ? 'active' : '' ?>" href="<?= base_url('admin/links') ?>">
                <i class="fas fa-link"></i> 友情链接
            </a>
            <a class="nav-link <?= $activePage == 'contacts' ? 'active' : '' ?>" href="<?= base_url('admin/contacts') ?>">
                <i class="fas fa-envelope"></i> 联系消息
            </a>
            <a class="nav-link <?= $activePage == 'settings' ? 'active' : '' ?>" href="<?= base_url('admin/settings') ?>">
                <i class="fas fa-cog"></i> 配置管理
            </a>
            <a class="nav-link logout-link" href="<?= base_url('admin/logout') ?>">
                <i class="fas fa-sign-out-alt"></i> 退出登录
            </a>
        </div>
    </div>

    <!-- 内容区域 -->
    <div class="content-wrapper">