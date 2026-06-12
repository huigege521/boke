<?php

namespace App\Controllers\Api;

use App\Models\SettingModel;

/**
 * 设置API控制器
 * 提供系统设置的API接口
 */
class SettingController extends BaseApiController
{
    /**
     * 获取所有设置
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function index()
    {
        $settingModel = new SettingModel();
        $settings = $settingModel->findAll();

        return $this->success($settings, '获取成功');
    }

    /**
     * 获取单个设置
     *
     * @param int $id 设置ID
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function show($id)
    {
        $settingModel = new SettingModel();
        $setting = $settingModel->find($id);

        if (!$setting) {
            return $this->error('设置不存在', 404);
        }

        return $this->success($setting, '获取成功');
    }

    /**
     * 更新设置
     *
     * @param int $id 设置ID
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function update($id)
    {
        $settingModel = new SettingModel();
        $setting = $settingModel->find($id);

        if (!$setting) {
            return $this->error('设置不存在', 404);
        }

        $settingValue = $this->request->getPost('setting_value');

        if ($settingModel->update($id, ['setting_value' => $settingValue])) {
            return $this->success($settingModel->find($id), '更新成功');
        }

        return $this->error('更新失败');
    }
}
