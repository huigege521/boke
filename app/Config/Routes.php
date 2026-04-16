<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Home::index');

$routes->get('home/login', 'Home::login');
$routes->post('home/login', 'Home::login');
$routes->get('home/register', 'Home::register');
$routes->post('home/register', 'Home::register');
$routes->get('home/logout', 'Home::logout');
$routes->get('home/profile', 'Home::profile');
$routes->post('home/profile', 'Home::profile');
$routes->get('home/forgotPassword', 'Home::forgotPassword');
$routes->post('home/forgotPassword', 'Home::forgotPassword');
$routes->post('home/updateProfile', 'Home::updateProfile');
$routes->post('home/updateProfile', 'Home::updateProfile');
$routes->get('home/changePassword', 'Home::changePassword');
$routes->post('home/changePassword', 'Home::changePassword');
$routes->get('post/(:segment)', 'Home::post/$1');
$routes->get('category/(:segment)', 'Home::category/$1');
$routes->get('tag/(:segment)', 'Home::tag/$1');
$routes->get('archive/(:num)/(:num)', 'Home::archive/$1/$2');
$routes->post('comment', 'Home::addComment');
$routes->post('search', 'Home::search');
$routes->get('rss', 'Home::rss');
$routes->get('sitemap', 'Home::sitemap');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');
$routes->post('contact', 'Home::contact');

// 验证码路由
$routes->get('captcha', 'Home::captcha');

// API routes
$routes->group('api', function ($routes) {
    // 文章 API
    $routes->resource('posts', ['controller' => 'Api\PostController']);

    // 分类 API
    $routes->resource('categories', ['controller' => 'Api\CategoryController']);

    // 标签 API
    $routes->resource('tags', ['controller' => 'Api\TagController']);
});

// Admin routes
$routes->group('admin', ['filter' => 'auth:admin'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Posts
    $routes->get('posts', 'Admin\PostController::index');
    $routes->get('posts/create', 'Admin\PostController::create');
    $routes->post('posts', 'Admin\PostController::store');
    $routes->get('posts/(:num)/edit', 'Admin\PostController::edit/$1');
    $routes->put('posts/(:num)', 'Admin\PostController::update/$1');
    $routes->delete('posts/(:num)', 'Admin\PostController::delete/$1');
    $routes->post('posts/upload', 'Admin\PostController::upload');
    $routes->post('posts/auto-save', 'Admin\PostController::autoSave');
    $routes->get('posts/publish-scheduled', 'Admin\PostController::publishScheduled');

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

    // Links
    $routes->get('links', 'Admin\LinkController::index');
    $routes->get('links/create', 'Admin\LinkController::create');
    $routes->post('links/store', 'Admin\LinkController::store');
    $routes->get('links/edit/(:num)', 'Admin\LinkController::edit/$1');
    $routes->post('links/update/(:num)', 'Admin\LinkController::update/$1');
    $routes->get('links/delete/(:num)', 'Admin\LinkController::delete/$1');

    // 联系消息
    $routes->get('contacts', 'Admin\ContactController::index');
    $routes->get('contacts/show/(:num)', 'Admin\ContactController::show/$1');
    $routes->post('contacts/process/(:num)', 'Admin\ContactController::process/$1');
    $routes->delete('contacts/delete/(:num)', 'Admin\ContactController::delete/$1');

    // 配置管理
    $routes->get('settings', 'Admin\SettingController::index');
    $routes->get('settings/edit/(:num)', 'Admin\SettingController::edit/$1');
    $routes->post('settings/update/(:num)', 'Admin\SettingController::update/$1');

    // 媒体库
    $routes->get('media', 'Admin\MediaController::index');
    $routes->post('media/upload', 'Admin\MediaController::upload');
    $routes->post('media/upload-multiple', 'Admin\MediaController::uploadMultiple');
    $routes->get('media/detail/(:num)', 'Admin\MediaController::detail/$1');
    $routes->post('media/edit/(:num)', 'Admin\MediaController::edit/$1');
    $routes->post('media/delete/(:num)', 'Admin\MediaController::delete/$1');
    $routes->post('media/batch-delete', 'Admin\MediaController::batchDelete');
    $routes->get('media/library', 'Admin\MediaController::library');
    $routes->get('media/load', 'Admin\MediaController::load');


    // 退出登录
    $routes->get('logout', 'Home::logout');
});
