-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Jun 23, 2026 at 11:09 AM
=======
-- Generation Time: Jun 22, 2026 at 03:25 PM
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `csc_recruitment`
--

-- --------------------------------------------------------

--
-- Table structure for table `anonymization_tokens`
--

CREATE TABLE `anonymization_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
<<<<<<< HEAD
  `token` varchar(30) NOT NULL,
=======
  `token` varchar(12) NOT NULL,
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
  `unmasked_at` timestamp NULL DEFAULT NULL,
  `unmasked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<<<<<<< HEAD
--
-- Dumping data for table `anonymization_tokens`
--

INSERT INTO `anonymization_tokens` (`id`, `application_id`, `token`, `unmasked_at`, `unmasked_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'CSCRO-06-2026-933', NULL, NULL, '2026-06-22 18:56:55', '2026-06-22 18:56:55'),
(2, 2, 'CSCRO-06-2026-516', NULL, NULL, '2026-06-22 18:56:55', '2026-06-22 18:56:55'),
(3, 4, 'CSCRO-06-2026-946', NULL, NULL, '2026-06-22 18:56:55', '2026-06-22 18:56:55'),
(4, 5, 'CSCRO-04-2026-723', NULL, NULL, '2026-06-22 18:56:55', '2026-06-22 18:56:55'),
(5, 10, 'AO2-06-2026-315', NULL, NULL, '2026-06-22 19:30:41', '2026-06-22 19:30:41');

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
-- --------------------------------------------------------

--
-- Table structure for table `applicant_profiles`
--

CREATE TABLE `applicant_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `civil_status` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `city_municipality` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `eligibility` varchar(255) DEFAULT NULL,
  `eligibility_other` varchar(255) DEFAULT NULL,
  `indigenous_group` varchar(255) DEFAULT NULL,
  `pwd` varchar(255) DEFAULT NULL,
  `solo_parent` varchar(255) DEFAULT NULL,
  `pds_path` varchar(255) DEFAULT NULL,
  `app_letter_path` varchar(255) DEFAULT NULL,
  `ipcr_path` varchar(255) DEFAULT NULL,
  `coe_path` varchar(255) DEFAULT NULL,
  `tor_path` varchar(255) DEFAULT NULL,
  `profile_completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_profiles`
--

INSERT INTO `applicant_profiles` (`id`, `user_id`, `photo_path`, `first_name`, `last_name`, `middle_name`, `gender`, `civil_status`, `birthday`, `religion`, `mobile_number`, `address`, `region`, `province`, `city_municipality`, `barangay`, `created_at`, `updated_at`, `eligibility`, `eligibility_other`, `indigenous_group`, `pwd`, `solo_parent`, `pds_path`, `app_letter_path`, `ipcr_path`, `coe_path`, `tor_path`, `profile_completed_at`) VALUES
(1, 4, 'profile-photos/4/I0Nv0gVEPWRwMvQ4T7gOaWrg5YfGPOTG1MNUdpWI.jpg', 'Ana', 'Dela Cruz', 'Santos', 'Female', 'Married', '1995-01-05', 'Roman Catholic', '09518159848', '12 Sampaguita St., Quezon City', 'Region VIII - Eastern Visayas', 'Leyte', 'Tacloban City', '93', '2026-06-20 08:18:17', '2026-06-20 22:16:47', 'Career Service Professional Eligibility', NULL, 'No', 'No', 'No', 'profile-documents/4/d28SaaHg1lnxfxR3FxOwlNMsW3v0BoWKaNmurwEb.pdf', 'profile-documents/4/ucKET4TFMQbWLlkw9OKCa85wztHq88boT06Ah8DJ.pdf', 'profile-documents/4/gMWibFwMzU0eX7iYi3zoWE8i5nWkV5rnSknqipR9.pdf', 'profile-documents/4/SQvjhwrr6uLmUjYK8xnkpQA0rPXWr5W1LEwGJLJY.pdf', 'profile-documents/4/0gxGIjpylvH8hu0Gu5SvkTjkUm4Jb2XRgylJYyK8.pdf', '2026-06-20 19:39:37'),
(2, 5, NULL, 'Carlo', 'Mendoza', 'Bautista', NULL, NULL, NULL, NULL, '09281234567', '45 Rizal Ave., Manila', NULL, NULL, NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 6, NULL, 'Liza', 'Reyes', 'Cruz', NULL, NULL, NULL, NULL, '09391234567', '78 Magsaysay Blvd., Davao City', NULL, NULL, NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 7, NULL, 'Ramon', 'Garcia', 'Torres', NULL, NULL, NULL, NULL, '09501234567', '3 Bonifacio St., Cebu City', NULL, NULL, NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 8, NULL, 'Patricia', 'Lim', 'Ong', NULL, NULL, NULL, NULL, '09611234567', '21 Mabini St., Makati City', NULL, NULL, NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 9, NULL, 'Marco', 'Fernandez', 'dela Vega', NULL, NULL, NULL, NULL, '09721234567', '56 Luna St., Pasig City', NULL, NULL, NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
<<<<<<< HEAD
(7, 10, 'profile-photos/10/hwR1EogNpqr3BgSIHMo6Dn7ACHE8Jh5LmrI50suJ.jpg', 'Kim Benedick', 'Banoyo', 'Macawile', 'Male', 'Married', '1995-01-05', 'Roman Catholic', '09518159848', NULL, 'Region VIII - Eastern Visayas', 'Leyte', 'Tacloban City', '93', '2026-06-20 23:30:38', '2026-06-22 20:04:29', 'Career Service Professional Eligibility', NULL, 'No', 'No', 'No', 'profile-documents/10/BROJA67yGMX9qhUytkBlh6UiFM3uW412fPWYXvZM.pdf', 'profile-documents/10/jJoalcVtcQ0PxdjwTTcMGH8nw2vRuRMK4dqhYSoa.pdf', 'profile-documents/10/U9DE1KK1pPHKkbnWytqumfwVg52iGm4tXtpOwKAy.pdf', 'profile-documents/10/mtoDhiBRLh5t3zseesTuETGs9W8tztAHOOqKvJJB.pdf', 'profile-documents/10/zbymW3FQxUNcvCOlQlCqm1kU26AptMODLX2wthtw.pdf', '2026-06-20 23:32:30');
=======
(7, 10, NULL, 'Kim Benedick', 'Banoyo', 'Macawile', 'Male', 'Married', '1995-01-05', 'Roman Catholic', '09518159848', NULL, 'Region VIII - Eastern Visayas', 'Leyte', 'Tacloban City', '93', '2026-06-20 23:30:38', '2026-06-22 06:13:23', 'Career Service Professional Eligibility', NULL, 'No', 'No', 'No', 'profile-documents/10/BROJA67yGMX9qhUytkBlh6UiFM3uW412fPWYXvZM.pdf', 'profile-documents/10/jJoalcVtcQ0PxdjwTTcMGH8nw2vRuRMK4dqhYSoa.pdf', 'profile-documents/10/U9DE1KK1pPHKkbnWytqumfwVg52iGm4tXtpOwKAy.pdf', 'profile-documents/10/mtoDhiBRLh5t3zseesTuETGs9W8tztAHOOqKvJJB.pdf', 'profile-documents/10/zbymW3FQxUNcvCOlQlCqm1kU26AptMODLX2wthtw.pdf', '2026-06-20 23:32:30');
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('submitted','under_review','screened','qualified','disqualified','exam_scheduled','interviewed','shortlisted','for_interview','recommended','appointed','completed','withdrawn') NOT NULL DEFAULT 'submitted',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `vacancy_id`, `applicant_id`, `status`, `submitted_at`, `reviewed_at`, `remarks`, `created_at`, `updated_at`, `deleted_at`) VALUES
<<<<<<< HEAD
(1, 1, 1, 'qualified', '2026-06-12 08:18:17', '2026-06-22 07:06:33', NULL, '2026-06-20 08:18:17', '2026-06-22 18:22:35', NULL),
=======
(1, 1, 1, 'under_review', '2026-06-12 08:18:17', '2026-06-22 07:06:33', NULL, '2026-06-20 08:18:17', '2026-06-22 07:06:33', NULL),
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
(2, 2, 2, 'exam_scheduled', '2026-06-14 08:18:17', '2026-06-16 08:18:17', NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
(3, 3, 3, 'submitted', '2026-06-16 08:18:17', NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
(4, 4, 4, 'interviewed', '2026-06-18 08:18:17', '2026-06-19 08:18:17', NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
(5, 5, 5, 'recommended', '2026-04-26 08:18:17', '2026-05-31 08:18:17', 'Applicant met all requirements and passed the interview panel.', '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
(6, 2, 6, 'submitted', '2026-06-15 08:18:17', NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
(7, 3, 1, 'disqualified', '2026-05-21 08:18:17', '2026-06-05 08:18:17', 'Did not meet the minimum eligibility requirement.', '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
<<<<<<< HEAD
(8, 1, 4, 'disqualified', '2026-06-20 19:40:37', '2026-06-22 07:06:32', NULL, '2026-06-20 19:40:37', '2026-06-22 18:22:35', NULL),
(10, 1, 7, 'qualified', '2026-06-22 19:04:52', NULL, NULL, '2026-06-22 19:04:52', '2026-06-22 19:30:41', NULL),
(11, 2, 7, 'submitted', '2026-06-22 20:23:03', NULL, NULL, '2026-06-22 20:23:03', '2026-06-22 20:23:03', NULL);
=======
(8, 1, 4, 'under_review', '2026-06-20 19:40:37', '2026-06-22 07:06:32', NULL, '2026-06-20 19:40:37', '2026-06-22 07:06:32', NULL),
(9, 1, 7, 'under_review', '2026-06-22 06:13:31', '2026-06-22 07:06:32', NULL, '2026-06-22 06:13:31', '2026-06-22 07:06:32', NULL);
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `auditable_type`, `auditable_id`, `created_at`, `updated_at`) VALUES
<<<<<<< HEAD
(1, 1, 'hrmpsb_assigned:director-representative', 'App\\Models\\HrmbsboardComposition', 6, '2026-06-22 19:06:04', '2026-06-22 19:06:04'),
(2, 1, 'hrmpsb_removed:chairperson', 'App\\Models\\HrmbsboardComposition', 5, '2026-06-22 19:06:10', '2026-06-22 19:06:10'),
(3, 1, 'user_updated', 'App\\Models\\User', 2, '2026-06-22 19:07:02', '2026-06-22 19:07:02'),
(4, 2, 'qs_evaluation_submitted', 'App\\Models\\Application', 1, '2026-06-22 19:29:26', '2026-06-22 19:29:26'),
(5, 2, 'qs_evaluation_submitted', 'App\\Models\\Application', 8, '2026-06-22 19:30:01', '2026-06-22 19:30:01'),
(6, 2, 'qs_evaluation_submitted', 'App\\Models\\Application', 10, '2026-06-22 19:30:21', '2026-06-22 19:30:21'),
(7, 3, 'qs_evaluations_locked', 'App\\Models\\Vacancy', 1, '2026-06-22 19:30:41', '2026-06-22 19:30:41'),
(8, 2, 'exam_result_encoded', 'App\\Models\\Application', 1, '2026-06-22 19:36:37', '2026-06-22 19:36:37'),
(9, 2, 'exam_result_encoded', 'App\\Models\\Application', 1, '2026-06-22 19:36:49', '2026-06-22 19:36:49'),
(10, 2, 'exam_result_encoded', 'App\\Models\\Application', 10, '2026-06-22 19:37:06', '2026-06-22 19:37:06'),
(11, 2, 'exam_result_encoded', 'App\\Models\\Application', 10, '2026-06-22 19:37:15', '2026-06-22 19:37:15'),
(12, 3, 'qs_evaluation_submitted', 'App\\Models\\Application', 11, '2026-06-22 20:26:43', '2026-06-22 20:26:43');
=======
(1, 3, 'created', 'App\\Models\\Vacancy', 1, '2026-06-10 08:18:17', '2026-06-10 08:18:17'),
(2, 3, 'published', 'App\\Models\\Vacancy', 1, '2026-06-10 08:18:17', '2026-06-10 08:18:17'),
(3, 3, 'created', 'App\\Models\\Vacancy', 2, '2026-06-13 08:18:17', '2026-06-13 08:18:17'),
(4, 3, 'published', 'App\\Models\\Vacancy', 2, '2026-06-13 08:18:17', '2026-06-13 08:18:17'),
(5, 3, 'created', 'App\\Models\\Vacancy', 3, '2026-06-15 08:18:17', '2026-06-15 08:18:17'),
(6, 3, 'published', 'App\\Models\\Vacancy', 3, '2026-06-15 08:18:17', '2026-06-15 08:18:17'),
(7, 3, 'created', 'App\\Models\\Vacancy', 4, '2026-06-17 08:18:17', '2026-06-17 08:18:17'),
(8, 3, 'published', 'App\\Models\\Vacancy', 4, '2026-06-17 08:18:17', '2026-06-17 08:18:17'),
(9, 2, 'updated', 'App\\Models\\Application', 1, '2026-06-14 08:18:17', '2026-06-14 08:18:17'),
(10, 2, 'updated', 'App\\Models\\Application', 2, '2026-06-16 08:18:17', '2026-06-16 08:18:17'),
(11, 1, 'created', 'App\\Models\\User', 3, '2026-06-05 08:18:17', '2026-06-05 08:18:17'),
(12, 1, 'user_updated', 'App\\Models\\User', 10, '2026-06-21 01:16:53', '2026-06-21 01:16:53'),
(13, 1, 'hrmpsb_assigned:director-representative', 'App\\Models\\Vacancy', 1, '2026-06-21 01:17:03', '2026-06-21 01:17:03'),
(14, 10, 'user_updated', 'App\\Models\\User', 10, '2026-06-21 01:17:35', '2026-06-21 01:17:35'),
(15, 1, 'hrmpsb_type_toggled:director-representative→alternate', 'App\\Models\\Vacancy', 1, '2026-06-21 01:20:18', '2026-06-21 01:20:18'),
(16, 1, 'hrmpsb_type_toggled:director-representative→principal', 'App\\Models\\Vacancy', 1, '2026-06-21 01:20:19', '2026-06-21 01:20:19'),
(17, 1, 'user_updated', 'App\\Models\\User', 10, '2026-06-21 01:30:23', '2026-06-21 01:30:23'),
(18, 1, 'hrmpsb_removed:director-representative', 'App\\Models\\Vacancy', 1, '2026-06-21 01:30:48', '2026-06-21 01:30:48'),
(19, 1, 'hrmpsb_assigned:director-representative', 'App\\Models\\Vacancy', 1, '2026-06-21 01:31:01', '2026-06-21 01:31:01'),
(20, 1, 'hrmpsb_removed:director-representative', 'App\\Models\\Vacancy', 1, '2026-06-21 01:34:03', '2026-06-21 01:34:03'),
(21, 1, 'hrmpsb_assigned:director-representative', 'App\\Models\\Vacancy', 1, '2026-06-21 01:34:10', '2026-06-21 01:34:10'),
(22, 10, 'qs_evaluation_submitted', 'App\\Models\\Application', 1, '2026-06-21 01:38:27', '2026-06-21 01:38:27'),
(23, 10, 'qs_evaluation_submitted', 'App\\Models\\Application', 8, '2026-06-21 01:44:24', '2026-06-21 01:44:24'),
(24, 1, 'user_updated', 'App\\Models\\User', 3, '2026-06-21 01:49:48', '2026-06-21 01:49:48'),
(25, 1, 'hrmpsb_assigned:secretariat', 'App\\Models\\Vacancy', 1, '2026-06-21 01:51:00', '2026-06-21 01:51:00'),
(26, 1, 'hrmpsb_type_toggled:director-representative→alternate', 'App\\Models\\HrmbsboardComposition', 3, '2026-06-21 06:09:50', '2026-06-21 06:09:50'),
(27, 1, 'hrmpsb_type_toggled:director-representative→principal', 'App\\Models\\HrmbsboardComposition', 3, '2026-06-21 06:09:54', '2026-06-21 06:09:54'),
(28, 1, 'hrmpsb_type_toggled:director-representative→alternate', 'App\\Models\\HrmbsboardComposition', 3, '2026-06-21 06:09:58', '2026-06-21 06:09:58'),
(29, 1, 'hrmpsb_type_toggled:director-representative→principal', 'App\\Models\\HrmbsboardComposition', 3, '2026-06-21 06:09:59', '2026-06-21 06:09:59'),
(30, 1, 'updated', 'App\\Models\\Vacancy', 1, '2026-06-21 07:55:09', '2026-06-21 07:55:09'),
(31, 1, 'updated', 'App\\Models\\Vacancy', 1, '2026-06-21 08:01:18', '2026-06-21 08:01:18'),
(32, 1, 'hrmpsb_assigned:chairperson', 'App\\Models\\HrmbsboardComposition', 5, '2026-06-21 08:06:45', '2026-06-21 08:06:45'),
(33, 1, 'user_updated', 'App\\Models\\User', 10, '2026-06-21 08:31:12', '2026-06-21 08:31:12'),
(34, 1, 'hrmpsb_removed:director-representative', 'App\\Models\\HrmbsboardComposition', 3, '2026-06-21 08:31:24', '2026-06-21 08:31:24'),
(35, 10, 'user_updated', 'App\\Models\\User', 10, '2026-06-21 08:34:05', '2026-06-21 08:34:05'),
(36, 1, 'application_status_changed:submitted→under_review', 'App\\Models\\Application', 9, '2026-06-22 07:06:32', '2026-06-22 07:06:32'),
(37, 1, 'application_status_changed:submitted→under_review', 'App\\Models\\Application', 8, '2026-06-22 07:06:32', '2026-06-22 07:06:32'),
(38, 1, 'application_status_changed:under_review→under_review', 'App\\Models\\Application', 1, '2026-06-22 07:06:33', '2026-06-22 07:06:33'),
(39, 1, 'updated', 'App\\Models\\Vacancy', 1, '2026-06-22 07:19:58', '2026-06-22 07:19:58'),
(40, 1, 'updated', 'App\\Models\\Vacancy', 2, '2026-06-22 07:20:33', '2026-06-22 07:20:33'),
(41, 1, 'updated', 'App\\Models\\Vacancy', 3, '2026-06-22 07:20:50', '2026-06-22 07:20:50'),
(42, 1, 'updated', 'App\\Models\\Vacancy', 4, '2026-06-22 07:21:04', '2026-06-22 07:21:04');
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `bei_ratings`
--

CREATE TABLE `bei_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `evaluator_id` bigint(20) UNSIGNED NOT NULL,
  `competency_scores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`competency_scores`)),
  `total_rating` decimal(4,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `rated_at` timestamp NULL DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
<<<<<<< HEAD
('csc-recruitment-system-cache-dashboard_stats', 'a:3:{s:9:\"vacancies\";a:3:{s:5:\"total\";i:6;s:9:\"published\";i:4;s:12:\"closing_soon\";i:0;}s:12:\"applications\";a:3:{s:5:\"total\";i:10;s:7:\"pending\";i:3;s:10:\"this_month\";i:10;}s:8:\"pipeline\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:6:{i:0;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:9:\"submitted\";s:5:\"count\";i:3;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:9:\"submitted\";s:5:\"count\";i:3;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:1;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:9:\"qualified\";s:5:\"count\";i:2;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:9:\"qualified\";s:5:\"count\";i:2;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:2;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:12:\"disqualified\";s:5:\"count\";i:2;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:12:\"disqualified\";s:5:\"count\";i:2;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:3;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:14:\"exam_scheduled\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:14:\"exam_scheduled\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:4;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:11:\"interviewed\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:11:\"interviewed\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:5;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:11:\"recommended\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:11:\"recommended\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}', 1782212686);
=======
('csc-recruitment-system-cache-dashboard_stats', 'a:3:{s:9:\"vacancies\";a:3:{s:5:\"total\";i:6;s:9:\"published\";i:4;s:12:\"closing_soon\";i:0;}s:12:\"applications\";a:3:{s:5:\"total\";i:9;s:7:\"pending\";i:4;s:10:\"this_month\";i:9;}s:8:\"pipeline\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:6:{i:0;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:9:\"submitted\";s:5:\"count\";i:4;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:9:\"submitted\";s:5:\"count\";i:4;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:1;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:12:\"under_review\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:12:\"under_review\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:2;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:12:\"disqualified\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:12:\"disqualified\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:3;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:14:\"exam_scheduled\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:14:\"exam_scheduled\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:4;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:11:\"interviewed\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:11:\"interviewed\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:5;O:22:\"App\\Models\\Application\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"applications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:11:\"recommended\";s:5:\"count\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:11:\"recommended\";s:5:\"count\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:12:\"submitted_at\";s:8:\"datetime\";s:11:\"reviewed_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:10:\"vacancy_id\";i:1;s:12:\"applicant_id\";i:2;s:6:\"status\";i:3;s:12:\"submitted_at\";i:4;s:11:\"reviewed_at\";i:5;s:7:\"remarks\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}', 1782143839);
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competencies`
--

CREATE TABLE `competencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `competency_key` varchar(64) NOT NULL,
  `competency_name` varchar(255) NOT NULL,
  `competency_group` enum('Core','Organizational','Leadership','Technical') NOT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `competencies`
--

INSERT INTO `competencies` (`id`, `competency_key`, `competency_name`, `competency_group`, `sort_order`, `description`, `created_at`, `updated_at`) VALUES
(1, 'exemplifying_integrity', 'Exemplifying Integrity', 'Core', 1, 'The ability to exemplify high standards of professional behavior as public servants, adhering to ethical as well as moral principles, values and standards of public office.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(2, 'delivering_service_excellence', 'Delivering Service Excellence', 'Core', 2, 'The ability to provide proactive, responsive, accessible, courteous and effective public service to attain the highest level of customer satisfaction.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(3, 'solving_problems_making_decisions', 'Solving Problems and Making Decisions', 'Core', 3, 'The ability to resolve deviations and exercise good judgement by using fact-based analysis and generating and selecting appropriate courses of action to produce positive results.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(4, 'demonstrating_personal_effectiveness_1', 'Demonstrating Personal Effectiveness', 'Core', 4, 'The ability to demonstrate and display self-direction or self-motivation as well as engaging in ongoing personal development.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(5, 'speaking_effectively_1', 'Speaking Effectively', 'Core', 5, 'The ability to actively listen, understand and respond appropriately when interacting with individuals and groups.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(6, 'writing_effectively_1', 'Writing Effectively', 'Core', 6, 'The ability to write in a clear, concise and coherent manner using different tools to convey information or express ideas effectively.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(7, 'championing_and_applying_innovation', 'Championing and Applying Innovation', 'Core', 7, 'The ability to increase productivity and efficiency at work by applying new ideas and creative solutions to existing processes, methods, and services.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(8, 'planning_and_delivering', 'Planning and Delivering', 'Core', 8, 'The ability to set priorities and identify scope and allocate resources to meet individual, team or organization targets and objectives.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(9, 'thinking_strategically', 'Thinking Strategically', 'Core', 9, 'The ability to direct and establish short and long-range plans and calculate and manage risks based on future or emerging trends and outcomes of decisions to achieve CSC goals.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(10, 'demonstrating_personal_effectiveness', 'Demonstrating Personal Effectiveness', 'Organizational', 1, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(11, 'championing_applying_innovation', 'Championing and Applying Innovation', 'Organizational', 2, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(12, 'speaking_effectively', 'Speaking Effectively', 'Organizational', 3, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(13, 'planning_delivering', 'Planning and Delivering', 'Organizational', 4, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(14, 'writing_effectively', 'Writing Effectively', 'Organizational', 5, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(15, 'managing_information', 'Managing Information', 'Organizational', 6, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(16, 'managing_performance_coaching', 'Managing Performance and Coaching for Results', 'Leadership', 1, 'The ability to provide timely and relevant feedback to individuals or groups in order for them to take action and improve their performance.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(17, 'thinking_strategically_creatively', 'Thinking Strategically and Creatively', 'Leadership', 2, 'The ability to direct and establish short and long-range plans and calculate and manage risks based on future or emerging trends and outcomes of decisions to achieve CSC goals.', '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(18, 'building_collaborative_inclusive', 'Building Collaborative and Inclusive Working Relationship', 'Leadership', 3, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(19, 'leading_change', 'Leading Change', 'Leadership', 4, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(20, 'creating_nurturing_high_performing', 'Creating and Nurturing a High Performing Organization', 'Leadership', 5, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(21, 'accounting', 'Accounting', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(22, 'policy_interpretation_implementation', 'Policy Interpretation and Implementation', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(23, 'audit_management', 'Audit Management', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(24, 'learning_delivery_evaluation', 'Learning Delivery and Evaluation', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(25, 'records_management', 'Records Management', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(26, 'test_administration', 'Test Administration', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(27, 'secretariat_liaison_services', 'Secretariat and Liaison Services', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(28, 'supplies_property_management', 'Supplies and Property Management', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(29, 'information_technology', 'Information Technology', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45'),
(30, 'legal_management', 'Legal Management', 'Technical', 0, NULL, '2026-06-21 07:47:45', '2026-06-21 07:47:45');

-- --------------------------------------------------------

--
-- Table structure for table `compliance_deadlines`
--

CREATE TABLE `compliance_deadlines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` bigint(20) UNSIGNED NOT NULL,
  `deadline_type` varchar(255) NOT NULL,
  `due_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cs_forms`
--

CREATE TABLE `cs_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `form_type` enum('33A','33B','form1') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `generated_by` bigint(20) UNSIGNED NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `signed_at` timestamp NULL DEFAULT NULL,
  `submitted_to_csc_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliberation_results`
--

CREATE TABLE `deliberation_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `action` enum('endorsed','appointed','not_recommended') NOT NULL,
  `rank` int(11) DEFAULT NULL,
  `decided_by` bigint(20) UNSIGNED NOT NULL,
  `decided_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `educational_attainments`
--

CREATE TABLE `educational_attainments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `applicant_profile_id` bigint(20) UNSIGNED NOT NULL,
  `level` varchar(255) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `degree_course` varchar(255) DEFAULT NULL,
  `period_from` varchar(255) DEFAULT NULL,
  `period_to` varchar(255) DEFAULT NULL,
  `units_earned` varchar(255) DEFAULT NULL,
  `year_graduated` varchar(255) DEFAULT NULL,
  `honors` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `educational_attainments`
--

INSERT INTO `educational_attainments` (`id`, `applicant_profile_id`, `level`, `school_name`, `degree_course`, `period_from`, `period_to`, `units_earned`, `year_graduated`, `honors`, `created_at`, `updated_at`) VALUES
(1, 7, 'College', 'ACLC College of Tacloban', 'Bachelor of Science in Information Technology', '2014', '2017', NULL, '2017', NULL, '2026-06-20 23:31:55', '2026-06-20 23:31:55'),
(2, 7, 'Elementary', 'Panalaron Central School', 'Primary Education', '2000', '2006', NULL, '2006', NULL, '2026-06-21 08:36:04', '2026-06-21 08:36:04'),
(3, 7, 'Secondary', 'Cirilo Roy Montejo National High School', 'Secondary Education', '2006', '2010', NULL, '2010', NULL, '2026-06-21 08:36:28', '2026-06-21 08:36:28'),
(5, 7, 'Graduate Studies', 'Asian Development Foundation College', 'Master in Information Technology', '2024', 'Present', NULL, NULL, NULL, '2026-06-21 08:38:25', '2026-06-21 08:38:25');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `exam_type` enum('TWE','CBWE') NOT NULL,
  `raw_score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL DEFAULT 100.00,
  `encoded_by` bigint(20) UNSIGNED NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<<<<<<< HEAD
--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `application_id`, `exam_type`, `raw_score`, `max_score`, `encoded_by`, `encoded_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'TWE', 83.00, 100.00, 2, '2026-06-22 19:36:37', '2026-06-22 19:36:37', '2026-06-22 19:36:37'),
(2, 1, 'CBWE', 80.00, 100.00, 2, '2026-06-22 19:36:49', '2026-06-22 19:36:49', '2026-06-22 19:36:49'),
(3, 10, 'TWE', 60.00, 100.00, 2, '2026-06-22 19:37:06', '2026-06-22 19:37:06', '2026-06-22 19:37:06'),
(4, 10, 'CBWE', 75.00, 100.00, 2, '2026-06-22 19:37:15', '2026-06-22 19:37:15', '2026-06-22 19:37:15');

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
-- --------------------------------------------------------

--
-- Table structure for table `exam_schedules`
--

CREATE TABLE `exam_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `exam_type` enum('TWE','CBWE') NOT NULL DEFAULT 'TWE',
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `venue` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrmpsb_compositions`
--

CREATE TABLE `hrmpsb_compositions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `hrmpsb_role` varchar(255) NOT NULL,
  `member_type` enum('principal','alternate') NOT NULL DEFAULT 'principal',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hrmpsb_compositions`
--

INSERT INTO `hrmpsb_compositions` (`id`, `user_id`, `hrmpsb_role`, `member_type`, `is_active`, `assigned_by`, `assigned_at`, `created_at`, `updated_at`) VALUES
(4, 3, 'secretariat', 'principal', 1, 1, '2026-06-21 01:51:00', '2026-06-21 01:51:00', '2026-06-21 01:51:00'),
<<<<<<< HEAD
(6, 2, 'director-representative', 'principal', 1, 1, '2026-06-22 19:06:04', '2026-06-22 19:06:04', '2026-06-22 19:06:04');
=======
(5, 2, 'chairperson', 'principal', 1, 1, '2026-06-21 08:06:45', '2026-06-21 08:06:45', '2026-06-21 08:06:45');
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `interview_schedules`
--

CREATE TABLE `interview_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `venue` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"9d26a974-eb41-4c24-ad97-38eb225e93bc\",\"displayName\":\"App\\\\Notifications\\\\ApplicationStatusUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":30,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":5:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:10;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ApplicationStatusUpdated\\\":4:{s:55:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000application\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Application\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:2:{i:0;s:9:\\\"applicant\\\";i:1;s:14:\\\"applicant.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000oldStatus\\\";s:9:\\\"submitted\\\";s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000newStatus\\\";s:12:\\\"under_review\\\";s:2:\\\"id\\\";s:36:\\\"cbc358a1-720b-49af-b2f9-b6e93e59cc46\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:5:\\\"tries\\\";i:3;s:7:\\\"timeout\\\";i:30;}\",\"batchId\":null},\"createdAt\":1782140792,\"delay\":null}', 0, NULL, 1782140792, 1782140792),
(2, 'default', '{\"uuid\":\"74b1191b-a8e9-4dcb-b159-34736858011c\",\"displayName\":\"App\\\\Notifications\\\\ApplicationStatusUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":30,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":5:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:10;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ApplicationStatusUpdated\\\":4:{s:55:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000application\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Application\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:2:{i:0;s:9:\\\"applicant\\\";i:1;s:14:\\\"applicant.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000oldStatus\\\";s:9:\\\"submitted\\\";s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000newStatus\\\";s:12:\\\"under_review\\\";s:2:\\\"id\\\";s:36:\\\"cbc358a1-720b-49af-b2f9-b6e93e59cc46\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:5:\\\"tries\\\";i:3;s:7:\\\"timeout\\\";i:30;}\",\"batchId\":null},\"createdAt\":1782140792,\"delay\":null}', 0, NULL, 1782140792, 1782140792),
(3, 'default', '{\"uuid\":\"35ced245-464c-445f-a31c-9184c7759ac5\",\"displayName\":\"App\\\\Notifications\\\\ApplicationStatusUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":30,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":5:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:7;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ApplicationStatusUpdated\\\":4:{s:55:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000application\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Application\\\";s:2:\\\"id\\\";i:8;s:9:\\\"relations\\\";a:2:{i:0;s:9:\\\"applicant\\\";i:1;s:14:\\\"applicant.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000oldStatus\\\";s:9:\\\"submitted\\\";s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000newStatus\\\";s:12:\\\"under_review\\\";s:2:\\\"id\\\";s:36:\\\"bd0ee05f-53d9-4420-bdbf-3a6e8087aeb5\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:5:\\\"tries\\\";i:3;s:7:\\\"timeout\\\";i:30;}\",\"batchId\":null},\"createdAt\":1782140793,\"delay\":null}', 0, NULL, 1782140793, 1782140793),
(4, 'default', '{\"uuid\":\"d9fe689f-df32-4353-aacd-989691152bd6\",\"displayName\":\"App\\\\Notifications\\\\ApplicationStatusUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":30,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":5:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:7;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ApplicationStatusUpdated\\\":4:{s:55:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000application\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Application\\\";s:2:\\\"id\\\";i:8;s:9:\\\"relations\\\";a:2:{i:0;s:9:\\\"applicant\\\";i:1;s:14:\\\"applicant.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000oldStatus\\\";s:9:\\\"submitted\\\";s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000newStatus\\\";s:12:\\\"under_review\\\";s:2:\\\"id\\\";s:36:\\\"bd0ee05f-53d9-4420-bdbf-3a6e8087aeb5\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:5:\\\"tries\\\";i:3;s:7:\\\"timeout\\\";i:30;}\",\"batchId\":null},\"createdAt\":1782140793,\"delay\":null}', 0, NULL, 1782140793, 1782140793),
(5, 'default', '{\"uuid\":\"111f16f1-5d97-4fd1-98bb-62c46f5009ef\",\"displayName\":\"App\\\\Notifications\\\\ApplicationStatusUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":30,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":5:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ApplicationStatusUpdated\\\":4:{s:55:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000application\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Application\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:2:{i:0;s:9:\\\"applicant\\\";i:1;s:14:\\\"applicant.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000oldStatus\\\";s:12:\\\"under_review\\\";s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000newStatus\\\";s:12:\\\"under_review\\\";s:2:\\\"id\\\";s:36:\\\"edd3fe6f-3788-436d-bc03-faedd98db321\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:5:\\\"tries\\\";i:3;s:7:\\\"timeout\\\";i:30;}\",\"batchId\":null},\"createdAt\":1782140793,\"delay\":null}', 0, NULL, 1782140793, 1782140793),
(6, 'default', '{\"uuid\":\"93e148d3-6f13-46b7-bdd4-fa733a161885\",\"displayName\":\"App\\\\Notifications\\\\ApplicationStatusUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":30,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":5:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ApplicationStatusUpdated\\\":4:{s:55:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000application\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Application\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:2:{i:0;s:9:\\\"applicant\\\";i:1;s:14:\\\"applicant.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000oldStatus\\\";s:12:\\\"under_review\\\";s:53:\\\"\\u0000App\\\\Notifications\\\\ApplicationStatusUpdated\\u0000newStatus\\\";s:12:\\\"under_review\\\";s:2:\\\"id\\\";s:36:\\\"edd3fe6f-3788-436d-bc03-faedd98db321\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:5:\\\"tries\\\";i:3;s:7:\\\"timeout\\\";i:30;}\",\"batchId\":null},\"createdAt\":1782140793,\"delay\":null}', 0, NULL, 1782140793, 1782140793);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_19_085641_create_personal_access_tokens_table', 1),
(5, '2026_06_19_090453_create_notifications_table', 1),
(6, '2026_06_19_130658_create_applicant_profiles_table', 1),
(7, '2026_06_19_131050_create_vacancies_table', 1),
(8, '2026_06_19_132000_create_applications_table', 1),
(9, '2026_06_19_132100_create_documents_table', 1),
(10, '2026_06_19_132200_create_exam_schedules_table', 1),
(11, '2026_06_19_132300_create_audit_logs_table', 1),
(12, '2026_06_19_132400_create_interview_schedules_table', 1),
(13, '2026_06_21_000001_add_fields_to_applicant_profiles_table', 2),
(14, '2026_06_21_000002_create_work_experiences_table', 2),
(15, '2026_06_21_000003_create_educational_attainments_table', 2),
(16, '2026_06_21_000004_create_trainings_table', 2),
(17, '2026_06_21_061232_add_photo_path_to_applicant_profiles_table', 3),
(18, '2026_06_21_070000_drop_birthdate_from_applicant_profiles', 4),
(19, '2026_06_21_070001_standardize_application_status_enum', 5),
(20, '2026_06_21_080000_extend_users_role_enum', 6),
(21, '2026_06_21_080001_create_hrmpsb_compositions_table', 6),
(22, '2026_06_21_080002_create_qs_evaluations_table', 6),
(23, '2026_06_21_080003_create_compliance_deadlines_table', 6),
(24, '2026_06_21_090000_create_anonymization_tokens_table', 7),
(25, '2026_06_21_090001_add_exam_type_to_exam_schedules', 7),
(26, '2026_06_21_090002_create_exam_results_table', 7),
(27, '2026_06_21_090003_create_bei_ratings_table', 7),
(28, '2026_06_21_090004_create_deliberation_results_table', 7),
(29, '2026_06_21_100000_create_cs_forms_table', 8),
(30, '2026_06_21_100001_create_submission_tracking_table', 8),
(31, '2026_06_21_120000_make_hrmpsb_compositions_global', 9),
(32, '2026_06_21_130000_add_position_fields_to_vacancies', 10),
(33, '2026_06_21_130001_create_competencies_table', 10),
(34, '2026_06_21_130002_create_vacancy_competencies_table', 10),
<<<<<<< HEAD
(35, '2026_06_22_151003_migrate_contact_number_to_mobile_number_on_applicant_profiles', 11),
(36, '2026_06_23_000001_expand_anonymization_token_column', 12),
(37, '2026_06_23_000001_create_pre_assessments_table', 13),
(38, '2026_06_23_000002_add_remarks_columns_to_pre_assessments', 14);
=======
(35, '2026_06_22_151003_migrate_contact_number_to_mobile_number_on_applicant_profiles', 11);
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'api-token', '042fed5b31a3f0dc67c093946b6d6da03f3dc094f0f93342f669904a9bb63cd1', '[\"*\"]', NULL, NULL, '2026-06-20 08:22:18', '2026-06-20 08:22:18'),
(2, 'App\\Models\\User', 4, 'api-token', '3169d435cfaf5bd7022132b9ec4b0809b7db7f6617ecbff6c5cb0f69a38297f6', '[\"*\"]', NULL, NULL, '2026-06-20 08:23:09', '2026-06-20 08:23:09'),
(3, 'App\\Models\\User', 2, 'api-token', 'fdd2e127cc3ff72bc4117dfa86ccc3872f261f94ac2136cd721547dd3d2727f5', '[\"*\"]', '2026-06-20 08:24:40', NULL, '2026-06-20 08:24:06', '2026-06-20 08:24:40'),
(4, 'App\\Models\\User', 1, 'api-token', '9b27ce049b8b89f6444dc7927241fb813bc5a72a85ca978c48b817f7193ad565', '[\"*\"]', '2026-06-20 08:45:59', NULL, '2026-06-20 08:44:38', '2026-06-20 08:45:59'),
(5, 'App\\Models\\User', 1, 'api-token', '199dac6b2d47a8ba3395aa6650e15875ccdaa4f6dbc6d09bbc6407433aed6235', '[\"*\"]', '2026-06-20 19:11:46', NULL, '2026-06-20 18:56:48', '2026-06-20 19:11:46'),
(6, 'App\\Models\\User', 4, 'api-token', '3d46351daff056775ee4c9c49df62dfa7d5bad26b7d1d23c8790a5cf7aa24fb6', '[\"*\"]', '2026-06-20 19:19:45', NULL, '2026-06-20 19:13:07', '2026-06-20 19:19:45'),
(7, 'App\\Models\\User', 4, 'api-token', 'e9e9f2ee697e8ffa02d7fad83d35b062692ca5d918b6e83d3a92ba9851ffd27d', '[\"*\"]', '2026-06-20 20:00:49', NULL, '2026-06-20 19:20:02', '2026-06-20 20:00:49'),
(8, 'App\\Models\\User', 4, 'api-token', '870a05c1b13de8f31445c6b85cf896db6326986c84e25c8c3dfbeae6e2faa033', '[\"*\"]', '2026-06-20 19:22:20', NULL, '2026-06-20 19:21:45', '2026-06-20 19:22:20'),
(9, 'App\\Models\\User', 4, 'api-token', 'e97844ea39d792ddc5905bc732e068b4e5a79d79819ba5ab48dabc35848acc6b', '[\"*\"]', '2026-06-20 19:46:29', NULL, '2026-06-20 19:34:24', '2026-06-20 19:46:29'),
(10, 'App\\Models\\User', 3, 'api-token', 'ec8716035320c6e5c8447e2b679aa3909012a8911f28902b119170de00274b24', '[\"*\"]', '2026-06-20 19:48:05', NULL, '2026-06-20 19:47:09', '2026-06-20 19:48:05'),
(11, 'App\\Models\\User', 6, 'api-token', '8ca359cd23e7cb150e73f328c4e9c83b7739cfdaa5b041e480b1dc48ccf20265', '[\"*\"]', '2026-06-20 19:49:46', NULL, '2026-06-20 19:48:15', '2026-06-20 19:49:46'),
(12, 'App\\Models\\User', 6, 'api-token', '1138e87689bf901b5631f5d1ed47a3364be707a5569f91713def902cc88ba976', '[\"*\"]', '2026-06-20 20:01:26', NULL, '2026-06-20 20:01:18', '2026-06-20 20:01:26'),
(13, 'App\\Models\\User', 4, 'api-token', '81fa148a1596c4ef611442db2c7de3d04cea33abfbe2d180dd496ceae7e4ac03', '[\"*\"]', '2026-06-20 20:10:38', NULL, '2026-06-20 20:01:42', '2026-06-20 20:10:38'),
(14, 'App\\Models\\User', 4, 'api-token', 'd0ff90777a0981ca0526e6a91710a3e6f7eeaeedb66a87ef43db5d02f9bab132', '[\"*\"]', '2026-06-20 20:16:56', NULL, '2026-06-20 20:11:55', '2026-06-20 20:16:56'),
(15, 'App\\Models\\User', 1, 'api-token', '2c46df64d3765870d3f7ff12309e594373ae409cc16372c450e6b9bece12dd34', '[\"*\"]', '2026-06-20 20:17:21', NULL, '2026-06-20 20:17:13', '2026-06-20 20:17:21'),
(16, 'App\\Models\\User', 1, 'api-token', '33dfaccc06025c1e7fa213d014e9595b09fe67084b62f4d5f271ee7bb26df44a', '[\"*\"]', '2026-06-20 20:31:59', NULL, '2026-06-20 20:31:35', '2026-06-20 20:31:59'),
(17, 'App\\Models\\User', 2, 'api-token', 'f7e192f5badcce03f367425ce6d7d57726569f5b35d4b01260fc9b8af79912d4', '[\"*\"]', '2026-06-20 20:32:14', NULL, '2026-06-20 20:32:10', '2026-06-20 20:32:14'),
(18, 'App\\Models\\User', 3, 'api-token', '3272fb7fdcc8ece5f5ea82e945d984d9d58e1df627fff3afbd83e6d984a0de5f', '[\"*\"]', '2026-06-20 20:32:30', NULL, '2026-06-20 20:32:24', '2026-06-20 20:32:30'),
(19, 'App\\Models\\User', 1, 'api-token', '041b0953fa7c4db662a701a75e69ce6342526fe027d62f8ff617fc4752a01263', '[\"*\"]', NULL, NULL, '2026-06-20 20:43:07', '2026-06-20 20:43:07'),
(20, 'App\\Models\\User', 4, 'api-token', 'a0cfd3c869e55a6945b5ffb78c6a12341fc140d36e1624f42ddc0cc880d0165e', '[\"*\"]', '2026-06-20 22:26:30', NULL, '2026-06-20 20:45:17', '2026-06-20 22:26:30'),
(21, 'App\\Models\\User', 1, 'api-token', '9968be3d88abb358b6f4924992bd7c3a123aa1be179b3c2d0482651a74f6e840', '[\"*\"]', '2026-06-21 08:01:45', NULL, '2026-06-20 20:51:01', '2026-06-21 08:01:45'),
(22, 'App\\Models\\User', 4, 'api-token', '85078e0c6044750012f9e8cdc2738dffa7eecb5d423a9e2285ceb84db18704ee', '[\"*\"]', '2026-06-21 01:01:28', NULL, '2026-06-20 21:39:08', '2026-06-21 01:01:28'),
(23, 'App\\Models\\User', 4, 'api-token', '99c4e4a73725790464ba84cb8a66bd0abec4f424d6dec2d595f3f74a643ab9ab', '[\"*\"]', '2026-06-20 23:29:40', NULL, '2026-06-20 23:16:33', '2026-06-20 23:29:40'),
(24, 'App\\Models\\User', 10, 'api-token', '248bcbf04bd11b35d882ccc4e2b1fcbaf4b45b96110ba5b385bc48a941add8f5', '[\"*\"]', '2026-06-20 23:32:45', NULL, '2026-06-20 23:30:11', '2026-06-20 23:32:45'),
(25, 'App\\Models\\User', 1, 'api-token', '1b42e2083bd87ff0ba06ce06c8d0ebc803963ac1fb1bf1aa17631a6e35760fa7', '[\"*\"]', '2026-06-20 23:36:44', NULL, '2026-06-20 23:33:09', '2026-06-20 23:36:44'),
(26, 'App\\Models\\User', 1, 'api-token', '9ecd7a22798af6a6ae7d7d4ec945f2f9a944e482a4decc0180e7686479045d12', '[\"*\"]', '2026-06-20 23:38:21', NULL, '2026-06-20 23:37:56', '2026-06-20 23:38:21'),
(27, 'App\\Models\\User', 4, 'api-token', '0448fd488e67e648308b238a9c5e6ae0613e4b26a0e0769abe02e850c983987f', '[\"*\"]', '2026-06-20 23:54:53', NULL, '2026-06-20 23:38:35', '2026-06-20 23:54:53'),
(28, 'App\\Models\\User', 1, 'api-token', 'ec07c90cb7e8cb1ab85d12892b50bbba97c6060ae7c60fbf044d1adbe1201b32', '[\"*\"]', '2026-06-21 06:00:13', NULL, '2026-06-20 23:55:16', '2026-06-21 06:00:13'),
(29, 'App\\Models\\User', 1, 'api-token', '84392aa6849a631436a70e761b85b976614849acc092aa443660c63c0afaae66', '[\"*\"]', '2026-06-21 01:01:46', NULL, '2026-06-21 01:01:41', '2026-06-21 01:01:46'),
(30, 'App\\Models\\User', 1, 'api-token', 'f24bee7778e31227977efacd5e2c36318df29510100ed4468ecc91857c1e9ba3', '[\"*\"]', '2026-06-21 01:17:03', NULL, '2026-06-21 01:06:12', '2026-06-21 01:17:03'),
(31, 'App\\Models\\User', 10, 'api-token', 'f9b8a2c5f1db7abc58e6108028bb46f61f8bd98f386428aa7f9b89c3392c11da', '[\"*\"]', '2026-06-21 01:17:35', NULL, '2026-06-21 01:17:20', '2026-06-21 01:17:35'),
(32, 'App\\Models\\User', 10, 'api-token', 'd5d75358e1170b6c5fc4b6eba42173d65012e9d2eefd647858e549671670cd46', '[\"*\"]', '2026-06-21 01:19:58', NULL, '2026-06-21 01:17:48', '2026-06-21 01:19:58'),
(33, 'App\\Models\\User', 1, 'api-token', '08b69ee74ee1287bc242e6487fac6228c516282ac5fff81ff7600c7e255a32ba', '[\"*\"]', '2026-06-21 01:20:19', NULL, '2026-06-21 01:20:09', '2026-06-21 01:20:19'),
(34, 'App\\Models\\User', 10, 'api-token', '0abe79500ac862bcf5fc83c969b3195adb67d7fdc57b31a1468d12c91da00cd0', '[\"*\"]', '2026-06-21 01:20:56', NULL, '2026-06-21 01:20:29', '2026-06-21 01:20:56'),
(35, 'App\\Models\\User', 1, 'api-token', '1b708d203f7f3cd57246e3e0d152808a425fe5a63e53587a42073e4a96989831', '[\"*\"]', '2026-06-21 01:25:28', NULL, '2026-06-21 01:21:07', '2026-06-21 01:25:28'),
(36, 'App\\Models\\User', 10, 'api-token', '23f98637b859b3eb3e590427123d1f2182b68db1a741c0a959e20794c467832d', '[\"*\"]', '2026-06-21 01:25:41', NULL, '2026-06-21 01:25:37', '2026-06-21 01:25:41'),
(37, 'App\\Models\\User', 1, 'api-token', '964475c7c7056deb66c1a3a04e838154817136454a17b1240d91586ce8c35a06', '[\"*\"]', '2026-06-21 01:26:04', NULL, '2026-06-21 01:25:51', '2026-06-21 01:26:04'),
(38, 'App\\Models\\User', 10, 'api-token', '6fd0623c6d0ea2672cfb31a92558b9740c51f1aa4b67fbf815b911efdd513f80', '[\"*\"]', '2026-06-21 01:26:22', NULL, '2026-06-21 01:26:16', '2026-06-21 01:26:22'),
(39, 'App\\Models\\User', 1, 'api-token', '2a5dda3bd143cd567d4345592ac7826897fcad90bdd43726025c03a24305cdc5', '[\"*\"]', '2026-06-21 01:28:46', NULL, '2026-06-21 01:26:54', '2026-06-21 01:28:46'),
(40, 'App\\Models\\User', 1, 'api-token', '9bf03aeb818e38b8d3d15f9fe1c9804aafce946c9ffd7c9199558ddc09a4f02c', '[\"*\"]', '2026-06-21 01:29:16', NULL, '2026-06-21 01:28:51', '2026-06-21 01:29:16'),
(41, 'App\\Models\\User', 1, 'api-token', 'ac42b1e1bff466829d4e511985cfa40262be7336461209053394c68949a936cd', '[\"*\"]', '2026-06-21 01:30:23', NULL, '2026-06-21 01:29:23', '2026-06-21 01:30:23'),
(42, 'App\\Models\\User', 10, 'api-token', '904394d8a87d820942c05997f8374abf19f20198a72f02700a7e57a15dfe50e8', '[\"*\"]', '2026-06-21 01:30:33', NULL, '2026-06-21 01:30:33', '2026-06-21 01:30:33'),
(43, 'App\\Models\\User', 1, 'api-token', 'b23f04187f5ded507d838e4ec9ed7029d9633ce1aa349defb9bb4457dd0cae66', '[\"*\"]', '2026-06-21 01:31:01', NULL, '2026-06-21 01:30:42', '2026-06-21 01:31:01'),
(44, 'App\\Models\\User', 10, 'api-token', 'f59ce6fbb817238e717b2814470a40e52eb56ad9ed14938d4388ae4e421c03b0', '[\"*\"]', '2026-06-21 01:31:11', NULL, '2026-06-21 01:31:10', '2026-06-21 01:31:11'),
(45, 'App\\Models\\User', 1, 'api-token', 'eae6638e21cd332b9c92592cb8b2f5783b5a45294f21b985570b901f8a85ecb9', '[\"*\"]', '2026-06-21 01:33:15', NULL, '2026-06-21 01:31:17', '2026-06-21 01:33:15'),
(46, 'App\\Models\\User', 1, 'api-token', 'ecd48f51fcd156f600db5e5fcf3e1c4d9b850a48333ce97803415cec1c71d52a', '[\"*\"]', '2026-06-21 01:34:10', NULL, '2026-06-21 01:33:52', '2026-06-21 01:34:10'),
(47, 'App\\Models\\User', 10, 'api-token', '9c5e228a197cca5f6cd49398ef73822688f2b6120d3ff884d9338d5a68830be5', '[\"*\"]', '2026-06-21 01:36:51', NULL, '2026-06-21 01:34:24', '2026-06-21 01:36:51'),
(48, 'App\\Models\\User', 10, 'api-token', '75500d311cc208c8887856680f84641c940b478d9013b99ab778fc12119ab614', '[\"*\"]', '2026-06-21 01:38:27', NULL, '2026-06-21 01:37:03', '2026-06-21 01:38:27'),
(49, 'App\\Models\\User', 1, 'api-token', '0fbcbcc6385b40ec380d5e0022fdad152762468c20682eeb16ed20227c945dc4', '[\"*\"]', '2026-06-21 01:38:41', NULL, '2026-06-21 01:38:34', '2026-06-21 01:38:41'),
(50, 'App\\Models\\User', 1, 'api-token', '80b8fdf04c2f739b9981d6d21146c09fa54664e8874f7041a61b44e52e0dddb6', '[\"*\"]', '2026-06-21 01:44:05', NULL, '2026-06-21 01:43:56', '2026-06-21 01:44:05'),
(51, 'App\\Models\\User', 10, 'api-token', 'a368122e2f3b2760a7a5f1ef6b9ad42ff14c85be2b07c442d373e7944597f8d5', '[\"*\"]', '2026-06-21 01:44:24', NULL, '2026-06-21 01:44:16', '2026-06-21 01:44:24'),
(52, 'App\\Models\\User', 1, 'api-token', '0858af1372b24c58fa02a6e062028d3012e0226aaaf780e96c50a65e8baf5fad', '[\"*\"]', '2026-06-21 01:49:48', NULL, '2026-06-21 01:45:47', '2026-06-21 01:49:48'),
(53, 'App\\Models\\User', 3, 'api-token', 'f6ca0b21019aef12adffb8672205e858c127a580e191e8e9fee1a4fec8e76487', '[\"*\"]', NULL, NULL, '2026-06-21 01:49:56', '2026-06-21 01:49:56'),
(54, 'App\\Models\\User', 3, 'api-token', '93910fba100df39bcf60d9464253904d3251a5686fe462023fb53efbff9cfca5', '[\"*\"]', NULL, NULL, '2026-06-21 01:50:01', '2026-06-21 01:50:01'),
(55, 'App\\Models\\User', 3, 'api-token', '59f6028c79b801fd2409f8d04760b187a1ac7b13e52fe14b783d45389944a20c', '[\"*\"]', NULL, NULL, '2026-06-21 01:50:02', '2026-06-21 01:50:02'),
(56, 'App\\Models\\User', 3, 'api-token', 'abd0a44d7e9f8df2ec521e898ec089f7ce7b662d382fb1ae07ab7f6eb0e1555f', '[\"*\"]', '2026-06-21 01:50:19', NULL, '2026-06-21 01:50:18', '2026-06-21 01:50:19'),
(57, 'App\\Models\\User', 1, 'api-token', '68883a548cbfa7e1fcaee852dc7fd4a4c5204ce2c46a93312d4b3f906843cdb2', '[\"*\"]', '2026-06-21 01:51:00', NULL, '2026-06-21 01:50:30', '2026-06-21 01:51:00'),
(58, 'App\\Models\\User', 3, 'api-token', 'c26c23d1a50bf2004e995b054e1dbbf414af30e3dc4ed47cfbb915b00b52ac47', '[\"*\"]', '2026-06-21 01:51:18', NULL, '2026-06-21 01:51:06', '2026-06-21 01:51:18'),
(59, 'App\\Models\\User', 1, 'api-token', '38d841d64e1a85890aebe169eb2914361c643b6343bcce49bd215763be0f1a3b', '[\"*\"]', '2026-06-21 06:01:15', NULL, '2026-06-21 06:00:24', '2026-06-21 06:01:15'),
(60, 'App\\Models\\User', 10, 'api-token', 'f665650861d8e5fa3a2c3f46db760e79c739f13228da38848b0be42aa2a893c9', '[\"*\"]', NULL, NULL, '2026-06-21 06:04:34', '2026-06-21 06:04:34'),
(61, 'App\\Models\\User', 10, 'api-token', '7bb8dc39027c91221f3aa0dcec23acbd25f1a90ff142b292ca41202c76ef4cfd', '[\"*\"]', NULL, NULL, '2026-06-21 06:04:36', '2026-06-21 06:04:36'),
(62, 'App\\Models\\User', 10, 'api-token', 'dec92e4bafc264aef23ae228b3c20c451f4fcd0b75f83aae1da7429aed20c001', '[\"*\"]', '2026-06-21 06:09:28', NULL, '2026-06-21 06:04:56', '2026-06-21 06:09:28'),
(63, 'App\\Models\\User', 10, 'api-token', 'e4130c87c5f8b859e50fff323566ce6abaa8d39f1496f4208a20c339d373b701', '[\"*\"]', '2026-06-21 07:09:56', NULL, '2026-06-21 06:09:23', '2026-06-21 07:09:56'),
(64, 'App\\Models\\User', 1, 'api-token', '03e403c689c0685e9a50afdeda4af63df92de420c1b1b29a303bbeed7d28bc67', '[\"*\"]', '2026-06-21 06:10:09', NULL, '2026-06-21 06:09:38', '2026-06-21 06:10:09'),
(65, 'App\\Models\\User', 4, 'api-token', '78e8025bb7a49b3bdcdca4365982a7df774c9586b77bf268b77feb84e5a8760e', '[\"*\"]', '2026-06-21 07:49:29', NULL, '2026-06-21 07:11:38', '2026-06-21 07:49:29'),
(66, 'App\\Models\\User', 1, 'api-token', '1cd5d222b1e3ac8819b86619548c93c1440cbc8779f5c48836df7d8344db9afa', '[\"*\"]', '2026-06-21 08:09:15', NULL, '2026-06-21 07:49:42', '2026-06-21 08:09:15'),
(67, 'App\\Models\\User', 4, 'api-token', '90481028a5adf2c19f8c411e5e9792354bee409bdb5b5baa9df2a6bcfc46a917', '[\"*\"]', '2026-06-21 08:05:30', NULL, '2026-06-21 08:02:01', '2026-06-21 08:05:30'),
(68, 'App\\Models\\User', 10, 'api-token', 'ad0045b60dc0087d310da0f8c898c3eecba27e2738c23526770e814ab49a9db8', '[\"*\"]', '2026-06-21 08:06:02', NULL, '2026-06-21 08:06:02', '2026-06-21 08:06:02'),
(69, 'App\\Models\\User', 1, 'api-token', '9018783156feb8dda9ec1e16009ace139a8e6d42f25291c0dc2438a1931d28d9', '[\"*\"]', '2026-06-21 08:25:48', NULL, '2026-06-21 08:06:15', '2026-06-21 08:25:48'),
(70, 'App\\Models\\User', 10, 'api-token', 'de6aeb557d781ef18f4600739c5a67389b1c65cbd6887eda7330e5d0a84b5544', '[\"*\"]', '2026-06-21 08:12:12', NULL, '2026-06-21 08:11:30', '2026-06-21 08:12:12'),
(71, 'App\\Models\\User', 1, 'api-token', '29a098a8e2ea439559068f5e87bf08318ac3e34921de9b515510e841691e463a', '[\"*\"]', '2026-06-21 08:31:37', NULL, '2026-06-21 08:12:25', '2026-06-21 08:31:37'),
(72, 'App\\Models\\User', 4, 'api-token', '9d9603d68a22e112869724d504a54de22ccfcbd87acfed7da9737f44e2b8c3e2', '[\"*\"]', '2026-06-22 06:14:34', NULL, '2026-06-21 08:26:12', '2026-06-22 06:14:34'),
(73, 'App\\Models\\User', 4, 'api-token', '67670ad10d84b7e52b576cba03a0b9cf75a0f999106a70dfcecfd87fc579a80e', '[\"*\"]', '2026-06-21 08:33:39', NULL, '2026-06-21 08:32:40', '2026-06-21 08:33:39'),
(74, 'App\\Models\\User', 10, 'api-token', 'ae66259cdbf285a891e2a2d30165f4b8f974423fd894c1de2df28a61d58cc5a8', '[\"*\"]', '2026-06-21 08:34:05', NULL, '2026-06-21 08:33:59', '2026-06-21 08:34:05'),
(75, 'App\\Models\\User', 10, 'api-token', 'df6c67f23319adba62c20947083098b079ae0487b6b3b2522d605d4b556927a4', '[\"*\"]', '2026-06-21 08:53:00', NULL, '2026-06-21 08:34:15', '2026-06-21 08:53:00'),
(76, 'App\\Models\\User', 10, 'api-token', '2b926585575c0c8e1b5c42e0231007d02cbfd2bf0ded488ea1ac8f13e1d49623', '[\"*\"]', '2026-06-22 05:41:42', NULL, '2026-06-22 04:23:01', '2026-06-22 05:41:42'),
(77, 'App\\Models\\User', 1, 'api-token', '51c0dab42fec31463eff3e542663365e4b6ccad170a5e5bf379dcbd401c54d10', '[\"*\"]', '2026-06-22 06:12:51', NULL, '2026-06-22 05:42:09', '2026-06-22 06:12:51'),
(78, 'App\\Models\\User', 10, 'api-token', 'fab48f1d0f9afe0505d27cb315e00d5243a75606888de317f27f91352ef6767e', '[\"*\"]', '2026-06-22 06:13:46', NULL, '2026-06-22 06:13:00', '2026-06-22 06:13:46'),
(79, 'App\\Models\\User', 1, 'api-token', '7d69827361bda344b1e127b182053590ecef753ba150b6d94bee0ec8da4e47f3', '[\"*\"]', '2026-06-22 07:24:03', NULL, '2026-06-22 06:13:54', '2026-06-22 07:24:03'),
(80, 'App\\Models\\User', 1, 'api-token', 'dc5b093f92c632387b83abbf8c7fa101522076745d3dfad6479dbc3680229f78', '[\"*\"]', '2026-06-22 07:07:18', NULL, '2026-06-22 06:14:42', '2026-06-22 07:07:18'),
<<<<<<< HEAD
(81, 'App\\Models\\User', 10, 'api-token', 'a2e6d9eace1630b51ec99e97221b6f9810e6de9968fae3faa9ab5ffe1eeec194', '[\"*\"]', '2026-06-22 07:23:37', NULL, '2026-06-22 07:07:29', '2026-06-22 07:23:37'),
(82, 'App\\Models\\User', 1, 'api-token', '096f2327669e9dd1fbb5bdde99c141802be8da00fe2b65c6fb5a8ba33d640b7e', '[\"*\"]', '2026-06-22 18:02:52', NULL, '2026-06-22 18:01:53', '2026-06-22 18:02:52'),
(83, 'App\\Models\\User', 3, 'api-token', '22b9628618cfd4d52edfecf33497da940b63689075137b12afb7eca8b5d09c22', '[\"*\"]', '2026-06-22 19:00:53', NULL, '2026-06-22 18:03:07', '2026-06-22 19:00:53'),
(84, 'App\\Models\\User', 3, 'api-token', '3e2bcb306ec704656e08cb82841bebc79532e98277eb64dc34e1a092bae14650', '[\"*\"]', '2026-06-22 20:44:23', NULL, '2026-06-22 18:06:41', '2026-06-22 20:44:23'),
(85, 'App\\Models\\User', 10, 'api-token', 'b38984a1be96529a9444d5b48890a382bbd63403f08e82136196f4ac72d77d12', '[\"*\"]', '2026-06-22 19:04:56', NULL, '2026-06-22 19:03:22', '2026-06-22 19:04:56'),
(86, 'App\\Models\\User', 3, 'api-token', '744c846e0e8437e9cc38b3a53266bd59bf95047d7b7f979c71cbb310c69ab380', '[\"*\"]', '2026-06-22 19:05:21', NULL, '2026-06-22 19:05:11', '2026-06-22 19:05:21'),
(87, 'App\\Models\\User', 1, 'api-token', '4d2d9202a89fe5faf62119b90a042ee9b9ae2e77ec0f02c283a376c01994a798', '[\"*\"]', '2026-06-22 19:06:10', NULL, '2026-06-22 19:05:44', '2026-06-22 19:06:10'),
(88, 'App\\Models\\User', 2, 'api-token', '079ce6d601ae416f9e06ac00631a0f35e4fc5739d891abcd33af5293a76f48fd', '[\"*\"]', '2026-06-22 19:06:31', NULL, '2026-06-22 19:06:23', '2026-06-22 19:06:31'),
(89, 'App\\Models\\User', 1, 'api-token', 'ad0efda4e955030394c354fb6c6ede2d40322732cfb8e8c7a7fe4036ca6f38aa', '[\"*\"]', '2026-06-22 19:07:03', NULL, '2026-06-22 19:06:49', '2026-06-22 19:07:03'),
(90, 'App\\Models\\User', 2, 'api-token', '36a33e9176fab531a1e56348705cae8c7523dfa81a138fc6eea685ed9bb33549', '[\"*\"]', '2026-06-22 19:12:34', NULL, '2026-06-22 19:07:17', '2026-06-22 19:12:34'),
(91, 'App\\Models\\User', 2, 'api-token', '2d6c6571934227d66658863dcfa31127b2665875048739b88a1b0458d7fb917f', '[\"*\"]', '2026-06-22 19:14:54', NULL, '2026-06-22 19:12:58', '2026-06-22 19:14:54'),
(92, 'App\\Models\\User', 2, 'api-token', 'f74de5a365f4b3e58a5da83a2a505acdeeb0bde0d532bc79f0fbf158ab80df2b', '[\"*\"]', '2026-06-22 19:54:51', NULL, '2026-06-22 19:17:36', '2026-06-22 19:54:51'),
(93, 'App\\Models\\User', 3, 'api-token', 'ae2e8e2522c8e9874835788d71b146a6e34d8cace998ae6f4c4710e881eb0eeb', '[\"*\"]', '2026-06-22 19:55:34', NULL, '2026-06-22 19:55:08', '2026-06-22 19:55:34'),
(94, 'App\\Models\\User', 10, 'api-token', 'a14d241c0b10c5a99aaff31e5cf09ca381387375df67896b40b1cfeb0dda5d06', '[\"*\"]', '2026-06-22 20:08:43', NULL, '2026-06-22 19:55:53', '2026-06-22 20:08:43'),
(95, 'App\\Models\\User', 2, 'api-token', '6c45a22c25cc729289f85ea3c46d75c549a6287f32419aa2cec43e6ba200a04b', '[\"*\"]', '2026-06-22 20:09:26', NULL, '2026-06-22 20:09:02', '2026-06-22 20:09:26'),
(96, 'App\\Models\\User', 1, 'api-token', '34127a001bbf1a376b765589936a66f320fac128cefdd2e16a6ca8e51a7fd785', '[\"*\"]', '2026-06-22 20:10:17', NULL, '2026-06-22 20:09:38', '2026-06-22 20:10:17'),
(97, 'App\\Models\\User', 3, 'api-token', 'd93d06df9c4fbdd07f488cbeabb97fdc86faff28c154c1e3f9e822448d76deb8', '[\"*\"]', '2026-06-22 20:18:01', NULL, '2026-06-22 20:17:52', '2026-06-22 20:18:01'),
(98, 'App\\Models\\User', 1, 'api-token', '8980a63bd21639c65f840d7bfa575e210e86b644239a527bbf0bb0ee1752aea0', '[\"*\"]', '2026-06-22 20:18:21', NULL, '2026-06-22 20:18:12', '2026-06-22 20:18:21'),
(99, 'App\\Models\\User', 10, 'api-token', '2833965820d39435d90e107a8719b854e3bb5adb4cc0dc8e4e9c79564434d6fd', '[\"*\"]', '2026-06-22 20:23:15', NULL, '2026-06-22 20:21:38', '2026-06-22 20:23:15'),
(100, 'App\\Models\\User', 2, 'api-token', '343d3819cc2bd1632a1280d7d586facfe20fa60eccdf53d8e17b2ccc7d8c065e', '[\"*\"]', '2026-06-22 20:24:13', NULL, '2026-06-22 20:24:05', '2026-06-22 20:24:13'),
(101, 'App\\Models\\User', 3, 'api-token', 'b5e9cf347e651662646aa85526b231a820256940bd21b0fc71868ea462578a4d', '[\"*\"]', '2026-06-22 20:30:54', NULL, '2026-06-22 20:24:36', '2026-06-22 20:30:54'),
(102, 'App\\Models\\User', 2, 'api-token', 'b85d31cb66b5b42b3034b5fb9c6b6a732af9975249984810e007b5dbdb4714ee', '[\"*\"]', '2026-06-22 21:06:44', NULL, '2026-06-22 20:31:05', '2026-06-22 21:06:44'),
(103, 'App\\Models\\User', 10, 'api-token', '216cfde2f4d56ebb0f8bfda10d70177ce0560e9a77bd2041b11cd1e6f42f5cbc', '[\"*\"]', '2026-06-22 21:30:46', NULL, '2026-06-22 21:22:54', '2026-06-22 21:30:46'),
(104, 'App\\Models\\User', 1, 'api-token', '003c2b86c9529c8689f276acb028d54fb624890031bc3b2edc1c6359d5316d2a', '[\"*\"]', '2026-06-22 21:33:49', NULL, '2026-06-22 21:31:08', '2026-06-22 21:33:49'),
(105, 'App\\Models\\User', 3, 'api-token', '47abcf37834b7bacdaa0f66af71d880f1402b1078e979b54cb1dfde9444e9607', '[\"*\"]', '2026-06-22 22:01:10', NULL, '2026-06-22 21:33:57', '2026-06-22 22:01:10'),
(106, 'App\\Models\\User', 1, 'api-token', 'd27de51ab98e79f4e1b579536e5a2d7781cac6d32e709cfdfbc8e8aadebe8171', '[\"*\"]', '2026-06-22 22:13:15', NULL, '2026-06-22 22:01:37', '2026-06-22 22:13:15'),
(107, 'App\\Models\\User', 3, 'api-token', '7db53e22fac0d9ef61c7b975d7b4a1dfdbaa8f39fa7f672734aee046af87da6d', '[\"*\"]', '2026-06-22 22:53:51', NULL, '2026-06-22 22:13:26', '2026-06-22 22:53:51'),
(108, 'App\\Models\\User', 3, 'api-token', 'ba56fb4dbcea1a93eeb1ff50c2ab800900cefbebc702f64589a55a93e8bc70d5', '[\"*\"]', '2026-06-23 02:42:52', NULL, '2026-06-22 22:24:58', '2026-06-23 02:42:52'),
(109, 'App\\Models\\User', 2, 'api-token', '34174cabdebd009468193d3f8b0c6a6b63d669236f7bb9e4659fd05809b9704a', '[\"*\"]', '2026-06-23 02:04:35', NULL, '2026-06-22 22:54:37', '2026-06-23 02:04:35'),
(110, 'App\\Models\\User', 1, 'api-token', '7d75cac8b4854a8e56186599f4cdb4c5cbc97c016d9627885fd48c0a9057487d', '[\"*\"]', '2026-06-23 03:01:20', NULL, '2026-06-22 23:14:51', '2026-06-23 03:01:20'),
(111, 'App\\Models\\User', 1, 'api-token', '5b6b06048ad66692ab7311752afec747bd4cd4f6a21ec656098afaca5350c8c3', '[\"*\"]', '2026-06-23 02:05:44', NULL, '2026-06-23 02:04:45', '2026-06-23 02:05:44'),
(112, 'App\\Models\\User', 1, 'api-token', 'f5f46679ae1a03a229490f4e58f55c05668c3191e8cabdbfc69f2de39d1cc9b6', '[\"*\"]', '2026-06-23 02:43:45', NULL, '2026-06-23 02:43:08', '2026-06-23 02:43:45'),
(113, 'App\\Models\\User', 3, 'api-token', 'b2c1511d67943a1a578a2c72051353dcaf30edbec9ff1cd48819e36638f61a58', '[\"*\"]', '2026-06-23 03:05:23', NULL, '2026-06-23 02:44:57', '2026-06-23 03:05:23'),
(114, 'App\\Models\\User', 3, 'api-token', 'dc9757ef06e500d4d9384fce7982698c57b4f780aa684434be0e5b1e03a70e9e', '[\"*\"]', '2026-06-23 02:57:05', NULL, '2026-06-23 02:45:52', '2026-06-23 02:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `pre_assessments`
--

CREATE TABLE `pre_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `pds_submitted` tinyint(1) DEFAULT NULL,
  `ipcr_submitted` tinyint(1) DEFAULT NULL,
  `tor_submitted` tinyint(1) DEFAULT NULL,
  `coe_submitted` tinyint(1) DEFAULT NULL,
  `pds_remarks` text DEFAULT NULL,
  `ipcr_remarks` text DEFAULT NULL,
  `tor_remarks` text DEFAULT NULL,
  `coe_remarks` text DEFAULT NULL,
  `requirements_complete` tinyint(1) DEFAULT NULL,
  `requirements_remarks` text DEFAULT NULL,
  `secretariat_remarks` text DEFAULT NULL,
  `license_remarks` text DEFAULT NULL,
  `education_meets` tinyint(1) DEFAULT NULL,
  `license_meets` tinyint(1) DEFAULT NULL,
  `experience_meets` tinyint(1) DEFAULT NULL,
  `training_meets` tinyint(1) DEFAULT NULL,
  `eligibility_meets` tinyint(1) DEFAULT NULL,
  `hrd_assessment` tinyint(1) DEFAULT NULL,
  `hrd_remarks` text DEFAULT NULL,
  `consensus` tinyint(1) DEFAULT NULL,
  `assessed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pre_assessments`
--

INSERT INTO `pre_assessments` (`id`, `application_id`, `pds_submitted`, `ipcr_submitted`, `tor_submitted`, `coe_submitted`, `pds_remarks`, `ipcr_remarks`, `tor_remarks`, `coe_remarks`, `requirements_complete`, `requirements_remarks`, `secretariat_remarks`, `license_remarks`, `education_meets`, `license_meets`, `experience_meets`, `training_meets`, `eligibility_meets`, `hrd_assessment`, `hrd_remarks`, `consensus`, `assessed_by`, `assessed_at`, `created_at`, `updated_at`) VALUES
(1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'License will be expired on 3 June 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-06-22 22:53:51', '2026-06-22 22:53:51', '2026-06-22 22:53:51');
=======
(81, 'App\\Models\\User', 10, 'api-token', 'a2e6d9eace1630b51ec99e97221b6f9810e6de9968fae3faa9ab5ffe1eeec194', '[\"*\"]', '2026-06-22 07:23:37', NULL, '2026-06-22 07:07:29', '2026-06-22 07:23:37');
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `qs_evaluations`
--

CREATE TABLE `qs_evaluations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `evaluator_id` bigint(20) UNSIGNED NOT NULL,
  `education_meets` tinyint(1) DEFAULT NULL,
  `experience_meets` tinyint(1) DEFAULT NULL,
  `training_meets` tinyint(1) DEFAULT NULL,
  `eligibility_meets` tinyint(1) DEFAULT NULL,
  `overall_qualified` tinyint(1) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `evaluated_at` timestamp NULL DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qs_evaluations`
--

INSERT INTO `qs_evaluations` (`id`, `application_id`, `evaluator_id`, `education_meets`, `experience_meets`, `training_meets`, `eligibility_meets`, `overall_qualified`, `remarks`, `evaluated_at`, `locked_at`, `created_at`, `updated_at`) VALUES
<<<<<<< HEAD
(1, 1, 2, 1, 1, 1, 1, 1, 'The applicant is qualified for the said position.', '2026-06-22 19:29:26', '2026-06-22 19:30:41', '2026-06-22 19:29:26', '2026-06-22 19:30:41'),
(2, 8, 2, 0, 1, 1, 1, 0, 'Education is not relevant to the applied vacant position.', '2026-06-22 19:30:01', '2026-06-22 19:30:41', '2026-06-22 19:30:01', '2026-06-22 19:30:41'),
(3, 10, 2, 1, 1, 1, 1, 1, 'The applicant is qualified for the said position.', '2026-06-22 19:30:21', '2026-06-22 19:30:41', '2026-06-22 19:30:21', '2026-06-22 19:30:41'),
(4, 11, 3, 1, 1, 1, 1, 1, NULL, '2026-06-22 20:26:43', NULL, '2026-06-22 20:26:43', '2026-06-22 20:26:43');
=======
(1, 1, 10, 1, 1, 1, 1, 1, NULL, '2026-06-21 01:38:27', NULL, '2026-06-21 01:38:27', '2026-06-21 01:38:27'),
(2, 8, 10, 0, 1, 1, 1, 0, NULL, '2026-06-21 01:44:24', NULL, '2026-06-21 01:44:24', '2026-06-21 01:44:24');
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
<<<<<<< HEAD
('KOMJtpUfQ41OLHpM0Si5ApQKnbDz3ksFhqBK6CU3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.125.1 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'eyJfdG9rZW4iOiJCYnNmcVRvR3UySTI0TzVvUE5IVFVzd3pqUTFodXI3N0d1MW1CVkFuIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9ocm1wc2JcL3FzLWV2YWx1YXRpb25cLzEiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1782212722),
('l5KH3C9tAM0zouNPHeHYumlYFqusXiD543GsIIAn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ2NEVkUkZEZ0kzVDQ5RGVQM0VWVzY1T2RSYnRWNnk5WnVEMXMwU0NNIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2hybXBzYlwvZXhhbS1zY2hlZHVsZVwvMSIsInJvdXRlIjpudWxsfX0=', 1782212224),
('UKTrLkozK3qEBLpmeE9ZFEY4rcJSUTgly6vAadLI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJwTjRrMVZrMzdhNGZtMnc4Q3c3MVowMWdMZlZ0MENzRTR3Q1dCOVJRIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvZGFzaGJvYXJkIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782212471);
=======
('oxCd2QU5KrVGy2DIZqG76oDFotElODdb626GOJUi', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIwQ0JzTnRLU1FPaFNYSUhPMHBpNlFDdnZlZXNva2hSQTBEb0c2Qk1vIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvcHJvZmlsZVwvcGhvdG8/dG9rZW49ODElN0NrYkU3a1BVVFphMjNkVUtvOFlGdkdyT1E4Z1NMeHQ1cTV1ZlZUQVNSMDE2ZTg3YmIiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1782141817),
('Yf0qG9odESwzFucYG4AXXghj3dMCw3zE5fhtgRmk', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.125.1 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'eyJfdG9rZW4iOiJXVlFsT2RpblcwQ0l4MFBhRlBWRXFBRWVwN0xONG84Y3l4T0FFVmZIIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvYWRtaW5cL2FwcGxpY2F0aW9ucyIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1782141818);
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `submission_tracking`
--

CREATE TABLE `submission_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `deadline_type` varchar(255) NOT NULL DEFAULT 'csc_submission',
  `due_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','submitted','overdue') NOT NULL DEFAULT 'pending',
  `last_notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trainings`
--

CREATE TABLE `trainings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `applicant_profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `date_from` varchar(255) NOT NULL,
  `date_to` varchar(255) DEFAULT NULL,
  `hours` decimal(8,2) DEFAULT NULL,
  `ld_type` varchar(255) DEFAULT NULL,
  `conducted_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trainings`
--

INSERT INTO `trainings` (`id`, `applicant_profile_id`, `title`, `date_from`, `date_to`, `hours`, `ld_type`, `conducted_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Leadership and Management Seminar', '2020-01-01', '2020-01-01', 8.00, 'Managerial', 'Civil Service Commission', '2026-06-20 19:39:27', '2026-06-20 19:39:27'),
<<<<<<< HEAD
(2, 7, '2025 Conversations with Local Leaders in Eastern Visayas', '2026-04-15', '2026-04-16', 16.00, 'Managerial', 'Civil Service Commission Regional Office VIII', '2026-06-20 23:32:24', '2026-06-20 23:32:24'),
(3, 7, 'Capacity Building on Designing and Developing Innovative Microlearning and e-Learning Courses', '2025-02-19', '2025-03-14', 28.00, 'Technical', 'CSI Fulbright Philippines', '2026-06-22 20:06:24', '2026-06-22 20:06:24'),
(4, 7, 'Briefing and Orientation for the Beneficiaries of the Government Network (GovNet)', '2025-07-30', '2025-07-30', 8.00, 'Technical', 'Department of Information and Communications Technology - Infrastructure Management Bureau', '2026-06-22 20:07:01', '2026-06-22 20:07:01'),
(5, 7, 'Orientation on the 2024 Implementing Rules and Regulations of E.O. 180 for Public Sector Employees Organizations (PSEOs) in Eastern Visayas', '2025-08-27', '2025-08-27', 8.00, 'Managerial', 'CSC Regional Office VIII', '2026-06-22 20:07:32', '2026-06-22 20:07:32'),
(6, 7, 'Orientation-Workshop on Strategic Performance Management System (SPMS)', '2025-08-11', '2025-08-11', 8.00, 'Technical', 'CSC Regional Office VIII', '2026-06-22 20:08:09', '2026-06-22 20:08:09'),
(7, 7, 'Strategic Operations Planning Workshop', '2025-12-18', '2025-12-19', 12.00, 'Technical', 'CSC Regional Office VIII', '2026-06-22 20:08:40', '2026-06-22 20:08:40');
=======
(2, 7, '2025 Conversations with Local Leaders in Eastern Visayas', '2026-04-15', '2026-04-16', 16.00, 'Managerial', 'Civil Service Commission Regional Office VIII', '2026-06-20 23:32:24', '2026-06-20 23:32:24');
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('applicant','hr-officer','hr-manager','admin','hrmpsb-member','hrmpsb-secretariat','appointing-authority') NOT NULL DEFAULT 'applicant',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@csc.gov.ph', '2026-06-20 08:18:15', '$2y$12$0uhOXB7Rp5n3FjP04CFZHeASJbltRccHWuq1hukQwtR2UjNUfF55u', 'admin', NULL, '2026-06-20 08:18:15', '2026-06-20 08:18:15'),
<<<<<<< HEAD
(2, 'Maria Santos', 'hr.manager@csc.gov.ph', '2026-06-20 08:18:15', '$2y$12$SxwTYHUrHCRkexVs5ZkGYOGp25lAWcmQcdVaIQSZaTfVjuWgTNH..', 'hrmpsb-member', NULL, '2026-06-20 08:18:15', '2026-06-22 19:07:02'),
=======
(2, 'Maria Santos', 'hr.manager@csc.gov.ph', '2026-06-20 08:18:15', '$2y$12$SxwTYHUrHCRkexVs5ZkGYOGp25lAWcmQcdVaIQSZaTfVjuWgTNH..', 'hr-manager', NULL, '2026-06-20 08:18:15', '2026-06-20 08:18:15'),
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
(3, 'Jose Reyes', 'hr.officer@csc.gov.ph', '2026-06-20 08:18:16', '$2y$12$DRKWTSgi7euJaUs30aH2YOSxA6tQB7GtkldgS0OW6uSftYercEg9u', 'hrmpsb-secretariat', NULL, '2026-06-20 08:18:16', '2026-06-21 01:51:00'),
(4, 'Ana Dela Cruz', 'ana.delacruz@gmail.com', '2026-06-20 08:18:16', '$2y$12$pA4QY98oQBZTVVnRTC60TObmMKco/GLfA.Jb1DQdCecQ2DbVq6TT6', 'applicant', NULL, '2026-06-20 08:18:16', '2026-06-20 08:18:16'),
(5, 'Carlo Mendoza', 'carlo.mendoza@gmail.com', '2026-06-20 08:18:16', '$2y$12$7zX4EFncX2.JbdAOPJb2JeydVTd8xiLwMRDTRALyTC4S5WRX.aacC', 'applicant', NULL, '2026-06-20 08:18:16', '2026-06-20 08:18:16'),
(6, 'Liza Reyes', 'liza.reyes@gmail.com', '2026-06-20 08:18:16', '$2y$12$XNLss7.VmaTZdtSZLZ7.MOL3z9HtyT/Hod7/RDNE/k299MXspvIbK', 'applicant', NULL, '2026-06-20 08:18:16', '2026-06-20 08:18:16'),
(7, 'Ramon Garcia', 'ramon.garcia@gmail.com', '2026-06-20 08:18:16', '$2y$12$JBpRyiI5R4ULjFRrE/0IOu8i1a7ef8m/ZlPvm0gWbXLcC7rZZsLv.', 'applicant', NULL, '2026-06-20 08:18:16', '2026-06-20 08:18:16'),
(8, 'Patricia Lim', 'patricia.lim@gmail.com', '2026-06-20 08:18:16', '$2y$12$oRmU5yXsaCWT1WcCG80Z9uyXGtX5Bj3XWfCFsJEn.0qO6Zqws3G/q', 'applicant', NULL, '2026-06-20 08:18:16', '2026-06-20 08:18:16'),
(9, 'Marco Fernandez', 'marco.fernandez@gmail.com', '2026-06-20 08:18:17', '$2y$12$a3I4k/X0CMclQE7MOLFeJOcZgKktc1a.He/SzZLuRciZRr7.WDWUi', 'applicant', NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17'),
(10, 'Kim Benedick M. Banoyo', 'kimbenedick.banoyo@gmail.com', NULL, '$2y$12$A/bGrN2AsAygA9S6Mj4rreoWL.UMDpTCooLf9PKcBYB1K4rUTpbSW', 'applicant', NULL, '2026-06-20 23:30:11', '2026-06-21 08:34:05');

-- --------------------------------------------------------

--
-- Table structure for table `vacancies`
--

CREATE TABLE `vacancies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `position_title` varchar(255) NOT NULL,
  `item_number` varchar(255) NOT NULL,
  `salary_grade` tinyint(3) UNSIGNED NOT NULL,
  `monthly_salary` decimal(12,2) DEFAULT NULL,
  `position_level` varchar(255) DEFAULT NULL,
  `is_anticipated_vacancy` tinyint(1) NOT NULL DEFAULT 0,
  `plantilla_number` varchar(255) DEFAULT NULL,
  `place_of_assignment` varchar(255) NOT NULL,
  `education_req` text NOT NULL,
  `experience_req` text NOT NULL,
  `training_req` text NOT NULL,
  `eligibility_req` text NOT NULL,
  `status` enum('draft','published','closed','filled') NOT NULL DEFAULT 'draft',
  `posted_by` bigint(20) UNSIGNED NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `deadline_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vacancies`
--

INSERT INTO `vacancies` (`id`, `position_title`, `item_number`, `salary_grade`, `monthly_salary`, `position_level`, `is_anticipated_vacancy`, `plantilla_number`, `place_of_assignment`, `education_req`, `experience_req`, `training_req`, `eligibility_req`, `status`, `posted_by`, `published_at`, `deadline_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
<<<<<<< HEAD
(1, 'Administrative Officer II', 'AO2-001-2026', 11, 31705.00, NULL, 0, 'PNTN-001', 'CSC Regional Office VIII', 'Bachelor\'s Degree relevant to the job', '1 year of relevant experience', '4 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'published', 3, '2026-06-10 08:18:17', '2026-07-09 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:19:58', NULL),
(2, 'Information Technology Officer I', 'ITO1-002-2026', 19, 59153.00, NULL, 0, 'PNTN-002', 'CSC Regional Office VIII', 'Bachelor\'s Degree in Information Technology, Computer Science, or related field', '2 years of relevant experience', '8 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'published', 3, '2026-06-13 08:18:17', '2026-07-12 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:20:33', NULL),
(3, 'Human Resource Specialist II', 'PS2-003-2026', 16, 45694.00, NULL, 0, 'PNTN-003', 'CSC Regional Office VIII', 'Bachelor\'s Degree in Public Administration, Human Resource Management, or related field', '2 years of relevant experience', '8 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'published', 3, '2026-06-15 08:18:17', '2026-07-14 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:20:50', NULL),
(4, 'Attorney III', 'ATY3-004-2026', 21, 73303.00, NULL, 0, 'PNTN-004', 'CSC Regional Office VIII', 'Bachelor of Laws', '3 years of relevant experience', '16 hours of relevant training', 'RA 1080 (Bar)', 'published', 3, '2026-06-17 08:18:17', '2026-07-16 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:21:04', NULL),
(5, 'Administrative Aide VI', 'AA6-005-2026', 6, NULL, NULL, 0, 'PNTN-005', 'CSC Regional Office VIII', 'Completion of 2 years studies in college', '1 year of relevant experience', '4 hours of relevant training', 'Career Service (Subprofessional) / First Level Eligibility', 'closed', 3, '2026-04-21 08:18:17', '2026-06-15 08:18:17', '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
(6, 'Records Officer II', 'RO2-006-2026', 11, NULL, NULL, 0, 'PNTN-006', 'CSC Regional Office VIII', 'Bachelor\'s Degree relevant to the job', '1 year of relevant experience', '4 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'draft', 3, NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL);
=======
(1, 'Administrative Officer II', 'CSCRO-AO2-001-2026', 11, 31705.00, NULL, 0, 'PNTN-001', 'CSC Regional Office VIII', 'Bachelor\'s Degree relevant to the job', '1 year of relevant experience', '4 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'published', 3, '2026-06-10 08:18:17', '2026-07-09 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:19:58', NULL),
(2, 'Information Technology Officer I', 'CSCRO-ITO1-002-2026', 19, 59153.00, NULL, 0, 'PNTN-002', 'CSC Regional Office VIII', 'Bachelor\'s Degree in Information Technology, Computer Science, or related field', '2 years of relevant experience', '8 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'published', 3, '2026-06-13 08:18:17', '2026-07-12 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:20:33', NULL),
(3, 'Human Resource Specialist II', 'CSCRO-HRMO3-003-2026', 16, 45694.00, NULL, 0, 'PNTN-003', 'CSC Regional Office VIII', 'Bachelor\'s Degree in Public Administration, Human Resource Management, or related field', '2 years of relevant experience', '8 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'published', 3, '2026-06-15 08:18:17', '2026-07-14 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:20:50', NULL),
(4, 'Attorney III', 'CSCRO-ATY3-004-2026', 21, 73303.00, NULL, 0, 'PNTN-004', 'CSC Regional Office VIII', 'Bachelor of Laws', '3 years of relevant experience', '16 hours of relevant training', 'RA 1080 (Bar)', 'published', 3, '2026-06-17 08:18:17', '2026-07-16 16:00:00', '2026-06-20 08:18:17', '2026-06-22 07:21:04', NULL),
(5, 'Administrative Aide VI', 'CSCRO-AA6-005-2026', 6, NULL, NULL, 0, 'PNTN-005', 'CSC Regional Office VIII', 'Completion of 2 years studies in college', '1 year of relevant experience', '4 hours of relevant training', 'Career Service (Subprofessional) / First Level Eligibility', 'closed', 3, '2026-04-21 08:18:17', '2026-06-15 08:18:17', '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL),
(6, 'Records Officer II', 'CSCRO-RO2-006-2026', 11, NULL, NULL, 0, 'PNTN-006', 'CSC Regional Office VIII', 'Bachelor\'s Degree relevant to the job', '1 year of relevant experience', '4 hours of relevant training', 'Career Service (Professional) / Second Level Eligibility', 'draft', 3, NULL, NULL, '2026-06-20 08:18:17', '2026-06-20 08:18:17', NULL);
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `vacancy_competencies`
--

CREATE TABLE `vacancy_competencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` bigint(20) UNSIGNED NOT NULL,
  `competency_key` varchar(64) NOT NULL,
  `competency_level` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=Basic 2=Intermediate 3=Advanced 4=Superior',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vacancy_competencies`
--

INSERT INTO `vacancy_competencies` (`id`, `vacancy_id`, `competency_key`, `competency_level`, `created_at`, `updated_at`) VALUES
(1, 1, 'exemplifying_integrity', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
(2, 1, 'delivering_service_excellence', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
(3, 1, 'solving_problems_making_decisions', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
(4, 1, 'demonstrating_personal_effectiveness_1', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
<<<<<<< HEAD
(10, 1, 'demonstrating_personal_effectiveness', 2, '2026-06-22 23:22:01', '2026-06-22 23:27:16'),
(11, 1, 'championing_applying_innovation', 2, '2026-06-22 23:22:01', '2026-06-22 23:27:16'),
(12, 1, 'speaking_effectively', 2, '2026-06-22 23:22:01', '2026-06-22 23:27:16'),
(13, 1, 'leading_change', 2, '2026-06-22 23:22:01', '2026-06-22 23:27:16'),
(14, 1, 'building_collaborative_inclusive', 2, '2026-06-22 23:22:01', '2026-06-22 23:27:16'),
(15, 1, 'thinking_strategically_creatively', 2, '2026-06-22 23:22:01', '2026-06-22 23:27:16'),
(16, 1, 'information_technology', 1, '2026-06-22 23:22:01', '2026-06-22 23:22:01'),
(17, 1, 'learning_delivery_evaluation', 1, '2026-06-22 23:22:01', '2026-06-22 23:22:01');
=======
(5, 1, 'speaking_effectively_1', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
(6, 1, 'writing_effectively_1', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
(7, 1, 'championing_and_applying_innovation', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
(8, 1, 'planning_and_delivering', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04'),
(9, 1, 'thinking_strategically', 1, '2026-06-21 07:55:04', '2026-06-21 07:55:04');
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

-- --------------------------------------------------------

--
-- Table structure for table `work_experiences`
--

CREATE TABLE `work_experiences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `applicant_profile_id` bigint(20) UNSIGNED NOT NULL,
  `position_title` varchar(255) NOT NULL,
  `department_agency` varchar(255) NOT NULL,
  `monthly_salary` decimal(12,2) DEFAULT NULL,
  `salary_grade` varchar(255) DEFAULT NULL,
  `appointment_status` varchar(255) DEFAULT NULL,
  `government_service` tinyint(1) NOT NULL DEFAULT 0,
  `date_from` varchar(255) NOT NULL,
  `date_to` varchar(255) DEFAULT NULL,
  `is_present` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_experiences`
--

INSERT INTO `work_experiences` (`id`, `applicant_profile_id`, `position_title`, `department_agency`, `monthly_salary`, `salary_grade`, `appointment_status`, `government_service`, `date_from`, `date_to`, `is_present`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrative Officer II', 'Civil Service Commission Regional Office VIII', NULL, '12', 'Permanent', 1, '2020-01-01', '2020-12-31', 0, '2026-06-20 19:35:36', '2026-06-20 19:35:36'),
(2, 7, 'Administrative Assistant II', 'Civil Service Commission Regional Office VIII', NULL, '8', 'Permanent', 1, '2024-01-02', NULL, 1, '2026-06-20 23:31:06', '2026-06-20 23:31:06'),
(3, 7, 'Administrative Aide VI', 'Civil Service Commission Regional Office VIII', NULL, '6', 'Permanent', 1, '2022-05-10', '2024-01-01', 0, '2026-06-20 23:31:24', '2026-06-20 23:31:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anonymization_tokens`
--
ALTER TABLE `anonymization_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `anonymization_tokens_application_id_unique` (`application_id`),
  ADD UNIQUE KEY `anonymization_tokens_token_unique` (`token`),
  ADD KEY `anonymization_tokens_unmasked_by_foreign` (`unmasked_by`);

--
-- Indexes for table `applicant_profiles`
--
ALTER TABLE `applicant_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applicant_profiles_user_id_foreign` (`user_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applications_vacancy_id_foreign` (`vacancy_id`),
  ADD KEY `applications_applicant_id_foreign` (`applicant_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `bei_ratings`
--
ALTER TABLE `bei_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bei_ratings_application_id_evaluator_id_unique` (`application_id`,`evaluator_id`),
  ADD KEY `bei_ratings_evaluator_id_foreign` (`evaluator_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `competencies`
--
ALTER TABLE `competencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `competencies_competency_key_unique` (`competency_key`);

--
-- Indexes for table `compliance_deadlines`
--
ALTER TABLE `compliance_deadlines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compliance_deadlines_vacancy_id_foreign` (`vacancy_id`);

--
-- Indexes for table `cs_forms`
--
ALTER TABLE `cs_forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cs_forms_application_id_form_type_unique` (`application_id`,`form_type`),
  ADD KEY `cs_forms_generated_by_foreign` (`generated_by`);

--
-- Indexes for table `deliberation_results`
--
ALTER TABLE `deliberation_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deliberation_results_vacancy_id_application_id_action_unique` (`vacancy_id`,`application_id`,`action`),
  ADD KEY `deliberation_results_application_id_foreign` (`application_id`),
  ADD KEY `deliberation_results_decided_by_foreign` (`decided_by`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_application_id_foreign` (`application_id`);

--
-- Indexes for table `educational_attainments`
--
ALTER TABLE `educational_attainments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educational_attainments_applicant_profile_id_foreign` (`applicant_profile_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_results_application_id_exam_type_unique` (`application_id`,`exam_type`),
  ADD KEY `exam_results_encoded_by_foreign` (`encoded_by`);

--
-- Indexes for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_schedules_application_id_foreign` (`application_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `hrmpsb_compositions`
--
ALTER TABLE `hrmpsb_compositions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hrmpsb_compositions_user_id_hrmpsb_role_unique` (`user_id`,`hrmpsb_role`),
  ADD KEY `hrmpsb_compositions_assigned_by_foreign` (`assigned_by`);

--
-- Indexes for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `interview_schedules_application_id_foreign` (`application_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
<<<<<<< HEAD
-- Indexes for table `pre_assessments`
--
ALTER TABLE `pre_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pre_assessments_application_id_unique` (`application_id`),
  ADD KEY `pre_assessments_assessed_by_foreign` (`assessed_by`);

--
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
-- Indexes for table `qs_evaluations`
--
ALTER TABLE `qs_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qs_evaluations_application_id_evaluator_id_unique` (`application_id`,`evaluator_id`),
  ADD KEY `qs_evaluations_evaluator_id_foreign` (`evaluator_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `submission_tracking`
--
ALTER TABLE `submission_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `submission_tracking_application_id_deadline_type_unique` (`application_id`,`deadline_type`),
  ADD KEY `submission_tracking_vacancy_id_foreign` (`vacancy_id`);

--
-- Indexes for table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainings_applicant_profile_id_foreign` (`applicant_profile_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vacancies`
--
ALTER TABLE `vacancies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vacancies_item_number_unique` (`item_number`),
  ADD KEY `vacancies_posted_by_foreign` (`posted_by`);

--
-- Indexes for table `vacancy_competencies`
--
ALTER TABLE `vacancy_competencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vacancy_competencies_vacancy_id_competency_key_unique` (`vacancy_id`,`competency_key`),
  ADD KEY `vacancy_competencies_competency_key_foreign` (`competency_key`);

--
-- Indexes for table `work_experiences`
--
ALTER TABLE `work_experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_experiences_applicant_profile_id_foreign` (`applicant_profile_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anonymization_tokens`
--
ALTER TABLE `anonymization_tokens`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `applicant_profiles`
--
ALTER TABLE `applicant_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `bei_ratings`
--
ALTER TABLE `bei_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competencies`
--
ALTER TABLE `competencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `compliance_deadlines`
--
ALTER TABLE `compliance_deadlines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cs_forms`
--
ALTER TABLE `cs_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliberation_results`
--
ALTER TABLE `deliberation_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `educational_attainments`
--
ALTER TABLE `educational_attainments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrmpsb_compositions`
--
ALTER TABLE `hrmpsb_compositions`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
<<<<<<< HEAD
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
=======
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `pre_assessments`
--
ALTER TABLE `pre_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `qs_evaluations`
--
ALTER TABLE `qs_evaluations`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `submission_tracking`
--
ALTER TABLE `submission_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trainings`
--
ALTER TABLE `trainings`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vacancy_competencies`
--
ALTER TABLE `vacancy_competencies`
<<<<<<< HEAD
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
=======
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

--
-- AUTO_INCREMENT for table `work_experiences`
--
ALTER TABLE `work_experiences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anonymization_tokens`
--
ALTER TABLE `anonymization_tokens`
  ADD CONSTRAINT `anonymization_tokens_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anonymization_tokens_unmasked_by_foreign` FOREIGN KEY (`unmasked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `applicant_profiles`
--
ALTER TABLE `applicant_profiles`
  ADD CONSTRAINT `applicant_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicant_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_vacancy_id_foreign` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bei_ratings`
--
ALTER TABLE `bei_ratings`
  ADD CONSTRAINT `bei_ratings_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bei_ratings_evaluator_id_foreign` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compliance_deadlines`
--
ALTER TABLE `compliance_deadlines`
  ADD CONSTRAINT `compliance_deadlines_vacancy_id_foreign` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cs_forms`
--
ALTER TABLE `cs_forms`
  ADD CONSTRAINT `cs_forms_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cs_forms_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliberation_results`
--
ALTER TABLE `deliberation_results`
  ADD CONSTRAINT `deliberation_results_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliberation_results_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliberation_results_vacancy_id_foreign` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `educational_attainments`
--
ALTER TABLE `educational_attainments`
  ADD CONSTRAINT `educational_attainments_applicant_profile_id_foreign` FOREIGN KEY (`applicant_profile_id`) REFERENCES `applicant_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_encoded_by_foreign` FOREIGN KEY (`encoded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD CONSTRAINT `exam_schedules_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hrmpsb_compositions`
--
ALTER TABLE `hrmpsb_compositions`
  ADD CONSTRAINT `hrmpsb_compositions_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hrmpsb_compositions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  ADD CONSTRAINT `interview_schedules_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
<<<<<<< HEAD
-- Constraints for table `pre_assessments`
--
ALTER TABLE `pre_assessments`
  ADD CONSTRAINT `pre_assessments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pre_assessments_assessed_by_foreign` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
-- Constraints for table `qs_evaluations`
--
ALTER TABLE `qs_evaluations`
  ADD CONSTRAINT `qs_evaluations_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qs_evaluations_evaluator_id_foreign` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_tracking`
--
ALTER TABLE `submission_tracking`
  ADD CONSTRAINT `submission_tracking_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submission_tracking_vacancy_id_foreign` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trainings`
--
ALTER TABLE `trainings`
  ADD CONSTRAINT `trainings_applicant_profile_id_foreign` FOREIGN KEY (`applicant_profile_id`) REFERENCES `applicant_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vacancies`
--
ALTER TABLE `vacancies`
  ADD CONSTRAINT `vacancies_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vacancy_competencies`
--
ALTER TABLE `vacancy_competencies`
  ADD CONSTRAINT `vacancy_competencies_competency_key_foreign` FOREIGN KEY (`competency_key`) REFERENCES `competencies` (`competency_key`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacancy_competencies_vacancy_id_foreign` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_experiences`
--
ALTER TABLE `work_experiences`
  ADD CONSTRAINT `work_experiences_applicant_profile_id_foreign` FOREIGN KEY (`applicant_profile_id`) REFERENCES `applicant_profiles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
