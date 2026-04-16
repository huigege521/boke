<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 联系表单模型
 * 负责处理联系表单的数据库操作
 */
class ContactModel extends Model
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'contacts';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 允许填充的字段
     *
     * @var array
     */
    protected $allowedFields = ['name', 'email', 'subject', 'message', 'status', 'created_at', 'updated_at'];

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
     * 验证规则
     *
     * @var array
     */
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'email' => 'required|valid_email|max_length[100]',
        'subject' => 'required|min_length[3]|max_length[200]',
        'message' => 'required|min_length[5]'
    ];

    /**
     * 验证错误消息
     *
     * @var array
     */
    protected $validationMessages = [
        'name' => [
            'required' => '姓名不能为空',
            'min_length' => '姓名长度不能少于2个字符',
            'max_length' => '姓名长度不能超过100个字符'
        ],
        'email' => [
            'required' => '邮箱不能为空',
            'valid_email' => '请输入有效的邮箱地址',
            'max_length' => '邮箱长度不能超过100个字符'
        ],
        'subject' => [
            'required' => '主题不能为空',
            'min_length' => '主题长度不能少于3个字符',
            'max_length' => '主题长度不能超过200个字符'
        ],
        'message' => [
            'required' => '消息内容不能为空',
            'min_length' => '消息内容长度不能少于5个字符'
        ]
    ];

    /**
     * 获取所有联系消息
     *
     * @return array 联系消息列表
     */
    public function getAllContacts()
    {
        return $this->orderBy('created_at', 'desc')->findAll();
    }

    /**
     * 根据ID获取联系消息
     *
     * @param int $id 联系消息ID
     * @return array 联系消息详情
     */
    public function getContactById($id)
    {
        return $this->find($id);
    }

    /**
     * 统计联系消息数量
     *
     * @return int 联系消息数量
     */
    public function countContacts()
    {
        return $this->countAllResults();
    }
}
