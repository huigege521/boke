-- 博客系统数据库初始化脚本
-- 基于 CodeIgniter 4.6.3 和 PHP 8.1
-- 创建时间: 2024-01-01

-- 创建数据库（如果不存在）
CREATE DATABASE IF NOT EXISTS `codeigniter_blog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 使用数据库
USE `codeigniter_blog`;

-- -----------------------------
-- 表结构: users (用户表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `email` varchar(100) NOT NULL COMMENT '邮箱地址',
  `password` varchar(255) NOT NULL COMMENT '密码（哈希存储）',
  `name` varchar(50) NOT NULL COMMENT '真实姓名',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像URL',
  `bio` text DEFAULT NULL COMMENT '个人简介',
  `role` enum('admin','editor','user') NOT NULL DEFAULT 'user' COMMENT '用户角色',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '用户状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `last_login` datetime DEFAULT NULL COMMENT '最后登录时间',
  `reset_token` varchar(255) DEFAULT NULL COMMENT '密码重置令牌',
  `reset_token_expires` datetime DEFAULT NULL COMMENT '密码重置令牌过期时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- -----------------------------
-- 表结构: categories (分类表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `slug` varchar(50) NOT NULL COMMENT '分类别名（URL友好）',
  `description` text DEFAULT NULL COMMENT '分类描述',
  `parent_id` int(11) UNSIGNED DEFAULT NULL COMMENT '父分类ID（自关联）',
  `order` int(11) NOT NULL DEFAULT 0 COMMENT '排序权重',
  `posts_count` int(11) NOT NULL DEFAULT 0 COMMENT '分类下文章数量',
  `icon` varchar(255) DEFAULT NULL COMMENT '分类图标（Font Awesome 类名）',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `name` (`name`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分类表';

-- -----------------------------
-- 表结构: tags (标签表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `name` varchar(50) NOT NULL COMMENT '标签名称',
  `slug` varchar(50) NOT NULL COMMENT '标签别名（URL友好）',
  `description` text DEFAULT NULL COMMENT '标签描述',
  `posts_count` int(11) NOT NULL DEFAULT 0 COMMENT '标签下文章数量',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签表';

-- -----------------------------
-- 表结构: posts (文章表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `title` varchar(200) NOT NULL COMMENT '文章标题',
  `slug` varchar(200) NOT NULL COMMENT '文章别名（URL友好）',
  `content` text NOT NULL COMMENT '文章内容',
  `excerpt` text DEFAULT NULL COMMENT '文章摘要',
  `featured_image` varchar(255) DEFAULT NULL COMMENT '特色图片URL',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '作者ID（关联users表）',
  `category_id` int(11) UNSIGNED DEFAULT NULL COMMENT '分类ID（关联categories表）',
  `status` enum('draft','published','pending','scheduled') NOT NULL DEFAULT 'draft' COMMENT '文章状态',
  `visibility` enum('public','private') NOT NULL DEFAULT 'public' COMMENT '文章可见性',
  `views` int(11) NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `comments_count` int(11) NOT NULL DEFAULT 0 COMMENT '评论数量',
  `published_at` datetime DEFAULT NULL COMMENT '发布时间',
  `scheduled_at` datetime DEFAULT NULL COMMENT '定时发布时间',
  `auto_saved_at` datetime DEFAULT NULL COMMENT '自动保存时间',
  `auto_saved_content` text DEFAULT NULL COMMENT '自动保存的内容',
  -- SEO元数据字段
  `meta_title` varchar(200) DEFAULT NULL COMMENT 'SEO标题（页面title标签）',
  `meta_description` varchar(500) DEFAULT NULL COMMENT 'SEO描述（meta description）',
  `meta_keywords` varchar(500) DEFAULT NULL COMMENT 'SEO关键词（meta keywords）',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `visibility` (`visibility`),
  KEY `published_at` (`published_at`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章表';

-- -----------------------------
-- 表结构: post_tags (文章标签关联表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `post_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `post_id` int(11) UNSIGNED NOT NULL COMMENT '文章ID（关联posts表）',
  `tag_id` int(11) UNSIGNED NOT NULL COMMENT '标签ID（关联tags表）',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_id_tag_id` (`post_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `post_tags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `post_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章标签关联表';

