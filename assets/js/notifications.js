/* ==========================================================================
   notifications.js — نظام الإشعارات والتأكيدات العام لمنصة عطاء
   ========================================================================== */

(function () {
    'use strict';

    /* ────────────────────────────────────────────────
       1. Toast System
    ──────────────────────────────────────────────── */
    var TOAST_DURATION = 4500; // ms

    var icons = {
        success : 'check_circle',
        error   : 'error',
        warning : 'warning',
        info    : 'info'
    };

    var titles = {
        success : 'تمت العملية بنجاح',
        error   : 'حدث خطأ',
        warning : 'تنبيه',
        info    : 'معلومة'
    };

    function getContainer() {
        var c = document.getElementById('toast-container');
        if (!c) {
            c = document.createElement('div');
            c.id = 'toast-container';
            c.className = 'toast-container';
            document.body.appendChild(c);
        }
        return c;
    }

    function showToast(type, message, customTitle) {
        type = type || 'info';
        var container = getContainer();
        var duration  = TOAST_DURATION;

        var toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        toast.style.setProperty('--toast-duration', (duration / 1000) + 's');

        toast.innerHTML =
            '<div class="toast__icon">' +
                '<span class="material-symbols-outlined">' + (icons[type] || 'info') + '</span>' +
            '</div>' +
            '<div class="toast__body">' +
                '<div class="toast__title">' + (customTitle || titles[type] || '') + '</div>' +
                '<div class="toast__msg">'   + message + '</div>' +
            '</div>' +
            '<button class="toast__close" aria-label="إغلاق">' +
                '<span class="material-symbols-outlined">close</span>' +
            '</button>';

        container.appendChild(toast);

        // Close button
        toast.querySelector('.toast__close').addEventListener('click', function () {
            dismissToast(toast);
        });

        // Auto dismiss
        var timer = setTimeout(function () { dismissToast(toast); }, duration);

        // Pause on hover
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () {
            timer = setTimeout(function () { dismissToast(toast); }, 1500);
        });
    }

    function dismissToast(toast) {
        if (toast.classList.contains('is-hiding')) return;
        toast.classList.add('is-hiding');
        setTimeout(function () {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 350);
    }

    /* ────────────────────────────────────────────────
       2. Confirm Dialog (يحل محل window.confirm)
    ──────────────────────────────────────────────── */
    function showConfirm(options) {
        return new Promise(function (resolve) {
            var type    = options.type    || 'danger';
            var title   = options.title   || 'هل أنت متأكد؟';
            var message = options.message || '';
            var okText  = options.okText  || 'تأكيد';
            var noText  = options.noText  || 'إلغاء';

            var iconMap = { danger: 'delete_forever', warning: 'logout', info: 'help' };

            var overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.innerHTML =
                '<div class="confirm-box">' +
                    '<div class="confirm-box__icon confirm-box__icon--' + type + '">' +
                        '<span class="material-symbols-outlined">' + (iconMap[type] || 'help') + '</span>' +
                    '</div>' +
                    '<div class="confirm-box__title">' + title + '</div>' +
                    '<div class="confirm-box__msg">'   + message + '</div>' +
                    '<div class="confirm-box__actions">' +
                        '<button class="confirm-box__btn confirm-box__btn--cancel" id="cnf-no">'  + noText  + '</button>' +
                        '<button class="confirm-box__btn confirm-box__btn--' + type + '" id="cnf-ok">' + okText + '</button>' +
                    '</div>' +
                '</div>';

            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';

            function close(result) {
                overlay.style.animation = 'overlay-out 0.2s ease forwards';
                setTimeout(function () {
                    if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                    document.body.style.overflow = '';
                }, 200);
                resolve(result);
            }

            overlay.querySelector('#cnf-ok').addEventListener('click', function () { close(true); });
            overlay.querySelector('#cnf-no').addEventListener('click', function () { close(false); });

            // ESC key
            function onKey(e) {
                if (e.key === 'Escape') { document.removeEventListener('keydown', onKey); close(false); }
            }
            document.addEventListener('keydown', onKey);

            // Focus ok button
            setTimeout(function () { overlay.querySelector('#cnf-ok').focus(); }, 100);
        });
    }

    /* ────────────────────────────────────────────────
       3. Auto-wire: Delete Forms
       كل form يحتوي على data-confirm سيستخدم الـ dialog المخصص
    ──────────────────────────────────────────────── */
    function wireDeleteForms() {
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var type    = form.dataset.confirmType    || 'danger';
                var title   = form.dataset.confirmTitle   || 'هل أنت متأكد؟';
                var message = form.dataset.confirm;
                var okText  = form.dataset.confirmOk      || 'نعم، تأكيد';
                var noText  = form.dataset.confirmNo      || 'إلغاء';

                showConfirm({ type, title, message, okText, noText }).then(function (confirmed) {
                    if (confirmed) form.submit();
                });
            });
        });
    }

    /* ────────────────────────────────────────────────
       4. Auto-wire: Logout Link
    ──────────────────────────────────────────────── */
    function wireLogout() {
        document.querySelectorAll('a[data-logout], .logout-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var href = link.href;
                showConfirm({
                    type    : 'warning',
                    title   : 'تسجيل الخروج',
                    message : 'هل تريد تسجيل الخروج من حسابك على منصة عطاء؟',
                    okText  : 'نعم، خروج',
                    noText  : 'إلغاء'
                }).then(function (confirmed) {
                    if (confirmed) window.location.href = href;
                });
            });
        });
    }

    /* ────────────────────────────────────────────────
       5. Flash Messages من PHP → Toast
       إذا وُجد عنصر #php-flash في الصفحة يُحوّل لـ toast
    ──────────────────────────────────────────────── */
    function processPhpFlash() {
        var el = document.getElementById('php-flash');
        if (!el) return;
        var type = el.dataset.type || 'info';
        var msg  = el.dataset.msg  || '';
        if (msg) showToast(type, msg);
        el.parentNode && el.parentNode.removeChild(el);
    }

    /* ────────────────────────────────────────────────
       6. Public API
    ──────────────────────────────────────────────── */
    window.Ataa = window.Ataa || {};
    window.Ataa.notify  = showToast;
    window.Ataa.confirm = showConfirm;

    /* ────────────────────────────────────────────────
       Init
    ──────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        wireDeleteForms();
        wireLogout();
        processPhpFlash();
    });

    /* CSS animation for overlay-out */
    var style = document.createElement('style');
    style.textContent = '@keyframes overlay-out { to { opacity: 0; } }';
    document.head.appendChild(style);

})();
