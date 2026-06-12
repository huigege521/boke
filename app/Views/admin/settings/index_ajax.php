<?php
$activePage = 'settings';
$pageTitle = '配置管理';
$styles = '<style>.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay"><div class="spinner-border text-primary"></div></div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">系统配置</h5></div>
    <div class="card-body">
        <table class="table table-bordered table-hover" id="settingsTable">
            <thead>
                <tr><th>ID</th><th>键名</th><th>标题</th><th>类型</th><th>值预览</th><th>操作</th></tr>
            </thead>
            <tbody id="settingsBody"></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
});

function loadSettings() {
    showLoading();
    fetch('<?= base_url('api/settings') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json()).then(result => {
        hideLoading();
        if (result.success) {
            renderSettings(result.data);
        } else {
            toastr.error(result.message || '加载失败');
        }
    }).catch(() => { hideLoading(); toastr.error('加载数据失败'); });
}

function renderSettings(settings) {
    const tbody = document.getElementById('settingsBody');
    tbody.innerHTML = settings.map(s => {
        const typeLabels = {
            'text': '<span class="badge bg-info">文本</span>',
            'textarea': '<span class="badge bg-primary">多行文本</span>',
            'editor': '<span class="badge bg-success">富文本</span>'
        };
        const valuePreview = s.setting_value.length > 50 ? s.setting_value.substring(0, 50) + '...' : s.setting_value;
        return `
            <tr>
                <td>${s.id}</td>
                <td><code>${s.setting_key}</code></td>
                <td>${s.title}</td>
                <td>${typeLabels[s.type] || '<span class="badge bg-secondary">其他</span>'}</td>
                <td class="text-truncate max-w-xs">${valuePreview}</td>
                <td>
                    <a href="<?= base_url('admin/settings/edit/') ?>${s.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                </td>
            </tr>
        `;
    }).join('');
}

function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>
