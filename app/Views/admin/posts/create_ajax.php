<?php
$activePage = 'posts';
$pageTitle = '创建文章';
$styles = '<style>
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
    .form-group input[type="text"],
    .form-group input[type="file"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .form-group textarea { resize: vertical; }
    .form-actions { margin-top: 1.5rem; }
    .form-actions button { margin-right: 10px; }
    .error-message { color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; }
    .ck-editor__editable { min-height: 300px; }
    .media-item { cursor: pointer; transition: all 0.2s ease; position: relative; }
    .media-item:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
    .media-item.selected { border: 2px solid #007bff; box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25); }
    .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: none; justify-content: center; align-items: center; }
    .loading-overlay .spinner { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    
    /* 媒体选择卡片样式 */
    .media-select-card { cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; }
    .media-select-card:hover { border-color: #007bff; box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2); transform: translateY(-2px); }
    .media-select-card img { object-fit: cover; }
</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">保存中...</span>
        </div>
        <p class="mt-2">正在保存，请稍候...</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">创建新文章</h5>
    </div>
    <div class="card-body">
        <form id="postForm">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="title">标题 <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required>
                        <div class="error-message" id="title-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="slug">别名</label>
                        <input type="text" id="slug" name="slug" class="form-control" placeholder="留空则自动生成">
                        <small class="form-text text-muted">用于URL显示，留空将根据标题自动生成</small>
                    </div>

                    <div class="form-group">
                        <label for="content">内容 <span class="text-danger">*</span></label>
                        <textarea id="content" name="content"></textarea>
                        <div class="error-message" id="content-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">摘要</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" rows="3" placeholder="留空将自动从内容截取"></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="category_id">分类 <span class="text-danger">*</span></label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">选择分类</option>
                        </select>
                        <div class="error-message" id="category_id-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="tags">标签</label>
                        <select id="tags" name="tags[]" multiple class="form-control">
                        </select>
                        <small class="form-text text-muted">按住Ctrl键可选择多个标签</small>
                    </div>

                    <div class="form-group">
                        <label for="featured_image">特色图片</label>
                        <div class="input-group">
                            <input type="file" id="featured_image" name="featured_image" class="form-control">
                            <button type="button" class="btn btn-outline-primary" id="selectFromMediaBtn">
                                <i class="fas fa-images"></i> 从媒体库选择
                            </button>
                        </div>
                        <div id="featured_image_preview" class="mt-2"></div>
                        <input type="hidden" id="featured_image_from_media_id" name="featured_image_from_media_id">
                    </div>

                    <div class="form-group">
                        <label for="status">状态</label>
                        <select id="status" name="status" class="form-control">
                            <option value="draft">草稿</option>
                            <option value="published">已发布</option>
                            <option value="pending">待审核</option>
                            <option value="scheduled">定时发布</option>
                        </select>
                    </div>

                    <div class="form-group" id="scheduled_at_container" style="display: none;">
                        <label for="scheduled_at">定时发布时间</label>
                        <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="visibility">可见性</label>
                        <select id="visibility" name="visibility" class="form-control">
                            <option value="public">公开</option>
                            <option value="private">私有</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> 保存文章
                </button>
                <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                    <i class="fas fa-file"></i> 保存草稿
                </button>
                <a href="<?= base_url('admin/posts') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('js/ckeditor/ckeditor.js') ?>"></script>
<script>
let editor;
let categories = [];
let tags = [];

document.addEventListener('DOMContentLoaded', function() {
    // 配置 toastr 位置
    toastr.options = {
        positionClass: 'toast-top-center',
        timeOut: 3000,
        preventDuplicates: true
    };
    
    initEditor();
    loadFormData();
    initStatusToggle();
    initImagePreview();
    initMediaLibraryButton(); // 初始化媒体库按钮
});

function initEditor() {
    ClassicEditor.create(document.querySelector('#content'), {
        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'imageUpload', 'insertTable', 'code', 'codeBlock', 'undo', 'redo'],
        language: 'zh-cn',
        ckfinder: {
            uploadUrl: '/admin/posts/upload',
            headers: { 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' }
        },
        image: {
            toolbar: ['imageTextAlternative', '|', 'imageStyle:alignLeft', 'imageStyle:full', 'imageStyle:alignRight'],
            styles: [
                { name: 'full', element: 'img', attributes: { class: 'img-fluid' } },
                { name: 'alignLeft', element: 'img', attributes: { class: 'float-start me-3' } },
                { name: 'alignRight', element: 'img', attributes: { class: 'float-end ms-3' } }
            ]
        }
    }).then(e => {
        editor = e;
    }).catch(error => {
        console.error(error);
    });
}

function loadFormData() {
    showLoading();
    
    Promise.all([
        fetch('<?= base_url('api/categories') ?>').then(r => r.json()),
        fetch('<?= base_url('api/tags') ?>').then(r => r.json())
    ]).then(([catResult, tagResult]) => {
        hideLoading();
        
        if (catResult.success) {
            categories = catResult.data.list;
            populateCategories(categories);
        }
        
        if (tagResult.success) {
            tags = tagResult.data.list;
            populateTags(tags);
        }
    }).catch(error => {
        hideLoading();
        console.error('加载表单数据失败:', error);
        toastr.error('加载表单数据失败，请刷新页面重试');
    });
}

function populateCategories(cats) {
    const select = document.getElementById('category_id');
    select.innerHTML = '<option value="">选择分类</option>';
    cats.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        select.appendChild(option);
    });
}

function populateTags(tagList) {
    const select = document.getElementById('tags');
    select.innerHTML = '';
    tagList.forEach(tag => {
        const option = document.createElement('option');
        option.value = tag.id;
        option.textContent = tag.name;
        select.appendChild(option);
    });
}

function initStatusToggle() {
    const statusSelect = document.getElementById('status');
    const scheduledContainer = document.getElementById('scheduled_at_container');
    
    statusSelect.addEventListener('change', function() {
        scheduledContainer.style.display = this.value === 'scheduled' ? 'block' : 'none';
    });
}

function initImagePreview() {
    const imageInput = document.getElementById('featured_image');
    const preview = document.getElementById('featured_image_preview');
    
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px;">';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

document.getElementById('postForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitPost('published');
});

function saveDraft() {
    document.getElementById('status').value = 'draft';
    submitPost('draft');
}

function submitPost(defaultStatus) {
    clearErrors();
    showLoadingOverlay();
    
    const formData = new FormData();
    formData.append('title', document.getElementById('title').value);
    formData.append('slug', document.getElementById('slug').value);
    formData.append('content', editor ? editor.getData() : document.getElementById('content').value);
    formData.append('excerpt', document.getElementById('excerpt').value);
    formData.append('category_id', document.getElementById('category_id').value);
    formData.append('status', document.getElementById('status').value);
    formData.append('visibility', document.getElementById('visibility').value);
    
    const selectedTags = Array.from(document.getElementById('tags').selectedOptions).map(opt => opt.value);
    formData.append('tags', JSON.stringify(selectedTags));
    
    // 添加媒体库选择的图片ID
    const featuredImageFromMediaIdElement = document.getElementById('featured_image_from_media_id');
    if (featuredImageFromMediaIdElement && featuredImageFromMediaIdElement.value) {
        formData.append('featured_image_from_media_id', featuredImageFromMediaIdElement.value);
    }
    
    const imageFile = document.getElementById('featured_image').files[0];
    if (imageFile) {
        formData.append('featured_image', imageFile);
    }
    
    const scheduledAt = document.getElementById('scheduled_at').value;
    if (scheduledAt) {
        formData.append('scheduled_at', scheduledAt);
    }
    
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch('<?= base_url('api/posts') ?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(result => {
        hideLoadingOverlay();
        
        if (result.success) {
            toastr.success('文章创建成功');
            setTimeout(() => {
                window.location.href = '<?= base_url('admin/posts') ?>';
            }, 1000);
        } else {
            if (result.message) {
                toastr.error(result.message);
            }
            if (result.errors) {
                showErrors(result.errors);
            }
        }
    })
    .catch(error => {
        hideLoadingOverlay();
        console.error('提交失败:', error);
        toastr.error('提交失败，请稍后重试');
    });
}

