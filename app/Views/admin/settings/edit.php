<?php
$activePage = 'settings';
$pageTitle = '编辑配置';
$scripts = '';
if ($setting['type'] === 'editor') {
    $scripts = '<script src="https://cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>';
}
?>
<?= $this->include('admin/layouts/header') ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        <?php if ($setting['type'] === 'editor'): ?>
            // 保存原始的 textarea 值
            const settingValue = document.getElementById('editor');
            const originalValue = settingValue.value;

            // 初始化 CKEditor
            const editor = CKEDITOR.replace('editor', {
                allowedContent: true,
                extraAllowedContent: 'i[*]; span[*]; div[*]; ul[*]; li[*];',
                fullPage: false,
                autoParagraph: false,
                enterMode: CKEDITOR.ENTER_BR,
                shiftEnterMode: CKEDITOR.ENTER_P,
                removePlugins: 'elementspath,magicline',
                toolbar: [
                    ['Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'],
                    ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
                    ['Link', 'Unlink'],
                    ['Maximize']
                ]
            });

            // 监听源码模式切换事件
            editor.on('mode', function() {
                if (editor.mode === 'source') {
                    // 当切换到源码模式时，使用原始的初始值
                    setTimeout(function() {
                        const sourceEditor = document.querySelector('.cke_source');
                        if (sourceEditor) {
                            sourceEditor.value = originalValue;
                        }
                    }, 100);
                }
            });

            // 表单提交时确保正确的值被提交
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                // 确保编辑器内容被同步
                settingValue.value = editor.getData();
            });
        <?php endif; ?>
    });
</script>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">编辑配置</h3>
    </div>
    <div class="card-body">
        <form action="/admin/settings/update/<?= $setting['id'] ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="setting_key">键名</label>
                <input type="text" class="form-control" id="setting_key" name="setting_key"
                    value="<?= $setting['setting_key'] ?>" readonly>
            </div>
            <div class="form-group">
                <label for="title">标题</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= $setting['title'] ?>"
                    readonly>
            </div>
            <div class="form-group">
                <label for="type">类型</label>
                <input type="text" class="form-control" id="type" name="type" value="<?php
                $typeLabels = ['text' => '文本', 'textarea' => '多行文本', 'editor' => '富文本'];
                echo $typeLabels[$setting['type']] ?? '其他';
                ?>" readonly>
            </div>
            <div class="form-group">
                <label for="setting_value">值</label>
                <?php if ($setting['type'] === 'text'): ?>
                    <input type="text" class="form-control" id="setting_value" name="setting_value"
                        value="<?= $setting['setting_value'] ?>">
                <?php elseif ($setting['type'] === 'textarea'): ?>
                    <textarea class="form-control" id="setting_value" name="setting_value"
                        rows="5"><?= $setting['setting_value'] ?></textarea>
                <?php elseif ($setting['type'] === 'editor'): ?>
                    <textarea class="form-control" id="editor" name="setting_value"
                        rows="10"><?= $setting['setting_value'] ?></textarea>
                <?php endif; ?>
            </div>
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="/admin/settings" class="btn btn-secondary ml-2">取消</a>
            </div>
        </form>
    </div>
</div>

<?= $this->include('admin/layouts/footer') ?>

<?= $scripts ?>