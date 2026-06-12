<?php

use CodeIgniter\Router\RouteCollection;

/**
 * 路由配置文件
 * 
 * 本文件定义了应用的所有路由规则，采用模块化分组管理：
 * 1. 前台路由 - 面向访客的博客页面
 * 2. API路由 - RESTful接口，用于前后端分离的数据交互
 * 3. 后台路由 - 管理员管理面板（需认证）
 * 
 * @var RouteCollection $routes
 */

// ==================== 前台路由 ====================

// 默认路由 - 首页
$routes->get('/', 'BlogController::index');

// 博客相关路由
$routes->get('post/(:segment)', 'BlogController::post/$1');         // 文章详情页
$routes->get('category/(:segment)', 'BlogController::category/$1'); // 分类列表页
$routes->get('tag/(:segment)', 'BlogController::tag/$1');           // 标签列表页
$routes->get('archive/(:num)/(:num)', 'BlogController::archive/$1/$2'); // 归档页
$routes->post('comment', 'BlogController::submitComment');          // 提交评论
$routes->get('search', 'BlogController::search');                   // 搜索页（GET）
$routes->post('search', 'BlogController::search');                  // 搜索页（POST）

// 用户认证路由
$routes->get('home/login', 'AuthController::login');                // 登录页面
$routes->post('home/login', 'AuthController::doLogin');             // 执行登录
$routes->get('home/register', 'AuthController::register');          // 注册页面
$routes->post('home/register', 'AuthController::doRegister');       // 执行注册
$routes->get('home/logout', 'AuthController::logout');              // 退出登录
$routes->get('home/profile', 'AuthController::profile');            // 用户资料页
$routes->post('home/updateProfile', 'AuthController::updateProfile'); // 更新资料
$routes->get('home/changePassword', 'AuthController::changePassword'); // 修改密码页
$routes->post('home/changePassword', 'AuthController::changePassword'); // 执行修改密码
$routes->get('home/forgotPassword', 'AuthController::forgotPassword'); // 忘记密码页
$routes->post('home/forgotPassword', 'AuthController::forgotPassword'); // 发送重置邮件
$routes->get('home/resetPassword', 'AuthController::resetPassword');   // 重置密码页
$routes->post('home/resetPassword', 'AuthController::resetPassword');  // 执行重置密码

// 静态页面路由
$routes->get('about', 'PageController::about');                     // 关于页
$routes->get('contact', 'PageController::contact');                 // 联系页
$routes->post('contact', 'PageController::submitContact');          // 提交联系表单
$routes->get('rss', 'PageController::rss');                         // RSS订阅
$routes->get('sitemap', 'PageController::sitemap');                 // 站点地图

// 验证码路由
$routes->get('captcha', 'Home::captcha');                           // 获取验证码

// ==================== API路由（RESTful前后端分离） ====================
/**
 * API路由组说明：
 * - 所有API接口均位于 /api/* 路径下
 * - 采用RESTful风格设计，使用资源路由(resource)
 * - 前端通过AJAX调用，实现无感加载
 * - 统一响应格式：{success: boolean, data: mixed, message: string}
 */
