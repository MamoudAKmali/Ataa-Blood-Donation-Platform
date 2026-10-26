/* ==========================================================================
   main.js — JavaScript الموحد لموقع عطاء
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ── 1. هامبرجر الموقع العام (الصفحات العامة فقط) ── */
    var hamburgerBtn = document.getElementById('hamburger-btn');
    var mobileMenu   = document.getElementById('mobile-menu');

    if (hamburgerBtn && mobileMenu) {
        hamburgerBtn.addEventListener('click', function () {
            hamburgerBtn.classList.toggle('is-open');
            mobileMenu.classList.toggle('is-open');
        });

        document.addEventListener('click', function (e) {
            if (!hamburgerBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                hamburgerBtn.classList.remove('is-open');
                mobileMenu.classList.remove('is-open');
            }
        });
    }

    /* ── 2. سايدبار لوحة التحكم (الأدمن) ── */
    var toggleBtn = document.getElementById('dbSidebarToggle');
    var sidebar   = document.querySelector('.db-sidebar');
    var overlay   = document.getElementById('dbSidebarOverlay');

    if (toggleBtn && sidebar && overlay) {

        /* فتح / إغلاق بالزر */
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = sidebar.classList.contains('is-open');
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        /* إغلاق بالضغط على الأوفرلاي */
        overlay.addEventListener('click', function () {
            closeSidebar();
        });

        /* إغلاق بالضغط على روابط السايدبار (موبايل فقط) */
        sidebar.querySelectorAll('a.db-sidebar__link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        /* إغلاق بالضغط على ESC */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        function openSidebar() {
            sidebar.classList.add('is-open');
            overlay.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    }

});
