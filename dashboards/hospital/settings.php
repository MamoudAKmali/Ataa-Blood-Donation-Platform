<?php
require_once '../../includes/hospital_auth.php';
require_once '../../includes/db_connect.php';

$page_title    = "إعدادات المنشأة - عطاء";
$facility_id   = $_SESSION['facility_id'];

// PRG: قراءة رسائل الجلسة
$success = $_SESSION['hs_success'] ?? '';
$error   = $_SESSION['hs_error']   ?? '';
unset($_SESSION['hs_success'], $_SESSION['hs_error']);

// جلب بيانات المنشأة الحالية
$stmt = $pdo->prepare("SELECT name, email, phone, latitude, longitude FROM facilities WHERE id = ?");
$stmt->execute([$facility_id]);
$facility = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $latitude  = trim($_POST['latitude']  ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $password  = $_POST['password']       ?? '';

    if (empty($name) || empty($email)) {
        $_SESSION['hs_error'] = 'الاسم والبريد الإلكتروني حقول إجبارية.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM facilities WHERE email = ? AND id != ?");
        $stmt->execute([$email, $facility_id]);
        if ($stmt->fetch()) {
            $_SESSION['hs_error'] = 'البريد الإلكتروني مستخدم لمنشأة أخرى.';
        } else {
            $lat = !empty($latitude)  ? $latitude  : null;
            $lng = !empty($longitude) ? $longitude : null;

            if (!empty($password)) {
                $hash   = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE facilities SET name=?, email=?, phone=?, latitude=?, longitude=?, password_hash=? WHERE id=?");
                $ok     = $update->execute([$name, $email, $phone, $lat, $lng, $hash, $facility_id]);
            } else {
                $update = $pdo->prepare("UPDATE facilities SET name=?, email=?, phone=?, latitude=?, longitude=? WHERE id=?");
                $ok     = $update->execute([$name, $email, $phone, $lat, $lng, $facility_id]);
            }

            if ($ok) {
                $_SESSION['name']       = $name;
                $_SESSION['hs_success'] = 'تم حفظ إعدادات المنشأة بنجاح.';
            } else {
                $_SESSION['hs_error'] = 'حدث خطأ أثناء التحديث، يرجى المحاولة مجدداً.';
            }
        }
    }

    header('Location: settings.php');
    exit;
}

require_once '../../includes/dashboard_header.php';
?>

<style>
/* ── Settings Hero ──────────────────────────────────────────── */
.settings-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #3b82f6 100%);
    border-radius: 20px;
    padding: 32px 36px;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(37,99,235,0.25);
}
.settings-hero::before {
    content: '';
    position: absolute;
    top: -60px; left: -60px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    pointer-events: none;
}
.settings-hero__icon {
    width: 72px;
    height: 72px;
    border-radius: 16px;
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.settings-hero__icon .material-symbols-outlined {
    font-size: 36px;
    color: #fff;
}
.settings-hero__info { position: relative; z-index: 1; }
.settings-hero__name {
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 6px;
    font-family: var(--font-heading);
}
.settings-hero__sub {
    font-size: 14px;
    color: rgba(255,255,255,0.75);
}

/* ── Settings Card ──────────────────────────────────────────── */
.settings-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 24px;
    overflow: hidden;
}
.settings-card__head {
    padding: 18px 28px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.settings-card__head h2 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}
.settings-card__head .material-symbols-outlined {
    font-size: 22px;
    color: var(--primary);
}
.settings-card__body { padding: 28px; }
.settings-card__footer {
    padding: 18px 28px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.settings-footer-hint {
    font-size: 13px;
    color: var(--text-muted);
}

/* ── Form Grid ──────────────────────────────────────────────── */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}
.settings-field > label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.settings-field input {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--text-primary);
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    display: block;
}
.settings-field input:focus {
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(181,38,25,0.08);
}
.settings-field-hint {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 6px;
}
.settings-field-hint .material-symbols-outlined { font-size: 14px; }

