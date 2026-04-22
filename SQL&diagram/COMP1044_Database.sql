-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- 主机： localhost:8889
-- 生成日期： 2026-04-22 03:13:36
-- 服务器版本： 8.0.44
-- PHP 版本： 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `COMP1044_CW_DB`
--

-- --------------------------------------------------------

--
-- 表的结构 `Assessments`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `Assessments`
--

INSERT INTO `Assessments` (`assessment_id`, `internship_id`, `task_score`, `health_safety_score`, `connectivity_score`, `report_score`, `clarity_score`, `lifelong_score`, `project_mgmt_score`, `time_mgmt_score`, `total_score`, `qualitative_comments`) VALUES
(1, 1, 9.00, 9.50, 9.00, 14.00, 9.00, 14.00, 14.00, 14.00, 92.50, 'Alice demonstrated outstanding technical skills and adapted perfectly to the company culture. Highly recommended.'),
(2, 2, 7.00, 8.00, 7.50, 11.00, 7.00, 10.50, 12.00, 11.00, 74.00, 'Bob completed the tasks adequately, but needs to improve his communication skills and time management.'),
(3, 4, 8.50, 9.00, 8.00, 13.00, 8.50, 12.00, 13.00, 12.50, 84.50, 'Diana is a fast learner and contributed well to the cloud architecture project. Good overall performance.'),
(4, 5, 55.00, 60.00, 58.00, 62.00, 55.00, 60.00, 58.00, 60.00, 58.50, 'Evan showed basic understanding of data analytics concepts but struggled with independent problem-solving. Improvement needed in time management and project delivery.'),
(5, 6, 40.00, 45.00, 40.00, 42.00, 40.00, 45.00, 42.00, 45.00, 42.50, 'Frank did not meet the expected standard. Frequent absence, poor engagement with assigned tasks, and weak technical foundation. Requires significant improvement.');

-- --------------------------------------------------------

--
-- 表的结构 `Internships`
--

CREATE TABLE `Internships` (
  `internship_id` int NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `assessor_id` int NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `other_details` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `Internships`
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
-- 表的结构 `Students`
--

CREATE TABLE `Students` (
  `student_id` varchar(20) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `programme` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `Students`
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
-- 表的结构 `Users`
--

CREATE TABLE `Users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Assessor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `Users`
--

INSERT INTO `Users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'admin_main', 'hashed_pwd_001', 'Admin'),
(2, 'Dr_smith', 'hashed_pwd_002', 'Assessor'),
(3, 'Prof_jones', 'hashed_pwd_003', 'Assessor');

--
-- 转储表的索引
--

--
-- 表的索引 `Assessments`
--
ALTER TABLE `Assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD UNIQUE KEY `internship_id` (`internship_id`);

--
-- 表的索引 `Internships`
--
ALTER TABLE `Internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assessor_id` (`assessor_id`);

--
-- 表的索引 `Students`
--
ALTER TABLE `Students`
  ADD PRIMARY KEY (`student_id`);

--
-- 表的索引 `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `Assessments`
--
ALTER TABLE `Assessments`
  MODIFY `assessment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `Internships`
--
ALTER TABLE `Internships`
  MODIFY `internship_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 限制导出的表
--

--
-- 限制表 `Assessments`
--
ALTER TABLE `Assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `Internships` (`internship_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `Internships`
--
ALTER TABLE `Internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `Students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `internships_ibfk_2` FOREIGN KEY (`assessor_id`) REFERENCES `Users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
