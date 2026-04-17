<!-- 页脚 -->
<footer class="bg-light mt-6 py-5" style="display: flex; justify-content: center; align-items: center;">
    <div class="container" style="display: flex; justify-content: center; align-items: center;">
        <div class="row" style="display: flex; justify-content: center; align-items: center; width: 100%;">
            <div class="col-md-12"
                style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <!--<div class="mb-3" style="display: flex; justify-content: center; align-items: center;">
                        <a href="<?= base_url() ?>" class="text-muted mr-4" title="首页"><i class="fas fa-home"></i></a>
                        <a href="<?= base_url('about') ?>" class="text-muted mr-4" title="关于我们"><i class="fas fa-info-circle"></i></a>
                        <a href="<?= base_url('contact') ?>" class="text-muted mr-4" title="联系我们"><i class="fas fa-envelope"></i></a>
                        <a href="<?= base_url('rss') ?>" class="text-muted mr-4" title="RSS订阅"><i class="fas fa-rss"></i></a>
                        <a href="<?= base_url('sitemap') ?>" class="text-muted mr-4" title="站点地图"><i class="fas fa-sitemap"></i></a>
                    </div>-->
                <p style="margin: 0;">&copy; <?= date('Y') ?> <i class="fas fa-blog mr-1"></i>博客系统</p>
            </div>
        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="<?= cdn_asset('js/jquery.min.js') ?>"></script>
<!-- Bootstrap Bundle -->
<script src="<?= cdn_asset('js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<!-- Toastr -->
<script src="<?= cdn_asset('js/toastr/toastr.min.js') ?>"></script>
<!-- Custom JS -->
<script src="<?= cdn_asset('js/app.js') ?>"></script>

<script>
    // 初始化toastr
    $(document).ready(function () {
        // Toastr配置 - 自动关闭
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3000,
            extendedTimeOut: 1000,
            preventDuplicates: true,
            newestOnTop: true,
            showEasing: 'swing',
            hideMethod: 'fadeOut'
        };

        // Bootstrap Alert自动关闭功能
        $('.alert').each(function () {
            var $alert = $(this);
            var autoClose = $alert.data('auto-close') || 5000; // 默认5秒后关闭
            
            if ($alert.hasClass('alert-dismissible')) {
                setTimeout(function () {
                    $alert.alert('close');
                }, autoClose);
            }
        });

        // Bootstrap Modal自动关闭功能
        $('.modal').each(function () {
            var $modal = $(this);
            var autoClose = $modal.data('auto-close') || 10000; // 默认10秒后关闭
            
            if ($modal.hasClass('fade')) {
                $modal.on('shown.bs.modal', function () {
                    setTimeout(function () {
                        $modal.modal('hide');
                    }, autoClose);
                });
            }
        });

        // Bootstrap Popover自动关闭功能
        $('[data-bs-toggle="popover"]').each(function () {
            var $popover = $(this);
            var autoClose = $popover.data('auto-close') || 3000; // 默认3秒后关闭
            
            $popover.on('shown.bs.popover', function () {
                setTimeout(function () {
                    $popover.popover('hide');
                }, autoClose);
            });
        });

        // Bootstrap Tooltip自动关闭功能
        $('[data-bs-toggle="tooltip"]').each(function () {
            var $tooltip = $(this);
            var autoClose = $tooltip.data('auto-close') || 2000; // 默认2秒后关闭
            
            $tooltip.on('shown.bs.tooltip', function () {
                setTimeout(function () {
                    $tooltip.tooltip('hide');
                }, autoClose);
            });
        });

        // 自定义通知自动关闭
        $('.notification').each(function () {
            var $notification = $(this);
            var autoClose = $notification.data('auto-close') || 4000; // 默认4秒后关闭
            
            setTimeout(function () {
                $notification.fadeOut(500, function () {
                    $(this).remove();
                });
            }, autoClose);
        });

        // 导航栏激活状态
        var currentPath = window.location.pathname;
        $('.navbar-nav a').each(function () {
            var href = $(this).attr('href');
            if (href === currentPath || href === window.location.href) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    });
</script>
</body>

</html>