$routes->group('api', ['filter' => 'auth'], function ($routes) {
    // 文章 API - 完整CRUD + 批量操作
    $routes->resource('posts', ['controller' => 'Api\PostController', 'except' => ['new', 'edit']]);
    $routes->post('posts/batch', 'Api\PostController::batch');       // 批量操作（发布/草稿/删除）

    $routes->get('tags/hot', 'Api\TagController::hot'); // 热门标签
    // 分类 API - 完整CRUD
    $routes->resource('categories', ['controller' => 'Api\CategoryController', 'except' => ['new', 'edit']]);

    // 标签 API - 完整CRUD
    $routes->resource('tags', ['controller' => 'Api\TagController', 'except' => ['new', 'edit']]);

    // 评论 API - 完整CRUD + 审核操作
    $routes->resource('comments', ['controller' => 'Api\CommentController', 'except' => ['new', 'edit']]);
    $routes->post('comments/approve/(:num)', 'Api\CommentController::approve/$1'); // 审核评论

    // 链接 API - 完整CRUD
    $routes->resource('links', ['controller' => 'Api\LinkController', 'except' => ['new', 'edit']]);

    // 用户 API - 完整CRUD
    $routes->resource('users', ['controller' => 'Api\UserController', 'except' => ['new', 'edit']]);

    // 联系消息 API - 列表/查看/处理/删除（禁用创建接口）
    $routes->resource('contacts', ['controller' => 'Api\ContactController', 'except' => ['new', 'edit', 'create']]);
    $routes->post('contacts/process/(:num)', 'Api\ContactController::process/$1'); // 处理消息

    // 媒体 API - 完整CRUD + 批量操作
    $routes->get('media', 'Api\MediaController::index');
    $routes->get('media/(:num)', 'Api\MediaController::show/$1');
    $routes->put('media/(:num)', 'Api\MediaController::update/$1');
    $routes->post('media/upload', 'Api\MediaController::upload');
    $routes->delete('media/(:num)', 'Api\MediaController::delete/$1');
    $routes->post('media/batch-delete', 'Api\MediaController::batchDelete');

    // 设置 API - 列表/查看/更新
    $routes->get('settings', 'Api\SettingController::index');
    $routes->get('settings/(:num)', 'Api\SettingController::show/$1');
    $routes->put('settings/(:num)', 'Api\SettingController::update/$1');

    // 仪表盘 API - 统计数据
    $routes->get('dashboard', 'Api\DashboardController::index');
});

// ==================== 后台管理路由 ====================
/**
 * 后台路由组说明：
 * - 所有后台路由均位于 /admin/* 路径下
 * - 需要管理员权限认证（auth:admin过滤器）
 * - 部分列表页使用AJAX视图，实现无感加载
 */
