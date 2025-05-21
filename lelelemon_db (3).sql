-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 05:22 AM
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
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `image_path`, `product_name`, `category`, `price`, `created_at`, `updated_at`) VALUES
(2, 'default_product.jpg', 'basketball', 'Solid', 100.00, '2025-05-12 10:13:56', '2025-05-12 10:13:56'),
(4, 'default_product.jpg', 'water ', 'Liquid', 10.00, '2025-05-12 10:58:16', '2025-05-12 10:58:16'),
(5, 'uploads/682322e227d11.jpg', 'basketball', 'Liquid', 10.00, '2025-05-13 18:45:54', '2025-05-13 18:45:54'),
(6, 'uploads/68232303bac25.jpg', 'book', 'Solid', 50.00, '2025-05-13 18:46:27', '2025-05-13 18:46:27'),
(7, 'default_product.jpg', 'condom', 'Solid', 0.00, '2025-05-13 18:58:14', '2025-05-13 18:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `stock_level` enum('Low','High') GENERATED ALWAYS AS (case when `current_stock` < 50 then 'Low' else 'High' end) STORED,
  `unit_price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `quantity` int(11) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `sku`, `category`, `supplier`, `current_stock`, `unit_price`, `image_path`, `created_at`, `updated_at`, `quantity`, `price`) VALUES
(11, 'water ', 'WAT-20250510-668', 'Solid', '', 0, 0.00, 'default_product.jpg', '2025-05-10 14:24:28', '2025-05-13 04:33:52', 30, 70.00),
(15, 'basketball', 'BAS-20250512-486', 'Solid', '', 0, 0.00, 'default_product.jpg', '2025-05-12 01:52:33', '2025-05-13 04:30:10', 10, 50.00),
(16, 'basketball', 'BAS-20250512-459', 'Solid', '', 0, 0.00, 'default_product.jpg', '2025-05-12 01:55:25', '2025-05-14 06:49:09', 90, 100.00),
(21, 'lemon', 'LEM-20250514-892', 'Liquid', '', 0, 0.00, 'uploads/68243c56c56fb.jpg', '2025-05-14 06:46:46', '2025-05-14 06:47:32', 100, 15.00),
(22, 'champion', 'CHA-20250517-962', 'Liquid', '', 0, 0.00, 'default_product.jpg', '2025-05-16 23:39:53', '2025-05-16 23:40:05', 10, 50.00),
(23, 'container', 'CON-20250517-130', 'Solid', '', 0, 0.00, 'default_product.jpg', '2025-05-17 14:15:01', '2025-05-17 14:16:01', 10, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(180) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'profile-default.png',
  `position` varchar(50) DEFAULT 'staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `date_of_birth`, `email`, `phone`, `password`, `created_at`, `address`, `profile_image`, `position`) VALUES
(4, 'abdull jakoll', NULL, 'abduljakoll@gmail.com', '09456969097', '$2y$10$QJPrfXPesNa3a57Vn3l87O3r4MV9bxYcOwtT7KumbiQiToLyq5BLq', '2025-04-30 03:27:07', 'legazpi city, legazpi', NULL, 'staff'),
(5, 'althea  vergara', NULL, 'Avergara@gmail.com', '09456969097', '$2y$10$Rm.ImynlvBzU3qeb3EYe8ux5TcTBu3lxRb0dImu0XI.lT5LHRpg5W', '2025-05-05 07:38:19', 'legazpi city, legazpi', NULL, 'staff'),
(6, 'althea vergara', NULL, 'vergara@gmail.com', '09456969097', '$2y$10$.joNfksqDl.R9JOwkmmyXuoBXlQ1pAYUhglJOSgi4mA8VIMBsV.iG', '2025-05-05 07:39:25', 'legazpi city, legazpi', NULL, 'staff'),
(8, 'vince depota', NULL, 'vdepota@gmail.com', '09997556609', '$2y$10$EEk1jHgGpMG0ET6JHVOySe9Gss6RFDRaTMhGJWPxmb53oZC4IOaL2', '2025-05-06 22:09:25', 'legazpi city', NULL, 'staff'),
(12, 'christian nickhos alcazar', '2003-11-15', 'christiaindivina2326@gmail.com', '094555660987', '$2y$10$arVs18oxoFGJNzjz271VouQxZeiexeshWrUYfc5uWANJMIjXZvRGG', '2025-05-13 17:02:07', 'sorsogon cityy', '12_1747797076.png', 'staff'),
(14, 'christian nickhos alcazar', '2003-12-11', 'christiaindivina@gmail.com', '09455566098', '$2y$10$OsszfIcpf0UoDmy2JJsJRu/.taj09KaYE5pQKaEIpbpnIpCxr36JK', '2025-05-13 23:37:34', 'legazpi city', NULL, 'staff'),
(16, 'christian nickhos alcazar divina', '2003-12-11', 'cdivina@gmail.com', '09455566097', '$2y$10$hqRVaN25vcoV0E345M7ZnOSKPWbJMgo1OnHR1qJoguzatJ5HfQx9m', '2025-05-17 08:55:34', 'seabreeze cabid-an homes sorsogon city phase 2 blk 7 lot 1', NULL, 'staff'),
(17, 'christian nickhos alcazar divina', '2003-11-15', 'divina2316@gmail.com', '09455566097', '$2y$10$wboEsDNrN.IQvRGdQzUwlOXDa9bWPGVi5FxtTLmHcl09ZyA2hhkN.', '2025-05-18 03:08:18', 'seabreeze cabid-an homes sorsogon city phase 2 blk 7 lot 1', NULL, 'staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_password` (`password`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
