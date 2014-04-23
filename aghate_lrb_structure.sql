-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mar 08 Avril 2014 à 15:28
-- Version du serveur: 5.6.12-log
-- Version de PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `aghate_lrb`
--
CREATE DATABASE IF NOT EXISTS `aghate_lrb` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `aghate_lrb`;

-- --------------------------------------------------------

--
-- Structure de la table `agt_calendar`
--

CREATE TABLE IF NOT EXISTS `agt_calendar` (
  `DAY` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_calendrier_jours_cycle`
--

CREATE TABLE IF NOT EXISTS `agt_calendrier_jours_cycle` (
  `DAY` int(11) NOT NULL DEFAULT '0',
  `Jours` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_config`
--

CREATE TABLE IF NOT EXISTS `agt_config` (
  `NAME` varchar(32) NOT NULL DEFAULT '',
  `VALUE` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_entry_moderate`
--

CREATE TABLE IF NOT EXISTS `agt_entry_moderate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login_moderateur` varchar(40) NOT NULL DEFAULT '',
  `motivation_moderation` text NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `entry_type` int(11) NOT NULL DEFAULT '0',
  `repeat_id` int(11) NOT NULL DEFAULT '0',
  `room_id` int(11) NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_by` varchar(25) NOT NULL DEFAULT '',
  `beneficiaire_ext` varchar(200) NOT NULL DEFAULT '',
  `beneficiaire` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(80) NOT NULL DEFAULT '',
  `type` char(2) DEFAULT NULL,
  `description` text,
  `statut_entry` char(1) NOT NULL DEFAULT '-',
  `option_reservation` int(11) NOT NULL DEFAULT '0',
  `overload_desc` text,
  `moderate` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idxStartTime` (`start_time`),
  KEY `idxEndTime` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_exam_compl`
--

CREATE TABLE IF NOT EXISTS `agt_exam_compl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_prog` int(11) DEFAULT NULL,
  `noip` varchar(10) DEFAULT NULL,
  `nda` varchar(9) DEFAULT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `room_id` int(11) NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_by` varchar(25) NOT NULL DEFAULT '',
  `name` varchar(80) NOT NULL DEFAULT '',
  `type` char(2) NOT NULL DEFAULT 'A',
  `protocole` text,
  `description` text,
  `statut_entry` varchar(25) NOT NULL DEFAULT '-',
  `medecin` varchar(30) DEFAULT NULL,
  `uh` varchar(3) NOT NULL DEFAULT ' ',
  `gilda_id` varchar(12) DEFAULT NULL,
  `de_source` varchar(25) NOT NULL,
  `ds_source` varchar(25) NOT NULL,
  `tydos` varchar(1) NOT NULL DEFAULT 'A',
  `plage_pos` varchar(25) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idxStartTime` (`start_time`),
  KEY `idxEndTime` (`end_time`),
  KEY `noip` (`noip`),
  KEY `room_id` (`room_id`),
  KEY `nda` (`nda`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2503 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_j_mailuser_room`
--

CREATE TABLE IF NOT EXISTS `agt_j_mailuser_room` (
  `login` varchar(40) NOT NULL DEFAULT '',
  `id_room` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`login`,`id_room`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_j_type_area`
--

CREATE TABLE IF NOT EXISTS `agt_j_type_area` (
  `id_type` int(11) NOT NULL DEFAULT '0',
  `id_area` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_j_useradmin_area`
--

CREATE TABLE IF NOT EXISTS `agt_j_useradmin_area` (
  `login` varchar(40) NOT NULL DEFAULT '',
  `id_area` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`login`,`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_j_user_area`
--

CREATE TABLE IF NOT EXISTS `agt_j_user_area` (
  `login` varchar(40) NOT NULL DEFAULT '',
  `id_area` int(11) NOT NULL DEFAULT '0',
  `droit` varchar(1) NOT NULL,
  PRIMARY KEY (`login`,`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_j_user_room`
--

CREATE TABLE IF NOT EXISTS `agt_j_user_room` (
  `login` varchar(40) NOT NULL DEFAULT '',
  `id_room` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`login`,`id_room`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_loc`
--

CREATE TABLE IF NOT EXISTS `agt_loc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_prog` int(11) DEFAULT NULL,
  `noip` varchar(10) DEFAULT NULL,
  `nda` varchar(9) DEFAULT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `room_id` int(11) NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_by` varchar(25) NOT NULL DEFAULT '',
  `name` varchar(80) NOT NULL DEFAULT '',
  `type` char(2) NOT NULL DEFAULT 'A',
  `protocole` text,
  `description` text,
  `statut_entry` varchar(25) NOT NULL DEFAULT '-',
  `medecin` varchar(30) DEFAULT NULL,
  `uh` varchar(3) NOT NULL DEFAULT ' ',
  `gilda_id` varchar(12) DEFAULT NULL,
  `de_source` varchar(25) NOT NULL,
  `ds_source` varchar(25) NOT NULL,
  `tydos` varchar(1) NOT NULL DEFAULT 'A',
  `plage_pos` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idxStartTime` (`start_time`),
  KEY `idxEndTime` (`end_time`),
  KEY `noip` (`noip`),
  KEY `room_id` (`room_id`),
  KEY `nda` (`nda`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2856 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_loc_parametres`
--

CREATE TABLE IF NOT EXISTS `agt_loc_parametres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `medecin_id` int(9) NOT NULL,
  `details` text,
  `service_fermee` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_log`
--

CREATE TABLE IF NOT EXISTS `agt_log` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `LOGIN` varchar(40) NOT NULL DEFAULT '',
  `START` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `SESSION_ID` varchar(64) NOT NULL DEFAULT '',
  `REMOTE_ADDR` varchar(16) NOT NULL DEFAULT '',
  `USER_AGENT` varchar(255) NOT NULL DEFAULT '',
  `REFERER` varchar(255) NOT NULL DEFAULT '',
  `AUTOCLOSE` enum('0','1') NOT NULL DEFAULT '0',
  `END` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=821 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_medecin`
--

CREATE TABLE IF NOT EXISTS `agt_medecin` (
  `id_medecin` int(10) NOT NULL AUTO_INCREMENT,
  `titre` varchar(10) NOT NULL,
  `nom` varchar(30) NOT NULL,
  `prenom` varchar(30) NOT NULL,
  `tel` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `service` varchar(10) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  PRIMARY KEY (`id_medecin`),
  KEY `nom` (`nom`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1211 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_overload`
--

CREATE TABLE IF NOT EXISTS `agt_overload` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_area` int(11) NOT NULL,
  `fieldname` varchar(25) NOT NULL DEFAULT '',
  `fieldtype` varchar(25) NOT NULL DEFAULT '',
  `fieldlist` text NOT NULL,
  `obligatoire` char(1) NOT NULL DEFAULT 'n',
  `affichage` char(1) NOT NULL DEFAULT 'n',
  `confidentiel` char(1) NOT NULL DEFAULT 'n',
  `overload_mail` char(1) NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_overload_data`
--

CREATE TABLE IF NOT EXISTS `agt_overload_data` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `entry_id` int(8) NOT NULL,
  `field_name` varchar(25) NOT NULL,
  `field_data` longblob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_pat`
--

CREATE TABLE IF NOT EXISTS `agt_pat` (
  `id_pat` int(8) NOT NULL AUTO_INCREMENT,
  `noip` varchar(10) NOT NULL DEFAULT '',
  `nom` varchar(30) NOT NULL DEFAULT '',
  `prenom` varchar(30) NOT NULL DEFAULT '',
  `nomjf` varchar(30) NOT NULL DEFAULT '',
  `ddn` date NOT NULL DEFAULT '0000-00-00',
  `sex` char(1) NOT NULL DEFAULT '',
  `adresse` varchar(50) NOT NULL DEFAULT '',
  `ville` varchar(50) NOT NULL DEFAULT '',
  `codepostal` varchar(7) NOT NULL DEFAULT '',
  `age` int(3) NOT NULL DEFAULT '0',
  `tel` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id_pat`),
  KEY `noip` (`noip`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6137 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_prog`
--

CREATE TABLE IF NOT EXISTS `agt_prog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noip` varchar(10) NOT NULL,
  `nda` varchar(9) DEFAULT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `medecin` varchar(25) NOT NULL,
  `protocole` varchar(25) NOT NULL,
  `room_id` int(4) NOT NULL,
  `create_by` varchar(25) NOT NULL,
  `service_id` int(3) NOT NULL,
  `type` char(2) NOT NULL DEFAULT 'A',
  `description` text,
  `statut_entry` varchar(25) NOT NULL DEFAULT '-',
  `motif` varchar(100) DEFAULT NULL,
  `statut_consult` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_protocole`
--

CREATE TABLE IF NOT EXISTS `agt_protocole` (
  `id_protocole` int(10) NOT NULL AUTO_INCREMENT,
  `protocole` varchar(75) NOT NULL,
  `desc_detail` varchar(50) DEFAULT ' ',
  `service` varchar(10) NOT NULL,
  `duree` int(4) NOT NULL,
  `date_deb` date DEFAULT '2000-01-01',
  `date_fin` date DEFAULT '2000-01-01',
  PRIMARY KEY (`id_protocole`),
  KEY `protocole` (`protocole`),
  KEY `service` (`service`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=59 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_repeat`
--

CREATE TABLE IF NOT EXISTS `agt_repeat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `rep_type` int(11) NOT NULL DEFAULT '0',
  `end_date` int(11) NOT NULL DEFAULT '0',
  `rep_opt` varchar(32) NOT NULL DEFAULT '',
  `room_id` int(11) NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_by` varchar(25) NOT NULL DEFAULT '',
  `beneficiaire_ext` varchar(200) NOT NULL DEFAULT '',
  `beneficiaire` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(80) NOT NULL DEFAULT '',
  `type` char(2) NOT NULL DEFAULT 'A',
  `description` text,
  `rep_num_weeks` tinyint(4) DEFAULT '0',
  `overload_desc` text,
  `jours` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_room`
--

CREATE TABLE IF NOT EXISTS `agt_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL DEFAULT '0',
  `room_name` varchar(60) NOT NULL DEFAULT '',
  `room_alias` varchar(100) DEFAULT NULL,
  `description` varchar(60) NOT NULL DEFAULT '',
  `capacity` int(11) NOT NULL DEFAULT '0',
  `max_booking` smallint(6) NOT NULL DEFAULT '-1',
  `statut_room` char(1) NOT NULL DEFAULT '1',
  `show_fic_room` char(1) NOT NULL DEFAULT 'n',
  `picture_room` varchar(50) NOT NULL DEFAULT '',
  `comment_room` text,
  `delais_max_resa_room` smallint(6) NOT NULL DEFAULT '-1',
  `delais_min_resa_room` smallint(6) NOT NULL DEFAULT '0',
  `allow_action_in_past` char(1) NOT NULL DEFAULT 'n',
  `dont_allow_modify` char(1) NOT NULL DEFAULT 'n',
  `order_display` smallint(6) NOT NULL DEFAULT '0',
  `delais_option_reservation` smallint(6) NOT NULL DEFAULT '0',
  `type_affichage_reser` smallint(6) NOT NULL DEFAULT '0',
  `moderate` tinyint(1) DEFAULT '0',
  `qui_peut_reserver_pour` char(1) NOT NULL DEFAULT '5',
  `active_ressource_empruntee` char(1) NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2649 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_room_idp`
--

CREATE TABLE IF NOT EXISTS `agt_room_idp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `start_time_idp` varchar(25) NOT NULL,
  `end_time_idp` varchar(25) NOT NULL,
  `motif` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_service`
--

CREATE TABLE IF NOT EXISTS `agt_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affichage` varchar(25) DEFAULT NULL,
  `service_name` varchar(30) NOT NULL,
  `noposte` varchar(3) DEFAULT NULL,
  `grp_service` varchar(35) DEFAULT NULL,
  `grp_localisation` varchar(35) DEFAULT NULL,
  `pole` varchar(35) DEFAULT NULL,
  `access` char(1) NOT NULL DEFAULT '',
  `order_display` smallint(6) NOT NULL DEFAULT '0',
  `ip_adr` varchar(15) NOT NULL DEFAULT '',
  `morningstarts_area` smallint(6) NOT NULL DEFAULT '0',
  `eveningends_area` smallint(6) NOT NULL DEFAULT '0',
  `duree_max_resa_area` int(11) NOT NULL DEFAULT '-1',
  `resolution_area` int(11) NOT NULL DEFAULT '0',
  `eveningends_minutes_area` smallint(6) NOT NULL DEFAULT '0',
  `weekstarts_area` smallint(6) NOT NULL DEFAULT '0',
  `twentyfourhour_format_area` smallint(6) NOT NULL DEFAULT '0',
  `calendar_default_values` char(1) NOT NULL DEFAULT 'y',
  `enable_periods` char(1) NOT NULL DEFAULT 'n',
  `display_days` varchar(7) NOT NULL DEFAULT 'yyyyyyy',
  `id_type_par_defaut` int(11) NOT NULL DEFAULT '-1',
  `duree_par_defaut_reservation_area` int(11) NOT NULL DEFAULT '0',
  `mail_msi` varchar(250) DEFAULT NULL,
  `urm` varchar(5) DEFAULT NULL,
  `uh` varchar(255) NOT NULL,
  `service_type` varchar(10) NOT NULL DEFAULT 'HC',
  `duree_previsionnel` float DEFAULT NULL,
  `disponibilite` int(1) DEFAULT NULL,
  `etat` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_service_periodes`
--

CREATE TABLE IF NOT EXISTS `agt_service_periodes` (
  `service_id` int(11) NOT NULL DEFAULT '0',
  `num_periode` smallint(6) NOT NULL DEFAULT '0',
  `nom_periode` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `agt_type_area`
--

CREATE TABLE IF NOT EXISTS `agt_type_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(30) NOT NULL DEFAULT '',
  `order_display` smallint(6) NOT NULL DEFAULT '0',
  `couleur` smallint(6) NOT NULL DEFAULT '0',
  `type_letter` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_user_consult`
--

CREATE TABLE IF NOT EXISTS `agt_user_consult` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_prog` int(11) NOT NULL,
  `login` varchar(40) NOT NULL,
  `consult` varchar(40) NOT NULL,
  `type` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_prog` (`id_prog`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `agt_utilisateurs`
--

CREATE TABLE IF NOT EXISTS `agt_utilisateurs` (
  `login` varchar(40) NOT NULL DEFAULT '',
  `nom` varchar(30) NOT NULL DEFAULT '',
  `prenom` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `statut` varchar(30) NOT NULL DEFAULT '',
  `etat` varchar(20) NOT NULL DEFAULT '',
  `default_area` smallint(6) NOT NULL DEFAULT '0',
  `default_room` smallint(6) NOT NULL DEFAULT '0',
  `default_style` varchar(50) NOT NULL DEFAULT '',
  `default_list_type` varchar(50) NOT NULL DEFAULT '',
  `default_language` char(3) NOT NULL DEFAULT '',
  `source` varchar(10) NOT NULL DEFAULT 'local',
  `medecin` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `loc_backup`
--

CREATE TABLE IF NOT EXISTS `loc_backup` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NOIP` varchar(10) DEFAULT NULL,
  `NMMAL` varchar(35) DEFAULT NULL,
  `NMPMAL` varchar(35) DEFAULT NULL,
  `DANAIS` date NOT NULL DEFAULT '0000-00-00',
  `NOTLDO` varchar(30) DEFAULT NULL,
  `NOLIT` varchar(10) DEFAULT NULL,
  `NOCHAM` varchar(10) DEFAULT NULL,
  `NOPOST` varchar(10) DEFAULT NULL,
  `NOSERV` varchar(10) DEFAULT NULL,
  `DDLOPT` date NOT NULL DEFAULT '0000-00-00',
  `CDSEXM` varchar(2) DEFAULT NULL,
  `HHLOPT` varchar(5) DEFAULT NULL,
  `NDA` varchar(10) DEFAULT NULL,
  `DTENT` date NOT NULL DEFAULT '0000-00-00',
  `HHENT` varchar(5) DEFAULT NULL,
  `UH` varchar(3) DEFAULT NULL,
  `TYSEJ` varchar(3) DEFAULT NULL,
  `TYMVT` varchar(2) DEFAULT NULL,
  `NOIDMV` varchar(15) DEFAULT NULL,
  `DATE_MAJ` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=816 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
