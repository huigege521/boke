    </div>
</div>

<!-- Bootstrap JS -->
<script src="<?= base_url('js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
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