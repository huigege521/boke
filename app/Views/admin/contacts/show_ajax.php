<?php
$activePage = 'contacts';
$pageTitle = '联系消息详情';
$styles = '<style>.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<div class="card mb-4" id="contactCard" style="display: none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">联系消息详情</h5>
        <a href="<?= base_url('admin/contacts') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i>
            返回列表</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">姓名</label>
                    <p class="form-control-plaintext" id="contact_name"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">邮箱</label>
                    <p class="form-control-plaintext" id="contact_email"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">主题</label>
                    <p class="form-control-plaintext" id="contact_subject"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">提交时间</label>
                    <p class="form-control-plaintext" id="contact_created_at"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">处理状态</label>
                    <p class="form-control-plaintext" id="contact_status"></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">消息内容</label>
                    <div class="border rounded p-3 bg-light" id="contact_message"></div>
                </div>
            </div>
        </div>
        <div class="mt-4" id="contact_actions">
            <button type="button" id="processBtn" class="btn btn-success" style="display: none;">
                <i class="fas fa-check"></i> 标记为已处理
            </button>
            <button type="button" id="deleteBtn" class="btn btn-danger ml-2">
                <i class="fas fa-trash"></i> 删除
            </button>
        </div>
    </div>
</div>

<script>
    let contactId = '<?= $contactId ?? '' ?>';
    let currentContact = null;

    document.addEventListener('DOMContentLoaded', function () {
        if (!contactId) {
            toastr.error('消息ID无效');
            setTimeout(() => { window.location.href = '<?= base_url('admin/contacts') ?>'; }, 1500);
            return;
        }
        loadContactData();
    });

    function loadContactData() {
        showLoading();
        fetch('<?= base_url('api/contacts/') ?>' + contactId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(result => {
                hideLoading();
                if (result.success) {
                    currentContact = result.data;
                    populateContactData(currentContact);
                    document.getElementById('contactCard').style.display = 'block';
                } else {
                    toastr.error(result.message || '加载失败');
                    setTimeout(() => { window.location.href = '<?= base_url('admin/contacts') ?>'; }, 1500);
                }
            }).catch(() => { hideLoading(); toastr.error('加载数据失败'); });
    }

    function populateContactData(contact) {
        document.getElementById('contact_name').textContent = contact.name || '-';
        document.getElementById('contact_email').textContent = contact.email || '-';
        document.getElementById('contact_subject').textContent = contact.subject || '-';
        document.getElementById('contact_created_at').textContent = contact.created_at || '-';
        document.getElementById('contact_message').innerHTML = (contact.message ? nl2br(contact.message) : '-');

        if (contact.status === 'processed') {
            document.getElementById('contact_status').innerHTML = '<span class="badge bg-success">已处理</span>';
            document.getElementById('processBtn').style.display = 'none';
        } else {
            document.getElementById('contact_status').innerHTML = '<span class="badge bg-warning text-dark">未处理</span>';
            document.getElementById('processBtn').style.display = 'inline-block';
        }
    }

    document.getElementById('processBtn').addEventListener('click', function () {
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'PUT');
        fetch('<?= base_url('api/contacts/process/') ?>' + contactId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success('标记成功');
                loadContactData();
            } else {
                toastr.error(result.message || '操作失败');
            }
        }).catch(() => { hideLoading(); toastr.error('操作失败'); });
    });

    document.getElementById('deleteBtn').addEventListener('click', function () {
        if (!confirm('确定要删除这条联系消息吗？')) return;
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch('<?= base_url('api/contacts/') ?>' + contactId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success('删除成功');
                setTimeout(() => { window.location.href = '<?= base_url('admin/contacts') ?>'; }, 1000);
            } else {
                toastr.error(result.message || '删除失败');
            }
        }).catch(() => { hideLoading(); toastr.error('删除失败'); });
    });

    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>