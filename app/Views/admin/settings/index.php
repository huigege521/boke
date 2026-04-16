<?php
$activePage = 'settings';
$pageTitle = '配置管理';
?>
<?= $this->include('admin/layouts/header') ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">系统配置</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>键名</th>
                        <th>标题</th>
                        <th>类型</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($settings as $setting): ?>
                        <tr>
                            <td><?= $setting['id'] ?></td>
                            <td><?= $setting['setting_key'] ?></td>
                            <td><?= $setting['title'] ?></td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'text' => '<span class="badge bg-info">文本</span>',
                                    'textarea' => '<span class="badge bg-primary">多行文本</span>',
                                    'editor' => '<span class="badge bg-success">富文本</span>'
                                ];
                                echo $typeLabels[$setting['type']] ?? '<span class="badge bg-secondary">其他</span>';
                                ?>
                            </td>
                            <td>
                                <a href="/admin/settings/edit/<?= $setting['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> 编辑
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->include('admin/layouts/footer') ?>