function showErrors(errors) {
    for (const [field, message] of Object.entries(errors)) {
        const errorEl = document.getElementById(field + '-error');
        if (errorEl) {
            errorEl.textContent = message;
        }
    }
}

function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.textContent = '';
    });
}

function showLoadingOverlay() {
    document.getElementById('loadingOverlay').style.display = 'block';
}

function hideLoadingOverlay() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

// 初始化媒体库选择按钮
function initMediaLibraryButton() {
    const btn = document.getElementById('selectFromMediaBtn');
    if (btn) {
        btn.addEventListener('click', openMediaLibrary);
    }
}

// 打开媒体库选择模态框
function openMediaLibrary() {
    // 创建或显示媒体库选择模态框
    if (!document.getElementById('mediaSelectModal')) {
        const modalHtml = `
            <div class="modal fade" id="mediaSelectModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">从媒体库选择图片</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row" id="mediaGrid"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    // 加载媒体库数据
    loadMediaForSelection();
    
    // 显示模态框
    const modal = new bootstrap.Modal(document.getElementById('mediaSelectModal'));
    modal.show();
}

// 加载媒体库图片供选择
function loadMediaForSelection(page = 1) {
    const grid = document.getElementById('mediaGrid');
    grid.innerHTML = '<div class="col-12 text-center"><i class="fas fa-spinner fa-spin"></i> 加载中...</div>';
    
    fetch(`<?= base_url('api/media') ?>?page=${page}&type=image`)
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                renderMediaSelectionGrid(result.data.list);
            } else {
                grid.innerHTML = '<div class="col-12 text-center text-danger">加载失败</div>';
            }
        })
        .catch(() => {
            grid.innerHTML = '<div class="col-12 text-center text-danger">网络错误</div>';
        });
}

// 渲染媒体选择网格
function renderMediaSelectionGrid(mediaList) {
    const grid = document.getElementById('mediaGrid');
    
    if (mediaList.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center">暂无图片，请先上传</div>';
        return;
    }
    
    grid.innerHTML = mediaList.map(m => `
        <div class="col-md-3 col-sm-4 col-6 mb-3">
            <div class="card media-select-card" onclick="selectMedia(${m.id}, '${m.url}', '${m.original_name}')">
                <img src="${m.url}" class="card-img-top" alt="${m.original_name}" style="height: 150px; object-fit: cover;">
                <div class="card-body p-2">
                    <small class="text-truncate d-block" title="${m.original_name}">${m.original_name}</small>
                </div>
            </div>
        </div>
    `).join('');
}

// 选择媒体图片
function selectMedia(id, url, name) {
    // 更新隐藏字段
    document.getElementById('featured_image_from_media_id').value = id;
    
    // 显示预览
    const preview = document.getElementById('featured_image_preview');
    preview.innerHTML = `
        <div class="alert alert-success">
            <img src="${url}" style="max-width: 200px; max-height: 150px;" class="img-thumbnail"><br>
            <small>已选择: ${name}</small>
            <button type="button" class="btn btn-sm btn-danger float-right" onclick="clearSelectedMedia()">清除</button>
        </div>
    `;
    
    // 关闭模态框
    const modal = bootstrap.Modal.getInstance(document.getElementById('mediaSelectModal'));
    modal.hide();
    
    toastr.success('已选择特色图片');
}

// 清除选择的媒体
function clearSelectedMedia() {
    document.getElementById('featured_image_from_media_id').value = '';
    document.getElementById('featured_image_preview').innerHTML = '';
}

</script>
