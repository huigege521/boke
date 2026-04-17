<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\ImageHelper;

/**
 * 媒体模型
 * 管理上传的文件和图片
 */
class MediaModel extends Model
{
    protected $table = 'media';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $allowedFields = [
        'filename',
        'original_name',
        'file_path',
        'file_url',
        'file_type',
        'file_size',
        'mime_type',
        'extension',
        'width',
        'height',
        'alt_text',
        'title',
        'description',
        'user_id',
        'folder_id',
        'is_image',
        'thumbnails'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // 允许的图片类型
    protected $allowedImageTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml'
    ];

    // 允许的文档类型
    protected $allowedDocumentTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv'
    ];

    // 允许的视频类型
    protected $allowedVideoTypes = [
        'video/mp4',
        'video/webm',
        'video/ogg'
    ];

    // 允许的所有类型
    protected $allowedMimeTypes = [];

    /**
     * 图片压缩配置
     */
    protected $imageCompressConfig = [
        'quality' => 80,           // 压缩质量 (1-100)
        'max_width' => 1920,       // 最大宽度
        'max_height' => 1080,      // 最大高度
        'maintain_ratio' => true,  // 保持宽高比
        'create_thumb' => true,    // 创建缩略图
        'thumb_width' => 300,      // 缩略图宽度
        'thumb_height' => 300,     // 缩略图高度
    ];

    public function __construct()
    {
        parent::__construct();
        $this->allowedMimeTypes = array_merge(
            $this->allowedImageTypes,
            $this->allowedDocumentTypes,
            $this->allowedVideoTypes
        );
    }

    /**
     * 获取所有媒体文件
     *
     * @param int $limit
     * @param int $offset
     * @param string|null $type
     * @param string|null $search
     * @param int|null $folderId
     * @return array
     */
    public function getAllMedia($limit = 20, $offset = 0, $type = null, $search = null, $folderId = null)
    {
        $builder = $this->select('media.*, users.username as uploader_name')
            ->join('users', 'users.id = media.user_id', 'left')
            ->where('media.deleted_at IS NULL');

        // 按类型筛选
        if ($type) {
            switch ($type) {
                case 'image':
                    $builder->whereIn('media.mime_type', $this->allowedImageTypes);
                    break;
                case 'document':
                    $builder->whereIn('media.mime_type', $this->allowedDocumentTypes);
                    break;
                case 'video':
                    $builder->whereIn('media.mime_type', $this->allowedVideoTypes);
                    break;
            }
        }

        // 按文件夹筛选
        if ($folderId !== null) {
            $builder->where('media.folder_id', $folderId);
        }

        // 搜索
        if ($search) {
            $builder->groupStart()
                ->like('media.filename', $search)
                ->orLike('media.original_name', $search)
                ->orLike('media.title', $search)
                ->orLike('media.description', $search)
                ->groupEnd();
        }

        return $builder->orderBy('media.created_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * 获取媒体文件总数
     *
     * @param string|null $type
     * @param string|null $search
     * @param int|null $folderId
     * @return int
     */
    public function getMediaCount($type = null, $search = null, $folderId = null)
    {
        $builder = $this->where('deleted_at IS NULL');

        if ($type) {
            switch ($type) {
                case 'image':
                    $builder->whereIn('mime_type', $this->allowedImageTypes);
                    break;
                case 'document':
                    $builder->whereIn('mime_type', $this->allowedDocumentTypes);
                    break;
                case 'video':
                    $builder->whereIn('mime_type', $this->allowedVideoTypes);
                    break;
            }
        }

        if ($folderId !== null) {
            $builder->where('folder_id', $folderId);
        }

        if ($search) {
            $builder->groupStart()
                ->like('filename', $search)
                ->orLike('original_name', $search)
                ->orLike('title', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * 根据 ID 获取媒体文件
     *
     * @param int $id
     * @return array|null
     */
    public function getMediaById($id)
    {
        return $this->select('media.*, users.username as uploader_name')
            ->join('users', 'users.id = media.user_id', 'left')
            ->where('media.id', $id)
            ->where('media.deleted_at IS NULL')
            ->first();
    }

    /**
     * 保存上传的文件
     *
     * @param array $file 上传的文件信息
     * @param int $userId 上传用户ID
     * @param int|null $folderId 文件夹ID
     * @return array|bool
     */
    public function saveUpload($file, $userId, $folderId = null)
    {
        // 验证文件类型
        if (!$this->isAllowedType($file['type'])) {
            return ['error' => '不支持的文件类型: ' . $file['type']];
        }

        // 验证文件大小（最大 10MB）
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['error' => '文件大小超过限制（最大 10MB）'];
        }

        // 生成安全的文件名
        $originalName = $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $filename = $this->generateFilename($extension);

        // 确定存储路径
        $uploadPath = $this->getUploadPath($folderId);
        $filePath = $uploadPath . $filename;

        // 确保目录存在
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // 移动文件
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['error' => '文件上传失败'];
        }

        // 获取图片尺寸
        $width = null;
        $height = null;
        $isImage = $this->isImage($file['type']);
        $thumbnails = null;

        if ($isImage) {
            // 压缩图片
            $compressResult = ImageHelper::compress($filePath, $this->imageCompressConfig);
            
            if ($compressResult['success']) {
                log_message('info', '图片压缩成功: ' . $compressResult['message'] . 
                    ', 原始大小: ' . $this->formatBytes($compressResult['original_size'] ?? $file['size']) . 
                    ', 压缩后: ' . $this->formatBytes($compressResult['size']) . 
                    ', 压缩率: ' . ($compressResult['compression_ratio'] ?? 0) . '%');
                
                $width = $compressResult['width'] ?? null;
                $height = $compressResult['height'] ?? null;
            } else {
                log_message('warning', '图片压缩失败: ' . $compressResult['message']);
                // 即使压缩失败，也获取原始图片尺寸
                $imageInfo = getimagesize($filePath);
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                }
            }

            // 生成缩略图
            if ($this->imageCompressConfig['create_thumb']) {
                $thumbResult = ImageHelper::createThumbnail(
                    $filePath,
                    '', // 自动生成缩略图路径
                    $this->imageCompressConfig
                );
                
                if ($thumbResult['success']) {
                    $thumbnails = [
                        [
                            'size' => 'thumbnail',
                            'path' => $thumbResult['path'],
                            'url' => str_replace('/uploads/', '/uploads/', $thumbResult['path']),
                            'width' => $thumbResult['width'] ?? $this->imageCompressConfig['thumb_width'],
                            'height' => $thumbResult['height'] ?? $this->imageCompressConfig['thumb_height'],
                        ]
                    ];
                }
            }
        }

        // 生成正确的 file_url
        $baseUrl = '/uploads/';
        if ($folderId) {
            $fileUrl = $baseUrl . $folderId . '/' . $filename;
        } else {
            // 按日期组织的路径
            //$datePath = date('Y') . '/' . date('m') . '/';
            $datePath = date('Ymd') . '/';
            $fileUrl = $baseUrl . $datePath . $filename;
        }

        // 保存到数据库
        $data = [
            'filename' => $filename,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_type' => $this->getFileType($file['type']),
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'extension' => $extension,
            'width' => $width,
            'height' => $height,
            'user_id' => $userId,
            'folder_id' => $folderId,
            'is_image' => $isImage ? 1 : 0,
            'thumbnails' => isset($thumbnails) ? json_encode($thumbnails) : null
        ];

        $id = $this->insert($data);

        if (!$id) {
            // 删除已上传的文件
            unlink($filePath);
            return ['error' => '保存文件信息失败'];
        }

        return $this->getMediaById($id);
    }

    /**
     * 更新媒体文件信息
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateMedia($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * 删除媒体文件
     *
     * @param int $id
     * @return bool
     */
    public function deleteMedia($id)
    {
        $media = $this->getMediaById($id);
        if (!$media) {
            return false;
        }

        // 删除物理文件
        if (file_exists($media['file_path'])) {
            unlink($media['file_path']);
        }

        // 删除缩略图
        if ($media['thumbnails']) {
            $thumbnails = json_decode($media['thumbnails'], true);
            foreach ($thumbnails as $thumbnail) {
                if (file_exists($thumbnail['path'])) {
                    unlink($thumbnail['path']);
                }
            }
        }

        // 软删除数据库记录
        return $this->delete($id);
    }

    /**
     * 批量删除媒体文件
     *
     * @param array $ids
     * @return bool
     */
    public function batchDelete($ids)
    {
        if (empty($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            $this->deleteMedia($id);
        }

        return true;
    }

    /**
     * 获取允许的 MIME 类型
     *
     * @return array
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * 检查是否是允许的类型
     *
     * @param string $mimeType
     * @return bool
     */
    public function isAllowedType($mimeType)
    {
        return in_array($mimeType, $this->allowedMimeTypes);
    }

    /**
     * 检查是否是图片
     *
     * @param string $mimeType
     * @return bool
     */
    public function isImage($mimeType)
    {
        return in_array($mimeType, $this->allowedImageTypes);
    }

    /**
     * 获取文件类型
     *
     * @param string $mimeType
     * @return string
     */
    protected function getFileType($mimeType)
    {
        if (in_array($mimeType, $this->allowedImageTypes)) {
            return 'image';
        } elseif (in_array($mimeType, $this->allowedDocumentTypes)) {
            return 'document';
        } elseif (in_array($mimeType, $this->allowedVideoTypes)) {
            return 'video';
        }
        return 'other';
    }

    /**
     * 生成安全的文件名
     *
     * @param string $extension
     * @return string
     */
    protected function generateFilename($extension)
    {
        return date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    /**
     * 获取上传路径
     *
     * @param int|null $folderId
     * @return string
     */
    protected function getUploadPath($folderId = null)
    {
        $basePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR;

        if ($folderId) {
            return $basePath . $folderId . DIRECTORY_SEPARATOR;
        }

        // 按日期组织文件
        $datePath = date('Ymd') . DIRECTORY_SEPARATOR;
        return $basePath . $datePath;
    }

    /**
     * 生成缩略图
     *
     * @param string $sourcePath
     * @param string $filename
     * @param string $uploadPath
     * @return array
     */
    protected function generateThumbnails($sourcePath, $filename, $uploadPath)
    {
        $thumbnails = [];
        $sizes = [
            'small' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 300, 'height' => 300],
            'large' => ['width' => 800, 'height' => 600]
        ];

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        foreach ($sizes as $name => $dimensions) {
            $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '_' . $name . '.' . $extension;
            $thumbPath = $uploadPath . 'thumbs' . DIRECTORY_SEPARATOR . $thumbFilename;

            // 确保缩略图目录存在
            if (!is_dir($uploadPath . 'thumbs')) {
                mkdir($uploadPath . 'thumbs', 0755, true);
            }

            // 创建缩略图
            if ($this->createThumbnail($sourcePath, $thumbPath, $dimensions['width'], $dimensions['height'], $extension)) {
                $thumbnails[$name] = [
                    'path' => $thumbPath,
                    'url' => str_replace(FCPATH, '/', $thumbPath),
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height']
                ];
            }
        }

        return $thumbnails;
    }

    /**
     * 创建缩略图
     *
     * @param string $sourcePath
     * @param string $thumbPath
     * @param int $maxWidth
     * @param int $maxHeight
     * @param string $extension
     * @return bool
     */
    protected function createThumbnail($sourcePath, $thumbPath, $maxWidth, $maxHeight, $extension)
    {
        // 获取原图尺寸
        list($width, $height) = getimagesize($sourcePath);

        // 计算缩略图尺寸（保持比例）
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        // 创建画布
        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        // 根据图片类型创建源图像
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $source = imagecreatefrompng($sourcePath);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                break;
            case 'gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            case 'webp':
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        // 调整大小
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // 保存缩略图
        $result = false;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($thumb, $thumbPath, 85);
                break;
            case 'png':
                $result = imagepng($thumb, $thumbPath, 8);
                break;
            case 'gif':
                $result = imagegif($thumb, $thumbPath);
                break;
            case 'webp':
                $result = imagewebp($thumb, $thumbPath, 85);
                break;
        }

        // 释放内存
        imagedestroy($source);
        imagedestroy($thumb);

        return $result;
    }

    /**
     * 获取图片的缩略图 URL
     *
     * @param array $media
     * @param string $size
     * @return string
     */
    public function getThumbnailUrl($media, $size = 'medium')
    {
        if (!$media['is_image'] || !$media['thumbnails']) {
            return $media['file_url'];
        }

        $thumbnails = json_decode($media['thumbnails'], true);

        if (isset($thumbnails[$size])) {
            return $thumbnails[$size]['url'];
        }

        return $media['file_url'];
    }

    /**
     * 按文件夹获取媒体统计
     *
     * @return array
     */
    public function getStatsByFolder()
    {
        return $this->select('folder_id, COUNT(*) as count, SUM(file_size) as total_size')
            ->where('deleted_at IS NULL')
            ->groupBy('folder_id')
            ->findAll();
    }

    /**
     * 按类型获取媒体统计
     *
     * @return array
     */
    public function getStatsByType()
    {
        return $this->select('file_type, COUNT(*) as count, SUM(file_size) as total_size')
            ->where('deleted_at IS NULL')
            ->groupBy('file_type')
            ->findAll();
    }

    /**
     * 格式化字节大小
     *
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的大小
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
