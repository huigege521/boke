<?php
/**
 * CodeIgniter 4.6.3
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2024 British Columbia Institute of Technology
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace Config;

/**
 * Class Paths
 *
 * Holds the paths that are used throughout the framework
 *
 * @package Config
 */
class Paths
{
    /**
     * --------------------------------------------------------------------
     * Base Directory
     * --------------------------------------------------------------------
     *
     * This is the base directory of the application, without the trailing slash.
     *
     * @var string
     */
    public string $baseDirectory;

    /**
     * --------------------------------------------------------------------
     * Application Directory
     * --------------------------------------------------------------------
     *
     * This is the directory that contains the application code, without the
     * trailing slash. This is typically the `app` directory, but can be
     * changed, if needed.
     *
     * @var string
     */
    public string $appDirectory;

    /**
     * --------------------------------------------------------------------
     * System Directory
     * --------------------------------------------------------------------
     *
     * This is the directory that contains the system code, without the trailing
     * slash. This is typically the `system` directory, but can be changed, if
     * needed.
     *
     * @var string
     */
    public string $systemDirectory;

    /**
     * --------------------------------------------------------------------
     * Writable Directory
     * --------------------------------------------------------------------
     *
     * This is the directory that is writable by the application, without the
     * trailing slash. This is typically the `writable` directory, but can be
     * changed, if needed.
     *
     * @var string
     */
    public string $writableDirectory;

    /**
     * --------------------------------------------------------------------
     * Tests Directory
     * --------------------------------------------------------------------
     *
     * This is the directory that contains the tests, without the trailing slash.
     * This is typically the `tests` directory, but can be changed, if needed.
     *
     * @var string
     */
    public string $testsDirectory;

    /**
     * --------------------------------------------------------------------
     * Views Directory
     * --------------------------------------------------------------------
     *
     * This is the directory that contains the views, without the trailing slash.
     * This is typically the `app/Views` directory, but can be changed, if needed.
     *
     * @var string
     */
    public string $viewDirectory;

    /**
     * --------------------------------------------------------------------
     * Constructor
     * --------------------------------------------------------------------
     *
     * @param string $systemPath The path to the system directory
     */
    public function __construct(string $systemPath = '')
    {
        // 使用更可靠的路径拼接方式
        $basePath = dirname(__DIR__, 2); // 从 Config 目录向上两级到项目根目录
        $this->systemDirectory = $systemPath ?: $basePath . '/vendor/codeigniter4/framework/system';
        $this->appDirectory = $basePath . '/app';
        $this->baseDirectory = $basePath;
        $this->writableDirectory = $basePath . '/writable';
        $this->testsDirectory = $basePath . '/tests';
        $this->viewDirectory = $this->appDirectory . '/Views';

        // 确保路径使用正确的分隔符
        $this->systemDirectory = str_replace('\\', '/', $this->systemDirectory);
        $this->appDirectory = str_replace('\\', '/', $this->appDirectory);
        $this->baseDirectory = str_replace('\\', '/', $this->baseDirectory);
        $this->writableDirectory = str_replace('\\', '/', $this->writableDirectory);
        $this->testsDirectory = str_replace('\\', '/', $this->testsDirectory);
        $this->viewDirectory = str_replace('\\', '/', $this->viewDirectory);
    }
}
