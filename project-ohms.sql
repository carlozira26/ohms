-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2020 at 07:41 PM
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
-- Table structure for table `diagnostic_logs`
--

CREATE TABLE `diagnostic_logs` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `diagnostic_type` varchar(50) NOT NULL,
  `result` int(11) NOT NULL,
  `image_location` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `message_from` int(11) NOT NULL,
  `message_to` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_seen` enum('Y','N') NOT NULL DEFAULT 'N',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `status` varchar(20) DEFAULT 'New',
  `drtb` varchar(10) NOT NULL,
  `category` varchar(10) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `patient_logs`
--

CREATE TABLE `patient_logs` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `updated_by` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `date_added` date DEFAULT NULL,
  `is_active` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(10) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `licensenumber` varchar(50) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `clinic_name` varchar(200) NOT NULL,
  `clinic_address` varchar(200) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `usertype` int(1) NOT NULL,
  `token` text DEFAULT NULL,
  `is_active` enum('Y','N') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `birthdate`, `gender`, `contact_number`, `licensenumber`, `specialization`, `clinic_name`, `clinic_address`, `email`, `password`, `usertype`, `token`, `is_active`) VALUES
(1, 'Anonymous', 'Test', 'Admin', '1995-05-22', 'Male', '+639012345678', '2165723', 'test', 'Bagong Silang Health Center', 'Mandaluyong Street', 'admin@gmail.com', '0e80d388c94b6f9a9a64242602842df1', 1, 'gsHawgTw12FGaghajaj124', 'Y')
--
-- Indexes for dumped tables
--

--
-- Indexes for table `diagnostic_logs`
--
ALTER TABLE `diagnostic_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
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
-- Indexes for table `patient_logs`
--
ALTER TABLE `patient_logs`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `diagnostic_logs`
--
ALTER TABLE `diagnostic_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_logs`
--
ALTER TABLE `patient_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_medicine`
--
ALTER TABLE `patient_medicine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_intake`
--
ALTER TABLE `time_intake`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
