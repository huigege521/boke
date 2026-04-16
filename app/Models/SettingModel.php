<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 系统配置模型
 * 负责处理系统配置的数据库操作
 */
class SettingModel extends Model
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 返回类型
     *
     * @var string
     */
    protected $returnType = 'array';

    /**
     * 允许填充的字段
     *
     * @var array
     */
    protected $allowedFields = ['setting_key', 'setting_value', 'title', 'type', 'created_at', 'updated_at'];

    /**
     * 时间戳
     *
     * @var bool
     */
    protected $useTimestamps = true;

    /**
     * 创建时间字段名
     *
     * @var string
     */
    protected $createdField = 'created_at';

    /**
     * 更新时间字段名
     *
     * @var string
     */
    protected $updatedField = 'updated_at';

    /**
     * 通过键名获取配置值
     *
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed 配置值
     */
    public function getValue($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : $default;
    }

    /**
     * 获取所有配置
     *
     * @return array 配置数组
     */
    public function getAllSettings()
    {
        $settings = $this->findAll();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        return $result;
    }
}
