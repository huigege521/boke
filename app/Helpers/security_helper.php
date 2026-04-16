<?php
/**
 * 安全辅助函数
 * 提供 XSS 过滤、输入验证等安全功能
 */

if (!function_exists('xss_clean')) {
    /**
     * XSS 过滤函数
     * 过滤用户输入，防止 XSS 攻击
     *
     * @param string|array $data 要过滤的数据
     * @param bool $is_image 是否是图片数据
     * @return string|array 过滤后的数据
     */
    function xss_clean($data, $is_image = false)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = xss_clean($value, $is_image);
            }
            return $data;
        }

        // 修复非法 UTF-8 字符
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 移除危险标签和属性
        $data = remove_invisible_characters($data);

        // 过滤 JavaScript 事件处理器
        $data = preg_replace_callback(
            '#<\s*([a-zA-Z][a-zA-Z0-9]*)[^>]*?(\s+on\w+\s*=\s*["\'][^"\']*["\'])*#i',
            function ($matches) {
                return '<' . $matches[1];
            },
            $data
        );

        // 过滤 javascript: 和 vbscript: 伪协议
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iU', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iU', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#i', '$1=$2nomozbinding...', $data);

        // 仅允许特定的 HTML 标签（白名单）
        $allowed_tags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><code><pre><a><img><table><thead><tbody><tr><th><td><div><span><iframe>';
        $data = strip_tags($data, $allowed_tags);

        return $data;
    }
}

if (!function_exists('remove_invisible_characters')) {
    /**
     * 移除不可见字符
     *
     * @param string $str 输入字符串
     * @param bool $url_encoded 是否包含 URL 编码的字符
     * @return string 处理后的字符串
     */
    function remove_invisible_characters($str, $url_encoded = true)
    {
        $non_displayables = [];

        // 控制字符数组
        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/i';    // url 编码 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/i';     // url 编码 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]+/S';   // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * 清理文件名，防止路径遍历攻击
     *
     * @param string $filename 文件名
     * @return string 清理后的文件名
     */
    function sanitize_filename($filename)
    {
        // 移除路径信息
        $filename = basename($filename);

        // 移除危险的字符
        $bad = [
            '../',
            '<!--',
            '-->',
            '<',
            '>',
            "'",
            '"',
            '&',
            '$',
            '#',
            '{',
            '}',
            '[',
            ']',
            '=',
            ';',
            '?',
            '%20',
            '%22',
            '%3c',
            '%253c',
            '%3e',
            '%0e',
            '%28',
            '%29',
            '%2528',
            '%26',
            '%24',
            '%3f',
            '%3b',
            '%3d'
        ];

        $filename = str_replace($bad, '', $filename);

        // 移除 PHP 标签
        $filename = preg_replace('/\.(php[0-9]?|phtml|pl|py|jsp|asp|sh|cgi)/i', '.txt', $filename);

        return $filename;
    }
}

if (!function_exists('escape_html')) {
    /**
     * HTML 实体编码
     * 将特殊字符转换为 HTML 实体
     *
     * @param string $text 要编码的文本
     * @return string 编码后的文本
     */
    function escape_html($text)
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('validate_email')) {
    /**
     * 验证邮箱地址
     *
     * @param string $email 邮箱地址
     * @return bool 是否有效
     */
    function validate_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validate_url')) {
    /**
     * 验证 URL 地址
     *
     * @param string $url URL 地址
     * @param bool $strict 是否严格模式（只允许 http/https）
     * @return bool 是否有效
     */
    function validate_url($url, $strict = true)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if ($strict) {
            $scheme = parse_url($url, PHP_URL_SCHEME);
            return in_array($scheme, ['http', 'https']);
        }

        return true;
    }
}

if (!function_exists('hash_password')) {
    /**
     * 密码哈希
     * 使用安全的算法对密码进行哈希
     *
     * @param string $password 原始密码
     * @return string 哈希后的密码
     */
    function hash_password($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
}

if (!function_exists('verify_password')) {
    /**
     * 验证密码
     *
     * @param string $password 原始密码
     * @param string $hash 哈希值
     * @return bool 是否匹配
     */
    function verify_password($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

if (!function_exists('generate_csrf_token')) {
    /**
     * 生成 CSRF 令牌
     *
     * @return string CSRF 令牌
     */
    function generate_csrf_token()
    {
        $token = bin2hex(random_bytes(32));
        session()->set('csrf_token', $token);
        return $token;
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * 验证 CSRF 令牌
     *
     * @param string $token 要验证的令牌
     * @return bool 是否有效
     */
    function verify_csrf_token($token)
    {
        $session_token = session()->get('csrf_token');
        return $session_token && hash_equals($session_token, $token);
    }
}

if (!function_exists('rate_limit_check')) {
    /**
     * 速率限制检查
     * 防止暴力破解和滥用
     *
     * @param string $key 限制键（如 IP 地址或用户 ID）
     * @param int $max_attempts 最大尝试次数
     * @param int $time_window 时间窗口（秒）
     * @return bool 是否允许请求
     */
    function rate_limit_check($key, $max_attempts = 5, $time_window = 300)
    {
        $cache = \Config\Services::cache();
        $cache_key = 'rate_limit_' . md5($key);

        $attempts = $cache->get($cache_key);

        if ($attempts === null) {
            $cache->save($cache_key, 1, $time_window);
            return true;
        }

        if ($attempts >= $max_attempts) {
            return false;
        }

        $cache->save($cache_key, $attempts + 1, $time_window);
        return true;
    }
}

if (!function_exists('log_security_event')) {
    /**
     * 记录安全事件
     *
     * @param string $event 事件类型
     * @param string $message 事件描述
     * @param array $data 附加数据
     * @return void
     */
    function log_security_event($event, $message, $data = [])
    {
        $log_data = [
            'event' => $event,
            'message' => $message,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];

        log_message('error', '[SECURITY] ' . json_encode($log_data, JSON_UNESCAPED_UNICODE));
    }
}
