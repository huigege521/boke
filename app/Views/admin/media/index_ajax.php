<?php
$activePage = 'media';
$pageTitle = '媒体库';
$styles = '';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary"></div>
</div>

<!-- 工具栏 -->
<div class="card mb-4">
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
                    <option value="image">图片</option>
                    <option value="document">文档</option>
                    <option value="video">视频</option>
                </select>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" id="searchInput" placeholder="搜索文件名..." value="">
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

<!-- 媒体网格 -->
<div class="row g-4" id="mediaGrid"></div>

<!-- 分页 -->
<div id="pagination" class="mt-4"></div>

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
                        onclick="document.getElementById('fileInput').click()">选择文件</button>
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
    /* 加载遮罩 */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
    }

    /* 媒体卡片样式 */
    .media-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        height: 100%;
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
    }

    /* 选择覆盖层 */
    .select-overlay {
        position: absolute;
        top: 12px;
        left: 12px;
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 10;
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
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
    }

    .upload-area i {
        font-size: 48px;
        margin-bottom: 16px;
        color: #007bff;
    }

    /* 上传预览 */
    .upload-preview-item {
        display: inline-block;
        margin: 8px;
        position: relative;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .upload-preview-item img {
        width: 120px;
        height: 120px;
        object-fit: cover;
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
    }

    /* 列表视图样式 */
    #mediaGrid.list-view .media-item {
        width: 100%;
    }

    #mediaGrid.list-view .media-card {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #ffffff;
    }

    #mediaGrid.list-view .card-img-wrapper {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        margin-right: 16px;
        border-radius: 4px;
    }

    #mediaGrid.list-view .card-body {
        flex-grow: 1;
        padding: 0;
        min-width: 0;
    }

    #mediaGrid.list-view .card-overlay {
        position: static;
        opacity: 1;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin-left: auto;
        flex-shrink: 0;
        width: auto;
        height: auto;
        top: auto;
        left: auto;
        right: auto;
        bottom: auto;
    }

    #mediaGrid.list-view .media-card:hover .card-overlay {
        opacity: 1;
    }

    #mediaGrid.list-view .overlay-actions {
        background: transparent;
        padding: 0;
        gap: 6px;
    }

    #mediaGrid.list-view .overlay-actions .btn {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }

    .empty-state {
        text-align: center;
        padding: 40px 0;
    }

    .empty-state i {
        font-size: 48px;
        color: #6c757d;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        color: #6c757d;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #adb5bd;
    }
</style>

