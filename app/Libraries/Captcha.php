<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;

/**
 * 验证码生成与验证库
 */
class Captcha
{
    /**
     * 会话接口
     * @var SessionInterface
     */
    protected $session;

    /**
     * 构造函数
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * 生成验证码
     * @param int $width 宽度
     * @param int $height 高度
     * @param int $length 验证码长度
     * @return string 验证码图片的base64编码
     */
    public function generate($width = 120, $height = 40, $length = 4)
    {
        // 生成随机验证码
        $code = $this->generateCode($length);
        
        // 保存验证码到会话
        $this->session->set('captcha_code', $code);
        
        // 创建图像
        $image = imagecreate($width, $height);
        
        // 设置颜色
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $lineColor = imagecolorallocate($image, 128, 128, 128);
        
        // 填充背景
        imagefill($image, 0, 0, $bgColor);
        
        // 添加干扰线
        for ($i = 0; $i < 5; $i++) {
            imageline($image, 0, rand(0, $height), $width, rand(0, $height), $lineColor);
        }
        
        // 添加干扰点
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $lineColor);
        }
        
        // 绘制验证码
        $fontSize = $height * 0.7;
        $x = ($width - $length * $fontSize) / 2;
        $y = $height * 0.8;
        
        for ($i = 0; $i < $length; $i++) {
            $angle = rand(-15, 15);
            imagestring($image, 5, $x + $i * 25, $y - 10, $code[$i], $textColor);
        }
        
        // 输出图像
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * 验证验证码
     * @param string $code 用户输入的验证码
     * @return bool 是否验证通过
     */
    public function verify($code)
    {
        $storedCode = $this->session->get('captcha_code');
        
        if (!$storedCode) {
            return false;
        }
        
        $result = strtolower($code) === strtolower($storedCode);
        
        // 验证后清除验证码
        $this->session->remove('captcha_code');
        
        return $result;
    }

    /**
     * 生成随机验证码
     * @param int $length 长度
     * @return string 验证码
     */
    private function generateCode($length)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return $code;
    }
}