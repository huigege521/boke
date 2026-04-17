<?php

namespace Config;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\ApcuHandler;
use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    /**
     * 缓存处理器
     * 可选值: file | redis | memcached | predis | apcu | wincache | dummy
     * 
     * 生产环境建议使用 redis，开发环境使用 file
     */
    public string $handler = 'file';

    /**
     * 备用缓存处理器（当主处理器失败时使用）
     */
    public string $backupHandler = 'dummy';

    /**
     * 缓存键前缀
     * 用于区分不同应用的缓存数据
     */
    public string $prefix = 'blog_';

    /**
     * 默认缓存过期时间（秒）
     */
    public int $ttl = 3600; // 1小时

    /**
     * Redis保留字符（用于文件缓存路径）
     */
    public string $reservedCharacters = '{}()/\@:';

    /**
     * 文件缓存配置
     */
    public array $file = [
        'storePath' => WRITEPATH . 'cache/',
        'mode'      => 0640,
    ];

    /**
     * Memcached缓存配置
     */
    public array $memcached = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];

    /**
     * Redis缓存配置
     * 
     * 生产环境建议：
     * - 设置密码保护
     * - 启用持久连接
     * - 根据服务器内存调整数据库编号
     */
    public array $redis = [
        'host'       => '127.0.0.1',
        'password'   => null, // 生产环境请设置强密码
        'port'       => 6379,
        'timeout'    => 0, // 连接超时时间（0表示不限制）
        'async'      => false,
        'persistent' => true, // 启用持久连接提升性能
        'database'   => 0, // Redis数据库编号（0-15）
        'prefix'     => 'blog_', // Redis键前缀
    ];

    /**
     * Predis缓存配置（如果使用Predis库）
     */
    public array $predis = [
        'host'       => '127.0.0.1',
        'password'   => null,
        'port'       => 6379,
        'timeout'    => 0,
        'read_write_timeout' => 0,
        'database'   => 0,
    ];

    /**
     * 有效的缓存处理器映射
     */
    public array $validHandlers = [
        'apcu'      => ApcuHandler::class,
        'dummy'     => DummyHandler::class,
        'file'      => FileHandler::class,
        'memcached' => MemcachedHandler::class,
        'predis'    => PredisHandler::class,
        'redis'     => RedisHandler::class,
        'wincache'  => WincacheHandler::class,
    ];

    /**
     * 是否在缓存中保存查询字符串
     */
    public $cacheQueryString = false;

    /**
     * 需要缓存的HTTP状态码
     * 空数组表示缓存所有成功响应
     */
    public array $cacheStatusCodes = [];
}
