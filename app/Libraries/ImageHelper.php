<?php

namespace App\Libraries;

/**
 * 图片处理助手类
 * 提供图片压缩、缩放、格式转换等功能
 */
class ImageHelper
{
    /**
     * 默认配置
     */
    private static array $defaultConfig = [
        'quality' => 80,           // 压缩质量 (1-100)
        'max_width' => 1920,       // 最大宽度
        'max_height' => 1080,      // 最大高度
        'maintain_ratio' => true,  // 保持宽高比
        'create_thumb' => false,   // 创建缩略图
        'thumb_width' => 150,      // 缩略图宽度
        'thumb_height' => 150,     // 缩略图高度
    ];

    /**
     * 支持的图片格式
     */
    private static array $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * 压缩图片
     *
     * @param string $sourcePath 源文件路径
     * @param array $config 配置参数
     * @return array 处理结果 ['success' => bool, 'path' => string, 'size' => int]
     */
    public static function compress(string $sourcePath, array $config = []): array
    {
        // 检查文件是否存在
        if (!file_exists($sourcePath)) {
            return [
                'success' => false,
                'message' => '文件不存在',
            ];
        }

        // 检查是否为支持的图片格式
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (!in_array($extension, self::$supportedFormats)) {
            return [
                'success' => false,
                'message' => '不支持的图片格式: ' . $extension,
            ];
        }

        // 合并配置
        $config = array_merge(self::$defaultConfig, $config);

        try {
            // 获取图片信息
            $imageInfo = getimagesize($sourcePath);
            if ($imageInfo === false) {
                return [
                    'success' => false,
                    'message' => '无效的图片文件',
                ];
            }

            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            // 计算新尺寸
            [$newWidth, $newHeight] = self::calculateDimensions(
                $originalWidth,
                $originalHeight,
                $config['max_width'],
                $config['max_height'],
                $config['maintain_ratio']
            );

            // 如果不需要压缩，直接返回
            if ($newWidth >= $originalWidth && $newHeight >= $originalHeight && $extension !== 'png') {
                return [
                    'success' => true,
                    'path' => $sourcePath,
                    'size' => filesize($sourcePath),
                    'width' => $originalWidth,
                    'height' => $originalHeight,
                    'original_size' => filesize($sourcePath), // 添加original_size字段
                    'compression_ratio' => 0, // 添加compression_ratio字段
                    'message' => '图片无需压缩',
                ];
            }

            // 创建图片资源
            $sourceImage = self::createImageFromPath($sourcePath, $mimeType);
            if (!$sourceImage) {
                return [
                    'success' => false,
                    'message' => '无法创建图片资源',
                ];
            }

            // 创建新的图片资源
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // 处理PNG和GIF的透明度
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // 重新采样图片
            imagecopyresampled(
                $newImage,
                $sourceImage,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            // 保存压缩后的图片
            $result = self::saveImage($newImage, $sourcePath, $extension, $config['quality']);

            // 释放资源
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            if ($result['success']) {
                return [
                    'success' => true,
                    'path' => $sourcePath,
                    'size' => $result['size'],
                    'width' => $newWidth,
                    'height' => $newHeight,
                    'original_size' => $result['original_size'],
                    'compression_ratio' => $result['compression_ratio'],
                    'message' => '图片压缩成功',
                ];
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', '图片压缩失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '图片压缩失败: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 创建缩略图
     *
     * @param string $sourcePath 源文件路径
     * @param string $thumbPath 缩略图路径（可选，默认为源文件同目录）
     * @param array $config 配置参数
     * @return array 处理结果
     */
    public static function createThumbnail(string $sourcePath, string $thumbPath = '', array $config = []): array
    {
        if (!file_exists($sourcePath)) {
            return [
                'success' => false,
                'message' => '文件不存在',
            ];
        }

        // 如果未指定缩略图路径，自动生成
        if (empty($thumbPath)) {
            $pathInfo = pathinfo($sourcePath);
            $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        }

        // 复制文件到缩略图路径
        if (!copy($sourcePath, $thumbPath)) {
            return [
                'success' => false,
                'message' => '无法复制文件',
            ];
        }

        // 设置缩略图配置
        $config = array_merge([
            'max_width' => $config['thumb_width'] ?? 150,
            'max_height' => $config['thumb_height'] ?? 150,
        ], $config);

        // 压缩为缩略图尺寸
        return self::compress($thumbPath, $config);
    }

    /**
     * 批量压缩图片
     *
     * @param array $filePaths 文件路径数组
     * @param array $config 配置参数
     * @return array 处理结果数组
     */
    public static function batchCompress(array $filePaths, array $config = []): array
    {
        $results = [];
        foreach ($filePaths as $filePath) {
            $results[$filePath] = self::compress($filePath, $config);
        }
        return $results;
    }

    /**
     * 计算缩放后的尺寸
     *
     * @param int $originalWidth 原始宽度
     * @param int $originalHeight 原始高度
     * @param int $maxWidth 最大宽度
     * @param int $maxHeight 最大高度
     * @param bool $maintainRatio 是否保持宽高比
     * @return array [width, height]
     */
    private static function calculateDimensions(
        int $originalWidth,
        int $originalHeight,
        int $maxWidth,
        int $maxHeight,
        bool $maintainRatio = true
    ): array {
        if (!$maintainRatio) {
            return [$maxWidth, $maxHeight];
        }

        $widthRatio = $maxWidth / $originalWidth;
        $heightRatio = $maxHeight / $originalHeight;
        $ratio = min($widthRatio, $heightRatio, 1); // 不超过原始尺寸

        $newWidth = (int) round($originalWidth * $ratio);
        $newHeight = (int) round($originalHeight * $ratio);

        return [$newWidth, $newHeight];
    }

    /**
     * 根据路径创建图片资源
     *
     * @param string $path 文件路径
     * @param string $mimeType MIME类型
     * @return resource|false GD图片资源
     */
    private static function createImageFromPath(string $path, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                return false;
        }
    }

    /**
     * 保存图片
     *
     * @param resource $image GD图片资源
     * @param string $path 保存路径
     * @param string $extension 文件扩展名
     * @param int $quality 压缩质量
     * @return array 保存结果
     */
    private static function saveImage($image, string $path, string $extension, int $quality): array
    {
        $originalSize = filesize($path);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $success = imagejpeg($image, $path, $quality);
                break;
            case 'png':
                // PNG使用压缩级别 (0-9)，将quality转换为压缩级别
                $compressionLevel = max(0, min(9, (int) ((100 - $quality) / 10)));
                $success = imagepng($image, $path, $compressionLevel);
                break;
            case 'gif':
                $success = imagegif($image, $path);
                break;
            case 'webp':
                $success = imagewebp($image, $path, $quality);
                break;
            default:
                return [
                    'success' => false,
                    'message' => '不支持的图片格式',
                ];
        }

        if (!$success) {
            return [
                'success' => false,
                'message' => '保存图片失败',
            ];
        }

        $newSize = filesize($path);
        $compressionRatio = $originalSize > 0 ? round((1 - $newSize / $originalSize) * 100, 2) : 0;

        return [
            'success' => true,
            'size' => $newSize,
            'original_size' => $originalSize,
            'compression_ratio' => $compressionRatio,
        ];
    }

    /**
     * 获取图片信息
     *
     * @param string $path 文件路径
     * @return array|false 图片信息
     */
    public static function getImageInfo(string $path)
    {
        if (!file_exists($path)) {
            return false;
        }

        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            return false;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'],
            'size' => filesize($path),
            'extension' => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
        ];
    }

    /**
     * 添加文字水印
     *
     * @param string $sourcePath 源图片路径
     * @param string $text 水印文字
     * @param array $config 配置参数
     * @return array 处理结果
     */
    public static function addTextWatermark(string $sourcePath, string $text, array $config = []): array
    {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'message' => '文件不存在'];
        }

