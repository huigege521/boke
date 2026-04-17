    </div>
</div>

<!-- jQuery -->
<script src="<?= base_url('js/jquery.min.js') ?>"></script>
<!-- Bootstrap JS -->
<script src="<?= base_url('js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<!-- Toastr JS -->
<script src="<?= base_url('js/toastr/toastr.min.js') ?>"></script>

<script>
    // Toastr配置
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // 显示Flash消息
    <?php if (session()->getFlashdata('success')): ?>
        toastr.success('<?= session()->getFlashdata('success') ?>');
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        toastr.error('<?= session()->getFlashdata('error') ?>');
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        toastr.warning('<?= session()->getFlashdata('warning') ?>');
    <?php endif; ?>

    <?php if (session()->getFlashdata('info')): ?>
        toastr.info('<?= session()->getFlashdata('info') ?>');
    <?php endif; ?>
</script>

<script>
    // 消息提示自动消失
    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 3000); // 3秒后自动消失
        });
    });
</script>

<script>
    // 处理DELETE请求
    document.querySelectorAll('[data-method="delete"]').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm(this.getAttribute('data-confirm') || '确定要删除吗？')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = this.href;
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_method';
                input.value = 'DELETE';
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>
<?= $scripts ?? '' ?>
</body>

</html>