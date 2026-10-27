-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 04:16 PM
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
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `location`, `created_at`) VALUES
(1, 'Web Development Workshop', 'Learn the basics of web development', '2026-11-15', 'Computer Lab', '2026-10-27 14:44:25'),
(2, 'Coding Competition', 'A programming competition for students', '2026-11-20', 'Seminar Hall', '2026-10-27 14:44:25'),
(3, 'AI and Machine Learning Seminar', 'Introduction to AI and machine learning applications', '2026-11-25', 'Seminar Hall', '2026-10-27 14:57:57'),
(4, 'Web Design Challenge', 'Design a responsive website for a given theme', '2026-12-02', 'Computer Lab 1', '2026-10-27 14:57:57'),
(5, 'Cyber Security Awareness', 'Learn about online safety and cyber threats', '2026-12-05', 'Auditorium', '2026-10-27 14:57:57'),
(6, 'Project Exhibition', 'Showcase student projects and innovations', '2026-12-10', 'Main Hall', '2026-10-27 14:57:57'),
(7, 'Robotics Workshop', 'Hands-on introduction to robotics', '2026-12-15', 'Innovation Lab', '2026-10-27 14:57:57'),
(8, 'Career Guidance Session', 'Explore career paths and interview preparation', '2027-01-08', 'Seminar Hall', '2026-10-27 14:57:57'),
(9, 'Tech Quiz Competition', 'A quiz covering programming and technology', '2027-01-15', 'Computer Lab 2', '2026-10-27 14:57:57'),
(10, 'Annual Cultural Festival', 'A celebration of student talent and creativity', '2027-01-25', 'College Ground', '2026-10-27 14:57:57');

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
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Aarav Patel', 'aarav@example.com', 'demo_hash_1', '2026-10-27 14:44:25'),
(2, 'Diya Shah', 'diya@example.com', 'demo_hash_2', '2026-10-27 14:44:25'),
(3, 'Riya Sharma', 'riya@example.com', 'seed_only', '2026-10-27 14:56:20'),
(4, 'Krish Patel', 'krish@example.com', 'seed_only', '2026-10-27 14:56:20'),
(5, 'Ananya Desai', 'ananya@example.com', 'seed_only', '2026-10-27 14:56:20'),
(6, 'Dev Shah', 'dev@example.com', 'seed_only', '2026-10-27 14:56:20'),
(7, 'Isha Mehta', 'isha@example.com', 'seed_only', '2026-10-27 14:56:20'),
(8, 'Arjun Joshi', 'arjun@example.com', 'seed_only', '2026-10-27 14:56:20'),
(9, 'Kavya Shah', 'kavya@example.com', 'seed_only', '2026-10-27 14:56:20'),
(10, 'Reyansh Patel', 'reyansh@example.com', 'seed_only', '2026-10-27 14:56:20'),
(11, 'Myra Trivedi', 'myra@example.com', 'seed_only', '2026-10-27 14:56:20'),
(12, 'Dhruv Modi', 'dhruv@example.com', 'seed_only', '2026-10-27 14:56:20'),
(13, 'Aadhya Shah', 'aadhya@example.com', 'seed_only', '2026-10-27 14:56:20'),
(14, 'Vivaan Desai', 'vivaan@example.com', 'seed_only', '2026-10-27 14:56:20'),
(15, 'Saanvi Mehta', 'saanvi@example.com', 'seed_only', '2026-10-27 14:56:20'),
(16, 'Aditya Joshi', 'aditya@example.com', 'seed_only', '2026-10-27 14:56:20'),
(17, 'Pari Patel', 'pari@example.com', 'seed_only', '2026-10-27 14:56:20'),
(18, 'Yash Thakkar', 'yash@example.com', 'seed_only', '2026-10-27 14:56:20'),
(19, 'Nisha Parmar', 'nisha@example.com', 'seed_only', '2026-10-27 14:56:20'),
(20, 'Manav Shah', 'manav@example.com', 'seed_only', '2026-10-27 14:56:20');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
