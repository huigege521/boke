-- 更新文章表结构，添加自动保存和定时发布功能
ALTER TABLE `posts` 
ADD COLUMN `scheduled_at` datetime DEFAULT NULL COMMENT '定时发布时间',
ADD COLUMN `auto_saved_at` datetime DEFAULT NULL COMMENT '自动保存时间',
ADD COLUMN `auto_saved_content` text DEFAULT NULL COMMENT '自动保存的内容',
MODIFY COLUMN `status` enum('draft','published','pending','scheduled') NOT NULL DEFAULT 'draft' COMMENT '文章状态';

-- 添加索引
ALTER TABLE `posts` ADD INDEX `scheduled_at` (`scheduled_at`);
