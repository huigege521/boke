<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 文章修订历史模型
 */
class PostRevisionModel extends Model
{
    protected $table = 'post_revisions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'post_id',
        'version',
        'title',
        'content',
        'excerpt',
        'category_id',
        'status',
        'visibility',
        'featured_image',
        'change_summary',
        'user_id',
        'is_current'
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';

    /**
     * 最大保留版本数
     */
    protected $maxRevisions = 50;

    /**
     * 创建新的修订记录
     *
     * @param int $postId 文章ID
     * @param array $postData 文章数据
     * @param string $changeSummary 修改说明
     * @param int $userId 用户ID
     * @return int|false 修订ID或false
     */
    public function createRevision(int $postId, array $postData, string $changeSummary = '', int $userId = null)
    {
        // 获取当前最新版本号
        $latestVersion = $this->where('post_id', $postId)
            ->orderBy('version', 'DESC')
            ->first();
        
        $newVersion = $latestVersion ? $latestVersion['version'] + 1 : 1;

        // 将所有旧版本的is_current设为0
        $this->where('post_id', $postId)
            ->set('is_current', 0)
            ->update();

        // 创建新版本
        $revisionData = [
            'post_id' => $postId,
            'version' => $newVersion,
            'title' => $postData['title'] ?? '',
            'content' => $postData['content'] ?? '',
            'excerpt' => $postData['excerpt'] ?? null,
            'category_id' => $postData['category_id'] ?? null,
            'status' => $postData['status'] ?? 'draft',
            'visibility' => $postData['visibility'] ?? 'public',
            'featured_image' => $postData['featured_image'] ?? null,
            'change_summary' => $changeSummary,
            'user_id' => $userId,
            'is_current' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $revisionId = $this->insert($revisionData);

        // 清理旧版本（保留最近的maxRevisions个）
        $this->cleanupOldRevisions($postId);

        return $revisionId;
    }

    /**
     * 获取文章的所有修订历史
     *
     * @param int $postId 文章ID
     * @param int $limit 限制数量
     * @return array 修订列表
     */
    public function getRevisions(int $postId, int $limit = 20): array
    {
        return $this->select('post_revisions.*, users.username, users.name as author_name')
            ->join('users', 'users.id = post_revisions.user_id', 'left')
            ->where('post_revisions.post_id', $postId)
            ->orderBy('post_revisions.version', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 获取指定版本的修订详情
     *
     * @param int $postId 文章ID
     * @param int $version 版本号
     * @return array|null 修订详情
     */
    public function getRevisionByVersion(int $postId, int $version): ?array
    {
        return $this->select('post_revisions.*, users.username, users.name as author_name')
            ->join('users', 'users.id = post_revisions.user_id', 'left')
            ->where('post_revisions.post_id', $postId)
            ->where('post_revisions.version', $version)
            ->first();
    }

    /**
     * 获取当前版本
     *
     * @param int $postId 文章ID
     * @return array|null 当前版本
     */
    public function getCurrentRevision(int $postId): ?array
    {
        return $this->select('post_revisions.*, users.username, users.name as author_name')
            ->join('users', 'users.id = post_revisions.user_id', 'left')
            ->where('post_revisions.post_id', $postId)
            ->where('post_revisions.is_current', 1)
            ->first();
    }

    /**
     * 恢复到指定版本
     *
     * @param int $postId 文章ID
     * @param int $version 要恢复的版本号
     * @param int $userId 操作用户ID
     * @return bool 是否成功
     */
    public function restoreToVersion(int $postId, int $version, int $userId): bool
    {
        $revision = $this->getRevisionByVersion($postId, $version);
        
        if (!$revision) {
            return false;
        }

        // 创建一个新的修订记录，标记为恢复操作
        $restoreData = [
            'title' => $revision['title'],
            'content' => $revision['content'],
            'excerpt' => $revision['excerpt'],
            'category_id' => $revision['category_id'],
            'status' => $revision['status'],
            'visibility' => $revision['visibility'],
            'featured_image' => $revision['featured_image'],
        ];

        $this->createRevision(
            $postId,
            $restoreData,
            "恢复到版本 #{$version}",
            $userId
        );

        return true;
    }

    /**
     * 清理旧版本
     *
     * @param int $postId 文章ID
     * @return void
     */
    private function cleanupOldRevisions(int $postId): void
    {
        $totalRevisions = $this->where('post_id', $postId)->countAllResults();
        
        if ($totalRevisions > $this->maxRevisions) {
            // 删除最旧的版本
            $oldestVersions = $this->select('id')
                ->where('post_id', $postId)
                ->orderBy('version', 'ASC')
                ->limit($totalRevisions - $this->maxRevisions)
                ->findAll();
            
            foreach ($oldestVersions as $revision) {
                $this->delete($revision['id']);
            }
        }
    }

    /**
     * 获取文章的修订数量
     *
     * @param int $postId 文章ID
     * @return int 修订数量
     */
    public function countRevisions(int $postId): int
    {
        return $this->where('post_id', $postId)->countAllResults();
    }
}