-- -----------------------------
-- 表结构: comments (评论表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `post_id` int(11) UNSIGNED NOT NULL COMMENT '文章ID（关联posts表）',
  `user_id` int(11) UNSIGNED DEFAULT NULL COMMENT '用户ID（关联users表，游客评论为null）',
  `parent_id` int(11) UNSIGNED DEFAULT NULL COMMENT '父评论ID（自关联，用于回复）',
  `content` text NOT NULL COMMENT '评论内容',
  `author_name` varchar(50) DEFAULT NULL COMMENT '游客评论者姓名',
  `author_email` varchar(100) DEFAULT NULL COMMENT '游客评论者邮箱',
  `author_ip` varchar(45) NOT NULL COMMENT '评论者IP地址',
  `status` enum('approved','pending','spam') NOT NULL DEFAULT 'pending' COMMENT '评论状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论表';

-- -----------------------------
-- 插入测试数据
-- -----------------------------

-- 插入管理员用户
INSERT INTO `users` (`username`, `email`, `password`, `name`, `role`, `status`, `created_at`, `updated_at`) VALUES
('admin', 'admin@example.com', '$2y$10$7eHx5aZvZ9y8v8a8a8a8a8a8a8a8a8a8a8a8a8a8a8a8a8a8a8a', '管理员', 'admin', 'active', NOW(), NOW());

-- 插入默认分类
INSERT INTO `categories` (`name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
('技术', 'tech', '技术相关文章', NOW(), NOW()),
('生活', 'life', '生活随笔', NOW(), NOW()),
('学习', 'study', '学习笔记', NOW(), NOW());

-- 插入默认标签
INSERT INTO `tags` (`name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
('PHP', 'php', 'PHP编程语言', NOW(), NOW()),
('CodeIgniter', 'codeigniter', 'CodeIgniter框架', NOW(), NOW()),
('MySQL', 'mysql', 'MySQL数据库', NOW(), NOW()),
('前端', 'frontend', '前端开发', NOW(), NOW()),
('后端', 'backend', '后端开发', NOW(), NOW());

-- 插入测试文章
INSERT INTO `posts` (`title`, `slug`, `content`, `excerpt`, `user_id`, `category_id`, `status`, `visibility`, `published_at`, `created_at`, `updated_at`) VALUES
('欢迎使用博客系统', 'welcome', '<h2>欢迎使用博客系统</h2><p>这是一个基于 CodeIgniter 4.6.3 和 PHP 8.1 开发的完整博客平台。</p><p>系统功能包括：</p><ul><li>文章管理</li><li>分类管理</li><li>标签管理</li><li>评论系统</li><li>用户管理</li></ul>', '这是一个基于 CodeIgniter 4.6.3 和 PHP 8.1 开发的完整博客平台。', 1, 1, 'published', 'public', NOW(), NOW(), NOW()),
('CodeIgniter 4.6.3 新特性', 'codeigniter-463-features', '<h2>CodeIgniter 4.6.3 新特性</h2><p>CodeIgniter 4.6.3 带来了许多新特性和改进。</p><p>主要特性包括：</p><ul><li>性能优化</li><li>安全性增强</li><li>新的辅助函数</li><li>Bug 修复</li></ul>', 'CodeIgniter 4.6.3 带来了许多新特性和改进。', 1, 1, 'published', 'public', NOW(), NOW(), NOW());

-- 关联文章和标签
INSERT INTO `post_tags` (`post_id`, `tag_id`, `created_at`) VALUES
(1, 2, NOW()),
(2, 2, NOW()),
(2, 1, NOW());

-- -----------------------------
-- 表结构: links (友情链接表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '链接ID',
  `name` varchar(100) NOT NULL COMMENT '链接名称',
  `url` varchar(255) NOT NULL COMMENT '链接URL',
  `description` text DEFAULT NULL COMMENT '链接描述',
  `logo` varchar(255) DEFAULT NULL COMMENT '链接Logo URL',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序权重',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '链接状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='友情链接表';

-- 插入测试友情链接
INSERT INTO `links` (`name`, `url`, `description`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('CodeIgniter 官网', 'https://codeigniter.com/', 'CodeIgniter 官方网站', 1, 'active', NOW(), NOW()),
('PHP 官网', 'https://www.php.net/', 'PHP 官方网站', 2, 'active', NOW(), NOW()),
('MySQL 官网', 'https://www.mysql.com/', 'MySQL 官方网站', 3, 'active', NOW(), NOW());

-- -----------------------------
-- 表结构: media (媒体文件表)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '媒体ID',
  `filename` varchar(255) NOT NULL COMMENT '存储的文件名',
  `original_name` varchar(255) NOT NULL COMMENT '原始文件名',
  `file_path` varchar(500) NOT NULL COMMENT '文件存储路径',
  `file_url` varchar(500) NOT NULL COMMENT '文件访问URL',
  `file_type` enum('image','document','video','other') NOT NULL DEFAULT 'other' COMMENT '文件类型',
  `file_size` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小（字节）',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME类型',
  `extension` varchar(20) NOT NULL COMMENT '文件扩展名',
  `width` int(11) UNSIGNED DEFAULT NULL COMMENT '图片宽度',
  `height` int(11) UNSIGNED DEFAULT NULL COMMENT '图片高度',
  `alt_text` varchar(255) DEFAULT NULL COMMENT '图片替代文本',
  `title` varchar(255) DEFAULT NULL COMMENT '文件标题',
  `description` text DEFAULT NULL COMMENT '文件描述',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '上传用户ID',
  `folder_id` int(11) UNSIGNED DEFAULT NULL COMMENT '文件夹ID',
  `is_image` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否是图片',
  `thumbnails` text DEFAULT NULL COMMENT '缩略图信息（JSON格式）',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `folder_id` (`folder_id`),
  KEY `file_type` (`file_type`),
  KEY `is_image` (`is_image`),
  KEY `created_at` (`created_at`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='媒体文件表';
