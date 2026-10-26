-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql106.infinityfree.com
-- Generation Time: Apr 26, 2026 at 05:27 PM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41762104_ataa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `facility_id` int(11) NOT NULL,
  `patient_name` varchar(150) DEFAULT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units_needed` int(11) NOT NULL,
  `units_secured` int(11) DEFAULT 0,
  `urgency_level` enum('normal','high','critical') DEFAULT 'high',
  `status` enum('open','fulfilled','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `appointment_date` datetime NOT NULL,
  `status` enum('pending','completed','cancelled','rejected') DEFAULT 'pending',
  `units_donated` int(11) DEFAULT 0,
  `points_earned` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('hospital','blood_bank') NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units_available` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_assessments`
--

CREATE TABLE `medical_assessments` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `has_chronic_diseases` tinyint(1) DEFAULT 0,
  `last_travel_date` date DEFAULT NULL,
  `last_donation_date` date DEFAULT NULL,
  `is_eligible` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `screening_questions`
--

CREATE TABLE `screening_questions` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `rejection_reason` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `screening_questions`
--

INSERT INTO `screening_questions` (`id`, `question_text`, `rejection_reason`, `is_active`, `created_at`) VALUES
(1, 'هل تعاني من أعراض نزلات البرد، الحمى، أو السعال حالياً؟', 'يجب أن تكون بصحة جيدة تماماً ولا تعاني من أي أعراض مرضية أو حرارة في يوم التبرع لتجنب نقل العدوى والمضاعفات.', 1, '2026-04-26 17:19:42'),
(2, 'هل قمت بأي عمل جراحي أو خلع أسنان خلال الـ 6 أشهر الماضية؟', 'يُمنع التبرع بالدم مؤقتاً بعد العمليات الجراحية أو خلع الأسنان لتجنب خطر انتقال الميكروبات إلى مجرى الدم.', 1, '2026-04-26 17:19:42'),
(3, 'هل تناولت أي مضادات حيوية خلال الـ 72 ساعة الماضية؟', 'تناول المضادات الحيوية يعني وجود عدوى بكتيرية نشطة، مما يجعلك غير مؤهل للتبرع حتى إكمال العلاج والشفاء التام لمدة 3 أيام على الأقل.', 0, '2026-04-26 17:19:42'),
(4, 'هل تعاني من أي أمراض مزمنة (كالقلب، السكري المعتمد على الأنسولين، أو أمراض الدم)؟', 'التبرع بالدم قد يكون غير آمن لمرضى القلب والسكري المعتمد على الأنسولين، كما أن أمراض الدم تمنع التبرع نهائياً لسلامتك وسلامة المريض.', 1, '2026-04-26 17:19:42'),
(5, 'هل قمت بعمل وشم (تاتو) أو حجامة خلال الـ 6 أشهر الماضية؟', 'الحجامة والوشم يزيدان من احتمالية انتقال الأمراض الفيروسية عبر الدم، لذا يجب الانتظار لمدة 6 أشهر كإجراء وقائي.', 1, '2026-04-26 17:19:42'),
(6, 'هل أنتِ حامل أو في فترة الرضاعة الطبيعية حالياً؟ (تجاهل السؤال إذا كنت ذكراً)', 'يُمنع تبرع النساء الحوامل أو المرضعات حمايةً لصحتهن وصحة أطفالهن من خطر نقص الحديد.', 1, '2026-04-26 17:19:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gender` enum('male','female','unknown') DEFAULT 'unknown',
  `role` enum('donor','patient','admin') NOT NULL DEFAULT 'donor',
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown') DEFAULT 'Unknown',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `gender`, `role`, `blood_type`, `latitude`, `longitude`, `points`, `created_at`) VALUES
(1, 'سلمان العامر', 'salman@gmail.com', '$2y$10$s6JNZeDFHh9ZEx1MiKxqVuGFv.zKFmCtgt5WSlUgJySCTbKRuXb/G', '0589753765', 'unknown', 'donor', 'A+', NULL, NULL, 0, '2026-04-26 00:40:22'),
(2, 'مدير النظام', 'admin@ataa.com', '$2y$10$OZGlIjgsUA6pxE3N5ebn3.sUejtI.mFRZNyaoCGRH/Nj5AHRQbiNe', '0500000000', 'unknown', 'admin', 'Unknown', NULL, NULL, 0, '2026-04-26 01:50:14'),
(9, 'محمد عبدالسلام', 'mohammed.abd@test.com', '$2y$10$SNrVHWaWON04Ox9XcLE/KepM3kZvgZpiNbdlekWIU.kziv1G1Da4u', '966500112233', 'unknown', 'donor', 'O+', '24.71360000', '46.67530000', 0, '2026-04-26 14:17:24'),
(10, 'خالد الشمري', 'khalid.sh@test.com', '$2y$10$/TVHJHBj0.VCS0czMRZFB.HK7hpN5UHnQhxd78s8T4eb0hhhMxMuC', '966500112244', 'unknown', 'donor', 'A-', '24.72360000', '46.68530000', 0, '2026-04-26 14:17:24'),
(11, 'عبدالله الغامدي', 'abdullah.g@test.com', '$2y$10$2Qj6ichQXa7AX8UjQ1o8Y.bgyaO0wKWXELPPWo2k53/m/0PUPjNfm', '966500112255', 'unknown', 'donor', 'AB+', '24.73360000', '46.69530000', 0, '2026-04-26 14:17:24'),
(14, 'Aryam Al-Ruwaili', 'b@gmail.com', '$2y$10$L0MSVN1oAWGu/EhRTRMfmeYZd6MUUr7MjsywM7.GYKZXrDply9NLy', '96654390871425', 'unknown', 'donor', 'O+', NULL, NULL, 0, '2026-04-26 16:39:47'),
(15, 'غصون سامي السليمين', 'rfgh1199@icloud.com', '$2y$10$UneXXLB/GeT3ZvpeXfc5He2wDJy8xWJTbb2dmIkxNCi55ohCtnNBi', '539732352', 'female', 'donor', 'B+', NULL, NULL, 0, '2026-04-26 19:39:45'),
(16, 'لوجين محمد حمدي', 'ole44789@gmail.com', '$2y$10$Ome1NgS5b8rbKgLdi2L0bes0bUVVSPJziR6N0Ryj1BoxsQpw.D2NC', '503559531', 'female', 'donor', 'A+', NULL, NULL, 0, '2026-04-26 20:04:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requester_id` (`requester_id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_facility_blood` (`facility_id`,`blood_type`);

--
-- Indexes for table `medical_assessments`
--
ALTER TABLE `medical_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`);

--
-- Indexes for table `screening_questions`
--
ALTER TABLE `screening_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_assessments`
--
ALTER TABLE `medical_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `screening_questions`
--
ALTER TABLE `screening_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blood_requests_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `blood_requests` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_assessments`
--
ALTER TABLE `medical_assessments`
  ADD CONSTRAINT `medical_assessments_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
