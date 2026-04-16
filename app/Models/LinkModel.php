<?php

namespace App\Models;

use CodeIgniter\Model;

class LinkModel extends Model
{
    protected $table = 'links';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'url', 'description', 'logo', 'sort_order', 'status'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * 获取所有活跃的友情链接
     *
     * @return array
     */
    public function getActiveLinks()
    {
        return $this->where('status', 'active')->findAll();
    }
}
