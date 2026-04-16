<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * 安全配置
 * 提供 CSRF 保护、安全头等安全相关配置
 */
class Security extends BaseConfig
{
    /**
     * CSRF 保护模式
     * - cookie: 使用 Cookie 存储令牌
     * - session: 使用 Session 存储令牌
     */
    public string $csrfProtection = 'session';

    /**
     * 验证 CSRF 头
     */
    public bool $validateCsrfHeader = true;

    /**
     * 随机化令牌
     * 每次请求后重新生成令牌
     */
    public bool $tokenRandomize = true;

    /**
     * CSRF 令牌名称
     */
    public string $tokenName = 'csrf_token';

    /**
     * CSRF 头名称
     */
    public string $headerName = 'X-CSRF-TOKEN';

    /**
     * CSRF Cookie 名称
     */
    public string $cookieName = 'csrf_cookie';

    /**
     * CSRF 令牌过期时间（秒）
     */
    public int $expires = 3600;

    /**
     * 重新生成令牌
     */
    public bool $regenerate = true;

    /**
     * 验证失败时重定向
     */
    public bool $redirect = false;

    /**
     * 允许的域名（用于 CORS）
     */
    public array $allowedOrigins = [
        'http://localhost',
        'http://localhost:8080',
    ];

    /**
     * 允许的方法（用于 CORS）
     */
    public array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

    /**
     * 允许的请求头（用于 CORS）
     */
    public array $allowedHeaders = [
        'Content-Type',
        'X-CSRF-TOKEN',
        'X-Requested-With',
        'Authorization'
    ];

    /**
     * 是否允许携带凭证（用于 CORS）
     */
    public bool $allowCredentials = true;

    /**
     * 最大请求体大小（字节）
     */
    public int $maxRequestSize = 10485760; // 10MB

    /**
     * 速率限制配置
     */
    public array $rateLimit = [
        'enabled' => true,
        'default' => [
            'max_requests' => 60,
            'time_window' => 60, // 1分钟
        ],
        'login' => [
            'max_requests' => 5,
            'time_window' => 300, // 5分钟
        ],
        'api' => [
            'max_requests' => 100,
            'time_window' => 60, // 1分钟
        ],
    ];

    /**
     * 密码策略
     */
    public array $passwordPolicy = [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_special' => false,
        'max_age_days' => 90, // 密码最大有效期
    ];

    /**
     * 会话安全配置
     */
    public array $sessionSecurity = [
        'regenerate_id' => true,
        'regenerate_interval' => 300, // 5分钟
        'invalidate_on_ip_change' => false,
        'invalidate_on_user_agent_change' => false,
    ];

    /**
     * 内容安全策略
     */
    public array $contentSecurityPolicy = [
        'default_src' => ["'self'"],
        'script_src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'cdn.ckeditor.com', 'cdn.jsdelivr.net', 'code.jquery.com', 'cdnjs.cloudflare.com'],
        'style_src' => ["'self'", "'unsafe-inline'", 'cdn.jsdelivr.net', 'cdnjs.cloudflare.com', 'fonts.googleapis.com'],
        'img_src' => ["'self'", 'data:', 'blob:', '*.gravatar.com'],
        'font_src' => ["'self'", 'fonts.gstatic.com', 'cdnjs.cloudflare.com'],
        'frame_src' => ["'self'"],
        'connect_src' => ["'self'"],
    ];
}
