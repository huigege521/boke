<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\PostModel;
use App\Models\UserModel;

/**
 * PostModel 单元测试
 */
class PostModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed = 'TestSeeder';
    
    protected $postModel;
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->postModel = new PostModel();
        $this->userModel = new UserModel();
    }

    /**
     * 测试获取已发布文章列表
     */
    public function testGetPublishedPosts()
    {
        // 创建测试用户
        $userId = $this->userModel->insert([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'status' => 'active'
        ]);

        // 创建测试文章
        $postId = $this->postModel->insert([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'This is a test post content.',
            'excerpt' => 'Test excerpt',
            'user_id' => $userId,
            'status' => 'published',
            'visibility' => 'public',
            'published_at' => date('Y-m-d H:i:s')
        ]);

        // 获取已发布文章
        $posts = $this->postModel->getPublishedPosts(10, 0);

        // 断言
        $this->assertIsArray($posts);
        $this->assertNotEmpty($posts);
        $this->assertEquals('Test Post', $posts[0]['title']);
        $this->assertEquals('published', $posts[0]['status']);
    }

    /**
     * 测试通过Slug获取文章
     */
    public function testGetPostBySlug()
    {
        // 创建测试文章
        $postId = $this->postModel->insert([
            'title' => 'Slug Test',
            'slug' => 'slug-test',
            'content' => 'Content for slug test',
            'user_id' => 1,
            'status' => 'published',
            'visibility' => 'public'
        ]);

        // 通过slug获取
        $post = $this->postModel->getPostBySlug('slug-test');

        $this->assertIsArray($post);
        $this->assertEquals('Slug Test', $post['title']);
        $this->assertEquals('slug-test', $post['slug']);
    }

    /**
     * 测试增加浏览次数
     */
    public function testIncrementViews()
    {
        // 创建测试文章
        $postId = $this->postModel->insert([
            'title' => 'Views Test',
            'slug' => 'views-test',
            'content' => 'Content',
            'user_id' => 1,
            'status' => 'published',
            'views' => 0
        ]);

        // 增加浏览次数
        $result = $this->postModel->incrementViews($postId);
        $this->assertTrue($result);

        // 验证浏览次数已增加
        $post = $this->postModel->find($postId);
        $this->assertEquals(1, $post['views']);

        // 再次增加
        $this->postModel->incrementViews($postId);
        $post = $this->postModel->find($postId);
        $this->assertEquals(2, $post['views']);
    }

    /**
     * 测试搜索功能
     */
    public function testSearchPosts()
    {
        // 创建测试文章
        $this->postModel->insert([
            'title' => 'PHP Programming',
            'slug' => 'php-programming',
            'content' => 'Learn PHP programming language',
            'user_id' => 1,
            'status' => 'published',
            'visibility' => 'public'
        ]);

        $this->postModel->insert([
            'title' => 'JavaScript Basics',
            'slug' => 'javascript-basics',
            'content' => 'Introduction to JavaScript',
            'user_id' => 1,
            'status' => 'published',
            'visibility' => 'public'
        ]);

        // 搜索包含"PHP"的文章
        $results = $this->postModel->searchPosts('PHP', 10, 0);
        
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertEquals('PHP Programming', $results[0]['title']);
    }

    /**
     * 测试创建草稿文章
     */
    public function testCreateDraftPost()
    {
        $postData = [
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => 'This is a draft',
            'user_id' => 1,
            'status' => 'draft',
            'visibility' => 'private'
        ];

        $postId = $this->postModel->insert($postData);
        $this->assertNotFalse($postId);

        // 验证书稿未出现在已发布列表中
        $publishedPosts = $this->postModel->getPublishedPosts(10, 0);
        $found = false;
        foreach ($publishedPosts as $post) {
            if ($post['id'] == $postId) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * 测试更新文章
     */
    public function testUpdatePost()
    {
        // 创建测试文章
        $postId = $this->postModel->insert([
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => 'Original content',
            'user_id' => 1,
            'status' => 'draft'
        ]);

        // 更新文章
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ];
        
        $result = $this->postModel->update($postId, $updateData);
        $this->assertTrue($result);

        // 验证更新
        $post = $this->postModel->find($postId);
        $this->assertEquals('Updated Title', $post['title']);
        $this->assertEquals('Updated content', $post['content']);
    }

    /**
     * 测试删除文章
     */
    public function testDeletePost()
    {
        // 创建测试文章
        $postId = $this->postModel->insert([
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'content' => 'Will be deleted',
            'user_id' => 1,
            'status' => 'draft'
        ]);

        // 删除文章
        $result = $this->postModel->delete($postId);
        $this->assertTrue($result);

        // 验证已删除
        $post = $this->postModel->find($postId);
        $this->assertNull($post);
    }

    /**
     * 测试无效Slug查询
     */
    public function testGetPostByInvalidSlug()
    {
        $post = $this->postModel->getPostBySlug('non-existent-slug');
        $this->assertNull($post);
    }

    /**
     * 测试分页功能
     */
    public function testPagination()
    {
        // 创建多个测试文章
        for ($i = 1; $i <= 25; $i++) {
            $this->postModel->insert([
                'title' => "Post {$i}",
                'slug' => "post-{$i}",
                'content' => "Content {$i}",
                'user_id' => 1,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => date('Y-m-d H:i:s', strtotime("-{$i} days"))
            ]);
        }

        // 获取第一页（每页10条）
        $page1 = $this->postModel->getPublishedPosts(10, 0);
        $this->assertCount(10, $page1);

        // 获取第二页
        $page2 = $this->postModel->getPublishedPosts(10, 10);
        $this->assertCount(10, $page2);

        // 确保两页数据不同
        $this->assertNotEquals($page1[0]['id'], $page2[0]['id']);
    }
}
