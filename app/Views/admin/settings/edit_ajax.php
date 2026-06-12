<?php
$activePage = 'settings';
$pageTitle = '编辑配置';
$styles = '<style>.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<div class="card" id="settingsCard" style="display: none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">编辑配置</h5>
        <a href="<?= base_url('admin/settings') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i>
            返回</a>
    </div>
    <div class="card-body">
        <form id="settingsForm">
            <?= csrf_field() ?>
            <input type="hidden" id="setting_id">
            <div class="form-group">
                <label for="setting_key">键名</label>
                <input type="text" class="form-control" id="setting_key" readonly>
            </div>
            <div class="form-group">
                <label for="title">标题</label>
                <input type="text" class="form-control" id="title" readonly>
            </div>
            <div class="form-group">
                <label for="type">类型</label>
                <input type="text" class="form-control" id="type" readonly>
            </div>
            <div class="form-group">
                <label for="setting_value">值</label>
                <textarea class="form-control" id="setting_value" name="setting_value" rows="5"></textarea>
            </div>
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                <a href="<?= base_url('admin/settings') ?>" class="btn btn-secondary ml-2">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    let settingId = '<?= $settingId ?? '' ?>';

    document.addEventListener('DOMContentLoaded', function () {
        if (!settingId) {
            toastr.error('配置ID无效');
            setTimeout(() => { window.location.href = '<?= base_url('admin/settings') ?>'; }, 1500);
            return;
        }
        loadSettingData();
    });

    function loadSettingData() {
        showLoading();
        fetch('<?= base_url('api/settings/') ?>' + settingId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(result => {
                hideLoading();
                if (result.success) {
                    const setting = result.data;
                    document.getElementById('setting_id').value = setting.id;
                    document.getElementById('setting_key').value = setting.setting_key;
                    document.getElementById('title').value = setting.title;
                    const typeLabels = { 'text': '文本', 'textarea': '多行文本', 'editor': '富文本' };
                    document.getElementById('type').value = typeLabels[setting.type] || '其他';
                    document.getElementById('setting_value').value = setting.setting_value;
                    document.getElementById('settingsCard').style.display = 'block';
                } else {
                    toastr.error(result.message || '加载失败');
                    setTimeout(() => { window.location.href = '<?= base_url('admin/settings') ?>'; }, 1500);
                }
            }).catch(() => { hideLoading(); toastr.error('加载数据失败'); });
    }

    document.getElementById('settingsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('setting_value', document.getElementById('setting_value').value);

        fetch('<?= base_url('api/settings/') ?>' + settingId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success('配置更新成功');
                loadSettingData();
            } else {
                toastr.error(result.message || '更新失败');
            }
        }).catch(() => { hideLoading(); toastr.error('提交失败'); });
    });

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>