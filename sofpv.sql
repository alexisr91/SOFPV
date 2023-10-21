-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 18 oct. 2023 à 23:07
-- Version du serveur : 5.7.36
-- Version de PHP : 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sofpv`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_response_contact`
--

DROP TABLE IF EXISTS `admin_response_contact`;
CREATE TABLE IF NOT EXISTS `admin_response_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D98A0771E7A1254A` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `alert`
--

DROP TABLE IF EXISTS `alert`;
CREATE TABLE IF NOT EXISTS `alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_17FD46C17294869C` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `alert`
--

INSERT INTO `alert` (`id`, `article_id`, `created_at`, `description`) VALUES
(1, 945, '2023-10-18 17:53:04', 'Contenu offensant');

-- --------------------------------------------------------

--
-- Structure de la table `alert_comment`
--

DROP TABLE IF EXISTS `alert_comment`;
CREATE TABLE IF NOT EXISTS `alert_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A27F5AEF8697D13` (`comment_id`),
  KEY `IDX_A27F5AEA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` int(11) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `views` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `admin_news` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_23A0E6629C1004E` (`video_id`),
  KEY `IDX_23A0E66F675F31B` (`author_id`),
  KEY `IDX_23A0E6612469DE2` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=947 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `video_id`, `author_id`, `title`, `slug`, `content`, `views`, `created_at`, `category_id`, `admin_news`, `active`) VALUES
(933, NULL, 388, 'Bienvenue sur SO FPV', 'bienvenue-sur-so-fpv', 'Le site SO FPV ouvre ses portes !<br />\n<br />\nIl présente une partie blog, dans laquelle vous êtes libres de publier vidéos de vol, crashs, questions et divers contenus en rapport avec le FPV.<br />\n<br />\nVous pouvez organiser une session sur la page dédiée, ou simplement vous inscrire à une session existante.<br />\n<br />\nLa boutique présentée est fictive et les produits proposés ne sont pas disponibles à la vente : ce site est à titre démonstratif pour un projet de développement web. Le système de paiement ne validera donc pas la commande.<br />\n<br />\nPour le reste, enjoy &amp; fly safe ;)', 18, '2023-10-05 15:12:16', 228, 1, 1),
(945, 9, 398, 'Test 1', 'test-1', 'Vestibulum elementum, lacus ut semper convallis, dolor est vestibulum libero, ac bibendum magna mi ut justo. Morbi hendrerit quam non urna gravida scelerisque. Donec eleifend venenatis orci consequat elementum. Etiam pharetra diam sem, in semper odio finibus mollis. Morbi et mauris a quam gravida lacinia. Aliquam commodo elementum ipsum sed mollis. Donec scelerisque ultricies sapien, in lacinia metus lacinia et.', 2, '2023-10-16 20:32:03', 225, 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantity` int(11) NOT NULL,
  `amount` double NOT NULL,
  `created_at` datetime NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ordering_id` int(11) DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BA388B74584665A` (`product_id`),
  KEY `IDX_BA388B78E6C7DE4` (`ordering_id`),
  KEY `IDX_BA388B7A76ED395` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cart`
--

INSERT INTO `cart` (`id`, `quantity`, `amount`, `created_at`, `product_id`, `user_id`, `ordering_id`, `reference`) VALUES
(1, 1, 3.6, '2023-10-12 20:08:42', 263, 398, 1, '6528362a1a968'),
(2, 4, 72, '2023-10-12 20:15:59', 264, 398, 2, '652837dfb7e80'),
(3, 3, 10.8, '2023-10-13 11:35:57', 263, 398, 3, '65290f7d0954e'),
(4, 1, 682.8, '2023-10-13 11:46:14', 261, 398, NULL, '652911e6c22a1'),
(5, 1, 682.8, '2023-10-13 11:57:28', 261, 398, NULL, '6529148894069'),
(6, 1, 682.8, '2023-10-13 11:57:59', 261, 398, NULL, '652914a73fe56'),
(7, 1, 682.8, '2023-10-13 12:15:02', 261, 398, NULL, '652918a6010b3'),
(8, 1, 682.8, '2023-10-13 12:16:24', 261, 398, 4, '652918f82152f'),
(9, 2, 7.2, '2023-10-13 12:19:10', 263, 398, 5, '6529199e56eb2'),
(10, 1, 3.6, '2023-10-13 12:44:52', 263, 398, NULL, '65291fa4c49ad'),
(11, 1, 3.6, '2023-10-13 12:45:53', 263, 398, 6, '65291fe172d87'),
(12, 1, 6, '2023-10-13 12:53:53', 269, 398, 7, '652921c14d3d1');

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(223, 'Session'),
(224, 'Crash'),
(225, 'Build'),
(226, 'Review'),
(227, 'Question'),
(228, 'Inclassable');

-- --------------------------------------------------------

--
-- Structure de la table `comment`
--

DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9474526C7294869C` (`article_id`),
  KEY `IDX_9474526CF675F31B` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `closed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `counter`
--

DROP TABLE IF EXISTS `counter`;
CREATE TABLE IF NOT EXISTS `counter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `counter`
--

