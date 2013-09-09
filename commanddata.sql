-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2013 at 04:44 AM
-- Server version: 5.6.11
-- PHP Version: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rofwar`
--

-- --------------------------------------------------------

--
-- Table structure for table `commanddata`
--

CREATE TABLE IF NOT EXISTS `commanddata` (
  `Command` varchar(20) NOT NULL,
  `Role` varchar(20) NOT NULL,
  `Name` varchar(30) NOT NULL,
  `Email` varchar(30) NOT NULL,
  `Nationality` varchar(20) NOT NULL,
  `Password` varchar(50) NOT NULL,
  `Readonly` tinyint(1) NOT NULL,
  PRIMARY KEY (`Command`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `commanddata`
--

INSERT INTO `commanddata` (`Command`, `Role`, `Name`, `Email`, `Nationality`, `Password`, `Readonly`) VALUES
('Admin', 'Administration', 'Administrator', '69giaptushka@gmail.com', 'Admin', 'rofwar', 0),
('CentralHQ', 'Central Planning', 'Central Powers Headquarters', '', 'Germany', 'berlin', 0),
('CentralRanks', 'Central Briefing', 'Central Powers Ranks', '', 'Germany', 'munich', 1),
('EntenteHQ', 'Entente Planning', 'Entente Headquarters', '', 'France', 'paris', 0),
('EntenteRanks', 'Entente Briefing', 'Entente Ranks', '', 'France', 'verdun', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
