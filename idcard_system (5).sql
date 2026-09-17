-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2026 at 09:20 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `idcard_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `template_key` varchar(50) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `primary_color` varchar(7) NOT NULL DEFAULT '#1a3fa0',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`id`, `name`, `code`, `template_key`, `logo_path`, `primary_color`, `created_at`) VALUES
(1, 'College of Engineering', 'ENG', 'engineering', '/assets/images/coe.jpg', '#c8a96e', '2026-08-07 09:53:29'),
(2, 'College of Management and Social Sciences', 'CMSS', 'Management', '/assets/images/cmss.jpg', '#009a4e', '2026-08-07 09:53:29'),
(3, 'College of Science and Technology', 'CST', 'science', '/assets/images/cst.jpg', '#ed1c24', '2026-08-07 09:53:29'),
(4, 'College of Leadership and Development Studies', 'CLDS', 'leadership', '/assets/images/clds.jpg', '#1a3fa0', '2026-09-06 22:48:18');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `college_id`, `name`, `code`, `created_at`) VALUES
(1, 1, 'Chemical Engineering', 'CHE', '2026-09-06 22:48:58'),
(2, 1, 'Civil Engineering', 'CVE', '2026-09-06 22:48:58'),
(3, 1, 'Electrical and Information Engineering', 'EIE', '2026-09-06 22:48:58'),
(4, 1, 'Mechanical Engineering', 'MEE', '2026-09-06 22:48:58'),
(5, 1, 'Petroleum Engineering', 'PET', '2026-09-06 22:48:58'),
(6, 2, 'Accounting', 'ACC', '2026-09-06 22:48:58'),
(7, 2, 'Banking and Finance', 'BNF', '2026-09-06 22:48:58'),
(8, 2, 'Business Management', 'BMG', '2026-09-06 22:48:58'),
(9, 2, 'Economics', 'ECO', '2026-09-06 22:48:58'),
(10, 2, 'Mass Communication', 'MAC', '2026-09-06 22:48:58'),
(11, 2, 'Sociology', 'SOC', '2026-09-06 22:48:58'),
(12, 3, 'Architecture', 'ARC', '2026-09-06 22:48:58'),
(13, 3, 'Building Technology', 'BLT', '2026-09-06 22:48:58'),
(14, 3, 'Estate Management', 'ESM', '2026-09-06 22:48:58'),
(15, 3, 'Biological Sciences', 'BIO', '2026-09-06 22:48:58'),
(16, 3, 'Biochemistry', 'BCH', '2026-09-06 22:48:58'),
(17, 3, 'Chemistry', 'CHEM', '2026-09-06 22:48:58'),
(18, 3, 'Computer and Information Sciences', 'CIS', '2026-09-06 22:48:58'),
(19, 3, 'Mathematics', 'MAT', '2026-09-06 22:48:58'),
(20, 3, 'Physics', 'PHY', '2026-09-06 22:48:58'),
(21, 4, 'Political Science and International Relations', 'PSIR', '2026-09-06 22:48:58'),
(22, 4, 'Psychology', 'PSY', '2026-09-06 22:48:58'),
(23, 4, 'Languages and General Studies', 'LGS', '2026-09-06 22:48:58'),
(24, 4, 'Leadership Studies', 'LDS', '2026-09-06 22:48:58');

-- --------------------------------------------------------

--
-- Table structure for table `idcardapplicationevents`
--

CREATE TABLE `idcardapplicationevents` (
  `eventid` bigint(20) UNSIGNED NOT NULL,
  `applicationid` int(11) NOT NULL,
  `eventtype` varchar(50) NOT NULL,
  `actorid` varchar(100) NOT NULL,
  `actorrole` varchar(50) NOT NULL,
  `occurredat` datetime NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `idcardapplicationevents`
--
DELIMITER $$
CREATE TRIGGER `idcardevents_no_delete` BEFORE DELETE ON `idcardapplicationevents` FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Application events are append-only'
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `idcardevents_no_update` BEFORE UPDATE ON `idcardapplicationevents` FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Application events are append-only'
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `idcardapplications`
--

CREATE TABLE `idcardapplications` (
  `applicationid` int(11) NOT NULL,
  `referencenumber` varchar(50) NOT NULL,
  `matricnumber` varchar(50) NOT NULL,
  `applicationtype` enum('loststolen','damaged') NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'submitted',
  `photopath` varchar(255) NOT NULL,
  `photosizebytes` int(11) NOT NULL,
  `photomimetype` varchar(50) NOT NULL,
  `documenttype` varchar(255) DEFAULT NULL,
  `documentpath` varchar(255) DEFAULT NULL,
  `documentsizebytes` int(11) DEFAULT NULL,
  `documentmimetype` varchar(50) DEFAULT NULL,
  `rejectionreason` text DEFAULT NULL,
  `reviewedby` varchar(100) DEFAULT NULL,
  `approvedfee` decimal(10,2) DEFAULT NULL,
  `paymentdeadline` date DEFAULT NULL,
  `printmethod` varchar(30) DEFAULT NULL,
  `printedat` datetime DEFAULT NULL,
  `pickupdate` date DEFAULT NULL,
  `windowstart` time DEFAULT NULL,
  `windowend` time DEFAULT NULL,
  `pickuplocation` varchar(255) DEFAULT NULL,
  `collectionpin` varchar(10) DEFAULT NULL,
  `collectioninstructions` text DEFAULT NULL,
  `readyforpickupat` datetime DEFAULT NULL,
  `closedby` varchar(100) DEFAULT NULL,
  `submittedat` datetime NOT NULL,
  `createdat` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT NULL,
  `closedat` datetime DEFAULT NULL,
  `cancelledat` datetime DEFAULT NULL,
  `paymentstatus` enum('paid','failed') DEFAULT NULL,
  `paidat` datetime DEFAULT NULL,
  `collectedat` datetime DEFAULT NULL,
  `collectedby` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `idcardapplications`
--

INSERT INTO `idcardapplications` (`applicationid`, `referencenumber`, `matricnumber`, `applicationtype`, `status`, `photopath`, `photosizebytes`, `photomimetype`, `documenttype`, `documentpath`, `documentsizebytes`, `documentmimetype`, `rejectionreason`, `reviewedby`, `approvedfee`, `paymentdeadline`, `printmethod`, `printedat`, `pickupdate`, `windowstart`, `windowend`, `pickuplocation`, `collectionpin`, `collectioninstructions`, `readyforpickupat`, `closedby`, `submittedat`, `createdat`, `updatedat`, `closedat`, `cancelledat`, `paymentstatus`, `paidat`, `collectedat`, `collectedby`) VALUES
(1, 'IDC-20260504-68D34', '123456', 'damaged', 'rejected', 'idcard/photo-69f897fd68a17.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, 'STAFF001', 5000.00, '2026-09-21', 'download', '2026-09-14 10:01:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 13:58:37', '2026-05-04 13:58:37', '2026-09-14 10:01:31', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'IDC-20260504-02073', '2301030', 'damaged', 'submitted', 'idcard/photo-69f8996c01d4a.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 14:04:44', '2026-05-04 14:04:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'IDC-20260504-07BA4', '2301', 'damaged', 'submitted', 'idcard/photo-69f8a160e6568.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 14:38:41', '2026-05-04 14:38:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'IDC-20260504-0243C', '23014', 'loststolen', 'submitted', 'idcard/photo-69f8a18b02121.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 14:39:23', '2026-05-04 14:39:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'IDC-20260915-CEC86', 'CU/ENG/2026/010', 'loststolen', 'printed', 'uploads/idcard/photo-6aa8e456cbdc16.68024346.jpg', 80544, 'image/jpeg', 'WECC 2026 FLIER.pdf', 'uploads/idcard/document-6aa8e456cdf311.57135708.pdf', 1935169, 'application/pdf', NULL, 'STAFF001', 5000.00, '2026-09-22', 'local', '2026-09-16 18:05:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 07:23:18', '2026-09-15 07:23:18', '2026-09-16 18:05:05', NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'IDC-20260915-2E21A', 'CU/CMSS/2026/002', 'damaged', 'printed', 'uploads/idcard/photo-6aa8e5ac2cca30.67071751.jpg', 368139, 'image/jpeg', 'WECC FIRST INSTAGRAM SCREENSHOT.jpeg', 'uploads/idcard/document-6aa8e5ac2de0b7.82554480.jpg', 87001, 'image/jpeg', NULL, 'STAFF001', 3000.00, '2026-09-22', 'local', '2026-09-16 18:05:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 07:29:00', '2026-09-15 07:29:00', '2026-09-16 18:05:05', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'IDC-20260915-2F6D3', 'CU/ENG/2026/008', 'loststolen', 'printed', 'uploads/idcard/photo-6aa930b02863e3.62552038.png', 1830412, 'image/png', 'RESEARCH CALL.pdf', 'uploads/idcard/document-6aa930b02eecc2.16765930.pdf', 323489, 'application/pdf', NULL, 'STAFF001', 5000.00, '2026-09-22', 'local', '2026-09-16 18:05:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 12:49:04', '2026-09-15 12:49:04', '2026-09-16 18:05:05', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `idcardmigrations`
--

CREATE TABLE `idcardmigrations` (
  `version` varchar(80) NOT NULL,
  `appliedat` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `idcardmigrations`
--

INSERT INTO `idcardmigrations` (`version`, `appliedat`) VALUES
('002_replacement_lifecycle', '2026-09-16 22:24:04');

-- --------------------------------------------------------

--
-- Table structure for table `idcardpaymentattempts`
--

CREATE TABLE `idcardpaymentattempts` (
  `attemptid` bigint(20) UNSIGNED NOT NULL,
  `paymentreference` varchar(100) NOT NULL,
  `matricnumber` varchar(50) NOT NULL,
  `applicationtype` enum('loststolen','damaged') NOT NULL,
  `photopath` varchar(255) NOT NULL,
  `photosizebytes` int(11) NOT NULL,
  `photomimetype` varchar(50) NOT NULL,
  `paymentoptioncode` varchar(50) NOT NULL,
  `paymentoptionname` varchar(100) NOT NULL,
  `baseamount` decimal(10,2) NOT NULL,
  `chargeamount` decimal(10,2) NOT NULL,
  `totalamount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'NGN',
  `provider` varchar(50) NOT NULL DEFAULT 'local-simulator',
  `status` enum('pending','cancelled','paid','failed') NOT NULL DEFAULT 'pending',
  `applicationid` int(11) DEFAULT NULL,
  `failuremessage` varchar(255) DEFAULT NULL,
  `createdat` datetime NOT NULL,
  `completedat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `idcardrefunds`
--

CREATE TABLE `idcardrefunds` (
  `refundid` bigint(20) UNSIGNED NOT NULL,
  `applicationid` int(11) NOT NULL,
  `status` enum('requested','approved','credited') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `requestedat` datetime NOT NULL,
  `requestedby` varchar(100) NOT NULL,
  `approvedat` datetime DEFAULT NULL,
  `approvedby` varchar(100) DEFAULT NULL,
  `creditedat` datetime DEFAULT NULL,
  `creditedby` varchar(100) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `idcardsettings`
--

CREATE TABLE `idcardsettings` (
  `settingid` int(11) NOT NULL,
  `applicationtype` enum('loststolen','damaged') NOT NULL,
  `approvedfee` decimal(10,2) NOT NULL,
  `expirydays` int(11) NOT NULL DEFAULT 7,
  `cooldowndays` int(11) NOT NULL DEFAULT 0,
  `maxfilesizebytes` int(11) NOT NULL DEFAULT 2097152,
  `allowedphotomimetypes` varchar(255) NOT NULL DEFAULT 'image/jpeg,image/png',
  `alloweddocumentmimetypes` varchar(255) NOT NULL DEFAULT 'image/jpeg,image/png,application/pdf',
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `createdat` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `idcardsettings`
--

INSERT INTO `idcardsettings` (`settingid`, `applicationtype`, `approvedfee`, `expirydays`, `cooldowndays`, `maxfilesizebytes`, `allowedphotomimetypes`, `alloweddocumentmimetypes`, `isactive`, `createdat`, `updatedat`) VALUES
(1, 'loststolen', 5000.00, 7, 0, 2097152, 'image/jpeg,image/png', 'image/jpeg,image/png,application/pdf', 1, '2026-09-15 07:22:21', NULL),
(2, 'damaged', 5000.00, 7, 0, 2097152, 'image/jpeg,image/png', 'image/jpeg,image/png,application/pdf', 1, '2026-09-15 07:22:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `id_card_batches`
--

CREATE TABLE `id_card_batches` (
  `id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL,
  `generated_by` varchar(100) DEFAULT NULL,
  `student_count` int(11) NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `print_status` enum('awaiting_print','printed') NOT NULL DEFAULT 'awaiting_print',
  `print_method` enum('local','download') DEFAULT NULL,
  `printed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `id_card_batches`
--

INSERT INTO `id_card_batches` (`id`, `college_id`, `generated_by`, `student_count`, `pdf_path`, `status`, `created_at`, `print_status`, `print_method`, `printed_at`) VALUES
(1, 1, 'web', 9, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260828_133924.pdf', 'completed', '2026-08-28 11:39:24', 'awaiting_print', NULL, NULL),
(2, 1, 'web', 9, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260828_140030.pdf', 'completed', '2026-08-28 12:00:30', 'awaiting_print', NULL, NULL),
(3, 1, 'selective', 2, 'C:\\xampp\\htdocs\\idcard-system/output/SEL_20260828_140336.pdf', 'completed', '2026-08-28 12:03:36', 'awaiting_print', NULL, NULL),
(4, 1, 'web', 9, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260828_155806.pdf', 'completed', '2026-08-28 13:58:06', 'awaiting_print', NULL, NULL),
(5, 1, 'selective', 1, 'C:\\xampp\\htdocs\\idcard-system/output/SEL_20260828_155828.pdf', 'completed', '2026-08-28 13:58:28', 'awaiting_print', NULL, NULL),
(6, 1, 'web', 9, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260828_155911.pdf', 'completed', '2026-08-28 13:59:11', 'awaiting_print', NULL, NULL),
(7, 1, 'web', 9, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260828_165044.pdf', 'completed', '2026-08-28 14:50:44', 'awaiting_print', NULL, NULL),
(8, 2, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/LAW_20260828_165100.pdf', 'completed', '2026-08-28 14:51:00', 'awaiting_print', NULL, NULL),
(9, 1, 'web', 9, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260828_165112.pdf', 'completed', '2026-08-28 14:51:12', 'awaiting_print', NULL, NULL),
(10, 1, 'selective', 2, 'C:\\xampp\\htdocs\\idcard-system/output/SEL_20260828_165155.pdf', 'completed', '2026-08-28 14:51:55', 'awaiting_print', NULL, NULL),
(11, 1, 'web', 9, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_002030.pdf', 'completed', '2026-09-06 22:20:30', 'awaiting_print', NULL, NULL),
(12, 1, 'web', 5, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_002738.pdf', 'completed', '2026-09-06 22:27:38', 'awaiting_print', NULL, NULL),
(13, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_002744.pdf', 'completed', '2026-09-06 22:27:44', 'awaiting_print', NULL, NULL),
(14, 1, 'web', 5, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_002752.pdf', 'completed', '2026-09-06 22:27:52', 'awaiting_print', NULL, NULL),
(15, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_002808.pdf', 'completed', '2026-09-06 22:28:08', 'awaiting_print', NULL, NULL),
(16, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_002822.pdf', 'completed', '2026-09-06 22:28:22', 'awaiting_print', NULL, NULL),
(17, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_011644.pdf', 'completed', '2026-09-06 23:16:44', 'awaiting_print', NULL, NULL),
(18, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_011710.pdf', 'completed', '2026-09-06 23:17:10', 'awaiting_print', NULL, NULL),
(19, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_011730.pdf', 'completed', '2026-09-06 23:17:30', 'awaiting_print', NULL, NULL),
(20, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_012802.pdf', 'completed', '2026-09-06 23:28:02', 'awaiting_print', NULL, NULL),
(21, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260907_012810.pdf', 'completed', '2026-09-06 23:28:10', 'awaiting_print', NULL, NULL),
(22, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260914_002046.pdf', 'completed', '2026-09-13 22:20:46', 'awaiting_print', NULL, NULL),
(23, 1, 'selective', 1, 'C:\\xampp\\htdocs\\idcard-system/output/SEL_20260914_110957.pdf', 'completed', '2026-09-14 09:09:57', 'awaiting_print', NULL, NULL),
(24, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260914_124849.pdf', 'completed', '2026-09-14 10:48:49', 'awaiting_print', NULL, NULL),
(25, 1, 'web', 1, 'C:\\xampp\\htdocs\\idcard-system/output/ENG_20260914_124948.pdf', 'completed', '2026-09-14 10:49:48', 'awaiting_print', NULL, NULL),
(26, 1, 'selective', 1, 'C:\\xampp\\htdocs\\idcard-system/output/SEL_20260914_125023.pdf', 'completed', '2026-09-14 10:50:23', 'awaiting_print', NULL, NULL),
(27, 1, 'selective', 2, 'C:\\xampp\\htdocs\\idcard-system/output/SEL_20260914_125044.pdf', 'completed', '2026-09-14 10:50:44', 'awaiting_print', NULL, NULL),
(28, 1, 'selective', 3, 'C:\\xampp\\htdocs\\idcard-system/output/SEL_20260914_125103.pdf', 'completed', '2026-09-14 10:51:03', 'awaiting_print', NULL, NULL),
(29, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_132744.pdf', 'completed', '2026-09-14 11:27:44', 'awaiting_print', NULL, NULL),
(30, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161538.pdf', 'completed', '2026-09-14 14:15:38', 'awaiting_print', NULL, NULL),
(31, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161653.pdf', 'completed', '2026-09-14 14:16:53', 'awaiting_print', NULL, NULL),
(32, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161707.pdf', 'completed', '2026-09-14 14:17:07', 'awaiting_print', NULL, NULL),
(33, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161708.pdf', 'completed', '2026-09-14 14:17:08', 'awaiting_print', NULL, NULL),
(34, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161715.pdf', 'completed', '2026-09-14 14:17:15', 'awaiting_print', NULL, NULL),
(35, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161716.pdf', 'completed', '2026-09-14 14:17:16', 'awaiting_print', NULL, NULL),
(36, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161735.pdf', 'completed', '2026-09-14 14:17:35', 'awaiting_print', NULL, NULL),
(37, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161759.pdf', 'completed', '2026-09-14 14:17:59', 'awaiting_print', NULL, NULL),
(38, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161802.pdf', 'completed', '2026-09-14 14:18:02', 'awaiting_print', NULL, NULL),
(39, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161807.pdf', 'completed', '2026-09-14 14:18:07', 'awaiting_print', NULL, NULL),
(40, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161810.pdf', 'completed', '2026-09-14 14:18:10', 'awaiting_print', NULL, NULL),
(41, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_161859.pdf', 'completed', '2026-09-14 14:18:59', 'awaiting_print', NULL, NULL),
(42, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_163619.pdf', 'completed', '2026-09-14 14:36:19', 'awaiting_print', NULL, NULL),
(43, 2, 'selective', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/SEL_20260915_065811.pdf', 'completed', '2026-09-15 04:58:11', 'awaiting_print', NULL, NULL),
(44, 1, 'temporary', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260915_071611.pdf', 'completed', '2026-09-15 05:16:11', 'printed', 'local', '2026-09-15 06:16:38'),
(45, 1, 'temporary', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260915_071934.pdf', 'completed', '2026-09-15 05:19:34', 'printed', 'local', '2026-09-15 06:19:53'),
(46, 1, 'selective', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/SEL_20260915_072140.pdf', 'completed', '2026-09-15 05:21:40', 'awaiting_print', NULL, NULL),
(47, 1, 'selective', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/SEL_20260915_072504.pdf', 'completed', '2026-09-15 05:25:04', 'printed', 'local', '2026-09-15 06:25:37'),
(48, 2, 'awaiting-print', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/CMSS_20260915_081149.pdf', 'completed', '2026-09-15 06:11:49', 'printed', 'local', '2026-09-15 07:12:17'),
(49, 1, 'awaiting-print', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260915_082739.pdf', 'completed', '2026-09-15 06:27:39', 'awaiting_print', NULL, NULL),
(50, 1, 'awaiting-print', 2, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260915_082943.pdf', 'completed', '2026-09-15 06:29:43', 'awaiting_print', NULL, NULL),
(51, 2, 'awaiting-print', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/CMSS_20260915_082953.pdf', 'completed', '2026-09-15 06:29:53', 'awaiting_print', NULL, NULL),
(52, 1, 'permanent', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260915_162229.pdf', 'completed', '2026-09-15 14:22:29', 'printed', 'local', '2026-09-15 15:22:40'),
(53, 1, 'awaiting-print', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260916_190436.pdf', 'completed', '2026-09-16 17:04:36', 'awaiting_print', NULL, NULL),
(54, 1, 'awaiting-print', 3, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260916_190450.pdf', 'completed', '2026-09-16 17:04:50', 'printed', 'local', '2026-09-16 18:05:05');

-- --------------------------------------------------------

--
-- Table structure for table `id_card_batch_items`
--

CREATE TABLE `id_card_batch_items` (
  `id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('success','skipped','failed') NOT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `applicationid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `id_card_batch_items`
--

INSERT INTO `id_card_batch_items` (`id`, `batch_id`, `student_id`, `status`, `error_message`, `created_at`, `applicationid`) VALUES
(1, 1, 10, 'success', NULL, '2026-08-28 11:39:25', NULL),
(2, 1, 3, 'success', NULL, '2026-08-28 11:39:25', NULL),
(3, 1, 9, 'success', NULL, '2026-08-28 11:39:25', NULL),
(4, 1, 8, 'success', NULL, '2026-08-28 11:39:25', NULL),
(5, 1, 6, 'success', NULL, '2026-08-28 11:39:25', NULL),
(6, 1, 1, 'success', NULL, '2026-08-28 11:39:26', NULL),
(7, 1, 5, 'success', NULL, '2026-08-28 11:39:26', NULL),
(8, 1, 7, 'success', NULL, '2026-08-28 11:39:26', NULL),
(9, 1, 4, 'success', NULL, '2026-08-28 11:39:26', NULL),
(10, 2, 10, 'success', NULL, '2026-08-28 12:00:32', NULL),
(11, 2, 3, 'success', NULL, '2026-08-28 12:00:32', NULL),
(12, 2, 9, 'success', NULL, '2026-08-28 12:00:32', NULL),
(13, 2, 8, 'success', NULL, '2026-08-28 12:00:33', NULL),
(14, 2, 6, 'success', NULL, '2026-08-28 12:00:33', NULL),
(15, 2, 1, 'success', NULL, '2026-08-28 12:00:33', NULL),
(16, 2, 5, 'success', NULL, '2026-08-28 12:00:34', NULL),
(17, 2, 7, 'success', NULL, '2026-08-28 12:00:34', NULL),
(18, 2, 4, 'success', NULL, '2026-08-28 12:00:34', NULL),
(19, 3, 1, 'success', NULL, '2026-08-28 12:03:36', NULL),
(20, 3, 2, 'success', NULL, '2026-08-28 12:03:37', NULL),
(21, 4, 10, 'success', NULL, '2026-08-28 13:58:06', NULL),
(22, 4, 3, 'success', NULL, '2026-08-28 13:58:06', NULL),
(23, 4, 9, 'success', NULL, '2026-08-28 13:58:07', NULL),
(24, 4, 8, 'success', NULL, '2026-08-28 13:58:07', NULL),
(25, 4, 6, 'success', NULL, '2026-08-28 13:58:07', NULL),
(26, 4, 1, 'success', NULL, '2026-08-28 13:58:07', NULL),
(27, 4, 5, 'success', NULL, '2026-08-28 13:58:07', NULL),
(28, 4, 7, 'success', NULL, '2026-08-28 13:58:07', NULL),
(29, 4, 4, 'success', NULL, '2026-08-28 13:58:08', NULL),
(30, 5, 1, 'success', NULL, '2026-08-28 13:58:30', NULL),
(31, 6, 10, 'success', NULL, '2026-08-28 13:59:11', NULL),
(32, 6, 3, 'success', NULL, '2026-08-28 13:59:11', NULL),
(33, 6, 9, 'success', NULL, '2026-08-28 13:59:11', NULL),
(34, 6, 8, 'success', NULL, '2026-08-28 13:59:11', NULL),
(35, 6, 6, 'success', NULL, '2026-08-28 13:59:12', NULL),
(36, 6, 1, 'success', NULL, '2026-08-28 13:59:12', NULL),
(37, 6, 5, 'success', NULL, '2026-08-28 13:59:12', NULL),
(38, 6, 7, 'success', NULL, '2026-08-28 13:59:12', NULL),
(39, 6, 4, 'success', NULL, '2026-08-28 13:59:12', NULL),
(40, 7, 10, 'success', NULL, '2026-08-28 14:50:45', NULL),
(41, 7, 3, 'success', NULL, '2026-08-28 14:50:46', NULL),
(42, 7, 9, 'success', NULL, '2026-08-28 14:50:47', NULL),
(43, 7, 8, 'success', NULL, '2026-08-28 14:50:47', NULL),
(44, 7, 6, 'success', NULL, '2026-08-28 14:50:47', NULL),
(45, 7, 1, 'success', NULL, '2026-08-28 14:50:48', NULL),
(46, 7, 5, 'success', NULL, '2026-08-28 14:50:49', NULL),
(47, 7, 7, 'success', NULL, '2026-08-28 14:50:49', NULL),
(48, 7, 4, 'success', NULL, '2026-08-28 14:50:49', NULL),
(49, 8, 2, 'success', NULL, '2026-08-28 14:51:00', NULL),
(50, 9, 10, 'success', NULL, '2026-08-28 14:51:13', NULL),
(51, 9, 3, 'success', NULL, '2026-08-28 14:51:13', NULL),
(52, 9, 9, 'success', NULL, '2026-08-28 14:51:14', NULL),
(53, 9, 8, 'success', NULL, '2026-08-28 14:51:14', NULL),
(54, 9, 6, 'success', NULL, '2026-08-28 14:51:14', NULL),
(55, 9, 1, 'success', NULL, '2026-08-28 14:51:14', NULL),
(56, 9, 5, 'success', NULL, '2026-08-28 14:51:15', NULL),
(57, 9, 7, 'success', NULL, '2026-08-28 14:51:22', NULL),
(58, 9, 4, 'success', NULL, '2026-08-28 14:51:23', NULL),
(59, 10, 1, 'success', NULL, '2026-08-28 14:51:56', NULL),
(60, 10, 2, 'success', NULL, '2026-08-28 14:51:56', NULL),
(61, 11, 10, 'success', NULL, '2026-09-06 22:20:32', NULL),
(62, 11, 3, 'success', NULL, '2026-09-06 22:20:33', NULL),
(63, 11, 9, 'success', NULL, '2026-09-06 22:20:33', NULL),
(64, 11, 8, 'success', NULL, '2026-09-06 22:20:33', NULL),
(65, 11, 6, 'success', NULL, '2026-09-06 22:20:33', NULL),
(66, 11, 1, 'success', NULL, '2026-09-06 22:20:33', NULL),
(67, 11, 5, 'success', NULL, '2026-09-06 22:20:34', NULL),
(68, 11, 7, 'success', NULL, '2026-09-06 22:20:34', NULL),
(69, 11, 4, 'success', NULL, '2026-09-06 22:20:34', NULL),
(70, 12, 3, 'success', NULL, '2026-09-06 22:27:38', NULL),
(71, 12, 1, 'success', NULL, '2026-09-06 22:27:38', NULL),
(72, 12, 5, 'success', NULL, '2026-09-06 22:27:38', NULL),
(73, 12, 7, 'success', NULL, '2026-09-06 22:27:39', NULL),
(74, 12, 4, 'success', NULL, '2026-09-06 22:27:39', NULL),
(75, 13, 6, 'success', NULL, '2026-09-06 22:27:44', NULL),
(76, 14, 3, 'success', NULL, '2026-09-06 22:27:54', NULL),
(77, 14, 1, 'success', NULL, '2026-09-06 22:27:54', NULL),
(78, 14, 5, 'success', NULL, '2026-09-06 22:27:54', NULL),
(79, 14, 7, 'success', NULL, '2026-09-06 22:27:54', NULL),
(80, 14, 4, 'success', NULL, '2026-09-06 22:27:54', NULL),
(81, 15, 6, 'success', NULL, '2026-09-06 22:28:10', NULL),
(82, 16, 6, 'success', NULL, '2026-09-06 22:28:23', NULL),
(83, 17, 4, 'success', NULL, '2026-09-06 23:16:45', NULL),
(84, 18, 5, 'success', NULL, '2026-09-06 23:17:10', NULL),
(85, 19, 5, 'success', NULL, '2026-09-06 23:17:30', NULL),
(86, 20, 5, 'success', NULL, '2026-09-06 23:28:02', NULL),
(87, 21, 1, 'success', NULL, '2026-09-06 23:28:10', NULL),
(88, 22, 5, 'success', NULL, '2026-09-13 22:20:48', NULL),
(89, 23, 1, 'success', NULL, '2026-09-14 09:09:57', NULL),
(90, 24, 4, 'success', NULL, '2026-09-14 10:48:51', NULL),
(91, 25, 5, 'success', NULL, '2026-09-14 10:49:48', NULL),
(92, 26, 1, 'success', NULL, '2026-09-14 10:50:24', NULL),
(93, 27, 3, 'success', NULL, '2026-09-14 10:50:45', NULL),
(94, 27, 1, 'success', NULL, '2026-09-14 10:50:45', NULL),
(95, 28, 3, 'success', NULL, '2026-09-14 10:51:03', NULL),
(96, 28, 1, 'success', NULL, '2026-09-14 10:51:03', NULL),
(97, 28, 2, 'success', NULL, '2026-09-14 10:51:04', NULL),
(98, 29, 4, 'success', NULL, '2026-09-14 11:27:44', NULL),
(99, 30, 4, 'success', NULL, '2026-09-14 14:15:39', NULL),
(100, 31, 4, 'success', NULL, '2026-09-14 14:16:53', NULL),
(101, 32, 4, 'success', NULL, '2026-09-14 14:17:08', NULL),
(102, 33, 4, 'success', NULL, '2026-09-14 14:17:09', NULL),
(103, 34, 4, 'success', NULL, '2026-09-14 14:17:15', NULL),
(104, 35, 4, 'success', NULL, '2026-09-14 14:17:17', NULL),
(105, 36, 4, 'success', NULL, '2026-09-14 14:17:35', NULL),
(106, 37, 4, 'success', NULL, '2026-09-14 14:17:59', NULL),
(107, 38, 4, 'success', NULL, '2026-09-14 14:18:03', NULL),
(108, 39, 4, 'success', NULL, '2026-09-14 14:18:08', NULL),
(109, 40, 4, 'success', NULL, '2026-09-14 14:18:11', NULL),
(110, 41, 4, 'success', NULL, '2026-09-14 14:19:00', NULL),
(111, 42, 5, 'success', NULL, '2026-09-14 14:36:21', NULL),
(112, 43, 2, 'success', NULL, '2026-09-15 04:58:11', NULL),
(113, 44, 4, 'success', NULL, '2026-09-15 05:16:12', NULL),
(114, 45, 4, 'success', NULL, '2026-09-15 05:19:35', NULL),
(115, 46, 1, 'success', NULL, '2026-09-15 05:21:40', NULL),
(116, 47, 1, 'success', NULL, '2026-09-15 05:25:05', NULL),
(117, 48, 2, 'success', NULL, '2026-09-15 06:11:50', NULL),
(118, 49, 10, 'success', NULL, '2026-09-15 06:27:39', NULL),
(119, 50, 10, 'success', NULL, '2026-09-15 06:29:45', NULL),
(120, 50, 2, 'success', NULL, '2026-09-15 06:29:45', NULL),
(121, 51, 2, 'success', NULL, '2026-09-15 06:29:55', NULL),
(122, 52, 4, 'success', NULL, '2026-09-15 14:22:30', NULL),
(123, 53, 8, 'success', NULL, '2026-09-16 17:04:38', NULL),
(124, 54, 10, 'success', NULL, '2026-09-16 17:04:50', NULL),
(125, 54, 8, 'success', NULL, '2026-09-16 17:04:50', NULL),
(126, 54, 2, 'success', NULL, '2026-09-16 17:04:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `paymentoptions`
--

CREATE TABLE `paymentoptions` (
  `optionid` int(11) NOT NULL,
  `optioncode` varchar(50) NOT NULL,
  `optionname` varchar(100) NOT NULL,
  `chargepercentage` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `fixedcharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `createdat` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentoptions`
--

INSERT INTO `paymentoptions` (`optionid`, `optioncode`, `optionname`, `chargepercentage`, `fixedcharge`, `isactive`, `createdat`, `updatedat`) VALUES
(1, 'paystack', 'Paystack', 0.0150, 0.00, 1, '2026-09-15 07:19:38', '2026-09-15 07:19:38'),
(2, 'flutterwave', 'Flutterwave', 0.0140, 0.00, 1, '2026-09-15 07:19:38', '2026-09-15 07:19:38'),
(3, 'remita', 'Remita', 0.0200, 0.00, 1, '2026-09-15 07:19:38', '2026-09-15 07:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `paymenttransactions`
--

CREATE TABLE `paymenttransactions` (
  `paymentid` int(11) NOT NULL,
  `applicationid` int(11) NOT NULL,
  `referencenumber` varchar(50) NOT NULL,
  `matricnumber` varchar(50) NOT NULL,
  `paymentoptioncode` varchar(50) NOT NULL,
  `paymentoptionname` varchar(100) NOT NULL,
  `baseamount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `chargeamount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `totalamount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paymentreference` varchar(100) NOT NULL,
  `gatewayreference` varchar(100) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'successful',
  `paidat` datetime DEFAULT NULL,
  `createdat` datetime NOT NULL DEFAULT current_timestamp(),
  `attemptid` bigint(20) UNSIGNED DEFAULT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `completedat` datetime DEFAULT NULL,
  `failuremessage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymenttransactions`
--

INSERT INTO `paymenttransactions` (`paymentid`, `applicationid`, `referencenumber`, `matricnumber`, `paymentoptioncode`, `paymentoptionname`, `baseamount`, `chargeamount`, `totalamount`, `paymentreference`, `gatewayreference`, `status`, `paidat`, `createdat`, `attemptid`, `provider`, `currency`, `completedat`, `failuremessage`) VALUES
(1, 6, 'IDC-20260915-CEC86', 'CU/ENG/2026/010', 'flutterwave', 'Flutterwave', 5000.00, 70.00, 5070.00, 'PAY-20260915-4EEF96', 'flutterwave-1789453604966', 'successful', '2026-09-15 07:26:44', '2026-09-15 07:26:44', NULL, NULL, NULL, NULL, NULL),
(2, 7, 'IDC-20260915-2E21A', 'CU/CMSS/2026/002', 'flutterwave', 'Flutterwave', 3000.00, 42.00, 3042.00, 'PAY-20260915-CA3D85', 'flutterwave-1789453772660', 'successful', '2026-09-15 07:29:32', '2026-09-15 07:29:32', NULL, NULL, NULL, NULL, NULL),
(3, 8, 'IDC-20260915-2F6D3', 'CU/ENG/2026/008', 'flutterwave', 'Flutterwave', 5000.00, 70.00, 5070.00, 'PAY-20260915-90F064', 'flutterwave-1789472985046', 'successful', '2026-09-15 12:49:45', '2026-09-15 12:49:45', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `programmes`
--

CREATE TABLE `programmes` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programmes`
--

