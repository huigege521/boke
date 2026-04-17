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
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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