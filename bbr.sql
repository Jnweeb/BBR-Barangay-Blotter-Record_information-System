-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 02:46 PM
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
-- Database: `bbr`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `log_time` datetime NOT NULL DEFAULT current_timestamp(),
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `log_time`, `table_name`, `record_id`) VALUES
(1, 2, 'Added complainant #1 (jan lloyd blanco)', '2025-11-27 21:44:27', 'complainants', 1),
(2, 2, 'Added respondent #1 (Kagawad)', '2025-11-27 21:44:27', 'respondents', 1),
(3, 2, 'Added blotter record #1', '2025-11-27 21:44:27', 'blotter_records', 1),
(4, 2, 'Added blotter record #1 (Noise Complaint at Maharlika HWay, Tinambacan)', '2025-11-27 21:44:27', 'blotter_records', 1);

-- --------------------------------------------------------

--
-- Table structure for table `blotter_records`
--

CREATE TABLE `blotter_records` (
  `id` int(11) NOT NULL,
  `incident_datetime` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `case_type` varchar(150) NOT NULL,
  `complainant` int(11) NOT NULL,
  `respondent` int(11) NOT NULL,
  `incident_summary` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blotter_records`
--

INSERT INTO `blotter_records` (`id`, `incident_datetime`, `location`, `case_type`, `complainant`, `respondent`, `incident_summary`, `created_by`, `created_at`) VALUES
(1, '2025-11-27 21:43:00', 'Maharlika HWay, Tinambacan', 'Noise Complaint', 1, 1, 'Videoke in the neighborhood is in max volume and is making a lot of noise', 2, '2025-11-27 13:44:27');

--
-- Triggers `blotter_records`
--
DELIMITER $$
CREATE TRIGGER `after_blotter_insert` AFTER INSERT ON `blotter_records` FOR EACH ROW INSERT INTO activity_log (user_id, action, table_name, record_id, log_time)
VALUES (NEW.created_by, CONCAT('Added blotter record #', NEW.id), 'blotter_records', NEW.id, NOW())
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `complainants`
--

CREATE TABLE `complainants` (
  `id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complainants`
--

INSERT INTO `complainants` (`id`, `full_name`, `age`, `contact`, `address`, `created_at`) VALUES
(1, 'jan lloyd blanco', 21, '0990909090', 'Tinambacan Norte', '2025-11-27 13:44:27');

-- --------------------------------------------------------

--
-- Table structure for table `respondents`
--

CREATE TABLE `respondents` (
  `id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `respondents`
--

INSERT INTO `respondents` (`id`, `full_name`, `age`, `contact`, `address`, `created_at`) VALUES
(1, 'Kagawad', 25, '09090909090', 'Tinambacan Norte', '2025-11-27 13:44:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Official','Secretary') NOT NULL DEFAULT 'Official',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin', '$2y$10$XZeu/tX/C7hZj87LVliaoOqxtkJ.L2yiBdwEKYxVD877OS7b.2F2a', 'Admin', 'active', '2025-11-27 13:36:18', '2025-11-27 13:40:33'),
(2, 'Janlloyd Blanco', 'Jnllydblnc', '$2y$10$8EYD0yB86/bE9l.Ugx19PuP1I5kcjwjDiudcBdPSZlQDv13.Qywpm', 'Admin', 'active', '2025-11-27 13:42:32', '2025-11-27 13:42:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blotter_records`
--
ALTER TABLE `blotter_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_complainant` (`complainant`),
  ADD KEY `fk_respondent` (`respondent`);

--
-- Indexes for table `complainants`
--
ALTER TABLE `complainants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `respondents`
--
ALTER TABLE `respondents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blotter_records`
--
ALTER TABLE `blotter_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complainants`
--
ALTER TABLE `complainants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `respondents`
--
ALTER TABLE `respondents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blotter_records`
--
ALTER TABLE `blotter_records`
  ADD CONSTRAINT `blotter_records_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_complainant` FOREIGN KEY (`complainant`) REFERENCES `complainants` (`id`),
  ADD CONSTRAINT `fk_respondent` FOREIGN KEY (`respondent`) REFERENCES `respondents` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
