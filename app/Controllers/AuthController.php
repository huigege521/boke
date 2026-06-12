<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Libraries\AuthService;

/**
 * 认证控制器
 * 负责用户登录、注册、登出等认证功能
 */
class AuthController extends BaseController
{
    /**
     * 登录页面
     *
     * @return string 视图字符串
     */
    public function login()
    {
        // 如果已登录，重定向到首页
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $data = [
            'title' => '登录 - 博客系统',
        ];

        return view('frontend/login', $data);
    }

    /**
     * 处理登录
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function doLogin()
    {
        // 验证输入
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->getValidationErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // 使用AuthService进行登录
        $authService = new AuthService();
        $result = $authService->login($email, $password);

        if ($result['success']) {
            // 设置会话
            session()->set([
                'logged_in' => true,
                'user_id' => $result['user']['id'],
                'username' => $result['user']['username'],
                'email' => $result['user']['email'],
                'role' => $result['user']['role'],
                'name' => $result['user']['name'],
            ]);

            // 重定向到之前访问的页面或首页
            $redirectUrl = session()->get('redirect_url') ?? '/';
            session()->remove('redirect_url');

            return redirect()->to($redirectUrl)->with('success', '登录成功');
        } else {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * 注册页面
     *
     * @return string 视图字符串
     */
    public function register()
    {
        // 如果已登录，重定向到首页
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $data = [
            'title' => '注册 - 博客系统',
        ];

        return view('frontend/register', $data);
    }

    /**
     * 处理注册
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function doRegister()
    {
        // 验证输入
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->getValidationErrors());
        }

        // 准备注册数据
        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'name' => $this->request->getPost('name') ?: $this->request->getPost('username'),
        ];

        // 使用AuthService进行注册
        $authService = new AuthService();
        $result = $authService->register($data);

        if ($result['success']) {
            return redirect()->to('/home/login')->with('success', '注册成功，请登录');
        } else {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * 忘记密码页面
     *
     * @return string 视图字符串
     */
    public function forgotPassword()
    {
        // 如果已登录，重定向到首页
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        // 如果是 POST 请求，处理密码重置请求
        if (strtolower($this->request->getMethod()) === 'post') {
            $email = $this->request->getPost('email');

            if (empty($email)) {
                return redirect()->back()->withInput()->with('error', '请输入邮箱地址');
            }

            // 检查邮箱是否存在
            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            if (!$user) {
                return redirect()->back()->withInput()->with('error', '该邮箱未注册');
            }

            // 生成重置令牌
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1小时过期

            // 保存令牌到用户表
            $userModel->update($user['id'], [
                'reset_token' => $token,
                'reset_token_expires' => $expiresAt
            ]);

            // 构建重置链接
            $resetLink = base_url('home/resetPassword?token=' . $token);

            // 发送邮件
            $emailSent = $this->sendResetEmail($email, $user['name'], $resetLink);

            if ($emailSent) {
                session()->setFlashdata('success', '重置链接已发送到您的邮箱，请在1小时内点击链接重置密码');
            } else {
                session()->setFlashdata('error', '邮件发送失败，请稍后重试');
            }

            return redirect()->to('/home/forgotPassword');
        }

        $data = [
            'title' => '忘记密码 - 博客系统',
        ];

        return view('frontend/forgot_password', $data);
    }

