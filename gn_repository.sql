-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2026 at 06:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gn_repository`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `marked_by` int(11) NOT NULL,
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `session_id`, `student_id`, `status`, `marked_by`, `marked_at`) VALUES
(2, 2, 8, 'present', 5, '2026-04-16 07:40:16'),
(3, 2, 3, 'present', 5, '2026-04-16 07:40:16'),
(4, 3, 20, 'present', 5, '2026-04-16 16:12:16'),
(5, 3, 19, 'present', 5, '2026-04-16 16:12:16'),
(6, 6, 23, 'present', 5, '2026-04-16 16:13:15'),
(7, 7, 8, 'present', 5, '2026-04-16 16:15:31'),
(8, 7, 3, 'present', 5, '2026-04-16 16:15:31'),
(9, 8, 24, 'present', 5, '2026-04-16 16:19:35'),
(10, 8, 25, 'present', 5, '2026-04-16 16:19:35'),
(11, 8, 26, 'present', 5, '2026-04-16 16:19:35'),
(12, 8, 8, 'present', 5, '2026-04-16 16:19:35'),
(13, 8, 27, 'present', 5, '2026-04-16 16:19:35'),
(14, 8, 3, 'present', 5, '2026-04-16 16:19:35'),
(15, 9, 24, 'present', 5, '2026-04-16 17:51:21'),
(16, 9, 25, 'present', 5, '2026-04-16 17:51:21'),
(17, 9, 26, 'present', 5, '2026-04-16 17:51:21'),
(18, 9, 8, 'present', 5, '2026-04-16 17:51:21'),
(19, 9, 27, 'late', 5, '2026-04-16 17:51:21'),
(20, 9, 3, 'absent', 5, '2026-04-16 17:51:21'),
(21, 10, 24, 'present', 5, '2026-04-16 17:51:52'),
(22, 10, 25, 'absent', 5, '2026-04-16 17:51:52'),
(23, 10, 26, 'present', 5, '2026-04-16 17:51:52'),
(24, 10, 8, 'late', 5, '2026-04-16 17:51:52'),
(25, 10, 27, 'present', 5, '2026-04-16 17:51:52'),
(26, 10, 3, 'absent', 5, '2026-04-16 17:51:52');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_summary`
--

