<?php

/**
 * CDN 辅助函数
 * 用于处理静态资源的CDN加速
 */

if (!function_exists('cdn_asset')) {
    /**
     * 将本地资源路径转换为CDN路径
     * 
     * @param string $path 资源路径（相对于public目录）
     * @param bool $forceHttps 是否强制使用HTTPS
     * @return string CDN URL或本地URL
     * 
     * 示例:
     * cdn_asset('css/app.css') => https://cdn.example.com/css/app.css
     * cdn_asset('js/app.js', true) => https://cdn.example.com/js/app.js
     */
    function cdn_asset(string $path, bool $forceHttps = false): string
    {
        // 获取CDN配置
        $cdnUrl = env('app.cdnUrl', '');
        
        // 如果没有配置CDN，返回本地路径
        if (empty($cdnUrl)) {
            return base_url($path);
        }
        
        // 移除开头的斜杠
        $path = ltrim($path, '/');
        
        // 构建CDN URL
        $cdnPath = rtrim($cdnUrl, '/') . '/' . $path;
        
        // 如果强制使用HTTPS
        if ($forceHttps && strpos($cdnPath, 'http://') === 0) {
            $cdnPath = str_replace('http://', 'https://', $cdnPath);
        }
        
        return $cdnPath;
    }
}

if (!function_exists('cdn_image')) {
    /**
     * 生成带CDN的图片标签
     * 
     * @param string $path 图片路径
     * @param array $attributes HTML属性
     * @param bool $lazyLoad 是否启用懒加载
     * @return string img标签HTML
     * 
     * 示例:
     * cdn_image('uploads/image.jpg', ['alt' => '图片', 'class' => 'img-fluid'])
     */
    function cdn_image(string $path, array $attributes = [], bool $lazyLoad = true): string
    {
        $src = cdn_asset($path);
        
        // 默认属性
        $defaultAttrs = [
            'src' => $src,
        ];
        
        // 启用懒加载
        if ($lazyLoad) {
            $defaultAttrs['loading'] = 'lazy';
            $defaultAttrs['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' lazyload' 
                : 'lazyload';
        }
        
        // 合并属性
        $attrs = array_merge($defaultAttrs, $attributes);
        
        // 构建HTML属性字符串
        $attrString = '';
        foreach ($attrs as $key => $value) {
            $attrString .= ' ' . $key . '="' . esc($value) . '"';
        }
        
        return '<img' . $attrString . '>';
    }
}

if (!function_exists('cdn_css')) {
    /**
     * 生成带CDN的CSS链接标签
     * 
     * @param string|array $paths CSS文件路径（可以是数组）
     * @return string link标签HTML
     * 
     * 示例:
     * cdn_css('css/app.css')
     * cdn_css(['css/app.css', 'css/style.css'])
     */
    function cdn_css($paths): string
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        
        $html = '';
        foreach ($paths as $path) {
            $href = cdn_asset($path);
            $html .= '<link rel="stylesheet" href="' . esc($href) . '">' . "\n";
        }
        
        return $html;
    }
}

if (!function_exists('cdn_js')) {
    /**
     * 生成带CDN的JS脚本标签
     * 
     * @param string|array $paths JS文件路径（可以是数组）
     * @param bool $defer 是否使用defer属性
     * @return string script标签HTML
     * 
     * 示例:
     * cdn_js('js/app.js')
     * cdn_js(['js/jquery.min.js', 'js/app.js'], true)
     */
    function cdn_js($paths, bool $defer = false): string
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        
        $html = '';
        foreach ($paths as $path) {
            $src = cdn_asset($path);
            $deferAttr = $defer ? ' defer' : '';
            $html .= '<script src="' . esc($src) . '"' . $deferAttr . '></script>' . "\n";
        }
        
        return $html;
    }
}

if (!function_exists('get_cdn_config')) {
    /**
     * 获取CDN配置信息
     * 
     * @return array CDN配置
     */
    function get_cdn_config(): array
    {
        return [
            'enabled' => !empty(env('app.cdnUrl', '')),
            'url' => env('app.cdnUrl', ''),
            'version' => env('app.assetVersion', date('YmdHis')), // 资源版本号，用于缓存清除
        ];
    }
}
