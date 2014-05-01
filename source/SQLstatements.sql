-- These are the statements to build all the sql tables used in the Food Bank site without
-- any data, because the real data used is confidential and have personal information about
-- real people.
-- In the fake database is possible to log in the system with the user jpd21 and the password asdfjkl;

-- phpMyAdmin SQL Dump
-- version 4.0.6deb1
-- http://www.phpmyadmin.net
--
-- Máquina: localhost
-- Data de Criação: 02-Abr-2014 às 05:09
-- Versão do servidor: 5.5.35-0ubuntu0.13.10.2
-- versão do PHP: 5.5.3-1ubuntu2.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de Dados: `BrazilianDB2014-03-17`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `Agency`
--

CREATE TABLE IF NOT EXISTS `Agency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organisation` varchar(32) DEFAULT NULL,
  `referralCentreReference` varchar(15) DEFAULT NULL,
  `homeTelephone` varchar(15) DEFAULT NULL,
  `mobileTelephone` varchar(15) DEFAULT NULL,
  `address1` varchar(32) DEFAULT NULL,
  `address2` varchar(32) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `town` varchar(32) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  `areaOfAssistance` varchar(100) DEFAULT NULL,
  `webAddress` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Client`
--

CREATE TABLE IF NOT EXISTS `Client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(5) DEFAULT NULL,
  `forename` varchar(15) DEFAULT NULL,
  `familyName` varchar(15) DEFAULT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `gender` varchar(7) DEFAULT NULL,
  `address1` varchar(128) DEFAULT NULL,
  `address2` varchar(128) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `town` varchar(32) DEFAULT NULL,
  `ethnicBackground` varchar(15) DEFAULT NULL,
  `oldAddress` longtext,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `distributionpoint`
--

CREATE TABLE IF NOT EXISTS `distributionpoint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `distributionPointName` varchar(32) DEFAULT NULL,
  `address1` varchar(32) DEFAULT NULL,
  `address2` varchar(32) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `town` varchar(32) DEFAULT NULL,
  `homeTelephone` varchar(15) DEFAULT NULL,
  `mobileTelephone` varchar(15) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `donation`
--

CREATE TABLE IF NOT EXISTS `donation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `idWarehouse` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `items` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `exchange`
--

CREATE TABLE IF NOT EXISTS `exchange` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pointOfIssue` int(11) DEFAULT NULL,
  `pointOfIssueType` varchar(10) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `idVoucher` int(11) DEFAULT NULL,
  `explanation` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fooditem`
--

CREATE TABLE IF NOT EXISTS `fooditem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `FoodParcel`
--

CREATE TABLE IF NOT EXISTS `FoodParcel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expiryDate` date DEFAULT NULL,
  `referenceNumber` varchar(10) DEFAULT NULL,
  `packingDate` date DEFAULT NULL,
  `wasGiven` tinyint(1) DEFAULT NULL,
  `idAgency` int(11) DEFAULT NULL,
  `idDP` int(11) DEFAULT NULL,
  `idWarehouse` int(11) DEFAULT NULL,
  `idFPType` int(11) DEFAULT NULL,
  `idVoucher` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `foodparceltype`
--

CREATE TABLE IF NOT EXISTS `foodparceltype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tagColour` varchar(15) DEFAULT NULL,
  `startingLetter` varchar(2) DEFAULT NULL,
  `name` varchar(15) DEFAULT NULL,
  `edited` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fptype_contains`
--

CREATE TABLE IF NOT EXISTS `fptype_contains` (
  `quantity` int(11) DEFAULT NULL,
  `idFoodParcelType` int(11) DEFAULT NULL,
  `idFoodItem` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `action` longtext,
  `idUsers` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1957 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `natureofneed`
--

CREATE TABLE IF NOT EXISTS `natureofneed` (
  `nature` varchar(32) DEFAULT NULL,
  `idVoucher` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `reportedproblems`
--

CREATE TABLE IF NOT EXISTS `reportedproblems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `problem` longtext,
  `idUsers` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `store`
--

CREATE TABLE IF NOT EXISTS `store` (
  `quantity` int(11) DEFAULT NULL,
  `idFoodItem` int(11) DEFAULT NULL,
  `idWarehouse` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(15) DEFAULT NULL,
  `password` varchar(41) DEFAULT NULL,
  `auth` int(11) DEFAULT NULL,
  `title` varchar(5) DEFAULT NULL,
  `forename` varchar(15) DEFAULT NULL,
  `familyName` varchar(15) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `volunteers`
--

CREATE TABLE IF NOT EXISTS `volunteers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(15) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `auth` int(11) DEFAULT NULL,
  `title` varchar(5) DEFAULT NULL,
  `forename` varchar(15) DEFAULT NULL,
  `familyName` varchar(15) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `homeTelephone` varchar(15) DEFAULT NULL,
  `mobileTelephone` varchar(15) DEFAULT NULL,
  `availability` varchar(100) DEFAULT NULL,
  `roles` varchar(100) DEFAULT NULL,
  `address1` varchar(32) DEFAULT NULL,
  `address2` varchar(32) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `town` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `voucher`
--

CREATE TABLE IF NOT EXISTS `voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numberOfAdults` int(11) DEFAULT NULL,
  `numberOfChildren` int(11) DEFAULT NULL,
  `wasExchanged` tinyint(1) DEFAULT NULL,
  `agencyVoucherReference` varchar(20) DEFAULT NULL,
  `helping` longtext,
  `dateVoucherIssued` date DEFAULT NULL,
  `idAgency` int(11) DEFAULT NULL,
  `idClient` int(11) DEFAULT NULL,
  `agencyContactName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=77 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `warehouse`
--

CREATE TABLE IF NOT EXISTS `warehouse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `centralWarehouseName` varchar(32) DEFAULT NULL,
  `address1` varchar(32) DEFAULT NULL,
  `address2` varchar(32) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `town` varchar(32) DEFAULT NULL,
  `homeTelephone` varchar(15) DEFAULT NULL,
  `mobileTelephone` varchar(15) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `work_agency`
--

CREATE TABLE IF NOT EXISTS `work_agency` (
  `idVolunteers` int(11) DEFAULT NULL,
  `idAgency` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `work_dp`
--

CREATE TABLE IF NOT EXISTS `work_dp` (
  `idVolunteers` int(11) DEFAULT NULL,
  `idDistributionPoint` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `work_warehouse`
--

CREATE TABLE IF NOT EXISTS `work_warehouse` (
  `idVolunteers` int(11) DEFAULT NULL,
  `idWarehouse` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
