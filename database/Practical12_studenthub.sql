-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 06:33 PM
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
-- Database: `studenthub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`) VALUES
(1, 'Admin', 'admin@studenthub.com', '$2y$10$xfnQiXLO4jArbmfBsnCwyOcaEymkqv/g88Lbbdper7jmP0IqCtOYC');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `poster` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `location`, `created_at`, `poster`) VALUES
(1, 'Web Development Workshop', 'Learn the basics of web development', '2026-11-15', 'Computer Lab', '2026-10-27 14:44:25', NULL),
(2, 'Coding Competition', 'A programming competition for students', '2026-11-20', 'Seminar Hall', '2026-10-27 14:44:25', NULL),
(3, 'AI and Machine Learning Seminar', 'Introduction to AI and machine learning applications', '2026-11-25', 'Seminar Hall', '2026-10-27 14:57:57', NULL),
(4, 'Web Design Challenge', 'Design a responsive website for a given theme', '2026-12-02', 'Computer Lab 1', '2026-10-27 14:57:57', NULL),
(5, 'Cyber Security Awareness', 'Learn about online safety and cyber threats', '2026-12-05', 'Auditorium', '2026-10-27 14:57:57', NULL),
(6, 'Project Exhibition', 'Showcase student projects and innovations', '2026-12-10', 'Main Hall', '2026-10-27 14:57:57', NULL),
(7, 'Robotics Workshop', 'Hands-on introduction to robotics', '2026-12-15', 'Innovation Lab', '2026-10-27 14:57:57', NULL),
(8, 'Career Guidance Session', 'Explore career paths and interview preparation', '2027-01-08', 'Seminar Hall', '2026-10-27 14:57:57', NULL),
(9, 'Tech Quiz Competition', 'A quiz covering programming and technology', '2027-01-15', 'Computer Lab 2', '2026-10-27 14:57:57', NULL),
(10, 'Annual Cultural Festival', 'A celebration of student talent and creativity', '2027-01-25', 'College Ground', '2026-10-27 14:57:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `student_id`, `event_id`, `registration_date`) VALUES
(1, 1, 1, '2026-10-27 15:11:17'),
(2, 2, 2, '2026-10-27 15:11:17');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mobile` varchar(10) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `year` tinyint(3) UNSIGNED DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `name`, `email`, `password`, `created_at`, `mobile`, `course`, `year`, `gender`) VALUES
(1, 'Aarav Patel', 'aarav@example.com', 'demo_hash_1', '2026-10-27 14:44:25', NULL, NULL, NULL, NULL),
(2, 'Diya Shah', 'diya@example.com', 'demo_hash_2', '2026-10-27 14:44:25', NULL, NULL, NULL, NULL),
(3, 'Riya Sharma', 'riya@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(4, 'Krish Patel', 'krish@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(5, 'Ananya Desai', 'ananya@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(6, 'Dev Shah', 'dev@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(7, 'Isha Mehta', 'isha@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(8, 'Arjun Joshi', 'arjun@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(9, 'Kavya Shah', 'kavya@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(10, 'Reyansh Patel', 'reyansh@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(11, 'Myra Trivedi', 'myra@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(12, 'Dhruv Modi', 'dhruv@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(13, 'Aadhya Shah', 'aadhya@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(14, 'Vivaan Desai', 'vivaan@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(15, 'Saanvi Mehta', 'saanvi@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(16, 'Aditya Joshi', 'aditya@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(17, 'Pari Patel', 'pari@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(18, 'Yash Thakkar', 'yash@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(19, 'Nisha Parmar', 'nisha@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(20, 'Manav Shah', 'manav@example.com', 'seed_only', '2026-10-27 14:56:20', NULL, NULL, NULL, NULL),
(21, 'Miku', 'kevli31122010@gmail.com', '$2y$10$Boayyx/nOhyRQ4zKWpOJl.bRhHyever1fIl/hILwvd33mlPue5jTq', '2026-10-27 17:03:11', '9876543210', 'Computer Engineering', 1, 'Female'),
(23, 'Prachi', 'prachi23@gmail.com', '$2y$10$eZ0ppyKww8ortSqH9JFFTePhyYOOf88K5te6pBlpLWatrYyooKVle', '2026-10-28 04:40:40', '9876543210', 'Computer Engineering', 1, 'Female'),
(24, 'Hemangini', 'hem10@gamil.com', '$2y$10$MKjyEvOWnv6K1hnjABA84e6PHY6m9yzhs6T26fQH2OHUaq4lkHjQG', '2026-10-28 05:08:04', '9876543210', 'Computer Engineering', 3, 'Female'),
(25, 'Khushi', 'khushi@gmail.com', '$2y$10$sJlMHiLRvj2u8aPhJtu8pulbEVSpj/b4qylsie3vKhXX4GWINSpC6', '2026-10-28 05:25:35', '9876543210', 'Computer Engineering', 1, 'Female');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_remember_admin` (`admin_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_remember_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