CREATE TABLE `attendance_summary` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `total_classes` int(11) DEFAULT 0,
  `present_count` int(11) DEFAULT 0,
  `absent_count` int(11) DEFAULT 0,
  `late_count` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_summary`
--

INSERT INTO `attendance_summary` (`id`, `student_id`, `subject_id`, `total_classes`, `present_count`, `absent_count`, `late_count`, `percentage`, `updated_at`) VALUES
(2, 3, 26, 2, 1, 1, 0, 50.00, '2026-04-16 17:51:52'),
(3, 8, 26, 2, 1, 0, 1, 50.00, '2026-04-16 17:51:52'),
(4, 19, 67, 1, 1, 0, 0, 100.00, '2026-04-16 16:12:16'),
(5, 20, 67, 1, 1, 0, 0, 100.00, '2026-04-16 16:12:16'),
(6, 23, 73, 1, 1, 0, 0, 100.00, '2026-04-16 16:13:15'),
(7, 3, 27, 2, 2, 0, 0, 100.00, '2026-04-16 16:19:35'),
(8, 8, 27, 2, 2, 0, 0, 100.00, '2026-04-16 16:19:35'),
(11, 24, 27, 1, 1, 0, 0, 100.00, '2026-04-16 16:19:35'),
(12, 25, 27, 1, 1, 0, 0, 100.00, '2026-04-16 16:19:35'),
(13, 26, 27, 1, 1, 0, 0, 100.00, '2026-04-16 16:19:35'),
(14, 27, 27, 1, 1, 0, 0, 100.00, '2026-04-16 16:19:35'),
(15, 3, 28, 1, 0, 1, 0, 0.00, '2026-04-16 17:51:21'),
(16, 8, 28, 1, 1, 0, 0, 100.00, '2026-04-16 17:51:21'),
(17, 24, 28, 1, 1, 0, 0, 100.00, '2026-04-16 17:51:21'),
(18, 25, 28, 1, 1, 0, 0, 100.00, '2026-04-16 17:51:21'),
(19, 26, 28, 1, 1, 0, 0, 100.00, '2026-04-16 17:51:21'),
(20, 27, 28, 1, 0, 0, 1, 0.00, '2026-04-16 17:51:21'),
(23, 24, 26, 1, 1, 0, 0, 100.00, '2026-04-16 17:51:52'),
(24, 25, 26, 1, 0, 1, 0, 0.00, '2026-04-16 17:51:52'),
(25, 26, 26, 1, 1, 0, 0, 100.00, '2026-04-16 17:51:52'),
(26, 27, 26, 1, 1, 0, 0, 100.00, '2026-04-16 17:51:52');

-- --------------------------------------------------------

--
-- Table structure for table `class_sessions`
--

CREATE TABLE `class_sessions` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `branch` varchar(10) NOT NULL,
  `year` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `lecture_topic` varchar(200) DEFAULT NULL,
  `lecture_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_sessions`
--

INSERT INTO `class_sessions` (`id`, `faculty_id`, `subject_id`, `branch`, `year`, `semester`, `lecture_topic`, `lecture_date`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(2, 2, 26, 'CSE', 3, 8, 'Seminar-1', '2026-04-16', '10:40:00', '11:40:00', 'completed', '2026-04-16 07:40:08'),
(3, 2, 67, 'AIML', 3, 5, 'dgdgdg', '2026-04-16', '18:11:00', '11:11:00', 'completed', '2026-04-16 16:11:25'),
(4, 2, 63, 'AIML', 3, 3, 'UNIT-1', '2026-04-16', '18:11:00', '00:20:00', 'completed', '2026-04-16 16:12:04'),
(5, 2, 61, 'AIML', 3, 2, 'Seminar-1', '2026-04-16', '18:12:00', '07:12:00', 'scheduled', '2026-04-16 16:12:42'),
(6, 2, 73, 'AIML', 3, 8, 'dgdgdg', '2026-04-16', '18:12:00', '02:20:00', 'completed', '2026-04-16 16:13:03'),
(7, 2, 27, 'CSE', 3, 8, 'UNIT-1', '2026-04-16', '18:14:00', '00:30:00', 'completed', '2026-04-16 16:15:14'),
(8, 2, 27, 'CSE', 3, 8, 'UNIT-1', '2026-04-16', '00:30:00', '00:00:00', 'completed', '2026-04-16 16:19:28'),
(9, 2, 28, 'CSE', 3, 8, 'Seminar-5', '2026-04-16', '02:20:00', '00:00:00', 'completed', '2026-04-16 16:20:01'),
(10, 2, 26, 'CSE', 3, 8, 'lecture-8', '2026-04-16', '02:40:00', '11:45:00', 'completed', '2026-04-16 17:51:01');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `faculty_id` varchar(20) NOT NULL,
  `department` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `college` varchar(100) DEFAULT 'GNIT',
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `user_id`, `faculty_id`, `department`, `subject`, `college`, `is_approved`, `approved_by`, `approved_at`, `created_at`) VALUES
(2, 5, 'GNITFY202601', 'CSE', 'Blockchain & its Appliccations', 'GNIT', 0, NULL, NULL, '2026-04-03 13:23:51'),
(3, 108, 'GNITFY202422', 'CSEDS', 'AIML', 'GNIT', 0, NULL, NULL, '2026-04-15 21:56:49'),
(4, 110, 'GNITFY202423', 'AIDS', 'DBMS', 'GNIT', 0, NULL, NULL, '2026-04-15 21:58:52'),
(5, 131, 'FAC2024001', 'CSE', 'Data Structures & Algorithms', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(6, 132, 'FAC2024002', 'CSE', 'Database Management Systems', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(7, 133, 'FAC2024003', 'CSE', 'Operating Systems', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(8, 134, 'FAC2024004', 'IT', 'Web Technologies', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(9, 135, 'FAC2024005', 'IT', 'Computer Networks', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(10, 136, 'FAC2024006', 'AIDS', 'Machine Learning', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(11, 137, 'FAC2024007', 'AIDS', 'Artificial Intelligence', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(12, 138, 'FAC2024008', 'AIML', 'Deep Learning', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(13, 139, 'FAC2024009', 'AIML', 'Natural Language Processing', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(14, 140, 'FAC2024010', 'CSE', 'Blockchain & Its Applications', 'GNIT', 0, NULL, NULL, '2026-04-16 07:37:19'),
(15, 149, 'GNITFY2024222', 'CSE', 'AI', 'GNIT', 0, NULL, NULL, '2026-04-16 18:08:15'),
(16, 150, '123', 'CSEDS', 'TOC', 'GNIT', 0, NULL, NULL, '2026-04-16 18:23:40');

-- --------------------------------------------------------

--
-- Stand-in structure for view `faculty_details`
-- (See below for the actual view)
--
CREATE TABLE `faculty_details` (
`faculty_record_id` int(11)
,`faculty_id` varchar(20)
,`department` varchar(50)
,`primary_subject` varchar(100)
,`college` varchar(100)
,`user_id` int(11)
,`email` varchar(100)
,`first_name` varchar(50)
,`last_name` varchar(50)
,`full_name` varchar(101)
,`registered_on` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `resource_type` enum('note','video','assignment','syllabus','pyq') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `branch` varchar(10) NOT NULL,
  `year` int(11) NOT NULL CHECK (`year` between 1 and 4),
  `semester` int(11) NOT NULL CHECK (`semester` between 1 and 8),
  `file_path` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `views_count` int(11) DEFAULT 0,
  `downloads_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `title`, `resource_type`, `subject`, `subject_code`, `branch`, `year`, `semester`, `file_path`, `video_url`, `author_id`, `author_name`, `description`, `is_approved`, `views_count`, `downloads_count`, `created_at`, `updated_at`) VALUES
