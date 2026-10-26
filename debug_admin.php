<?php
/**
 * ملف تشخيص مؤقت — احذفه بعد الانتهاء!
 */
require_once 'includes/db_connect.php';

echo "<h2 style='font-family:Arial'>🔍 تشخيص مشكلة تسجيل دخول الأدمن</h2>";

// 1. فحص وجود الأدمن في قاعدة البيانات
$stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE role = 'admin'");
$stmt->execute();
$admins = $stmt->fetchAll();

echo "<h3>1. حسابات الأدمن في قاعدة البيانات:</h3>";
if (empty($admins)) {
    echo "<p style='color:red'>❌ لا يوجد أي حساب أدمن في جدول users!</p>";
} else {
    foreach ($admins as $a) {
        echo "<p>✅ ID: {$a['id']} | الاسم: {$a['name']} | الإيميل: {$a['email']} | الرول: {$a['role']}</p>";
        echo "<p style='font-size:12px;color:gray'>Hash: " . substr($a['password_hash'], 0, 30) . "...</p>";
    }
}

// 2. اختبار كلمة المرور
$test_passwords = ['admin123', 'Admin123', 'admin@123', '123456', 'admin', 'ataa2024', 'ataa@2024'];
echo "<h3>2. اختبار كلمات المرور الشائعة:</h3>";
if (!empty($admins)) {
    foreach ($test_passwords as $pass) {
        $result = password_verify($pass, $admins[0]['password_hash']);
        echo $result
            ? "<p style='color:green'>✅ كلمة المرور الصحيحة هي: <strong>$pass</strong></p>"
            : "<p style='color:#aaa'>❌ $pass — خاطئة</p>";
    }
}

// 3. عرض جلسة الحالية
echo "<h3>3. حالة الجلسة الحالية:</h3>";
if (session_status() === PHP_SESSION_NONE) session_start();
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// 4. زر إعادة تعيين كلمة المرور
echo "<h3>4. إعادة تعيين كلمة مرور الأدمن:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE role = 'admin'");
    $stmt->execute([$new_hash]);
    echo "<p style='color:green'>✅ تم تحديث كلمة المرور! الحين جرّب تسجّل دخول بـ: <strong>" . htmlspecialchars($_POST['new_password']) . "</strong></p>";
}
?>
<form method="POST" style="margin-top:10px">
    <label>كلمة مرور جديدة للأدمن: </label>
    <input type="text" name="new_password" value="admin123" style="padding:5px;margin:5px">
    <button type="submit" style="padding:5px 15px;background:crimson;color:white;border:none;cursor:pointer">تحديث</button>
</form>

<p style="color:red;margin-top:30px"><strong>⚠️ احذف هذا الملف بعد الانتهاء من التشخيص!</strong></p>
