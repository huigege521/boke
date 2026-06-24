<?php
$activePage = 'posts';
$pageTitle = '编辑文章';
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
    .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: none; justify-content: center; align-items: center; }
    .loading-overlay .spinner { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    .current-image { max-width: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px; }
    
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
            <span class="visually-hidden">加载中...</span>
        </div>
        <p class="mt-2" id="loadingText">正在加载...</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">编辑文章</h5>
        <a href="<?= base_url('admin/posts') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 返回列表
        </a>
    </div>
    <div class="card-body">
        <form id="postForm">
            <?= csrf_field() ?>
            <input type="hidden" id="post_id" value="<?= $postId ?? '' ?>">
            
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
                        <div id="current_image" class="mb-2"></div>
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

                    <div class="form-group">
                        <label>文章信息</label>
                        <ul class="list-unstyled text-muted small">
                            <li>浏览量: <span id="post_views">0</span></li>
                            <li>评论数: <span id="post_comments">0</span></li>
                            <li>创建时间: <span id="post_created">-</span></li>
                            <li>更新时间: <span id="post_updated">-</span></li>
                        </ul>
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
                <button type="button" class="btn btn-outline-danger" onclick="deletePost()">
                    <i class="fas fa-trash"></i> 删除
                </button>
                <a href="<?= base_url('admin/posts') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('js/ckeditor/ckeditor.js') ?>"></script>
<script>
let editor;
let postId = '<?= $postId ?? '' ?>';
let currentPost = null;

document.addEventListener('DOMContentLoaded', function() {
    // 配置 toastr 位置
    toastr.options = {
        positionClass: 'toast-top-center',
        timeOut: 3000
    };
    
    if (!postId) {
        toastr.error('文章ID无效');
        setTimeout(() => { window.location.href = '<?= base_url('admin/posts') ?>'; }, 1500);
        return;
    }
    initEditor();
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
        // 编辑器初始化完成后再加载数据
        loadPostData();
        initStatusToggle();
        initMediaLibraryButton(); // 初始化媒体库按钮
    }).catch(error => {
        console.error(error);
        // 编辑器初始化失败也尝试加载数据
        loadPostData();
        initMediaLibraryButton(); // 初始化媒体库按钮
    });
}

function loadPostData() {
    showLoadingOverlay('正在加载文章数据...');
    
    Promise.all([
        fetch('<?= base_url('api/posts/') ?>' + postId).then(r => r.json()),
        fetch('<?= base_url('api/categories') ?>').then(r => r.json()),
        fetch('<?= base_url('api/tags') ?>').then(r => r.json())
    ]).then(([postResult, catResult, tagResult]) => {
        hideLoadingOverlay();
        
        if (postResult.success) {
            currentPost = postResult.data;
            populatePostData(currentPost);
        } else {
            toastr.error(postResult.message || '加载文章失败');
            setTimeout(() => { window.location.href = '<?= base_url('admin/posts') ?>'; }, 1500);
            return;
        }
        
        if (catResult.success) {
            populateCategories(catResult.data.list, currentPost ? currentPost.category_id : null);
        }
        
        if (tagResult.success) {
            populateTags(tagResult.data.list, currentPost ? currentPost.tags : []);
        }
    }).catch(error => {
        hideLoadingOverlay();
        console.error('加载数据失败:', error);
        toastr.error('加载数据失败，请刷新页面重试');
    });
}

function populatePostData(post) {
    document.getElementById('title').value = post.title || '';
    document.getElementById('slug').value = post.slug || '';
    document.getElementById('excerpt').value = post.excerpt || '';
    document.getElementById('status').value = post.status || 'draft';
    document.getElementById('visibility').value = post.visibility || 'public';
    
    if (editor && post.content) {
        editor.setData(post.content);
    }
    
    if (post.status === 'scheduled' && post.scheduled_at) {
        document.getElementById('scheduled_at_container').style.display = 'block';
        document.getElementById('scheduled_at').value = post.scheduled_at.replace(' ', 'T').substring(0, 16);
    }
    
    if (post.featured_image) {
        document.getElementById('current_image').innerHTML = 
            '<img src="<?= base_url('uploads/') ?>' + post.featured_image + '" class="current-image"><br>' +
            '<small class="text-muted">当前特色图片</small>';
    }
    
    document.getElementById('post_views').textContent = post.views || 0;
    document.getElementById('post_comments').textContent = post.comments_count || 0;
    document.getElementById('post_created').textContent = post.created_at || '-';
    document.getElementById('post_updated').textContent = post.updated_at || '-';
}

function populateCategories(cats, selectedId) {
    const select = document.getElementById('category_id');
    select.innerHTML = '<option value="">选择分类</option>';
    cats.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        if (cat.id == selectedId) option.selected = true;
        select.appendChild(option);
    });
}

function populateTags(tagList, selectedTags) {
    const select = document.getElementById('tags');
    select.innerHTML = '';
    const selectedIds = selectedTags ? selectedTags.map(t => t.id) : [];
    tagList.forEach(tag => {
        const option = document.createElement('option');
        option.value = tag.id;
        option.textContent = tag.name;
        if (selectedIds.includes(tag.id)) option.selected = true;
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

document.getElementById('postForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitPost();
});

function saveDraft() {
    document.getElementById('status').value = 'draft';
    submitPost();
}

function submitPost() {
    clearErrors();
    showLoadingOverlay('正在保存文章...');
    
    const formData = new FormData();
    formData.append('_method', 'PUT'); // 模拟 PUT 请求
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
    
    fetch('<?= base_url('api/posts/') ?>' + postId, {
        method: 'POST', // 使用 POST 方式
        body: formData,
        credentials: 'include'
    })
    .then(response => response.json())
    .then(result => {
        hideLoadingOverlay();
        
        if (result.success) {
            toastr.success('文章更新成功');
            loadPostData();
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

function deletePost() {
    if (!confirm('确定要删除这篇文章吗？此操作不可恢复！')) {
        return;
    }
    
    showLoadingOverlay('正在删除文章...');
    
    fetch('<?= base_url('api/posts/') ?>' + postId, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(result => {
        hideLoadingOverlay();
        
        if (result.success) {
            toastr.success('文章删除成功');
            setTimeout(() => {
                window.location.href = '<?= base_url('admin/posts') ?>';
            }, 1000);
        } else {
            toastr.error(result.message || '删除失败');
        }
    })
    .catch(error => {
        hideLoadingOverlay();
        console.error('删除失败:', error);
        toastr.error('删除失败，请稍后重试');
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

function showLoadingOverlay(text) {
    document.getElementById('loadingText').textContent = text || '正在处理...';
    document.getElementById('loadingOverlay').style.display = 'block';
}

function hideLoadingOverlay() {
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

<?= view('admin/layouts/footer') ?>
