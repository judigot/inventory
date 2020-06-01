-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2020 at 03:15 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `appjudigot_inventory`
--
CREATE DATABASE IF NOT EXISTS `appjudigot_inventory` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `appjudigot_inventory`;

-- --------------------------------------------------------

--
-- Table structure for table `app_customer`
--

CREATE TABLE `app_customer` (
  `customer_id` int(10) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `client_address` varchar(255) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `jumbo_price` float DEFAULT 0,
  `jumbo_c_price` float DEFAULT 0,
  `xl_price` float DEFAULT 0,
  `xl_c_price` float DEFAULT 0,
  `l_price` float DEFAULT 0,
  `l_c_price` float DEFAULT 0,
  `m_price` float DEFAULT 0,
  `m_c_price` float DEFAULT 0,
  `s_price` float DEFAULT 0,
  `s_c_price` float DEFAULT 0,
  `p_price` float DEFAULT 0,
  `p_c_price` float DEFAULT 0,
  `pwe_price` float DEFAULT 0,
  `pwe_c_price` float DEFAULT 0,
  `d2_price` float DEFAULT 0,
  `d2_c_price` float DEFAULT 0,
  `marble_price` float DEFAULT 0,
  `marble_c_price` float DEFAULT 0,
  `d1b_price` float DEFAULT 0,
  `d1b_c_price` float DEFAULT 0,
  `d1s_price` float DEFAULT 0,
  `d1s_c_price` float DEFAULT 0,
  `b1_price` float DEFAULT 0,
  `b1_c_price` float DEFAULT 0,
  `b2_price` float DEFAULT 0,
  `b2_c_price` float DEFAULT 0,
  `b3_price` float DEFAULT 0,
  `b3_c_price` float DEFAULT 0,
  `es_price` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_custom_price`
--

CREATE TABLE `app_custom_price` (
  `custom_price_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `custom_price` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_order`
--

CREATE TABLE `app_order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_order_product`
--

CREATE TABLE `app_order_product` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_cost` float NOT NULL,
  `product_price` float NOT NULL,
  `discount` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='test';

-- --------------------------------------------------------

--
-- Table structure for table `app_product`
--

CREATE TABLE `app_product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_category` int(11) NOT NULL,
  `product_cost` float DEFAULT 0,
  `product_price` float DEFAULT 0,
  `product_stock` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_product_category`
--

CREATE TABLE `app_product_category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_user`
--

CREATE TABLE `app_user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('administrator','secretary') NOT NULL DEFAULT 'secretary'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `app_user`
--

INSERT INTO `app_user` (`user_id`, `username`, `password`, `user_type`) VALUES
(1, 'admin', '$2y$10$ejAMelr40nk12xTThVxJvuntJr39ABMeBBnJkatuQqCypgbjHw8lm', 'administrator'),
(2, 'secretary', '$2y$10$I2Ws7mxleh76eez7nXygbuYvkjZ.QYgYww2ImZI8aPef.hWdR8i/e', 'secretary');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_customer`
--
ALTER TABLE `app_customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `app_custom_price`
--
ALTER TABLE `app_custom_price`
  ADD PRIMARY KEY (`custom_price_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `app_order`
--
ALTER TABLE `app_order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `app_order_product`
--
ALTER TABLE `app_order_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `app_product`
--
ALTER TABLE `app_product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `product_category` (`product_category`);

--
-- Indexes for table `app_product_category`
--
ALTER TABLE `app_product_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `app_user`
--
ALTER TABLE `app_user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_customer`
--
ALTER TABLE `app_customer`
  MODIFY `customer_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_custom_price`
--
ALTER TABLE `app_custom_price`
  MODIFY `custom_price_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_order`
--
ALTER TABLE `app_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_order_product`
--
ALTER TABLE `app_order_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_product`
--
ALTER TABLE `app_product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_product_category`
--
ALTER TABLE `app_product_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_user`
--
ALTER TABLE `app_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app_custom_price`
--
ALTER TABLE `app_custom_price`
  ADD CONSTRAINT `app_custom_price_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `app_product` (`product_id`),
  ADD CONSTRAINT `app_custom_price_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `app_customer` (`customer_id`);

--
-- Constraints for table `app_order`
--
ALTER TABLE `app_order`
  ADD CONSTRAINT `app_order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `app_customer` (`customer_id`);

--
-- Constraints for table `app_order_product`
--
ALTER TABLE `app_order_product`
  ADD CONSTRAINT `app_order_product_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `app_order` (`order_id`),
  ADD CONSTRAINT `app_order_product_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `app_product` (`product_id`);

--
-- Constraints for table `app_product`
--
ALTER TABLE `app_product`
  ADD CONSTRAINT `app_product_ibfk_1` FOREIGN KEY (`product_category`) REFERENCES `app_product_category` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