(1, 'lecture-1', 'video', 'Blockchain & its Appliccations', '802-BA', 'CSE', 4, 8, NULL, 'https://www.youtube.com/embed/eSCrjZdzpn8', 5, 'pooja jaiswal', 'lecture-1', 1, 1, 0, '2026-04-03 13:27:22', '2026-04-03 13:30:00'),
(2, 'unit-1', 'note', 'Blockchain & its Appliccations', '802-BA', 'CSE', 4, 8, 'uploads/1775222896_BlockChainallQuestions.docx', '', 5, 'pooja jaiswal', 'notes unit-1', 1, 1, 1, '2026-04-03 13:28:16', '2026-04-03 13:30:05'),
(3, 'Unit-1 Assignment', 'assignment', 'Blockchain & its Appliccations', '802-BA', 'CSE', 4, 8, 'uploads/1775222942_BlockChainallQuestions.docx', '', 5, 'pooja jaiswal', '', 1, 1, 1, '2026-04-03 13:29:02', '2026-04-03 13:30:10'),
(4, 'dsfsf', 'syllabus', 'Blockchain & its Appliccations', '802-BA', 'CSE', 4, 8, '', '', 5, 'pooja jaiswal', 'Block Chain& its Applications Syllabus', 1, 0, 0, '2026-04-16 16:08:36', '2026-04-16 16:08:36'),
(5, 'Blockchain & its Aplications -PYQs', 'pyq', 'Blockchain & its Appliccations', '802-BA', 'CSE', 4, 8, 'uploads/1776355806_GPLSchedule_Final.pdf', '', 5, 'pooja jaiswal', 'PYQS', 1, 0, 0, '2026-04-16 16:10:06', '2026-04-16 16:10:06');

-- --------------------------------------------------------

--
-- Stand-in structure for view `resource_details`
-- (See below for the actual view)
--
CREATE TABLE `resource_details` (
`id` int(11)
,`title` varchar(200)
,`resource_type` enum('note','video','assignment','syllabus','pyq')
,`subject` varchar(100)
,`subject_code` varchar(20)
,`branch` varchar(10)
,`year` int(11)
,`semester` int(11)
,`file_path` varchar(500)
,`video_url` varchar(500)
,`author_id` int(11)
,`author_name` varchar(100)
,`description` text
,`is_approved` tinyint(1)
,`views_count` int(11)
,`downloads_count` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`author_email` varchar(100)
,`author_full_name` varchar(101)
);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `branch` varchar(10) NOT NULL,
  `year` int(11) NOT NULL CHECK (`year` between 1 and 4),
  `semester` int(11) NOT NULL CHECK (`semester` between 1 and 8),
  `college` varchar(100) DEFAULT 'GNIT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `branch`, `year`, `semester`, `college`, `created_at`) VALUES
