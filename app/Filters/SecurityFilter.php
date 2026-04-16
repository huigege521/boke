<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 安全过滤器
 * 提供 XSS 防护、SQL 注入防护、请求验证等安全功能
 */
class SecurityFilter implements FilterInterface
{
    /**
     * 需要排除的字段（富文本编辑器字段）
     */
    protected array $excludedFields = [
        'content',
        'about_features',
        'team_intro',
        'description',
        'excerpt',
        'csrf_token'
    ];

    /**
     * 请求前的安全处理
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. 检查请求头安全
        $this->checkSecurityHeaders($request);

        // 2. 检查请求方法
        if (!$this->isValidRequestMethod($request)) {
            log_message('warning', '非法请求方法: ' . $request->getMethod() . ' at ' . $request->getUri()->getPath());
            return service('response')->setStatusCode(405)->setBody('Method Not Allowed');
        }

        // 3. 检查请求大小
        if ($this->isRequestTooLarge($request)) {
            log_message('warning', '请求体过大: ' . $request->getHeaderLine('Content-Length') . ' at ' . $request->getUri()->getPath());
            return service('response')->setStatusCode(413)->setBody('Request Entity Too Large');
        }

        // 4. 检查恶意 User-Agent
        if ($this->isMaliciousUserAgent($request)) {
            log_message('warning', '恶意 User-Agent: ' . $request->getUserAgent()->getAgentString());
            return service('response')->setStatusCode(403)->setBody('Forbidden');
        }

        // 5. 过滤输入数据
        $this->sanitizeInput($request);

        // 6. 检查 SQL 注入
        if ($this->detectSqlInjection($request)) {
            log_message('warning', '检测到 SQL 注入攻击 at ' . $request->getUri()->getPath());
            return service('response')->setStatusCode(403)->setBody('Forbidden');
        }

        // 7. 检查 XSS 攻击
        if ($this->detectXssAttack($request)) {
            log_message('warning', '检测到 XSS 攻击 at ' . $request->getUri()->getPath());
            return service('response')->setStatusCode(403)->setBody('Forbidden');
        }

        return null;
    }

    /**
     * 响应后的安全处理
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 添加安全响应头
        $this->addSecurityHeaders($response);
    }

    /**
     * 检查安全请求头
     *
     * @param RequestInterface $request
     * @return void
     */
    private function checkSecurityHeaders(RequestInterface $request)
    {
        // 检查 Host 头，防止 Host 头攻击
        $host = $request->getHeaderLine('Host');
        $allowed_hosts = [
            'localhost',
            'localhost:8080',
            $_SERVER['SERVER_NAME'] ?? ''
        ];

        if (!in_array($host, $allowed_hosts)) {
            log_message('warning', '非法 Host 头: ' . $host);
        }
    }

    /**
     * 验证请求方法
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isValidRequestMethod(RequestInterface $request)
    {
        $allowed_methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'PATCH'];
        return in_array($request->getMethod(), $allowed_methods);
    }

    /**
     * 检查请求大小
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isRequestTooLarge(RequestInterface $request)
    {
        $content_length = $request->getHeaderLine('Content-Length');
        if ($content_length && $content_length > 10 * 1024 * 1024) { // 10MB
            return true;
        }
        return false;
    }

    /**
     * 检查恶意 User-Agent
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isMaliciousUserAgent(RequestInterface $request)
    {
        $user_agent = strtolower($request->getUserAgent()->getAgentString());

        $malicious_patterns = [
            'sqlmap',
            'nikto',
            'nmap',
            'masscan',
            'openvas',
            'acunetix',
            'netsparker',
            'appscan',
            'w3af',
            'skipfish',
            'wapiti',
            'zap',
            'arachni',
            'vega',
            'grabber',
            'grendel-scan',
            'owasp-zap'
        ];

        foreach ($malicious_patterns as $pattern) {
            if (strpos($user_agent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 清理输入数据
     *
     * @param RequestInterface $request
     * @return void
     */
    private function sanitizeInput(RequestInterface $request)
    {
        // 调试：打印原始POST参数
        $post_params = $request->getPost();
        if (!empty($post_params)) {
            file_put_contents('debug_post.txt', print_r($post_params, true));
        }

        // GET 参数清理
        $get_params = $request->getGet();
        if (!empty($get_params)) {
            foreach ($get_params as $key => $value) {
                if (is_string($value) && !in_array($key, $this->excludedFields)) {
                    $get_params[$key] = $this->sanitizeString($value);
                } elseif (is_array($value)) {
                    // 递归清理数组中的元素
                    foreach ($value as $itemKey => $itemValue) {
                        if (is_string($itemValue)) {
                            $get_params[$key][$itemKey] = $this->sanitizeString($itemValue);
                        }
                    }
                }
            }
            $request->setGlobal('get', $get_params);
        }

        // POST 参数清理（排除富文本编辑器字段和 CSRF 令牌）
        $post_params = $request->getPost();

        if (!empty($post_params)) {
            foreach ($post_params as $key => $value) {
                if (is_string($value) && !in_array($key, $this->excludedFields)) {
                    $post_params[$key] = $this->sanitizeString($value);
                } elseif (is_array($value)) {
                    // 递归清理数组中的元素
                    foreach ($value as $itemKey => $itemValue) {
                        if (is_string($itemValue)) {
                            $post_params[$key][$itemKey] = $this->sanitizeString($itemValue);
                        }
                    }
                }
            }
            $request->setGlobal('post', $post_params);
        }

        // 其他 HTTP 方法的参数清理
        $method = $request->getMethod();
        if (in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
            // 检查是否是通过POST方法模拟的PUT请求
            $post_params = $request->getPost();
            if (!empty($post_params)) {
                // 使用POST参数作为PUT参数
                $raw_params = $post_params;

                foreach ($raw_params as $key => $value) {
                    if (is_string($value) && !in_array($key, $this->excludedFields)) {
                        $raw_params[$key] = $this->sanitizeString($value);
                    } elseif (is_array($value)) {
                        // 递归清理数组中的元素
                        foreach ($value as $itemKey => $itemValue) {
                            if (is_string($itemValue)) {
                                $raw_params[$key][$itemKey] = $this->sanitizeString($itemValue);
                            }
                        }
                    }
                }
                // 将清理后的参数存储到 request 超全局变量中
                $request->setGlobal('request', $raw_params);
            } else {
                // 处理真正的PUT请求
                $raw_params = $request->getRawInput();
                if (!empty($raw_params)) {
                    foreach ($raw_params as $key => $value) {
                        if (is_string($value) && !in_array($key, $this->excludedFields)) {
                            $raw_params[$key] = $this->sanitizeString($value);
                        } elseif (is_array($value)) {
                            // 递归清理数组中的元素
                            foreach ($value as $itemKey => $itemValue) {
                                if (is_string($itemValue)) {
                                    $raw_params[$key][$itemKey] = $this->sanitizeString($itemValue);
                                }
                            }
                        }
                    }
                    // 将清理后的参数存储到 request 超全局变量中
                    $request->setGlobal('request', $raw_params);
                }
            }
        }
    }

