-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 07:06 AM
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
-- Database: `dsc_db1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'System Administrator', 'admin@sanjivani.edu.in', 'admin', '2025-06-14 14:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `issuer` varchar(100) NOT NULL,
  `issue_date` date NOT NULL,
  `certificate_image` varchar(255) NOT NULL,
  `verification_link` varchar(255) DEFAULT NULL,
  `points` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_role` enum('hod','dean','admin') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deans`
--

CREATE TABLE `deans` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deans`
--

INSERT INTO `deans` (`id`, `username`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'dean', 'Dean of Engineering', 'dean@sanjivani.edu.in', 'dean', '2025-06-14 14:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `default_logos`
--

CREATE TABLE `default_logos` (
  `id` int(11) NOT NULL,
  `letter` char(1) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `default_logos`
--

INSERT INTO `default_logos` (`id`, `letter`, `image_path`) VALUES
(1, 'A', 'images/default/A.png'),
(2, 'B', 'images/default/B.png'),
(3, 'C', 'images/default/C.png'),
(4, 'D', 'images/default/D.png'),
(5, 'E', 'images/default/E.png'),
(6, 'F', 'images/default/F.png'),
(7, 'G', 'images/default/G.png'),
(8, 'H', 'images/default/H.png'),
(9, 'I', 'images/default/I.png'),
(10, 'J', 'images/default/J.png'),
(11, 'K', 'images/default/K.png'),
(12, 'L', 'images/default/L.png'),
(13, 'M', 'images/default/M.png'),
(14, 'N', 'images/default/N.png'),
(15, 'O', 'images/default/O.png'),
(16, 'P', 'images/default/P.png'),
(17, 'Q', 'images/default/Q.png'),
(18, 'R', 'images/default/R.png'),
(19, 'S', 'images/default/S.png'),
(20, 'T', 'images/default/T.png'),
(21, 'U', 'images/default/U.png'),
(22, 'V', 'images/default/V.png'),
(23, 'W', 'images/default/W.png'),
(24, 'X', 'images/default/X.png'),
(25, 'Y', 'images/default/Y.png'),
(26, 'Z', 'images/default/Z.png');

-- --------------------------------------------------------

--
-- Stand-in structure for view `department_leaderboard`
-- (See below for the actual view)
--
CREATE TABLE `department_leaderboard` (
`department` enum('CSE','CY','AIML','ALDS')
,`year` enum('FY','SY','TY','FINAL')
,`id` int(11)
,`prn` varchar(20)
,`name` varchar(101)
,`total_points` decimal(32,0)
,`rank_in_class` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `speaker` varchar(100) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `speaker`, `max_participants`, `image`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Web Development Workshop', 'Learn the basics of web development with HTML, CSS, and JavaScript. This hands-on workshop will help you build your first website from scratch.', '2025-10-15', '10:00:00', 'Computer Lab 1', 'Prof. Sharma', 30, NULL, 1, '2025-06-14 14:38:04', '2025-06-15 15:04:22'),
(2, 'Introduction to Machine Learning', 'This workshop covers the fundamentals of machine learning, including supervised and unsupervised learning, with practical examples using Python.', '2025-10-20', '14:00:00', 'Seminar Hall', 'Dr. Patel', 50, NULL, 1, '2025-06-14 14:38:04', '2025-06-15 15:04:12'),
(3, 'Competitive Programming Contest', 'Test your coding skills in this competitive programming contest. Solve algorithmic problems and compete with your peers.', '2025-11-05', '09:00:00', 'Computer Lab 2', '', 100, NULL, 1, '2025-06-14 14:38:04', '2025-06-15 15:04:00'),
(4, 'Cloud Computing Seminar', 'Learn about cloud computing technologies, including AWS, Azure, and Google Cloud Platform.', '2025-11-15', '11:00:00', 'Auditorium', 'Mr. Rajesh Kumar (AWS)', 200, NULL, 1, '2025-06-14 14:38:04', '2025-06-15 15:03:50'),
(5, 'Hackathon: Solve for Community', 'A 24-hour hackathon to build solutions for local community problems using technology.', '2025-12-01', '09:00:00', 'Main Building', '', 150, NULL, 1, '2025-06-14 14:38:04', '2025-06-15 15:03:34');

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance` tinyint(1) DEFAULT 0,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hods`
--

CREATE TABLE `hods` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` enum('CSE','CY','AIML','ALDS') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hods`
--

INSERT INTO `hods` (`id`, `username`, `name`, `email`, `password`, `department`, `created_at`) VALUES
(1, 'hodcse', 'HOD Computer Science & Engineering', 'hodcse@sanjivani.edu.in', 'hodcse', 'CSE', '2025-06-14 14:38:04'),
(2, 'hodcy', 'HOD Cyber Security', 'hodcy@sanjivani.edu.in', '$2y$10$7J/Kt4qwT6TQBtolTk0xE.gPX1DVNZ3fYBue7r5wKmtG.rftf5Ivi', 'CY', '2025-06-14 14:38:04'),
(3, 'hodaiml', 'HOD AI & ML', 'hodaiml@sanjivani.edu.in', '$2y$10$7J/Kt4qwT6TQBtolTk0xE.gPX1DVNZ3fYBue7r5wKmtG.rftf5Ivi', 'AIML', '2025-06-14 14:38:04'),
(4, 'hodalds', 'HOD AI & Data Science', 'hodalds@sanjivani.edu.in', '$2y$10$7J/Kt4qwT6TQBtolTk0xE.gPX1DVNZ3fYBue7r5wKmtG.rftf5Ivi', 'ALDS', '2025-06-14 14:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `department` enum('all','CSE','CY','AIML','ALDS') NOT NULL,
  `year` enum('all','FY','SY','TY','FINAL') NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_role` enum('hod','dean','admin') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `department`, `year`, `created_by`, `created_role`, `is_read`, `created_at`) VALUES
(1, 'Important: Registration Deadline', 'Registration for the Web Development Workshop closes tomorrow. Don\'t miss out!', 'all', 'all', 1, 'admin', 0, '2025-06-14 14:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `type` enum('internship','certification','project','other') NOT NULL,
  `link` varchar(255) NOT NULL,
  `department` enum('all','CSE','CY','AIML','ALDS') NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_role` enum('hod','dean','admin') NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `prn` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `department` enum('CSE','CY','AIML','ALDS') NOT NULL,
  `year` enum('FY','SY','TY','FINAL') NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `leetcode_url` varchar(255) DEFAULT NULL,
  `other_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `prn`, `first_name`, `middle_name`, `last_name`, `email`, `contact_no`, `department`, `year`, `password`, `profile_image`, `linkedin_url`, `github_url`, `leetcode_url`, `other_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PRN001', 'John', 'A', 'Doe', 'john.doe@sanjivani.edu.in', '9876543210', 'CSE', 'TY', '$2y$10$HT6V0XBAfQdF1C1XJxHx5eMz9l5ccPACWNnUkjcTH2al2JTH2Ioj.', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-14 14:38:04', '2025-06-14 14:38:04'),
(2, 'PRN002', 'Jane', 'B', 'Smith', 'jane.smith@sanjivani.edu.in', '9876543211', 'CSE', 'TY', '$2y$10$XhIviLAzUfhzM5dUvI2eG.Q8zCvbm9IWOiAM2sG8LlAZ.1dR0PsNS', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-14 14:38:04', '2025-06-14 14:38:04'),
(3, 'PRN003', 'Amit', 'C', 'Patel', 'amit.patel@sanjivani.edu.in', '9876543212', 'AIML', 'SY', '$2y$10$wHs0mCHXdTOh6tF2EvDcdeqA4ZyRwq3QX10xnnfeBE8KPZvIhdLyi', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-14 14:38:04', '2025-06-14 14:38:04'),
(4, 'PRN004', 'Priya', 'D', 'Sharma', 'priya.sharma@sanjivani.edu.in', '9876543213', 'AIML', 'SY', '$2y$10$EBiAP4GE79yCUe8.FKgOceYW1Z3jHM8fLfQyW6YblzJZ2eE2FIhvW', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-14 14:38:04', '2025-06-14 14:38:04'),
(5, 'PRN005', 'Raj', 'E', 'Kumar', 'raj.kumar@sanjivani.edu.in', '9876543214', 'CY', 'FY', 'Raj@123', NULL, '', '', '', '', 1, '2025-06-14 14:38:04', '2025-06-14 15:57:25'),
(6, 'PRN006', 'Neha', 'F', 'Verma', 'neha.verma@sanjivani.edu.in', '9876543215', 'CY', 'FY', 'Neha@123', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-14 14:38:04', '2025-06-14 16:18:38'),
(7, 'PRN007', 'Vikram', 'G', 'Singh', 'vikram.singh@sanjivani.edu.in', '9876543216', 'ALDS', 'FINAL', '$2y$10$4B1mLMH7aCg7AiUbHHt98uBXBPbDAFsxwLtBTaNTLQ35Nj99UNm36', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-14 14:38:04', '2025-06-14 14:38:04'),
(8, 'PRN008', 'Ananya', 'H', 'Joshi', 'ananya.joshi@sanjivani.edu.in', '9876543217', 'ALDS', 'FINAL', '$2y$10$nU1mzXfpd4VWm2eDWkB0xeYG5HX1t81jvpnQpW8kVUjFlE7p0zFky', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-14 14:38:04', '2025-06-14 14:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `student_notifications`
--

CREATE TABLE `student_notifications` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_points`
--

CREATE TABLE `student_points` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `description` text NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `student_points_summary`
-- (See below for the actual view)
--
CREATE TABLE `student_points_summary` (
`id` int(11)
,`prn` varchar(20)
,`name` varchar(101)
,`department` enum('CSE','CY','AIML','ALDS')
,`year` enum('FY','SY','TY','FINAL')
,`total_points` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `role` enum('dean','hod','staff_incharge','president','vice_president','technical_head','non_technical_head','management_head','photography_head','social_media_manager','domain_lead','accountant','student','member','core','lead') NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `bio`, `image`, `role`, `department`, `skills`, `linkedin`, `github`, `twitter`, `email`, `phone`, `created_at`) VALUES
(1, 'Ravi Kumar', 'DSC Lead', 'A passionate tech enthusiast with a love for building community projects.', NULL, 'core', NULL, 'Leadership, Web Development, Public Speaking', 'https://linkedin.com', 'https://github.com', 'https://twitter.com', NULL, NULL, '2025-06-14 14:38:04'),
(2, 'Anjali Desai', 'Technical Lead', 'Full-stack developer with experience in React and Node.js.', NULL, 'core', NULL, 'React, Node.js, MongoDB, Express', 'https://linkedin.com', 'https://github.com', NULL, NULL, NULL, '2025-06-14 14:38:04'),
(3, 'Suresh Patel', 'Design Lead', 'UI/UX designer passionate about creating beautiful user experiences.', NULL, 'lead', NULL, 'Figma, Adobe XD, UI/UX, Illustration', 'https://linkedin.com', NULL, 'https://twitter.com', NULL, NULL, '2025-06-14 14:38:04'),
(4, 'Meera Shah', 'Android Lead', 'Android developer with a focus on Kotlin and Jetpack Compose.', NULL, 'lead', NULL, 'Android, Kotlin, Java, Firebase', 'https://linkedin.com', 'https://github.com', NULL, NULL, NULL, '2025-06-14 14:38:04'),
(5, 'Rahul Gupta', 'ML Lead', 'Machine Learning enthusiast specializing in computer vision.', NULL, 'lead', NULL, 'Python, TensorFlow, PyTorch, OpenCV', 'https://linkedin.com', 'https://github.com', NULL, NULL, NULL, '2025-06-14 14:38:04'),
(6, 'Neha Sharma', 'Web Lead', 'Web developer with expertise in modern JavaScript frameworks.', NULL, 'lead', NULL, 'JavaScript, React, Vue, SCSS', 'https://linkedin.com', 'https://github.com', 'https://twitter.com', NULL, NULL, '2025-06-14 14:38:04'),
(10, 'Arun Joshi', 'Member', 'Backend developer specializing in microservices.', NULL, 'member', NULL, 'Java, Spring Boot, Microservices', 'https://linkedin.com', 'https://github.com', NULL, NULL, NULL, '2025-06-14 14:38:04'),
(12, 'kavitha', 'Dean of Engineering', 'Experienced academic leader with 20+ years in computer science education.', '', 'dean', 'CSE', 'Academic Leadership, Research, Curriculum Development', 'https://linkedin.com/in/sarah-johnson', '', '', 'dean.sarah@university.edu', '+91-9876543211', '2025-06-15 03:48:28'),
(15, 'Vikram Singh', 'Technical Head', 'Senior developer passionate about emerging technologies and mentorship.', NULL, 'technical_head', 'CSE', 'Full Stack Development, DevOps, Mentoring', 'https://linkedin.com/in/vikram-singh', 'https://github.com/vikramsingh', 'https://twitter.com/vikramtech', 'vikram.singh@example.com', '+91-9876543214', '2025-06-15 03:48:28'),
(18, 'Sneha Reddy', 'Photography Head', 'Creative visual storyteller capturing moments and memories for the community.', NULL, 'photography_head', 'CSE', 'Photography, Video Editing, Creative Design', 'https://linkedin.com/in/sneha-reddy', NULL, 'https://twitter.com/snehaphoto', 'sneha.reddy@example.com', '+91-9876543217', '2025-06-15 03:48:28'),
(20, 'Kavya Nair', 'Domain Lead - Web Development', 'Full-stack developer leading web development initiatives and workshops.', NULL, 'domain_lead', 'CSE', 'React, Node.js, MongoDB, JavaScript', 'https://linkedin.com/in/kavya-nair', 'https://github.com/kavyanair', NULL, 'kavya.nair@student.edu', '+91-9876543219', '2025-06-15 03:48:28'),
(22, 'Aditi Sharma', 'Student Member', 'Enthusiastic computer science student passionate about web development.', NULL, '', 'CSE', 'HTML, CSS, JavaScript, Python', 'https://linkedin.com/in/aditi-sharma', 'https://github.com/aditisharma', NULL, 'aditi.sharma@student.edu', '+91-9876543221', '2025-06-15 03:48:28'),
(23, 'Karan Malhotra', 'Student Member', 'AI/ML enthusiast working on innovative projects and research.', NULL, '', 'AIML', 'Python, TensorFlow, Machine Learning, Data Science', 'https://linkedin.com/in/karan-malhotra', 'https://github.com/karanmalhotra', NULL, 'karan.malhotra@student.edu', '+91-9876543222', '2025-06-15 03:48:28'),
(28, 'Rohit Ravindra Lakare', 'President', '', NULL, 'president', 'CSE', '', '', '', '', '', '', '2025-06-15 04:57:01');

-- --------------------------------------------------------

--
-- Stand-in structure for view `university_leaderboard`
-- (See below for the actual view)
--
CREATE TABLE `university_leaderboard` (
`id` int(11)
,`prn` varchar(20)
,`name` varchar(101)
,`department` enum('CSE','CY','AIML','ALDS')
,`year` enum('FY','SY','TY','FINAL')
,`total_points` decimal(32,0)
,`university_rank` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `department_leaderboard`
--
DROP TABLE IF EXISTS `department_leaderboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `department_leaderboard`  AS SELECT `student_points_summary`.`department` AS `department`, `student_points_summary`.`year` AS `year`, `student_points_summary`.`id` AS `id`, `student_points_summary`.`prn` AS `prn`, `student_points_summary`.`name` AS `name`, `student_points_summary`.`total_points` AS `total_points`, rank() over ( partition by `student_points_summary`.`department`,`student_points_summary`.`year` order by `student_points_summary`.`total_points` desc) AS `rank_in_class` FROM `student_points_summary` ;

-- --------------------------------------------------------

--
-- Structure for view `student_points_summary`
--
DROP TABLE IF EXISTS `student_points_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `student_points_summary`  AS SELECT `s`.`id` AS `id`, `s`.`prn` AS `prn`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `name`, `s`.`department` AS `department`, `s`.`year` AS `year`, sum(`sp`.`points`) AS `total_points` FROM (`students` `s` left join `student_points` `sp` on(`s`.`id` = `sp`.`student_id`)) GROUP BY `s`.`id`, `s`.`prn`, concat(`s`.`first_name`,' ',`s`.`last_name`), `s`.`department`, `s`.`year` ORDER BY sum(`sp`.`points`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `university_leaderboard`
--
DROP TABLE IF EXISTS `university_leaderboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `university_leaderboard`  AS SELECT `student_points_summary`.`id` AS `id`, `student_points_summary`.`prn` AS `prn`, `student_points_summary`.`name` AS `name`, `student_points_summary`.`department` AS `department`, `student_points_summary`.`year` AS `year`, `student_points_summary`.`total_points` AS `total_points`, rank() over ( order by `student_points_summary`.`total_points` desc) AS `university_rank` FROM `student_points_summary` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deans`
--
ALTER TABLE `deans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `default_logos`
--
ALTER TABLE `default_logos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `letter` (`letter`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `hods`
--
ALTER TABLE `hods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prn` (`prn`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_id` (`notification_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_points`
--
ALTER TABLE `student_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deans`
--
ALTER TABLE `deans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `default_logos`
--
ALTER TABLE `default_logos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hods`
--
ALTER TABLE `hods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_notifications`
--
ALTER TABLE `student_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_points`
--
ALTER TABLE `student_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `event_participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_participants_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD CONSTRAINT `student_notifications_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_notifications_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_points`
--
ALTER TABLE `student_points`
  ADD CONSTRAINT `student_points_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
