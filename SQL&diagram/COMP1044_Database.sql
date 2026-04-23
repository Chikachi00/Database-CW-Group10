-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generated: 2026-04-22 03:13:36
-- Server version: 8.0.44
-- PHP version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `COMP1044_database`
--

-- --------------------------------------------------------

--
-- Table structure for `Assessments`
--

CREATE TABLE `Assessments` (
  `assessment_id` int NOT NULL,
  `internship_id` int NOT NULL,
  `task_score` decimal(5,2) NOT NULL,
  `health_safety_score` decimal(5,2) NOT NULL,
  `connectivity_score` decimal(5,2) NOT NULL,
  `report_score` decimal(5,2) NOT NULL,
  `clarity_score` decimal(5,2) NOT NULL,
  `lifelong_score` decimal(5,2) NOT NULL,
  `project_mgmt_score` decimal(5,2) NOT NULL,
  `time_mgmt_score` decimal(5,2) NOT NULL,
  `total_score` decimal(5,2) NOT NULL,
  `qualitative_comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Assessments`
--
-- NOTE: All component scores are raw marks (0-100).
-- total_score is calculated by the system as:
--   task*0.10 + health*0.10 + conn*0.10 + report*0.15 + clarity*0.10 + lifelong*0.15 + proj*0.15 + time*0.15
--
-- Conversion reference (original weighted → raw):
--   10% components: raw = weighted / 0.10  (e.g. 9.00 → 90.00)
--   15% components: raw = weighted / 0.15  (e.g. 14.00 → 93.33)

INSERT INTO `Assessments` (`assessment_id`, `internship_id`, `task_score`, `health_safety_score`, `connectivity_score`, `report_score`, `clarity_score`, `lifelong_score`, `project_mgmt_score`, `time_mgmt_score`, `total_score`, `qualitative_comments`) VALUES
-- Alice: 90*0.10 + 95*0.10 + 90*0.10 + 93.33*0.15 + 90*0.10 + 93.33*0.15 + 93.33*0.15 + 93.33*0.15 = 92.50
(1, 1, 90.00, 95.00, 90.00, 93.33, 90.00, 93.33, 93.33, 93.33, 92.50, 'Alice demonstrated outstanding technical skills and adapted perfectly to the company culture. Highly recommended.'),
-- Bob: 70*0.10 + 80*0.10 + 75*0.10 + 73.33*0.15 + 70*0.10 + 70*0.15 + 80*0.15 + 73.33*0.15 = 74.00
(2, 2, 70.00, 80.00, 75.00, 73.33, 70.00, 70.00, 80.00, 73.33, 74.00, 'Bob completed the tasks adequately, but needs to improve his communication skills and time management.'),
-- Diana: 85*0.10 + 90*0.10 + 80*0.10 + 86.67*0.15 + 85*0.10 + 80*0.15 + 86.67*0.15 + 83.33*0.15 = 84.50
(3, 4, 85.00, 90.00, 80.00, 86.67, 85.00, 80.00, 86.67, 83.33, 84.50, 'Diana is a fast learner and contributed well to the cloud architecture project. Good overall performance.'),
-- Evan: 55*0.10 + 60*0.10 + 58*0.10 + 60*0.15 + 55*0.10 + 60*0.15 + 58*0.15 + 60*0.15 = 58.50
(4, 5, 55.00, 60.00, 58.00, 60.00, 55.00, 60.00, 58.00, 60.00, 58.50, 'Evan showed basic understanding of data analytics concepts but struggled with independent problem-solving. Improvement needed in time management and project delivery.'),
-- Frank: 40*0.10 + 50*0.10 + 40*0.10 + 42.50*0.15 + 40*0.10 + 42.50*0.15 + 42.50*0.15 + 42.50*0.15 = 42.50
(5, 6, 40.00, 50.00, 40.00, 42.50, 40.00, 42.50, 42.50, 42.50, 42.50, 'Frank did not meet the expected standard. Frequent absence, poor engagement with assigned tasks, and weak technical foundation. Requires significant improvement.');

-- --------------------------------------------------------

--
-- Table structure for `Internships`
--

CREATE TABLE `Internships` (
  `internship_id` int NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `assessor_id` int NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `other_details` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Internships`
--

INSERT INTO `Internships` (`internship_id`, `student_id`, `assessor_id`, `company_name`, `other_details`) VALUES
(1, 'S2024001', 2, 'Google Malaysia', 'Backend Intern, 12 weeks'),
(2, 'S2024002', 2, 'Shopee', 'Frontend Intern, 12 weeks'),
(3, 'S2024003', 2, 'Intel', 'System Analyst Intern, 10 weeks'),
(4, 'S2024004', 3, 'Microsoft', 'Cloud Architect Intern, 12 weeks'),
(5, 'S2024005', 3, 'Grab', 'Data Analyst Intern, 10 weeks'),
(6, 'S2024006', 2, 'Small Local Startup', 'Junior Developer Intern, 8 weeks');

-- --------------------------------------------------------

--
-- Table structure for `Students`
--

CREATE TABLE `Students` (
  `student_id` varchar(20) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `programme` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Students`
--

INSERT INTO `Students` (`student_id`, `student_name`, `programme`) VALUES
('S2024001', 'Alice Wong', 'Computer Science'),
('S2024002', 'Bob Chen', 'Software Engineering'),
('S2024003', 'Charlie Davis', 'Information Technology'),
('S2024004', 'Diana Lim', 'Computer Science'),
('S2024005', 'Evan Taylor', 'Data Science'),
('S2024006', 'Frank Wilson', 'Software Engineering');

-- --------------------------------------------------------

--
-- Table structure for `Users`
--

CREATE TABLE `Users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Assessor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
-- Test Accounts (Plaintext Passwords for Reference)
-- admin       -> admin123
-- Dr_smith    -> smith123
-- Prof_jones  -> jones123
--

INSERT INTO `Users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$12$SDdiBj6qgTVtU7KKhi/mROuIxtW6ZjBY5IcuTBQRrqs0mX5pRWo7u', 'Admin'),
(2, 'Dr_smith', '$2y$12$n.Td7esO2fVYH9yTTWMmeeKm0nnaQxW3ZLzYd7IA6nK/DSggiJDly', 'Assessor'),
(3, 'Prof_jones', '$2y$12$1udppAvrOXxZwKb5LYui2ewOvpkMXIyNGiWhCyUWOBWVtZDtZEKCi', 'Assessor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Assessments`
--
ALTER TABLE `Assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD UNIQUE KEY `internship_id` (`internship_id`);

--
-- Indexes for table `Internships`
--
ALTER TABLE `Internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assessor_id` (`assessor_id`);

--
-- Indexes for table `Students`
--
ALTER TABLE `Students`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Assessments`
--
ALTER TABLE `Assessments`
  MODIFY `assessment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Internships`
--
ALTER TABLE `Internships`
  MODIFY `internship_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Assessments`
--
ALTER TABLE `Assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `Internships` (`internship_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Internships`
--
ALTER TABLE `Internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `Students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `internships_ibfk_2` FOREIGN KEY (`assessor_id`) REFERENCES `Users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
