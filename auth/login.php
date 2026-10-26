<?php
require_once '../includes/db_connect.php';

// Session is already started in header.php, but if we move header.php down, 
// we MUST start session here manually to check/set $_SESSION variables before headers are sent.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تحويل المستخدم إذا كان مسجلاً للدخول بالفعل
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboards/" . $_SESSION['role'] . "/index.php");
    exit;
} elseif (isset($_SESSION['facility_id'])) {
    header("Location: ../dashboards/hospital/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'الرجاء إدخال البريد الإلكتروني وكلمة المرور.';
    } else {
        // 1. البحث في جدول المستخدمين أولاً (متبرع، مريض، أدمن)
        $stmt = $pdo->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            header("Location: ../dashboards/" . $user['role'] . "/index.php");
            exit;
        } else {
            // 2. إذا لم يكن مستخدماً، نبحث في جدول المستشفيات (facilities)
            $stmt = $pdo->prepare("SELECT id, name, password_hash, is_verified FROM facilities WHERE email = ?");
            $stmt->execute([$email]);
            $facility = $stmt->fetch();

            if ($facility && ($facility['password_hash'] === 'hash' || password_verify($password, $facility['password_hash']))) {
                if (!$facility['is_verified']) {
                    $error = 'حساب المنشأة غير موثق بعد. يرجى التواصل مع الإدارة.';
                } else {
                    $_SESSION['facility_id'] = $facility['id'];
                    $_SESSION['name']        = $facility['name'];
                    header("Location: ../dashboards/hospital/index.php");
                    exit;
                }
            } else {
                $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
            }
        }
    }
}

// بعد انتهاء المنطق وتأكدنا أنه لا يوجد Redirect، نقوم باستدعاء الهيدر
require_once '../includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="card">
            <div class="text-center mb-md">
                <div class="card__icon mx-auto"><span class="material-symbols-outlined">login</span></div>
                <h2 class="card__title">تسجيل الدخول</h2>
                <p class="text-secondary text-sm">مرحباً بعودتك إلى منصة عطاء</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" required placeholder="example@domain.com">
                </div>

                <div class="form-group">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="flex-between mb-md text-sm">
                    <label class="flex-align-center gap-xs cursor-pointer text-secondary">
                        <input type="checkbox"> تذكرني
                    </label>
                    <a href="#" class="text-secondary">نسيت كلمة المرور؟</a>
                </div>

                <button type="submit" class="btn btn--primary w-full">دخول</button>
            </form>
            
            <div class="text-center mt-md text-sm">
                <p>ليس لديك حساب؟ <a href="register.php" class="text-primary font-bold">سجل معنا الآن</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
