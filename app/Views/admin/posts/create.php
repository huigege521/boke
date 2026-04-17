<?php
$activePage = 'posts';
$pageTitle = '创建文章';
$styles = '<style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group textarea {
            resize: vertical;
        }

        .form-actions {
            margin-top: 1.5rem;
        }

        .form-actions button {
            margin-right: 10px;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .ck-editor__editable {
            min-height: 300px;
        }

        /* 媒体库样式 */
        .media-item {
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .media-item.selected {
            border: 2px solid #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .media-item .card-img-wrapper {
            position: relative;
        }

        .media-item .select-overlay {
            z-index: 10;
        }

        .media-item .select-overlay input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>';

$scripts = '';

?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles', 'scripts')) ?>

<!-- 创建文章表单 -->
<div class="card">
    <div class="card-header">
        创建新文章
    </div>
    <div class="card-body">
        <form action="/admin/posts/store" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title">标题</label>
                <input type="text" id="title" name="title" value="<?= old('title') ?>">
                <?php if (isset($errors['title'])): ?>
                    <div class="error-message"><?= $errors['title'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="slug">别名</label>
                <input type="text" id="slug" name="slug" value="<?= old('slug') ?>">
                <?php if (isset($errors['slug'])): ?>
                    <div class="error-message"><?= $errors['slug'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="category_id">分类</label>
                <select id="category_id" name="category_id">
                    <option value="">选择分类</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= old('category_id') == $category['id'] ? 'selected' : '' ?>>
                            <?= str_repeat('-', $category['level'] ?? 0) ?>     <?= $category['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <div class="error-message"><?= $errors['category_id'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="tags">标签</label>
                <select id="tags" name="tags[]" multiple class="form-control">
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?= $tag['id'] ?>" <?= in_array($tag['id'], old('tags') ?? []) ? 'selected' : '' ?>>
                            <?= $tag['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">按住Ctrl键可选择多个标签</small>
            </div>

            <div class="form-group">
                <label for="featured_image">特色图片</label>
                <div class="mb-2">
                    <input type="file" id="featured_image" name="featured_image" class="form-control">
                </div>
                <button type="button" class="btn btn-outline-secondary" id="selectFromMedia">
                    <i class="fas fa-images"></i> 从媒体库选择
                </button>
                <?php if (isset($errors['featured_image'])): ?>
                    <div class="error-message"><?= $errors['featured_image'] ?></div>
                <?php endif; ?>
                <input type="hidden" id="featured_image_from_media" name="featured_image_from_media">
            </div>

            <div class="form-group">
                <label for="content">内容</label>
                <textarea id="content" name="content"><?= old('content') ?></textarea>
                <?php if (isset($errors['content'])): ?>
                    <div class="error-message"><?= $errors['content'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="excerpt">摘要</label>
                <textarea id="excerpt" name="excerpt" rows="5"><?= old('excerpt') ?></textarea>
                <?php if (isset($errors['excerpt'])): ?>
                    <div class="error-message"><?= $errors['excerpt'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="status">状态</label>
                <select id="status" name="status">
                    <option value="draft" <?= old('status') == 'draft' ? 'selected' : '' ?>>草稿</option>
                    <option value="published" <?= old('status') == 'published' ? 'selected' : '' ?>>已发布</option>
                    <option value="pending" <?= old('status') == 'pending' ? 'selected' : '' ?>>待审核</option>
                    <option value="scheduled" <?= old('status') == 'scheduled' ? 'selected' : '' ?>>定时发布</option>
                </select>
            </div>

            <div class="form-group" id="scheduled_at_container"
                style="display: <?= old('status') == 'scheduled' ? 'block' : 'none' ?>">
                <label for="scheduled_at">定时发布时间</label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                    value="<?= old('scheduled_at') ? date('Y-m-d\TH:i', strtotime(old('scheduled_at'))) : '' ?>">
            </div>

            <div class="form-group">
                <label for="visibility">可见性</label>
                <select id="visibility" name="visibility">
                    <option value="public" <?= old('visibility') == 'public' ? 'selected' : '' ?>>公开</option>
                    <option value="private" <?= old('visibility') == 'private' ? 'selected' : '' ?>>私有</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="/admin/posts" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    // 全局错误处理，捕获 Selection.getRangeAt 错误
    window.addEventListener('error', function (e) {
        if (e.error instanceof IndexSizeError && e.error.message.includes('getRangeAt')) {
            console.log('Caught IndexSizeError for getRangeAt:', e.error);
            e.preventDefault();
            return false;
        }
    });

    // 完全重写 Selection 对象，防止任何 getRangeAt 错误
    (function () {
        // 保存原始方法
        const originalGetSelection = window.getSelection;

        // 重写 window.getSelection
        window.getSelection = function () {
            try {
                const sel = originalGetSelection.call(window);
                if (sel) {
                    // 先保存原始的 getRangeAt 方法
                    const originalGetRangeAt = sel.getRangeAt.bind(sel);

                    // 重写 getRangeAt 方法
                    sel.getRangeAt = function (index) {
                        try {
                            if (index < 0 || index >= this.rangeCount) {
                                // 返回一个空的 Range 对象
                                return {
                                    setStart: function () { },
                                    setEnd: function () { },
                                    cloneRange: function () { return this; },
                                    deleteContents: function () { },
                                    extractContents: function () { return document.createDocumentFragment(); },
                                    insertNode: function () { },
                                    surroundContents: function () { }
                                };
                            }
                            // 调用原始方法
                            return originalGetRangeAt(index);
                        } catch (e) {
                            console.log('Selection.getRangeAt error caught:', e);
                            // 返回空 Range
                            return {
                                setStart: function () { },
                                setEnd: function () { },
                                cloneRange: function () { return this; },
                                deleteContents: function () { },
                                extractContents: function () { return document.createDocumentFragment(); },
                                insertNode: function () { },
                                surroundContents: function () { }
                            };
                        }
                    };
                }
                return sel;
            } catch (e) {
                console.log('window.getSelection error caught:', e);
                // 返回一个完整的模拟 Selection 对象
                return {
                    rangeCount: 0,
                    getRangeAt: function () {
                        return {
                            setStart: function () { },
                            setEnd: function () { },
                            cloneRange: function () { return this; },
                            deleteContents: function () { },
                            extractContents: function () { return document.createDocumentFragment(); },
                            insertNode: function () { },
                            surroundContents: function () { }
                        };
                    },
                    toString: function () { return ''; },
                    removeAllRanges: function () { },
                    addRange: function () { },
                    collapse: function () { },
                    selectAllChildren: function () { },
                    anchorNode: null,
                    anchorOffset: 0,
                    focusNode: null,
                    focusOffset: 0,
                    isCollapsed: true,
                    type: 'None'
                };
            }
        };
    })();
</script>
<script src="<?= base_url('js/ckeditor/ckeditor.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 状态切换逻辑
        const statusSelect = document.getElementById('status');
        const scheduledAtContainer = document.getElementById('scheduled_at_container');

        statusSelect.addEventListener('change', function () {
            if (this.value === 'scheduled') {
                scheduledAtContainer.style.display = 'block';
            } else {
                scheduledAtContainer.style.display = 'none';
            }
        });

        // 确保CSRF令牌正确传递
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        // 自动保存功能（针对新文章，暂时只在控制台显示）
        let autoSaveTimer = null;
        let lastSavedContent = document.getElementById('content').value;

        function showAutoSaveMessage(message, isSuccess = true) {
            // 创建提示元素
            const autoSaveStatus = document.createElement('div');
            autoSaveStatus.className = 'auto-save-status';
            autoSaveStatus.style.position = 'fixed';
            autoSaveStatus.style.bottom = '20px';
            autoSaveStatus.style.right = '20px';
            autoSaveStatus.style.padding = '10px 15px';
            autoSaveStatus.style.borderRadius = '4px';
            autoSaveStatus.style.fontSize = '14px';
            autoSaveStatus.style.zIndex = '1000';
            autoSaveStatus.style.backgroundColor = isSuccess ? '#d4edda' : '#f8d7da';
            autoSaveStatus.style.color = isSuccess ? '#155724' : '#721c24';
            autoSaveStatus.style.border = `1px solid ${isSuccess ? '#c3e6cb' : '#f5c6cb'}`;
            autoSaveStatus.style.display = 'block';
            autoSaveStatus.textContent = message;

            document.body.appendChild(autoSaveStatus);

            // 3秒后隐藏
            setTimeout(() => {
                autoSaveStatus.style.display = 'none';
                setTimeout(() => {
                    if (autoSaveStatus.parentNode) {
                        autoSaveStatus.parentNode.removeChild(autoSaveStatus);
                    }
                }, 1000);
            }, 3000);
        }

        ClassicEditor
            .create(document.querySelector("#content"), {
                toolbar: ["heading", "|", "bold", "italic", "link", "bulletedList", "numberedList", "blockQuote", "imageUpload", "insertTable", "code", "codeBlock", "undo", "redo"],
                language: "zh-cn",
                ckfinder: {
                    uploadUrl: "/admin/posts/upload",
                    headers: {
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                },
                image: {
                    toolbar: [
                        'imageTextAlternative', '|', 'imageStyle:alignLeft', 'imageStyle:full', 'imageStyle:alignRight'
                    ],
                    styles: [
                        {
                            name: 'full',
                            element: 'img',
                            attributes: {
                                class: 'img-fluid'
                            }
                        },
                        {
                            name: 'alignLeft',
                            element: 'img',
                            attributes: {
                                class: 'float-start me-3'
                            }
                        },
                        {
                            name: 'alignRight',
                            element: 'img',
                            attributes: {
                                class: 'float-end ms-3'
                            }
                        }
                    ]
                },
                codeBlock: {
                    languages: [
                        { language: 'plaintext', label: 'Plain text' },
                        { language: 'php', label: 'PHP' },
                        { language: 'javascript', label: 'JavaScript' },
                        { language: 'css', label: 'CSS' },
                        { language: 'html', label: 'HTML' },
                        { language: 'sql', label: 'SQL' }
                    ]
                }
            })
            .then(editor => {
                // 监听 CKEditor 内容变化
                editor.model.document.on('change:data', function () {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(() => {
                        const content = editor.getData();
                        if (content !== lastSavedContent) {
                            lastSavedContent = content;
                            console.log('文章内容已更改，将在保存后自动保存');
                            showAutoSaveMessage('文章内容已更改，保存后将启用自动保存功能');
                            // 注意：新文章在未保存前没有ID，无法进行自动保存
                            // 保存后会跳转到编辑页面，那里有完整的自动保存功能
                        }
                    }, 3000); // 3秒后检查
                });
            })
            .catch(error => {
                console.error(error);
            });
    });
</script>

<!-- 媒体库选择模态框 -->
<div class="modal fade" id="mediaModal" tabindex="-1" role="dialog" aria-labelledby="mediaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaModalLabel">从媒体库选择</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="mediaSearch" placeholder="搜索文件...">
                            <button class="btn btn-outline-secondary" type="button" id="mediaSearchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="mediaTypeFilter">
                            <option value="">所有类型</option>
                            <option value="image">图片</option>
                            <option value="document">文档</option>
                            <option value="video">视频</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary btn-block" id="refreshMedia">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                <div id="mediaLibrary" class="row g-3">
                    <!-- 媒体文件将通过AJAX加载 -->
                    <div class="col-12 text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">加载中...</span>
                        </div>
                        <p class="mt-2">正在加载媒体库...</p>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-between">
                    <div class="text-muted">
                        已选择: <span id="selectedCount">0</span> 个文件
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" id="confirmMediaSelection">确认选择</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 媒体库选择功能
    document.addEventListener('DOMContentLoaded', function () {
        const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
        const selectFromMediaBtn = document.getElementById('selectFromMedia');
        const mediaLibrary = document.getElementById('mediaLibrary');
        const mediaSearch = document.getElementById('mediaSearch');
        const mediaSearchBtn = document.getElementById('mediaSearchBtn');
        const mediaTypeFilter = document.getElementById('mediaTypeFilter');
        const refreshMediaBtn = document.getElementById('refreshMedia');
        const confirmMediaSelectionBtn = document.getElementById('confirmMediaSelection');
        const selectedCount = document.getElementById('selectedCount');
        const featuredImageFromMedia = document.getElementById('featured_image_from_media');

        let selectedMedia = [];

        // 打开媒体库模态框
        selectFromMediaBtn.addEventListener('click', function () {
            loadMediaLibrary();
            mediaModal.show();
        });

        // 加载媒体库
        function loadMediaLibrary() {
            const search = mediaSearch.value;
            const type = mediaTypeFilter.value;

            mediaLibrary.innerHTML = `
                <div class="col-12 text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">加载中...</span>
                    </div>
                    <p class="mt-2">正在加载媒体库...</p>
                </div>
            `;

            fetch(`/admin/media/load?search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderMediaLibrary(data.media);
                    } else {
                        mediaLibrary.innerHTML = `
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-exclamation-circle fa-3x text-danger mb-2"></i>
                                <p>${data.message || '加载媒体库失败'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading media library:', error);
                    mediaLibrary.innerHTML = `
                        <div class="col-12 text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-danger mb-2"></i>
                            <p>加载媒体库失败</p>
                        </div>
                    `;
                });
        }

        // 渲染媒体库
        function renderMediaLibrary(media) {
            if (media.length === 0) {
                mediaLibrary.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-2"></i>
                        <p class="text-muted">暂无媒体文件</p>
                        <a href="/admin/media" class="btn btn-primary mt-2" target="_blank">
                            <i class="fas fa-upload"></i> 上传文件
                        </a>
                    </div>
                `;
                return;
            }

            mediaLibrary.innerHTML = media.map(item => {
                const isImage = item.is_image;
                const fileUrl = item.file_url;
                const isSelected = selectedMedia.includes(item.id);

                return `
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                        <div class="card media-item ${isSelected ? 'selected' : ''}" data-id="${item.id}" data-url="${fileUrl}">
                            <div class="card-img-wrapper" style="height: 120px; overflow: hidden;">
                                ${Boolean(isImage) ?
                        `<img src="${fileUrl}" class="card-img-top" alt="${item.alt_text || item.filename}" style="width: 100%; height: 100%; object-fit: cover;">` :
                        `<div class="file-icon" style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f8f9fa;">
                                        <i class="fas fa-${item.file_type === 'document' ? 'file-alt' : (item.file_type === 'video' ? 'video' : 'file')} fa-3x text-muted"></i>
                                    </div>`
                    }
                                <div class="select-overlay" style="position: absolute; top: 8px; right: 8px;">
                                    <input type="checkbox" class="media-select" value="${item.id}" ${isSelected ? 'checked' : ''}>
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <p class="card-text text-truncate" style="font-size: 12px; margin: 0;" title="${item.original_name}">
                                    ${item.original_name}
                                </p>
                                <small class="text-muted" style="font-size: 10px;">
                                    ${(item.file_size / 1024).toFixed(1)} KB
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // 添加选择事件
            document.querySelectorAll('.media-select').forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const mediaId = parseInt(this.value);
                    if (this.checked) {
                        if (!selectedMedia.includes(mediaId)) {
                            selectedMedia.push(mediaId);
                        }
                    } else {
                        selectedMedia = selectedMedia.filter(id => id !== mediaId);
                    }
                    selectedCount.textContent = selectedMedia.length;

                    // 更新卡片选中状态
                    const card = this.closest('.media-item');
                    if (this.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });
            });

            // 添加卡片点击事件
            document.querySelectorAll('.media-item').forEach(card => {
                card.addEventListener('click', function (e) {
                    if (!e.target.closest('.media-select')) {
                        const checkbox = this.querySelector('.media-select');
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
            });
        }

        // 搜索和筛选
        mediaSearchBtn.addEventListener('click', loadMediaLibrary);
        mediaSearch.addEventListener('keypress', function (e) {
            if (e.which === 13) {
                loadMediaLibrary();
            }
        });
        mediaTypeFilter.addEventListener('change', loadMediaLibrary);
        refreshMediaBtn.addEventListener('click', loadMediaLibrary);

        // 确认选择
        confirmMediaSelectionBtn.addEventListener('click', function () {
            if (selectedMedia.length > 0) {
                // 对于特色图片，只使用第一个选择的文件
                const firstSelected = document.querySelector('.media-item.selected');
                if (firstSelected) {
                    const fileUrl = firstSelected.dataset.url;
                    // 提取完整的路径部分（从/uploads/开始）
                    const path = fileUrl.replace(/^.*\/uploads\//, '');
                    featuredImageFromMedia.value = path;

                    // 显示选择的文件
                    const featuredImageGroup = document.querySelector('#featured_image').closest('.form-group');
                    let currentImageDiv = featuredImageGroup.querySelector('.current-image');

                    // 获取文件扩展名
                    const fileExtension = fileUrl.split('.').pop().toLowerCase();
                    let displayContent = '';

                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
                        // 显示图片
                        displayContent = `<img src="${fileUrl}" alt="Featured Image" style="max-width: 200px; height: auto;">`;
                    } else if (fileExtension === 'pdf') {
                        // 显示 PDF 链接
                        displayContent = `<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary">查看 PDF 文件</a>`;
                    } else {
                        // 显示其他文件链接
                        displayContent = `<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-secondary">下载文件</a>`;
                    }

                    if (currentImageDiv) {
                        currentImageDiv.innerHTML = `
                            <p>当前文件:</p>
                            ${displayContent}
                            <input type="checkbox" name="remove_image" id="remove_image">
                            <label for="remove_image">删除当前文件</label>
                        `;
                    } else {
                        // 如果没有当前图片，添加一个
                        const newImageDiv = document.createElement('div');
                        newImageDiv.className = 'current-image mt-2';
                        newImageDiv.innerHTML = `
                            <p>当前文件:</p>
                            ${displayContent}
                            <input type="checkbox" name="remove_image" id="remove_image">
                            <label for="remove_image">删除当前文件</label>
                        `;
                        featuredImageGroup.appendChild(newImageDiv);
                    }

                    mediaModal.hide();
                    selectedMedia = [];
                    selectedCount.textContent = '0';
                }
            }
        });

        // 模态框关闭时重置选择
        document.getElementById('mediaModal').addEventListener('hidden.bs.modal', function () {
            selectedMedia = [];
            selectedCount.textContent = '0';
        });
    });
</script>

<?= view('admin/layouts/footer') ?>