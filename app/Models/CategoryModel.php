<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'parent_id',
        'order',
        'posts_count',
        'icon'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // 获取所有分类（包含父分类名称）
    public function getAllCategories($limit = 10, $offset = 0)
    {
        return $this->select('categories.*, COALESCE(parent.name, \'\') as parent_name')
            ->join('categories as parent', 'parent.id = categories.parent_id', 'left')
            ->orderBy('categories.order', 'asc')
            ->orderBy('categories.created_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    // 获取分类总数
    public function getAllCategoriesCount()
    {
        return $this->countAllResults();
    }

    // 根据ID获取分类
    public function getCategoryById($id)
    {
        return $this->find($id);
    }

    // 根据slug获取分类
    public function getCategoryBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    // 获取所有顶级分类
    public function getTopLevelCategories()
    {
        return $this->where('parent_id', null)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->findAll();
    }

    // 获取子分类
    public function getChildCategories($parentId)
    {
        return $this->where('parent_id', $parentId)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->findAll();
    }

    // 获取分类树（包含父分类名称）
    public function getCategoryTree($parentId = null, $level = 0)
    {
        $categories = $this->select('categories.*, COALESCE(parent.name, \'\') as parent_name')
            ->join('categories as parent', 'parent.id = categories.parent_id', 'left')
            ->where('categories.parent_id', $parentId)
            ->orderBy('categories.order', 'asc')
            ->findAll();

        $result = [];
        foreach ($categories as $category) {
            $category['level'] = $level;
            $result[] = $category;
            // 递归获取子分类
            $children = $this->getCategoryTree($category['id'], $level + 1);
            $result = array_merge($result, $children);
        }

        return $result;
    }

    // 更新分类文章数量
    public function updatePostsCount($categoryId)
    {
        $db = \Config\Database::connect();
        $count = $db->table('posts')
            ->where('category_id', $categoryId)
            ->where('status', 'published')
            ->countAllResults();

        return $this->update($categoryId, ['posts_count' => $count]);
    }

    // 批量更新分类文章数量
    public function updateAllPostsCount()
    {
        $categories = $this->findAll();
        foreach ($categories as $category) {
            $this->updatePostsCount($category['id']);
        }
    }

    // 检查分类是否存在
    public function categoryExists($id)
    {
        return $this->find($id) !== null;
    }

    // 检查分类名称是否已存在
    public function categoryNameExists($name, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('name', $name);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    // 检查分类slug是否已存在
    public function categorySlugExists($slug, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('slug', $slug);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }
}
