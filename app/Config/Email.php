<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * 邮件配置
 * 提供 SMTP 邮件发送的相关配置
 */
class Email extends BaseConfig
{
    /**
     * 邮件发送协议
     * - smtp: 使用 SMTP 服务器
     * - sendmail: 使用 sendmail 命令
     * - mail: 使用 PHP mail() 函数
     */
    public string $protocol = 'smtp';

    /**
     * SMTP 服务器地址
     * 例如：smtp.example.com, smtp.gmail.com, smtp.qq.com
     */
    public string $SMTPHost = 'smtp.163.com';

    /**
     * SMTP 用户名
     */
    public string $SMTPUser = '15100146754@163.com';

    /**
     * SMTP 密码
     */
    public string $SMTPPass = 'NQRdVdDEHayMLXjN';

    /**
     * SMTP 端口
     * - 587: TLS 加密
     * - 465: SSL 加密
     * - 25: 非加密
     */
    public int $SMTPPort = 465;

    /**
     * SMTP 连接超时时间（秒）
     */
    public int $SMTPTimeout = 5;

    /**
     * 发件人邮箱
     */
    public string $fromEmail = '15100146754@163.com';

    /**
     * 发件人名称
     */
    public string $fromName = '博客系统';

    /**
     * 是否启用调试模式
     */
    public bool $debug = false;
}