<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- 首页 -->
    <url>
        <loc><?= site_url() ?></loc>
        <lastmod><?= $last_modified ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- 文章页面 -->
    <?php foreach ($posts as $post): ?>
    <url>
        <loc><?= site_url('post/' . $post['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($post['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- 分类页面 -->
    <?php foreach ($categories as $category): ?>
    <url>
        <loc><?= site_url('category/' . $category['slug']) ?></loc>
        <lastmod><?= $last_modified ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- 标签页面 -->
    <?php foreach ($tags as $tag): ?>
    <url>
        <loc><?= site_url('tag/' . $tag['slug']) ?></loc>
        <lastmod><?= $last_modified ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>
</urlset>