<?php require_once 'includes/header.php'; ?>
<section class="section">
    <div class="container">
        <div class="card mt-xl p-xl" style="max-width: 600px; margin-left: auto; margin-right: auto; padding: 2rem;">
            <div class="card__icon mx-auto text-primary mb-md"><span class="material-symbols-outlined" style="font-size: 3rem;">contact_support</span></div>
            <h1 class="text-primary mb-md text-center">تواصل معنا</h1>
            <p class="mb-md text-secondary text-center">يسعدنا تواصلكم معنا لأي استفسارات أو لطلب الدعم الفني.</p>
            <form class="mt-sm">
                <div class="form-group">
                    <label class="form-label">الاسم</label>
                    <input type="text" class="form-control" placeholder="اسمك الكريم">
                </div>
                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" class="form-control" placeholder="example@domain.com">
                </div>
                <div class="form-group">
                    <label class="form-label">الرسالة</label>
                    <textarea class="form-control" rows="5" placeholder="اكتب رسالتك هنا..."></textarea>
                </div>
                <button type="button" onclick="alert('تم إرسال رسالتك بنجاح. شكراً لتواصلك معنا!');" class="btn btn--primary w-full mt-md">إرسال الرسالة</button>
            </form>
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
