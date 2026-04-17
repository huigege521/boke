# API 文档

本文档详细介绍CodeIgniter博客系统提供的RESTful API接口。

## 📋 目录

- [基础信息](#基础信息)
- [认证方式](#认证方式)
- [统一响应格式](#统一响应格式)
- [文章接口](#文章接口)
- [分类接口](#分类接口)
- [标签接口](#标签接口)
- [错误码说明](#错误码说明)
- [速率限制](#速率限制)
- [示例代码](#示例代码)

---

## 🌐 基础信息

**Base URL**: `http://yourdomain.com/api`  
**版本**: v1.0  
**格式**: JSON  
**字符编码**: UTF-8  
**请求方法**: GET, POST, PUT, DELETE

---

## 🔐 认证方式

目前API为公开接口，无需认证。未来版本将支持：
- API Key认证
- JWT Token认证
- OAuth 2.0

---

## 📦 统一响应格式

### 成功响应

```json
{
  "success": true,
  "message": "操作成功",
  "data": {
    // 具体数据
  },
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "total_pages": 10
  }
}
```

### 错误响应

```json
{
  "success": false,
  "message": "错误描述",
  "errors": {
    "field_name": ["验证错误信息"]
  }
}
```

---

## 📝 文章接口

### 1. 获取文章列表

**接口**: `GET /api/posts`

**查询参数**:

| 参数 | 类型 | 必填 | 默认值 | 说明 |
|------|------|------|--------|------|
| page | int | 否 | 1 | 页码 |
| limit | int | 否 | 10 | 每页数量（最大100） |
| category_id | int | 否 | - | 分类ID筛选 |
| tag_id | int | 否 | - | 标签ID筛选 |
| search | string | 否 | - | 搜索关键词 |
| order_by | string | 否 | published_at | 排序字段 |
| order | string | 否 | desc | 排序方向（asc/desc） |

**示例请求**:
```bash
curl -X GET "http://yourdomain.com/api/posts?page=1&limit=10&category_id=1"
```

**示例响应**:
```json
{
  "success": true,
  "message": "获取成功",
  "data": [
    {
      "id": 1,
      "title": "文章标题",
      "slug": "article-slug",
      "excerpt": "文章摘要...",
      "featured_image": "20240101/abc123.jpg",
      "author_name": "作者名",
      "category_name": "分类名",
      "views": 100,
      "comments_count": 5,
      "published_at": "2024-01-01 10:00:00",
      "created_at": "2024-01-01 09:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "total_pages": 5
  }
}
```

---

### 2. 获取文章详情

**接口**: `GET /api/posts/{id}`

**路径参数**:
- `id`: 文章ID

**示例请求**:
```bash
curl -X GET "http://yourdomain.com/api/posts/1"
```

**示例响应**:
```json
{
  "success": true,
  "message": "获取成功",
  "data": {
    "id": 1,
    "title": "文章标题",
    "slug": "article-slug",
    "content": "<p>文章完整内容...</p>",
    "excerpt": "文章摘要",
    "featured_image": "20240101/abc123.jpg",
    "user_id": 1,
    "category_id": 1,
    "status": "published",
    "visibility": "public",
    "views": 100,
    "comments_count": 5,
    "meta_title": "SEO标题",
    "meta_description": "SEO描述",
    "meta_keywords": "关键词1,关键词2",
    "author_name": "作者名",
    "author_email": "author@example.com",
    "category_name": "分类名",
    "category_slug": "category-slug",
    "tags": [
      {
        "id": 1,
        "name": "标签1",
        "slug": "tag1"
      }
    ],
    "published_at": "2024-01-01 10:00:00",
    "created_at": "2024-01-01 09:00:00",
    "updated_at": "2024-01-01 09:30:00"
  }
}
```

---

### 3. 创建文章

**接口**: `POST /api/posts`

**权限**: 需要管理员或编辑者权限（待实现认证）

**请求体**:
```json
{
  "title": "新文章标题",
  "content": "<p>文章内容...</p>",
  "excerpt": "文章摘要（可选，不填则自动生成）",
  "category_id": 1,
  "tags": [1, 2, 3],
  "status": "draft",
  "visibility": "public",
  "featured_image": "uploads/20240101/image.jpg",
  "meta_title": "SEO标题（可选）",
  "meta_description": "SEO描述（可选）",
  "meta_keywords": "关键词（可选）"
}
```

**字段说明**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| title | string | 是 | 文章标题（3-200字符） |
| content | string | 是 | 文章内容 |
| excerpt | string | 否 | 文章摘要 |
| category_id | int | 是 | 分类ID |
| tags | array | 否 | 标签ID数组 |
| status | string | 否 | 状态：draft/published/pending/scheduled |
| visibility | string | 否 | 可见性：public/private |
| featured_image | string | 否 | 特色图片路径 |
| scheduled_at | datetime | 否 | 定时发布时间 |

**示例响应**:
```json
{
  "success": true,
  "message": "文章创建成功",
  "data": {
    "id": 101,
    "title": "新文章标题",
    "slug": "new-article-title",
    "status": "draft"
  }
}
```

---

### 4. 更新文章

**接口**: `PUT /api/posts/{id}`

**请求体**: 同创建文章（所有字段可选）

**示例**:
```bash
curl -X PUT "http://yourdomain.com/api/posts/1" \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated Title","status":"published"}'
```

---

### 5. 删除文章

**接口**: `DELETE /api/posts/{id}`

**示例**:
```bash
curl -X DELETE "http://yourdomain.com/api/posts/1"
```

**响应**:
```json
{
  "success": true,
  "message": "文章删除成功"
}
```

---

## 📂 分类接口

### 1. 获取分类列表

**接口**: `GET /api/categories`

**查询参数**:
- `page`: 页码
- `limit`: 每页数量

**示例响应**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "技术",
      "slug": "tech",
      "description": "技术相关文章",
      "icon": "fa-code",
      "posts_count": 25,
      "parent_id": null
    }
  ]
}
```

---

### 2. 获取分类详情

**接口**: `GET /api/categories/{id}`

**响应**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "技术",
    "slug": "tech",
    "description": "技术相关文章",
    "posts_count": 25,
    "recent_posts": [
      // 最近的文章列表
    ]
  }
}
```

---

## 🏷️ 标签接口

### 1. 获取标签列表

**接口**: `GET /api/tags`

**查询参数**:
- `page`: 页码
- `limit`: 每页数量
- `popular`: 是否只返回热门标签（按文章数排序）

**示例响应**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "PHP",
      "slug": "php",
      "posts_count": 15
    }
  ]
}
```

---

### 2. 获取标签详情

**接口**: `GET /api/tags/{id}`

---

## ⚠️ 错误码说明

| HTTP状态码 | 说明 |
|-----------|------|
| 200 | 成功 |
| 201 | 创建成功 |
| 400 | 请求参数错误 |
| 401 | 未授权（需要登录） |
| 403 | 禁止访问（权限不足） |
| 404 | 资源不存在 |
| 422 | 验证失败 |
| 429 | 请求过于频繁 |
| 500 | 服务器内部错误 |

---

## 🚦 速率限制

为防止API滥用，系统实施以下限流策略：

**当前配置**: 未启用（待实现）

**计划配置**:
- 未认证用户: 60次/分钟
- 已认证用户: 300次/分钟
- 管理员: 无限制

**限流响应头**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1609459200
```

---

## 💻 示例代码

### JavaScript (Fetch API)

```javascript
// 获取文章列表
fetch('http://yourdomain.com/api/posts?page=1&limit=10')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('文章列表:', data.data);
      console.log('分页信息:', data.pagination);
    } else {
      console.error('错误:', data.message);
    }
  })
  .catch(error => console.error('请求失败:', error));

// 获取文章详情
fetch('http://yourdomain.com/api/posts/1')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('文章详情:', data.data);
    }
  });

// 创建文章
fetch('http://yourdomain.com/api/posts', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    title: 'New Article',
    content: '<p>Content here</p>',
    category_id: 1,
    status: 'draft'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Python (Requests)

```python
import requests

# 获取文章列表
response = requests.get('http://yourdomain.com/api/posts', params={
    'page': 1,
    'limit': 10
})
data = response.json()
if data['success']:
    print(f"共 {data['pagination']['total']} 篇文章")
    for post in data['data']:
        print(f"- {post['title']}")

# 获取文章详情
response = requests.get('http://yourdomain.com/api/posts/1')
post = response.json()['data']
print(f"标题: {post['title']}")
print(f"内容: {post['content'][:100]}...")
```

### PHP (cURL)

```php
<?php
// 获取文章列表
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://yourdomain.com/api/posts?page=1&limit=10');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if ($data['success']) {
    foreach ($data['data'] as $post) {
        echo "- {$post['title']}\n";
    }
}

// 创建文章
$postData = [
    'title' => 'New Article',
    'content' => '<p>Content</p>',
    'category_id' => 1,
    'status' => 'draft'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://yourdomain.com/api/posts');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
echo $result['message'];
?>
```

---

## 📊 性能优化建议

### 缓存策略

- **文章列表**: 缓存5-30分钟（根据热度调整）
- **文章详情**: 缓存1-2小时
- **分类/标签列表**: 缓存24小时

### 最佳实践

1. **合理使用分页**: 避免一次性加载大量数据
2. **按需请求字段**: 列表接口只返回必要字段
3. **利用缓存**: 客户端应实现本地缓存
4. **条件请求**: 使用ETag或Last-Modified减少不必要的数据传输
5. **批量操作**: 合并多个请求为一个

---

## 🔗 相关链接

- [项目README](README.md)
- [性能优化指南](PERFORMANCE_OPTIMIZATION.md)
- [改进总结](IMPROVEMENTS_SUMMARY.md)

---

**API版本**: v1.0  
**最后更新**: 2024-01-01  
**维护者**: Development Team
