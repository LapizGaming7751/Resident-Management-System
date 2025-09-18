-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 08:38 AM
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
-- Database: `finals_scanner`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `access_level` int(1) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user`, `pass`, `access_level`, `email`) VALUES
(1, 'AdminA', '$2a$12$F9lWppszbUrl1Cc4ZvTFyey9XPjdR9t.Wz84jPdF0FJSlxuwxtcsS', 2, ''),
(2, 'AdminB', '$2a$12$iQPXC3/R/kfhO0.2WEQeOOJSLlTagHrY6yAnrvAnrEHLku9LnezqW', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `post_time` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `post_time`, `content`) VALUES
(3, 'Third Announcement Test', '2025-09-12 11:42:32.000', 'Because we accidentally\n\ndeleted the second announcement'),
(4, 'Welcome to Sky Condominium', '2025-09-12 11:39:33.000', 'Make sure to follow the rules!\n\nWe look forward to your stay');

-- --------------------------------------------------------

--
-- Table structure for table `calls`
--

CREATE TABLE `calls` (
  `id` int(11) NOT NULL,
  `security_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `offer` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `ice_candidates` text DEFAULT NULL,
  `status` enum('pending','active','ended') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `codes`
--

CREATE TABLE `codes` (
  `id` int(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_by` int(255) NOT NULL,
  `expiry` datetime(3) NOT NULL,
  `intended_visitor` varchar(255) NOT NULL,
  `plate_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `codes`
--

INSERT INTO `codes` (`id`, `token`, `created_by`, `expiry`, `intended_visitor`, `plate_id`) VALUES
(18, 'f5abce143c', 1, '2025-09-11 15:14:56.000', 'ABC', 'ABC 4321'),
(19, '2ec1287b41', 1, '2025-09-19 13:36:00.000', 'Andrew', 'PJK 7751');

-- --------------------------------------------------------

--
-- Table structure for table `invite_codes`
--

CREATE TABLE `invite_codes` (
  `id` int(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `user_type` enum('resident','security') NOT NULL,
  `email` varchar(255) NOT NULL,
  `room_code` varchar(255) DEFAULT NULL,
  `created_by` int(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invite_codes`
--

INSERT INTO `invite_codes` (`id`, `code`, `user_type`, `email`, `room_code`, `created_by`, `created_at`, `expires_at`, `is_used`, `used_at`) VALUES
(1, '07F58B8EEECE4304', 'resident', 'lapizgaming7751@gmail.com', '16-03-A3', 1, '2025-09-17 09:50:13', '2025-09-18 09:50:13', 1, '2025-09-17 09:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `scan_time` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `scan_type` enum('In','Out') NOT NULL,
  `scan_by` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `token`, `scan_time`, `scan_type`, `scan_by`) VALUES
(39, 'f5abce143c', '2025-09-11 15:14:50.592', 'In', 1),
(40, 'f5abce143c', '2025-09-11 15:14:56.384', 'Out', 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(255) NOT NULL,
  `sender_id` int(255) NOT NULL,
  `sender_type` enum('security','resident') NOT NULL,
  `receiver_id` int(255) NOT NULL,
  `receiver_type` enum('security','resident') NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `sender_type`, `receiver_id`, `receiver_type`, `message`, `created_at`, `is_read`) VALUES
(1, 1, 'security', 1, 'resident', 'testing message', '2025-04-04 09:01:53', 0),
(2, 1, 'resident', 1, 'security', 'also testing message', '2025-04-04 09:08:17', 0),
(3, 1, 'resident', 1, 'security', 'abc', '2025-09-03 14:05:26', 0),
(4, 1, 'security', 2, 'resident', 'Good morning', '2025-09-03 14:16:11', 0),
(5, 1, 'resident', 1, 'security', 'Hello!', '2025-09-09 10:10:15', 0),
(6, 1, 'resident', 1, 'security', 'abcd', '2025-09-11 13:37:54', 0),
(7, 1, 'security', 1, 'resident', 'bbac', '2025-09-11 13:48:35', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(255) NOT NULL,
  `resident_id` int(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_type` enum('resident','security','admin') NOT NULL,
  `user_id` int(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `token`, `user_type`, `user_id`, `email`, `created_at`, `expires_at`, `is_used`, `used_at`) VALUES
(5, '7b44ac44f924242980b8f213f5e37872f1eb005f6457f5e533c55b75e544e30f', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 10:55:48', '2025-09-17 11:55:48', 1, NULL),
(9, '1ef72090c40749dd22d2763561fd2b9268634c47c310a2ad7227b4df59e0cbc8', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 11:47:46', '2025-09-17 12:47:46', 1, NULL),
(10, 'ee7177b4fd739e962a9de970c5f6dd5f65771e0cc7b2daabf2960311264aa2f7', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 12:48:23', '2025-09-17 13:48:23', 1, NULL),
(11, 'a01a95ce8f70999d5d1afdc52d586c259d9deefc32fdc32f19940dceef9772b6', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 12:48:26', '2025-09-17 13:48:26', 1, NULL),
(13, '749a998b44164de7512559dc88caea6c9679ed161439eb730543dee7e3cf8cf9', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 13:04:10', '2025-09-17 14:04:10', 1, NULL),
(14, '6132851624432b704312be715289d351e07e798d2b9c1a80885222f8db9c5d6d', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 13:06:33', '2025-09-17 14:06:33', 1, NULL),
(15, '0c6da6fef090e3071cd6200b279856b883f04c2e7d793b184a398d9002ca678b', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 13:08:40', '2025-09-17 14:08:40', 1, NULL),
(16, '4a7463cd175307f1590bf8a7de65241dde5dd3c4dbba2731b0a827432929d4c9', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 13:17:04', '2025-09-17 14:17:04', 1, NULL),
(17, '83b06ca5e0ffee78994bb8b9d19d438112dbd4531f37b3e12423db83a7b1bef6', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 13:30:30', '2025-09-17 14:30:30', 1, NULL),
(18, 'f9e997a0885cced49cd6d7450c78630bb6ee90bce25a6f7b57b67934f5ab2762', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 13:57:10', '2025-09-17 14:57:10', 1, NULL),
(19, '0ea72eefc3abb4f3346ff5e83a0c333d7ddd3a9f8ce39243398e5cbc1a7c6475', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 13:59:54', '2025-09-17 14:59:54', 1, NULL),
(20, '4693647905fff898f493b9d3991d700c049dff43aedfdf150fa7a7259d95436c', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 14:12:45', '2025-09-17 15:12:45', 1, NULL),
(21, 'fbd73aa3b364d4fed1f5225dddeba7e7146e84fd5ef868e2f2507d4dd6c46f45', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 14:12:48', '2025-09-17 15:12:48', 1, NULL),
(22, '34f2e5a1d9e9fa3c78600421d48d16b0e1176e7f1b3714bfd1782c9b3fa87569', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 14:16:45', '2025-09-17 15:16:45', 1, NULL),
(23, '74d4f103620055cbc6b78b590f44bfb4c691c9b3545c2fb927e809c522743862', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 14:17:10', '2025-09-17 15:17:10', 1, NULL),
(24, 'ad3d9129568de38d69f7038c2b581df86c5abf2a29f8c746bd1a31e98bc08756', 'resident', 3, 'lapizgaming7751@gmail.com', '2025-09-17 14:35:37', '2025-09-17 15:35:37', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` int(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `room_code` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `user`, `pass`, `room_code`, `is_active`, `email`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'TestingA', '$2a$12$lgoh9LdftOu80NItsklYVuqammy2nz0Pj5tIX7sbqSlksQx/OepSq', '13-10-B6', 1, '', NULL, NULL),
(2, 'TestingB', '$2a$12$QGQxxtUsddp462cR3YSnlO8CnXebddTD65muXaNA5SPMTy99Lkqwa', '16-08-C5', 1, '', NULL, NULL),
(3, 'CharlieQuixote', '$2y$10$95Uwks3Zj.EkD0V3kXPgcuRbiYbK9wbTC8O8Ww5N9zeXu1npGhoKC', '16-03-A3', 1, 'lapizgaming7751@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `security`
--

CREATE TABLE `security` (
  `id` int(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security`
--

INSERT INTO `security` (`id`, `user`, `pass`, `email`, `is_active`) VALUES
(1, 'security1', '$2a$12$LYQEUibSF0lYBwyuirxkkeDe2oTOvregbT/Mt7dzcLmahb9Qdh1wy', '', 1),
(2, 'security2', '$2a$12$K7CdxdmG1Xbhn4n1xc0suOGEsuwro8LqRRSCvCmGcUuIZ6/a0JtQO', '', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calls`
--
ALTER TABLE `calls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_id` (`security_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `codes`
--
ALTER TABLE `codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invite_codes`
--
ALTER TABLE `invite_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scan_time` (`scan_time`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `security`
--
ALTER TABLE `security`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `calls`
--
ALTER TABLE `calls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `codes`
--
ALTER TABLE `codes`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `invite_codes`
--
ALTER TABLE `invite_codes`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `security`
--
ALTER TABLE `security`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calls`
--
ALTER TABLE `calls`
  ADD CONSTRAINT `calls_ibfk_1` FOREIGN KEY (`security_id`) REFERENCES `security` (`id`),
  ADD CONSTRAINT `calls_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `codes`
--
ALTER TABLE `codes`
  ADD CONSTRAINT `codes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `residents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invite_codes`
--
ALTER TABLE `invite_codes`
  ADD CONSTRAINT `invite_codes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
