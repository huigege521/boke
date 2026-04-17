<?php

namespace App\Models;

use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'posts_count'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // 获取所有标签
    public function getAllTags($limit = 100, $offset = 0)
    {
        return $this->orderBy('posts_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    // 获取标签总数
    public function getAllTagsCount()
    {
        return $this->countAllResults();
    }

    // 根据ID获取标签
    public function getTagById($id)
    {
        return $this->find($id);
    }

    // 根据slug获取标签
    public function getTagBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    // 获取热门标签
    public function getPopularTags($limit = 10)
    {
        return $this->where('posts_count >', 0)
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->findAll();
    }

    // 更新标签文章数量
    public function updatePostsCount($tagId)
    {
        $db = \Config\Database::connect();
        $count = $db->table('post_tags')
            ->join('posts', 'posts.id = post_tags.post_id')
            ->where('post_tags.tag_id', $tagId)
            ->where('posts.status', 'published')
            ->countAllResults();

        return $this->update($tagId, ['posts_count' => $count]);
    }

    // 批量更新标签文章数量
    public function updateAllPostsCount()
    {
        $tags = $this->findAll();
        foreach ($tags as $tag) {
            $this->updatePostsCount($tag['id']);
        }
    }

    // 检查标签是否存在
    public function tagExists($id)
    {
        return $this->find($id) !== null;
    }

    // 检查标签名称是否已存在
    public function tagNameExists($name, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('name', $name);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    // 检查标签slug是否已存在
    public function tagSlugExists($slug, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('slug', $slug);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    // 根据文章ID获取标签
    public function getTagsByPostId($postId)
    {
        $db = \Config\Database::connect();
        return $db->table('tags')
            ->join('post_tags', 'post_tags.tag_id = tags.id')
            ->where('post_tags.post_id', $postId)
            ->orderBy('tags.name', 'asc')
            ->findAll();
    }

    // 根据多个文章ID获取标签
    public function getTagsByPostIds($postIds)
    {
        $db = \Config\Database::connect();
        return $db->table('tags')
            ->select('tags.*, post_tags.post_id')
            ->join('post_tags', 'post_tags.tag_id = tags.id')
            ->whereIn('post_tags.post_id', $postIds)
            ->orderBy('tags.name', 'asc')
            ->findAll();
    }
}