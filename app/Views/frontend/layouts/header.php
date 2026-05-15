<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <?php if (isset($post)): ?>
        <!-- 文章页面SEO -->
        <title><?= esc($post['meta_title'] ?? $post['title']) ?> - <?= esc(env('app.siteName', '博客系统')) ?></title>
        <meta name="description"
            content="<?= esc($post['meta_description'] ?? $post['excerpt'] ?? mb_substr(strip_tags($post['content']), 0, 200)) ?>">
        <meta name="keywords" content="<?= esc($post['meta_keywords'] ?? '') ?>">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="article">
        <meta property="og:url" content="<?= current_url() ?>">
        <meta property="og:title" content="<?= esc($post['meta_title'] ?? $post['title']) ?>">
        <meta property="og:description"
            content="<?= esc($post['meta_description'] ?? $post['excerpt'] ?? mb_substr(strip_tags($post['content']), 0, 200)) ?>">
        <?php if (!empty($post['featured_image'])): ?>
            <meta property="og:image" content="<?= base_url('uploads/' . $post['featured_image']) ?>">
        <?php endif; ?>
        <meta property="article:published_time" content="<?= $post['published_at'] ?? '' ?>">
        <meta property="article:author" content="<?= esc($post['author_name'] ?? '') ?>">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= esc($post['meta_title'] ?? $post['title']) ?>">
        <meta name="twitter:description"
            content="<?= esc($post['meta_description'] ?? $post['excerpt'] ?? mb_substr(strip_tags($post['content']), 0, 200)) ?>">
        <?php if (!empty($post['featured_image'])): ?>
            <meta name="twitter:image" content="<?= base_url('uploads/' . $post['featured_image']) ?>">
        <?php endif; ?>
    <?php else: ?>
        <!-- 默认SEO -->
        <title><?= esc($title ?? env('app.siteName', '博客系统')) ?></title>
        <meta name="description" content="<?= esc($metaDescription ?? env('app.siteDescription', '欢迎来到我们的博客')) ?>">
        <meta name="keywords" content="<?= esc($metaKeywords ?? env('app.siteKeywords', '博客,文章,技术')) ?>">
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Robots -->
    <meta name="robots" content="index, follow">

    <!-- Author -->
    <?php if (isset($post)): ?>
        <meta name="author" content="<?= esc($post['author_name'] ?? '') ?>">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="<?= cdn_asset('css/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= cdn_asset('css/all.min.css') ?>">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #5b6abf;
            --primary-dark: #4a5699;
            --secondary-color: #6c757d;
            --accent-color: #7c8bc5;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --bg-light: #f5f6f8;
            --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            --card-hover-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            --gradient-primary: linear-gradient(135deg, #5b6abf 0%, #2c3e50 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            background-color: #f5f6f8;
            min-height: 100vh;
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* 导航栏样式优化 */
        .navbar {
            background: #fff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 15px 0;
            transition: all 0.3s ease;
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: #fff !important;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: var(--primary-dark) !important;
        }

        .navbar-brand i {
            color: var(--primary-color);
        }

        .nav-link {
            font-weight: 600;
            color: var(--text-dark) !important;
            margin: 0 8px;
            padding: 8px 16px !important;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(91, 106, 191, 0.08);
        }

        .nav-link i {
            margin-right: 6px;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background-color: var(--primary-color);
            color: #fff;
        }

        .dropdown-item i {
            margin-right: 8px;
            width: 16px;
        }

        .dropdown-divider {
            margin: 8px 0;
            border-top: 1px solid #e2e8f0;
        }

        /* 搜索框样式 */
        .search-form {
            position: relative;
        }

        .search-form .form-control {
            border-radius: 20px;
            border: 1px solid #dcdde1;
            padding: 8px 40px 8px 20px;
            transition: all 0.3s ease;
            background: #fff;
        }

        .search-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.15rem rgba(91, 106, 191, 0.15);
        }

        .search-form .btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 50%;
            width: 34px;
            height: 34px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            border: none;
            color: #fff;
            transition: all 0.3s ease;
        }

        .search-form .btn:hover {
            background-color: var(--primary-dark);
        }

        /* 卡片样式优化 */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .card-title {
            margin-bottom: 0.75rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .card-title a {
            color: var(--text-dark);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .card-title a:hover {
            color: var(--primary-color);
        }

        .card-text {
            color: var(--text-light);
            line-height: 1.8;
        }

        .card-footer {
            background: transparent;
            border-top: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        /* 侧边栏卡片 */
        .sidebar-card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
            margin-bottom: 20px;
        }

        .sidebar-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-hover-shadow);
        }

        .sidebar-card .card-header {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            padding: 15px 20px;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .sidebar-card .card-body {
            padding: 20px;
        }

        .sidebar-card .list-group-item {
            padding: 12px 20px;
            border: none;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar-card .list-group-item:last-child {
            border-bottom: none;
        }

        .sidebar-card .list-group-item:hover {
            background-color: rgba(91, 106, 191, 0.05);
            padding-left: 25px;
            color: var(--primary-color);
        }

        /* 徽章样式 */
        .badge {
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 600;
            margin-right: 6px;
            margin-bottom: 6px;
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: translateY(-1px);
        }

        .badge-primary {
            background-color: var(--primary-color);
        }

        .badge-secondary {
            background-color: var(--secondary-color);
        }

        .badge-success {
            background-color: #27ae60;
        }

        /* 按钮样式 */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 24px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: #fff;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            color: #fff;
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        .btn-success {
            background-color: #27ae60;
            color: #fff;
        }

        /* 分页样式 */
        .pagination .page-link {
            border-radius: 8px;
            margin: 0 4px;
            border: none;
            color: var(--text-dark);
            font-weight: 600;
            padding: 10px 18px;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 2px 8px rgba(91, 106, 191, 0.3);
        }

        .pagination .page-link:hover {
            background-color: rgba(91, 106, 191, 0.08);
            color: var(--primary-color);
        }

        /* 页脚样式 */
        .footer {
            background-color: #2c3e50;
            color: #fff;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        /* 链接样式 */
        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        a:hover {
            color: var(--primary-dark);
        }

        .hover-primary {
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        .hover-primary:hover {
            color: var(--primary-color) !important;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .navbar {
                padding: 10px 0;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .nav-link {
                padding: 6px 12px !important;
                margin: 0 4px;
            }

            .card {
                margin-bottom: 20px;
            }

            .footer {
                padding: 30px 0 15px;
            }
        }

        /* 动画效果 */
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 图片样式 */
        .img-fluid {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .img-fluid:hover {
            transform: scale(1.01);
            box-shadow: var(--card-hover-shadow);
        }
    </style>
</head>

<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-blog mr-2"></i>博客系统
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>">
                            <i class="fas fa-home"></i>首页
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-folder"></i>分类
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories ?? [] as $category): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('category/' . $category['slug']) ?>">
                                        <?php if (!empty($category['icon'])): ?>
                                            <i class="<?= $category['icon'] ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-folder"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tags"></i>标签
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($tags ?? [] as $tag): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('tag/' . $tag['slug']) ?>">
                                        <i class="fas fa-tag"></i><?= htmlspecialchars($tag['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('about') ?>">
                            <i class="fas fa-info-circle"></i>关于我们
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('contact') ?>">
                            <i class="fas fa-envelope"></i>联系我们
                        </a>
                    </li>
                </ul>

                <!-- 搜索框 -->
                <form class="search-form my-2 my-lg-0 me-4" action="<?= base_url('search') ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= csrf_hash() ?>">
                    <input class="form-control" type="search" placeholder="搜索文章..." name="keyword">
                    <button class="btn" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <!-- 用户菜单 -->
                <ul class="navbar-nav">
                    <?php if (session()->get('logged_in')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i><?= htmlspecialchars(session()->get('username')) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('/home/profile') ?>">
                                        <i class="fas fa-user-cog"></i>个人资料
                                    </a>
                                </li>
                                <?php if (session()->get('role') === 'admin'): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/dashboard') ?>">
                                            <i class="fas fa-tachometer-alt"></i>控制面板
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/posts') ?>">
                                            <i class="fas fa-edit"></i>管理文章
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/categories') ?>">
                                            <i class="fas fa-folder"></i>管理分类
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/tags') ?>">
                                            <i class="fas fa-tags"></i>管理标签
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('/home/logout') ?>">
                                        <i class="fas fa-sign-out-alt"></i>退出登录
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/home/login') ?>">
                                <i class="fas fa-sign-in-alt"></i>登录
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/home/register') ?>">
                                <i class="fas fa-user-plus"></i>注册
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>

</html>