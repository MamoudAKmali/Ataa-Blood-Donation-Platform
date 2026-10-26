<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'donor';
    $blood_type = $_POST['blood_type'] ?? 'Unknown';
    $gender = $_POST['gender'] ?? 'unknown';

    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($gender)) {
        $error = 'جميع الحقول المطلوبة يجب تعبئتها.';
    } else {
        // التحقق مما إذا كان البريد الإلكتروني مسجل مسبقاً
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'البريد الإلكتروني مسجل مسبقاً.';
        } else {
            // إدخال المستخدم في القاعدة
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, role, blood_type, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($insert->execute([$name, $email, $hash, $phone, $role, $blood_type, $gender])) {
                $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
            } else {
                $error = 'حدث خطأ أثناء التسجيل، يرجى المحاولة لاحقاً.';
            }
        }
    }
}
?>

<div class="container">
    <div class="auth-container">
        <div class="card">
            <div class="text-center mb-md">
                <div class="card__icon mx-auto"><span class="material-symbols-outlined">person_add</span></div>
                <h2 class="card__title">إنشاء حساب جديد</h2>
                <p class="text-secondary text-sm">انضم إلى مجتمع عطاء وساهم في إنقاذ الأرواح</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert--success">
                    <?php echo htmlspecialchars($success); ?>
                    <br><br>
                    <a href="login.php" class="btn btn--primary w-full">الانتقال لتسجيل الدخول</a>
                </div>
            <?php else: ?>

                <form method="POST" action="register.php">
                    <input type="hidden" name="role" value="donor">

                    <div class="form-group">
                        <label class="form-label">الاسم الكامل *</label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: أحمد محمد">
                    </div>

                    <div class="form-group">
                        <label class="form-label">البريد الإلكتروني *</label>
                        <input type="email" name="email" class="form-control" required placeholder="example@domain.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">رقم الهاتف *</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="05xxxxxxxx">
                    </div>

                    <div class="form-group">
                        <label class="form-label">فصيلة الدم </label>
                        <select name="blood_type" class="form-control">
                            <option value="Unknown">غير معروف</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">الجنس *</label>
                        <select name="gender" class="form-control" required>
                            <option value="">-- يرجى الاختيار --</option>
                            <option value="male">ذكر</option>
                            <option value="female">أنثى</option>
                        </select>
                        <small class="text-secondary mt-xs" style="display:block;">مطلوب لتحديد أهليتك للتبرع حسب الفترات الطبية المعتمدة</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">كلمة المرور *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn--primary w-full mt-sm">إنشاء الحساب</button>
                </form>

                <div class="text-center mt-md text-sm">
                    <p>لديك حساب بالفعل؟ <a href="login.php" class="text-primary font-bold">سجل دخولك هنا</a></p>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>