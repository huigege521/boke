<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MediaModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 媒体控制器
 * 管理媒体文件的上传、查看、编辑和删除
 */
class MediaController extends BaseController
{
    protected $mediaModel;

    public function __construct()
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            header('Location: /admin/login');
            exit();
        }

        $this->mediaModel = new MediaModel();
    }

    /**
     * 媒体库首页
     *
     * @return string
     */
    public function index()
    {
        $type = $this->getGet('type');
        $search = $this->getGet('search');
        $folderId = $this->getGet('folder');

        // 分页设置
        $perPage = 24;
        $page = (int) ($this->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;

        // 获取媒体文件
        $media = $this->mediaModel->getAllMedia($perPage, $offset, $type, $search, $folderId);
        $total = $this->mediaModel->getMediaCount($type, $search, $folderId);

        // 获取统计信息
        $stats = $this->mediaModel->getStatsByType();

        $data = [
            'title' => '媒体库 - 后台',
            'media' => $media,
            'stats' => $stats,
            'type' => $type,
            'search' => $search,
            'folderId' => $folderId,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
                'total_items' => $total,
                'per_page' => $perPage
            ]
        ];

        return view('admin/media/index', $data);
    }

    /**
     * 上传文件
     *
     * @return ResponseInterface
     */
    public function upload()
    {
        // 检查请求方法
        if ($this->request->getMethod() !== 'POST') {
            return $this->errorResponse('非法请求', 405);
        }

        // 检查是否有文件上传
        $files = $this->request->getFiles();

        if (empty($files['file'])) {
            return $this->errorResponse('没有文件被上传');
        }

        $file = $files['file'];

        // 检查上传错误
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->errorResponse('文件上传失败: ' . $this->getUploadErrorMessage($file->getError()));
        }

        // 获取文件夹ID
        $folderId = $this->getPost('folder_id');

        // 获取当前用户ID
        $userId = session()->get('user_id');

        // 准备文件信息
        $fileInfo = [
            'name' => $file->getClientName(),
            'type' => $file->getClientMimeType(),
            'tmp_name' => $file->getTempName(),
            'size' => $file->getSize()
        ];

        // 保存文件
        $result = $this->mediaModel->saveUpload($fileInfo, $userId, $folderId);

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']);
        }

        return $this->successResponse('文件上传成功', $result);
    }

    /**
     * 批量上传
     *
     * @return ResponseInterface
     */
    public function uploadMultiple()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->errorResponse('非法请求', 405);
        }

        $files = $this->request->getFiles();

        if (empty($files['files'])) {
            return $this->errorResponse('没有文件被上传');
        }

        $folderId = $this->getPost('folder_id');
        $userId = session()->get('user_id');

        $uploaded = [];
        $errors = [];

        foreach ($files['files'] as $file) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                $errors[] = [
                    'name' => $file->getClientName(),
                    'error' => $this->getUploadErrorMessage($file->getError())
                ];
                continue;
            }

            $fileInfo = [
                'name' => $file->getClientName(),
                'type' => $file->getClientMimeType(),
                'tmp_name' => $file->getTempName(),
                'size' => $file->getSize()
            ];

            $result = $this->mediaModel->saveUpload($fileInfo, $userId, $folderId);

            if (isset($result['error'])) {
                $errors[] = [
                    'name' => $file->getClientName(),
                    'error' => $result['error']
                ];
            } else {
                $uploaded[] = $result;
            }
        }

        // 确定是否成功
        $success = !empty($uploaded) || empty($files['files']);

        // 生成详细的消息
        if (empty($files['files'])) {
            $message = '没有文件被上传';
        } elseif (empty($uploaded)) {
            $message = '所有文件上传失败';
        } elseif (count($errors) > 0) {
            $message = '部分文件上传成功（成功：' . count($uploaded) . '个，失败：' . count($errors) . '个）';
        } else {
            $message = '所有文件上传成功（共 ' . count($uploaded) . ' 个）';
        }

        return $this->jsonResponse([
            'success' => $success,
            'message' => $message,
            'uploaded' => $uploaded,
            'errors' => $errors,
            'total' => count($files['files']),
            'success_count' => count($uploaded),
            'error_count' => count($errors)
        ]);
    }

    /**
     * 获取媒体文件详情
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function detail($id)
    {
        $media = $this->mediaModel->getMediaById($id);

        if (!$media) {
            return $this->errorResponse('文件不存在', 404);
        }

        return $this->successResponse('获取成功', $media);
    }

    /**
     * 编辑媒体文件信息
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function edit($id)
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->errorResponse('非法请求', 405);
        }

        $media = $this->mediaModel->getMediaById($id);

        if (!$media) {
            return $this->errorResponse('文件不存在', 404);
        }

        // 验证输入
        $rules = [
            'title' => 'max_length[255]',
            'alt_text' => 'max_length[255]',
            'description' => 'max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->errorResponse('验证失败', 400, $this->getValidationErrors());
        }

        $data = [
            'title' => $this->getPost('title'),
            'alt_text' => $this->getPost('alt_text'),
            'description' => $this->getPost('description')
        ];

        // 过滤空值
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($data)) {
            return $this->errorResponse('没有数据需要更新');
        }

        if ($this->mediaModel->updateMedia($id, $data)) {
            return $this->successResponse('更新成功', $this->mediaModel->getMediaById($id));
        }

        return $this->errorResponse('更新失败');
    }

    /**
     * 删除媒体文件
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function delete($id)
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->errorResponse('非法请求', 405);
        }

        $media = $this->mediaModel->getMediaById($id);

        if (!$media) {
            return $this->errorResponse('文件不存在', 404);
        }

        if ($this->mediaModel->deleteMedia($id)) {
            return $this->successResponse('删除成功');
        }

        return $this->errorResponse('删除失败');
    }

    /**
     * 批量删除
     *
     * @return ResponseInterface
     */
    public function batchDelete()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->errorResponse('非法请求', 405);
        }

        $ids = $this->getPost('ids');

        if (empty($ids) || !is_array($ids)) {
            return $this->errorResponse('请选择要删除的文件');
        }

        $deleted = 0;
        $errors = [];
        $failedIds = [];

        foreach ($ids as $id) {
            // 验证ID是否为数字
            if (!is_numeric($id)) {
                $errors[] = "ID {$id}: 无效的文件ID";
                $failedIds[] = $id;
                continue;
            }

            $media = $this->mediaModel->getMediaById((int)$id);
            if (!$media) {
                $errors[] = "ID {$id}: 文件不存在";
                $failedIds[] = $id;
                continue;
            }

            if ($this->mediaModel->deleteMedia((int)$id)) {
                $deleted++;
            } else {
                $errors[] = "ID {$id}: 删除失败";
                $failedIds[] = $id;
            }
        }

        // 生成消息
        if ($deleted > 0 && count($errors) === 0) {
            $message = "成功删除 {$deleted} 个文件";
        } elseif ($deleted > 0 && count($errors) > 0) {
            $message = "成功删除 {$deleted} 个文件，" . count($errors) . " 个失败";
        } else {
            $message = "删除失败：" . implode(', ', array_slice($errors, 0, 3));
        }

        return $this->jsonResponse([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted_count' => $deleted,
            'failed_count' => count($errors),
            'errors' => $errors,
            'failed_ids' => $failedIds,
            'csrf_token' => csrf_token(),
            'csrf_name' => csrf_header()
        ]);
    }

    /**
     * 获取媒体库数据（用于选择器）
     *
     * @return ResponseInterface
     */
    public function library()
    {
        $type = $this->getGet('type');
        $search = $this->getGet('search');
        $page = (int) ($this->getGet('page') ?? 1);
        $perPage = (int) ($this->getGet('per_page') ?? 20);
        $offset = ($page - 1) * $perPage;

        $media = $this->mediaModel->getAllMedia($perPage, $offset, $type, $search);
        $total = $this->mediaModel->getMediaCount($type, $search);

        return $this->jsonResponse([
            'success' => true,
            'data' => $media,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
                'total_items' => $total,
                'per_page' => $perPage
            ]
        ]);
    }

    /**
     * 加载媒体库数据（用于模态框选择器）
     *
     * @return ResponseInterface
     */
    public function load()
    {
        $type = $this->getGet('type');
        $search = $this->getGet('search');

        // 获取媒体文件，不限制数量，因为是在模态框中使用
        $media = $this->mediaModel->getAllMedia(100, 0, $type, $search);

        // 格式化媒体数据
        $formattedMedia = array_map(function ($item) {
            // 确定文件类型
            $fileExtension = strtolower(pathinfo($item['filename'], PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $documentExtensions = ['pdf', 'doc', 'docx', 'txt', 'csv', 'xls', 'xlsx'];
            $videoExtensions = ['mp4', 'avi', 'mov', 'wmv'];

            $fileType = 'other';
            if (in_array($fileExtension, $imageExtensions)) {
                $fileType = 'image';
            } elseif (in_array($fileExtension, $documentExtensions)) {
                $fileType = 'document';
            } elseif (in_array($fileExtension, $videoExtensions)) {
                $fileType = 'video';
            }

            return [
                'id' => $item['id'],
                'filename' => $item['filename'],
                'original_name' => $item['original_name'],
                'file_url' => $item['file_url'], // 直接使用数据库中存储的完整路径
                'file_size' => $item['file_size'],
                'is_image' => $item['is_image'], // 使用数据库中存储的is_image值
                'file_type' => $fileType,
                'alt_text' => $item['alt_text'],
                'created_at' => $item['created_at']
            ];
        }, $media);

        return $this->jsonResponse([
            'success' => true,
            'media' => $formattedMedia
        ]);
    }

    /**
     * 获取上传错误信息
     *
     * @param int $errorCode
     * @return string
     */
    protected function getUploadErrorMessage($errorCode)
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => '文件大小超过服务器限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
            UPLOAD_ERR_PARTIAL => '文件部分上传失败',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '缺少临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => '上传被扩展阻止'
        ];

        return $messages[$errorCode] ?? '未知错误';
    }
}
