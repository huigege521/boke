<?php
$activePage = 'categories';
$pageTitle = '编辑分类';
$styles = '<style>
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
    .form-group input[type="text"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .form-actions { margin-top: 1.5rem; }
    .error-message { color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; }
    .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: none; justify-content: center; align-items: center; }
</style>';
?>

<?= view('admin/layouts/header', compact('title', 'activePage', 'pageTitle', 'styles')) ?>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">加载中...</span>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">编辑分类</h5>
        <a href="<?= base_url('admin/categories') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 返回列表
        </a>
    </div>
    <div class="card-body">
        <form id="categoryForm">
            <?= csrf_field() ?>
            <input type="hidden" id="category_id" value="<?= $categoryId ?? '' ?>">

            <div class="form-group">
                <label for="name">分类名称 <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required>
                <div class="error-message" id="name-error"></div>
            </div>

            <div class="form-group">
                <label for="slug">别名</label>
                <input type="text" id="slug" name="slug" class="form-control" placeholder="留空则自动生成">
            </div>

            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="parent_id">父分类</label>
                <select id="parent_id" name="parent_id" class="form-control">
                    <option value="">无</option>
                </select>
                <small class="form-text text-muted">注意：不能选择自己或自己的子分类作为父分类</small>
            </div>

            <div class="form-group">
                <label for="order">排序</label>
                <input type="number" id="order" name="order" class="form-control" value="0">
                <small class="form-text text-muted">数字越小越靠前</small>
            </div>

            <div class="form-group">
                <label for="icon">图标</label>
                <input type="text" id="icon" name="icon" class="form-control" placeholder="例如：fas fa-folder">
                <small class="form-text text-muted">请输入 Font Awesome 图标类名</small>
            </div>

            <div class="form-group">
                <label>分类信息</label>
                <ul class="list-unstyled text-muted small">
                    <li>文章数: <span id="posts_count">0</span></li>
                    <li>创建时间: <span id="created_at">-</span></li>
                    <li>更新时间: <span id="updated_at">-</span></li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 保存
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="deleteCategory()">
                    <i class="fas fa-trash"></i> 删除
                </button>
                <a href="<?= base_url('admin/categories') ?>" class="btn btn-outline-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
    let categoryId = '<?= $categoryId ?? '' ?>';
    let currentCategory = null;

    document.addEventListener('DOMContentLoaded', function () {
        if (!categoryId) {
            toastr.error('分类ID无效');
            setTimeout(() => { window.location.href = '<?= base_url('admin/categories') ?>'; }, 1500);
            return;
        }
        loadCategoryData();
    });

    function loadCategoryData() {
        showLoading();

        Promise.all([
            fetch('<?= base_url('api/categories/') ?>' + categoryId).then(r => r.json()),
            fetch('<?= base_url('api/categories') ?>').then(r => r.json())
        ]).then(([catResult, listResult]) => {
            hideLoading();

            if (catResult.success) {
                currentCategory = catResult.data;
                populateCategoryData(currentCategory);
            } else {
                toastr.error(catResult.message || '加载分类失败');
                setTimeout(() => { window.location.href = '<?= base_url('admin/categories') ?>'; }, 1500);
                return;
            }

            if (listResult.success) {
                populateParentCategories(listResult.data.list, currentCategory.id);
            }
        }).catch(error => {
            hideLoading();
            console.error('加载数据失败:', error);
            toastr.error('加载数据失败，请刷新页面重试');
        });
    }

    function populateCategoryData(cat) {
        document.getElementById('name').value = cat.name || '';
        document.getElementById('slug').value = cat.slug || '';
        document.getElementById('description').value = cat.description || '';
        document.getElementById('order').value = cat.order || 0;
        document.getElementById('icon').value = cat.icon || '';
        document.getElementById('posts_count').textContent = cat.posts_count || 0;
        document.getElementById('created_at').textContent = cat.created_at || '-';
        document.getElementById('updated_at').textContent = cat.updated_at || '-';

        if (cat.parent_id) {
            document.getElementById('parent_id').value = cat.parent_id;
        }
    }

    function populateParentCategories(categories, excludeId) {
        const select = document.getElementById('parent_id');
        select.innerHTML = '<option value="">无</option>';

        function addOptions(cats, level = 0) {
            cats.forEach(cat => {
                if (cat.id == excludeId) return;

                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = '—'.repeat(level) + ' ' + cat.name;
                if (currentCategory && currentCategory.parent_id == cat.id) {
                    option.selected = true;
                }
                select.appendChild(option);

                if (cat.children && cat.children.length > 0) {
                    addOptions(cat.children, level + 1);
                }
            });
        }

        addOptions(categories);
    }

    document.getElementById('categoryForm').addEventListener('submit', function (e) {
        e.preventDefault();
        submitCategory();
    });

    function submitCategory() {
        clearErrors();
        showLoading();

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', document.getElementById('name').value);
        formData.append('slug', document.getElementById('slug').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('parent_id', document.getElementById('parent_id').value || 0);
        formData.append('order', document.getElementById('order').value);
        formData.append('icon', document.getElementById('icon').value);

        fetch('<?= base_url('api/categories/') ?>' + categoryId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
            .then(response => response.json())
            .then(result => {
                hideLoading();

                if (result.success) {
                    toastr.success('分类更新成功');
                    loadCategoryData();
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
                hideLoading();
                console.error('提交失败:', error);
                toastr.error('提交失败，请稍后重试');
            });
    }

    function deleteCategory() {
        if (!confirm('确定要删除这个分类吗？此操作不可恢复！')) {
            return;
        }

        showLoading();

        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch('<?= base_url('api/categories/') ?>' + categoryId, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
            .then(response => response.json())
            .then(result => {
                hideLoading();

                if (result.success) {
                    toastr.success('分类删除成功');
                    setTimeout(() => {
                        window.location.href = '<?= base_url('admin/categories') ?>';
                    }, 1000);
                } else {
                    toastr.error(result.message || '删除失败');
                }
            })
            .catch(error => {
                hideLoading();
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

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
</script>

<?= view('admin/layouts/footer') ?>