$routes->group('admin', ['filter' => 'auth:admin'], function ($routes) {
    // Dashboard - 仪表盘
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Posts - 文章管理（使用AJAX视图）
    $routes->get('posts', 'Admin\PostController::index');            // 列表页
    $routes->post('posts', 'Admin\PostController::index');           // 搜索/筛选
    $routes->get('posts/create', 'Admin\PostController::create');    // 创建页
    $routes->post('posts/store', 'Admin\PostController::store');     // 保存创建
    $routes->get('posts/(:num)/edit', 'Admin\PostController::edit/$1'); // 编辑页
    $routes->put('posts/(:num)', 'Admin\PostController::update/$1'); // 更新文章
    $routes->delete('posts/(:num)', 'Admin\PostController::delete/$1'); // 删除文章
    $routes->post('posts/upload', 'Admin\PostController::upload');   // 上传图片
    $routes->post('posts/auto-save', 'Admin\PostController::autoSave'); // 自动保存
    $routes->get('posts/publish-scheduled', 'Admin\PostController::publishScheduled'); // 定时发布

    // Post Revisions - 文章版本管理
    $routes->get('posts/(:num)/revisions', 'Admin\PostController::revisions/$1'); // 版本列表
    $routes->get('posts/(:num)/revision/(:num)', 'Admin\PostController::revisionDetail/$1/$2'); // 版本详情
    $routes->post('posts/(:num)/restore-revision', 'Admin\PostController::restoreRevision/$1'); // 恢复版本

    // Categories - 分类管理（使用AJAX视图）
    $routes->get('categories', 'Admin\CategoryController::index');   // 列表页
    $routes->get('categories/create', 'Admin\CategoryController::create'); // 创建页
    $routes->post('categories', 'Admin\CategoryController::store');  // 保存创建
    $routes->get('categories/(:num)/edit', 'Admin\CategoryController::edit/$1'); // 编辑页
    $routes->put('categories/(:num)', 'Admin\CategoryController::update/$1'); // 更新分类
    $routes->delete('categories/(:num)', 'Admin\CategoryController::delete/$1'); // 删除分类

    // Tags - 标签管理（使用AJAX视图）
    $routes->get('tags', 'Admin\TagController::index');              // 列表页
    $routes->get('tags/create', 'Admin\TagController::create');      // 创建页
    $routes->post('tags', 'Admin\TagController::store');             // 保存创建
    $routes->get('tags/(:num)/edit', 'Admin\TagController::edit/$1'); // 编辑页
    $routes->put('tags/(:num)', 'Admin\TagController::update/$1');   // 更新标签
    $routes->delete('tags/(:num)', 'Admin\TagController::delete/$1'); // 删除标签

    // Comments - 评论管理（使用AJAX视图）
    $routes->get('comments', 'Admin\CommentController::index');      // 列表页
    $routes->get('comments/create', 'Admin\CommentController::create'); // 创建页
    $routes->post('comments', 'Admin\CommentController::store');     // 保存评论
    $routes->get('comments/(:num)/edit', 'Admin\CommentController::edit/$1'); // 编辑页
    $routes->put('comments/(:num)', 'Admin\CommentController::update/$1'); // 更新评论
    $routes->delete('comments/(:num)', 'Admin\CommentController::delete/$1'); // 删除评论
    $routes->get('comments/pending', 'Admin\CommentController::pending'); // 待审核列表
    $routes->get('comments/approved', 'Admin\CommentController::approved'); // 已审核列表
    $routes->get('comments/spam', 'Admin\CommentController::spam');  // 垃圾评论列表
    $routes->post('comments/batchAction', 'Admin\CommentController::batchAction'); // 批量操作

    // Users - 用户管理（使用AJAX视图）
    $routes->get('users', 'Admin\UserController::index');            // 列表页
    $routes->get('users/create', 'Admin\UserController::create');    // 创建页
    $routes->post('users', 'Admin\UserController::store');           // 保存创建
    $routes->get('users/(:num)/edit', 'Admin\UserController::edit/$1'); // 编辑页
    $routes->put('users/(:num)', 'Admin\UserController::update/$1'); // 更新用户
    $routes->delete('users/(:num)', 'Admin\UserController::delete/$1'); // 删除用户

    // Media - 媒体管理（前后端分离版本）
    $routes->get('media', 'Admin\MediaController::index');            // 媒体库首页（简化版AJAX视图）
    $routes->post('media/upload', 'Admin\MediaController::upload');   // 上传文件（兼容旧版）
    $routes->post('media/upload-multiple', 'Admin\MediaController::uploadMultiple'); // 批量上传（兼容旧版）
    $routes->get('media/detail/(:num)', 'Admin\MediaController::detail/$1'); // 文件详情（兼容旧版）
    $routes->post('media/edit/(:num)', 'Admin\MediaController::edit/$1'); // 编辑文件信息（兼容旧版）
    $routes->post('media/delete/(:num)', 'Admin\MediaController::delete/$1'); // 删除文件（兼容旧版）
    $routes->post('media/batch-delete', 'Admin\MediaController::batchDelete'); // 批量删除（兼容旧版）
    $routes->get('media/library', 'Admin\MediaController::library'); // 媒体库列表（兼容旧版）
    $routes->get('media/load', 'Admin\MediaController::load');       // 加载媒体文件（兼容旧版）
    $routes->get('media/form', 'Admin\MediaController::indexForm');  // 完整版表单视图（备用）

    // Links - 友情链接管理（使用AJAX视图）
    $routes->get('links', 'Admin\LinkController::index');            // 列表页
    $routes->get('links/create', 'Admin\LinkController::create');    // 创建页
    $routes->post('links', 'Admin\LinkController::store');           // 保存创建
    $routes->get('links/(:num)/edit', 'Admin\LinkController::edit/$1'); // 编辑页
    $routes->put('links/(:num)', 'Admin\LinkController::update/$1'); // 更新链接
    $routes->delete('links/(:num)', 'Admin\LinkController::delete/$1'); // 删除链接

    // Contact Messages - 联系消息管理（使用AJAX视图）
    $routes->get('contacts', 'Admin\ContactController::index');      // 列表页
    $routes->get('contacts/show/(:num)', 'Admin\ContactController::show/$1'); // 查看详情
    $routes->post('contacts/process/(:num)', 'Admin\ContactController::process/$1'); // 处理消息
    $routes->delete('contacts/delete/(:num)', 'Admin\ContactController::delete/$1'); // 删除消息

    // Settings - 设置管理
    $routes->get('settings', 'Admin\SettingController::index');      // 设置列表
    $routes->get('settings/edit/(:num)', 'Admin\SettingController::edit/$1'); // 编辑设置
    $routes->post('settings/update/(:num)', 'Admin\SettingController::update/$1'); // 更新设置

    // 退出登录
    $routes->get('logout', 'AuthController::logout');
});