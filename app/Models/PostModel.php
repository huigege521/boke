<?php
namespace App\Models;

use CodeIgniter\Model;

class PostModel extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'user_id',
        'category_id',
        'status',
        'visibility',
        'published_at',
        'scheduled_at',
        'views',
        'comments_count',
        'auto_saved_at',
        'auto_saved_content',
        // SEO元数据字段
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getPublishedPosts($limit = 10, $offset = 0, $order_by = null, $order = null)
    {
        $builder = $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->orderBy('posts.published_at', 'desc');
        if ($order_by && $order) {
            $builder->orderBy($order_by, $order);
        }
        return $builder->limit($limit, $offset)
            ->findAll();
        //echo $builder->getLastQuery()->getQuery();die;
    }

    public function getPostBySlug($slug)
    {
        return $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.slug', $slug)
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->first();
    }

    public function getPostsByCategory($categoryId, $limit = 10, $offset = 0)
    {
        return $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.category_id', $categoryId)
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->orderBy('posts.published_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    public function getPostsByTag($tagId, $limit = 10, $offset = 0)
    {
        return $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->join('post_tags', 'post_tags.post_id = posts.id', 'left')
            ->where('post_tags.tag_id', $tagId)
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->groupBy('posts.id')
            ->orderBy('posts.published_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * 增加文章浏览次数
     * 使用参数化查询防止SQL注入
     *
     * @param int $postId 文章ID
     * @return bool 是否成功
     */
    public function incrementViews($postId)
    {
        // 检查文章是否存在
        if (!$this->find($postId)) {
            return false;
        }

        try {
            // 使用CodeIgniter的查询构建器，更安全
            $this->where('id', $postId)
                ->set('views', 'views + 1', false) // false表示不转义，允许SQL表达式
                ->update();
            return true;
        } catch (\Exception $e) {
            // 记录错误但不抛出异常
            log_message('error', 'Failed to increment views: ' . $e->getMessage());
            return false;
        }
    }

    public function getPostsByUser($userId, $limit = 10, $offset = 0)
    {
        return $this->where('user_id', $userId)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->orderBy('published_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    // 搜索功能

    /**
     * 获取搜索结果总数
     *
     * @param string $keyword 搜索关键词
     * @return int 结果数量
     */
    public function getSearchCount($keyword)
    {
        $keyword = trim($keyword);
        $this->ensureFullTextIndex();

        $builder = $this->where('status', 'published')
            ->where('visibility', 'public');

        // 改进的搜索逻辑：优先使用精确匹配，再使用部分匹配
        // 首先尝试精确匹配整个短语
        $builder->groupStart();

        // 精确匹配整个短语
        $builder->groupStart()
            ->like('title', $keyword)
            ->orLike('content', $keyword)
            ->orLike('excerpt', $keyword)
            ->groupEnd();

        // 然后尝试匹配关键词的各个部分（作为补充）
        $keywords = preg_split('/\s+/', $keyword);
        foreach ($keywords as $key) {
            if (mb_strlen(trim($key)) >= 2) {
                $builder->orGroupStart()
                    ->like('title', trim($key))
                    ->orLike('content', trim($key))
                    ->orLike('excerpt', trim($key))
                    ->groupEnd();
            }
        }

        $builder->groupEnd();

        return $builder->countAllResults(false);
    }

    /**
     * 确保全文索引存在
     * 如果不存在则创建全文索引
     *
     * @return void
     */
    private function ensureFullTextIndex()
    {
        try {
            // 检查索引是否存在
            $indexes = $this->db->query("SHOW INDEX FROM posts WHERE Key_name = 'idx_fulltext_search'")->getResultArray();

            if (empty($indexes)) {
                // 创建全文索引
                $this->db->query("ALTER TABLE posts ADD FULLTEXT INDEX idx_fulltext_search (title, content, excerpt)");
                log_message('info', '已为posts表创建全文索引');
            }
        } catch (\Exception $e) {
            // 如果创建失败（如MyISAM引擎不支持），记录日志但不中断程序
            log_message('warning', '创建全文索引失败: ' . $e->getMessage());
        }
    }

    // 归档功能
    public function getPostsByDate($year, $month)
    {
        return $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->where('YEAR(posts.published_at)', $year)
            ->where('MONTH(posts.published_at)', $month)
            ->orderBy('posts.published_at', 'desc')
            ->findAll();
    }

    // 获取归档月份列表
    public function getArchiveMonths()
    {
        $query = $this->db->query("SELECT DISTINCT YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count FROM posts WHERE status = 'published' AND visibility = 'public' GROUP BY YEAR(published_at), MONTH(published_at) ORDER BY YEAR(published_at) DESC, MONTH(published_at) DESC");
        return $query->getResultArray();
    }

    // 自动保存文章
    public function autoSave($postId, $content)
    {
        return $this->update($postId, [
            'auto_saved_content' => $content,
            'auto_saved_at' => date('Y-m-d H:i:s')
        ]);
    }

    // 获取自动保存的内容
    public function getAutoSavedContent($postId)
    {
        $post = $this->select('auto_saved_content, auto_saved_at')
            ->where('id', $postId)
            ->first();
        return $post ? $post : null;
    }

    // 发布定时文章
    public function publishScheduledPosts()
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('status', 'scheduled')
            ->where('scheduled_at <=', $now)
            ->set('status', 'published')
            ->set('published_at', $now)
            ->update();
    }

    // 获取定时发布的文章
    public function getScheduledPosts()
    {
        return $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.status', 'scheduled')
            ->orderBy('posts.scheduled_at', 'asc')
            ->findAll();
    }

    // 获取所有文章（用于后台管理）
    public function getAllPosts($limit = 10, $offset = 0, $search = null, $status = null, $categoryId = null, $orderBy = 'created_at', $orderDirection = 'desc')
    {
        $builder = $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left');

        // 添加搜索条件
        if (!empty($search)) {
            $builder->groupStart()
                ->like('posts.title', $search)
                ->orLike('posts.content', $search)
                ->orLike('users.username', $search)
                ->orLike('categories.name', $search)
                ->groupEnd();
        }

        // 添加状态筛选
        if (!empty($status)) {
            $builder->where('posts.status', $status);
        }

        // 添加分类筛选
        if (!empty($categoryId)) {
            $builder->where('posts.category_id', $categoryId);
        }

        // 添加排序
        $validOrderFields = ['id', 'title', 'created_at', 'published_at', 'views', 'comments_count'];
        if (in_array($orderBy, $validOrderFields)) {
            $builder->orderBy('posts.' . $orderBy, $orderDirection);
        } else {
            $builder->orderBy('posts.created_at', 'desc');
        }

        return $builder->limit($limit, $offset)
            ->findAll();
    }

    // 获取文章的标签
    public function getPostTags($postId)
    {
        $query = $this->db->query("SELECT tags.* FROM tags JOIN post_tags ON post_tags.tag_id = tags.id WHERE post_tags.post_id = ?", [$postId]);
        return $query->getResultArray();
    }

    // 获取所有文章总数（用于后台管理）
    public function getAllPostsCount($search = null, $status = null, $categoryId = null)
    {
        $builder = $this->select('posts.*')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left');

        // 添加搜索条件
        if (!empty($search)) {
            $builder->groupStart()
                ->like('posts.title', $search)
                ->orLike('posts.content', $search)
                ->orLike('users.username', $search)
                ->orLike('categories.name', $search)
                ->groupEnd();
        }

        // 添加状态筛选
        if (!empty($status)) {
            $builder->where('posts.status', $status);
        }

        // 添加分类筛选
        if (!empty($categoryId)) {
            $builder->where('posts.category_id', $categoryId);
        }

        return $builder->countAllResults(false);
    }

    /**
     * 搜索文章（优化版：使用MySQL全文索引）
     * 支持标题、内容、摘要的全文搜索
     *
     * @param string $keyword 搜索关键词
     * @param int $limit 每页数量
     * @param int $offset 偏移量
     * @return array 搜索结果
     */
    public function searchPosts($keyword, $limit = 10, $offset = 0)
    {
        // 清理关键词，防止SQL注入
        $keyword = trim($keyword);

        // 检查是否已创建全文索引
        $this->ensureFullTextIndex();

        // 使用MATCH...AGAINST进行全文搜索
        $builder = $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public');

        // 改进的搜索逻辑：优先使用精确匹配，再使用部分匹配
        // 首先尝试精确匹配整个短语
        $builder->groupStart();

        // 精确匹配整个短语
        $builder->groupStart()
            ->like('posts.title', $keyword)
            ->orLike('posts.content', $keyword)
            ->orLike('posts.excerpt', $keyword)
            ->groupEnd();

        // 然后尝试匹配关键词的各个部分（作为补充）
        $keywords = preg_split('/\s+/', $keyword);
        foreach ($keywords as $key) {
            if (mb_strlen(trim($key)) >= 2) {
                $builder->orGroupStart()
                    ->like('posts.title', trim($key))
                    ->orLike('posts.content', trim($key))
                    ->orLike('posts.excerpt', trim($key))
                    ->groupEnd();
            }
        }

        $builder->groupEnd();

        return $builder->orderBy('posts.published_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    // 批量更新文章状态
    public function batchUpdateStatus($ids, $status)
    {
        return $this->whereIn('id', $ids)->update(['status' => $status]);
    }

    // 批量删除文章
    public function batchDelete($ids)
    {
        return $this->whereIn('id', $ids)->delete();
    }
}