INSERT INTO `counter` (`id`, `name`, `count`) VALUES
(88, 'Lipo', 10),
(89, 'ESC', 5),
(90, 'Frame', 42);

-- --------------------------------------------------------

--
-- Structure de la table `counter_user`
--

DROP TABLE IF EXISTS `counter_user`;
CREATE TABLE IF NOT EXISTS `counter_user` (
  `counter_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`,`user_id`),
  KEY `IDX_C21E0F2EFCEEF2E3` (`counter_id`),
  KEY `IDX_C21E0F2EA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20220901154536', '2022-09-01 15:45:48', 95),
('DoctrineMigrations\\Version20220901155240', '2022-09-01 15:52:45', 66),
('DoctrineMigrations\\Version20220910173947', '2022-09-10 17:39:58', 86),
('DoctrineMigrations\\Version20221009195206', '2022-10-09 19:52:15', 127),
('DoctrineMigrations\\Version20221009204106', '2022-10-09 20:41:12', 101),
('DoctrineMigrations\\Version20221011203128', '2022-10-11 20:31:36', 56),
('DoctrineMigrations\\Version20221011203240', '2022-10-11 20:32:45', 95),
('DoctrineMigrations\\Version20221011204251', '2022-10-11 20:42:54', 206),
('DoctrineMigrations\\Version20221017143156', '2022-10-17 14:32:10', 204),
('DoctrineMigrations\\Version20221021132014', '2022-10-21 13:20:23', 195),
('DoctrineMigrations\\Version20221021142615', '2022-10-21 14:26:19', 118),
('DoctrineMigrations\\Version20221021145218', '2022-10-21 14:52:34', 199),
('DoctrineMigrations\\Version20221022114608', '2022-10-22 11:47:29', 54),
('DoctrineMigrations\\Version20221022125526', '2022-10-22 12:55:34', 155),
('DoctrineMigrations\\Version20221028125327', '2022-10-28 14:53:31', 150),
('DoctrineMigrations\\Version20221031210458', '2022-10-31 22:05:16', 179),
('DoctrineMigrations\\Version20221031220421', '2022-10-31 23:04:33', 73),
('DoctrineMigrations\\Version20221102180504', '2022-11-02 19:05:12', 124),
('DoctrineMigrations\\Version20221109201550', '2022-11-09 21:39:06', 66),
('DoctrineMigrations\\Version20221109204746', '2022-11-09 21:47:51', 64),
('DoctrineMigrations\\Version20221222132928', '2022-12-22 14:29:49', 211),
('DoctrineMigrations\\Version20221222144846', '2022-12-22 15:49:03', 168),
('DoctrineMigrations\\Version20221223180233', '2022-12-23 19:02:49', 154),
('DoctrineMigrations\\Version20230109172223', '2023-01-09 18:22:35', 335),
('DoctrineMigrations\\Version20230117171520', '2023-01-17 18:15:36', 274),
('DoctrineMigrations\\Version20230124185152', '2023-01-24 19:52:02', 73),
('DoctrineMigrations\\Version20230124190949', '2023-01-24 20:09:54', 40),
('DoctrineMigrations\\Version20230128201208', '2023-01-28 21:12:16', 154),
('DoctrineMigrations\\Version20230215152128', '2023-02-15 16:21:39', 70),
('DoctrineMigrations\\Version20230215175729', '2023-02-15 18:57:40', 173),
('DoctrineMigrations\\Version20230217171358', '2023-02-17 18:14:07', 87),
('DoctrineMigrations\\Version20230316125308', '2023-03-16 13:53:16', 222),
('DoctrineMigrations\\Version20230316131122', '2023-03-16 14:11:34', 103),
('DoctrineMigrations\\Version20230316142247', '2023-03-16 15:22:56', 123),
('DoctrineMigrations\\Version20230316143743', '2023-03-16 15:37:51', 122),
('DoctrineMigrations\\Version20230316144626', '2023-03-16 15:46:34', 154),
('DoctrineMigrations\\Version20230320164525', '2023-03-20 17:45:32', 192),
('DoctrineMigrations\\Version20230322150136', '2023-03-22 16:01:44', 52),
('DoctrineMigrations\\Version20230322154448', '2023-03-22 16:44:58', 70),
('DoctrineMigrations\\Version20230324134059', '2023-03-24 14:41:10', 118),
('DoctrineMigrations\\Version20230324151406', '2023-03-24 16:14:13', 136),
('DoctrineMigrations\\Version20230324162656', '2023-03-24 17:27:16', 176),
('DoctrineMigrations\\Version20230324162937', '2023-03-24 17:29:44', 130),
('DoctrineMigrations\\Version20230324163816', '2023-03-24 17:38:20', 67),
('DoctrineMigrations\\Version20230329143729', '2023-03-29 16:37:37', 95),
('DoctrineMigrations\\Version20230329144027', '2023-03-29 16:40:33', 67),
('DoctrineMigrations\\Version20230329160958', '2023-03-29 18:10:02', 196),
('DoctrineMigrations\\Version20230329163100', '2023-03-29 18:31:05', 67),
('DoctrineMigrations\\Version20230402141117', '2023-04-02 16:11:27', 73),
('DoctrineMigrations\\Version20230402141343', '2023-04-02 16:13:47', 138),
('DoctrineMigrations\\Version20230402142416', '2023-04-02 16:24:20', 87),
('DoctrineMigrations\\Version20230402144535', '2023-04-02 16:45:43', 135),
('DoctrineMigrations\\Version20230402145114', '2023-04-02 16:51:24', 214),
('DoctrineMigrations\\Version20230402151836', '2023-04-02 17:18:40', 234),
('DoctrineMigrations\\Version20230402154756', '2023-04-02 17:48:00', 116),
('DoctrineMigrations\\Version20230403093658', '2023-04-03 11:37:05', 52),
('DoctrineMigrations\\Version20230403094007', '2023-04-03 11:40:16', 188),
('DoctrineMigrations\\Version20230410171157', '2023-04-10 19:12:05', 142),
('DoctrineMigrations\\Version20230415155821', '2023-04-15 17:58:32', 144),
('DoctrineMigrations\\Version20230415160142', '2023-04-15 18:01:45', 134),
('DoctrineMigrations\\Version20230415165032', '2023-04-15 18:50:36', 125),
('DoctrineMigrations\\Version20230501175659', '2023-05-01 19:57:09', 82),
('DoctrineMigrations\\Version20230501175947', '2023-05-01 19:59:57', 227),
('DoctrineMigrations\\Version20230504132027', '2023-05-04 15:20:37', 124),
('DoctrineMigrations\\Version20230505130835', '2023-05-05 15:08:42', 188),
('DoctrineMigrations\\Version20230511202540', '2023-05-11 22:25:54', 118),
('DoctrineMigrations\\Version20230517122213', '2023-05-17 14:22:23', 52),
('DoctrineMigrations\\Version20230517130926', '2023-05-17 15:09:30', 122),
('DoctrineMigrations\\Version20230517131431', '2023-05-17 15:14:35', 62),
('DoctrineMigrations\\Version20230517132415', '2023-05-17 15:28:32', 140),
('DoctrineMigrations\\Version20230517134624', '2023-05-17 15:46:28', 74),
('DoctrineMigrations\\Version20230601140245', '2023-06-01 16:02:54', 146),
('DoctrineMigrations\\Version20230710165450', '2023-07-10 18:55:43', 153),
('DoctrineMigrations\\Version20230715120427', '2023-07-15 14:04:36', 156),
('DoctrineMigrations\\Version20230720180624', '2023-07-20 20:06:34', 60),
('DoctrineMigrations\\Version20230720225052', '2023-07-21 00:51:02', 85),
('DoctrineMigrations\\Version20230722170811', '2023-07-22 19:08:20', 109),
('DoctrineMigrations\\Version20230904111555', '2023-09-04 13:16:14', 220),
('DoctrineMigrations\\Version20230904112440', '2023-09-04 13:24:44', 116),
('DoctrineMigrations\\Version20231013112817', '2023-10-13 13:28:38', 100);

-- --------------------------------------------------------

--
-- Structure de la table `drone`
--

DROP TABLE IF EXISTS `drone`;
CREATE TABLE IF NOT EXISTS `drone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frame` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motors` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `esc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reception` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lipo_cells` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `drone`
--

INSERT INTO `drone` (`id`, `frame`, `motors`, `fc`, `esc`, `cam`, `reception`, `lipo_cells`, `image`) VALUES
(39, 'Sloop V3 - Pirat Frame', 'Lumenier JohnnyFPV Cinematic - 1750Kv', 'Hobbywing F7', 'Hobbywing 60A', 'Cam DJI', 'Crossfire', 6, NULL),
(40, 'azerty', 'azerty', 'azerty', 'azerty', 'azerty', 'ExpressLRS', 6, 'quadJerome-651ac7b575455-652d68909f0e7.bmp');

-- --------------------------------------------------------

--
-- Structure de la table `image`
--

DROP TABLE IF EXISTS `image`;
CREATE TABLE IF NOT EXISTS `image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C53D045F7294869C` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `image`
--

INSERT INTO `image` (`id`, `article_id`, `source`) VALUES
(2, 945, 'quadRaceDenis-650c357e75c88-65293c2315b95-65293fa516dc2-652ffef70d542.bmp');

-- --------------------------------------------------------

--
-- Structure de la table `likes`
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_49CA4E7D7294869C` (`article_id`),
  KEY `IDX_49CA4E7DA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `map_spot`
--

DROP TABLE IF EXISTS `map_spot`;
CREATE TABLE IF NOT EXISTS `map_spot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authorization` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `admin_map_spot` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `map_spot`
--

INSERT INTO `map_spot` (`id`, `name`, `authorization`, `address`, `longitude`, `latitude`, `created_at`, `admin_map_spot`) VALUES
(115, 'La Sabla', 'Télépilotes Pro', '5 rue Gilbert Affre, 31830 Plaisance-du-Touch', '1.301499', '43.556387', '2023-10-05 15:12:16', 0),
(117, 'Lavoir', 'Public', '81400 Blaye-les-Mines', '2.142730', '44.039917', '2023-10-05 15:12:16', 0),
(118, 'DWS Gymnase', 'Public', '26 route de Portet, 31270 Villeneuve-Tolosane', '1.355887', '43.522812', '2023-10-05 15:12:16', 1),
(119, 'Château de Bram', 'Public', 'Valgros, 11150 Bram', '2.130921', '43.237722', '2023-10-05 15:12:16', 0),
(120, 'Parc des Quinze Sols', 'Public', 'Chemin du Tiers État, 31700 Blagnac', '1.390432', '43.664566', '2023-10-05 15:12:16', 0),
(121, 'Forêt de Bouconne', 'Public', 'Chem. du Ratelier, 31530 Montaigut-sur-Save', '1.2286994043916', '43.635499837933', '2023-10-05 15:12:16', 0);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `messenger_messages`
--

INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(1, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:28:\\\"le vrai premier test de mail\\\";i:1;s:5:\\\"utf-8\\\";i:2;s:56:\\\"<p>See Twig integration for better HTML integration!</p>\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:15:\\\"Naerys@test.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:14:\\\"admin@sofpv.fr\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:11:\\\"vrai test 1\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2023-07-20 23:41:07', '2023-07-20 23:41:07', NULL),
(2, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:10:\\\"vrai test2\\\";i:1;s:5:\\\"utf-8\\\";i:2;s:56:\\\"<p>See Twig integration for better HTML integration!</p>\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:15:\\\"Naerys@test.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:20:\\\"naerys.404@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:6:\\\"test 2\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2023-07-20 23:42:52', '2023-07-20 23:42:52', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `order`
--

DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `delivery_address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_stripe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `transporter_id` int(11) NOT NULL,
  `stripe_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_payment_intent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_status_id` int(11) DEFAULT NULL,
  `tracker_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F5299398A76ED395` (`user_id`),
  KEY `IDX_F52993984F335C8B` (`transporter_id`),
  KEY `IDX_F52993982F924C2F` (`delivery_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `order`
--

INSERT INTO `order` (`id`, `user_id`, `created_at`, `updated_at`, `delivery_address`, `status_stripe`, `reference`, `price`, `transporter_id`, `stripe_customer_id`, `stripe_payment_intent`, `delivery_status_id`, `tracker_id`) VALUES
(1, 398, '2023-10-12 20:09:24', '2023-10-12 20:09:40', '123 rue de l&#039;améthyste<br/>11100 Narbonne', 'succeeded', '652836549a5f4', 12.59, 48, 'cus_Oo5VRpXU1RzyZk', 'pi_3O0TK5LJ2OenaJq215McLPuM', 61, NULL),
(2, 398, '2023-10-12 20:16:24', '2023-10-12 20:16:40', '123 rue de l&#039;améthyste<br/>11100 Narbonne', 'succeeded', '652837f81b573', 76.99, 47, 'cus_Oo5cPu9j55G8nT', 'pi_3O0TQrLJ2OenaJq21TCpOWLv', 61, NULL),
(3, 398, '2023-10-13 11:41:03', '2023-10-13 11:41:17', '123 rue de l&#039;améthyste<br/>11100 narbonne', 'succeeded', '652910af79d63', 19.79, 48, 'cus_OoKWNUxn4wsKNr', 'pi_3O0hrgLJ2OenaJq20vxUkXmb', 61, NULL),
(4, 398, '2023-10-13 12:17:11', '2023-10-13 12:17:24', '123 rue des alpins<br/>11100 Narbonne', 'succeeded', '652919272495b', 691.79, 48, 'cus_OoL7bVPWbGeu25', 'pi_3O0iQeLJ2OenaJq21leL6Rm1', 61, NULL),
(5, 398, '2023-10-13 12:19:27', '2023-10-13 12:19:40', '123 rue de l&#039;améthyste<br/>11100 Narbonne', 'succeeded', '652919af80adc', 16.19, 48, 'cus_OoL9ysL1RssdIH', 'pi_3O0iSqLJ2OenaJq21upNxMMy', 61, NULL),
(6, 398, '2023-10-13 12:46:20', '2023-10-13 12:46:32', '123 rue des lapins<br/>12000 azerty', 'succeeded', '65291ffc3fba6', 8.59, 47, 'cus_OoLan5ncsr39Od', 'pi_3O0isrLJ2OenaJq21RGtBcAW', 61, NULL),
(7, 398, '2023-10-13 12:54:12', '2023-10-13 12:54:23', 'laura test <br>123 rue de l&#039;améthyste<br/>11100 Narbonne', 'succeeded', '652921d42f0c0', 14.99, 48, 'cus_OoLhMqVfcC5Gg7', 'pi_3O0j0TLJ2OenaJq20x703V7g', 61, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `order_status`
--

DROP TABLE IF EXISTS `order_status`;
CREATE TABLE IF NOT EXISTS `order_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `order_status`
--

INSERT INTO `order_status` (`id`, `status`, `status_description`) VALUES
(61, '0', 'En cours de préparation'),
(62, '1', 'Remis au transporteur'),
(63, '2', 'En cours de livraison'),
(64, '3', 'Livré'),
(65, '4', 'Commande annulée');

-- --------------------------------------------------------

--
-- Structure de la table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_ttc` double NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `stock` int(11) NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_ht` double NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=270 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product`
--

INSERT INTO `product` (`id`, `name`, `price_ttc`, `description`, `image`, `created_at`, `stock`, `slug`, `price_ht`, `active`) VALUES
(261, 'Casque DJI FPV V2', 682.8, '\n        ● Résolution 1440 x 810 par écran </br>\n        ● Enregistrement Vidéos MP4 à 720p/60im/s </br>\n        ● Alimentation Batterie externe entre 7,4 et 17,6V (recommandé 4S)  XT60 </br>\n        ● Dimensions 184 x 122 x 110 mm (sans antennes) 202 x 126 x 110 mm (avec antennes) </br>\n        ● Poids 415 g (bandeau et antennes inclus) </br>\n        ● Ecran Deux écrans de 2\" </br>\n        ● Fréquence de rafraîchissement de l\'écran 120 Hz </br>\n        ● Fréquence de communication 5,725 à 5,850 GHz </br>\n        ● Puissance de l\'émetteur (EIRP) FCC/MIC : </br>\n        ● Mode Vue en direct Mode Faible latence (720p/120 ips) Mode Haute qualité (720p/60 ips) </br>\n        ● Encodage vidéo MP4, H.264 </br> \n        ● Formats de lecture vidéo compatibles MP4, MOV, MKV (Encodage vidéo : H264 ; Encodage audio : AAC-LC, AAC-HE, AC-3, DTS, MP3) </br>\n        ● Température de fonctionnement 0 à 40 °C (32 à 104 °F) </br>\n        ● Puissance d\'entrée 7,4 à 17,6 V </br>\n        ● FOV Réglable de 30° à 54°. Taille d’image réglable de 50 % à 100 % </br>\n        ● Écart pupillaire 58 à 70 mm </br>\n        ● Batterie Batterie externe 6,6 à 21,75 V ; consommation totale d’énergie de 7 W </br>\n        ● Cartes mémoire compatibles Cartes microSD d’une capacité allant jusqu’à 128 Go </br>\n        ', 'product-1.jpg', '2023-10-05 15:12:16', 9, 'casque-dji-fpv-v2', 569, 1),
(262, 'Case GoPro Session 5', 10.8, 'Impression 3D en filament TPU pour case GoPro Session 5. \n        ', 'product-2.jpg', '2023-10-05 15:12:16', 0, 'case-gopro-session-5', 9, 0),
(263, 'Support Immortal T pour frame APEX', 3.6, 'Impression 3D en filament TPU de support Immortal T </br>\n        ● Frame APEX \n        ', 'product-3.jpg', '2023-10-05 15:12:16', 3, 'support-immortal-t-pour-frame-apex', 3, 1),
(264, 'T-shirt SO FPV', 18, '\n        ● T-Shirt floqué du logo SO FPV </br>\n        ● 100 % coton.\n        ', 'product-4.png', '2023-10-05 15:12:16', 0, 't-shirt-so-fpv', 15, 1),
(269, 'Strap Lipo SO FPV', 6, 'Strap lipo en kevlar, 20cm', 'product-default-6522e5f36b26a.jpg', '2023-10-08 19:22:29', 2, 'strap-lipo-so-fpv', 5, 1);

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `map_spot_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `timesheet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `past` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D044D5D42DA2B20` (`map_spot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `session`
--

INSERT INTO `session` (`id`, `map_spot_id`, `date`, `timesheet`, `created_at`, `past`) VALUES
(2, 115, '2023-10-31 00:00:00', 'après-midi', '2023-10-12 18:50:07', 0),
(3, 117, '2023-10-20 00:00:00', 'matin', '2023-10-13 13:38:16', 0);

-- --------------------------------------------------------

--
-- Structure de la table `session_user`
--

DROP TABLE IF EXISTS `session_user`;
CREATE TABLE IF NOT EXISTS `session_user` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`session_id`,`user_id`),
  KEY `IDX_4BE2D663613FECDF` (`session_id`),
  KEY `IDX_4BE2D663A76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `session_user`
--

INSERT INTO `session_user` (`session_id`, `user_id`) VALUES
(2, 398),
(3, 398);

-- --------------------------------------------------------

--
-- Structure de la table `transporter`
--

DROP TABLE IF EXISTS `transporter`;
CREATE TABLE IF NOT EXISTS `transporter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transporter`
--

INSERT INTO `transporter` (`id`, `name`, `description`, `price`) VALUES
(47, 'Colissimo', 'Livraison en 2 à 3 jours en France Métropolitaine', 4.99),
(48, 'Chronopost', 'Livraison en moins de 24h en France Métropolitaine', 8.99);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `drone_id` int(11) DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D6492CDF9A` (`drone_id`)
) ENGINE=InnoDB AUTO_INCREMENT=399 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `nickname`, `avatar`, `banner`, `firstname`, `lastname`, `address`, `address_complement`, `zip`, `city`, `created_at`, `drone_id`, `facebook`, `instagram`, `tiktok`, `active`) VALUES
(388, 'naerys.404@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$McMkkqQKywf1XW3mbuvoqeUl5.bNI6y4zHeK3dvJd25VrkkY5jYPa', 'Naerys', 'avatarDefault.jpg', 'bannerDefault.png', 'Laura', 'Admin', '40 rue d\'Aoste', NULL, '11100', 'Narbonne', '2023-10-05 15:12:12', 39, NULL, NULL, NULL, 1),
(389, 'langosh.bertha@example.org', '[]', '$2y$13$THG.DUp8J4LagW91PSvaAuPZlpRohaJdhhlwZl6CbR9IG/0O4EhwO', 'pauline.kassulke', 'avatarDefault.jpg', 'bannerDefault.png', 'Deondre', 'Lemke', '1067 Wuckert Groves Suite 044\nTorphyhaven, MT 44504-7849', 'Suite 204', '53173-6061', 'New Cletusville', '2023-10-05 15:12:12', NULL, NULL, NULL, NULL, 1),
(390, 'simone.adams@example.com', '[]', '$2y$13$HsKb6zZxdJ1o64izoWLTUuxVrtG2qNcKNIf7Pjx1PKXAovwFO58jm', 'lorenzo.haag', 'avatarDefault.jpg', 'bannerDefault.png', 'Adelia', 'Schaden', '3207 Dietrich Springs Apt. 005\nNorth Gerdaborough, OH 61103', 'Suite 745', '77452', 'Kittyport', '2023-10-05 15:12:13', NULL, NULL, NULL, NULL, 1),
(392, 'spinka.lilian@example.com', '[]', '$2y$13$EA2aWvOXcl6xxxwCuR3b2u7nHi1pf9BBM5f/l65nhwtdg6SgVUmhC', 'mac82', 'avatarDefault.jpg', 'bannerDefault.png', 'Carmel', 'Thiel', '236 Oberbrunner Square Suite 317\nLake Germainetown, RI 19129', 'Apt. 504', '90641', 'Rueckerton', '2023-10-05 15:12:13', NULL, NULL, NULL, NULL, 1),
(393, 'danial54@example.net', '[]', '$2y$13$kjMR4VHMwgF9HdwwgKMHc.cM6g.kF6BwNkFI4cOPdpf16tRNTtHm6', 'jailyn.kshlerin', 'avatarDefault.jpg', 'bannerDefault.png', 'Assunta', 'Hickle', '210 Carter Ferry Apt. 009\nNew Garfield, GA 83605-4831', NULL, '60993', 'East Albertha', '2023-10-05 15:12:14', NULL, NULL, NULL, NULL, 0),
(394, 'sawayn.caesar@example.org', '[]', '$2y$13$5j.isp1ga4Uof03n.XcqneWMnX1S7Gt/j.RXIPD0eAdb5oUncDnEy', 'wiegand.ayden', 'avatarDefault.jpg', 'bannerDefault.png', 'Ally', 'Renner', '3306 Leda Orchard\nParkerburgh, IN 22419', NULL, '32734', 'New Michele', '2023-10-05 15:12:14', NULL, NULL, NULL, NULL, 1),
(395, 'ward.pauline@example.net', '[]', '$2y$13$lXYm1m1gvfH1wxVqb4c9C.gun/LpREwYZ7voqewJsxU8cD8jYvXJy', 'kertzmann.mac', 'avatarDefault.jpg', 'bannerDefault.png', 'Maud', 'Wisoky', '712 Rogahn Fork Apt. 274\nLake Reyhaven, WV 30849', 'Suite 577', '60050', 'New Carlos', '2023-10-05 15:12:15', NULL, NULL, NULL, NULL, 0),
(396, 'semard@example.net', '[]', '$2y$13$7xJG5VT3F3/ZTUrJ2ilqyuHa3DkWN1XBGd80c3exBOAteLdCDVws6', 'brenna.considine', 'avatarDefault.jpg', 'bannerDefault.png', 'Della', 'Senger', '849 Oliver Orchard\nPort Stephanie, AZ 37879-0542', NULL, '39355', 'Huelmouth', '2023-10-05 15:12:15', NULL, NULL, NULL, NULL, 1),
(398, 'test@test.com', '[]', '$2y$13$/Pzc9uMHGCdZ/7xdc1kB8e3uFhKiK5nIFJoFxJLMnN9e7A9v5dqtm', 'test', 'quadJerome-651ac7b575455-652d686d30d60.bmp', 'bannerDefault.png', 'laura', 'test', NULL, NULL, NULL, NULL, '2023-10-09 13:50:06', 40, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `video`
--

DROP TABLE IF EXISTS `video`;
CREATE TABLE IF NOT EXISTS `video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `views` int(11) NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `is_uploaded` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7CC7DA2CA76ED395` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `video`
--

INSERT INTO `video` (`id`, `title`, `duration`, `created_at`, `views`, `source`, `thumbnail`, `user_id`, `active`, `is_uploaded`) VALUES
(9, NULL, '00:00', '2023-10-16 20:32:03', 0, 'https://www.youtube.com/embed/Ojs5cERnQqg', 'blogDefault.png', 398, 1, 0);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admin_response_contact`
--
ALTER TABLE `admin_response_contact`
  ADD CONSTRAINT `FK_D98A0771E7A1254A` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`id`);

--
-- Contraintes pour la table `alert`
--
ALTER TABLE `alert`
  ADD CONSTRAINT `FK_17FD46C17294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `alert_comment`
--
ALTER TABLE `alert_comment`
  ADD CONSTRAINT `FK_A27F5AEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_A27F5AEF8697D13` FOREIGN KEY (`comment_id`) REFERENCES `comment` (`id`);

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `FK_23A0E6612469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  ADD CONSTRAINT `FK_23A0E6629C1004E` FOREIGN KEY (`video_id`) REFERENCES `video` (`id`),
  ADD CONSTRAINT `FK_23A0E66F675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `FK_BA388B74584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `FK_BA388B78E6C7DE4` FOREIGN KEY (`ordering_id`) REFERENCES `order` (`id`),
  ADD CONSTRAINT `FK_BA388B7A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `FK_9474526C7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_9474526CF675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `counter_user`
--
ALTER TABLE `counter_user`
  ADD CONSTRAINT `FK_C21E0F2EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C21E0F2EFCEEF2E3` FOREIGN KEY (`counter_id`) REFERENCES `counter` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `FK_C53D045F7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `FK_49CA4E7D7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_49CA4E7DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `FK_F52993982F924C2F` FOREIGN KEY (`delivery_status_id`) REFERENCES `order_status` (`id`),
  ADD CONSTRAINT `FK_F52993984F335C8B` FOREIGN KEY (`transporter_id`) REFERENCES `transporter` (`id`),
  ADD CONSTRAINT `FK_F5299398A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `FK_D044D5D42DA2B20` FOREIGN KEY (`map_spot_id`) REFERENCES `map_spot` (`id`);

--
-- Contraintes pour la table `session_user`
--
ALTER TABLE `session_user`
  ADD CONSTRAINT `FK_4BE2D663613FECDF` FOREIGN KEY (`session_id`) REFERENCES `session` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_4BE2D663A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D6492CDF9A` FOREIGN KEY (`drone_id`) REFERENCES `drone` (`id`);

--
-- Contraintes pour la table `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `FK_7CC7DA2CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
