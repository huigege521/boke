<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? '博客系统' ?></title>
    <!-- Bootstrap CSS -->
    <link href="<?= base_url('css/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('css/all.min.css') ?>">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card {
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 1.25rem;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            margin-bottom: 0.75rem;
        }

        .sidebar-card {
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .sidebar-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-card .card-body {
            padding: 1.25rem;
        }

        .sidebar-card .list-group-item {
            padding: 1rem 1.25rem;
            border: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sidebar-card .list-group-item:last-child {
            border-bottom: none;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .sidebar-card .card-header {
            padding: 1rem 1.25rem;
        }

        .footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .social-links a {
            color: var(--secondary-color);
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--primary-color);
        }

        .hover-primary {
            color: #333;
            transition: color 0.3s ease;
        }

        .hover-primary:hover {
            color: var(--primary-color) !important;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>"><i class="fas fa-blog mr-2"></i>博客系统</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>"><i class="fas fa-home mr-1"></i>首页</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-folder mr-1"></i>分类
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories ?? [] as $category): ?>
                                <li><a class="dropdown-item" href="/category/<?= $category['slug'] ?>">
                                        <?php if (!empty($category['icon'])): ?>
                                            <i class="<?= $category['icon'] ?> mr-2"></i>
                                        <?php else: ?>
                                            <i class="fas fa-folder mr-2"></i>
                                        <?php endif; ?>
                                        <?= $category['name'] ?>
                                    </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tags mr-1"></i>标签
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($tags ?? [] as $tag): ?>
                                <li><a class="dropdown-item" href="/tag/<?= $tag['slug'] ?>"><?= $tag['name'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('about') ?>"><i
                                class="fas fa-info-circle mr-1"></i>关于我们</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('contact') ?>"><i
                                class="fas fa-envelope mr-1"></i>联系我们</a>
                    </li>
                </ul>
                <form class="d-flex my-2 my-lg-0 me-4" action="/search" method="post">
                    <input type="hidden" name="csrf_token" value="<?= csrf_hash() ?>">
                    <input class="form-control me-2" type="search" placeholder="搜索文章" name="keyword"
                        style="width: 200px;">
                    <button class="btn btn-outline-success" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <ul class="navbar-nav">
                    <?php if (session()->get('logged_in')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user mr-1"></i><?= session()->get('username') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a class="dropdown-item" href="<?= base_url('/home/profile') ?>"><i
                                            class="fas fa-user-cog mr-1"></i>个人资料</a></li>
                                <?php if (session()->get('role') === 'admin'): ?>
                                    <li class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/dashboard') ?>"><i
                                                class="fas fa-tachometer-alt mr-1"></i>控制面板</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/posts') ?>"><i
                                                class="fas fa-edit mr-1"></i>管理文章</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/categories') ?>"><i
                                                class="fas fa-folder mr-1"></i>管理分类</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/tags') ?>"><i
                                                class="fas fa-tags mr-1"></i>管理标签</a></li>
                                    <li class="dropdown-divider"></li>
                                <?php else: ?>
                                    <li class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= base_url('/home/logout') ?>"><i
                                            class="fas fa-sign-out-alt mr-1"></i>退出登录</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/home/login') ?>"><i
                                    class="fas fa-sign-in-alt mr-1"></i>登录</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/home/register') ?>"><i
                                    class="fas fa-user-plus mr-1"></i>注册</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>