    /**
     * 清理字符串
     *
     * @param string $str
     * @return string
     */
    private function sanitizeString($str)
    {
        // 移除 null 字节
        $str = str_replace(chr(0), '', $str);

        // 移除控制字符
        $str = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', '', $str);

        // 转义特殊字符
        $str = htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $str;
    }

    /**
     * 检测 SQL 注入
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function detectSqlInjection(RequestInterface $request)
    {
        $sql_patterns = [
            '/(\%27)|(\')|(\-\-)|(\%23)|(#)/i',
            '/((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i',
            '/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i',
            '/((\%27)|(\'))union/i',
            '/exec(\s|\+)+(s|x)p\w+/i',
            '/UNION\s+SELECT/i',
            '/INSERT\s+INTO/i',
            '/DELETE\s+FROM/i',
            '/DROP\s+TABLE/i',
            '/ALTER\s+TABLE/i',
            '/SCRIPT\s*>/i'
        ];

        $getParams = $request->getGet() ?? [];
        $postParams = $request->getPost() ?? [];
        $requestParams = $request->getVar() ?? [];

        // 排除富文本编辑器字段和 CSRF 令牌
        foreach ($this->excludedFields as $field) {
            unset($getParams[$field]);
            unset($postParams[$field]);
            unset($requestParams[$field]);
        }

        $params = array_merge($getParams, $postParams, $requestParams);

        foreach ($params as $value) {
            if (is_string($value)) {
                foreach ($sql_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            } elseif (is_array($value)) {
                // 递归检查数组中的元素
                foreach ($value as $item) {
                    if (is_string($item)) {
                        foreach ($sql_patterns as $pattern) {
                            if (preg_match($pattern, $item)) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * 检测 XSS 攻击
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function detectXssAttack(RequestInterface $request)
    {
        $xss_patterns = [
            '/<script[^>]*>[\s\S]*?<\/script>/i',
            '/<iframe[^>]*>[\s\S]*?<\/iframe>/i',
            '/<object[^>]*>[\s\S]*?<\/object>/i',
            '/<embed[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<\s*\/\s*script\s*>/i',
            '/<\s*script\s*>/i',
            '/eval\s*\(/i',
            '/expression\s*\(/i'
        ];

        $getParams = $request->getGet() ?? [];
        $postParams = $request->getPost() ?? [];
        $requestParams = $request->getVar() ?? [];

        // 排除富文本编辑器字段和 CSRF 令牌
        foreach ($this->excludedFields as $field) {
            unset($getParams[$field]);
            unset($postParams[$field]);
            unset($requestParams[$field]);
        }

        $params = array_merge($getParams, $postParams, $requestParams);

        foreach ($params as $value) {
            if (is_string($value)) {
                foreach ($xss_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            } elseif (is_array($value)) {
                // 递归检查数组中的元素
                foreach ($value as $item) {
                    if (is_string($item)) {
                        foreach ($xss_patterns as $pattern) {
                            if (preg_match($pattern, $item)) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * 添加安全响应头
     *
     * @param ResponseInterface $response
     * @return void
     */
    private function addSecurityHeaders(ResponseInterface $response)
    {
        // 防止点击劫持
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');

        // 防止 MIME 类型嗅探
        $response->setHeader('X-Content-Type-Options', 'nosniff');

        // XSS 防护
        $response->setHeader('X-XSS-Protection', '1; mode=block');

        // 内容安全策略
        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.ckeditor.com; " .
            "style-src 'self' 'unsafe-inline' cdn.ckeditor.com fonts.googleapis.com; " .
            "font-src 'self' fonts.gstatic.com; " .
            "img-src 'self' data: blob:; " .
            "connect-src 'self';";

        $response->setHeader('Content-Security-Policy', $csp);

        // 强制 HTTPS（生产环境启用）
        // $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // 引用策略
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 权限策略
        $response->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }
}
