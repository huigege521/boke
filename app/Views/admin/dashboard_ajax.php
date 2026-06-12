<?php
$activePage = 'dashboard';
$pageTitle = '仪表盘';
$styles = '<style>.stats-card{transition:transform 0.3s ease,box-shadow 0.3s ease;border-radius:8px}.stats-card:hover{transform:translateY(-5px);box-shadow:0 10px 20px rgba(0,0,0,0.1)}.stats-icon{font-size:1.5rem;margin-bottom:10px}.stats-number{font-size:1.5rem;font-weight:bold}.recent-activity{max-height:250px;overflow-y:auto}.table-sm th,.table-sm td{padding:0.4rem;font-size:0.85rem}.card{border-radius:8px}.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
$scripts = '<script src="' . base_url('js/chart.umd.min.js') . '"></script>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay"><div class="spinner-border text-primary"></div></div>

<div id="dashboardContent" style="display: none;">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">欢迎回来，<span id="username"></span>！</h5>
            <p class="card-text">今天是 <span id="todayDate"></span>，祝您工作愉快！</p>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-5 g-2 mb-3">
        <div class="col">
            <div class="card stats-card bg-primary text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">📝</div>
                    <h5 class="card-title">文章总数</h5>
                    <p class="card-text stats-number" id="total_posts">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-success text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">✅</div>
                    <h5 class="card-title">已发布</h5>
                    <p class="card-text stats-number" id="published_posts">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-warning text-dark h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">💬</div>
                    <h5 class="card-title">评论总数</h5>
                    <p class="card-text stats-number" id="total_comments">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-info text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">👥</div>
                    <h5 class="card-title">用户总数</h5>
                    <p class="card-text stats-number" id="total_users">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-danger text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">⚠️</div>
                    <h5 class="card-title">待审核评论</h5>
                    <p class="card-text stats-number" id="pending_comments">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-secondary text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">📁</div>
                    <h5 class="card-title">分类总数</h5>
                    <p class="card-text stats-number" id="total_categories">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-primary text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">🏷️</div>
                    <h5 class="card-title">标签总数</h5>
                    <p class="card-text stats-number" id="total_tags">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-secondary text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">📊</div>
                    <h5 class="card-title">待审核文章</h5>
                    <p class="card-text stats-number" id="pending_posts">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-warning text-dark h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">📩</div>
                    <h5 class="card-title">未处理消息</h5>
                    <p class="card-text stats-number" id="pending_contacts">0</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card bg-success text-white h-100">
                <div class="card-body text-center py-2">
                    <div class="stats-icon">✉️</div>
                    <h5 class="card-title">已处理消息</h5>
                    <p class="card-text stats-number" id="processed_contacts">0</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3 g-2">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2">文章状态分布</div>
                <div class="card-body p-2" style="height: 250px;"><canvas id="postStatusChart"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2">分类文章数量</div>
                <div class="card-body p-2" style="height: 250px;"><canvas id="categoryChart"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2">月度文章发布趋势</div>
                <div class="card-body p-2" style="height: 250px;"><canvas id="monthlyChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">最近文章</div>
                <div class="card-body">
                    <div class="recent-activity">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>标题</th><th>状态</th><th>时间</th></tr></thead>
                            <tbody id="recent_posts"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">最近评论</div>
                <div class="card-body">
                    <div class="recent-activity">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>内容</th><th>状态</th><th>时间</th></tr></thead>
                            <tbody id="recent_comments"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-3">
            <div class="card">
                <div class="card-header">最近联系消息</div>
                <div class="card-body">
                    <div class="recent-activity">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>姓名</th><th>主题</th><th>时间</th></tr></thead>
                            <tbody id="recent_contacts"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentRequest = null;  // 当前正在进行的请求
let isLoading = false;      // 是否正在加载中

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

