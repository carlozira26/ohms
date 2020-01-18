-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2020 at 04:41 PM
-- Server version: 10.4.8-MariaDB
-- PHP Version: 7.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project-ohms`
--

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `brandname` varchar(100) DEFAULT NULL,
  `genericname` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `brandname`, `genericname`, `instructions`, `is_active`) VALUES
(1, 'Ascorbic Acid', 'Ceelin', '1 teaspoon per serving', 'Y'),
(2, 'Paracetamol', 'Calpol', '15 teaspoon per serving', 'Y'),
(3, 'test', 'test', 'test', 'Y'),
(4, 'test', 'test', 'test', 'N'),
(5, 'test', 'test', 'test', 'Y'),
(6, 'Christian', 'Obille', 'Test', 'N'),
(7, 'Another', 'One', 'Another test', 'Y'),
(8, '', '', '', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `firstname` varchar(30) DEFAULT NULL,
  `middlename` varchar(30) DEFAULT NULL,
  `lastname` varchar(30) DEFAULT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(100) NOT NULL,
  `dateofbirth` date NOT NULL,
  `consultationdate` date NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `mobilenumber` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `drtb` varchar(10) NOT NULL,
  `category` varchar(10) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `dateofbirth`, `consultationdate`, `doctor_id`, `gender`, `mobilenumber`, `status`, `drtb`, `category`, `address`, `remarks`, `token`, `created_at`) VALUES
(1, 200001, 'Wendy', 'Gervacio', 'Bolos', 'wendybolos', '2cd9cb7bce7bdac9066413631f466d99', '1971-04-08', '0000-00-00', 3, 'Female', '+639183303425', 'Ongoing', 'No', NULL, '255 sen. neptali daang bakal, mandaluyong city', 'Lorem ipsum dolor sit amet', 'tSnsThjehsSmhZw79KgPyE', '0000-00-00 00:00:00'),
(2, 200002, 'Allan', 'Fadera', 'Fontarum', 'allanfontarum', '6ccbb4f7ad9cdf79a26e55b6f06675d8', '2003-08-06', '2020-01-10', NULL, 'Male', '+639663625219', 'New', 'Yes', NULL, '134 dreamville ligid tipas taguig city', 'Dysmenorrhea', 'hgnemWyEmQWRnJpfurBt2S', '2020-01-10 18:22:32'),
(3, 200003, 'Romeo', 'Pia', 'Gaviola', 'romeogaviola', 'f5e9c32cbf8a65bd83b82d876e2457ea', '1985-01-08', '2020-01-10', NULL, 'Male', '+639217119301', 'New', 'Yes', NULL, 'Pasig City', 'Diarrhea + headache', 'JPYxrEbgaErPVySbdmsF6w', '2020-01-10 18:27:33'),
(4, 200004, 'Jazmin', 'Diego', 'Gervacio', 'jazmingervacio', '21ef46437fbacfc4e10b55586390ee59', '2001-04-01', '2020-01-10', NULL, 'Male', '+639123456789', 'New', 'Yes', 'Cat I', 'test', 'Headache and dizziness', 'XTKz73KEGhNwHHajsgyR56', '2020-01-10 18:30:17'),
(5, 200005, 'John Christian', 'Santos', 'Obille', 'christianobille', '845fbb922344aa83d417c43ad65e04bf', '1995-05-22', '2020-01-18', NULL, 'Male', '+639055383442', 'Ongoing', 'Yes', NULL, 'M. Vasquez St., Harapin ang Bukas', '-- NOTHING FOLLOWS --', 'FRQZpfNzgCBgtDUAp4Rg4k', '2020-01-18 19:16:26'),
(6, 200006, 'Allen Marie', 'Generan', 'Galo', 'allengalo', '0fbaa79fb5711c4fb20e49b85cf25d8c', '1995-02-05', '2020-01-18', NULL, 'Female', '+639982961820', 'New', 'No', NULL, 'Ciudad Grande, Caloocan City', '--- nothing follows ----', 'ZvPbSTqAp9xYgck4GBWtYK', '2020-01-18 19:22:46');

-- --------------------------------------------------------

--
-- Table structure for table `patient_medicine`
--

CREATE TABLE `patient_medicine` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `medicineid` int(11) NOT NULL,
  `dosage` varchar(20) NOT NULL,
  `pieces` varchar(20) NOT NULL,
  `is_active` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `patient_medicine`
--

INSERT INTO `patient_medicine` (`id`, `uid`, `medicineid`, `dosage`, `pieces`, `is_active`) VALUES
(1, 1, 2, '500', '13', 'N'),
(2, 1, 1, '20', '32', 'N'),
(3, 1, 2, '500', '13', 'Y'),
(4, 1, 1, '20', '32', 'Y'),
(5, 4, 2, '500', '4', 'N'),
(6, 4, 3, '250', '1', 'N'),
(7, 3, 3, '500', '20', 'Y'),
(8, 3, 7, '400', '20', 'Y'),
(9, 4, 2, '500', '4', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `time_intake`
--

CREATE TABLE `time_intake` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `intakedays` varchar(200) NOT NULL,
  `intaketime` varchar(200) NOT NULL,
  `is_active` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `time_intake`
--

INSERT INTO `time_intake` (`id`, `uid`, `intakedays`, `intaketime`, `is_active`) VALUES
(1, 1, 'Monday,Tuesday', '00:30,02:30', 'Y'),
(2, 1, 'Wednesday,Thursday', '06:00,19:00', 'Y'),
(3, 2, 'Monday,Tuesday', '00:30,02:30', 'N'),
(4, 2, 'Wednesday,Thursday', '06:00,19:00', 'N'),
(5, 3, 'Monday', '06:30', 'Y'),
(6, 4, 'Monday', '06:30', 'Y'),
(8, 5, 'Monday', '00:00', 'Y'),
(9, 6, 'Monday,Tuesday', '06:35,09:20,10:30', 'N'),
(10, 6, 'Friday,Saturday', '23:30,09:05', 'N'),
(11, 7, 'Tuesday,Wednesday', '01:35,02:45', 'Y'),
(13, 6, 'Monday,Tuesday', '06:35,09:20,10:30', 'N'),
(14, 6, 'Friday,Saturday', '23:30,09:05', 'N'),
(15, 6, 'Wednesday,Thursday', '00:40,10:20', 'N'),
(16, 6, 'Monday,Tuesday', '06:35,09:20,10:30', 'Y'),
(17, 6, 'Friday,Saturday', '23:30,09:05', 'Y'),
(18, 2, 'Monday,Tuesday', '00:30,02:30', 'Y'),
(19, 2, 'Wednesday,Thursday', '06:00,19:00', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `licensenumber` int(11) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `clinic_name` varchar(200) NOT NULL,
  `clinic_address` varchar(200) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `usertype` int(1) NOT NULL,
  `token` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `birthdate`, `gender`, `contact_number`, `licensenumber`, `specialization`, `clinic_name`, `clinic_address`, `email`, `password`, `usertype`, `token`) VALUES
(1, 'Anonymous', 'Test', 'Admin', '1995-05-22', 'Male', '+639012345678', 2165723, 'test', 'Bagong Silang Health Center', 'Mandaluyong Street', 'admin@gmail.com', '0e80d388c94b6f9a9a64242602842df1', 1, 'gsHawgTw12FGaghajaj124'),
(2, 'Edward', 'George', 'Armstrong', '0000-00-00', 'Male', '', NULL, NULL, '', '', '', '', 2, NULL),
(3, 'Charles', NULL, 'Burton', '0000-00-00', 'Male', '', NULL, NULL, '', '', '', '', 2, NULL),
(4, 'Martin', NULL, 'Arrowsmith', '0000-00-00', 'Male', '', NULL, NULL, '', '', '', '', 2, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `firstname` (`firstname`),
  ADD KEY `middlename` (`middlename`),
  ADD KEY `lastname` (`lastname`),
  ADD KEY `consultationdate` (`consultationdate`);

--
-- Indexes for table `patient_medicine`
--
ALTER TABLE `patient_medicine`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_intake`
--
ALTER TABLE `time_intake`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_medicine`
--
ALTER TABLE `patient_medicine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `time_intake`
--
ALTER TABLE `time_intake`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
