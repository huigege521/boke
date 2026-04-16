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
        'auto_saved_content'
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

    public function incrementViews($postId)
    {
        // 检查文章是否存在
        if (!$this->find($postId)) {
            return false;
        }

        try {
            // 使用查询构建器直接执行更新，避免CodeIgniter的字段保护
            $this->db->query('UPDATE posts SET views = views + 1 WHERE id = ?', [$postId]);
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
    public function searchPosts($keyword, $limit = 10, $offset = 0)
    {
        return $this->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug, categories.icon as category_icon')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->groupStart()
            ->like('posts.title', $keyword)
            ->orLike('posts.content', $keyword)
            ->orLike('posts.excerpt', $keyword)
            ->orLike('users.username', $keyword)
            ->orLike('users.name', $keyword)
            ->orLike('categories.name', $keyword)
            ->groupEnd()
            ->orderBy('posts.published_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
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

        return $builder->countAllResults();
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
