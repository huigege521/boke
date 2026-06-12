<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

class Exceptions extends BaseConfig
{
    public bool $log = true;

    public array $ignoreCodes = [404];

    public string $errorViewPath = APPPATH . 'Views/errors';

    public array $sensitiveDataInTrace = [];

    public bool $logDeprecations = true;

    public string $deprecationLogLevel = LogLevel::WARNING;

    /**
     * 是否显示详细错误信息（生产环境应设置为false）
     */
    public bool $showDebug = false;

    /**
     * 是否记录错误日志
     */
    public bool $logErrors = true;

    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new ExceptionHandler($this);
    }
}
