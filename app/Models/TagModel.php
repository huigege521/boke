<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 标签模型
 * 
 * 处理标签数据的增删改查操作，支持与文章的关联管理
 * 
 * @package App\Models
 */
class TagModel extends Model
{
    /**
     * @var string 数据表名
     */
    protected $table = 'tags';

    /**
     * @var string 主键字段
     */
    protected $primaryKey = 'id';

    /**
     * @var bool 是否启用自增
     */
    protected $useAutoIncrement = true;

    /**
     * @var string 返回数据类型
     */
    protected $returnType = 'array';

    /**
     * @var bool 是否启用软删除
     */
    protected $useSoftDeletes = false;

    /**
     * @var bool 是否保护字段
     */
    protected $protectFields = true;

    /**
     * @var array 允许操作的字段列表
     */
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'posts_count'
    ];

    /**
     * @var bool 是否启用时间戳
     */
    protected $useTimestamps = true;

    /**
     * @var string 创建时间字段名
     */
    protected $createdField = 'created_at';

    /**
     * @var string 更新时间字段名
     */
    protected $updatedField = 'updated_at';

    /**
     * 获取所有标签
     * 
     * @param int $limit 限制数量
     * @param int $offset 偏移量
     * @return array 标签列表
     */
    public function getAllTags($limit = 100, $offset = 0)
    {
        return $this->orderBy('posts_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * 获取标签总数
     * 
     * @return int 标签总数
     */
    public function getAllTagsCount()
    {
        return $this->countAllResults();
    }

    /**
     * 根据ID获取标签
     * 
     * @param int $id 标签ID
     * @return array|null 标签数据
     */
    public function getTagById($id)
    {
        return $this->find($id);
    }

    /**
     * 根据slug获取标签
     * 
     * @param string $slug 标签别名
     * @return array|null 标签数据
     */
    public function getTagBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * 获取热门标签
     * 
     * @param int $limit 限制数量
     * @return array 热门标签列表
     */
    public function getPopularTags($limit = 10)
    {
        return $this->where('posts_count >', 0)
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 更新标签文章数量
     * 
     * @param int $tagId 标签ID
     * @return bool 是否更新成功
     */
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

    /**
     * 批量更新标签文章数量
     * 
     * @return void
     */
    public function updateAllPostsCount()
    {
        $tags = $this->findAll();
        foreach ($tags as $tag) {
            $this->updatePostsCount($tag['id']);
        }
    }

    /**
     * 检查标签是否存在
     * 
     * @param int $id 标签ID
     * @return bool 是否存在
     */
    public function tagExists($id)
    {
        return $this->find($id) !== null;
    }

    /**
     * 检查标签名称是否已存在
     * 
     * @param string $name 标签名称
     * @param int|null $excludeId 排除的标签ID（更新时使用）
     * @return bool 是否存在
     */
    public function tagNameExists($name, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('name', $name);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * 检查标签slug是否已存在
     * 
     * @param string $slug 标签别名
     * @param int|null $excludeId 排除的标签ID（更新时使用）
     * @return bool 是否存在
     */
    public function tagSlugExists($slug, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('slug', $slug);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * 根据文章ID获取标签
     * 
     * @param int $postId 文章ID
     * @return array 标签列表
     */
    public function getTagsByPostId($postId)
    {
        $db = \Config\Database::connect();
        return $db->table('tags')
            ->join('post_tags', 'post_tags.tag_id = tags.id')
            ->where('post_tags.post_id', $postId)
            ->orderBy('tags.name', 'asc')
            ->get()
            ->getResultArray();
    }

    /**
     * 根据多个文章ID获取标签
     * 
     * @param array $postIds 文章ID数组
     * @return array 标签列表（包含post_id字段）
     */
    public function getTagsByPostIds($postIds)
    {
        $db = \Config\Database::connect();
        return $db->table('tags')
            ->select('tags.*, post_tags.post_id')
            ->join('post_tags', 'post_tags.tag_id = tags.id')
            ->whereIn('post_tags.post_id', $postIds)
            ->orderBy('tags.name', 'asc')
            ->get()
            ->getResultArray();
    }
}