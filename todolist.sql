-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2025 at 03:37 AM
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
-- Database: `todolist`
--

-- --------------------------------------------------------

--
-- Table structure for table `subtasks`
--

CREATE TABLE `subtasks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `parent_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subtasks`
--

INSERT INTO `subtasks` (`id`, `name`, `status`, `parent_id`, `user_id`) VALUES
(2, 'rinjani', 1, 8, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `deadline` datetime DEFAULT NULL,
  `priority` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `status`, `user_id`, `deadline`, `priority`) VALUES
(6, 'keliling brazil', 2, 2, '2025-02-15 23:14:00', 0),
(7, 'mendaki', 0, 2, '2025-12-10 00:37:00', 0),
(8, 'ke lombok', 1, 2, '2025-07-10 12:45:00', 1),
(16, 'belajar matematika', 2, 8, '2025-02-10 12:01:00', 0),
(26, 'whislist hiking', 0, 1, '2025-03-01 00:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'rifky', 'liapasarbaru12@gmail.com', '$2y$10$Z8WK73XwJpWEL61V6zndhODv1GSQAcKAc.ElNc0o2u214NtEqVFXK', '2025-02-10 22:27:38'),
(2, 'tegar', 'tegarsatria106@gmail.com', '$2y$10$VXS1c/693QjnBtHH8hE3Ue/XJIlXFliMJ6rEZFXwjvDTLYmwRw8Km', '2025-02-10 22:55:07'),
(3, 'giovany', 'giovany01@gmail.com', '$2y$10$E3HHqQy1BlMLugHVvoILH.7smbJCtnbu2bKuUdI/0x8QbDKewzhSy', '2025-02-13 11:47:53'),
(4, 'aditya', 'aditya02@gmail.com', '$2y$10$XJdDfhWN2c51ZlSGRVUcKuJCejGUyrlHOZlGkXYfBDmeJ/RAJlRb6', '2025-02-13 11:48:18'),
(5, 'nayaka', 'nayaka03@gmail.com', '$2y$10$1AlgMDXHWvN9.oNpa0tWkOSFuxqdhMJmSPf/uLj9VSVP4A9XfSdgu', '2025-02-13 11:49:19'),
(6, 'faidz', 'faidz04@gmail.com', '$2y$10$AdXgFpFuGTMLH8w.d4oGu.J4v5B/SiHF7TWNDErTIj8Mzb9jAr7de', '2025-02-13 11:49:43'),
(7, 'aldi', 'aldi05@gmail.com', '$2y$10$DFjeV2y37gRjuMDB5X3kWOyrQVN/B9xe4.XNOzANanie98VH1wbw.', '2025-02-13 11:50:20'),
(8, 'rizky', 'rizky06@gmail.com', '$2y$10$3HVNLYzwRM4YxV8bAe.vJ.vI2h2PNuyzOYm8KmuWK35TQHRCJL.LC', '2025-02-13 11:51:35'),
(9, 'yudha', 'yudha07@gmail.com', '$2y$10$m4Rm6i1UvEra5ZWI37SJwOOC6pMOQHyWBlpkAOlEDrt81GSqu/kQm', '2025-02-13 11:52:58'),
(10, 'raja', 'raja08@gmail.com', '$2y$10$fbK5wMNBl3JBYGsSGQsZ.eALdU48EveJnlCrPsGLAi5hT1NHYIa0.', '2025-02-13 11:53:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `subtasks`
--
ALTER TABLE `subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_task` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `subtasks`
--
ALTER TABLE `subtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `subtasks`
--
ALTER TABLE `subtasks`
  ADD CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_user_task` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
