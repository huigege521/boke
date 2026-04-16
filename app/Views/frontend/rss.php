<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>博客系统</title>
        <link><?= site_url() ?></link>
        <description>个人博客系统</description>
        <language>zh-CN</language>
        <lastBuildDate><?= $last_build_date ?></lastBuildDate>
        <atom:link href="<?= site_url('rss') ?>" rel="self" type="application/rss+xml" />
        
        <?php foreach ($posts as $post): ?>
        <item>
            <title><?= htmlspecialchars($post['title']) ?></title>
            <link><?= site_url('post/' . $post['slug']) ?></link>
            <description><?= htmlspecialchars(strip_tags($post['content'])) ?></description>
            <pubDate><?= date('r', strtotime($post['published_at'])) ?></pubDate>
            <guid isPermaLink="true"><?= site_url('post/' . $post['slug']) ?></guid>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>