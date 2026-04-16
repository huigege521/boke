<?php
$activePage = 'dashboard';
$pageTitle = '仪表盘';

// 准备分类数据
$categoryLabels = [];
$categoryCounts = [];
if (isset($category_data) && is_array($category_data)) {
    foreach ($category_data as $category) {
        $categoryLabels[] = $category['name'];
        $categoryCounts[] = $category['count'];
    }
}

// 准备月度数据
$monthlyLabels = [];
$monthlyCounts = [];
if (isset($monthly_data) && is_array($monthly_data)) {
    foreach ($monthly_data as $month) {
        $monthlyLabels[] = $month['month'];
        $monthlyCounts[] = $month['count'];
    }
}

$styles = '<style>
        .stats-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 8px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .stats-number {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .recent-activity {
            max-height: 250px;
            overflow-y: auto;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.4rem;
            font-size: 0.85rem;
        }

        .card {
            border-radius: 8px;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 1rem;
        }

        .card-title {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 10px;
            }
            .stats-number {
                font-size: 1.2rem;
            }
        }
    </style>';
$scripts = '<script src="' . base_url('js/chart.umd.min.js') . '"></script>
    <script>
        // 文章状态分布图表
        const postStatusCtx = document.getElementById("postStatusChart").getContext("2d");
        new Chart(postStatusCtx, {
            type: "pie",
            data: {
                labels: ["已发布", "草稿", "待审核"],
                datasets: [{
                    data: [' . (isset($published_posts) ? $published_posts : 0) . ', ' . (isset($draft_posts) ? $draft_posts : 0) . ', ' . ((isset($total_posts) ? $total_posts : 0) - (isset($published_posts) ? $published_posts : 0) - (isset($draft_posts) ? $draft_posts : 0)) . '],
                    backgroundColor: ["#28a745", "#ffc107", "#17a2b8"],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // 分类文章数量图表
        const categoryCtx = document.getElementById("categoryChart").getContext("2d");
        new Chart(categoryCtx, {
            type: "bar",
            data: {
                labels: [' . implode(', ', array_map(function ($label) {
    return '"' . $label . '"';
}, $categoryLabels)) . '],
                datasets: [{
                    label: "文章数量",
                    data: [' . implode(', ', $categoryCounts) . '],
                    backgroundColor: "#007bff",
                    borderColor: "#0069d9",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });

        // 月度文章发布趋势图表
        const monthlyCtx = document.getElementById("monthlyChart").getContext("2d");
        new Chart(monthlyCtx, {
            type: "line",
            data: {
                labels: [' . implode(', ', array_map(function ($label) {
    return '"' . $label . '"';
}, $monthlyLabels)) . '],
                datasets: [{
                    label: "文章发布数",
                    data: [' . implode(', ', $monthlyCounts) . '],
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    duration: 1500
                }
            }
        });
    </script>';

?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<!-- 欢迎信息 -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">欢迎回来，<?= session()->get('username') ?>！</h5>
        <p class="card-text">今天是 <?= date('Y年m月d日') ?>，祝您工作愉快！</p>
    </div>
</div>

<!-- 统计卡片 -->
<div class="row row-cols-2 row-cols-md-5 g-2 mb-3">
    <div class="col">
        <div class="card stats-card bg-primary text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">📝</div>
                <h5 class="card-title">文章总数</h5>
                <p class="card-text stats-number"><?= isset($total_posts) ? $total_posts : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-success text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">✅</div>
                <h5 class="card-title">已发布</h5>
                <p class="card-text stats-number"><?= isset($published_posts) ? $published_posts : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-warning text-dark h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">💬</div>
                <h5 class="card-title">评论总数</h5>
                <p class="card-text stats-number"><?= isset($total_comments) ? $total_comments : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-info text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">👥</div>
                <h5 class="card-title">用户总数</h5>
                <p class="card-text stats-number"><?= isset($total_users) ? $total_users : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-danger text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">⚠️</div>
                <h5 class="card-title">待审核评论</h5>
                <p class="card-text stats-number"><?= isset($pending_comments) ? $pending_comments : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-secondary text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">📁</div>
                <h5 class="card-title">分类总数</h5>
                <p class="card-text stats-number"><?= isset($total_categories) ? $total_categories : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-primary text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">🏷️</div>
                <h5 class="card-title">标签总数</h5>
                <p class="card-text stats-number"><?= isset($total_tags) ? $total_tags : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-secondary text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">📊</div>
                <h5 class="card-title">待审核文章</h5>
                <p class="card-text stats-number">
                    <?= (isset($total_posts) ? $total_posts : 0) - (isset($published_posts) ? $published_posts : 0) - (isset($draft_posts) ? $draft_posts : 0) ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-warning text-dark h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">📩</div>
                <h5 class="card-title">未处理消息</h5>
                <p class="card-text stats-number"><?= isset($pending_contacts) ? $pending_contacts : 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card bg-success text-white h-100">
            <div class="card-body text-center py-2">
                <div class="stats-icon">✉️</div>
                <h5 class="card-title">已处理消息</h5>
                <p class="card-text stats-number"><?= isset($processed_contacts) ? $processed_contacts : 0 ?></p>
            </div>
        </div>
    </div>
</div>

<!-- 图表区域 -->
<div class="row mb-3 g-2">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-2">
                文章状态分布
            </div>
            <div class="card-body p-2" style="height: 250px;">
                <canvas id="postStatusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-2">
                分类文章数量
            </div>
            <div class="card-body p-2" style="height: 250px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-2">
                月度文章发布趋势
            </div>
            <div class="card-body p-2" style="height: 250px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- 最近动态 -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                最近文章
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>标题</th>
                                <th>状态</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (isset($recent_posts) ? $recent_posts : [] as $post): ?>
                                <tr>
                                    <td><?= $post['title'] ?></td>
                                    <td>
                                        <span
                                            class="badge bg-<?= $post['status'] == 'published' ? 'success' : ($post['status'] == 'draft' ? 'warning' : 'info') ?>">
                                            <?= $post['status'] == 'published' ? '已发布' : ($post['status'] == 'draft' ? '草稿' : '待审核') ?>
                                        </span>
                                    </td>
                                    <td><?= $post['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                最近评论
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>内容</th>
                                <th>状态</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (isset($recent_comments) ? $recent_comments : [] as $comment): ?>
                                <tr>
                                    <td><?= substr($comment['content'], 0, 50) ?>...</td>
                                    <td>
                                        <span
                                            class="badge bg-<?= $comment['status'] == 'approved' ? 'success' : ($comment['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                            <?= $comment['status'] == 'approved' ? '已通过' : ($comment['status'] == 'pending' ? '待审核' : '垃圾') ?>
                                        </span>
                                    </td>
                                    <td><?= $comment['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mt-3">
        <div class="card">
            <div class="card-header">
                最近联系消息
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>姓名</th>
                                <th>主题</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (isset($recent_contacts) ? $recent_contacts : [] as $contact): ?>
                                <tr>
                                    <td><?= $contact['name'] ?></td>
                                    <td><?= substr($contact['subject'], 0, 30) ?>...</td>
                                    <td><?= $contact['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/layouts/footer', compact('scripts')) ?>