INSERT INTO `programmes` (`id`, `department_id`, `name`, `code`, `created_at`) VALUES
(1, 1, 'Chemical Engineering', 'CHE', '2026-09-06 22:50:05'),
(2, 2, 'Civil Engineering', 'CVE', '2026-09-06 22:50:05'),
(3, 3, 'Computer Engineering', 'CPE', '2026-09-06 22:50:05'),
(4, 3, 'Electrical and Electronics Engineering', 'EEE', '2026-09-06 22:50:05'),
(5, 3, 'Information and Communication Engineering', 'ICE', '2026-09-06 22:50:05'),
(6, 4, 'Mechanical Engineering', 'MEE', '2026-09-06 22:50:05'),
(7, 5, 'Petroleum Engineering', 'PET', '2026-09-06 22:50:05'),
(8, 6, 'Accounting', 'ACC', '2026-09-06 22:50:05'),
(9, 7, 'Finance', 'FIN', '2026-09-06 22:50:05'),
(10, 8, 'Business Administration', 'BUS', '2026-09-06 22:50:05'),
(11, 8, 'Industrial Relations and Human Resource Management', 'IRHRM', '2026-09-06 22:50:05'),
(12, 8, 'Marketing', 'MKT', '2026-09-06 22:50:05'),
(13, 9, 'Economics', 'ECO', '2026-09-06 22:50:05'),
(14, 10, 'Mass Communication', 'MSC', '2026-09-06 22:50:05'),
(15, 11, 'Sociology', 'SOC', '2026-09-06 22:50:05'),
(16, 12, 'Architecture', 'ARC', '2026-09-06 22:50:05'),
(17, 13, 'Building Technology', 'BLT', '2026-09-06 22:50:05'),
(18, 14, 'Estate Management', 'ESM', '2026-09-06 22:50:05'),
(19, 15, 'Applied Biology and Biotechnology', 'ABB', '2026-09-06 22:50:05'),
(20, 15, 'Microbiology', 'MCB', '2026-09-06 22:50:05'),
(21, 16, 'Biochemistry and Molecular Biology', 'BCH', '2026-09-06 22:50:05'),
(22, 17, 'Industrial Chemistry', 'ICH', '2026-09-06 22:50:05'),
(23, 18, 'Computer Science', 'CSC', '2026-09-06 22:50:05'),
(24, 18, 'Management Information Systems', 'MIS', '2026-09-06 22:50:05'),
(25, 19, 'Industrial Mathematics', 'IMT', '2026-09-06 22:50:05'),
(26, 19, 'Industrial Mathematics (Computer Science Option)', 'IMT-CS', '2026-09-06 22:50:05'),
(27, 19, 'Industrial Mathematics (Statistics Option)', 'IMT-STAT', '2026-09-06 22:50:05'),
(28, 20, 'Industrial Physics (Applied Geophysics Option)', 'IPH-GEO', '2026-09-06 22:50:05'),
(29, 20, 'Industrial Physics (Electronics and IT Applications Option)', 'IPH-EIT', '2026-09-06 22:50:05'),
(30, 20, 'Industrial Physics (Renewable Energy Option)', 'IPH-RE', '2026-09-06 22:50:05'),
(31, 13, 'Quantity Surveying', 'QSV', '2026-09-06 22:50:05'),
(32, 21, 'International Relations', 'IR', '2026-09-06 22:50:05'),
(33, 21, 'Policy and Strategic Studies', 'PSS', '2026-09-06 22:50:05'),
(34, 21, 'Political Science', 'POL', '2026-09-06 22:50:05'),
(35, 22, 'Psychology', 'PSY', '2026-09-06 22:50:05'),
(36, 23, 'English', 'ENG', '2026-09-06 22:50:05');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(30) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `department` varchar(150) NOT NULL,
  `programme` varchar(150) NOT NULL,
  `college_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `programme_id` int(11) DEFAULT NULL,
  `photo_path` varchar(255) NOT NULL,
  `photo_processed_path` varchar(255) DEFAULT NULL,
  `validity_start` year(4) NOT NULL,
  `validity_end` year(4) NOT NULL,
  `status` enum('active','graduated','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `level` int(11) NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `matric_no`, `first_name`, `middle_name`, `last_name`, `full_name`, `department`, `programme`, `college_id`, `department_id`, `programme_id`, `photo_path`, `photo_processed_path`, `validity_start`, `validity_end`, `status`, `created_at`, `level`) VALUES
(1, 'CU/ENG/2026/001', 'Adeyemi', NULL, 'John', 'John Adeyemi', 'Civil Engineering', 'Civil Engineering', 1, 2, 2, 'uploads/photos/CU_ENG_001.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_001.jpg', '2025', '2034', 'active', '2026-08-07 09:57:40', 100),
(2, 'CU/CMSS/2026/002', 'Johnson', NULL, 'Mary', 'Mary Johnson', 'Mass Communication', 'Mass Communication', 2, 10, 14, 'uploads/photos/CU_ENG_002.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_002.jpg', '2026', '2030', 'active', '2026-08-07 09:57:40', 100),
(3, 'CU/ENG/2026/003', 'Okafor', NULL, 'Daniel', 'Daniel Okafor', 'Electrical and Information Engineering', 'Electrical and Electronics Engineering', 1, 3, 4, 'uploads/photos/CU_ENG_003.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_003.jpg', '2026', '2030', 'active', '2026-08-07 09:57:40', 100),
(4, 'CU/ENG/2026/004', 'Williams', NULL, 'Sarah', 'Sarah Williams', 'Electrical and Information Engineering', 'Computer Engineering', 1, 3, 3, 'uploads/photos/CU_ENG_004.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_004.jpg', '2026', '2030', 'active', '2026-08-07 09:57:40', 100),
(5, 'CU/ENG/2026/005', 'Ibrahim', NULL, 'Michael', 'Michael Ibrahim', 'Chemical Engineering', 'Chemical Engineering', 1, 1, 1, 'uploads/photos/CU_ENG_005.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_005.jpg', '2026', '2030', 'active', '2026-08-07 09:57:40', 100),
(6, 'CU/ENG/2026/006', 'Adebayo', NULL, 'Grace', 'Grace Adebayo', 'Electrical and Information Engineering', 'Computer Engineering', 1, 3, 3, 'uploads/photos/CU_ENG_006.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_006.jpg', '2024', '2028', 'active', '2026-08-07 09:57:40', 300),
(7, 'CU/ENG/2026/007', 'Joseph', NULL, 'Samuel', 'Samuel Joseph', 'Petroleum Engineering', 'Petroleum Engineering', 1, 5, 7, 'uploads/photos/CU_ENG_007.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_007.jpg', '2026', '2030', 'active', '2026-08-07 09:57:40', 100),
(8, 'CU/ENG/2026/008', 'Oladipo', NULL, 'Esther', 'Esther Oladipo', 'Electrical and Information Engineering', 'Computer Engineering', 1, 3, 3, 'uploads/photos/CU_ENG_008.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_008.jpg', '2025', '2029', 'active', '2026-08-07 09:57:40', 200),
(9, 'CU/ENG/2026/009', 'Emmanuel', NULL, 'David', 'David Emmanuel', 'Electrical and Information Engineering', 'Electrical and Electronics Engineering', 1, 3, 4, 'uploads/photos/CU_ENG_009.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_009.jpg', '2025', '2029', 'active', '2026-08-07 09:57:40', 200),
(10, 'CU/ENG/2026/010', 'Eze', NULL, 'Blessing', 'Blessing Eze', 'Civil Engineering', 'Civil Engineering', 1, 2, 2, 'uploads/photos/CU_ENG_010.jpg', 'C:\\wamp64\\www\\idcard-system/uploads/photos_processed/CU_ENG_2026_010.jpg', '2025', '2029', 'active', '2026-08-07 09:57:40', 200);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_department_per_college` (`college_id`,`name`);