    /**
     * 发送密码重置邮件
     *
     * @param string $email 收件人邮箱
     * @param string $name 收件人姓名
     * @param string $resetLink 重置链接
     * @return bool 是否发送成功
     */
    protected function sendResetEmail($email, $name, $resetLink)
    {
        try {
            // 获取邮件配置
            $config = config('Email');

            // 配置邮件服务
            $emailService = \Config\Services::email();
            $emailService->initialize([
                'protocol' => $config->protocol ?? 'smtp',
                'SMTPHost' => $config->SMTPHost ?? 'smtp.example.com',
                'SMTPUser' => $config->SMTPUser ?? '',
                'SMTPPass' => $config->SMTPPass ?? '',
                'SMTPPort' => $config->SMTPPort ?? 587,
                'SMTPTimeout' => $config->SMTPTimeout ?? 5,
                'SMTPKeepAlive' => false,
                'SMTPAutoTLS' => true,
                'SMTPAuth' => true,
                'mailType' => 'html',
                'charset' => 'utf-8',
                'wordWrap' => true,
            ]);

            // 设置邮件内容
            $emailService->setFrom($config->fromEmail ?? 'noreply@example.com', $config->fromName ?? '博客系统');
            $emailService->setTo($email, $name);
            $emailService->setSubject('密码重置请求');

            // 邮件正文
            $message = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>密码重置</title>
</head>
<body style="font-family: 'Microsoft YaHei', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
        <h2 style="color: #333; text-align: center;">密码重置</h2>
        <p style="color: #666; line-height: 1.6;">尊敬的 {$name}，</p>
        <p style="color: #666; line-height: 1.6;">我们收到了您的密码重置请求。请点击下面的链接重置您的密码：</p>
        <div style="text-align: center; margin: 20px 0;">
            <a href="{$resetLink}" style="display: inline-block; padding: 12px 24px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px;">重置密码</a>
        </div>
        <p style="color: #666; line-height: 1.6; font-size: 14px;">此链接将在1小时后过期。如果您没有发起此请求，请忽略此邮件。</p>
        <p style="color: #999; font-size: 12px; text-align: center; margin-top: 20px;">© 2026 博客系统</p>
    </div>
</body>
</html>
HTML;

            $emailService->setMessage($message);

            // 发送邮件
            if ($emailService->send()) {
                log_message('info', '[AuthController.sendResetEmail] 邮件发送成功: ' . $email);
                return true;
            } else {
                log_message('error', '[AuthController.sendResetEmail] 邮件发送失败: ' . $emailService->printDebugger());
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', '[AuthController.sendResetEmail] 异常: ' . $e->getMessage());
            // 如果邮件发送失败，记录日志但不抛出异常，让用户继续使用系统
            return false;
        }
    }

    /**
     * 重置密码页面
     *
     * @return string|RedirectResponse 视图字符串或重定向响应
     */
    public function resetPassword()
    {
        // 如果已登录，重定向到首页
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        // 获取重置令牌
        $token = $this->request->getGet('token');

        // 如果没有令牌，显示错误
        if (empty($token)) {
            return view('frontend/reset_password', [
                'title' => '重置密码 - 博客系统',
                'error' => '无效的重置链接',
                'token' => null
            ]);
        }

        // 检查令牌是否有效
        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $token)->first();

        if (!$user) {
            return view('frontend/reset_password', [
                'title' => '重置密码 - 博客系统',
                'error' => '无效的重置链接',
                'token' => null
            ]);
        }

        // 检查令牌是否过期
        if (strtotime($user['reset_token_expires']) < time()) {
            return view('frontend/reset_password', [
                'title' => '重置密码 - 博客系统',
                'error' => '重置链接已过期，请重新申请',
                'token' => null
            ]);
        }

        // 如果是 POST 请求，处理密码重置
        if (strtolower($this->request->getMethod()) === 'post') {
            $password = $this->request->getPost('password');
            $confirmPassword = $this->request->getPost('confirm_password');

            // 验证输入
            if (empty($password)) {
                return view('frontend/reset_password', [
                    'title' => '重置密码 - 博客系统',
                    'error' => '请输入新密码',
                    'token' => $token
                ]);
            }

            if (strlen($password) < 6) {
                return view('frontend/reset_password', [
                    'title' => '重置密码 - 博客系统',
                    'error' => '密码至少需要6个字符',
                    'token' => $token
                ]);
            }

            if ($password !== $confirmPassword) {
                return view('frontend/reset_password', [
                    'title' => '重置密码 - 博客系统',
                    'error' => '两次输入的密码不一致',
                    'token' => $token
                ]);
            }

            // 更新密码
            $userModel->update($user['id'], [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'reset_token' => null,
                'reset_token_expires' => null
            ]);

            // 重定向到登录页面
            return redirect()->to('/home/login')->with('success', '密码重置成功，请登录');
        }

        // 显示重置密码表单
        return view('frontend/reset_password', [
            'title' => '重置密码 - 博客系统',
            'token' => $token
        ]);
    }

