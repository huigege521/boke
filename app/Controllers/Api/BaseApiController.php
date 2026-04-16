<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;

/**
 * API 基础控制器
 * 所有 API 控制器的父类，提供通用的 API 响应方法
 */
class BaseApiController extends Controller
{
    /**
     * 成功响应
     *
     * @param mixed $data 响应数据
     * @param string $message 响应消息
     * @param int $statusCode HTTP 状态码
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function success($data = null, string $message = '操作成功', int $statusCode = 200)
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data
        ])->setStatusCode($statusCode);
    }

    /**
     * 错误响应
     *
     * @param string $message 错误消息
     * @param int $statusCode HTTP 状态码
     * @param mixed $data 额外数据
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function error(string $message = '操作失败', int $statusCode = 400, $data = null)
    {
        return $this->response->setJSON([
            'success' => false,
            'message' => $message,
            'data' => $data
        ])->setStatusCode($statusCode);
    }

    /**
     * 未授权响应
     *
     * @param string $message 错误消息
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function unauthorized(string $message = '未授权访问')
    {
        return $this->error($message, 401);
    }

    /**
     * 禁止访问响应
     *
     * @param string $message 错误消息
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function forbidden(string $message = '禁止访问')
    {
        return $this->error($message, 403);
    }

    /**
     * 资源不存在响应
     *
     * @param string $message 错误消息
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function notFound(string $message = '资源不存在')
    {
        return $this->error($message, 404);
    }

    /**
     * 验证失败响应
     *
     * @param array $errors 验证错误信息
     * @param string $message 错误消息
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function validationError(array $errors, string $message = '验证失败')
    {
        return $this->error($message, 422, $errors);
    }
}
