-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 10 déc. 2024 à 15:32
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pokemonmovies`
--

-- --------------------------------------------------------

--
-- Structure de la table `movies`
--

DROP TABLE IF EXISTS `movies`;
CREATE TABLE IF NOT EXISTS `movies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `release_date` date NOT NULL,
  `duration` int NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `image_url` varchar(2083) DEFAULT NULL,
  `description` text,
  `desc_film` text,
  `creator` varchar(50) NOT NULL DEFAULT 'admin',
  `movie_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `movies`
--

INSERT INTO `movies` (`id`, `title`, `release_date`, `duration`, `rating`, `image_url`, `description`, `desc_film`, `creator`, `movie_file`) VALUES
(1, 'Pokémon: The First Movie', '1998-07-18', 75, 10.0, 'asset/img/Pokemon_the_first_movie.webp', NULL, '0', 'admin', 'asset/movies/67585a779ad0a.mp4'),
(2, 'Pokémon: The Movie 2000', '1999-07-17', 82, 6.1, 'asset/img/6752c779100dc.jpg', NULL, '0', 'admin', 'asset/movies/67585cb2d9a27.mp4'),
(3, 'Pokémon 3: The Movie', '2000-07-08', 93, 5.9, 'asset/img/pokemon_3_the_movie.jpg', NULL, '0', 'admin', 'asset/movies/67585d2cf0459.mp4'),
(4, 'Pokémon 4Ever', '2001-07-07', 80, 5.4, 'asset/img/6752c79202a01.jpg', NULL, '0', 'admin', 'asset/movies/67585cbaa8181.mp4'),
(5, 'Pokémon Heroes', '2002-07-13', 71, 6.1, 'asset/img/pokemon_heroes.webp', NULL, '0', 'admin', 'asset/movies/67585cc2804ce.mp4'),
(6, 'Pokémon: Jirachi—Wish Maker', '2003-07-19', 81, 6.2, 'asset/img/pokemon_jirachi_wish_maker.jpg', NULL, '0', 'admin', 'asset/movies/67585ce094a27.mp4'),
(7, 'Pokémon: Destiny Deoxys', '2004-07-17', 98, 6.0, 'asset/img/pokemon_deoxis_destiny.jpg', NULL, '0', 'admin', 'asset/movies/67585cee6e1c6.mp4'),
(8, 'Pokémon: Lucario and the Mystery of Mew', '2005-07-16', 103, 6.9, 'asset/img/pokemon_lucario_et_le_mister_de_mew.jpg', NULL, '0', 'admin', 'asset/movies/67585cf7001c7.mp4'),
(9, 'Pokémon Ranger and the Temple of the Sea', '2006-07-15', 107, 6.3, 'asset/img/pokemon_ranger_et_le_temple_de_la_mers.jpg', NULL, '0', 'admin', 'asset/movies/67585cffa1da1.mp4'),
(10, 'Pokémon: The Rise of Darkrai', '2007-07-14', 90, 6.4, 'asset/img/pokémon_the-rise-of-darkrai.jpg', NULL, '0', 'admin', 'asset/movies/67585d07cb7e5.mp4');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'admin'),
(2, 'user', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'user'),
(3, 'user1', '0b14d501a594442a01c6859541bcb3e8164d183d32937b851835442f69d5c94e', 'user'),
(4, 'user2', '6cf615d5bcaac778352a8f1f3360d23f02f34ec182e259897fd6ce485d7870d4', 'user'),
(5, 'user3', '5906ac361a137e2d286465cd6588ebb5ac3f5ae955001100bc41577c3d751764', 'user'),
(6, 'cepoko', 'e29f56802201a8417bf87dc292a652c8eed8d5b5274f265e68d1c56498a224d7', 'user'),
(7, 'test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 'user');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
