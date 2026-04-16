<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Validation;

/**
 * 基础控制器
 * 提供安全输入处理、验证等通用功能
 */
class BaseController extends Controller
{
    /**
     * 验证库实例
     *
     * @var Validation
     */
    protected $validation;

    /**
     * 构造函数
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 初始化验证库
        $this->validation = new Validation();

        // 加载安全辅助函数
        helper('security');
    }

    /**
     * 安全获取 GET 参数
     *
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @param bool $xss_clean 是否进行 XSS 清理
     * @return mixed
     */
    protected function getGet($key = null, $default = null, $xss_clean = true)
    {
        $value = $this->request->getGet($key);

        if ($value === null) {
            return $default;
        }

        if ($xss_clean && is_string($value)) {
            $value = xss_clean($value);
        }

        return $value;
    }

    /**
     * 安全获取 POST 参数
     *
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @param bool $xss_clean 是否进行 XSS 清理
     * @return mixed
     */
    protected function getPost($key = null, $default = null, $xss_clean = true)
    {
        $value = $this->request->getPost($key);

        if ($value === null) {
            return $default;
        }

        // 对于富文本字段，跳过 XSS 清理
        $rich_text_fields = ['content', 'about_features', 'team_intro', 'description', 'excerpt'];

        if ($xss_clean && is_string($value) && !in_array($key, $rich_text_fields)) {
            $value = xss_clean($value);
        }

        return $value;
    }

    /**
     * 安全获取 JSON 输入
     *
     * @param bool $assoc 是否返回数组
     * @return mixed
     */
    protected function getJsonInput($assoc = true)
    {
        $input = $this->request->getJSON($assoc);

        if (is_array($input)) {
            array_walk_recursive($input, function (&$value) {
                if (is_string($value)) {
                    $value = xss_clean($value);
                }
            });
        }

        return $input;
    }

    /**
     * 验证输入数据
     *
     * @param mixed $rules 验证规则
     * @param array $messages 自定义错误消息
     * @return bool
     */
    protected function validate($rules, array $messages = []): bool
    {
        $this->validation->setRules($rules);

        if (!empty($messages)) {
            $this->validation->setMessages($messages);
        }

        return $this->validation->run($this->request->getPost());
    }

    /**
     * 获取验证错误
     *
     * @return array
     */
    protected function getValidationErrors()
    {
        return $this->validation->getErrors();
    }

    /**
     * 获取第一个验证错误
     *
     * @return string|null
     */
    protected function getFirstValidationError()
    {
        return $this->validation->getFirstError();
    }

    /**
     * 返回 JSON 响应
     *
     * @param mixed $data 响应数据
     * @param int $statusCode HTTP 状态码
     * @return \CodeIgniter\HTTP\Response
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        // 添加新的 CSRF 令牌到响应中
        $data['csrf_token'] = csrf_hash();
        $data['csrf_name'] = config('Security')->tokenName;

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回成功响应
     *
     * @param string $message 成功消息
     * @param mixed $data 附加数据
     * @return \CodeIgniter\HTTP\Response
     */
    protected function successResponse($message = '操作成功', $data = null)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->jsonResponse($response);
    }

    /**
     * 返回错误响应
     *
     * @param string $message 错误消息
     * @param int $statusCode HTTP 状态码
     * @param mixed $errors 详细错误信息
     * @return \CodeIgniter\HTTP\Response
     */
    protected function errorResponse($message = '操作失败', $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->jsonResponse($response, $statusCode);
    }

    /**
     * 检查速率限制
     *
     * @param string $key 限制键
     * @param int $maxAttempts 最大尝试次数
     * @param int $timeWindow 时间窗口（秒）
     * @return bool
     */
    protected function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300)
    {
        return rate_limit_check($key, $maxAttempts, $timeWindow);
    }

    /**
     * 获取客户端 IP 地址
     *
     * @return string
     */
    protected function getClientIp()
    {
        $ip = $this->request->getIPAddress();
        return $ip === '::1' ? '127.0.0.1' : $ip;
    }

    /**
     * 记录安全日志
     *
     * @param string $event 事件类型
     * @param string $message 事件描述
     * @param array $data 附加数据
     * @return void
     */
    protected function logSecurity($event, $message, $data = [])
    {
        log_security_event($event, $message, array_merge($data, [
            'ip' => $this->getClientIp(),
            'uri' => $this->request->getUri()->getPath(),
            'method' => $this->request->getMethod()
        ]));
    }

    /**
     * 生成 CSRF 令牌
     *
     * @return string
     */
    protected function generateCsrfToken()
    {
        return generate_csrf_token();
    }

    /**
     * 验证 CSRF 令牌
     *
     * @param string $token
     * @return bool
     */
    protected function verifyCsrfToken($token)
    {
        return verify_csrf_token($token);
    }

    /**
     * 转义 HTML 输出
     *
     * @param string $text
     * @return string
     */
    protected function escapeHtml($text)
    {
        return escape_html($text);
    }

    /**
     * 清理文件名
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename($filename)
    {
        return sanitize_filename($filename);
    }
}
