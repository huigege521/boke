/**
 * 博客系统前端应用
 * 提供表单验证、交互效果、AJAX 请求等功能
 */

(function(window, document, $) {
    'use strict';

    // 应用命名空间
    const App = {
        // 配置
        config: {
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
            baseUrl: window.location.origin,
            apiUrl: window.location.origin + '/api',
            debug: false
        },

        // 初始化
        init: function() {
            this.initCsrfToken();
            this.initTooltips();
            this.initPopovers();
            this.initConfirmDialogs();
            this.initAjaxSetup();
            this.initFormValidation();
            this.initAutoSave();
            this.initLazyLoad();
            this.initSmoothScroll();
            this.initBackToTop();
            this.initMobileMenu();
            this.initSearchAutocomplete();
            this.initReadingProgress();
        },

        // 初始化 CSRF Token
        initCsrfToken: function() {
            // 从 meta 标签获取 CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) {
                this.config.csrfToken = token;
            }
        },

        // 初始化工具提示
        initTooltips: function() {
            if (typeof $ !== 'undefined' && $.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        },

        // 初始化弹出框
        initPopovers: function() {
            if (typeof $ !== 'undefined' && $.fn.popover) {
                $('[data-toggle="popover"]').popover();
            }
        },

        // 初始化确认对话框
        initConfirmDialogs: function() {
            $(document).on('click', '[data-confirm]', function(e) {
                const message = $(this).data('confirm') || '确定要执行此操作吗？';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        // 初始化 AJAX 设置
        initAjaxSetup: function() {
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': this.config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    beforeSend: function() {
                        App.showLoading();
                    },
                    complete: function() {
                        App.hideLoading();
                    },
                    error: function(xhr, status, error) {
                        App.handleAjaxError(xhr, status, error);
                    }
                });
            }
        },

        // 初始化表单验证
        initFormValidation: function() {
            const self = this;

            $(document).on('submit', 'form[data-validate]', function(e) {
                const $form = $(this);
                const rules = $form.data('validate');

                if (!self.validateForm($form, rules)) {
                    e.preventDefault();
                    return false;
                }
            });

            // 实时验证
            $(document).on('blur', '[data-validate-field]', function() {
                const $field = $(this);
                const rule = $field.data('validate-field');
                const value = $field.val();

                if (!self.validateField(value, rule)) {
                    $field.addClass('is-invalid');
                    $field.removeClass('is-valid');
                } else {
                    $field.removeClass('is-invalid');
                    $field.addClass('is-valid');
                }
            });
        },

        // 验证表单
        validateForm: function($form, rules) {
            let isValid = true;
            const self = this;

            // 清除之前的错误
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();

            // 遍历规则
            for (const fieldName in rules) {
                const $field = $form.find('[name="' + fieldName + '"]');
                const value = $field.val();
                const fieldRules = rules[fieldName].split('|');

                for (const rule of fieldRules) {
                    const [ruleName, ruleParam] = rule.split(':');

                    if (!self.validateField(value, ruleName, ruleParam)) {
                        isValid = false;
                        $field.addClass('is-invalid');

                        const errorMessage = self.getValidationMessage(ruleName, ruleParam);
                        $field.after('<div class="invalid-feedback">' + errorMessage + '</div>');
                        break;
                    }
                }
            }

            return isValid;
        },

        // 验证单个字段
        validateField: function(value, rule, param) {
            switch (rule) {
                case 'required':
                    return value !== null && value !== undefined && value.toString().trim() !== '';

                case 'email':
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

                case 'url':
                    return /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/.test(value);

                case 'min':
                    return value.length >= parseInt(param);

                case 'max':
                    return value.length <= parseInt(param);

                case 'numeric':
                    return !isNaN(parseFloat(value)) && isFinite(value);

                case 'integer':
                    return /^-?\d+$/.test(value);

                case 'alpha':
                    return /^[a-zA-Z]+$/.test(value);

                case 'alpha_num':
                    return /^[a-zA-Z0-9]+$/.test(value);

                case 'alpha_dash':
                    return /^[a-zA-Z0-9_-]+$/.test(value);

                case 'phone':
                    return /^1[3-9]\d{9}$/.test(value) || /^(\d{3,4}-)?\d{7,8}$/.test(value);

                case 'username':
                    return /^[a-zA-Z][a-zA-Z0-9_-]{3,31}$/.test(value);

                case 'password':
                    // 至少8位，包含大小写字母和数字
                    return value.length >= 8 &&
                           /[A-Z]/.test(value) &&
                           /[a-z]/.test(value) &&
                           /[0-9]/.test(value);

                case 'match':
                    return value === $(param).val();

                default:
                    return true;
            }
        },

        // 获取验证错误消息
        getValidationMessage: function(rule, param) {
            const messages = {
                required: '此字段为必填项',
                email: '请输入有效的邮箱地址',
                url: '请输入有效的 URL',
                min: '至少需要 ' + param + ' 个字符',
                max: '最多允许 ' + param + ' 个字符',
                numeric: '请输入数字',
                integer: '请输入整数',
                alpha: '只能包含字母',
                alpha_num: '只能包含字母和数字',
                alpha_dash: '只能包含字母、数字、下划线和破折号',
                phone: '请输入有效的电话号码',
                username: '用户名格式不正确',
                password: '密码必须至少8位，包含大小写字母和数字',
                match: '两次输入不匹配'
            };

            return messages[rule] || '验证失败';
        },

        // 初始化自动保存
        initAutoSave: function() {
            const self = this;

            $('form[data-autosave]').each(function() {
                const $form = $(this);
                const interval = $form.data('autosave') || 30000; // 默认30秒
                let saveTimer;

                $form.on('input change', 'input, textarea, select', function() {
                    clearTimeout(saveTimer);
                    saveTimer = setTimeout(function() {
                        self.autoSave($form);
                    }, interval);
                });
            });
        },

        // 自动保存
        autoSave: function($form) {
            const formData = $form.serialize();
            const saveUrl = $form.data('autosave-url') || $form.attr('action');

            $.ajax({
                url: saveUrl,
                type: 'POST',
                data: formData + '&autosave=1',
                success: function(response) {
                    if (response.success) {
                        App.showNotification('自动保存成功', 'success');
                    }
                }
            });
        },

        // 初始化懒加载
        initLazyLoad: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },

        // 初始化平滑滚动
        initSmoothScroll: function() {
            $(document).on('click', 'a[href^="#"]', function(e) {
                e.preventDefault();
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 500);
                }
            });
        },

        // 初始化返回顶部
        initBackToTop: function() {
            const $backToTop = $('#backToTop');

            if ($backToTop.length === 0) {
                $('body').append('<button id="backToTop" class="btn btn-primary back-to-top"><i class="fas fa-arrow-up"></i></button>');
            }

            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('#backToTop').fadeIn();
                } else {
                    $('#backToTop').fadeOut();
                }
            });

            $(document).on('click', '#backToTop', function() {
                $('html, body').animate({ scrollTop: 0 }, 500);
            });
        },

        // 初始化移动端菜单
        initMobileMenu: function() {
            $(document).on('click', '.mobile-menu-toggle', function() {
                $('.sidebar').toggleClass('active');
                $('body').toggleClass('menu-open');
            });

            $(document).on('click', function(e) {
                if ($(e.target).closest('.sidebar').length === 0 &&
                    $(e.target).closest('.mobile-menu-toggle').length === 0) {
                    $('.sidebar').removeClass('active');
                    $('body').removeClass('menu-open');
                }
            });
        },

        // 初始化搜索自动完成
        initSearchAutocomplete: function() {
            const $searchInput = $('#searchInput');

            if ($searchInput.length === 0) return;

            let searchTimer;

            $searchInput.on('input', function() {
                const query = $(this).val();

                clearTimeout(searchTimer);

                if (query.length < 2) {
                    $('#searchSuggestions').hide();
                    return;
                }

                searchTimer = setTimeout(function() {
                    $.ajax({
                        url: '/search/suggestions',
                        type: 'GET',
                        data: { q: query },
                        success: function(response) {
                            if (response.suggestions && response.suggestions.length > 0) {
                                let html = '<ul class="list-group">';
                                response.suggestions.forEach(function(item) {
                                    html += '<li class="list-group-item suggestion-item" data-url="' + item.url + '">' +
                                           '<i class="fas fa-' + (item.type === 'post' ? 'file-alt' : 'tag') + ' mr-2"></i>' +
                                           item.title + '</li>';
                                });
                                html += '</ul>';

                                $('#searchSuggestions').html(html).show();
                            }
                        }
                    });
                }, 300);
            });

            $(document).on('click', '.suggestion-item', function() {
                window.location.href = $(this).data('url');
            });

            $(document).on('click', function(e) {
                if ($(e.target).closest('#searchInput').length === 0) {
                    $('#searchSuggestions').hide();
                }
            });
        },

        // 初始化阅读进度
        initReadingProgress: function() {
            if ($('.article-content').length === 0) return;

            const $progressBar = $('<div class="reading-progress"><div class="reading-progress-bar"></div></div>');
            $('body').append($progressBar);

            $(window).scroll(function() {
                const article = $('.article-content');
                const articleTop = article.offset().top;
                const articleHeight = article.height();
                const windowHeight = $(window).height();
                const scrollTop = $(window).scrollTop();

                const progress = Math.min(100, Math.max(0,
                    ((scrollTop + windowHeight - articleTop) / articleHeight) * 100
                ));

                $('.reading-progress-bar').css('width', progress + '%');
            });
        },

        // 显示加载动画
        showLoading: function() {
            if ($('#loadingOverlay').length === 0) {
                $('body').append('<div id="loadingOverlay" class="loading-overlay"><div class="spinner"></div></div>');
            }
            $('#loadingOverlay').show();
        },

        // 隐藏加载动画
        hideLoading: function() {
            $('#loadingOverlay').hide();
        },

        // 处理 AJAX 错误
        handleAjaxError: function(xhr, status, error) {
            let message = '请求失败，请稍后重试';

            if (xhr.status === 401) {
                message = '请先登录';
                window.location.href = '/home/login';
            } else if (xhr.status === 403) {
                message = '权限不足';
            } else if (xhr.status === 404) {
                message = '请求的资源不存在';
            } else if (xhr.status === 422) {
                const response = JSON.parse(xhr.responseText);
                message = response.message || '验证失败';
            } else if (xhr.status === 500) {
                message = '服务器错误';
            }

            this.showNotification(message, 'error');
        },

        // 显示通知
        showNotification: function(message, type = 'info', duration = 3000) {
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                const $notification = $('<div class="notification notification-' + type + '">' + message + '</div>');
                $('body').append($notification);

                $notification.fadeIn().delay(duration).fadeOut(function() {
                    $(this).remove();
                });
            }
        },

        // 确认对话框
        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        // AJAX 请求封装
        ajax: function(options) {
            const defaults = {
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            };

            return $.ajax($.extend({}, defaults, options));
        },

        // 格式化日期
        formatDate: function(date, format = 'YYYY-MM-DD HH:mm:ss') {
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const seconds = String(d.getSeconds()).padStart(2, '0');

            return format
                .replace('YYYY', year)
                .replace('MM', month)
                .replace('DD', day)
                .replace('HH', hours)
                .replace('mm', minutes)
                .replace('ss', seconds);
        },

        // 格式化文件大小
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        // 复制到剪贴板
        copyToClipboard: function(text) {
            const temp = document.createElement('input');
            temp.value = text;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            this.showNotification('已复制到剪贴板', 'success');
        },

        // 防抖函数
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // 节流函数
        throttle: function(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    };

    // 暴露到全局
    window.App = App;

    // DOM 加载完成后初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            App.init();
        });
    } else {
        App.init();
    }

})(window, document, jQuery);
