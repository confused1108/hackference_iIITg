-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2018 at 03:53 AM
-- Server version: 10.1.19-MariaDB
-- PHP Version: 5.5.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `talent`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `username`, `password`) VALUES
(1, 'name', 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `total_var` text NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `name`, `total_var`, `created_date`) VALUES
(1, 'Athletics', '87.68', '2018-09-24 13:37:34');

-- --------------------------------------------------------

--
-- Table structure for table `distribution`
--

CREATE TABLE `distribution` (
  `dist_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `param_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `loading` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `distribution`
--

INSERT INTO `distribution` (`dist_id`, `group_id`, `param_id`, `cat_id`, `loading`) VALUES
(1, 2, 1, 1, '25');

-- --------------------------------------------------------

--
-- Table structure for table `dist_group`
--

CREATE TABLE `dist_group` (
  `group_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `parameters` varchar(1000) NOT NULL,
  `group_reliability` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dist_group`
--

INSERT INTO `dist_group` (`group_id`, `name`, `cat_id`, `parameters`, `group_reliability`) VALUES
(2, 'guyzz_force', 1, '1,2', '78.56'),
(3, 'confused1108', 1, 'dgerg,dwdw,test 1', '78.125'),
(4, 'Athletics', 1, 'dgerg, dwdw, test 1', '780');

-- --------------------------------------------------------

--
-- Table structure for table `grading`
--

CREATE TABLE `grading` (
  `grading_id` int(11) NOT NULL,
  `applicable` varchar(500) NOT NULL,
  `criteria` varchar(500) NOT NULL,
  `min` int(11) NOT NULL,
  `max` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `grading`
--

INSERT INTO `grading` (`grading_id`, `applicable`, `criteria`, `min`, `max`) VALUES
(28, '', 'poor', 1, 45),
(29, '', 'Good', 46, 70),
(30, '', 'Best', 71, 100);

-- --------------------------------------------------------

--
-- Table structure for table `norms`
--

CREATE TABLE `norms` (
  `norm_id` int(11) NOT NULL,
  `param_id` int(11) NOT NULL,
  `lower_limit` varchar(100) NOT NULL,
  `upper_limit` varchar(100) NOT NULL,
  `score` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `norms`
--

INSERT INTO `norms` (`norm_id`, `param_id`, `lower_limit`, `upper_limit`, `score`) VALUES
(1, 4, '20', '21', '5'),
(2, 4, '21', '22', '10'),
(3, 4, '22', '23', '15'),
(4, 4, '23', '24', '20'),
(5, 4, '24', '25', '25'),
(6, 4, '25', '26', '30'),
(7, 4, '26', '27', '35'),
(8, 4, '27', '28', '40'),
(9, 4, '28', '29', '45'),
(10, 4, '29', '30', '50'),
(11, 4, '30', '31', '55'),
(12, 4, '31', '32', '60'),
(13, 4, '32', '33', '65'),
(14, 4, '33', '34', '70'),
(15, 4, '34', '35', '75'),
(16, 4, '35', '36', '80'),
(17, 4, '36', '37', '85'),
(18, 4, '37', '38', '90'),
(19, 4, '38', '39', '95'),
(20, 4, '39', '40', '100'),
(21, 5, '79', '283.75', '5'),
(22, 5, '283.75', '488.5', '10'),
(23, 5, '488.5', '693.25', '15'),
(24, 5, '693.25', '898', '20'),
(25, 5, '898', '1102.75', '25'),
(26, 5, '1102.75', '1307.5', '30'),
(27, 5, '1307.5', '1512.25', '35'),
(28, 5, '1512.25', '1717', '40'),
(29, 5, '1717', '1921.75', '45'),
(30, 5, '1921.75', '2126.5', '50'),
(31, 5, '2126.5', '2331.25', '55'),
(32, 5, '2331.25', '2536', '60'),
(33, 5, '2536', '2740.75', '65'),
(34, 5, '2740.75', '2945.5', '70'),
(35, 5, '2945.5', '3150.25', '75'),
(36, 5, '3150.25', '3355', '80'),
(37, 5, '3355', '3559.75', '85'),
(38, 5, '3559.75', '3764.5', '90'),
(39, 5, '3764.5', '3969.25', '95'),
(40, 5, '3969.25', '4174', '100'),
(41, 6, '1454', '4109.5', '5'),
(42, 6, '4109.5', '6765', '10'),
(43, 6, '6765', '9420.5', '15'),
(44, 6, '9420.5', '12076', '20'),
(45, 6, '12076', '14731.5', '25'),
(46, 6, '14731.5', '17387', '30'),
(47, 6, '17387', '20042.5', '35'),
(48, 6, '20042.5', '22698', '40'),
(49, 6, '22698', '25353.5', '45'),
(50, 6, '25353.5', '28009', '50'),
(51, 6, '28009', '30664.5', '55'),
(52, 6, '30664.5', '33320', '60'),
(53, 6, '33320', '35975.5', '65'),
(54, 6, '35975.5', '38631', '70'),
(55, 6, '38631', '41286.5', '75'),
(56, 6, '41286.5', '43942', '80'),
(57, 6, '43942', '46597.5', '85'),
(58, 6, '46597.5', '49253', '90'),
(59, 6, '49253', '51908.5', '95'),
(60, 6, '51908.5', '54564', '100');

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

CREATE TABLE `organization` (
  `org_id` int(11) NOT NULL,
  `org_name` text NOT NULL,
  `type` varchar(500) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(500) NOT NULL,
  `district` varchar(500) NOT NULL,
  `state` varchar(500) NOT NULL,
  `country` varchar(500) NOT NULL,
  `pincode` varchar(50) NOT NULL,
  `email` varchar(500) NOT NULL,
  `website` varchar(300) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `phone_alt` varchar(50) NOT NULL,
  `contact_person` varchar(500) NOT NULL,
  `contact_person_desg` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`org_id`, `org_name`, `type`, `address`, `city`, `district`, `state`, `country`, `pincode`, `email`, `website`, `phone`, `phone_alt`, `contact_person`, `contact_person_desg`) VALUES
(1, 'dwdfw`', 'AK', 'fwed', 'uhuh', 'ub', 'b', 'u', 'j', 'uj', 'u', 'u', 'h', 'jh', 'uh'),
(2, 'dwdfw`', 'AK', 'fwed', 'uhuh', 'ub', 'b', 'u', 'j', 'uj', 'u', 'u', 'h', 'jh', 'uh'),
(3, 'sdfsdf', 'HI', 'hihbui', 'jubujbgu', 'ujhbugbugh', 'uihuigbuygb', 'ujgbugbvuyhjgvb', 'ujgbvyuhgvyh', 'hybvyuhgvb', 'ujgbyuhgvbyh', 'yhugyuhgv', 'yhugvyhgvyh', 'uyhhg', 'yug');

-- --------------------------------------------------------

--
-- Table structure for table `parameter`
--

CREATE TABLE `parameter` (
  `param_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `type` varchar(500) NOT NULL,
  `min` int(11) NOT NULL,
  `max` int(11) NOT NULL,
  `how_to` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `parameter`
--

INSERT INTO `parameter` (`param_id`, `cat_id`, `name`, `type`, `min`, `max`, `how_to`) VALUES
(1, 1, 'dgerg', 'specific', 4, 26, 'adadq'),
(2, 1, 'dwdw', 'genetic', 847, 878, 'no idea'),
(3, 1, 'test 1', 'specific', 78, 25, 'don''t know'),
(4, 1, 'check', 'Select', 20, 40, 'check for norms'),
(5, 1, 'check for float', 'specific', 79, 4174, 'dd'),
(6, 1, 'Admin Admin', 'genetic', 1454, 54564, 'dd');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `designation` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `picture` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `org_id`, `name`, `designation`, `email`, `phone`, `username`, `password`, `permission`, `picture`) VALUES
(1, 3, 'weedwed', 'dsd', 'dfwe@had.com', 'asdn', 'aud', 'wsd', '', ''),
(2, 2, 'hitesh', 'admin', 'abcs@gmail.com', '7894561235', 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `distribution`
--
ALTER TABLE `distribution`
  ADD PRIMARY KEY (`dist_id`);

--
-- Indexes for table `dist_group`
--
ALTER TABLE `dist_group`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `grading`
--
ALTER TABLE `grading`
  ADD PRIMARY KEY (`grading_id`);

--
-- Indexes for table `norms`
--
ALTER TABLE `norms`
  ADD PRIMARY KEY (`norm_id`);

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`org_id`);

--
-- Indexes for table `parameter`
--
ALTER TABLE `parameter`
  ADD PRIMARY KEY (`param_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `distribution`
--
ALTER TABLE `distribution`
  MODIFY `dist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `dist_group`
--
ALTER TABLE `dist_group`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `grading`
--
ALTER TABLE `grading`
  MODIFY `grading_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `norms`
--
ALTER TABLE `norms`
  MODIFY `norm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;
--
-- AUTO_INCREMENT for table `organization`
--
ALTER TABLE `organization`
  MODIFY `org_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `parameter`
--
ALTER TABLE `parameter`
  MODIFY `param_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