--
-- Indexes for table `idcardapplicationevents`
--
ALTER TABLE `idcardapplicationevents`
  ADD PRIMARY KEY (`eventid`),
  ADD UNIQUE KEY `uq_application_event` (`applicationid`,`eventtype`),
  ADD KEY `ix_history` (`applicationid`,`occurredat`,`eventid`);

--
-- Indexes for table `idcardapplications`
--
ALTER TABLE `idcardapplications`
  ADD PRIMARY KEY (`applicationid`),
  ADD UNIQUE KEY `referencenumber` (`referencenumber`),
  ADD KEY `ix_application_student` (`matricnumber`,`status`),
  ADD KEY `ix_application_print` (`paymentstatus`,`status`,`paidat`);

--
-- Indexes for table `idcardmigrations`
--
ALTER TABLE `idcardmigrations`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `idcardpaymentattempts`
--
ALTER TABLE `idcardpaymentattempts`
  ADD PRIMARY KEY (`attemptid`),
  ADD UNIQUE KEY `uq_attempt_reference` (`paymentreference`),
  ADD UNIQUE KEY `uq_attempt_application` (`applicationid`),
  ADD KEY `ix_attempt_student` (`matricnumber`,`status`);

--
-- Indexes for table `idcardrefunds`
--
ALTER TABLE `idcardrefunds`
  ADD PRIMARY KEY (`refundid`),
  ADD UNIQUE KEY `uq_refund_application` (`applicationid`),
  ADD KEY `ix_refund_queue` (`status`,`requestedat`);

