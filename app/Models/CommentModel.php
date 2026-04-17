<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'post_id',
        'parent_id',
        'user_id',
        'content',
        'author_name',
        'author_email',
        'author_ip',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * 根据文章ID获取评论（优化版：避免递归查询）
     * 使用一次性查询获取所有评论，然后在PHP中构建树形结构
     *
     * @param int $postId 文章ID
     * @param string $status 评论状态
     * @return array 树形结构的评论数组
     */
    public function getCommentsByPostId($postId, $status = 'approved')
    {
        // 一次性获取所有评论，避免N+1查询问题
        $comments = $this->select('comments.*, users.username, users.name as user_name, users.avatar')
            ->join('users', 'users.id = comments.user_id', 'left')
            ->where('comments.post_id', $postId)
            ->where('comments.status', $status)
            ->orderBy('comments.created_at', 'asc')
            ->findAll();

        // 在内存中构建树形结构
        return $this->buildCommentTree($comments);
    }

    /**
     * 构建评论树形结构
     *
     * @param array $comments 扁平的评论数组
     * @return array 树形结构的评论数组
     */
    private function buildCommentTree(array $comments): array
    {
        // 按ID索引所有评论
        $indexed = [];
        foreach ($comments as $comment) {
            $comment['children'] = []; // 初始化子评论数组
            $indexed[$comment['id']] = $comment;
        }

        // 构建树形结构
        $tree = [];
        foreach ($indexed as $id => $comment) {
            if ($comment['parent_id'] && isset($indexed[$comment['parent_id']])) {
                // 添加到父评论的children中
                $indexed[$comment['parent_id']]['children'][] = &$indexed[$id];
            } else {
                // 顶级评论
                $tree[] = &$indexed[$id];
            }
        }

        return $tree;
    }

    // 获取所有评论
    public function getAllComments($limit = 2, $offset = 0, $search = null)
    {
        $builder = $this->select('comments.*, posts.title as post_title, posts.slug, users.username, users.name as user_name')
            ->join('posts', 'posts.id = comments.post_id', 'left')
            ->join('users', 'users.id = comments.user_id', 'left');

        // 添加搜索条件
        if (!empty($search)) {
            $builder->groupStart()
                ->like('comments.content', $search)
                ->orLike('posts.title', $search)
                ->orLike('users.username', $search)
                ->orLike('comments.author_name', $search)
                ->groupEnd();
        }

        return $builder->orderBy('comments.created_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    // 根据状态获取评论
    public function getCommentsByStatus($status, $limit = 20, $offset = 0, $search = null)
    {
        $builder = $this->select('comments.*, posts.title as post_title, posts.slug, users.username, users.name as user_name')
            ->join('posts', 'posts.id = comments.post_id', 'left')
            ->join('users', 'users.id = comments.user_id', 'left')
            ->where('comments.status', $status);

        // 添加搜索条件
        if (!empty($search)) {
            $builder->groupStart()
                ->like('comments.content', $search)
                ->orLike('posts.title', $search)
                ->orLike('users.username', $search)
                ->orLike('comments.author_name', $search)
                ->groupEnd();
        }

        return $builder->orderBy('comments.created_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    // 根据ID获取评论
    public function getCommentById($id)
    {
        return $this->select('comments.*, posts.title as post_title, posts.slug, users.username, users.name as user_name')
            ->join('posts', 'posts.id = comments.post_id', 'left')
            ->join('users', 'users.id = comments.user_id', 'left')
            ->where('comments.id', $id)
            ->first();
    }

    // 获取评论数量
    public function getCommentCount($postId = null, $status = null)
    {
        $query = $this->builder();
        if ($postId) {
            $query->where('post_id', $postId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        return $query->countAllResults();
    }

    // 创建评论
    public function createComment($data)
    {
        // 准备评论数据
        $commentData = [
            'post_id' => $data['post_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'content' => $data['content'],
            'author_name' => $data['author_name'] ?? null,
            'author_email' => $data['author_email'] ?? null,
            'author_ip' => $data['author_ip'],
            'status' => $data['user_id'] ? 'approved' : 'pending' // 登录用户的评论自动通过，游客评论需要审核
        ];

        // 创建评论
        $commentId = $this->insert($commentData);
        if (!$commentId) {
            return false;
        }

        // 更新文章评论数
        $this->updatePostCommentCount($data['post_id']);

        return $commentId;
    }

    // 更新评论状态
    public function updateCommentStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    // 删除评论
    public function deleteComment($id)
    {
        // 获取评论信息
        $comment = $this->find($id);
        if (!$comment) {
            return false;
        }

        // 删除评论
        if (!$this->delete($id)) {
            return false;
        }

        // 更新文章评论数
        $this->updatePostCommentCount($comment['post_id']);

        return true;
    }

    // 更新文章评论数
    private function updatePostCommentCount($postId)
    {
        $postModel = new PostModel();
        $count = $this->where('post_id', $postId)
            ->where('status', 'approved')
            ->countAllResults();

        $postModel->update($postId, ['comments_count' => $count]);
    }

    // 获取评论的回复
    public function getCommentReplies($commentId)
    {
        return $this->where('parent_id', $commentId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'asc')
            ->findAll();
    }

    // 获取所有评论总数
    public function getAllCommentsCount($search = null)
    {
        $builder = $this->select('comments.*')
            ->join('posts', 'posts.id = comments.post_id', 'left')
            ->join('users', 'users.id = comments.user_id', 'left');

        // 添加搜索条件
        if (!empty($search)) {
            $builder->groupStart()
                ->like('comments.content', $search)
                ->orLike('posts.title', $search)
                ->orLike('users.username', $search)
                ->orLike('comments.author_name', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    // 根据状态获取评论总数
    public function getCommentsCountByStatus($status, $search = null)
    {
        $builder = $this->select('comments.*')
            ->join('posts', 'posts.id = comments.post_id', 'left')
            ->join('users', 'users.id = comments.user_id', 'left')
            ->where('comments.status', $status);

        // 添加搜索条件
        if (!empty($search)) {
            $builder->groupStart()
                ->like('comments.content', $search)
                ->orLike('posts.title', $search)
                ->orLike('users.username', $search)
                ->orLike('comments.author_name', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    // 检查用户是否可以删除评论
    public function canDeleteComment($commentId, $userId, $userRole)
    {
        $comment = $this->find($commentId);
        if (!$comment) {
            return false;
        }

        // 管理员可以删除所有评论
        if ($userRole == 'admin') {
            return true;
        }

        // 编辑可以删除所有评论
        if ($userRole == 'editor') {
            return true;
        }

        // 用户只能删除自己的评论
        return $comment['user_id'] == $userId;
    }
}
