-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2026 at 10:36 PM
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
-- Database: `sms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `target_role` enum('all','student','teacher') NOT NULL DEFAULT 'all',
  `posted_by` int(10) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `body`, `target_role`, `posted_by`, `is_active`, `created_at`) VALUES
(1, 'Welcome to 2024-2025 Academic Year', 'We welcome all students and staff to a new and exciting academic year. Please ensure all registrations are completed by end of week.', 'all', 1, 1, '2026-05-26 20:21:03'),
(2, 'Staff Meeting — Friday 3 PM', 'All teaching staff are reminded of the mandatory meeting this Friday at 3:00 PM in the conference room. Attendance is compulsory.', 'teacher', 1, 1, '2026-05-26 20:21:03'),
(3, 'Library Books Now Available', 'New textbooks for all Form 1 and Form 2 students are now available at the library. Please collect yours with your student ID.', 'student', 1, 1, '2026-05-26 20:21:03'),
(4, 'Exam Timetable Released', 'The end of term examination timetable has been released. Students are advised to check the notice board for their exam schedules.', 'student', 1, 1, '2026-05-26 20:21:03'),
(5, 'Grading System Update', 'Please note that the grading system has been updated for this academic year. Kindly review the new grading scale on the system.', 'teacher', 1, 1, '2026-05-26 20:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `course_id`, `date`, `status`) VALUES
(1, 6, 1, '2025-01-06', 'Present'),
(2, 6, 1, '2025-01-07', 'Present'),
(3, 6, 1, '2025-01-08', 'Absent'),
(4, 6, 1, '2025-01-09', 'Present'),
(5, 6, 1, '2025-01-10', 'Late'),
(6, 6, 2, '2025-01-06', 'Present'),
(7, 6, 2, '2025-01-07', 'Present'),
(8, 6, 2, '2025-01-08', 'Present'),
(9, 7, 1, '2025-01-06', 'Absent'),
(10, 7, 1, '2025-01-07', 'Present'),
(11, 7, 1, '2025-01-08', 'Present'),
(12, 7, 1, '2025-01-09', 'Absent'),
(13, 7, 1, '2025-01-10', 'Present'),
(14, 8, 1, '2025-01-06', 'Present'),
(15, 8, 1, '2025-01-07', 'Present'),
(16, 8, 1, '2025-01-08', 'Present'),
(17, 8, 3, '2025-01-06', 'Present'),
(18, 8, 3, '2025-01-07', 'Late'),
(19, 8, 3, '2025-01-08', 'Present'),
(20, 9, 1, '2025-01-06', 'Late'),
(21, 9, 1, '2025-01-07', 'Present'),
(22, 9, 1, '2025-01-08', 'Excused'),
(23, 9, 3, '2025-01-06', 'Present'),
(24, 9, 3, '2025-01-07', 'Absent'),
(25, 9, 3, '2025-01-08', 'Present'),
(26, 10, 2, '2025-01-06', 'Present'),
(27, 10, 2, '2025-01-07', 'Present'),
(28, 10, 2, '2025-01-08', 'Present'),
(29, 10, 3, '2025-01-06', 'Present'),
(30, 10, 3, '2025-01-07', 'Present'),
(31, 10, 3, '2025-01-08', 'Absent');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` varchar(50) NOT NULL,
  `academic_year` varchar(20) NOT NULL DEFAULT '2024-2025',
  `capacity` smallint(5) UNSIGNED NOT NULL DEFAULT 40
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `level`, `academic_year`, `capacity`) VALUES
(1, 'Form 1A', 'Form 1', '2024-2025', 40),
(2, 'Form 2B', 'Form 2', '2024-2025', 40),
(3, 'Form 3C', 'Form 3', '2024-2025', 35);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `description`, `credits`, `teacher_id`, `class_id`, `status`) VALUES
(1, 'MATH101', 'Mathematics', 'Algebra, geometry and calculus basics', 4, 1, 1, 'active'),
(2, 'ENG101', 'English Language', 'Grammar, composition and literature', 3, 2, 1, 'active'),
(3, 'PHY101', 'Physics', 'Mechanics, waves and electromagnetism', 4, 1, 2, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrolled_at`) VALUES
(11, 6, 1, '2026-05-26 20:16:00'),
(12, 6, 2, '2026-05-26 20:16:00'),
(13, 7, 1, '2026-05-26 20:16:00'),
(14, 7, 2, '2026-05-26 20:16:00'),
(15, 8, 1, '2026-05-26 20:16:00'),
(16, 8, 3, '2026-05-26 20:16:00'),
(17, 9, 1, '2026-05-26 20:16:00'),
(18, 9, 3, '2026-05-26 20:16:00'),
(19, 10, 2, '2026-05-26 20:16:00'),
(20, 10, 3, '2026-05-26 20:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `exam_type` enum('CA','Midterm','Final') NOT NULL,
  `marks` decimal(5,2) NOT NULL DEFAULT 0.00,
  `grade` varchar(3) NOT NULL DEFAULT 'F',
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `course_id`, `exam_type`, `marks`, `grade`, `remarks`, `recorded_by`, `recorded_at`) VALUES
(1, 6, 1, 'CA', 78.00, 'B+', 'Good effort', 1, '2026-05-26 20:16:54'),
(2, 6, 1, 'Midterm', 82.00, 'A', 'Excellent', 1, '2026-05-26 20:16:54'),
(3, 6, 2, 'CA', 91.00, 'A+', 'Outstanding', 1, '2026-05-26 20:16:54'),
(4, 7, 1, 'CA', 55.00, 'C', 'Needs improvement', 1, '2026-05-26 20:16:54'),
(5, 7, 1, 'Midterm', 60.00, 'C', 'Satisfactory', 1, '2026-05-26 20:16:54'),
(6, 7, 2, 'CA', 72.00, 'B+', 'Good', 1, '2026-05-26 20:16:54'),
(7, 8, 1, 'CA', 88.00, 'A', 'Very good', 1, '2026-05-26 20:16:54'),
(8, 8, 3, 'CA', 65.00, 'B', 'Good', 1, '2026-05-26 20:16:54'),
(9, 9, 1, 'CA', 45.00, 'D', 'Needs more work', 1, '2026-05-26 20:16:54'),
(10, 9, 3, 'Midterm', 70.00, 'B+', 'Improving', 1, '2026-05-26 20:16:54'),
(11, 10, 2, 'CA', 95.00, 'A+', 'Exceptional', 1, '2026-05-26 20:16:54'),
(12, 10, 3, 'CA', 58.00, 'C', 'Satisfactory', 1, '2026-05-26 20:16:54');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `student_no` varchar(30) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_no`, `first_name`, `last_name`, `dob`, `gender`, `phone`, `address`, `guardian_name`, `guardian_phone`, `class_id`, `status`, `created_at`) VALUES
(6, 4, 'STU-001', 'Alice', 'Njeri', '2008-03-15', 'Female', '+254 700 200 001', NULL, NULL, NULL, 1, 'active', '2026-05-26 20:09:53'),
(7, 5, 'STU-002', 'Brian', 'Kamau', '2007-11-22', 'Male', '+254 700 200 002', NULL, NULL, NULL, 1, 'active', '2026-05-26 20:09:53'),
(8, 6, 'STU-003', 'Carol', 'Achieng', '2008-06-05', 'Female', '+254 700 200 003', NULL, NULL, NULL, 2, 'active', '2026-05-26 20:09:53'),
(9, 7, 'STU-004', 'David', 'Mwangi', '2007-09-17', 'Male', '+254 700 200 004', NULL, NULL, NULL, 2, 'active', '2026-05-26 20:09:53'),
(10, 8, 'STU-005', 'Eva', 'Chebet', '2008-01-30', 'Female', '+254 700 200 005', NULL, NULL, NULL, 3, 'active', '2026-05-26 20:09:53');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `teacher_no` varchar(30) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialisation` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_no`, `first_name`, `last_name`, `phone`, `specialisation`, `status`, `created_at`) VALUES
(1, 2, 'TCH-001', 'James', 'Omondi', '+254 700 111 001', 'Mathematics & Physics', 'active', '2026-05-26 14:10:11'),
(2, 3, 'TCH-002', 'Grace', 'Wanjiku', '+254 700 111 002', 'English & Literature', 'active', '2026-05-26 14:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `profile_photo`, `created_at`) VALUES
(1, 'System Administrator', 'admin@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'admin', 'active', NULL, '2026-05-25 09:20:58'),
(2, 'Mr. James Omondi', 'james.omondi@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'teacher', 'active', NULL, '2026-05-25 09:20:58'),
(3, 'Ms. Grace Wanjiku', 'grace.wanjiku@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'teacher', 'active', NULL, '2026-05-25 09:20:58'),
(4, 'Alice Njeri', 'alice.njeri@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'student', 'active', NULL, '2026-05-25 09:20:58'),
(5, 'Brian Kamau', 'brian.kamau@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'student', 'active', NULL, '2026-05-25 09:20:58'),
(6, 'Carol Achieng', 'carol.achieng@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'student', 'active', NULL, '2026-05-25 09:20:58'),
(7, 'David Mwangi', 'david.mwangi@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'student', 'active', NULL, '2026-05-25 09:20:58'),
(8, 'Eva Chebet', 'eva.chebet@sms.com', '$2y$12$7kB8tof7FVM1i.n7nxr77ebs6tmlZlj0BdXORt5sduIJxIkF2SK9C', 'student', 'active', NULL, '2026-05-25 09:20:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ann_role` (`target_role`),
  ADD KEY `idx_ann_active` (`is_active`),
  ADD KEY `fk_ann_user` (`posted_by`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendance` (`student_id`,`course_id`,`date`),
  ADD KEY `idx_attend_course` (`course_id`),
  ADD KEY `idx_attend_date` (`date`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_classes_year` (`academic_year`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_courses_code` (`code`),
  ADD KEY `idx_courses_teacher` (`teacher_id`),
  ADD KEY `idx_courses_class` (`class_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enrollment` (`student_id`,`course_id`),
  ADD KEY `idx_enroll_course` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_grades` (`student_id`,`course_id`,`exam_type`),
  ADD KEY `idx_grades_course` (`course_id`),
  ADD KEY `fk_grades_user` (`recorded_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_settings_key` (`key`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_students_no` (`student_no`),
  ADD UNIQUE KEY `uq_students_user` (`user_id`),
  ADD KEY `idx_students_class` (`class_id`),
  ADD KEY `idx_students_status` (`status`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_teachers_no` (`teacher_no`),
  ADD UNIQUE KEY `uq_teachers_user` (`user_id`),
  ADD KEY `idx_teachers_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_ann_user` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attend_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attend_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_courses_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grades_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grades_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grades_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
