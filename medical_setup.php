<?php
require 'includes/db_connect.php';

try {
    $pdo->beginTransaction();

    // 1. Add gender column to users
    try {
        $pdo->query("ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'unknown') DEFAULT 'unknown' AFTER phone");
        echo "Added gender column.\n";
    } catch (Exception $e) {
        echo "Column gender might already exist.\n";
    }

    // 2. Create screening_questions table
    $pdo->query("
        CREATE TABLE IF NOT EXISTS screening_questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_text TEXT NOT NULL,
            rejection_reason TEXT NOT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created screening_questions table.\n";

    // 3. Seed screening_questions
    // We empty it first to avoid duplicates if script is run multiple times
    $pdo->query("TRUNCATE TABLE screening_questions");

    $questions = [
        [
            'q' => 'هل تعاني من أعراض نزلات البرد، الحمى، أو السعال حالياً؟',
            'r' => 'يجب أن تكون بصحة جيدة تماماً ولا تعاني من أي أعراض مرضية أو حرارة في يوم التبرع لتجنب نقل العدوى والمضاعفات.'
        ],
        [
            'q' => 'هل قمت بأي عمل جراحي أو خلع أسنان خلال الـ 6 أشهر الماضية؟',
            'r' => 'يُمنع التبرع بالدم مؤقتاً بعد العمليات الجراحية أو خلع الأسنان لتجنب خطر انتقال الميكروبات إلى مجرى الدم.'
        ],
        [
            'q' => 'هل تناولت أي مضادات حيوية خلال الـ 72 ساعة الماضية؟',
            'r' => 'تناول المضادات الحيوية يعني وجود عدوى بكتيرية نشطة، مما يجعلك غير مؤهل للتبرع حتى إكمال العلاج والشفاء التام لمدة 3 أيام على الأقل.'
        ],
        [
            'q' => 'هل تعاني من أي أمراض مزمنة (كالقلب، السكري المعتمد على الأنسولين، أو أمراض الدم)؟',
            'r' => 'التبرع بالدم قد يكون غير آمن لمرضى القلب والسكري المعتمد على الأنسولين، كما أن أمراض الدم تمنع التبرع نهائياً لسلامتك وسلامة المريض.'
        ],
        [
            'q' => 'هل قمت بعمل وشم (تاتو) أو حجامة خلال الـ 6 أشهر الماضية؟',
            'r' => 'الحجامة والوشم يزيدان من احتمالية انتقال الأمراض الفيروسية عبر الدم، لذا يجب الانتظار لمدة 6 أشهر كإجراء وقائي.'
        ],
        [
            'q' => 'هل أنتِ حامل أو في فترة الرضاعة الطبيعية حالياً؟ (تجاهل السؤال إذا كنت ذكراً)',
            'r' => 'يُمنع تبرع النساء الحوامل أو المرضعات حمايةً لصحتهن وصحة أطفالهن من خطر نقص الحديد.'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO screening_questions (question_text, rejection_reason) VALUES (?, ?)");
    foreach ($questions as $q) {
        $stmt->execute([$q['q'], $q['r']]);
    }
    echo "Seeded screening questions.\n";

    $pdo->commit();
    echo "Medical setup completed successfully.";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