    /**
     * 登出
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function logout()
    {
        // 清除会话
        session()->destroy();

        return redirect()->to('/')->with('success', '已成功登出');
    }

    /**
     * 个人资料页面
     *
     * @return string 视图字符串
     */
    public function profile()
    {
        // 检查是否登录
        if (!session()->get('logged_in')) {
            return redirect()->to('/home/login')->with('error', '请先登录');
        }

        $userModel = new UserModel();
        $user = $userModel->find(session()->get('user_id'));

        // 初始化模型
        $categoryModel = new \App\Models\CategoryModel();
        $tagModel = new \App\Models\TagModel();

        $data = [
            'title' => '个人资料 - 博客系统',
            'user' => $user,
            // 为侧边栏传递数据
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags()
        ];

        return view('frontend/profile', $data);
    }

    /**
     * 更新个人资料
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function updateProfile()
    {
        // 检查是否登录
        if (!session()->get('logged_in')) {
            return redirect()->to('/home/login')->with('error', '请先登录');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();

        // 验证输入
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'bio' => 'max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 准备更新数据
        $data = [
            'name' => $this->request->getPost('name'),
            'bio' => $this->request->getPost('bio'),
        ];

        // 处理头像上传
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = $avatar->getRandomName();
            $avatar->move(ROOTPATH . 'public/uploads/avatars', $newName);
            $data['avatar'] = 'uploads/avatars/' . $newName;
        }

        // 更新用户信息
        if ($userModel->update($userId, $data)) {
            // 更新会话中的用户名
            session()->set('name', $data['name']);
            return redirect()->back()->with('success', '资料更新成功');
        } else {
            return redirect()->back()->withInput()->with('error', '资料更新失败');
        }
    }

    /**
     * 修改密码
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function changePassword()
    {
        // 检查是否登录
        if (!session()->get('logged_in')) {
            return redirect()->to('/home/login')->with('error', '请先登录');
        }

        if (strtolower($this->request->getMethod()) == 'post') {
            $currentPassword = $this->request->getVar('current_password');
            $newPassword = $this->request->getVar('new_password');
            $confirmNewPassword = $this->request->getVar('confirm_password');

            if ($newPassword != $confirmNewPassword) {
                session()->setFlashdata('error', '两次输入的新密码不一致');
                return redirect()->back()->withInput();
            }

            $userModel = new UserModel();
            $userId = session()->get('user_id');
            $user = $userModel->find($userId);

            if (!$user) {
                session()->setFlashdata('error', '用户不存在');
                return redirect()->to('/');
            }

            // 验证当前密码
            if (!password_verify((string) $currentPassword, (string) $user['password'])) {
                session()->setFlashdata('error', '当前密码错误');
                return redirect()->back()->withInput();
            }

            // 哈希新密码
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // 更新密码
            if (
                $userModel->update($userId, [
                    'password' => $passwordHash,
                    'updated_at' => date('Y-m-d H:i:s')
                ])
            ) {
                session()->setFlashdata('success', '密码修改成功');
            } else {
                session()->setFlashdata('error', '密码修改失败，请重试');
            }

            return redirect()->to('/home/profile');
        }

        return redirect()->to('/home/profile');
    }

    /**
     * 生成验证码
     * 生成验证码图片并返回
     *
     * @return \CodeIgniter\HTTP\Response 响应对象
     */
    public function captcha()
    {
        $captcha = new \App\Libraries\Captcha(session());
        $imageData = $captcha->generate();

        // 从base64字符串中提取图片数据
        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $imageData = base64_decode($imageData);

        return $this->response->setContentType('image/png')->setBody($imageData);
    }
}