(3, 6, 'CSE2022011', 'CSE', 4, 8, 'GNIT', '2026-04-03 13:34:26'),
(4, 111, 'CSE2021001', 'CSE', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(5, 112, 'CSE2021002', 'CSE', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(6, 113, 'CSE2021003', 'CSE', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(7, 114, 'CSE2021004', 'CSE', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(8, 115, 'CSE2021005', 'CSE', 4, 8, 'GNIT', '2026-04-16 07:37:19'),
(9, 116, 'IT2021001', 'IT', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(10, 117, 'IT2021002', 'IT', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(11, 118, 'IT2021003', 'IT', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(12, 119, 'IT2021004', 'IT', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(13, 120, 'IT2021005', 'IT', 4, 8, 'GNIT', '2026-04-16 07:37:19'),
(14, 121, 'AIDS2021001', 'AIDS', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(15, 122, 'AIDS2021002', 'AIDS', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(16, 123, 'AIDS2021003', 'AIDS', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(17, 124, 'AIDS2021004', 'AIDS', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(18, 125, 'AIDS2021005', 'AIDS', 4, 8, 'GNIT', '2026-04-16 07:37:19'),
(19, 126, 'AIML2021001', 'AIML', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(20, 127, 'AIML2021002', 'AIML', 3, 5, 'GNIT', '2026-04-16 07:37:19'),
(21, 128, 'AIML2021003', 'AIML', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(22, 129, 'AIML2021004', 'AIML', 4, 7, 'GNIT', '2026-04-16 07:37:19'),
(23, 130, 'AIML2021005', 'AIML', 4, 8, 'GNIT', '2026-04-16 07:37:19'),
(24, 141, 'CSE2022030', 'CSE', 4, 8, 'GNIT', '2026-04-16 16:16:17'),
(25, 142, 'CSE2022020', 'CSE', 4, 8, 'GNIT', '2026-04-16 16:16:54'),
(26, 143, 'CSE2022010', 'CSE', 4, 8, 'GNIT', '2026-04-16 16:17:39'),
(27, 144, 'CSE2022015', 'CSE', 4, 8, 'GNIT', '2026-04-16 16:18:18'),
(28, 146, 'CSE2022014', 'CSE', 4, 8, 'GNIT', '2026-04-16 17:57:44'),
(29, 147, 'CSE2022016', 'CSE', 4, 8, 'GNIT', '2026-04-16 18:05:27');

-- --------------------------------------------------------

--
-- Stand-in structure for view `student_details`
-- (See below for the actual view)
--
CREATE TABLE `student_details` (
`student_record_id` int(11)
,`student_id` varchar(20)
,`branch` varchar(10)
,`year` int(11)
,`semester` int(11)
,`college` varchar(100)
,`user_id` int(11)
,`email` varchar(100)
,`first_name` varchar(50)
,`last_name` varchar(50)
,`full_name` varchar(101)
,`registered_on` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `student_notes`
--

CREATE TABLE `student_notes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT 'Untitled Note',
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `branch` varchar(10) NOT NULL,
  `semester` int(11) NOT NULL CHECK (`semester` between 1 and 8),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `branch`, `semester`, `created_at`) VALUES
(1, 'CS101', 'Engineering Mathematics-I', 'CSE', 1, '2026-04-03 13:16:42'),
(2, 'CS102', 'Engineering Physics', 'CSE', 1, '2026-04-03 13:16:42'),
(3, 'CS103', 'Programming in C', 'CSE', 1, '2026-04-03 13:16:42'),
(4, 'CS201', 'Engineering Mathematics-II', 'CSE', 2, '2026-04-03 13:16:42'),
(5, 'CS202', 'Digital Electronics', 'CSE', 2, '2026-04-03 13:16:42'),
(6, 'CS203', 'Data Structures', 'CSE', 2, '2026-04-03 13:16:42'),
(7, 'CS301', 'Applied Mathematics-3', 'CSE', 3, '2026-04-03 13:16:42'),
(8, 'CS302', 'Object Oriented Programming With Java', 'CSE', 3, '2026-04-03 13:16:42'),
(9, 'CS303', 'Operating System', 'CSE', 3, '2026-04-03 13:16:42'),
(10, 'CS304', 'Computer Architecture & Digital System', 'CSE', 3, '2026-04-03 13:16:42'),
(11, 'CS401', 'Discrete Mathematics & Graph Theory', 'CSE', 4, '2026-04-03 13:16:42'),
(12, 'CS402', 'Data Structure & Program Design', 'CSE', 4, '2026-04-03 13:16:42'),
(13, 'CS403', 'Theory of Computation', 'CSE', 4, '2026-04-03 13:16:42'),
(14, 'CS404', 'Computer Networks', 'CSE', 4, '2026-04-03 13:16:42'),
(15, 'CS501', 'Artificial Intelligence', 'CSE', 5, '2026-04-03 13:16:42'),
(16, 'CS502', 'Design & Analysis of Algorithm', 'CSE', 5, '2026-04-03 13:16:42'),
(17, 'CS503', 'Software Engineering & Project Management', 'CSE', 5, '2026-04-03 13:16:42'),
(18, 'CS504', 'Database Management Systems', 'CSE', 5, '2026-04-03 13:16:42'),
(19, 'CS601', 'Compiler Design', 'CSE', 6, '2026-04-03 13:16:42'),
(20, 'CS602', 'Internet of Things', 'CSE', 6, '2026-04-03 13:16:42'),
(21, 'CS603', 'Web Technologies', 'CSE', 6, '2026-04-03 13:16:42'),
(22, 'CS701', 'Data Science', 'CSE', 7, '2026-04-03 13:16:42'),
(23, 'CS702', 'Natural Language Processing', 'CSE', 7, '2026-04-03 13:16:42'),
(24, 'CS703', 'DevOps', 'CSE', 7, '2026-04-03 13:16:42'),
(25, 'CS704', 'Computer Vision', 'CSE', 7, '2026-04-03 13:16:42'),
(26, 'CS801', 'BlockChain & Its Applications', 'CSE', 8, '2026-04-03 13:16:42'),
(27, 'CS802', 'Social Network', 'CSE', 8, '2026-04-03 13:16:42'),
(28, 'CS803', 'Major Project', 'CSE', 8, '2026-04-03 13:16:42'),
(29, 'IT101', 'Engineering Mathematics-I', 'IT', 1, '2026-04-03 13:16:42'),
(30, 'IT102', 'Programming Fundamentals', 'IT', 1, '2026-04-03 13:16:42'),
(31, 'IT201', 'Data Structures & Algorithms', 'IT', 2, '2026-04-03 13:16:42'),
(32, 'IT202', 'Computer Networks', 'IT', 2, '2026-04-03 13:16:42'),
(33, 'IT301', 'Applied Mathematics-3', 'IT', 3, '2026-04-03 13:16:42'),
(34, 'IT302', 'Programming Logic & Design using C', 'IT', 3, '2026-04-03 13:16:42'),
(35, 'IT401', 'Discrete Mathematics & Graph Theory', 'IT', 4, '2026-04-03 13:16:42'),
(36, 'IT402', 'Data Structure & Program Design', 'IT', 4, '2026-04-03 13:16:42'),
(37, 'IT501', 'Software Engineering & Project Management', 'IT', 5, '2026-04-03 13:16:42'),
(38, 'IT502', 'Design and Analysis of Algorithm', 'IT', 5, '2026-04-03 13:16:42'),
(39, 'IT601', 'Database Management System', 'IT', 6, '2026-04-03 13:16:42'),
(40, 'IT602', 'Artificial Intelligence & Machine Learning', 'IT', 6, '2026-04-03 13:16:42'),
(41, 'IT701', 'Advanced Web Technologies', 'IT', 7, '2026-04-03 13:16:42'),
(42, 'IT702', 'Cyber Security', 'IT', 7, '2026-04-03 13:16:42'),
(43, 'IT801', 'Program Elective-6', 'IT', 8, '2026-04-03 13:16:42'),
(44, 'AIDS101', 'Engineering Mathematics-I', 'AIDS', 1, '2026-04-03 13:16:42'),
(45, 'AIDS102', 'Programming Fundamentals', 'AIDS', 1, '2026-04-03 13:16:42'),
(46, 'AIDS201', 'Engineering Mathematics-II', 'AIDS', 2, '2026-04-03 13:16:42'),
(47, 'AIDS202', 'Basic Electronics', 'AIDS', 2, '2026-04-03 13:16:42'),
(48, 'AIDS301', 'Discrete Mathematics and Graph Theory', 'AIDS', 3, '2026-04-03 13:16:42'),
(49, 'AIDS302', 'Operating System', 'AIDS', 3, '2026-04-03 13:16:42'),
(50, 'AIDS401', 'Introduction to AI', 'AIDS', 4, '2026-04-03 13:16:42'),
(51, 'AIDS402', 'Theory of Computation', 'AIDS', 4, '2026-04-03 13:16:42'),
(52, 'AIDS501', 'Data Mining', 'AIDS', 5, '2026-04-03 13:16:42'),
(53, 'AIDS502', 'Machine Learning Techniques', 'AIDS', 5, '2026-04-03 13:16:42'),
(54, 'AIDS601', 'Computer Communication Network', 'AIDS', 6, '2026-04-03 13:16:42'),
(55, 'AIDS602', 'Deep Learning', 'AIDS', 6, '2026-04-03 13:16:42'),
(56, 'AIDS701', 'Deep Learning Applications', 'AIDS', 7, '2026-04-03 13:16:42'),
(57, 'AIDS702', 'Digital Signal & Image Processing', 'AIDS', 7, '2026-04-03 13:16:42'),
(58, 'AIDS801', 'Elective-4', 'AIDS', 8, '2026-04-03 13:16:42'),
(59, 'AIML101', 'Engineering Mathematics', 'AIML', 1, '2026-04-03 13:16:42'),
(60, 'AIML102', 'Programming for AI', 'AIML', 1, '2026-04-03 13:16:42'),
(61, 'AIML201', 'Probability & Statistics', 'AIML', 2, '2026-04-03 13:16:42'),
(62, 'AIML202', 'Data Structures & Algorithms', 'AIML', 2, '2026-04-03 13:16:42'),
(63, 'AIML301', 'Machine Learning', 'AIML', 3, '2026-04-03 13:16:42'),
(64, 'AIML302', 'Database Management', 'AIML', 3, '2026-04-03 13:16:42'),
(65, 'AIML401', 'Deep Learning', 'AIML', 4, '2026-04-03 13:16:42'),
(66, 'AIML402', 'Natural Language Processing', 'AIML', 4, '2026-04-03 13:16:42'),
(67, 'AIML501', 'Reinforcement Learning', 'AIML', 5, '2026-04-03 13:16:42'),
(68, 'AIML502', 'Advanced Computer Vision', 'AIML', 5, '2026-04-03 13:16:42'),
(69, 'AIML601', 'AI Ethics', 'AIML', 6, '2026-04-03 13:16:42'),
(70, 'AIML602', 'Robotics & AI', 'AIML', 6, '2026-04-03 13:16:42'),
(71, 'AIML701', 'Advanced Deep Learning', 'AIML', 7, '2026-04-03 13:16:42'),
(72, 'AIML702', 'AI in Healthcare', 'AIML', 7, '2026-04-03 13:16:42'),
(73, 'AIML801', 'Major Project', 'AIML', 8, '2026-04-03 13:16:42'),
(74, 'CE101', 'Engineering Mathematics-I', 'CE', 1, '2026-04-03 13:16:42'),
(75, 'CE102', 'Engineering Mechanics', 'CE', 1, '2026-04-03 13:16:42'),
(76, 'CE201', 'Engineering Mathematics-II', 'CE', 2, '2026-04-03 13:16:42'),
(77, 'CE202', 'Building Materials', 'CE', 2, '2026-04-03 13:16:42'),
(78, 'CE301', 'Applied Maths-3', 'CE', 3, '2026-04-03 13:16:42'),
(79, 'CE302', 'Fluid Mechanics', 'CE', 3, '2026-04-03 13:16:42'),
(80, 'CE401', 'Concrete Technology', 'CE', 4, '2026-04-03 13:16:42'),
(81, 'CE402', 'Structural Analysis', 'CE', 4, '2026-04-03 13:16:42'),
(82, 'CE501', 'Hydraulic Engineering', 'CE', 5, '2026-04-03 13:16:42'),
(83, 'CE502', 'Reinforced Cement Concrete Designs', 'CE', 5, '2026-04-03 13:16:42'),
(84, 'CE601', 'Advanced Structural Design', 'CE', 6, '2026-04-03 13:16:42'),
(85, 'CE602', 'Construction Management', 'CE', 6, '2026-04-03 13:16:42'),
(86, 'CE701', 'Advanced Civil Engineering-1', 'CE', 7, '2026-04-03 13:16:42'),
(87, 'CE801', 'Major Project', 'CE', 8, '2026-04-03 13:16:42'),
(88, 'ME101', 'Engineering Mathematics-I', 'ME', 1, '2026-04-03 13:16:42'),
(89, 'ME102', 'Engineering Physics', 'ME', 1, '2026-04-03 13:16:42'),
(90, 'ME201', 'Engineering Mathematics-II', 'ME', 2, '2026-04-03 13:16:42'),
(91, 'ME202', 'Engineering Mechanics', 'ME', 2, '2026-04-03 13:16:42'),
(92, 'ME301', 'Applied Mathematics-3', 'ME', 3, '2026-04-03 13:16:42'),
(93, 'ME302', 'Manufacturing Process', 'ME', 3, '2026-04-03 13:16:42'),
(94, 'ME401', 'Machining Process', 'ME', 4, '2026-04-03 13:16:42'),
(95, 'ME402', 'Hydraulic Machines', 'ME', 4, '2026-04-03 13:16:42'),
(96, 'ME501', 'Heat Transfer', 'ME', 5, '2026-04-03 13:16:42'),
(97, 'ME502', 'Energy Conversion-1', 'ME', 5, '2026-04-03 13:16:42'),
(98, 'ME601', 'Mechanical Engineering Elective-1', 'ME', 6, '2026-04-03 13:16:42'),
(99, 'ME701', 'Advanced Mechanical Engineering-1', 'ME', 7, '2026-04-03 13:16:42'),
(100, 'ME801', 'Industrial Engineering', 'ME', 8, '2026-04-03 13:16:42'),
(101, 'EC101', 'Engineering Mathematics-I', 'ECE', 1, '2026-04-03 13:16:42'),
(102, 'EC102', 'Engineering Physics', 'ECE', 1, '2026-04-03 13:16:42'),
(103, 'EC201', 'Engineering Mathematics-II', 'ECE', 2, '2026-04-03 13:16:42'),
(104, 'EC202', 'Digital Electronics', 'ECE', 2, '2026-04-03 13:16:42'),
(105, 'EC301', 'Applied Mathematics-3', 'ECE', 3, '2026-04-03 13:16:42'),
(106, 'EC302', 'Electronic Devices & Circuits', 'ECE', 3, '2026-04-03 13:16:42'),
(107, 'EC401', 'Digital Signal Processing', 'ECE', 4, '2026-04-03 13:16:42'),
(108, 'EC402', 'Microprocessors & Microcontrollers', 'ECE', 4, '2026-04-03 13:16:42'),
(109, 'EC501', 'VLSI Design', 'ECE', 5, '2026-04-03 13:16:42'),
(110, 'EC502', 'Digital Communication', 'ECE', 5, '2026-04-03 13:16:42'),
(111, 'EC601', 'Wireless Communication', 'ECE', 6, '2026-04-03 13:16:42'),
(112, 'EC602', 'Optical Communication', 'ECE', 6, '2026-04-03 13:16:42'),
(113, 'EC701', 'Advanced Communication Systems', 'ECE', 7, '2026-04-03 13:16:42'),
(114, 'EC801', 'Project Work', 'ECE', 8, '2026-04-03 13:16:42'),
(115, 'CSEDS101', 'Engineering Mathematics-I', 'CSEDS', 1, '2026-04-03 13:16:42'),
(116, 'CSEDS102', 'Programming Fundamentals', 'CSEDS', 1, '2026-04-03 13:16:42'),
(117, 'CSEDS201', 'Data Structures', 'CSEDS', 2, '2026-04-03 13:16:42'),
(118, 'CSEDS202', 'Database Management Systems', 'CSEDS', 2, '2026-04-03 13:16:42'),
(119, 'CSEDS301', 'Mathematics Foundations for Data Science', 'CSEDS', 3, '2026-04-03 13:16:42'),
(120, 'CSEDS302', 'Object Oriented Programming', 'CSEDS', 3, '2026-04-03 13:16:42'),
(121, 'CSEDS401', 'Machine Learning', 'CSEDS', 4, '2026-04-03 13:16:42'),
(122, 'CSEDS402', 'Big Data Analytics', 'CSEDS', 4, '2026-04-03 13:16:42'),
(123, 'CSEDS501', 'Data Visualization', 'CSEDS', 5, '2026-04-03 13:16:42'),
(124, 'CSEDS502', 'Cloud Computing', 'CSEDS', 5, '2026-04-03 13:16:42'),
(125, 'CSEDS601', 'Data Mining', 'CSEDS', 6, '2026-04-03 13:16:42'),
(126, 'CSEDS602', 'Natural Language Processing', 'CSEDS', 6, '2026-04-03 13:16:42'),
(127, 'CSEDS701', 'Deep Learning', 'CSEDS', 7, '2026-04-03 13:16:42'),
(128, 'CSEDS801', 'Major Project', 'CSEDS', 8, '2026-04-03 13:16:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_type` enum('student','faculty','admin') NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `status`, `rejection_reason`, `email`, `password`, `first_name`, `last_name`, `created_at`, `updated_at`) VALUES
(5, 'faculty', 'approved', NULL, 'pooja@gmail.com', '$2y$10$nPND9e6fXhz1r7DT1oCkJuJppYNHjuSxxAfVCjvFPDxBrec307DwC', 'pooja', 'jaiswal', '2026-04-03 13:23:51', '2026-04-15 21:55:09'),
(6, 'student', 'approved', NULL, 'vitthal@gmail.com', '$2y$10$Cqel7ljPJoCcHxyQ4L02eeqwqXbOBVqT7EpPKBjP59u5wWvo3WXxe', 'vitthal', 'chavan', '2026-04-03 13:34:26', '2026-04-16 17:45:13'),
(107, 'admin', 'approved', NULL, 'admin@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '2026-04-15 21:41:47', '2026-04-15 21:41:47'),
(108, 'faculty', 'approved', NULL, 'ruchika@gmail.com', '$2y$10$5hP336vh54p59MxUfKZ6ieQKCJjN6guTBZxyfNAAKM/OV1SwBh94m', 'Ruchika', 'Parate', '2026-04-15 21:56:49', '2026-04-15 21:57:43'),
(110, 'faculty', 'rejected', '', 'roshni@gmail.com', '$2y$10$ijbV.6lkrQrg193JvWxWlOq.cksjLyXEa/Am6Wui9l1qVLDGvztWu', 'Roshni', 'Kolhe', '2026-04-15 21:58:52', '2026-04-15 21:59:17'),
(111, 'student', 'approved', NULL, 'aryan.sharma@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aryan', 'Sharma', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(112, 'student', 'approved', NULL, 'priya.verma@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya', 'Verma', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(113, 'student', 'approved', NULL, 'rohit.patel@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rohit', 'Patel', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(114, 'student', 'approved', NULL, 'neha.gupta@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Neha', 'Gupta', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(115, 'student', 'approved', NULL, 'rahul.kumar@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rahul', 'Kumar', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(116, 'student', 'approved', NULL, 'sneha.joshi@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sneha', 'Joshi', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(117, 'student', 'approved', NULL, 'amit.singh@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Amit', 'Singh', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(118, 'student', 'approved', NULL, 'kavya.reddy@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kavya', 'Reddy', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(119, 'student', 'approved', NULL, 'divya.mishra@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Divya', 'Mishra', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(120, 'student', 'approved', NULL, 'ankit.jain@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ankit', 'Jain', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(121, 'student', 'approved', NULL, 'shreya.desai@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shreya', 'Desai', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(122, 'student', 'approved', NULL, 'omkar.patil@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Omkar', 'Patil', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(123, 'student', 'approved', NULL, 'tanvi.kulkarni@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tanvi', 'Kulkarni', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(124, 'student', 'approved', NULL, 'siddharth.nair@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siddharth', 'Nair', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(125, 'student', 'approved', NULL, 'ishita.bose@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ishita', 'Bose', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(126, 'student', 'approved', NULL, 'vivek.mehta@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vivek', 'Mehta', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(127, 'student', 'approved', NULL, 'aditi.saxena@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aditi', 'Saxena', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(128, 'student', 'approved', NULL, 'rishabh.choudhary@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rishabh', 'Choudhary', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(129, 'student', 'approved', NULL, 'ananya.kapoor@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ananya', 'Kapoor', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(130, 'student', 'approved', NULL, 'yash.shah@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yash', 'Shah', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(131, 'faculty', 'approved', NULL, 'dr.rajesh.khanna@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rajesh', 'Khanna', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(132, 'faculty', 'approved', NULL, 'dr.meena.sharma@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Meena', 'Sharma', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(133, 'faculty', 'approved', NULL, 'prof.anil.kapoor@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anil', 'Kapoor', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(134, 'faculty', 'approved', NULL, 'dr.snehal.patil@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Snehal', 'Patil', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(135, 'faculty', 'approved', NULL, 'prof.vikram.joshi@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vikram', 'Joshi', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(136, 'faculty', 'approved', NULL, 'dr.ritu.verma@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ritu', 'Verma', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(137, 'faculty', 'approved', NULL, 'prof.sanjay.gupta@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sanjay', 'Gupta', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(138, 'faculty', 'approved', NULL, 'dr.kavita.desai@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kavita', 'Desai', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(139, 'faculty', 'approved', NULL, 'prof.manoj.tiwari@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manoj', 'Tiwari', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(140, 'faculty', 'approved', NULL, 'dr.neha.singh@gnit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Neha', 'Singh', '2026-04-16 07:37:19', '2026-04-16 07:37:19'),
(141, 'student', 'approved', NULL, 'avantar@gmail.com', '$2y$10$698evk75/rZKYEoPMsyPFOpYuuq.FF5vlsC0DUJUPUCdXNwWrOl9O', 'Avantar ', 'Nikhare', '2026-04-16 16:16:17', '2026-04-16 17:45:26'),
(142, 'student', 'approved', NULL, 'harshal@gmail.com', '$2y$10$hTFkBH5m82EzSbKmoH233ufSGZIlLSAuYB5vRbG/0kLaaO3J0SLy2', 'Harshal', 'Dhote', '2026-04-16 16:16:54', '2026-04-16 17:45:23'),
(143, 'student', 'approved', NULL, 'pradyumna@gmai.com', '$2y$10$m7l8AwfrNiBIXxv9T67nlemT3UzNfUgpQYNY8RdcAHcjWxl9wDsEW', 'Pradyumna ', 'Pudke', '2026-04-16 16:17:39', '2026-04-16 17:45:20'),
(144, 'student', 'approved', NULL, 'vaibhav@gmail.com', '$2y$10$NzBQhik.7dBmQ58sVX2bUeT/Sw9x/HTPYFeUy0bmXuOPF/RMWUqd.', 'Vibhav', 'Likhar', '2026-04-16 16:18:18', '2026-04-16 17:45:17'),
(146, 'student', 'rejected', 'you are not in  this college student.', 'pranav@gmail.com', '$2y$10$JS6/fdB9tpxHWiMJh3f7PuXFf0vwatxTsI39BhBX5LJ/Nvxj2N6MK', 'pranav', 'tute', '2026-04-16 17:57:44', '2026-04-16 18:06:50'),
(147, 'student', 'rejected', '', 'suraj@gmail.com', '$2y$10$OdmRYgHpDQ.9gZVkd0RRzO7zO1FO51YkeFaYFPqD7kFPOQQ8540dG', 'suraj', 'mozarkar', '2026-04-16 18:05:27', '2026-04-16 18:06:23'),
(149, 'faculty', 'rejected', '', 'atul@gmail.com', '$2y$10$meO0.VyXYAQ7BqBspuJQEOlNB1ENyriGhJ1Awhy519ZFODfkf7nWu', 'Atul', 'Kapgate', '2026-04-16 18:08:15', '2026-04-16 18:15:13'),
(150, 'faculty', 'approved', NULL, 'akshay@gmail.com', '$2y$10$xFGe4vzH.OM0mIwWj3KoO.h/0DpUf817sNDGr5JTqyK3YCiF6kXma', 'akshay', 'bankar', '2026-04-16 18:23:40', '2026-04-16 18:24:17');

-- --------------------------------------------------------

--
-- Structure for view `faculty_details`
--
DROP TABLE IF EXISTS `faculty_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `faculty_details`  AS SELECT `f`.`id` AS `faculty_record_id`, `f`.`faculty_id` AS `faculty_id`, `f`.`department` AS `department`, `f`.`subject` AS `primary_subject`, `f`.`college` AS `college`, `u`.`id` AS `user_id`, `u`.`email` AS `email`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`, `u`.`created_at` AS `registered_on` FROM (`faculty` `f` join `users` `u` on(`f`.`user_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `resource_details`
--
DROP TABLE IF EXISTS `resource_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `resource_details`  AS SELECT `r`.`id` AS `id`, `r`.`title` AS `title`, `r`.`resource_type` AS `resource_type`, `r`.`subject` AS `subject`, `r`.`subject_code` AS `subject_code`, `r`.`branch` AS `branch`, `r`.`year` AS `year`, `r`.`semester` AS `semester`, `r`.`file_path` AS `file_path`, `r`.`video_url` AS `video_url`, `r`.`author_id` AS `author_id`, `r`.`author_name` AS `author_name`, `r`.`description` AS `description`, `r`.`is_approved` AS `is_approved`, `r`.`views_count` AS `views_count`, `r`.`downloads_count` AS `downloads_count`, `r`.`created_at` AS `created_at`, `r`.`updated_at` AS `updated_at`, `u`.`email` AS `author_email`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `author_full_name` FROM (`resources` `r` join `users` `u` on(`r`.`author_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `student_details`
--
DROP TABLE IF EXISTS `student_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `student_details`  AS SELECT `s`.`id` AS `student_record_id`, `s`.`student_id` AS `student_id`, `s`.`branch` AS `branch`, `s`.`year` AS `year`, `s`.`semester` AS `semester`, `s`.`college` AS `college`, `u`.`id` AS `user_id`, `u`.`email` AS `email`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`, `u`.`created_at` AS `registered_on` FROM (`students` `s` join `users` `u` on(`s`.`user_id` = `u`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`session_id`,`student_id`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `attendance_summary`
--
ALTER TABLE `attendance_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_summary` (`student_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_percentage` (`percentage`);

--
-- Indexes for table `class_sessions`
--
ALTER TABLE `class_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_faculty_id` (`faculty_id`),
  ADD KEY `idx_lecture_date` (`lecture_date`),
  ADD KEY `idx_branch_semester` (`branch`,`semester`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_id` (`faculty_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_faculty_id` (`faculty_id`),
  ADD KEY `idx_department` (`department`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_branch_year_semester` (`branch`,`year`,`semester`),
  ADD KEY `idx_resource_type` (`resource_type`),
  ADD KEY `idx_subject` (`subject`),
  ADD KEY `idx_is_approved` (`is_approved`);
ALTER TABLE `resources` ADD FULLTEXT KEY `idx_search` (`title`,`subject`,`description`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_branch` (`branch`),
  ADD KEY `idx_semester` (`semester`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `student_notes`
--
ALTER TABLE `student_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subject` (`subject_code`,`branch`,`semester`),
  ADD KEY `idx_branch_semester` (`branch`,`semester`),
  ADD KEY `idx_subject_code` (`subject_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `attendance_summary`
--
ALTER TABLE `attendance_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `class_sessions`
--
ALTER TABLE `class_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `student_notes`
--
ALTER TABLE `student_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `class_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_ibfk_3` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `attendance_summary`
--
ALTER TABLE `attendance_summary`
  ADD CONSTRAINT `attendance_summary_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_summary_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_sessions`
--
ALTER TABLE `class_sessions`
  ADD CONSTRAINT `class_sessions_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_sessions_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty`
--
ALTER TABLE `faculty`
  ADD CONSTRAINT `faculty_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_notes`
--
ALTER TABLE `student_notes`
  ADD CONSTRAINT `student_notes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
