-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2026 at 06:19 AM
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
  `cancelledat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `idcardapplications`
--

INSERT INTO `idcardapplications` (`applicationid`, `referencenumber`, `matricnumber`, `applicationtype`, `status`, `photopath`, `photosizebytes`, `photomimetype`, `documenttype`, `documentpath`, `documentsizebytes`, `documentmimetype`, `rejectionreason`, `reviewedby`, `approvedfee`, `paymentdeadline`, `printmethod`, `printedat`, `pickupdate`, `windowstart`, `windowend`, `pickuplocation`, `collectionpin`, `collectioninstructions`, `readyforpickupat`, `closedby`, `submittedat`, `createdat`, `updatedat`, `closedat`, `cancelledat`) VALUES
(1, 'IDC-20260504-68D34', '123456', 'damaged', 'rejected', 'idcard/photo-69f897fd68a17.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, 'STAFF001', 5000.00, '2026-09-21', 'download', '2026-09-14 10:01:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 13:58:37', '2026-05-04 13:58:37', '2026-09-14 10:01:31', NULL, NULL),
(2, 'IDC-20260504-02073', '2301030', 'damaged', 'submitted', 'idcard/photo-69f8996c01d4a.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 14:04:44', '2026-05-04 14:04:44', NULL, NULL, NULL),
(3, 'IDC-20260504-07BA4', '2301', 'damaged', 'submitted', 'idcard/photo-69f8a160e6568.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 14:38:41', '2026-05-04 14:38:41', NULL, NULL, NULL),
(4, 'IDC-20260504-0243C', '23014', 'loststolen', 'submitted', 'idcard/photo-69f8a18b02121.jpeg', 127357, 'image/jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-04 14:39:23', '2026-05-04 14:39:23', NULL, NULL, NULL),
(5, 'IDC-20260914-53F79', '123456', 'loststolen', 'paid', 'idcard/photo-6aa7c0a75258c.jpg', 379951, 'image/jpeg', 'WALLPAPER.jpg', 'idcard/document-6aa7c0a753484.jpg', 37291, 'image/jpeg', NULL, 'STAFF001', 5000.00, '2026-09-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-14 10:38:47', '2026-09-14 10:38:47', '2026-09-14 10:41:07', NULL, NULL);

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
(42, 1, 'web', 1, 'C:\\xampp\\htdocs\\REFACTOR\\idcard-system/output/ENG_20260914_163619.pdf', 'completed', '2026-09-14 14:36:19', 'awaiting_print', NULL, NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `id_card_batch_items`
--

INSERT INTO `id_card_batch_items` (`id`, `batch_id`, `student_id`, `status`, `error_message`, `created_at`) VALUES
(1, 1, 10, 'success', NULL, '2026-08-28 11:39:25'),
(2, 1, 3, 'success', NULL, '2026-08-28 11:39:25'),
(3, 1, 9, 'success', NULL, '2026-08-28 11:39:25'),
(4, 1, 8, 'success', NULL, '2026-08-28 11:39:25'),
(5, 1, 6, 'success', NULL, '2026-08-28 11:39:25'),
(6, 1, 1, 'success', NULL, '2026-08-28 11:39:26'),
(7, 1, 5, 'success', NULL, '2026-08-28 11:39:26'),
(8, 1, 7, 'success', NULL, '2026-08-28 11:39:26'),
(9, 1, 4, 'success', NULL, '2026-08-28 11:39:26'),
(10, 2, 10, 'success', NULL, '2026-08-28 12:00:32'),
(11, 2, 3, 'success', NULL, '2026-08-28 12:00:32'),
(12, 2, 9, 'success', NULL, '2026-08-28 12:00:32'),
(13, 2, 8, 'success', NULL, '2026-08-28 12:00:33'),
(14, 2, 6, 'success', NULL, '2026-08-28 12:00:33'),
(15, 2, 1, 'success', NULL, '2026-08-28 12:00:33'),
(16, 2, 5, 'success', NULL, '2026-08-28 12:00:34'),
(17, 2, 7, 'success', NULL, '2026-08-28 12:00:34'),
(18, 2, 4, 'success', NULL, '2026-08-28 12:00:34'),
(19, 3, 1, 'success', NULL, '2026-08-28 12:03:36'),
(20, 3, 2, 'success', NULL, '2026-08-28 12:03:37'),
(21, 4, 10, 'success', NULL, '2026-08-28 13:58:06'),
(22, 4, 3, 'success', NULL, '2026-08-28 13:58:06'),
(23, 4, 9, 'success', NULL, '2026-08-28 13:58:07'),
(24, 4, 8, 'success', NULL, '2026-08-28 13:58:07'),
(25, 4, 6, 'success', NULL, '2026-08-28 13:58:07'),
(26, 4, 1, 'success', NULL, '2026-08-28 13:58:07'),
(27, 4, 5, 'success', NULL, '2026-08-28 13:58:07'),
(28, 4, 7, 'success', NULL, '2026-08-28 13:58:07'),
(29, 4, 4, 'success', NULL, '2026-08-28 13:58:08'),
(30, 5, 1, 'success', NULL, '2026-08-28 13:58:30'),
(31, 6, 10, 'success', NULL, '2026-08-28 13:59:11'),
(32, 6, 3, 'success', NULL, '2026-08-28 13:59:11'),
(33, 6, 9, 'success', NULL, '2026-08-28 13:59:11'),
(34, 6, 8, 'success', NULL, '2026-08-28 13:59:11'),
(35, 6, 6, 'success', NULL, '2026-08-28 13:59:12'),
(36, 6, 1, 'success', NULL, '2026-08-28 13:59:12'),
(37, 6, 5, 'success', NULL, '2026-08-28 13:59:12'),
(38, 6, 7, 'success', NULL, '2026-08-28 13:59:12'),
(39, 6, 4, 'success', NULL, '2026-08-28 13:59:12'),
(40, 7, 10, 'success', NULL, '2026-08-28 14:50:45'),
(41, 7, 3, 'success', NULL, '2026-08-28 14:50:46'),
(42, 7, 9, 'success', NULL, '2026-08-28 14:50:47'),
(43, 7, 8, 'success', NULL, '2026-08-28 14:50:47'),
(44, 7, 6, 'success', NULL, '2026-08-28 14:50:47'),
(45, 7, 1, 'success', NULL, '2026-08-28 14:50:48'),
(46, 7, 5, 'success', NULL, '2026-08-28 14:50:49'),
(47, 7, 7, 'success', NULL, '2026-08-28 14:50:49'),
(48, 7, 4, 'success', NULL, '2026-08-28 14:50:49'),
(49, 8, 2, 'success', NULL, '2026-08-28 14:51:00'),
(50, 9, 10, 'success', NULL, '2026-08-28 14:51:13'),
(51, 9, 3, 'success', NULL, '2026-08-28 14:51:13'),
(52, 9, 9, 'success', NULL, '2026-08-28 14:51:14'),
(53, 9, 8, 'success', NULL, '2026-08-28 14:51:14'),
(54, 9, 6, 'success', NULL, '2026-08-28 14:51:14'),
(55, 9, 1, 'success', NULL, '2026-08-28 14:51:14'),
(56, 9, 5, 'success', NULL, '2026-08-28 14:51:15'),
(57, 9, 7, 'success', NULL, '2026-08-28 14:51:22'),
(58, 9, 4, 'success', NULL, '2026-08-28 14:51:23'),
(59, 10, 1, 'success', NULL, '2026-08-28 14:51:56'),
(60, 10, 2, 'success', NULL, '2026-08-28 14:51:56'),
(61, 11, 10, 'success', NULL, '2026-09-06 22:20:32'),
(62, 11, 3, 'success', NULL, '2026-09-06 22:20:33'),
(63, 11, 9, 'success', NULL, '2026-09-06 22:20:33'),
(64, 11, 8, 'success', NULL, '2026-09-06 22:20:33'),
(65, 11, 6, 'success', NULL, '2026-09-06 22:20:33'),
(66, 11, 1, 'success', NULL, '2026-09-06 22:20:33'),
(67, 11, 5, 'success', NULL, '2026-09-06 22:20:34'),
(68, 11, 7, 'success', NULL, '2026-09-06 22:20:34'),
(69, 11, 4, 'success', NULL, '2026-09-06 22:20:34'),
(70, 12, 3, 'success', NULL, '2026-09-06 22:27:38'),
(71, 12, 1, 'success', NULL, '2026-09-06 22:27:38'),
(72, 12, 5, 'success', NULL, '2026-09-06 22:27:38'),
(73, 12, 7, 'success', NULL, '2026-09-06 22:27:39'),
(74, 12, 4, 'success', NULL, '2026-09-06 22:27:39'),
(75, 13, 6, 'success', NULL, '2026-09-06 22:27:44'),
(76, 14, 3, 'success', NULL, '2026-09-06 22:27:54'),
(77, 14, 1, 'success', NULL, '2026-09-06 22:27:54'),
(78, 14, 5, 'success', NULL, '2026-09-06 22:27:54'),
(79, 14, 7, 'success', NULL, '2026-09-06 22:27:54'),
(80, 14, 4, 'success', NULL, '2026-09-06 22:27:54'),
(81, 15, 6, 'success', NULL, '2026-09-06 22:28:10'),
(82, 16, 6, 'success', NULL, '2026-09-06 22:28:23'),
(83, 17, 4, 'success', NULL, '2026-09-06 23:16:45'),
(84, 18, 5, 'success', NULL, '2026-09-06 23:17:10'),
(85, 19, 5, 'success', NULL, '2026-09-06 23:17:30'),
(86, 20, 5, 'success', NULL, '2026-09-06 23:28:02'),
(87, 21, 1, 'success', NULL, '2026-09-06 23:28:10'),
(88, 22, 5, 'success', NULL, '2026-09-13 22:20:48'),
(89, 23, 1, 'success', NULL, '2026-09-14 09:09:57'),
(90, 24, 4, 'success', NULL, '2026-09-14 10:48:51'),
(91, 25, 5, 'success', NULL, '2026-09-14 10:49:48'),
(92, 26, 1, 'success', NULL, '2026-09-14 10:50:24'),
(93, 27, 3, 'success', NULL, '2026-09-14 10:50:45'),
(94, 27, 1, 'success', NULL, '2026-09-14 10:50:45'),
(95, 28, 3, 'success', NULL, '2026-09-14 10:51:03'),
(96, 28, 1, 'success', NULL, '2026-09-14 10:51:03'),
(97, 28, 2, 'success', NULL, '2026-09-14 10:51:04'),
(98, 29, 4, 'success', NULL, '2026-09-14 11:27:44'),
(99, 30, 4, 'success', NULL, '2026-09-14 14:15:39'),
(100, 31, 4, 'success', NULL, '2026-09-14 14:16:53'),
(101, 32, 4, 'success', NULL, '2026-09-14 14:17:08'),
(102, 33, 4, 'success', NULL, '2026-09-14 14:17:09'),
(103, 34, 4, 'success', NULL, '2026-09-14 14:17:15'),
(104, 35, 4, 'success', NULL, '2026-09-14 14:17:17'),
(105, 36, 4, 'success', NULL, '2026-09-14 14:17:35'),
(106, 37, 4, 'success', NULL, '2026-09-14 14:17:59'),
(107, 38, 4, 'success', NULL, '2026-09-14 14:18:03'),
(108, 39, 4, 'success', NULL, '2026-09-14 14:18:08'),
(109, 40, 4, 'success', NULL, '2026-09-14 14:18:11'),
(110, 41, 4, 'success', NULL, '2026-09-14 14:19:00'),
(111, 42, 5, 'success', NULL, '2026-09-14 14:36:21');

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
-- Indexes for table `idcardapplications`
--
ALTER TABLE `idcardapplications`
  ADD PRIMARY KEY (`applicationid`),
  ADD UNIQUE KEY `referencenumber` (`referencenumber`);

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
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `student_id` (`student_id`);

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
-- AUTO_INCREMENT for table `idcardapplications`
--
ALTER TABLE `idcardapplications`
  MODIFY `applicationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `id_card_batches`
--
ALTER TABLE `id_card_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `id_card_batch_items`
--
ALTER TABLE `id_card_batch_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

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
-- Constraints for table `id_card_batches`
--
ALTER TABLE `id_card_batches`
  ADD CONSTRAINT `id_card_batches_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`);

--
-- Constraints for table `id_card_batch_items`
--
ALTER TABLE `id_card_batch_items`
  ADD CONSTRAINT `id_card_batch_items_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `id_card_batches` (`id`),
  ADD CONSTRAINT `id_card_batch_items_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

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