/* ── Location Row ───────────────────────────────────────────── */
.location-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 16px;
    align-items: end;
}
.location-status {
    font-size: 13px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    min-height: 20px;
}
.location-status--loading { color: var(--text-muted); }
.location-status--success { color: #15803d; }
.location-status--error   { color: #be123c; }
.location-status .material-symbols-outlined { font-size: 16px; }

/* ── Save Button ────────────────────────────────────────────── */
.settings-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-family: var(--font-body);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(181,38,25,0.3);
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
}
.settings-save-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(181,38,25,0.4);
}
.settings-save-btn:active { transform: translateY(0); }
.settings-save-btn .material-symbols-outlined { font-size: 18px; }

/* ── Alert ──────────────────────────────────────────────────── */
.settings-alert {
    padding: 14px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}
.settings-alert--success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.settings-alert--error {
    background: #fff1f2;
    color: #be123c;
    border: 1px solid #fecdd3;
}
.settings-alert .material-symbols-outlined { font-size: 20px; }

/* ── Geo Button ─────────────────────────────────────────────── */
.geo-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    background: #f8fafc;
    color: var(--text-secondary);
    font-family: var(--font-body);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: border-color 0.2s, background 0.2s, color 0.2s;
}
.geo-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: #fff5f5;
}
.geo-btn .material-symbols-outlined { font-size: 18px; }

@media (max-width: 640px) {
    .settings-hero { flex-direction: column; padding: 24px 20px; }
    .settings-card__body { padding: 20px 16px; }
    .settings-card__footer { flex-direction: column; align-items: stretch; }
    .settings-save-btn { justify-content: center; }
    .location-grid { grid-template-columns: 1fr; }
}
</style>

<?php if ($success): ?>
<div class="settings-alert settings-alert--success">
    <span class="material-symbols-outlined">check_circle</span>
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="settings-alert settings-alert--error">
    <span class="material-symbols-outlined">error</span>
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Hero -->
<div class="settings-hero">
    <div class="settings-hero__icon">
        <span class="material-symbols-outlined">local_hospital</span>
    </div>
    <div class="settings-hero__info">
        <h1 class="settings-hero__name"><?php echo htmlspecialchars($facility['name']); ?></h1>
        <p class="settings-hero__sub">إدارة بيانات المنشأة، وتحديث موقعها الجغرافي لضمان وصول المتبرعين بدقة.</p>
    </div>
</div>

<form method="POST" action="">

    <!-- Basic Info -->
    <div class="settings-card">
        <div class="settings-card__head">
            <span class="material-symbols-outlined">apartment</span>
            <h2>بيانات المنشأة الأساسية</h2>
        </div>
        <div class="settings-card__body">
            <div class="settings-grid">
                <div class="settings-field">
                    <label>اسم المنشأة *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($facility['name']); ?>" required placeholder="مستشفى / بنك الدم ...">
                </div>
                <div class="settings-field">
                    <label>رقم هاتف الطوارئ</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($facility['phone'] ?? ''); ?>" placeholder="+966xxxxxxxxx">
                </div>
            </div>
        </div>
    </div>

    <!-- Account Security -->
    <div class="settings-card">
        <div class="settings-card__head">
            <span class="material-symbols-outlined">lock_person</span>
            <h2>بيانات الحساب والأمان</h2>
        </div>
        <div class="settings-card__body">
            <div class="settings-grid">
                <div class="settings-field">
                    <label>البريد الإلكتروني (لتسجيل الدخول) *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($facility['email']); ?>" required>
                </div>
                <div class="settings-field">
                    <label>كلمة المرور الجديدة</label>
                    <input type="password" name="password" placeholder="اتركه فارغاً للإبقاء على الحالية">
                    <p class="settings-field-hint">
                        <span class="material-symbols-outlined">shield</span>
                        8 أحرف على الأقل تشمل أرقاماً ورموزاً.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Location -->
    <div class="settings-card">
        <div class="settings-card__head">
            <span class="material-symbols-outlined">location_on</span>
            <h2>الموقع الجغرافي للمنشأة</h2>
        </div>
        <div class="settings-card__body">
            <p class="settings-field-hint" style="margin-bottom:20px; font-size:13px;">
                <span class="material-symbols-outlined">info</span>
                الخوارزمية تعتمد على الإحداثيات للبحث عن أقرب المتبرعين وإرسال النداءات لهم. يمكنك تحديد موقعك من الخريطة أدناه أو إدخال الإحداثيات يدوياً.
            </p>
            
            <div class="location-grid">
                <div class="settings-field">
                    <label>دوائر العرض (Latitude)</label>
                    <input type="text" id="latInput" name="latitude" value="<?php echo htmlspecialchars($facility['latitude'] ?? ''); ?>" placeholder="28.3835">
                </div>
                <div class="settings-field">
                    <label>خطوط الطول (Longitude)</label>
                    <input type="text" id="lngInput" name="longitude" value="<?php echo htmlspecialchars($facility['longitude'] ?? ''); ?>" placeholder="36.5662">
                </div>
                <div>
                    <button type="button" class="geo-btn" onclick="getMyLocation()">
                        <span class="material-symbols-outlined">my_location</span>
                        جلب موقعي الحالي
                    </button>
                </div>
            </div>

            <!-- الخريطة التفاعلية -->
            <div id="hospitalMap" style="height: 300px; width: 100%; border-radius: 12px; margin-top: 20px; border: 1.5px solid #e2e8f0; z-index: 1;"></div>
            <p style="font-size: 12px; color: #64748b; margin-top: 8px;">* انقر على الخريطة لتحديد موقع المنشأة بدقة.</p>

            <div id="locationStatus" class="location-status"></div>
        </div>
        <div class="settings-card__footer">
            <span class="settings-footer-hint">أي تغيير سيُحفظ فور الضغط على الزر</span>
            <button type="submit" class="settings-save-btn">
                <span class="material-symbols-outlined">save</span>
                حفظ إعدادات المنشأة
            </button>
        </div>
    </div>

