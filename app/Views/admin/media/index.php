<?php
$activePage = 'media';
$pageTitle = '媒体库';
$styles = '';
$scripts = '';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles', 'scripts')) ?>


<section class="content">
    <div class="container-fluid">
        <!-- 统计信息 -->
        <div class="row g-4 mb-6">
            <?php foreach ($stats as $stat): ?>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h3><?= $stat['count'] ?></h3>
                            <p><?= ucfirst($stat['file_type']) ?> 文件</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-<?= $stat['file_type'] === 'image' ? 'images' : ($stat['file_type'] === 'document' ? 'file-alt' : 'video') ?> fa-2x"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 工具栏 -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="$('#uploadModal').modal('show');">
                                        <i class="fas fa-upload mr-2"></i> 上传文件
                                    </button>
                                    <button type="button" class="btn btn-danger btn-lg" id="batchDeleteBtn" disabled>
                                        <i class="fas fa-trash mr-2"></i> 批量删除
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <select class="form-control form-control-lg" id="typeFilter">
                                    <option value="">所有类型</option>
                                    <option value="image" <?= $type === 'image' ? 'selected' : '' ?>>图片</option>
                                    <option value="document" <?= $type === 'document' ? 'selected' : '' ?>>文档</option>
                                    <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>视频</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="input-group input-group-lg">
                                    <input type="text" class="form-control" id="searchInput" placeholder="搜索文件名..." value="<?= $search ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-lg btn-outline-secondary active" id="gridView">
                                        <i class="fas fa-th"></i>
                                    </button>
                                    <button type="button" class="btn btn-lg btn-outline-secondary" id="listView">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 媒体网格 -->
        <div class="row g-4" id="mediaGrid">
            <?php if (empty($media)): ?>
                <div class="col-12 text-center py-10">
                    <div class="empty-state">
                        <i class="fas fa-folder-open fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted mb-2">暂无媒体文件</h3>
                        <p class="text-secondary">点击"上传文件"按钮开始上传</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($media as $item): ?>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 media-item" data-id="<?= $item['id'] ?>">
                        <div class="card media-card <?= $item['is_image'] ? 'image-card' : 'file-card' ?>">
                            <div class="card-img-wrapper">
                                <?php if ($item['is_image']): ?>
                                    <img src="<?= $item['file_url'] ?>" class="card-img-top"
                                        alt="<?= $item['alt_text'] ?? $item['filename'] ?>">
                                <?php else: ?>
                                    <div class="file-icon">
                                        <i class="fas fa-<?= $item['file_type'] === 'document' ? 'file-alt' : 'video' ?> fa-4x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-overlay">
                                    <div class="overlay-actions">
                                        <button type="button" class="btn btn-sm btn-light view-btn"
                                            data-id="<?= $item['id'] ?>" title="查看">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light edit-btn"
                                            data-id="<?= $item['id'] ?>" title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light copy-btn"
                                            data-url="<?= $item['file_url'] ?>" title="复制链接">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="<?= $item['id'] ?>" title="删除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="select-overlay">
                                    <input type="checkbox" class="media-select" value="<?= $item['id'] ?>">
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <p class="card-text text-truncate mb-1" title="<?= $item['original_name'] ?>">
                                    <?= $item['original_name'] ?>
                                </p>
                                <small class="text-muted d-block">
                                    <span><?= formatFileSize($item['file_size']) ?></span>
                                    <span class="mx-1">·</span>
                                    <span><?= date('Y-m-d', strtotime($item['created_at'])) ?></span>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 分页 -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=<?= $pagination['current_page'] - 1 ?><?= $type ? '&type=' . $type : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">上一页</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <?php if ($i == $pagination['current_page']): ?>
                                    <li class="page-item active">
                                        <span class="page-link"><?= $i ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?= $i ?><?= $type ? '&type=' . $type : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=<?= $pagination['current_page'] + 1 ?><?= $type ? '&type=' . $type : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">下一页</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 上传模态框 -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">上传文件</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                    <p>拖拽文件到此处或点击上传</p>
                    <input type="file" id="fileInput" multiple
                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,video/*" style="display: none;">
                    <button type="button" class="btn btn-outline-primary"
                        onclick="document.getElementById('fileInput').click()">
                        选择文件
                    </button>
                </div>
                <div id="uploadPreview" class="mt-3"></div>
                <div class="mt-3">
                    <small class="text-muted">
                        支持的格式：JPG, PNG, GIF, WebP, PDF, DOC, DOCX, XLS, XLSX, TXT, CSV, MP4, WebM<br>
                        最大文件大小：10MB
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="startUpload" disabled>开始上传</button>
            </div>
        </div>
    </div>
</div>

<!-- 查看详情模态框 -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">文件详情</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div id="detailPreview" class="text-center"></div>
                    </div>
                    <div class="col-md-4">
                        <div id="detailInfo"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 编辑模态框 -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">编辑文件信息</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" name="csrf_token" value="<?= csrf_hash() ?>">
                    <div class="form-group">
                        <label for="editTitle">标题</label>
                        <input type="text" class="form-control" id="editTitle" name="title">
                    </div>
                    <div class="form-group">
                        <label for="editAltText">替代文本 (Alt Text)</label>
                        <input type="text" class="form-control" id="editAltText" name="alt_text">
                        <small class="form-text text-muted">用于图片的 SEO 和可访问性</small>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">描述</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* 媒体卡片样式 */
    .media-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .media-card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .card-img-wrapper {
        position: relative;
        height: 180px;
        overflow: hidden;
        background: #f8f9fa;
    }

    .card-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .media-card:hover .card-img-wrapper img {
        transform: scale(1.05);
    }

    .file-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #495057;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .file-icon i {
        font-size: 48px;
        opacity: 0.7;
    }

    /* 卡片叠加层 */
    .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
        backdrop-filter: blur(2px);
    }

    .media-card:hover .card-overlay {
        opacity: 1;
    }

    .overlay-actions {
        display: flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.9);
        padding: 8px;
        border-radius: 8px;
    }

    .overlay-actions .btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .overlay-actions .btn:hover {
        transform: scale(1.1);
    }

    /* 选择覆盖层 */
    .select-overlay {
        position: absolute;
        top: 12px;
        left: 12px;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .select-overlay input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .media-card:hover .select-overlay,
    .media-card.selected .select-overlay {
        opacity: 1;
    }

    .media-card.selected {
        border: 2px solid #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .media-card.selected .card-img-wrapper::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 123, 255, 0.15);
    }

    /* 卡片内容 */
    .media-card .card-body {
        padding: 12px;
        background: #ffffff;
    }

    .media-card .card-text {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 4px;
        color: #343a40;
    }

    .media-card small {
        font-size: 12px;
        color: #6c757d;
    }

    /* 上传区域 */
    .upload-area {
        border: 2px dashed #ced4da;
        border-radius: 12px;
        padding: 60px 40px;
        text-align: center;
        color: #6c757d;
        transition: all 0.3s ease;
        background: #f8f9fa;
        min-height: 200px;
    }

    .upload-area:hover,
    .upload-area.dragover {
        border-color: #007bff;
        background: #f0f8ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.1);
    }

    .upload-area i {
        font-size: 48px;
        margin-bottom: 16px;
        color: #007bff;
    }

    .upload-area p {
        font-size: 16px;
        margin-bottom: 20px;
        color: #495057;
    }

    .upload-area .btn {
        border-radius: 6px;
        padding: 8px 24px;
        font-weight: 500;
    }

    /* 上传预览 */
    .upload-preview-item {
        display: inline-block;
        margin: 8px;
        position: relative;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .upload-preview-item:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .upload-preview-item img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 6px;
    }

    .upload-preview-item .remove-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .upload-preview-item .remove-btn:hover {
        background: #dc3545;
        transform: scale(1.1);
    }

    /* 视图切换样式 */
    /* 网格视图 - 默认使用Bootstrap网格系统 */
    #mediaGrid {
        display: flex;
        flex-wrap: wrap;
    }

    /* 列表视图样式 */
    #mediaGrid.list-view {
        display: block;
    }

    #mediaGrid.list-view .media-item {
        width: 100%;
        margin-bottom: 8px;
    }

    #mediaGrid.list-view .media-card {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #ffffff;
        transition: all 0.2s ease;
    }

    #mediaGrid.list-view .media-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-color: #007bff;
    }

    #mediaGrid.list-view .card-img-wrapper {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        margin-right: 16px;
        border-radius: 4px;
        background: #f8f9fa;
    }

    #mediaGrid.list-view .card-body {
        flex-grow: 1;
        padding: 0;
        min-width: 0;
    }

    #mediaGrid.list-view .media-card {
        position: relative;
        display: flex;
        align-items: center;
    }

    #mediaGrid.list-view .card-img-wrapper {
        position: relative;
        flex-shrink: 0;
    }

    #mediaGrid.list-view .card-img-wrapper .card-overlay {
        display: none;
    }

    #mediaGrid.list-view .media-card::after {
        content: '';
        display: flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
    }

    #mediaGrid.list-view .media-card .overlay-actions {
        position: relative;
        display: flex;
        align-items: center;
        gap: 6px;
        background: transparent;
        padding: 0;
        margin-left: auto;
        z-index: 10;
    }

    #mediaGrid.list-view .media-card .overlay-actions .btn {
        width: 32px;
        height: 32px;
        font-size: 12px;
        border-radius: 4px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    #mediaGrid.list-view .media-card .overlay-actions .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    #mediaGrid.list-view .card-text {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 4px;
        color: #343a40;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #mediaGrid.list-view .card-body small {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #mediaGrid.list-view .card-overlay {
        position: static;
        opacity: 1;
        background: transparent;
        backdrop-filter: none;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin-left: auto;
        flex-shrink: 0;
    }

    #mediaGrid.list-view .overlay-actions {
        background: transparent;
        padding: 0;
        gap: 6px;
        display: flex;
        align-items: center;
    }

    #mediaGrid.list-view .overlay-actions .btn {
        width: 32px;
        height: 32px;
        font-size: 12px;
        border-radius: 4px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    #mediaGrid.list-view .overlay-actions .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .card-img-wrapper {
            height: 150px;
        }
        
        .upload-area {
            padding: 40px 20px;
        }
        
        .upload-preview-item img {
            width: 100px;
            height: 100px;
        }
    }