--
-- Indexes for table `idcardsettings`
--
ALTER TABLE `idcardsettings`
  ADD PRIMARY KEY (`settingid`),
  ADD UNIQUE KEY `uq_idcardsettings_applicationtype` (`applicationtype`);

--
-- Indexes for table `id_card_batches`
--
ALTER TABLE `id_card_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `id_card_batch_items`
--
ALTER TABLE `id_card_batch_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_batch_application` (`batch_id`,`applicationid`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `ix_batch_application` (`applicationid`);

--
-- Indexes for table `paymentoptions`
--
ALTER TABLE `paymentoptions`
  ADD PRIMARY KEY (`optionid`),
  ADD UNIQUE KEY `optioncode` (`optioncode`);

--
-- Indexes for table `paymenttransactions`
--
ALTER TABLE `paymenttransactions`
  ADD PRIMARY KEY (`paymentid`),
  ADD UNIQUE KEY `paymentreference` (`paymentreference`),
  ADD UNIQUE KEY `uq_transaction_attempt` (`attemptid`),
  ADD KEY `applicationid` (`applicationid`),
  ADD KEY `referencenumber` (`referencenumber`),
  ADD KEY `matricnumber` (`matricnumber`);

--
-- Indexes for table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_programme_per_department` (`department_id`,`name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matric_no` (`matric_no`),
  ADD KEY `college_id` (`college_id`),
  ADD KEY `fk_students_department` (`department_id`),
  ADD KEY `fk_students_programme` (`programme_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `idcardapplicationevents`
--
ALTER TABLE `idcardapplicationevents`
  MODIFY `eventid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `idcardapplications`
--
ALTER TABLE `idcardapplications`
  MODIFY `applicationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `idcardpaymentattempts`
--
ALTER TABLE `idcardpaymentattempts`
  MODIFY `attemptid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `idcardrefunds`
--
ALTER TABLE `idcardrefunds`
  MODIFY `refundid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `idcardsettings`
--
ALTER TABLE `idcardsettings`
  MODIFY `settingid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `id_card_batches`
--
ALTER TABLE `id_card_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `id_card_batch_items`
--
ALTER TABLE `id_card_batch_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `paymentoptions`
--
ALTER TABLE `paymentoptions`
  MODIFY `optionid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `paymenttransactions`
--
ALTER TABLE `paymenttransactions`
  MODIFY `paymentid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `programmes`
--
ALTER TABLE `programmes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_college` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `idcardapplicationevents`
--
ALTER TABLE `idcardapplicationevents`
  ADD CONSTRAINT `fk_event_application` FOREIGN KEY (`applicationid`) REFERENCES `idcardapplications` (`applicationid`);

--
-- Constraints for table `idcardpaymentattempts`
--
ALTER TABLE `idcardpaymentattempts`
  ADD CONSTRAINT `fk_attempt_application` FOREIGN KEY (`applicationid`) REFERENCES `idcardapplications` (`applicationid`);

--
-- Constraints for table `idcardrefunds`
--
ALTER TABLE `idcardrefunds`
  ADD CONSTRAINT `fk_refund_application` FOREIGN KEY (`applicationid`) REFERENCES `idcardapplications` (`applicationid`);

--
-- Constraints for table `id_card_batches`
--
ALTER TABLE `id_card_batches`
  ADD CONSTRAINT `id_card_batches_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`);

--
-- Constraints for table `id_card_batch_items`
--
ALTER TABLE `id_card_batch_items`
  ADD CONSTRAINT `fk_batch_application` FOREIGN KEY (`applicationid`) REFERENCES `idcardapplications` (`applicationid`),
  ADD CONSTRAINT `id_card_batch_items_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `id_card_batches` (`id`),
  ADD CONSTRAINT `id_card_batch_items_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `paymenttransactions`
--
ALTER TABLE `paymenttransactions`
  ADD CONSTRAINT `fk_paymenttransactions_application` FOREIGN KEY (`applicationid`) REFERENCES `idcardapplications` (`applicationid`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transaction_attempt` FOREIGN KEY (`attemptid`) REFERENCES `idcardpaymentattempts` (`attemptid`);

--
-- Constraints for table `programmes`
--
ALTER TABLE `programmes`
  ADD CONSTRAINT `fk_programmes_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_programme` FOREIGN KEY (`programme_id`) REFERENCES `programmes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
