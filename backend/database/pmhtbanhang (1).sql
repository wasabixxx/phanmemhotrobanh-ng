-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3307
-- Thời gian đã tạo: Th10 04, 2024 lúc 12:41 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `pmhtbanhang`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_buy` decimal(10,2) NOT NULL,
  `price_sell` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `product_name`, `quantity`, `price_buy`, `price_sell`, `image`, `product_code`) VALUES
(8, 'cứt chó ', 4970, 10.00, 20.00, 'images/abu7.png', 'CUT'),
(10, 'siêu cứt', 530, 20000.00, 50000.00, 'images/1 (4).jpg', 'NGu'),
(11, 'cứt ngu', 10000, 20000.00, 500000.00, 'images/draw2.svg', 'CUTNGU');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `profit` decimal(10,2) NOT NULL,
  `sale_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sales`
--

INSERT INTO `sales` (`id`, `product_code`, `user_id`, `product_id`, `quantity`, `total`, `profit`, `sale_time`) VALUES
(4, 'CUT', 8, 8, 10, 200.00, 200.00, '2024-11-04 14:36:54'),
(5, 'NGu', 8, 10, 10, 500000.00, 0.00, '2024-11-04 15:04:09'),
(6, 'NGu', 8, 10, 10, 500000.00, 300000.00, '2024-11-04 15:07:22'),
(7, 'CUT', 8, 8, 10, 200.00, 100.00, '2024-11-04 15:08:44'),
(8, 'NGu', 8, 10, 10, 500000.00, 300000.00, '2024-11-04 15:08:44'),
(9, 'NGu', 4, 10, 10, 500000.00, 300000.00, '2024-11-04 15:39:38'),
(10, 'CUT', 8, 8, 10, 200.00, 100.00, '2024-11-04 15:59:08'),
(11, 'NGu', 8, 10, 10, 500000.00, 300000.00, '2024-11-04 15:59:08'),
(12, 'CUT', 4, 8, 10, 200.00, 100.00, '2024-11-04 18:41:22');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role_id`, `shift_id`) VALUES
(2, 'manager', '$2y$10$e0xFZszE7gUO2y7A3m7FFe.CUtZ0.BPhQj2mk9XU8zy66/37PeGdG', 2, NULL),
(3, 'staff1', '$2y$10$e0xFZszE7gUO2y7A3m7FFe.CUtZ0.BPhQj2mk9XU8zy66/37PeGdG', 3, 2),
(4, 'khanh', '$2y$10$WJi6ooG/BHCEc9sEtzgXHe3LpRRkYQNd4TE7KVQ5MVnVyspGmA/ua', 1, NULL),
(6, 'quanli', '$2y$10$2Dx3wcDPobihIuWlW625ZOjLqS1zdjXs9nzznG.cWrbml2eYOHE.G', 2, NULL),
(8, 'staff', '$2y$10$l7nLXN0B6/wBuxOEE3qA9OJVITsM97huwDpraki18Voxb31/vurA6', 3, 1);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `shift_id` (`shift_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
