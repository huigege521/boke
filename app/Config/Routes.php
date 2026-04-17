<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ==================== 前台路由 ====================

// 默认路由 - 首页
$routes->get('/', 'BlogController::index');

// 博客相关路由
$routes->get('post/(:segment)', 'BlogController::post/$1');
$routes->get('category/(:segment)', 'BlogController::category/$1');
$routes->get('tag/(:segment)', 'BlogController::tag/$1');
$routes->get('archive/(:num)/(:num)', 'BlogController::archive/$1/$2');
$routes->post('comment', 'BlogController::submitComment');
$routes->get('search', 'BlogController::search');
$routes->post('search', 'BlogController::search');

// 用户认证路由
$routes->get('home/login', 'AuthController::login');
$routes->post('home/login', 'AuthController::doLogin');
$routes->get('home/register', 'AuthController::register');
$routes->post('home/register', 'AuthController::doRegister');
$routes->get('home/logout', 'AuthController::logout');
$routes->get('home/profile', 'AuthController::profile');
$routes->post('home/updateProfile', 'AuthController::updateProfile');
$routes->get('home/changePassword', 'AuthController::changePassword');
$routes->post('home/changePassword', 'AuthController::changePassword');
$routes->get('home/forgotPassword', 'AuthController::forgotPassword');
$routes->post('home/forgotPassword', 'AuthController::forgotPassword');

// 静态页面路由
$routes->get('about', 'PageController::about');
$routes->get('contact', 'PageController::contact');
$routes->post('contact', 'PageController::submitContact');
$routes->get('rss', 'PageController::rss');
$routes->get('sitemap', 'PageController::sitemap');

// 验证码路由
$routes->get('captcha', 'Home::captcha');

// ==================== API路由 ====================
$routes->group('api', function ($routes) {
    // 文章 API
    $routes->resource('posts', ['controller' => 'Api\PostController']);

    // 分类 API
    $routes->resource('categories', ['controller' => 'Api\CategoryController']);

    // 标签 API
    $routes->resource('tags', ['controller' => 'Api\TagController']);
});

// ==================== 后台管理路由 ====================
$routes->group('admin', ['filter' => 'auth:admin'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Posts - 注意：GET和POST都指向index方法，以支持批量操作
    $routes->get('posts', 'Admin\PostController::index');
    $routes->post('posts', 'Admin\PostController::index');
    $routes->get('posts/create', 'Admin\PostController::create');
    $routes->post('posts/store', 'Admin\PostController::store');
    $routes->get('posts/(:num)/edit', 'Admin\PostController::edit/$1');
    $routes->put('posts/(:num)', 'Admin\PostController::update/$1');
    $routes->delete('posts/(:num)', 'Admin\PostController::delete/$1');
    $routes->post('posts/upload', 'Admin\PostController::upload');
    $routes->post('posts/auto-save', 'Admin\PostController::autoSave');
    $routes->get('posts/publish-scheduled', 'Admin\PostController::publishScheduled');

    // Post Revisions
    $routes->get('posts/(:num)/revisions', 'Admin\PostController::revisions/$1');
    $routes->get('posts/(:num)/revision/(:num)', 'Admin\PostController::revisionDetail/$1/$2');
    $routes->post('posts/(:num)/restore-revision', 'Admin\PostController::restoreRevision/$1');

    // Categories
    $routes->get('categories', 'Admin\CategoryController::index');
    $routes->get('categories/create', 'Admin\CategoryController::create');
    $routes->post('categories', 'Admin\CategoryController::store');
    $routes->get('categories/(:num)/edit', 'Admin\CategoryController::edit/$1');
    $routes->put('categories/(:num)', 'Admin\CategoryController::update/$1');
    $routes->delete('categories/(:num)', 'Admin\CategoryController::delete/$1');

    // Tags
    $routes->get('tags', 'Admin\TagController::index');
    $routes->get('tags/create', 'Admin\TagController::create');
    $routes->post('tags', 'Admin\TagController::store');
    $routes->get('tags/(:num)/edit', 'Admin\TagController::edit/$1');
    $routes->put('tags/(:num)', 'Admin\TagController::update/$1');
    $routes->delete('tags/(:num)', 'Admin\TagController::delete/$1');

    // Comments
    $routes->get('comments', 'Admin\CommentController::index');
    $routes->get('comments/create', 'Admin\CommentController::create');
    $routes->post('comments', 'Admin\CommentController::store');
    $routes->get('comments/(:num)/edit', 'Admin\CommentController::edit/$1');
    $routes->put('comments/(:num)', 'Admin\CommentController::update/$1');
    $routes->delete('comments/(:num)', 'Admin\CommentController::delete/$1');
    $routes->get('comments/pending', 'Admin\CommentController::pending');
    $routes->get('comments/approved', 'Admin\CommentController::approved');
    $routes->get('comments/spam', 'Admin\CommentController::spam');
    $routes->post('comments/batchAction', 'Admin\CommentController::batchAction');

    // Users
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/create', 'Admin\UserController::create');
    $routes->post('users', 'Admin\UserController::store');
    $routes->get('users/(:num)/edit', 'Admin\UserController::edit/$1');
    $routes->put('users/(:num)', 'Admin\UserController::update/$1');
    $routes->delete('users/(:num)', 'Admin\UserController::delete/$1');

    // Media
    $routes->get('media', 'Admin\MediaController::index');
    $routes->post('media/upload', 'Admin\MediaController::upload');
    $routes->post('media/upload-multiple', 'Admin\MediaController::uploadMultiple');
    $routes->get('media/detail/(:num)', 'Admin\MediaController::detail/$1');
    $routes->post('media/edit/(:num)', 'Admin\MediaController::edit/$1');
    $routes->post('media/delete/(:num)', 'Admin\MediaController::delete/$1');
    $routes->post('media/batch-delete', 'Admin\MediaController::batchDelete');
    $routes->get('media/library', 'Admin\MediaController::library');
    $routes->get('media/load', 'Admin\MediaController::load');

    // Links
    $routes->get('links', 'Admin\LinkController::index');
    $routes->get('links/create', 'Admin\LinkController::create');
    $routes->post('links', 'Admin\LinkController::store');
    $routes->get('links/edit/(:num)', 'Admin\LinkController::edit/$1');
    $routes->put('links/(:num)', 'Admin\LinkController::update/$1');
    $routes->delete('links/delete/(:num)', 'Admin\LinkController::delete/$1');

    // Contact Messages
    $routes->get('contacts', 'Admin\ContactController::index');
    $routes->get('contacts/show/(:num)', 'Admin\ContactController::show/$1');
    $routes->post('contacts/process/(:num)', 'Admin\ContactController::process/$1');
    $routes->delete('contacts/delete/(:num)', 'Admin\ContactController::delete/$1');

    // Settings
    $routes->get('settings', 'Admin\SettingController::index');
    $routes->get('settings/edit/(:num)', 'Admin\SettingController::edit/$1');
    $routes->post('settings/update/(:num)', 'Admin\SettingController::update/$1');

    // 退出
    $routes->get('logout', 'AuthController::logout');
});