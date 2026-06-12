<?php
$activePage = 'comments';
$pageTitle = '编辑评论';
$styles = '<style>.form-group{margin-bottom:1rem}.form-group label{display:block;margin-bottom:0.5rem;font-weight:500}.form-group input,.form-group textarea,.form-group select{width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px}.form-actions{margin-top:1.5rem}.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:none;justify-content:center;align-items:center}</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">编辑评论</h5>
        <a href="<?= base_url('admin/comments') ?>" class="btn btn-outline-secondary btn-sm"><i
                class="fas fa-arrow-left"></i> 返回</a>
    </div>
    <div class="card-body">
        <form id="commentForm">
            <?= csrf_field() ?>
            <input type="hidden" id="comment_id" value="<?= $commentId ?? '' ?>">

            <div class="form-group">
                <label>评论信息</label>
                <ul class="list-unstyled text-muted small">
                    <li>文章: <a id="post_link" href="#" target="_blank">-</a></li>
                    <li>评论者: <span id="user_name">-</span></li>
                    <li>评论时间: <span id="created_at">-</span></li>
                </ul>
            </div>

            <div class="form-group">
                <label for="content">评论内容 <span class="text-danger">*</span></label>
                <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label for="status">状态</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending">待审核</option>
                    <option value="approved">已批准</option>
                    <option value="rejected">已拒绝</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                <button type="button" class="btn btn-outline-danger" onclick="deleteComment()"><i
                        class="fas fa-trash"></i> 删除</button>
                <a href="<?= base_url('admin/comments') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    let commentId = '<?= $commentId ?? '' ?>';
    document.addEventListener('DOMContentLoaded', function () {
        if (!commentId) { toastr.error('评论ID无效'); setTimeout(() => { window.location.href = '<?= base_url('admin/comments') ?>'; }, 1500); return; }
        loadCommentData();
    });

    function loadCommentData() {
        showLoading();
        fetch('<?= base_url('api/comments/') ?>' + commentId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(result => {
                hideLoading();
                if (result.success) {
                    const comment = result.data;
                    document.getElementById('content').value = comment.content || '';
                    document.getElementById('status').value = comment.status || 'pending';
                    document.getElementById('user_name').textContent = comment.user_name || '游客';
                    document.getElementById('created_at').textContent = comment.created_at || '-';
                    if (comment.post) {
                        document.getElementById('post_link').textContent = comment.post.title || '文章';
                        document.getElementById('post_link').href = '/posts/' + comment.post.id;
                    }
                } else { toastr.error(result.message || '加载失败'); }
            }).catch(() => { hideLoading(); toastr.error('加载数据失败'); });
    }

    document.getElementById('commentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('content', document.getElementById('content').value);
        formData.append('status', document.getElementById('status').value);

        fetch('<?= base_url('api/comments/') ?>' + commentId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) { toastr.success('评论更新成功'); loadCommentData(); }
            else { toastr.error(result.message || '更新失败'); }
        }).catch(() => { hideLoading(); toastr.error('提交失败'); });
    });

    function deleteComment() {
        if (!confirm('确定要删除这条评论吗？')) return;
        showLoading();
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch('<?= base_url('api/comments/') ?>' + commentId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) { toastr.success('评论删除成功'); setTimeout(() => { window.location.href = '<?= base_url('admin/comments') ?>'; }, 1000); }
            else { toastr.error(result.message || '删除失败'); }
        }).catch(() => { hideLoading(); toastr.error('删除失败'); });
    }

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }
</script>

<?= view('admin/layouts/footer') ?>