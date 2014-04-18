<?php
#########################################################################
#                            admin_maj.php                              #
#                                                                       #
#            interface permettant la mise à jour de la base de données  #
#               Dernière modification : 20/03/2008                      #
#                                                                       #
#                                                                       #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau
 *
 * This file is part of GRR.
 *
 * GRR is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GRR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GRR; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
$grr_script_name = "admin_maj.php";

// Settings
require_once("./commun/include/settings.inc.php");
//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");

// Session related functions
require_once("./commun/include/session.inc.php");

// Paramètres langage
include "./commun/include/language.inc.php";

//version 02/04/2014
$sql[] ="ALTER TABLE `agt_loc` ADD `plage_pos` VARCHAR( 10 ) NULL ";

//version 08/04/2014
$sql[] ="ALTER TABLE `agt_prog` CHANGE `start_time_prog` `start_time` INT( 11 ) NOT NULL ";
$sql[] ="ALTER TABLE `agt_prog` CHANGE `end_time_prog` `end_time` INT( 11 ) NOT NULL ";
$sql[] ="ALTER TABLE `agt_prog` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `end_time` ";
$sql[] ="ALTER TABLE `agt_prog` ADD `type` VARCHAR( 2 ) NOT NULL DEFAULT 'A'";
$sql[] ="ALTER TABLE `agt_prog` ADD `description` text";
$sql[] ="ALTER TABLE `agt_prog` ADD `statut_entry` varchar(25) NOT NULL DEFAULT '-'";
$sql[] ="ALTER TABLE `agt_prog` ADD `motif` varchar(100) DEFAULT NULL";
$sql[] ="ALTER TABLE `agt_prog` ADD `statut_consult` varchar(25) DEFAULT NULL";
$sql[] ="ALTER TABLE `agt_prog` CHANGE `room_id` `room_id` INT( 4 ) NULL ";

//intranetmsi
ALTER TABLE `forms` CHANGE `idref` `idref` VARCHAR( 11 ) NOT NULL 
ALTER TABLE `forms` CHANGE `nda` `idref` VARCHAR( 9 ) NOT NULL 
ALTER TABLE `forms` CHANGE `type` `type` VARCHAR( 25 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL 

?>
</body>
</html>