<script>
    let currentPage = 1;
    let totalPages = 1;
    let selectedIds = [];
    let selectedFiles = [];
    let currentRequest = null;
    let isLoading = false;

    document.addEventListener('DOMContentLoaded', function () {
        loadMedia();
        initEventListeners();
    });

    function initEventListeners() {
        // 类型筛选
        document.getElementById('typeFilter').addEventListener('change', function () {
            loadMedia(1);
        });

        // 搜索
        document.getElementById('searchBtn').addEventListener('click', function () {
            loadMedia(1);
        });

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.which === 13) {
                loadMedia(1);
            }
        });

        // 视图切换
        document.getElementById('gridView').addEventListener('click', function () {
            this.classList.add('active');
            document.getElementById('listView').classList.remove('active');
            document.getElementById('mediaGrid').classList.remove('list-view');
        });

        document.getElementById('listView').addEventListener('click', function () {
            this.classList.add('active');
            document.getElementById('gridView').classList.remove('active');
            document.getElementById('mediaGrid').classList.add('list-view');
        });

        // 拖拽上传
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function () {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', function (e) {
            handleFiles(e.target.files);
        });

        // 开始上传
        document.getElementById('startUpload').addEventListener('click', startUpload);

        // 批量删除
        document.getElementById('batchDeleteBtn').addEventListener('click', batchDelete);

        // 编辑表单提交
        document.getElementById('editForm').addEventListener('submit', function (e) {
            e.preventDefault();
            saveEdit();
        });
    }

    function loadMedia(page = 1) {
        if (isLoading) {
            if (currentRequest) {
                currentRequest.abort();
            }
        }

        showLoading();
        currentPage = page;

        const type = document.getElementById('typeFilter').value;
        const search = document.getElementById('searchInput').value;

        let url = `<?= base_url('api/media') ?>?page=${page}`;
        if (type) url += `&type=${type}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        currentRequest = new XMLHttpRequest();
        currentRequest.open('GET', url, true);
        currentRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        currentRequest.onload = function () {
            isLoading = false;
            currentRequest = null;

            if (this.status === 0) return;

            if (this.status >= 200 && this.status < 400) {
                try {
                    const result = JSON.parse(this.responseText);
                    hideLoading();

                    if (result.success) {
                        currentPage = result.data.current_page;
                        totalPages = result.data.total_pages;
                        renderMediaGrid(result.data.list);
                        renderPagination();
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

        currentRequest.onerror = function () {
            isLoading = false;
            currentRequest = null;
            if (this.status !== 0) {
                hideLoading();
                toastr.error('加载数据失败');
            }
        };

        isLoading = true;
        currentRequest.send();
    }

    function renderMediaGrid(mediaList) {
        const grid = document.getElementById('mediaGrid');
        selectedIds = [];

        if (mediaList.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>暂无媒体文件</h3>
                        <p>点击"上传文件"按钮开始上传</p>
                    </div>
                </div>
            `;
            updateBatchDeleteButton();
            return;
        }

        grid.innerHTML = mediaList.map(m => {
            const isSelected = selectedIds.includes(m.id.toString());
            return `
                <div class="col-lg-2 col-md-3 col-sm-4 col-6 media-item" data-id="${m.id}">
                    <div class="card media-card ${isSelected ? 'selected' : ''}">
                        <div class="card-img-wrapper">
                            ${m.is_image ?
                    `<img src="${m.url}" class="card-img-top" alt="${m.alt_text || m.filename}">` :
                    `<div class="file-icon"><i class="fas fa-${m.type === 'document' ? 'file-alt' : 'video'} fa-4x"></i></div>`
                }
                            <div class="select-overlay">
                                <input type="checkbox" class="media-select" value="${m.id}" ${isSelected ? 'checked' : ''}>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <p class="card-text text-truncate" title="${m.original_name}">${m.original_name}</p>
                            <small class="text-muted d-block">
                                <span>${m.size_formatted}</span>
                                <span class="mx-1">·</span>
                                <span>${formatDate(m.created_at)}</span>
                            </small>
                            ${m.used_by_posts > 0 ? `
                                <small class="text-info d-block mt-1">
                                    <i class="fas fa-link"></i> 被 ${m.used_by_posts} 篇文章使用
                                </small>
                            ` : ''}
                        </div>
                        <div class="card-overlay">
                            <div class="overlay-actions">
                                <button class="btn btn-sm btn-light view-btn" data-id="${m.id}" title="查看">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-light edit-btn" data-id="${m.id}" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-light copy-btn" data-url="${m.url}" title="复制链接">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${m.id}" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // 添加事件监听
        document.querySelectorAll('.media-select').forEach(el => {
            el.addEventListener('change', handleMediaSelect);
        });

        document.querySelectorAll('.view-btn').forEach(el => {
            el.addEventListener('click', function () {
                viewMedia(this.dataset.id);
            });
        });

        document.querySelectorAll('.edit-btn').forEach(el => {
            el.addEventListener('click', function () {
                editMedia(this.dataset.id);
            });
        });

        document.querySelectorAll('.copy-btn').forEach(el => {
            el.addEventListener('click', function () {
                copyToClipboard(window.location.origin + this.dataset.url);
                toastr.success('链接已复制');
            });
        });

        document.querySelectorAll('.delete-btn').forEach(el => {
            el.addEventListener('click', function () {
                deleteMedia(this.dataset.id);
            });
        });

        updateBatchDeleteButton();
    }

    function handleMediaSelect(e) {
        const id = e.target.value;
        const card = e.target.closest('.media-card');

        if (e.target.checked) {
            if (!selectedIds.includes(id)) {
                selectedIds.push(id);
            }
            card.classList.add('selected');
        } else {
            selectedIds = selectedIds.filter(item => item !== id);
            card.classList.remove('selected');
        }
        updateBatchDeleteButton();
    }

    function updateBatchDeleteButton() {
        const btn = document.getElementById('batchDeleteBtn');
        if (selectedIds.length > 0) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-trash mr-2"></i> 批量删除 (${selectedIds.length})`;
        } else {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-trash mr-2"></i> 批量删除';
        }
    }

    function renderPagination() {
        const pagination = document.getElementById('pagination');
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination justify-content-center">';

        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button class="page-link" onclick="loadMedia(${currentPage - 1})">上一页</button>
        </li>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                <button class="page-link" onclick="loadMedia(${i})">${i}</button>
            </li>`;
        }

        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <button class="page-link" onclick="loadMedia(${currentPage + 1})">下一页</button>
        </li>`;

        pagination.innerHTML = html + '</ul></nav>';
    }

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

    function startUpload() {
        if (selectedFiles.length === 0) return;

        const formData = new FormData();
        selectedFiles.forEach(file => {
            formData.append('files[]', file);
        });

        showLoading();

        fetch('<?= base_url('api/media/upload') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success(`成功上传 ${result.data.count} 个文件`);
                $('#uploadModal').modal('hide');
                selectedFiles = [];
                loadMedia(1);
            } else {
                toastr.error(result.message || '上传失败');
            }
        }).catch(() => {
            hideLoading();
            toastr.error('上传失败');
        });
    }

    function viewMedia(id) {
        fetch(`<?= base_url('api/media') ?>/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(result => {
            if (result.success) {
                const media = result.data;
                let preview = '';

                if (media.is_image) {
                    preview = `<img src="${media.url}" class="img-fluid" alt="${media.alt_text || ''}">`;
                } else if (media.type === 'video') {
                    preview = `<video src="${media.url}" controls class="img-fluid"></video>`;
                } else {
                    preview = '<i class="fas fa-file-alt fa-5x text-muted"></i>';
                }
                document.getElementById('detailPreview').innerHTML = preview;

                document.getElementById('detailInfo').innerHTML = `
                    <p><strong>文件名：</strong>${media.original_name}</p>
                    <p><strong>类型：</strong>${media.type}</p>
                    <p><strong>大小：</strong>${media.size_formatted}</p>
                    <p><strong>尺寸：</strong>${media.width} x ${media.height}</p>
                    <p><strong>上传者：</strong>${media.uploader_name}</p>
                    <p><strong>上传时间：</strong>${media.created_at}</p>
                    <hr>
                    <p><strong>文件 URL：</strong></p>
                    <div class="input-group">
                        <input type="text" class="form-control" value="${window.location.origin}${media.url}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary copy-url" type="button">复制</button>
                        </div>
                    </div>
                `;

                document.querySelector('.copy-url')?.addEventListener('click', function () {
                    const input = this.closest('.input-group').querySelector('input');
                    copyToClipboard(input.value);
                    toastr.success('链接已复制');
                });

                $('#detailModal').modal('show');
            }
        });
    }

    function editMedia(id) {
        fetch(`<?= base_url('api/media') ?>/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(result => {
            if (result.success) {
                const media = result.data;
                document.getElementById('editId').value = media.id;
                document.getElementById('editTitle').value = media.title || '';
                document.getElementById('editAltText').value = media.alt_text || '';
                document.getElementById('editDescription').value = media.description || '';
                $('#editModal').modal('show');
            }
        });
    }

    function saveEdit() {
        const id = document.getElementById('editId').value;
        const formData = new FormData(document.getElementById('editForm'));

        // 关键：添加 _method=PUT 字段，让CI4识别为PUT请求
        formData.append('_method', 'PUT');

        fetch(`<?= base_url('api/media') ?>/${id}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            if (result.success) {
                toastr.success(result.message);
                $('#editModal').modal('hide');
                loadMedia(currentPage);
            } else {
                toastr.error(result.message || '保存失败');
            }
        }).catch(() => {
            toastr.error('保存失败');
        });
    }

    function deleteMedia(id) {
        if (!confirm('确定要删除这个文件吗？')) return;

        showLoading();

        fetch(`<?= base_url('api/media') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success('删除成功');
                loadMedia(currentPage);
            } else {
                toastr.error(result.message || '删除失败');
            }
        }).catch(() => {
            hideLoading();
            toastr.error('删除失败');
        });
    }

    function batchDelete() {
        if (selectedIds.length === 0) {
            toastr.warning('请先选择要删除的文件');
            return;
        }

        if (!confirm(`确定要删除选中的 ${selectedIds.length} 个文件吗？此操作不可恢复！`)) {
            return;
        }

        showLoading();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        fetch('<?= base_url('api/media/batch-delete') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ ids: selectedIds }),
            credentials: 'include'
        }).then(r => r.json()).then(result => {
            hideLoading();
            if (result.success) {
                toastr.success(result.message);
                selectedIds = [];
                updateBatchDeleteButton();
                loadMedia(1);
            } else {
                toastr.error(result.message || '批量删除失败');
            }
        }).catch(() => {
            hideLoading();
            toastr.error('批量删除失败');
        });
    }

    function copyToClipboard(text) {
        const temp = document.createElement('input');
        document.body.appendChild(temp);
        temp.value = text;
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('zh-CN');
    }

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
</script>

<?= view('admin/layouts/footer') ?>