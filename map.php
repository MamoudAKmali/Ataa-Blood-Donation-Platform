<?php
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// Fetch all hospitals with coordinates
$stmt = $pdo->query("
    SELECT f.id, f.name, f.address, f.latitude, f.longitude,
           COALESCE(SUM(i.units_available), 0) as total_units,
           COUNT(DISTINCT br.id) as active_requests
    FROM facilities f
    LEFT JOIN inventory i ON i.facility_id = f.id
    LEFT JOIN blood_requests br ON br.facility_id = f.id AND br.status = 'open'
    WHERE f.type = 'hospital'
    GROUP BY f.id
");
$hospitals = $stmt->fetchAll();

// Encode for JavaScript
$hospitals_json = json_encode($hospitals, JSON_UNESCAPED_UNICODE);
?>

<!-- Hero Strip -->
<div class="map-hero">
    <div class="container">
        <div class="map-hero__inner">
            <div>
                <div class="map-hero__label">تغطية شاملة</div>
                <h1 class="map-hero__title">خريطة المراكز الطبية</h1>
                <p class="map-hero__sub">اكتشف المستشفيات القريبة منك، وحالة الاحتياج للدم في الوقت الفعلي.</p>
            </div>
            <div class="map-hero__icon">
                <span class="material-symbols-outlined">map</span>
            </div>
        </div>
    </div>
</div>

<section class="section pt-0">
    <div class="container">
        <!-- Map App Container -->
        <div class="map-app">
            
            <!-- Sidebar List -->
            <div class="map-sidebar">
                <div class="map-sidebar__header">
                    <h3 class="map-sidebar__title">
                        <span class="material-symbols-outlined">format_list_bulleted</span>
                        المستشفيات المعتمدة
                    </h3>
                    <span class="map-sidebar__count"><?php echo count($hospitals); ?></span>
                </div>
                
                <div class="map-sidebar__list" id="hospitals-list">
                    <?php if (count($hospitals) > 0): ?>
                        <?php foreach ($hospitals as $h): ?>
                        <div class="map-card" onclick="focusHospital(<?php echo $h['id']; ?>)">
                            <div class="map-card__icon">
                                <span class="material-symbols-outlined">local_hospital</span>
                            </div>
                            <div class="map-card__content">
                                <h4 class="map-card__name"><?php echo htmlspecialchars($h['name']); ?></h4>
                                <p class="map-card__address">
                                    <span class="material-symbols-outlined">location_on</span>
                                    <?php echo htmlspecialchars(mb_substr($h['address'], 0, 40, 'UTF-8')); ?>
                                </p>
                                <div class="map-card__tags">
                                    <span class="map-tag map-tag--info">
                                        <?php echo $h['total_units']; ?> وحدة
                                    </span>
                                    <?php if ($h['active_requests'] > 0): ?>
                                        <span class="map-tag map-tag--urgent">
                                            <span class="material-symbols-outlined">emergency</span>
                                            <?php echo $h['active_requests']; ?> طلب عاجل
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="map-sidebar__empty">
                            <span class="material-symbols-outlined">location_off</span>
                            <p>لا توجد مستشفيات مسجلة.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- The Map -->
            <div class="map-view">
                <div id="map"></div>
            </div>

        </div>
    </div>
</section>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* ── Hero Strip ── */
.map-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    padding: 50px 0;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
}

.map-hero::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(226, 232, 240, 0.05) 0%, transparent 70%);
    border-radius: 50%;
}

.map-hero__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 1;
}

.map-hero__label {
    display: inline-block;
    background: rgba(255,255,255,0.1);
    color: #94a3b8;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 12px;
    letter-spacing: 0.5px;
}

.map-hero__title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 8px;
    line-height: 1.2;
}

.map-hero__sub {
    color: #94a3b8;
    font-size: 15px;
    max-width: 500px;
}

.map-hero__icon {
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(10deg);
}

.map-hero__icon .material-symbols-outlined {
    font-size: 56px;
    color: #334155;
}

/* ── Map App Container ── */
.map-app {
    display: flex;
    height: 650px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

/* ── Sidebar ── */
.map-sidebar {
    width: 360px;
    background: #f8fafc;
    border-inline-end: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    z-index: 10;
}

.map-sidebar__header {
    padding: 20px 24px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.map-sidebar__title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}

.map-sidebar__title .material-symbols-outlined {
    color: #b91c1c;
    font-size: 20px;
}

.map-sidebar__count {
    background: #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
}

.map-sidebar__list {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ── Map Card ── */
.map-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    gap: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.map-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transform: translateY(-2px);
}

.map-card__icon {
    width: 44px;
    height: 44px;
    background: #f1f5f9;
    color: #64748b;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s;
}

.map-card:hover .map-card__icon {
    background: #fef2f2;
    color: #991b1b;
}

.map-card__content { flex: 1; min-width: 0; }

.map-card__name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 4px;
    line-height: 1.3;
}