        // 默认配置
        $defaultConfig = [
            'font_size' => 20,              // 字体大小
            'font_color' => [255, 255, 255, 70], // 字体颜色 RGBA (白色，70%不透明度)
            'position' => 'bottom-right',   // 位置: top-left, top-right, bottom-left, bottom-right, center
            'margin' => 20,                 // 边距
            'font_file' => null,            // 字体文件路径（可选）
        ];

        $config = array_merge($defaultConfig, $config);

        try {
            // 获取图片信息
            $imageInfo = self::getImageInfo($sourcePath);
            if (!$imageInfo) {
                return ['success' => false, 'message' => '无效的图片文件'];
            }

            // 创建图片资源
            $image = self::createImageFromPath($sourcePath, $imageInfo['mime']);
            if (!$image) {
                return ['success' => false, 'message' => '无法创建图片资源'];
            }

            // 计算文字位置
            [$x, $y] = self::calculateTextPosition(
                $imageInfo['width'],
                $imageInfo['height'],
                $text,
                $config['font_size'],
                $config['position'],
                $config['margin'],
                $config['font_file']
            );

            // 分配颜色
            $color = imagecolorallocatealpha(
                $image,
                $config['font_color'][0],
                $config['font_color'][1],
                $config['font_color'][2],
                $config['font_color'][3]
            );

            // 添加文字水印
            if ($config['font_file'] && file_exists($config['font_file'])) {
                // 使用TrueType字体
                imagettftext(
                    $image,
                    $config['font_size'],
                    0,
                    $x,
                    $y,
                    $color,
                    $config['font_file'],
                    $text
                );
            } else {
                // 使用内置字体
                imagestring($image, 5, $x, $y, $text, $color);
            }

            // 保存图片
            $result = self::saveImage($image, $sourcePath, $imageInfo['extension'], 80);
            imagedestroy($image);

            return $result;
        } catch (\Exception $e) {
            log_message('error', '添加文字水印失败: ' . $e->getMessage());
            return ['success' => false, 'message' => '添加水印失败: ' . $e->getMessage()];
        }
    }

    /**
     * 添加图片水印
     *
     * @param string $sourcePath 源图片路径
     * @param string $watermarkPath 水印图片路径
     * @param array $config 配置参数
     * @return array 处理结果
     */
    public static function addImageWatermark(string $sourcePath, string $watermarkPath, array $config = []): array
    {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'message' => '源文件不存在'];
        }

        if (!file_exists($watermarkPath)) {
            return ['success' => false, 'message' => '水印图片不存在'];
        }

        // 默认配置
        $defaultConfig = [
            'opacity' => 70,                // 不透明度 (0-100)
            'position' => 'bottom-right',   // 位置
            'margin' => 20,                 // 边距
            'scale' => 0.2,                 // 水印缩放比例（相对于原图宽度）
        ];

        $config = array_merge($defaultConfig, $config);

        try {
            // 获取源图片信息
            $sourceInfo = self::getImageInfo($sourcePath);
            if (!$sourceInfo) {
                return ['success' => false, 'message' => '无效的源图片'];
            }

            // 获取水印图片信息
            $watermarkInfo = self::getImageInfo($watermarkPath);
            if (!$watermarkInfo) {
                return ['success' => false, 'message' => '无效的水印图片'];
            }

            // 创建图片资源
            $sourceImage = self::createImageFromPath($sourcePath, $sourceInfo['mime']);
            $watermarkImage = self::createImageFromPath($watermarkPath, $watermarkInfo['mime']);

            if (!$sourceImage || !$watermarkImage) {
                return ['success' => false, 'message' => '无法创建图片资源'];
            }

            // 计算水印尺寸
            $watermarkWidth = (int) ($sourceInfo['width'] * $config['scale']);
            $watermarkHeight = (int) ($watermarkWidth * ($watermarkInfo['height'] / $watermarkInfo['width']));

            // 创建调整大小后的水印图片
            $resizedWatermark = imagecreatetruecolor($watermarkWidth, $watermarkHeight);
            
            // 处理透明度
            if ($watermarkInfo['mime'] === 'image/png' || $watermarkInfo['mime'] === 'image/gif') {
                imagealphablending($resizedWatermark, false);
                imagesavealpha($resizedWatermark, true);
            }

            imagecopyresampled(
                $resizedWatermark,
                $watermarkImage,
                0, 0, 0, 0,
                $watermarkWidth,
                $watermarkHeight,
                $watermarkInfo['width'],
                $watermarkInfo['height']
            );

            // 计算位置
            [$x, $y] = self::calculateImagePosition(
                $sourceInfo['width'],
                $sourceInfo['height'],
                $watermarkWidth,
                $watermarkHeight,
                $config['position'],
                $config['margin']
            );

            // 设置透明度
            $opacity = (int) (($config['opacity'] / 100) * 127);
            if ($opacity > 0) {
                self::applyOpacity($resizedWatermark, $opacity);
            }

            // 合并水印
            imagecopy(
                $sourceImage,
                $resizedWatermark,
                $x,
                $y,
                0,
                0,
                $watermarkWidth,
                $watermarkHeight
            );

            // 保存
            $result = self::saveImage($sourceImage, $sourcePath, $sourceInfo['extension'], 80);
            
            imagedestroy($sourceImage);
            imagedestroy($watermarkImage);
            imagedestroy($resizedWatermark);

            return $result;
        } catch (\Exception $e) {
            log_message('error', '添加图片水印失败: ' . $e->getMessage());
            return ['success' => false, 'message' => '添加水印失败: ' . $e->getMessage()];
        }
    }

    /**
     * 计算文字位置
     */
    private static function calculateTextPosition(
        int $imgWidth,
        int $imgHeight,
        string $text,
        int $fontSize,
        string $position,
        int $margin,
        ?string $fontFile = null
    ): array {
        // 估算文字尺寸
        if ($fontFile && file_exists($fontFile)) {
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
            $textWidth = $bbox[2] - $bbox[0];
            $textHeight = $bbox[1] - $bbox[7];
        } else {
            $textWidth = strlen($text) * $fontSize * 0.6;
            $textHeight = $fontSize;
        }

        switch ($position) {
            case 'top-left':
                return [$margin, $margin + $textHeight];
            case 'top-right':
                return [$imgWidth - $textWidth - $margin, $margin + $textHeight];
            case 'bottom-left':
                return [$margin, $imgHeight - $margin];
            case 'bottom-right':
                return [$imgWidth - $textWidth - $margin, $imgHeight - $margin];
            case 'center':
                return [
                    (int) (($imgWidth - $textWidth) / 2),
                    (int) (($imgHeight + $textHeight) / 2)
                ];
            default:
                return [$imgWidth - $textWidth - $margin, $imgHeight - $margin];
        }
    }

    /**
     * 计算图片水印位置
     */
    private static function calculateImagePosition(
        int $imgWidth,
        int $imgHeight,
        int $wmWidth,
        int $wmHeight,
        string $position,
        int $margin
    ): array {
        switch ($position) {
            case 'top-left':
                return [$margin, $margin];
            case 'top-right':
                return [$imgWidth - $wmWidth - $margin, $margin];
            case 'bottom-left':
                return [$margin, $imgHeight - $wmHeight - $margin];
            case 'bottom-right':
                return [$imgWidth - $wmWidth - $margin, $imgHeight - $wmHeight - $margin];
            case 'center':
                return [
                    (int) (($imgWidth - $wmWidth) / 2),
                    (int) (($imgHeight - $wmHeight) / 2)
                ];
            default:
                return [$imgWidth - $wmWidth - $margin, $imgHeight - $wmHeight - $margin];
        }
    }

    /**
     * 应用透明度
     */
    private static function applyOpacity($image, int $opacity): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $color = imagecolorat($image, $x, $y);
                $alpha = ($color >> 24) & 0xFF;
                $newAlpha = min(127, $alpha + $opacity);
                $color = ($color & 0x00FFFFFF) | ($newAlpha << 24);
                imagesetpixel($image, $x, $y, $color);
            }
        }
    }
}
