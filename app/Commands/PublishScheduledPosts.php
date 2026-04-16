<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PostModel;

class PublishScheduledPosts extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Blog';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'blog:publish-scheduled';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '发布所有到时间的定时文章';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'blog:publish-scheduled';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $postModel = new PostModel();
        $updatedCount = $postModel->publishScheduledPosts();

        CLI::write('定时发布任务执行完成，共发布 ' . $updatedCount . ' 篇文章', 'green');
    }
}