.map-card__address {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 10px;
}

.map-card__address .material-symbols-outlined { font-size: 14px; }

.map-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.map-tag {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.map-tag .material-symbols-outlined { font-size: 14px; }
.map-tag--info { background: #f1f5f9; color: #475569; }
.map-tag--urgent { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

.map-sidebar__empty {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}

.map-sidebar__empty .material-symbols-outlined {
    font-size: 40px;
    margin-bottom: 10px;
    opacity: 0.5;
}

/* ── Map View ── */
.map-view {
    flex: 1;
    position: relative;
    background: #e2e8f0;
}

#map {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 1;
}

/* ── Custom Leaflet Popup ── */
.leaflet-popup-content-wrapper {
    border-radius: 12px !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    padding: 0 !important;
    overflow: hidden;
}
.leaflet-popup-content {
    margin: 0 !important;
    font-family: 'Cairo', sans-serif !important;
}
.leaflet-popup-tip-container { margin-top: -1px; }

.custom-popup {
    direction: rtl;
    text-align: right;
    width: 260px;
}

.custom-popup__header {
    background: #0f172a;
    padding: 16px;
    color: white;
}

.custom-popup__title {
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 4px 0;
    line-height: 1.3;
}

.custom-popup__address {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 4px;
}

.custom-popup__body {
    padding: 16px;
    background: #ffffff;
}

.custom-popup__stats {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.custom-popup__btn {
    display: block;
    width: 100%;
    text-align: center;
    background: #991b1b;
    color: white !important;
    text-decoration: none !important;
    padding: 10px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    transition: background 0.2s;
}
.custom-popup__btn:hover { background: #7f1d1d; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .map-sidebar { width: 300px; }
}

@media (max-width: 768px) {
    .map-hero { padding: 30px 0; margin-bottom: 24px; }
    .map-hero__icon { display: none; }
    
    .map-app {
        flex-direction: column-reverse;
        height: auto;
    }
    
    .map-view {
        height: 400px;
        flex: none;
    }
    
    .map-sidebar {
        width: 100%;
        height: 350px;
        border-inline-end: none;
        border-top: 1px solid #e2e8f0;
    }
}
</style>

<script>
const hospitals = <?php echo $hospitals_json; ?>;
const markers = {};

// Default center: Saudi Arabia
const map = L.map('map', {
    zoomControl: false // We will move it
}).setView([24.7136, 46.6753], 6);

// Move zoom control to bottom left
L.control.zoom({ position: 'bottomleft' }).addTo(map);

L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenStreetMap, © CartoDB',
    maxZoom: 18
}).addTo(map);

// Custom Modern Icon
const redIcon = L.divIcon({
    html: `
        <div style="
            background: #e11d48;
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(225,29,72,0.4);
            transition: transform 0.2s;
        ">
            <span class="material-symbols-outlined" style="color:white; font-size:18px;">local_hospital</span>
        </div>
    `,
    className: '',
    iconSize: [36, 36],
    iconAnchor: [18, 18],
    popupAnchor: [0, -20]
});

hospitals.forEach(function(h) {
    const lat = parseFloat(h.latitude) || (24.7 + Math.random() * 2);
    const lng = parseFloat(h.longitude) || (46.6 + Math.random() * 2);

    const marker = L.marker([lat, lng], { icon: redIcon }).addTo(map);

    const popupContent = `
        <div class="custom-popup">
            <div class="custom-popup__header">
                <h4 class="custom-popup__title">${h.name}</h4>
                <p class="custom-popup__address">
                    <span class="material-symbols-outlined" style="font-size:14px">location_on</span>
                    ${h.address ? h.address.substring(0,40) : 'العنوان غير محدد'}
                </p>
            </div>
            <div class="custom-popup__body">
                <div class="custom-popup__stats">
                    <span class="map-tag map-tag--info">${h.total_units} وحدة متاحة</span>
                    ${h.active_requests > 0 ? `<span class="map-tag map-tag--urgent">${h.active_requests} طلب عاجل</span>` : ''}
                </div>
                <a href="/Blood_Donations/auth/register.php" class="custom-popup__btn">تبرع هنا</a>
            </div>
        </div>
    `;

    marker.bindPopup(popupContent, { maxWidth: 260, minWidth: 260, closeButton: false });
    markers[h.id] = { marker, lat, lng };
});

function focusHospital(id) {
    if (markers[id]) {
        map.flyTo([markers[id].lat, markers[id].lng], 15, { 
            duration: 1.5,
            easeLinearity: 0.25
        });
        
        // Slight delay to allow map to fly before opening popup
        setTimeout(() => {
            markers[id].marker.openPopup();
        }, 300);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
