-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 28, 2013 at 01:21 PM
-- Server version: 5.5.25
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `adentify`
--

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `original_logo_url`, `large_logo_url`, `medium_logo_url`, `small_logo_url`, `added_at`, `products_count`, `tags_count`, `slug`) VALUES
(1, 'Coca-Cola', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-original-577x577/public/022013/coca-cola.ai-converted.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-original-577x577/public/022013/coca-cola.ai-converted.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/022013/coca-cola.ai-converted.png', NULL, '2013-06-06 00:00:00', 0, 10, 'coca-cola'),
(2, 'Redbull', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/122010/untitled-1_47.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/122010/untitled-1_47.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/122010/untitled-1_47.png', NULL, '2013-06-06 00:00:00', 0, 0, 'redbull'),
(3, 'Starbucks', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/102012/starbuckscoffeer-converted.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/102012/starbuckscoffeer-converted.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/102012/starbuckscoffeer-converted.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/102012/starbuckscoffeer-converted.png', '2013-06-06 00:00:00', 0, 0, 'starbucks'),
(4, 'Abercrombie & Fitch', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/072012/abercrombie.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/072012/abercrombie.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/072012/abercrombie.png', 'http://www.brandsoftheworld.com/sites/default/files/styles/logo-thumbnail/public/072012/abercrombie.png', '2013-06-06 00:00:00', 0, 0, 'abercrombie-fitch');

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `visible`, `created_at`, `slug`) VALUES
(1, 'Mode homme', 1, '2013-06-11 00:00:00', 'mode-homme'),
(2, 'Cuisine et gastronomie', 1, '2013-06-11 00:00:00', 'cuisine-gastronomie'),
(3, 'Cinéma, musique et livres', 1, '2013-06-11 00:00:00', 'cinema-musique-livres'),
(4, 'Voyages', 1, '2013-06-11 00:00:00', 'voyages'),
(5, 'High-tech', 1, '2013-06-11 00:00:00', 'high-tech'),
(6, 'Voitures et motos', 1, '2013-06-11 00:00:00', 'voitures-motos'),
(11, 'Mode femme', 1, '2013-06-11 00:00:00', 'mode-femme');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
