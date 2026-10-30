-- ========================================================
-- StudentHub Portal - Submission Database Dump
-- Target Database: `studenthub`
-- Compatible with MySQL 5.7+ / MariaDB 10.4+ / XAMPP
-- Prepared for GitHub & Academic Submission
-- ========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------


CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fictional Demo Admin Account:
-- Email: admin@studenthub.com
-- Password: Admin@123
INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`) VALUES
(1, 'System Administrator', 'admin@studenthub.com', '$2y$10$eYaMhG3HfQBnq3w383ThIuBjMghheRErYHlZN/4xoeygHgPkia1x2');

-- --------------------------------------------------------
-- Table structure for table `students`
-- --------------------------------------------------------


CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mobile` varchar(10) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `year` tinyint(3) unsigned DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fictional Sample Student Records:
-- Student 1 (Demo Login): teststudent@gmail.com / Student@123
INSERT INTO `students` (`student_id`, `name`, `email`, `password`, `created_at`, `mobile`, `course`, `year`, `gender`) VALUES
(1, 'Demo Student', 'teststudent@gmail.com', '$2y$10$Wd8En8O.mljpfQnfkOFTq.quQ03YM85pX8YbRncFriQlBk2BiZyV6', '2026-10-15 09:00:00', '9876543210', 'Computer Engineering', 2, 'Other'),
(2, 'Alex Morgan', 'alex.morgan@example.com', '$2y$10$Wd8En8O.mljpfQnfkOFTq.quQ03YM85pX8YbRncFriQlBk2BiZyV6', '2026-10-16 10:15:00', '9876543211', 'Computer Engineering', 3, 'Male'),
(3, 'Samantha Reed', 'samantha.reed@example.com', '$2y$10$Wd8En8O.mljpfQnfkOFTq.quQ03YM85pX8YbRncFriQlBk2BiZyV6', '2026-10-16 11:30:00', '9876543212', 'Information Technology', 2, 'Female'),
(4, 'Jordan Lee', 'jordan.lee@example.com', '$2y$10$Wd8En8O.mljpfQnfkOFTq.quQ03YM85pX8YbRncFriQlBk2BiZyV6', '2026-10-17 08:45:00', '9876543213', 'Computer Engineering', 1, 'Male'),
(5, 'Taylor Swift', 'taylor.swift@example.com', '$2y$10$Wd8En8O.mljpfQnfkOFTq.quQ03YM85pX8YbRncFriQlBk2BiZyV6', '2026-10-17 14:20:00', '9876543214', 'Mechanical Engineering', 4, 'Female');

-- --------------------------------------------------------
-- Table structure for table `events`
-- --------------------------------------------------------


CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `poster` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fictional Sample College Events:
INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `location`, `created_at`, `poster`) VALUES
(1, 'Web Development Workshop', 'Hands-on training covering HTML5, CSS3, JavaScript and responsive web frameworks.', '2026-11-15', 'Computer Lab 1', '2026-10-10 09:00:00', NULL),
(2, 'Annual Coding Competition', 'Competitive programming contest solving algorithmic challenges in C++, Java and Python.', '2026-11-20', 'Main Seminar Hall', '2026-10-10 09:15:00', NULL),
(3, 'AI and Machine Learning Seminar', 'Introductory seminar on neural networks, LLMs and contemporary AI applications.', '2026-11-25', 'Auditorium', '2026-10-10 09:30:00', NULL),
(4, 'Cyber Security Awareness Summit', 'Best practices for web application defense, authentication security and threat prevention.', '2026-12-05', 'Innovation Center', '2026-10-10 10:00:00', NULL),
(5, 'Annual Project Exhibition', 'Showcase of senior capstone projects, engineering prototypes and software solutions.', '2026-12-10', 'Exhibition Ground', '2026-10-10 10:30:00', NULL);

-- --------------------------------------------------------
-- Table structure for table `registrations`
-- --------------------------------------------------------


CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `student_id` (`student_id`,`event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample Registrations linking students to events:
INSERT INTO `registrations` (`registration_id`, `student_id`, `event_id`, `registration_date`) VALUES
(1, 1, 1, '2026-10-18 10:00:00'),
(2, 1, 2, '2026-10-18 10:05:00'),
(3, 2, 1, '2026-10-18 11:20:00'),
(4, 3, 3, '2026-10-19 14:15:00');

-- --------------------------------------------------------
-- Table structure for table `remember_tokens`
-- --------------------------------------------------------


CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `student_id` (`student_id`),
  KEY `fk_remember_admin` (`admin_id`),
  CONSTRAINT `fk_remember_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE,
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Note: `remember_tokens` is populated dynamically upon user "Remember Me" logins and rotated on use.
-- No stale or personal tokens are seeded.

-- --------------------------------------------------------
-- Table structure for table `audit_logs`
-- --------------------------------------------------------

;
CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fictional Sample Administrative Audit Trail:
INSERT INTO `audit_logs` (`log_id`, `admin_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'System Initialization', 'Portal database seeded and configured with sample data.', '2026-10-15 08:30:00'),
(2, 1, 'Added Event', 'Created Web Development Workshop (Event ID: 1)', '2026-10-15 09:00:00'),
(3, 1, 'Added Event', 'Created Annual Coding Competition (Event ID: 2)', '2026-10-15 09:15:00'),
(4, 1, 'Viewed Admin Dashboard', 'Admin accessed the administrative control panel.', '2026-10-20 10:00:00');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