function loadDashboardData() {
    // 如果正在加载中，取消之前的请求
    if (isLoading) {
        if (currentRequest) {
            currentRequest.abort();
        }
    }
    
    showLoading();
    
    // 创建新请求
    currentRequest = new XMLHttpRequest();
    currentRequest.open('GET', '<?= base_url('api/dashboard') ?>', true);
    currentRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    currentRequest.onload = function() {
        isLoading = false;
        currentRequest = null;
        
        if (this.status >= 200 && this.status < 400) {
            try {
                const result = JSON.parse(this.responseText);
                hideLoading();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('username').textContent = data.username || '管理员';
                    document.getElementById('todayDate').textContent = new Date().toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric' });
                    
                    document.getElementById('total_posts').textContent = data.stats.total_posts || 0;
                    document.getElementById('published_posts').textContent = data.stats.published_posts || 0;
                    document.getElementById('total_comments').textContent = data.stats.total_comments || 0;
                    document.getElementById('total_users').textContent = data.stats.total_users || 0;
                    document.getElementById('pending_comments').textContent = data.stats.pending_comments || 0;
                    document.getElementById('total_categories').textContent = data.stats.total_categories || 0;
                    document.getElementById('total_tags').textContent = data.stats.total_tags || 0;
                    document.getElementById('pending_posts').textContent = data.stats.pending_posts || 0;
                    document.getElementById('pending_contacts').textContent = data.stats.pending_contacts || 0;
                    document.getElementById('processed_contacts').textContent = data.stats.processed_contacts || 0;
                    
                    renderCharts(data);
                    renderRecentPosts(data.recent_posts);
                    renderRecentComments(data.recent_comments);
                    renderRecentContacts(data.recent_contacts);
                    
                    document.getElementById('dashboardContent').style.display = 'block';
                } else {
                    toastr.error(result.message || '加载失败');
                }
            } catch (e) {
                hideLoading();
                toastr.error('数据解析失败');
            }
        } else {
            hideLoading();
            toastr.error('请求失败');
        }
    };
    
    currentRequest.onerror = function() {
        isLoading = false;
        currentRequest = null;
        hideLoading();
        toastr.error('加载数据失败');
    };
    
    isLoading = true;
    currentRequest.send();
}

function renderCharts(data) {
    const postStatusCtx = document.getElementById('postStatusChart').getContext('2d');
    new Chart(postStatusCtx, {
        type: 'pie',
        data: {
            labels: ['已发布', '草稿', '待审核'],
            datasets: [{
                data: [data.stats.published_posts || 0, data.stats.draft_posts || 0, data.stats.pending_posts || 0],
                backgroundColor: ['#28a745', '#ffc107', '#17a2b8'],
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: data.category_data.map(c => c.name),
            datasets: [{
                label: '文章数量',
                data: data.category_data.map(c => c.count),
                backgroundColor: '#007bff',
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
    
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: data.monthly_data.map(m => m.month),
            datasets: [{
                label: '文章发布数',
                data: data.monthly_data.map(m => m.count),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
}

function renderRecentPosts(posts) {
    const tbody = document.getElementById('recent_posts');
    tbody.innerHTML = posts.map(p => {
        const statusMap = { 'published': '已发布', 'draft': '草稿', 'pending': '待审核' };
        const badgeMap = { 'published': 'success', 'draft': 'warning', 'pending': 'info' };
        return `<tr><td>${p.title}</td><td><span class="badge bg-${badgeMap[p.status]}">${statusMap[p.status]}</span></td><td>${p.created_at}</td></tr>`;
    }).join('');
}

function renderRecentComments(comments) {
    const tbody = document.getElementById('recent_comments');
    tbody.innerHTML = comments.map(c => {
        const statusMap = { 'approved': '已通过', 'pending': '待审核', 'rejected': '垃圾' };
        const badgeMap = { 'approved': 'success', 'pending': 'warning', 'rejected': 'danger' };
        return `<tr><td>${c.content.substring(0, 50)}...</td><td><span class="badge bg-${badgeMap[c.status]}">${statusMap[c.status]}</span></td><td>${c.created_at}</td></tr>`;
    }).join('');
}

function renderRecentContacts(contacts) {
    const tbody = document.getElementById('recent_contacts');
    tbody.innerHTML = contacts.map(c => `<tr><td>${c.name}</td><td>${c.subject.substring(0, 30)}...</td><td>${c.created_at}</td></tr>`).join('');
}

function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer', compact('scripts')) ?>
