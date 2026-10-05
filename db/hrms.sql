-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2021 at 01:58 PM
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
-- Database: `hrms`
--

-- --------------------------------------------------------

--
-- Table structure for table `deduction`
--

CREATE TABLE `deduction` (
  `row_ded_id` int(11) NOT NULL,
  `caption` varchar(45) DEFAULT NULL,
  `amount` double DEFAULT 0,
  `row_nonstatic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `deduction`
--

INSERT INTO `deduction` (`row_ded_id`, `caption`, `amount`, `row_nonstatic_id`) VALUES
(1, 'UNION DUES', 753.98, 1),
(2, 'NHF', 942.48, 1),
(3, 'PENSION', 2827.43, 1),
(4, 'FED UNI DUTSE SAVING DEPOSIT', 2000, 1),
(5, 'FEDERAL MORTGAGE BANK OF NIGERIA', 5799.84, 1),
(6, 'UNION DUES', 3005.34, 2),
(7, 'NHF', 3756.68, 2),
(8, 'PENSION', 11270.03, 2),
(9, 'FED UNI DUTSE SAVING DEPOSIT', 5000, 2),
(10, 'UNION DUES', 2914.16, 3),
(11, 'NHF', 3642.7, 3),
(12, 'PENSION', 10928.11, 3),
(13, 'FED UNI DUTSE MFB LTD LOAN', 38500, 3),
(14, 'FED UNI DUTSE SSANU LOAN RECOVERY', 13333.33, 3),
(15, 'FED UNI DUTSE MUSLIM FORUM', 1000, 3),
(16, 'UNION DUES', 2238.36, 4),
(17, 'NHF', 2797.95, 4),
(18, 'PENSION', 8393.86, 4),
(19, 'FED UNI DUTSE MATERIAL SUPPLY LOAN', 18333.33, 4),
(20, 'FED UNI DUTSE SAVING DEPOSIT', 2000, 4),
(21, 'UNION DUES', 2380.96, 5),
(22, 'NHF', 2976.19, 5),
(23, 'PENSION', 8928.58, 5),
(24, 'UNION DUES', 2095.77, 6),
(25, 'NHF', 2619.71, 6),
(26, 'PENSION', 7859.12, 6),
(27, 'NAAT LEVY', 10000, 6),
(28, 'UNION DUES', 2238.36, 7),
(29, 'NHF', 2797.95, 7),
(30, 'PENSION', 8393.86, 7),
(31, 'UNION DUES', 2238.36, 8),
(32, 'NHF', 2797.95, 8),
(33, 'PENSION', 8393.86, 8),
(34, 'FED UNI DUTSE SAVING DEPOSIT', 4000, 8),
(35, 'UNION DUES', 2095.77, 9),
(36, 'NHF', 2619.71, 9),
(37, 'PENSION', 7859.12, 9),
(38, 'UNION DUES', 2685.14, 10),
(39, 'NHF', 3356.43, 10),
(40, 'PENSION', 10069.28, 10),
(41, 'FED UNI DUTSE MFB LTD LOAN', 43000, 10),
(42, 'FED UNI DUTSE MATERIAL SUPPLY LOAN', 30250, 10),
(43, 'FED UNI DUTSE SAVING DEPOSIT', 5000, 10),
(44, 'FED UNI DUTSE MUSLIM FORUM', 1000, 10),
(45, 'NHF', 3168.38, 11),
(46, 'PENSION', 9505.15, 11),
(47, 'NHF DED OCT ARREARS', 3168.38, 11),
(48, 'PENSION DED OCT ARREARS', 9505.15, 11),
(49, 'NHF', 7403.44, 12),
(50, 'PENSION', 22210.31, 12),
(51, 'NHF DED OCT ARREARS', 7403.44, 12),
(52, 'PENSION DED OCT ARREARS', 22210.31, 12),
(53, 'NHF', 10937.49, 13),
(54, 'PENSION', 32812.48, 13),
(55, 'NHF DED OCT ARREARS', 10937.49, 13),
(56, 'PENSION DED OCT ARREARS', 32812.48, 13),
(57, 'NHF', 3637.75, 14),
(58, 'PENSION', 10913.24, 14),
(59, 'NHF DED OCT ARREARS', 3637.75, 14),
(60, 'PENSION DED OCT ARREARS', 10913.24, 14),
(61, 'NHF', 3637.75, 15),
(62, 'PENSION', 10913.24, 15),
(63, 'NHF DED OCT ARREARS', 3637.75, 15),
(64, 'PENSION DED OCT ARREARS', 10913.24, 15),
(65, 'NHF', 3168.38, 16),
(66, 'PENSION', 9505.15, 16),
(67, 'NHF DED OCT ARREARS', 3168.38, 16),
(68, 'PENSION DED OCT ARREARS', 9505.15, 16),
(69, 'NHF', 2619.71, 17),
(70, 'PENSION', 7859.12, 17),
(71, 'NHF DED OCT ARREARS', 2619.71, 17),
(72, 'PENSION DED OCT ARREARS', 7859.12, 17),
(73, 'NHF', 3168.38, 18),
(74, 'PENSION', 9505.15, 18),
(75, 'NHF DED OCT ARREARS', 3168.38, 18),
(76, 'PENSION DED OCT ARREARS', 9505.15, 18),
(77, 'NHF', 4126.39, 19),
(78, 'PENSION', 12379.16, 19),
(79, 'NHF DED OCT ARREARS', 4126.39, 19),
(80, 'PENSION DED OCT ARREARS', 12379.16, 19),
(81, 'NHF', 3168.38, 20),
(82, 'PENSION', 9505.15, 20),
(83, 'NHF DED OCT ARREARS', 3168.38, 20),
(84, 'PENSION DED OCT ARREARS', 9505.15, 20),
(85, 'NHF', 3041.75, 21),
(86, 'PENSION', 9125.24, 21),
(87, 'NHF DED OCT ARREARS', 3041.75, 21),
(88, 'PENSION DED OCT ARREARS', 9125.24, 21),
(89, 'NHF', 3168.38, 22),
(90, 'PENSION', 9505.15, 22),
(91, 'NHF DED OCT ARREARS', 3168.38, 22),
(92, 'PENSION DED OCT ARREARS', 9505.15, 22),
(93, 'NHF', 2619.71, 23),
(94, 'PENSION', 7859.12, 23),
(95, 'NHF DED OCT ARREARS', 2619.71, 23),
(96, 'PENSION DED OCT ARREARS', 7859.12, 23),
(97, 'NHF', 4126.39, 24),
(98, 'PENSION', 12379.16, 24),
(99, 'NHF DED OCT ARREARS', 4126.39, 24),
(100, 'PENSION DED OCT ARREARS', 12379.16, 24),
(101, 'NHF', 2619.71, 25),
(102, 'PENSION', 7859.12, 25),
(103, 'NHF DED OCT ARREARS', 2619.71, 25),
(104, 'PENSION DED OCT ARREARS', 7859.12, 25),
(105, 'NHF', 2708.83, 26),
(106, 'PENSION', 8126.49, 26);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `dept_id` int(11) NOT NULL,
  `department` varchar(45) DEFAULT NULL,
  `fac_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `directorate_unit`
--

CREATE TABLE `directorate_unit` (
  `directorate_id` int(11) NOT NULL,
  `directorate_unit_name` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `earning`
--

CREATE TABLE `earning` (
  `row_earn_id` int(11) NOT NULL,
  `caption` varchar(45) DEFAULT NULL,
  `amount` double DEFAULT 0,
  `row_nonstatic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `earning`
--

INSERT INTO `earning` (`row_earn_id`, `caption`, `amount`, `row_nonstatic_id`) VALUES
(1, 'CONTISS Cons Salary', 37699.08, 1),
(2, 'CONTISS Cons Salary', 150267.08, 2),
(3, 'CONTISS Cons Salary', 145708.08, 3),
(4, 'CONTISS Cons Salary', 111918.08, 4),
(5, 'CONTISS Cons Salary', 119047.75, 5),
(6, 'CONTISS Cons Salary', 104788.33, 6),
(7, 'CONTISS Cons Salary', 111918.08, 7),
(8, 'CONTISS Cons Salary', 111918.08, 8),
(9, 'CONTISS Cons Salary', 104788.33, 9),
(10, 'CONTISS Cons Salary', 134257.08, 10),
(11, 'CONUASS Cons Salary', 126735.33, 11),
(12, 'OCT RETRO ARREARS', 126735.33, 11),
(13, 'CONUASS Cons Salary', 296137.42, 12),
(14, 'OCT RETRO ARREARS', 296137.42, 12),
(15, 'CONUASS Cons Salary', 437499.67, 13),
(16, 'OCT RETRO ARREARS', 437499.67, 13),
(17, 'CONUASS Cons Salary', 145509.83, 14),
(18, 'OCT RETRO ARREARS', 145509.83, 14),
(19, 'CONUASS Cons Salary', 145509.83, 15),
(20, 'OCT RETRO ARREARS', 145509.83, 15),
(21, 'CONUASS Cons Salary', 126735.33, 16),
(22, 'OCT RETRO ARREARS', 126735.33, 16),
(23, 'CONTISS Cons Salary', 104788.33, 17),
(24, 'OCT RETRO ARREARS', 104788.33, 17),
(25, 'CONUASS Cons Salary', 126735.33, 18),
(26, 'OCT RETRO ARREARS', 126735.33, 18),
(27, 'CONUASS Cons Salary', 165055.5, 19),
(28, 'OCT RETRO ARREARS', 165055.5, 19),
(29, 'CONUASS Cons Salary', 126735.33, 20),
(30, 'OCT RETRO ARREARS', 126735.33, 20),
(31, 'CONTISS Cons Salary', 121669.83, 21),
(32, 'OCT RETRO ARREARS', 121669.83, 21),
(33, 'CONUASS Cons Salary', 126735.33, 22),
(34, 'OCT RETRO ARREARS', 126735.33, 22),
(35, 'CONTISS Cons Salary', 104788.33, 23),
(36, 'OCT RETRO ARREARS', 104788.33, 23),
(37, 'CONUASS Cons Salary', 165055.5, 24),
(38, 'OCT RETRO ARREARS', 165055.5, 24),
(39, 'CONTISS Cons Salary', 104788.33, 25),
(40, 'OCT RETRO ARREARS', 104788.33, 25),
(41, 'CONTISS Cons Salary', 108353.17, 26);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `sp_no` varchar(45) DEFAULT NULL,
  `title` varchar(45) DEFAULT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(45) DEFAULT NULL,
  `oname` varchar(45) DEFAULT NULL,
  `phone_no` varchar(45) DEFAULT NULL,
  `gender` varchar(45) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `doa` date DEFAULT NULL,
  `appo_type` varchar(45) DEFAULT NULL,
  `sub_type` varchar(45) DEFAULT NULL,
  `entry_level` varchar(45) DEFAULT NULL,
  `entry_step` int(11) DEFAULT NULL,
  `designation` varchar(45) DEFAULT NULL,
  `directorate_id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `lga_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `sp_no`, `title`, `fname`, `lname`, `oname`, `phone_no`, `gender`, `email`, `dob`, `doa`, `appo_type`, `sub_type`, `entry_level`, `entry_step`, `designation`, `directorate_id`, `dept_id`, `lga_id`) VALUES
(3, 'JP/R/576', NULL, 'AKOR', 'WOMBOH', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(4, 'SP/R/082', NULL, 'GINDAU', 'HUSSAINI', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(5, 'SP/R/299', NULL, 'USMAN', 'LAWAN', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(6, 'SP/R/2200', NULL, 'UMAR', 'BALARABE', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(7, 'SP/R/1827', NULL, 'MUHAMMAD', 'ZAKAR', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(8, 'SP/R/2619', NULL, 'HELEN', 'ETUKUDOH', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(9, 'SP/R/954', NULL, 'ALHASSAN', 'ABDULLAHI', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(10, 'SP/R/2386', NULL, 'LAWAL', 'SULE', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(11, 'SP/R/2569', NULL, 'SURAJO', 'GIMBA', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(12, 'SP/R/928', NULL, 'JAMILA', 'USMAN', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(13, 'SP/R/2948', NULL, 'MUKHTAR', 'ABDULLATEEF', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(14, 'SP/R/2937', NULL, 'ABDULRAHMAN', 'ALIYU', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(15, 'SP/R/2901', NULL, 'NUHU', 'SAMBO', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(16, 'SP/R/3073', NULL, 'CATHERINE', 'ASAJU', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(17, 'SP/R/2971', NULL, 'ABDULBASIT', 'MUSA', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(18, 'SP/R/2996', NULL, 'IBRAHIM', 'ZUBAIRU', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(19, 'SP/R/3041', NULL, 'ADAMU', 'LAWAN', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(20, 'SP/R/2950', NULL, 'TUKUR', 'NASIR', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(21, 'SP/R/3005', NULL, 'MUSTAPHA', 'BALARABE', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(22, 'SP/R/2947', NULL, 'UMAR', 'ZANNAH', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(23, 'SP/R/3067', NULL, 'UMAR', 'ABDULLAHI', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(24, 'SP/R/2965', NULL, 'USMAN', 'MUSA', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(25, 'SP/R/3060', NULL, 'ABDULAZIZ', 'TIJJANI', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(26, 'SP/R/2918', NULL, 'MUHAMMAD', 'SHUAIBU', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(27, 'SP/R/3061', NULL, 'AMINU', 'HUSSENI', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(28, 'SP/R/3130', NULL, 'AMINU', 'JAAFAR', NULL, NULL, 'MALE', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `fac_id` int(11) NOT NULL,
  `faculty` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ippis_income_tax`
--

CREATE TABLE `ippis_income_tax` (
  `tax_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `row_nonstatic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ippis_income_tax`
--

INSERT INTO `ippis_income_tax` (`tax_id`, `amount`, `row_nonstatic_id`) VALUES
(1, 680.59, 1),
(2, 10278.04, 2),
(3, 9799.35, 3),
(4, 6251.4, 4),
(5, 7000.01, 5),
(6, 5502.78, 6),
(7, 6251.4, 7),
(8, 6251.4, 8),
(9, 5502.78, 9),
(10, 8596.99, 10),
(11, 13741.16, 11),
(12, 59012.33, 12),
(13, 101833.22, 13),
(14, 17702.57, 14),
(15, 17702.57, 15),
(16, 13741.16, 16),
(17, 9348.33, 17),
(18, 13741.16, 18),
(19, 22114.93, 19),
(20, 13741.16, 20),
(21, 12672.33, 21),
(22, 13741.16, 22),
(23, 9348.33, 23),
(24, 22114.93, 24),
(25, 9348.33, 25),
(26, 5877.08, 26);

-- --------------------------------------------------------

--
-- Table structure for table `ippis_nonstatic_record`
--

CREATE TABLE `ippis_nonstatic_record` (
  `row_nonstatic_id` int(11) NOT NULL,
  `grade` varchar(45) DEFAULT NULL,
  `step` int(11) DEFAULT NULL,
  `job` varchar(45) DEFAULT NULL,
  `bank_name` varchar(45) DEFAULT NULL,
  `acctno` varchar(15) DEFAULT NULL,
  `pfa` varchar(45) DEFAULT NULL,
  `pfa_pin` varchar(45) DEFAULT NULL,
  `dept` varchar(45) DEFAULT NULL,
  `month` int(45) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `ippis_static_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ippis_nonstatic_record`
--

INSERT INTO `ippis_nonstatic_record` (`row_nonstatic_id`, `grade`, `step`, `job`, `bank_name`, `acctno`, `pfa`, `pfa_pin`, `dept`, `month`, `year`, `ippis_static_id`) VALUES
(1, 'GL03 CONTISS', 7, 'NON ACADEMIC', 'Zenith Bank Plc', '2081522499', 'Trustfund Pensions Plc', NULL, 'LIBRARY', 12, 2020, 1),
(2, 'GL09 CONTISS', 3, 'NON ACADEMIC', 'Jaiz Bank Plc', '0001509333', 'Premium Pension Limited', NULL, 'LIBRARY', 12, 2020, 2),
(3, 'GL09 CONTISS', 2, 'NON ACADEMIC', 'Jaiz Bank Plc', '0006997456', 'Premium Pension Limited', NULL, 'LIBRARY', 12, 2020, 3),
(4, 'GL07 CONTISS', 3, 'NON ACADEMIC', 'Fidelity Bank Plc', '6171841128', 'Not Available', NULL, 'LIBRARY', 12, 2020, 4),
(5, 'GL07 CONTISS', 5, 'NON ACADEMIC', 'First Bank of Nigeria Plc', '3026531419', 'Not Available', NULL, 'LIBRARY', 12, 2020, 5),
(6, 'GL07 CONTISS', 1, 'NON ACADEMIC', 'First Bank of Nigeria Plc', '3088325317', 'Not Available', NULL, 'MICROBIOLOGY', 12, 2020, 6),
(7, 'GL07 CONTISS', 3, 'NON ACADEMIC', 'First Bank of Nigeria Plc', '3039762260', 'Premium Pension Limited', NULL, 'LIBRARY', 12, 2020, 7),
(8, 'GL07 CONTISS', 3, 'UNSPECIFIED', 'Access Bank Nigeria Plc', '0052382383', 'Premium Pension Limited', NULL, 'LIBRARY', 12, 2020, 8),
(9, 'GL07 CONTISS', 1, 'NON ACADEMIC', 'Access Bank Nigeria Plc', '0707293079', 'Stanbic IBTC Pension \nManagers Limited', NULL, 'LIBRARY', 12, 2020, 9),
(10, 'GL08 CONTISS', 4, 'NON ACADEMIC', 'Polaris Bank Limited', '1742240461', 'Not Available', NULL, 'LIBRARY', 12, 2020, 10),
(11, 'GL01 CONUASS', 2, '', 'First Bank of Nigeria Plc', '3115872209', '', NULL, '', 12, 2020, 11),
(12, 'GL05 CONUASS', 2, '', 'Guaranty Trust Bank Plc', '48315116', '', NULL, '', 12, 2020, 12),
(13, 'GL07 CONUASS', 2, '', 'United Bank For Africa Plc', '1000579059', '', NULL, '', 12, 2020, 13),
(14, 'GL02 CONUASS', 2, '', 'First City Monument Bank Plc', '2361621020', '', NULL, '', 12, 2020, 14),
(15, 'GL02 CONUASS', 2, '', 'First Bank of Nigeria Plc', '3082126880', '', NULL, '', 12, 2020, 15),
(16, 'GL01 CONUASS', 2, '', 'Diamond Bank Nigeria Plc', '47766252', '', NULL, '', 12, 2020, 16),
(17, 'GL07 CONTISS', 1, '', 'Diamond Bank Nigeria Plc', '54686084', '', NULL, '', 12, 2020, 17),
(18, 'GL01 CONUASS', 2, '', 'First Bank of Nigeria Plc', '3098004541', '', NULL, '', 12, 2020, 18),
(19, 'GL03 CONUASS', 2, '', 'First Bank of Nigeria Plc', '3088566741', '', NULL, '', 12, 2020, 19),
(20, 'GL01 CONUASS', 2, '', 'Zenith Bank Plc', '2209413777', '', NULL, '', 12, 2020, 20),
(21, 'GL08 CONTISS', 1, '', 'Diamond Bank Nigeria Plc', '52378514', '', NULL, '', 12, 2020, 21),
(22, 'GL01 CONUASS', 2, '', 'Access Bank Nigeria Plc', '732166515', '', NULL, '', 12, 2020, 22),
(23, 'GL07 CONTISS', 1, '', 'First Bank of Nigeria Plc', '123456789', '', NULL, '', 12, 2020, 23),
(24, 'GL03 CONUASS', 2, '', 'United Bank For Africa Plc', '1003571140', '', NULL, '', 12, 2020, 24),
(25, 'GL07 CONTISS', 1, '', 'First City Monument Bank Plc', '5065275014', '', NULL, '', 12, 2020, 25),
(26, 'GL07 CONTISS', 2, 'SYSTEM ANALYST II', 'United Bank For Africa Plc', '1007094045', '', NULL, '', 12, 2020, 26);

-- --------------------------------------------------------

--
-- Table structure for table `ippis_static_record`
--

CREATE TABLE `ippis_static_record` (
  `row_id` int(11) NOT NULL,
  `legacy` varchar(45) DEFAULT NULL,
  `ippis_no` varchar(45) DEFAULT NULL,
  `tin` varchar(15) DEFAULT NULL,
  `school` varchar(45) DEFAULT NULL,
  `tax_state` varchar(45) DEFAULT NULL,
  `employee_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ippis_static_record`
--

INSERT INTO `ippis_static_record` (`row_id`, `legacy`, `ippis_no`, `tin`, `school`, `tax_state`, `employee_id`) VALUES
(1, 'JPR576', 'TI117515', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 3),
(2, 'SPR082', 'TI119673', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 4),
(3, 'SPR299', 'TI119675', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 5),
(4, 'SPR2200', 'TI145791', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 6),
(5, 'SPR1827', 'TI145800', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 7),
(6, 'SPR2619', 'TI145880', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 8),
(7, 'SPR954', 'TI145882', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 9),
(8, 'SPR2386', 'TI147780', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 10),
(9, 'SPR2569', 'TI147783', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 11),
(10, 'SPR928', 'TI147940', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', 'JIGAWA', 12),
(11, 'SPR2948', 'TI310554', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 13),
(12, 'SPR2937', 'TI310557', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 14),
(13, 'SPR2901', 'TI310559', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 15),
(14, 'SPR3073', 'TI310561', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 16),
(15, 'SPR2971', 'TI310572', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 17),
(16, 'SPR2996', 'TI310583', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 18),
(17, 'SPR3041', 'TI310584', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 19),
(18, 'SPR2950', 'TI310586', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 20),
(19, 'SPR3005', 'TI310598', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 21),
(20, 'SPR2947', 'TI310602', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 22),
(21, 'SPR3067', 'TI310604', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 23),
(22, 'SPR2965', 'TI310616', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 24),
(23, 'SPR3060', 'TI310621', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 25),
(24, 'SPR2918', 'TI310633', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 26),
(25, 'SPR3061', 'TI310643', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 27),
(26, 'SPR3130', 'TI310829', '', 'FEDERAL UNIVERSITY DUTSE JIGAWA \nSTATE', '', 28);

-- --------------------------------------------------------

--
-- Table structure for table `month`
--

CREATE TABLE `month` (
  `month_id` int(11) NOT NULL,
  `month_name` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `month`
--

INSERT INTO `month` (`month_id`, `month_name`) VALUES
(1, 'JANUARY'),
(2, 'FEBRUARY'),
(3, 'MARCH'),
(4, 'APRIL'),
(5, 'MAY'),
(6, 'JUNE'),
(7, 'JULY'),
(8, 'AUGUST'),
(9, 'SEPTEMBER'),
(10, 'OCTOBER'),
(11, 'NOVEMBER'),
(12, 'DECEMBER');

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

CREATE TABLE `promotion` (
  `promot_id` int(11) NOT NULL,
  `previous_level` varchar(45) DEFAULT NULL,
  `previous_step` int(11) DEFAULT NULL,
  `current_level` varchar(45) DEFAULT NULL,
  `current_step` int(11) DEFAULT NULL,
  `dop` date DEFAULT NULL,
  `emp_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(150) NOT NULL,
  `access_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `access_level`) VALUES
(1, 'bursary', 'b59c67bf196a4758191e42f76670ceba', 0),
(2, 'SP/R/2619', '5350e598cd7b7d6be11a905a28010926', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deduction`
--
ALTER TABLE `deduction`
  ADD PRIMARY KEY (`row_ded_id`,`row_nonstatic_id`),
  ADD KEY `fk_Deduction_IPPIS_nonstatic_record1_idx` (`row_nonstatic_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`dept_id`),
  ADD KEY `fk_department_faculty_idx` (`fac_id`);

--
-- Indexes for table `directorate_unit`
--
ALTER TABLE `directorate_unit`
  ADD PRIMARY KEY (`directorate_id`);

--
-- Indexes for table `earning`
--
ALTER TABLE `earning`
  ADD PRIMARY KEY (`row_earn_id`,`row_nonstatic_id`),
  ADD KEY `fk_Earnings_IPPIS_nonstatic_record_idx` (`row_nonstatic_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`) USING BTREE;

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`fac_id`);

--
-- Indexes for table `ippis_income_tax`
--
ALTER TABLE `ippis_income_tax`
  ADD PRIMARY KEY (`tax_id`);

--
-- Indexes for table `ippis_nonstatic_record`
--
ALTER TABLE `ippis_nonstatic_record`
  ADD PRIMARY KEY (`row_nonstatic_id`,`ippis_static_id`),
  ADD KEY `fk_IPPIS_nonstatic_record_IPPIS_static_record1_idx` (`ippis_static_id`);

--
-- Indexes for table `ippis_static_record`
--
ALTER TABLE `ippis_static_record`
  ADD PRIMARY KEY (`row_id`);

--
-- Indexes for table `month`
--
ALTER TABLE `month`
  ADD PRIMARY KEY (`month_id`);

--
-- Indexes for table `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`promot_id`,`emp_id`),
  ADD KEY `fk_promotion_employee1_idx` (`emp_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deduction`
--
ALTER TABLE `deduction`
  MODIFY `row_ded_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `directorate_unit`
--
ALTER TABLE `directorate_unit`
  MODIFY `directorate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earning`
--
ALTER TABLE `earning`
  MODIFY `row_earn_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `fac_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ippis_income_tax`
--
ALTER TABLE `ippis_income_tax`
  MODIFY `tax_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `ippis_nonstatic_record`
--
ALTER TABLE `ippis_nonstatic_record`
  MODIFY `row_nonstatic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `ippis_static_record`
--
ALTER TABLE `ippis_static_record`
  MODIFY `row_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `month`
--
ALTER TABLE `month`
  MODIFY `month_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `fk_department_faculty` FOREIGN KEY (`fac_id`) REFERENCES `faculty` (`fac_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `fk_employee_department1` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_employee_directorate_unit1` FOREIGN KEY (`directorate_id`) REFERENCES `directorate_unit` (`directorate_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_employee_lga1` FOREIGN KEY (`lga_id`) REFERENCES `lga` (`lga_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `promotion`
--
ALTER TABLE `promotion`
  ADD CONSTRAINT `fk_promotion_employee1` FOREIGN KEY (`emp_id`) REFERENCES `employee` (`employee_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
