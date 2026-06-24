<?php

namespace App\Controllers\Api;

use App\Models\MediaModel;

/**
 * 媒体API控制器
 * 提供媒体文件的API接口
 */
class MediaController extends BaseApiController
{
    /**
     * 获取媒体文件列表
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function index()
    {
        $mediaModel = new MediaModel();

        $page = (int) ($this->request->getVar('page') ?? 1);
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        // 获取筛选参数
        $type = $this->request->getVar('type') ?? '';
        $search = $this->request->getVar('search') ?? '';

        $media = $mediaModel->getAllMedia($perPage, $offset, $type, $search);
        $total = $mediaModel->getMediaCount($type, $search);

        // 格式化媒体数据，匹配前端期望的字段名
        $formattedMedia = array_map(function ($item) {
            // 统计该图片被多少篇文章使用
            $postModel = new \App\Models\PostModel();
            $usedByPosts = $postModel->where('featured_image_id', $item['id'])->countAllResults();
            
            return [
                'id' => $item['id'],
                'filename' => $item['filename'],
                'url' => $item['file_url'],  // 前端期望的字段名
                'size_formatted' => $this->formatFileSize($item['file_size']),  // 格式化文件大小
                'type' => $item['file_type'],  // 前端期望的字段名
                'file_url' => $item['file_url'],
                'file_size' => $item['file_size'],
                'file_type' => $item['file_type'],
                'original_name' => $item['original_name'],
                'is_image' => $item['is_image'],
                'title' => $item['title'] ?? '',
                'alt_text' => $item['alt_text'] ?? '',
                'description' => $item['description'] ?? '',
                'created_at' => $item['created_at'],
                'used_by_posts' => $usedByPosts  // 新增: 被文章使用的次数
            ];
        }, $media);

        return $this->success([
            'list' => $formattedMedia,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
            'total_items' => $total,
            'per_page' => $perPage
        ], '获取成功');
    }

    /**
     * 格式化文件大小
     *
     * @param int $bytes 字节数
     * @return string 格式化后的大小
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 获取单个媒体文件详情
     *
     * @param int $id 媒体文件ID
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function show($id)
    {
        $mediaModel = new MediaModel();
        $media = $mediaModel->getMediaById($id);

        if (!$media) {
            return $this->error('文件不存在', 404);
        }

        // 格式化响应数据，添加前端期望的字段
        $formattedMedia = [
            'id' => $media['id'],
            'filename' => $media['filename'],
            'url' => $media['file_url'],
            'size_formatted' => $this->formatFileSize($media['file_size']),
            'type' => $media['file_type'],
            'width' => $media['width'],
            'height' => $media['height'],
            'alt_text' => $media['alt_text'] ?? '',
            'title' => $media['title'] ?? '',
            'description' => $media['description'] ?? '',
            'file_url' => $media['file_url'],
            'file_size' => $media['file_size'],
            'file_type' => $media['file_type'],
            'original_name' => $media['original_name'],
            'is_image' => $media['is_image'],
            'created_at' => $media['created_at'],
            'uploader_name' => $media['uploader_name'] ?? ''
        ];

        return $this->success($formattedMedia, '获取成功');
    }

    /**
     * 上传媒体文件
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function upload()
    {
        $files = $this->request->getFiles();

        if (empty($files['files'])) {
            return $this->error('没有文件被上传');
        }

        $mediaModel = new MediaModel();
        $userId = session()->get('user_id');

        $uploaded = [];
        $errors = [];

        foreach ($files['files'] as $file) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                $errors[] = [
                    'name' => $file->getClientName(),
                    'error' => '上传失败'
                ];
                continue;
            }

            $fileInfo = [
                'name' => $file->getClientName(),
                'type' => $file->getClientMimeType(),
                'tmp_name' => $file->getTempName(),
                'size' => $file->getSize()
            ];

            $result = $mediaModel->saveUpload($fileInfo, $userId);

            if (isset($result['error'])) {
                $errors[] = [
                    'name' => $file->getClientName(),
                    'error' => $result['error']
                ];
            } else {
                $uploaded[] = $result;
            }
        }

        return $this->success([
            'count' => count($uploaded),
            'uploaded' => $uploaded,
            'errors' => $errors
        ], '上传完成');
    }

    /**
     * 更新媒体文件信息
     *
     * @param int $id 媒体文件ID
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function update($id)
    {
        $mediaModel = new MediaModel();
        $media = $mediaModel->getMediaById($id);

        if (!$media) {
            return $this->error('文件不存在', 404);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'alt_text' => $this->request->getPost('alt_text'),
            'description' => $this->request->getPost('description')
        ];

        if ($mediaModel->update($id, $data)) {
            return $this->success(null, '更新成功');
        }

        return $this->error('更新失败');
    }

    /**
     * 删除媒体文件
     *
     * @param int $id 媒体文件ID
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function delete($id)
    {
        $mediaModel = new MediaModel();
        $media = $mediaModel->getMediaById($id);

        if (!$media) {
            return $this->error('文件不存在', 404);
        }

        // 检查是否有文章正在使用此图片
        $postModel = new \App\Models\PostModel();
        $usedByPosts = $postModel->where('featured_image_id', $id)->countAllResults();
        
        if ($usedByPosts > 0) {
            return $this->error("该图片正被 {$usedByPosts} 篇文章使用，无法删除。请先更换这些文章的封面图。", 403);
        }

        try {
            $result = $mediaModel->deleteMedia($id);
            
            if ($result) {
                return $this->success(null, '删除成功');
            } else {
                log_message('error', "删除媒体文件失败 ID: {$id}");
                return $this->error('删除失败，请稍后重试');
            }
        } catch (\Exception $e) {
            log_message('error', "删除媒体文件异常 ID: {$id}, Error: " . $e->getMessage());
            return $this->error('删除失败：' . $e->getMessage(), 500);
        }
    }

    /**
     * 批量删除媒体文件
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function batchDelete()
    {
        $input = $this->request->getJSON();
        $ids = $input->ids ?? [];

        if (empty($ids)) {
            return $this->error('请选择要删除的文件');
        }

        $mediaModel = new MediaModel();
        $postModel = new \App\Models\PostModel();
        
        $deletedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            // 检查是否有文章正在使用此图片
            $usedByPosts = $postModel->where('featured_image_id', $id)->countAllResults();
            
            if ($usedByPosts > 0) {
                $failedCount++;
                $errors[] = "ID {$id}: 被 {$usedByPosts} 篇文章使用";
                continue;
            }
            
            $media = $mediaModel->getMediaById($id);
            if ($media && $mediaModel->deleteMedia($id)) {
                $deletedCount++;
            } else {
                $failedCount++;
            }
        }

        if ($deletedCount > 0) {
            $message = "成功删除 {$deletedCount} 个文件";
            if ($failedCount > 0) {
                $message .= "，{$failedCount} 个文件删除失败";
                if (!empty($errors)) {
                    $message .= ': ' . implode('; ', array_slice($errors, 0, 3));
                }
            }
            return $this->success([
                'deleted' => $deletedCount,
                'failed' => $failedCount,
                'total' => count($ids),
                'errors' => $errors
            ], $message);
        }

        return $this->error('所有文件删除失败: ' . implode('; ', array_slice($errors, 0, 5)), 400);
    }
}
