# CodeIgniter 博客系统

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.6+-red.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

一个基于 CodeIgniter 4 框架构建的现代化博客系统，提供完整的内容管理功能和 RESTful API 接口。

[功能特性](#-功能特性) • [快速开始](#-快速开始) • [项目结构](#-项目结构) • [API文档](#-api文档) • [部署指南](#-部署指南)

</div>

---

## 📋 目录

- [功能特性](#-功能特性)
- [技术栈](#-技术栈)
- [系统要求](#-系统要求)
- [快速开始](#-快速开始)
- [项目结构](#-项目结构)
- [数据库设计](#-数据库设计)
- [配置说明](#-配置说明)
- [使用指南](#-使用指南)
- [API文档](#-api文档)
- [定时任务](#-定时任务)
- [安全特性](#-安全特性)
- [性能优化](#-性能优化)
- [常见问题](#-常见问题)
- [开发指南](#-开发指南)
- [贡献指南](#-贡献指南)
- [许可证](#-许可证)

---

## ✨ 功能特性

### 🎯 前台功能
- ✅ **文章展示**: 支持分页、分类筛选、标签筛选、时间归档
- ✅ **搜索功能**: 全文搜索（标题、内容、作者、分类）
- ✅ **评论系统**: 支持嵌套评论、审核机制
- ✅ **用户系统**: 注册、登录、个人资料管理、密码找回
- ✅ **RSS订阅**: 自动生成 RSS Feed
- ✅ **站点地图**: XML格式站点地图
- ✅ **响应式设计**: 完美适配移动端

### 🔧 后台管理
- ✅ **仪表盘**: 数据统计与可视化图表
- ✅ **文章管理**: 
  - 富文本编辑器（CKEditor）
  - 自动保存草稿
  - 定时发布
  - 批量操作（发布/草稿/删除）
  - 特色图片上传
  - SEO友好（自定义Slug）
- ✅ **分类管理**: 支持层级分类、图标设置
- ✅ **标签管理**: 文章标签管理
- ✅ **评论审核**: 批准/拒绝/标记垃圾评论
- ✅ **用户管理**: 角色权限管理（Admin/Editor/User）
- ✅ **媒体库**: 文件上传与管理
- ✅ **友情链接**: 友链管理
- ✅ **联系表单**: 查看和处理访客留言
- ✅ **系统设置**: 网站配置管理

### 🚀 API接口
- ✅ RESTful API 设计
- ✅ 文章、分类、标签接口
- ✅ 统一响应格式
- ✅ 分页支持

---

## 🛠 技术栈

### 后端
- **框架**: [CodeIgniter 4.6+](https://codeigniter.com/)
- **语言**: PHP 8.1+
- **数据库**: MySQL 5.7+ / MariaDB 10.3+
- **缓存**: File Cache（可扩展 Redis）

### 前端
- **CSS框架**: Bootstrap 5
- **JavaScript**: jQuery 3.x
- **富文本编辑器**: CKEditor 4
- **消息提示**: Toastr
- **图表库**: Chart.js
- **图标**: Font Awesome 6

### 开发工具
- **依赖管理**: Composer
- **测试框架**: PHPUnit 10.5
- **数据填充**: Faker

---

## 💻 系统要求

- PHP >= 8.1
- MySQL >= 5.7 或 MariaDB >= 10.3
- Composer
- Web服务器（Nginx 1.18+ 或 Apache 2.4+）
- PHP扩展：
  - intl
  - mbstring
  - json
  - mysqlnd
  - libcurl（可选，用于HTTP请求）

---

## 🚀 快速开始

### 1. 克隆项目

```bash
git clone https://github.com/yourusername/codeigniter-blog.git
cd codeigniter-blog
```

### 2. 安装依赖

```bash
composer install
```

### 3. 环境配置

复制环境变量配置文件：

```bash
cp env .env
```

编辑 `.env` 文件，配置数据库连接：

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = codeigniter_blog
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

**重要**: 生成加密密钥（用于会话加密）：

```bash
php spark key:generate
```

### 4. 数据库初始化

运行数据库迁移：

```bash
php spark migrate
```

（可选）填充测试数据：

```bash
php spark db:seed MainSeeder
```

### 5. 启动开发服务器

```bash
php spark serve
```

访问 `http://localhost:8080` 即可看到博客首页。

### 6. 默认管理员账号

如果使用 Seeder 填充了测试数据：

- **邮箱**: admin@example.com
- **密码**: admin123

⚠️ **生产环境请务必修改默认密码！**

---

## 📁 项目结构

```
codeigniter-blog/
├── app/                      # 应用核心代码
│   ├── Commands/            # CLI命令
│   │   └── PublishScheduledPosts.php  # 定时发布命令
│   ├── Config/              # 配置文件
│   │   ├── App.php          # 应用配置
│   │   ├── Database.php     # 数据库配置
│   │   ├── Routes.php       # 路由配置
│   │   └── ...
│   ├── Controllers/         # 控制器
│   │   ├── Admin/           # 后台控制器
│   │   │   ├── PostController.php
│   │   │   ├── CategoryController.php
│   │   │   └── ...
│   │   ├── Api/             # API控制器
│   │   │   ├── PostController.php
│   │   │   └── ...
│   │   ├── BaseController.php
│   │   └── Home.php         # 前台控制器
│   ├── Database/            # 数据库相关
│   │   ├── Migrations/      # 数据库迁移
│   │   └── Seeds/           # 数据填充
│   ├── Filters/             # 过滤器
│   │   ├── AuthFilter.php   # 身份验证
│   │   └── SecurityFilter.php  # 安全防护
│   ├── Helpers/             # 辅助函数
│   ├── Libraries/           # 类库
│   │   ├── AuthService.php  # 认证服务
│   │   ├── Captcha.php      # 验证码
│   │   └── Validation.php   # 验证器
│   ├── Models/              # 数据模型
│   │   ├── PostModel.php
│   │   ├── UserModel.php
│   │   └── ...
│   └── Views/               # 视图模板
│       ├── admin/           # 后台视图
│       ├── frontend/        # 前台视图
│       └── errors/          # 错误页面
├── public/                  # 公共目录（Web根目录）
│   ├── css/                 # 样式文件
│   ├── js/                  # JavaScript文件
│   ├── uploads/             # 上传文件
│   └── index.php            # 入口文件
├── writable/                # 可写目录
│   ├── cache/               # 缓存文件
│   ├── logs/                # 日志文件
│   └── session/             # 会话文件
├── tests/                   # 测试文件
├── vendor/                  # Composer依赖
├── .env                     # 环境变量配置
├── composer.json            # Composer配置
├── database.sql             # 完整SQL脚本
├── nginx.conf               # Nginx配置示例
├── publish_scheduled.bat    # Windows定时任务脚本
└── publish_scheduled.sh     # Linux定时任务脚本
```

---

## 🗄 数据库设计

### 核心表结构

#### users (用户表)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 用户ID |
| username | VARCHAR(50) | 用户名（唯一） |
| email | VARCHAR(100) | 邮箱（唯一） |
| password | VARCHAR(255) | 密码（bcrypt哈希） |
| name | VARCHAR(50) | 真实姓名 |
| role | ENUM | 角色：admin/editor/user |
| status | ENUM | 状态：active/inactive |

#### posts (文章表)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 文章ID |
| title | VARCHAR(200) | 标题 |
| slug | VARCHAR(200) | URL别名（唯一） |
| content | TEXT | 文章内容 |
| excerpt | TEXT | 摘要 |
| featured_image | VARCHAR(255) | 特色图片 |
| user_id | INT UNSIGNED | 作者ID（外键） |
| category_id | INT UNSIGNED | 分类ID（外键） |
| status | ENUM | 状态：draft/published/pending/scheduled |
| visibility | ENUM | 可见性：public/private |
| views | INT | 浏览次数 |
| scheduled_at | DATETIME | 定时发布时间 |

#### categories (分类表)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 分类ID |
| name | VARCHAR(50) | 分类名（唯一） |
| slug | VARCHAR(50) | URL别名（唯一） |
| parent_id | INT UNSIGNED | 父分类ID（自关联） |
| icon | VARCHAR(255) | 图标类名 |

#### tags (标签表)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 标签ID |
| name | VARCHAR(50) | 标签名（唯一） |
| slug | VARCHAR(50) | URL别名（唯一） |

#### comments (评论表)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 评论ID |
| post_id | INT UNSIGNED | 文章ID（外键） |
| parent_id | INT UNSIGNED | 父评论ID（嵌套评论） |
| content | TEXT | 评论内容 |
| status | ENUM | 状态：approved/pending/spam |

完整数据库结构请查看 [`database.sql`](database.sql)

---

## ⚙️ 配置说明

### 主要配置文件

#### .env - 环境变量
```ini
# 环境模式
CI_ENVIRONMENT = development  # development | production | testing

# 应用配置
app.baseURL = 'http://yourdomain.com/'
app.indexPage = ''  # 空字符串启用URL重写

# 数据库配置
database.default.hostname = localhost
database.default.database = codeigniter_blog
database.default.username = root
database.default.password = your_password

# 缓存配置
cache.store = file  # file | redis

# 会话配置
session.driver = "CodeIgniter\Session\Handlers\FileHandler"
session.expiration = 7200  # 2小时
```

#### app/Config/App.php - 应用配置
- `$baseURL`: 网站基础URL
- `$indexPage`: 入口文件名（URL重写时设为空）
- `$defaultLocale`: 默认语言（zh-CN）

#### app/Config/Database.php - 数据库配置
- 主数据库连接配置
- 测试数据库连接配置

#### app/Config/Routes.php - 路由配置
定义所有URL路由规则

---

## 📖 使用指南

### 前台使用

1. **浏览文章**: 访问首页查看最新文章
2. **搜索文章**: 使用搜索框按关键词搜索
3. **分类浏览**: 点击侧边栏分类链接
4. **标签浏览**: 点击文章标签查看相关文章
5. **用户注册**: 点击"注册"创建账号
6. **发表评论**: 登录后在文章底部评论

### 后台管理

1. **登录后台**: 访问 `/admin/dashboard`
2. **发布文章**: 
   - 点击"文章管理" → "新建文章"
   - 填写标题、内容、选择分类和标签
   - 上传特色图片
   - 选择状态（草稿/发布/定时）
   - 点击"保存"
3. **管理分类**: "分类管理"中添加/编辑/删除分类
4. **审核评论**: "评论管理"中批准或拒绝评论
5. **管理用户**: "用户管理"中设置用户角色和权限

### 角色权限

| 权限 | User | Editor | Admin |
|------|------|--------|-------|
| 浏览文章 | ✅ | ✅ | ✅ |
| 发表评论 | ✅ | ✅ | ✅ |
| 管理自己的文章 | ❌ | ✅ | ✅ |
| 管理所有文章 | ❌ | ✅ | ✅ |
| 管理分类标签 | ❌ | ✅ | ✅ |
| 审核评论 | ❌ | ✅ | ✅ |
| 管理用户 | ❌ | ❌ | ✅ |
| 系统设置 | ❌ | ❌ | ✅ |

---

## 📡 API文档

### 基础信息

- **Base URL**: `http://yourdomain.com/api`
- **响应格式**: JSON
- **字符编码**: UTF-8

### 统一响应格式

**成功响应**:
```json
{
  "success": true,
  "message": "操作成功",
  "data": {...},
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "total_pages": 10
  }
}
```

**错误响应**:
```json
{
  "success": false,
  "message": "错误信息",
  "errors": {...}
}
```

### 文章接口

#### 获取文章列表
```
GET /api/posts
```

**查询参数**:
- `page`: 页码（默认1）
- `limit`: 每页数量（默认10）
- `category_id`: 分类ID筛选
- `tag_id`: 标签ID筛选
- `search`: 搜索关键词

**示例**:
```bash
curl http://yourdomain.com/api/posts?page=1&limit=10&category_id=1
```

#### 获取文章详情
```
GET /api/posts/{id}
```

**示例**:
```bash
curl http://yourdomain.com/api/posts/1
```

#### 创建文章
```
POST /api/posts
```

**请求体**:
```json
{
  "title": "文章标题",
  "content": "文章内容",
  "excerpt": "文章摘要",
  "category_id": 1,
  "tags": [1, 2, 3],
  "status": "published",
  "visibility": "public"
}
```

#### 更新文章
```
PUT /api/posts/{id}
```

#### 删除文章
```
DELETE /api/posts/{id}
```

### 分类接口

#### 获取分类列表
```
GET /api/categories
```

#### 获取分类详情
```
GET /api/categories/{id}
```

### 标签接口

#### 获取标签列表
```
GET /api/tags
```

#### 获取标签详情
```
GET /api/tags/{id}
```

---

## ⏰ 定时任务

### 定时发布文章

系统支持文章的定时发布功能，需要配置定时任务来自动发布到期的文章。

#### 方法一：使用CLI命令

手动执行定时发布：
```bash
php spark blog:publish-scheduled
```

#### 方法二：Windows计划任务

1. 编辑 [`publish_scheduled.bat`](publish_scheduled.bat)，修改项目路径
2. 打开"任务计划程序"
3. 创建基本任务，设置为每小时执行一次
4. 操作指向 `publish_scheduled.bat`

#### 方法三：Linux Cron

编辑 crontab：
```bash
crontab -e
```

添加以下行（每小时执行一次）：
```cron
0 * * * * cd /path/to/codeigniter-blog && php spark blog:publish-scheduled >> /var/log/blog-cron.log 2>&1
```

或直接使用提供的脚本：
```bash
chmod +x publish_scheduled.sh
./publish_scheduled.sh
```

---

## 🔒 安全特性

### 多层防护体系

1. **身份验证**: Session-based认证，角色权限控制
2. **CSRF保护**: Cookie模式的CSRF令牌
3. **XSS防护**: 输入过滤和输出转义
4. **SQL注入防护**: 查询构建器和参数化查询
5. **请求验证**: 恶意User-Agent检测、请求大小限制
6. **安全响应头**:
   - X-Frame-Options: SAMEORIGIN
   - X-Content-Type-Options: nosniff
   - Content-Security-Policy
   - Referrer-Policy

### 密码安全

- 使用 bcrypt 算法哈希存储
- 密码重置token有过期时间
- 登录失败不提示具体错误（防止枚举）

### 最佳实践建议

1. **生产环境**:
   - 设置 `CI_ENVIRONMENT = production`
   - 启用HTTPS
   - 配置强密码策略
   - 定期备份数据库

2. **文件权限**:
   ```bash
   chmod 755 public/
   chmod 644 writable/ -R
   chmod 600 .env
   ```

3. **隐藏敏感文件**:
   - Nginx配置已禁止访问 `.env`、`system/`、`writable/`

---

## ⚡ 性能优化

### 已实现的优化

✅ **数据库优化**
- 关键字段索引（slug, status, published_at）
- 外键约束保证数据完整性
- 分页查询避免全表扫描

✅ **缓存机制**
- 首页按页码缓存（1小时有效期）
- 文章详情缓存
- File Cache驱动（可扩展Redis）

✅ **前端优化**
- 图片懒加载（lazyload）
- Bootstrap CDN加速
- CSS/JS压缩

✅ **代码优化**
- Composer自动加载优化
- 查询构建器减少SQL注入风险
- JOIN查询减少数据库交互次数

### 进一步优化建议

1. **引入Redis缓存**: 替换File Cache提升性能
2. **CDN加速**: 静态资源使用CDN
3. **图片压缩**: 上传时自动压缩图片
4. **Gzip压缩**: Nginx启用Gzip
5. **数据库连接池**: 配置持久连接
6. **OPcache**: 启用PHP OPcache

---

## ❓ 常见问题

### Q1: 如何修改网站标题和Logo？

A: 编辑 `app/Views/frontend/layouts/header.php`，修改标题和Logo路径。

### Q2: 如何更改每页显示文章数量？

A: 在 `app/Controllers/Home.php` 的 `index()` 方法中修改 `$perPage` 变量。

### Q3: 上传图片失败怎么办？

A: 检查以下项：
- `writable/uploads/` 目录是否有写入权限
- PHP配置的 `upload_max_filesize` 和 `post_max_size`
- Nginx/Apache的文件大小限制

### Q4: 如何启用URL重写（去除index.php）？

A: 
1. 设置 `.env` 中 `app.indexPage = ''`
2. Nginx配置已包含重写规则
3. Apache需启用 `mod_rewrite` 并添加 `.htaccess`

### Q5: 忘记密码怎么办？

A: 访问 `/home/forgotPassword`，输入注册邮箱接收重置链接。

### Q6: 如何备份数据库？

A:
```bash
mysqldump -u root -p codeigniter_blog > backup_$(date +%Y%m%d).sql
```

### Q7: 如何切换生产环境？

A:
1. 修改 `.env`: `CI_ENVIRONMENT = production`
2. 关闭调试模式
3. 启用HTTPS
4. 配置正确的域名
5. 优化数据库和缓存

---

## 👨‍💻 开发指南

### 添加新功能

1. **创建迁移文件**:
   ```bash
   php spark make:migration CreateNewTable
   ```

2. **创建模型**:
   ```bash
   php spark make:model NewModel
   ```

3. **创建控制器**:
   ```bash
   php spark make:controller NewController
   ```

4. **创建视图**: 在 `app/Views/` 下创建对应文件

5. **配置路由**: 在 `app/Config/Routes.php` 添加路由规则

### 代码规范

- 遵循 PSR-4 自动加载标准
- 使用驼峰命名法（camelCase）
- 类名使用大驼峰（PascalCase）
- 添加必要的注释
- 保持单一职责原则

### 调试技巧

1. **开启调试工具栏**:
   ```ini
   # .env
   CI_ENVIRONMENT = development
   ```

2. **查看日志**:
   ```bash
   tail -f writable/logs/log-*.log
   ```

3. **使用Debug Bar**: CodeIgniter自带调试工具栏

### 运行测试

```bash
# 运行所有测试
composer test

# 运行特定测试
vendor/bin/phpunit tests/Models/PostModelTest.php
```

---

## 🤝 贡献指南

欢迎贡献代码！请遵循以下步骤：

1. **Fork** 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 **Pull Request**

### 贡献要求

- 代码符合PSR规范
- 添加必要的单元测试
- 更新相关文档
- 保持commit信息清晰

---

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情

---

## 📞 联系方式

- **项目主页**: [GitHub Repository](https://github.com/yourusername/codeigniter-blog)
- **问题反馈**: [Issues](https://github.com/yourusername/codeigniter-blog/issues)
- **邮箱**: your-email@example.com

---

## 🙏 致谢

感谢以下开源项目：

- [CodeIgniter](https://codeigniter.com/) - PHP框架
- [Bootstrap](https://getbootstrap.com/) - 前端框架
- [CKEditor](https://ckeditor.com/) - 富文本编辑器
- [jQuery](https://jquery.com/) - JavaScript库
- [Font Awesome](https://fontawesome.com/) - 图标库

---

<div align="center">

**如果这个项目对你有帮助，请给个 ⭐ Star 支持一下！**

Made with ❤️ by Your Name

</div>