</style>

<script>
    $(document).ready(function () {
        // 文件选择
        let selectedFiles = [];
        let selectedMedia = [];

        // 拖拽上传
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            selectedFiles = Array.from(files);
            updateUploadPreview();
        }

        function updateUploadPreview() {
            const preview = document.getElementById('uploadPreview');
            preview.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'upload-preview-item';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    div.appendChild(img);
                } else {
                    const icon = document.createElement('div');
                    icon.className = 'file-icon';
                    icon.innerHTML = '<i class="fas fa-file fa-3x"></i>';
                    div.appendChild(icon);
                }

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = () => {
                    selectedFiles.splice(index, 1);
                    updateUploadPreview();
                };
                div.appendChild(removeBtn);

                preview.appendChild(div);
            });

            document.getElementById('startUpload').disabled = selectedFiles.length === 0;
        }

        // 开始上传
        $('#startUpload').click(function () {
            if (selectedFiles.length === 0) return;

            const formData = new FormData();
            // 动态获取最新的 CSRF 令牌
            const csrfToken = $('input[name="csrf_token"]').val();
            formData.append('csrf_token', csrfToken);
            selectedFiles.forEach(file => {
                formData.append('files[]', file);
            });

            $.ajax({
                url: '/admin/media/upload-multiple',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    // 更新 CSRF 令牌
                    if (response.csrf_token && response.csrf_name) {
                        $('input[name="' + response.csrf_name + '"]').val(response.csrf_token);
                    }
                    
                    if (response.success) {
                        if (response.success_count > 0) {
                            toastr.success(response.message + '（成功：' + response.success_count + '个，失败：' + response.error_count + '个）');
                        } else {
                            toastr.info(response.message);
                        }
                        $('#uploadModal').modal('hide');
                        location.reload();
                    } else {
                        if (response.errors && response.errors.length > 0) {
                            let errorMessage = response.message + '<br><br>具体错误：<br>';
                            response.errors.forEach(function(error, index) {
                                errorMessage += (index + 1) + '. ' + error.name + ': ' + error.error + '<br>';
                            });
                            toastr.error(errorMessage, '上传失败', {timeOut: 5000, allowHtml: true});
                        } else {
                            toastr.error(response.message);
                        }
                    }
                },
                error: function (xhr) {
                    let errorMessage = '上传失败';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                        // 更新 CSRF 令牌
                        if (response.csrf_token && response.csrf_name) {
                            $('input[name="' + response.csrf_name + '"]').val(response.csrf_token);
                        }
                    } catch (e) {
                        // 解析失败，使用默认消息
                    }
                    toastr.error(errorMessage);
                }
            });
        });

        // 媒体选择
        $(document).on('change', '.media-select', function () {
            const id = $(this).val();
            const card = $(this).closest('.media-card');

            if ($(this).is(':checked')) {
                selectedMedia.push(id);
                card.addClass('selected');
            } else {
                selectedMedia = selectedMedia.filter(m => m !== id);
                card.removeClass('selected');
            }

            $('#batchDeleteBtn').prop('disabled', selectedMedia.length === 0);
        });

        // 批量删除
        $('#batchDeleteBtn').click(function () {
            if (selectedMedia.length === 0) return;

            if (!confirm('确定要删除选中的 ' + selectedMedia.length + ' 个文件吗？')) return;

            $.ajax({
                url: '/admin/media/batch-delete',
                type: 'POST',
                data: { ids: selectedMedia, csrf_token: $('input[name="csrf_token"]').val() },
                success: function (response) {
                    // 更新 CSRF 令牌
                    if (response.csrf_token && response.csrf_name) {
                        $('input[name="' + response.csrf_name + '"]').val(response.csrf_token);
                    }
                    
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('删除失败');
                }
            });
        });

        // 查看详情
        $(document).on('click', '.view-btn', function (e) {
            e.stopPropagation();
            const id = $(this).data('id');

            $.get('/admin/media/detail/' + id, function (response) {
                if (response.success) {
                    const media = response.data;

                    // 预览
                    let preview = '';
                    if (media.is_image) {
                        preview = '<img src="' + media.file_url + '" class="img-fluid" alt="' + (media.alt_text || '') + '">';
                    } else if (media.file_type === 'video') {
                        preview = '<video src="' + media.file_url + '" controls class="img-fluid"></video>';
                    } else {
                        preview = '<i class="fas fa-file-alt fa-5x text-muted"></i>';
                    }
                    $('#detailPreview').html(preview);

                    // 信息
                    let info = `
                    <p><strong>文件名：</strong>${media.original_name}</p>
                    <p><strong>类型：</strong>${media.mime_type}</p>
                    <p><strong>大小：</strong>${formatFileSize(media.file_size)}</p>
                    ${media.width ? `<p><strong>尺寸：</strong>${media.width} x ${media.height}</p>` : ''}
                    <p><strong>上传者：</strong>${media.uploader_name || '未知'}</p>
                    <p><strong>上传时间：</strong>${media.created_at}</p>
                    <hr>
                    <p><strong>文件 URL：</strong></p>
                    <div class="input-group">
                        <input type="text" class="form-control" value="${window.location.origin}${media.file_url}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary copy-url" type="button">复制</button>
                        </div>
                    </div>
                `;
                    $('#detailInfo').html(info);

                    $('#detailModal').modal('show');
                }
            });
        });

        // 编辑
        $(document).on('click', '.edit-btn', function (e) {
            e.stopPropagation();
            const id = $(this).data('id');

            $.get('/admin/media/detail/' + id, function (response) {
                if (response.success) {
                    const media = response.data;
                    $('#editId').val(media.id);
                    $('#editTitle').val(media.title || '');
                    $('#editAltText').val(media.alt_text || '');
                    $('#editDescription').val(media.description || '');
                    $('#editModal').modal('show');
                }
            });
        });

        // 保存编辑
        $('#editForm').submit(function (e) {
            e.preventDefault();
            const id = $('#editId').val();

            $.ajax({
                url: '/admin/media/edit/' + id,
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#editModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('保存失败');
                }
            });
        });

        // 复制链接
        $(document).on('click', '.copy-btn', function (e) {
            e.stopPropagation();
            const url = $(this).data('url');
            copyToClipboard(window.location.origin + url);
            toastr.success('链接已复制');
        });

        $(document).on('click', '.copy-url', function () {
            const input = $(this).closest('.input-group').find('input');
            copyToClipboard(input.val());
            toastr.success('链接已复制');
        });

        function copyToClipboard(text) {
            const temp = $('<input>');
            $('body').append(temp);
            temp.val(text).select();
            document.execCommand('copy');
            temp.remove();
        }

        // 删除
        $(document).on('click', '.delete-btn', function (e) {
            e.stopPropagation();
            const id = $(this).data('id');

            if (!confirm('确定要删除这个文件吗？')) return;

            $.ajax({
                url: '/admin/media/delete/' + id,
                type: 'POST',
                data: { csrf_token: $('input[name="csrf_token"]').val() },
                success: function (response) {
                    // 更新 CSRF 令牌
                    if (response.csrf_token && response.csrf_name) {
                        $('input[name="' + response.csrf_name + '"]').val(response.csrf_token);
                    }
                    
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('删除失败');
                }
            });
        });

        // 类型筛选
        $('#typeFilter').change(function () {
            const type = $(this).val();
            const url = new URL(window.location);
            if (type) {
                url.searchParams.set('type', type);
            } else {
                url.searchParams.delete('type');
            }
            url.searchParams.delete('page');
            window.location = url;
        });

        // 搜索
        $('#searchBtn').click(function () {
            const search = $('#searchInput').val();
            const url = new URL(window.location);
            if (search) {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location = url;
        });

        $('#searchInput').keypress(function (e) {
            if (e.which === 13) {
                $('#searchBtn').click();
            }
        });

        // 视图切换
        function moveOverlayActions() {
            $('.media-card').each(function() {
                const card = $(this);
                const overlayActions = card.find('.overlay-actions');
                
                if (card.closest('#mediaGrid').hasClass('list-view')) {
                    // 列表视图：将操作按钮移到media-card的直接子元素
                    if (overlayActions.parent('.card-overlay').length > 0) {
                        overlayActions.appendTo(card);
                    }
                } else {
                    // 网格视图：将操作按钮移回card-overlay
                    if (!overlayActions.parent('.card-overlay').length > 0) {
                        const cardOverlay = card.find('.card-overlay');
                        if (cardOverlay.length > 0) {
                            overlayActions.appendTo(cardOverlay);
                        }
                    }
                }
            });
        }

        $('#gridView').click(function () {
            $(this).addClass('active');
            $('#listView').removeClass('active');
            $('#mediaGrid').removeClass('list-view').addClass('grid-view');
            moveOverlayActions();
        });

        $('#listView').click(function () {
            $(this).addClass('active');
            $('#gridView').removeClass('active');
            $('#mediaGrid').removeClass('grid-view').addClass('list-view');
            moveOverlayActions();
        });

        // 页面加载时初始化
        moveOverlayActions();

        // 格式化文件大小
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    });
</script>

<?php
// 辅助函数：格式化文件大小
function formatFileSize($bytes)
{
    if ($bytes === 0)
        return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>

<?= view('admin/layouts/footer') ?>