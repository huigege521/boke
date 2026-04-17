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
    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            padding: 20px;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            width: 16.666667%;
        }

        .content-wrapper {
            margin-left: 16.666667%;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            .content-wrapper {
                margin-left: 0;
            }
        }

        .sidebar h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            color: #fff;
            background-color: #495057;
            transform: translateX(5px);
        }

        .sidebar a.active {
            color: #fff;
            background-color: #007bff;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        }

        .content {
            padding: 30px;
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            background-color: #fff;
            border-radius: 0 0 10px 10px;
        }

        .stats-card .card-body {
            background-color: transparent !important;
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
            padding: 12px;
            border-bottom: 2px solid #e9ecef;
        }

        .table td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .table img {
            max-width: 80px;
            height: auto;
            border-radius: 4px;
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
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .tox {
            border-radius: 4px !important;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
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
    </style>
    <?= $styles ?? '' ?>
    <?= $scripts ?? '' ?>
</head>

<body>
    <!-- 侧边栏 -->
    <div class="sidebar">
        <h3 class="mb-4">后台管理</h3>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'dashboard' ? 'active' : '' ?>" href="/admin/dashboard">仪表盘</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'posts' ? 'active' : '' ?>" href="/admin/posts">文章管理</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'categories' ? 'active' : '' ?>" href="/admin/categories">分类管理</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'tags' ? 'active' : '' ?>" href="/admin/tags">标签管理</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'comments' ? 'active' : '' ?>" href="/admin/comments">评论管理</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'media' ? 'active' : '' ?>" href="/admin/media">媒体库</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'users' ? 'active' : '' ?>" href="/admin/users">用户管理</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'links' ? 'active' : '' ?>" href="/admin/links">友情链接</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'contacts' ? 'active' : '' ?>" href="/admin/contacts">联系消息</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?= $activePage == 'settings' ? 'active' : '' ?>" href="/admin/settings">配置管理</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link " href="/admin/logout"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
            </li>
        </ul>
    </div>

    <!-- 内容区域 -->
    <div class="content-wrapper">
        <div class="content">
            <h1>
                <?= $pageTitle ?>
            </h1>

            <!-- 消息提示 -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                </div>
            <?php endif; ?>