</form>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const latInput = document.getElementById('latInput');
    const lngInput = document.getElementById('lngInput');
    
    // إحداثيات تبوك الافتراضية
    const tabukLat = 28.3835;
    const tabukLng = 36.5662;

    // استخدام الإحداثيات المحفوظة أو إحداثيات تبوك
    let startLat = latInput.value ? parseFloat(latInput.value) : tabukLat;
    let startLng = lngInput.value ? parseFloat(lngInput.value) : tabukLng;

    // تهيئة الخريطة
    const map = L.map('hospitalMap').setView([startLat, startLng], 13);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([startLat, startLng]).addTo(map);

    // النقر على الخريطة لتحديث الإحداثيات
    map.on('click', function(e) {
        let lat = e.latlng.lat.toFixed(6);
        let lng = e.latlng.lng.toFixed(6);
        
        marker.setLatLng(e.latlng);
        latInput.value = lat;
        lngInput.value = lng;
    });

    // تحديث الخريطة عند تغيير الأرقام يدوياً
    latInput.addEventListener('input', updateMapFromInput);
    lngInput.addEventListener('input', updateMapFromInput);

    function updateMapFromInput() {
        let lat = parseFloat(latInput.value);
        let lng = parseFloat(lngInput.value);
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng]);
        }
    }
});

function getMyLocation() {
    const statusEl = document.getElementById('locationStatus');
    statusEl.className  = 'location-status location-status--loading';
    statusEl.innerHTML  = '<span class="material-symbols-outlined">autorenew</span> جاري تحديد موقعك...';

    if (!navigator.geolocation) {
        statusEl.className = 'location-status location-status--error';
        statusEl.innerHTML = '<span class="material-symbols-outlined">error</span> المتصفح لا يدعم تحديد الموقع.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            let lat = pos.coords.latitude.toFixed(6);
            let lng = pos.coords.longitude.toFixed(6);
            
            document.getElementById('latInput').value = lat;
            document.getElementById('lngInput').value = lng;
            
            // Trigger input event to update map
            document.getElementById('latInput').dispatchEvent(new Event('input'));
            
            statusEl.className = 'location-status location-status--success';
            statusEl.innerHTML = '<span class="material-symbols-outlined">check_circle</span> تم التقاط الإحداثيات! لا تنسَ الضغط على حفظ.';
        },
        function() {
            statusEl.className = 'location-status location-status--error';
            statusEl.innerHTML = '<span class="material-symbols-outlined">location_off</span> تعذّر تحديد الموقع، يرجى السماح للمتصفح أو الإدخال يدوياً.';
        }
    );
}
</script>

<?php require_once '../../includes/dashboard_footer.php'; ?>
