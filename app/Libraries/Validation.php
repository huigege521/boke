<?php

namespace App\Libraries;

/**
 * 输入验证类
 * 提供全面的输入验证功能
 */
class Validation
{
    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [];

    /**
     * 验证错误信息
     *
     * @var array
     */
    protected $errors = [];

    /**
     * 自定义错误消息
     *
     * @var array
     */
    protected $messages = [];

    /**
     * 验证的数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 设置验证规则
     *
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * 设置自定义错误消息
     *
     * @param array $messages
     * @return $this
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * 执行验证
     *
     * @param array $data
     * @return bool
     */
    public function run(array $data = [])
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $rules_array = is_string($rules) ? explode('|', $rules) : $rules;
            $value = $data[$field] ?? null;

            foreach ($rules_array as $rule) {
                $rule_parts = explode(':', $rule);
                $rule_name = $rule_parts[0];
                $rule_param = $rule_parts[1] ?? null;

                if (!$this->validateRule($field, $value, $rule_name, $rule_param)) {
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * 验证单个规则
     *
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @param string|null $param
     * @return bool
     */
    protected function validateRule($field, $value, $rule, $param = null)
    {
        $method = 'validate' . ucfirst($rule);

        if (method_exists($this, $method)) {
            $result = $this->$method($value, $param);
        } else {
            // 使用 CodeIgniter 的验证规则
            $result = $this->validateWithCI($field, $value, $rule, $param);
        }

        if (!$result) {
            $this->errors[$field] = $this->getErrorMessage($field, $rule, $param);
            return false;
        }

        return true;
    }

    /**
     * 使用 CodeIgniter 验证
     *
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @param string|null $param
     * @return bool
     */
    protected function validateWithCI($field, $value, $rule, $param = null)
    {
        $validation = \Config\Services::validation();
        $validation->setRules([$field => $rule]);
        return $validation->run([$field => $value]);
    }

    /**
     * 获取错误消息
     *
     * @param string $field
     * @param string $rule
     * @param string|null $param
     * @return string
     */
    protected function getErrorMessage($field, $rule, $param = null)
    {
        $key = $field . '.' . $rule;

        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }

        $defaultMessages = [
            'required' => '{field} 是必填项',
            'min_length' => '{field} 至少需要 {param} 个字符',
            'max_length' => '{field} 最多允许 {param} 个字符',
            'exact_length' => '{field} 必须是 {param} 个字符',
            'alpha' => '{field} 只能包含字母',
            'alpha_numeric' => '{field} 只能包含字母和数字',
            'alpha_dash' => '{field} 只能包含字母、数字、下划线和破折号',
            'numeric' => '{field} 必须是数字',
            'integer' => '{field} 必须是整数',
            'decimal' => '{field} 必须是十进制数',
            'is_natural' => '{field} 必须是自然数',
            'is_natural_no_zero' => '{field} 必须是大于零的自然数',
            'valid_url' => '{field} 必须是有效的 URL',
            'valid_email' => '{field} 必须是有效的邮箱地址',
            'valid_ip' => '{field} 必须是有效的 IP 地址',
            'valid_base64' => '{field} 必须是有效的 Base64 字符串',
            'valid_json' => '{field} 必须是有效的 JSON 字符串',
            'valid_date' => '{field} 必须是有效的日期',
            'matches' => '{field} 与 {param} 不匹配',
            'is_unique' => '{field} 已存在',
            'in_list' => '{field} 必须是以下之一: {param}',
            'regex_match' => '{field} 格式不正确',
            'differs' => '{field} 不能与 {param} 相同',
            'is_not_unique' => '{field} 不存在',
            'greater_than' => '{field} 必须大于 {param}',
            'greater_than_equal_to' => '{field} 必须大于或等于 {param}',
            'less_than' => '{field} 必须小于 {param}',
            'less_than_equal_to' => '{field} 必须小于或等于 {param}',
            'alpha_space' => '{field} 只能包含字母和空格',
            'valid_phone' => '{field} 必须是有效的电话号码',
            'valid_username' => '{field} 只能包含字母、数字、下划线和破折号，且必须以字母开头',
            'valid_slug' => '{field} 只能包含小写字母、数字和破折号',
            'no_html' => '{field} 不能包含 HTML 标签',
            'safe_password' => '{field} 必须包含至少一个大写字母、一个小写字母和一个数字',
        ];

        $message = $defaultMessages[$rule] ?? '{field} 验证失败';

        // 替换占位符
        $message = str_replace('{field}', $field, $message);
        $message = str_replace('{param}', $param, $message);

        return $message;
    }

    /**
     * 获取所有错误
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 获取第一个错误
     *
     * @return string|null
     */
    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * 获取指定字段的错误
     *
     * @param string $field
     * @return string|null
     */
    public function getError($field)
    {
        return $this->errors[$field] ?? null;
    }

    // ==================== 自定义验证规则 ====================

    /**
     * 验证必填
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateRequired($value)
    {
        return $value !== null && $value !== '';
    }

    /**
     * 验证最小长度
     *
     * @param mixed $value
     * @param string $param
     * @return bool
     */
    protected function validateMin_length($value, $param)
    {
        return strlen((string) $value) >= (int) $param;
    }

    /**
     * 验证最大长度
     *
     * @param mixed $value
     * @param string $param
     * @return bool
     */
    protected function validateMax_length($value, $param)
    {
        return strlen((string) $value) <= (int) $param;
    }

    /**
     * 验证邮箱
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValid_email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证 URL
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValid_url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 验证电话号码
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValid_phone($value)
    {
        // 支持中国大陆手机号格式
        return preg_match('/^1[3-9]\d{9}$/', $value) ||
               preg_match('/^(\d{3,4}-)?\d{7,8}$/', $value);
    }

    /**
     * 验证用户名
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValid_username($value)
    {
        // 必须以字母开头，只能包含字母、数字、下划线和破折号
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{3,31}$/', $value);
    }

    /**
     * 验证别名（slug）
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValid_slug($value)
    {
        // 只能包含小写字母、数字和破折号
        return preg_match('/^[a-z0-9-]+$/', $value);
    }

    /**
     * 验证不包含 HTML
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateNo_html($value)
    {
        return $value === strip_tags($value);
    }

    /**
     * 验证安全密码
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateSafe_password($value)
    {
        // 至少8位，包含大小写字母和数字
        return strlen($value) >= 8 &&
               preg_match('/[A-Z]/', $value) &&
               preg_match('/[a-z]/', $value) &&
               preg_match('/[0-9]/', $value);
    }

    /**
     * 验证 JSON
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValid_json($value)
    {
        if (!is_string($value)) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 验证日期
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValid_date($value)
    {
        if (empty($value)) {
            return false;
        }
        $timestamp = strtotime($value);
        return $timestamp !== false;
    }

    /**
     * 验证字段匹配
     *
     * @param mixed $value
     * @param string $param
     * @return bool
     */
    protected function validateMatches($value, $param)
    {
        return $value === ($this->data[$param] ?? null);
    }

    /**
     * 验证在列表中
     *
     * @param mixed $value
     * @param string $param
     * @return bool
     */
    protected function validateIn_list($value, $param)
    {
        $list = explode(',', $param);
        return in_array($value, $list);
    }

    /**
     * 验证正则匹配
     *
     * @param mixed $value
     * @param string $param
     * @return bool
     */
    protected function validateRegex_match($value, $param)
    {
        return preg_match($param, $value) === 1;
    }
}
