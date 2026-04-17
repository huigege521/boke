<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
    ];

    public $classmap = [];

    /**
     * 自动加载的文件
     * 用于加载辅助函数文件等
     */
    public $files = [
        APPPATH . 'Helpers/cdn_helper.php', // CDN辅助函数
    ];

    /**
     * 自动加载的助手函数
     */
    public $helpers = [
        'url',      // URL辅助函数
        'html',     // HTML辅助函数
        'form',     // 表单辅助函数
        'text',     // 文本辅助函数
        'security', // 安全辅助函数
    ];
}
