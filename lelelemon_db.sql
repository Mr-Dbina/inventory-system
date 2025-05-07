-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 07:39 AM
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
-- Database: `lelelemon_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `quantity`, `updated_at`, `image`, `price`) VALUES
(1, 'beer', 20, '2025-05-05 14:43:26', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAsJCQcJCQcJCQkJCwkJCQkJCQsJCwsMCwsLDA0QDBEODQ4MEhkSJRodJR0ZHxwpKRYlNzU2GioyPi0pMBk7IRP/2wBDAQcICAsJCxULCxUsHRkdLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCz/wAARCAEOAWYDASIAAhEB', 50),
(2, 'beer', 10, '2025-05-05 14:44:08', '', 100);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(90) NOT NULL,
  `last_name` varchar(90) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`, `created_at`, `address`) VALUES
(1, 'nickhos ', 'divina', 'nd@gmail.com', '09455566097', '$2y$10$rLS9jP1H2.vUrj3wGm4uS.2/FJWL5niSk1gxoL7uRuFAiBcqLvEoW', 'admin', '2025-04-16 08:04:13', 'sorsogon city'),
(2, 'yani', 'villar', 'gvillar@gmail.com', '09997556609', '$2y$10$CWLy.IbeH3TTud7uFAcVeOHvL/Sx4QpoGQ6Ui3oV7Lk6PskxvgVyC', 'admin', '2025-04-16 08:04:52', 'legazpi city'),
(3, 'nickhos ', 'divina', 'ckhos@gmail.com', '09997556609', '$2y$10$4X733c7Mc5lTUzHsIEESYOkOv.KUxJxHZ.z1nPSbE4XU8DTx1FBJm', 'admin', '2025-04-20 20:18:50', 'legazpi city'),
(4, 'abdull', 'jakoll', 'abduljakoll@gmail.com', '09456969097', '$2y$10$QJPrfXPesNa3a57Vn3l87O3r4MV9bxYcOwtT7KumbiQiToLyq5BLq', 'admin', '2025-04-30 03:27:07', 'legazpi city, legazpi'),
(5, 'althea ', 'vergara', 'Avergara@gmail.com', '09456969097', '$2y$10$Rm.ImynlvBzU3qeb3EYe8ux5TcTBu3lxRb0dImu0XI.lT5LHRpg5W', 'admin', '2025-05-05 07:38:19', 'legazpi city, legazpi'),
(6, 'althea', 'vergara', 'vergara@gmail.com', '09456969097', '$2y$10$.joNfksqDl.R9JOwkmmyXuoBXlQ1pAYUhglJOSgi4mA8VIMBsV.iG', 'staff', '2025-05-05 07:39:25', 'legazpi city, legazpi'),
(7, 'potanginamo', 'hayopka', 'nickhos@gmail.com', '09997556609', '$2y$10$tdiWtS8Dty7fxryPRd.n4edezV/mWGslSMuIf3I1GjZhiGaX3X6hK', 'staff', '2025-05-06 22:07:31', 'sorsogon city'),
(8, 'vince', 'depota', 'vdepota@gmail.com', '09997556609', '$2y$10$EEk1jHgGpMG0ET6JHVOySe9Gss6RFDRaTMhGJWPxmb53oZC4IOaL2', 'staff', '2025-05-06 22:09:25', 'legazpi city');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
