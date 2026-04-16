    <!-- 页脚 -->
    <footer class="bg-light mt-6 py-5" style="display: flex; justify-content: center; align-items: center;">
        <div class="container" style="display: flex; justify-content: center; align-items: center;">
            <div class="row" style="display: flex; justify-content: center; align-items: center; width: 100%;">
                <div class="col-md-12" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
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

    <script>
        // 导航栏激活状态
        $(document).ready(function () {
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