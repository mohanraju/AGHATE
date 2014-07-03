<?php
/*
	mise a jour les version 
	Transfert les base sur les nouvelles structures
	
	version 	22/01/2014
	les tables stuctures sont a modiféé
	tous les tables commencent par "AGT"
	Etape 1 : creation des les nouvelles table s'il n'exite pas
	Etape 2 : copie les données d'ancinnes vers les nouvelles
	Etpte 3 : A faire manuel : suprimmer les tables commencent avec nom "grr"

*/
ini_set('dispaly_errors',1); 
include("./config/config.php");
include("./commun/include/ClassMysql.php");
$db = new MySQL();
/*
==============================================================================================

definition des nouvelle structure
	
==============================================================================================
*/
$V2_220120014['agt_calendar']="CREATE TABLE IF NOT EXISTS `agt_calendar` (
  														`DAY` int(11) NOT NULL default '0'
															) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_calendrier_jours_cycle']="
															CREATE TABLE IF NOT EXISTS `agt_calendrier_jours_cycle` (
															  `DAY` int(11) NOT NULL default '0',
															  `Jours` varchar(20) NOT NULL default ''
															) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_config']="
													CREATE TABLE IF NOT EXISTS `agt_config` (
													  `NAME` varchar(32) NOT NULL default '',
													  `VALUE` text character set latin1 collate latin1_general_cs NOT NULL,
													  PRIMARY KEY  (`NAME`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_entry_moderate']="
													CREATE TABLE IF NOT EXISTS `agt_entry_moderate` (
													  `id` int(11) NOT NULL auto_increment,
													  `login_moderateur` varchar(40) NOT NULL default '',
													  `motivation_moderation` text NOT NULL,
													  `start_time` int(11) NOT NULL default '0',
													  `end_time` int(11) NOT NULL default '0',
													  `entry_type` int(11) NOT NULL default '0',
													  `repeat_id` int(11) NOT NULL default '0',
													  `room_id` int(11) NOT NULL default '1',
													  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
													  `create_by` varchar(25) NOT NULL default '',
													  `beneficiaire_ext` varchar(200) NOT NULL default '',
													  `beneficiaire` varchar(100) NOT NULL default '',
													  `name` varchar(80) NOT NULL default '',
													  `type` char(2) default NULL,
													  `description` text,
													  `statut_entry` char(1) NOT NULL default '-',
													  `option_reservation` int(11) NOT NULL default '0',
													  `overload_desc` text,
													  `moderate` tinyint(1) default '0',
													  PRIMARY KEY  (`id`),
													  KEY `idxStartTime` (`start_time`),
													  KEY `idxEndTime` (`end_time`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_j_mailuser_room']="
													CREATE TABLE IF NOT EXISTS `agt_j_mailuser_room` (
													  `login` varchar(40) NOT NULL default '',
													  `id_room` int(11) NOT NULL default '0',
													  PRIMARY KEY  (`login`,`id_room`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_j_type_area']="
													CREATE TABLE IF NOT EXISTS `agt_j_type_area` (
													  `id_type` int(11) NOT NULL default '0',
													  `id_area` int(11) NOT NULL default '0'
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_j_useradmin_area']="
													CREATE TABLE IF NOT EXISTS `agt_j_useradmin_area` (
													  `login` varchar(40) NOT NULL default '',
													  `id_area` int(11) NOT NULL default '0',
													  PRIMARY KEY  (`login`,`id_area`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_j_user_area']="
													CREATE TABLE IF NOT EXISTS `agt_j_user_area` (
													  `login` varchar(40) NOT NULL default '',
													  `id_area` int(11) NOT NULL default '0',
													  PRIMARY KEY  (`login`,`id_area`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_j_user_room']="
													CREATE TABLE IF NOT EXISTS `agt_j_user_room` (
													  `login` varchar(40) NOT NULL default '',
													  `id_room` int(11) NOT NULL default '0',
													  PRIMARY KEY  (`login`,`id_room`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$V2_220120014['agt_loc']="
													CREATE TABLE IF NOT EXISTS `agt_loc` (
													  `id` int(11) NOT NULL auto_increment,
													  `id_prog` int(11) default NULL,
													  `noip` varchar(10) default NULL,
													  `nda` varchar(9) default NULL,
													  `start_time` int(11) NOT NULL default '0',
													  `end_time` int(11) NOT NULL default '0',
													  `room_id` int(11) NOT NULL default '1',
													  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
													  `create_by` varchar(25) NOT NULL default '',
													  `name` varchar(80) NOT NULL default '',
													  `type` char(2) NOT NULL default 'A',
													  `protocole` text,
													  `description` text,
													  `statut_entry` varchar(25) NOT NULL default '-',
													  `medecin` varchar(30) default NULL,
													  `uh` varchar(3) NOT NULL default ' ',
													  `gilda_id` varchar(12) default NULL,
													  `de_source` varchar(25) NOT NULL,
													  `ds_source` varchar(25) NOT NULL,
													  `tydos` varchar(1) NOT NULL default 'A',
													  PRIMARY KEY  (`id`),
													  KEY `idxStartTime` (`start_time`),
													  KEY `idxEndTime` (`end_time`),
													  KEY `noip` (`noip`),
													  KEY `room_id` (`room_id`),
													  KEY `nda` (`nda`)
													) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_loc_parametres']="
													CREATE TABLE IF NOT EXISTS `agt_loc_parametres` (
													  `id` int(11) NOT NULL auto_increment,
													  `service_id` int(11) NOT NULL,
													  `date` date default NULL,
													  `medecin_id` int(9) NOT NULL,
													  `details` text,
													  `service_fermee` int(1) NOT NULL default '0',
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_log']="
													CREATE TABLE IF NOT EXISTS `agt_log` (
													  `id` int(9) NOT NULL auto_increment,
													  `LOGIN` varchar(40) NOT NULL default '',
													  `START` datetime NOT NULL default '0000-00-00 00:00:00',
													  `SESSION_ID` varchar(64) NOT NULL default '',
													  `REMOTE_ADDR` varchar(16) NOT NULL default '',
													  `USER_AGENT` varchar(255) NOT NULL default '',
													  `REFERER` varchar(255) NOT NULL default '',
													  `AUTOCLOSE` enum('0','1') NOT NULL default '0',
													  `END` datetime NOT NULL default '0000-00-00 00:00:00',
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_medecin']="
													CREATE TABLE IF NOT EXISTS `agt_medecin` (
													  `id_medecin` int(10) NOT NULL auto_increment,
													  `titre` varchar(30)  NULL,
													  `nom` varchar(30) NOT NULL,
													  `prenom` varchar(30)  NULL,
													  `tel` varchar(30)  NULL,
													  `email` varchar(50)  NULL,
													  `service` varchar(10) default NULL,
													  `area_id` int(11)  NULL,
													  PRIMARY KEY  (`id_medecin`),
													  KEY `nom` (`nom`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_overload']="
													CREATE TABLE IF NOT EXISTS `agt_overload` (
													  `id` int(11) NOT NULL auto_increment,
													  `id_area` int(11) NOT NULL,
													  `fieldname` varchar(25) NOT NULL default '',
													  `fieldtype` varchar(25) NOT NULL default '',
													  `fieldlist` text NOT NULL,
													  `obligatoire` char(1) NOT NULL default 'n',
													  `affichage` char(1) NOT NULL default 'n',
													  `confidentiel` char(1) NOT NULL default 'n',
													  `overload_mail` char(1) NOT NULL default 'n',
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_overload_data']="
													CREATE TABLE IF NOT EXISTS `agt_overload_data` (
													  `id` int(8) NOT NULL auto_increment,
													  `entry_id` int(8) NOT NULL,
													  `field_name` varchar(25) NOT NULL,
													  `field_data` longblob NOT NULL,
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_pat']="
													CREATE TABLE IF NOT EXISTS `agt_pat` (
													  `id_pat` int(8) NOT NULL auto_increment,
													  `noip` varchar(10) NOT NULL default '',
													  `nom` varchar(30) NOT NULL default '',
													  `prenom` varchar(30) NOT NULL default '',
													  `nomjf` varchar(30) NOT NULL default '',
													  `ddn` date NOT NULL default '0000-00-00',
													  `sex` char(1) NOT NULL default '',
													  `adresse` varchar(50) NOT NULL default '',
													  `ville` varchar(50) NOT NULL default '',
													  `codepostal` varchar(7) NOT NULL default '',
													  `age` int(3) NOT NULL default '0',
													  `tel` varchar(25) default NULL,
													  PRIMARY KEY  (`id_pat`),
													  KEY `noip` (`noip`)
													) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_prog']="
													CREATE TABLE IF NOT EXISTS `agt_prog` (
													  `id` int(11) NOT NULL auto_increment,
													  `noip` varchar(10) NOT NULL,
													  `start_time_prog` int(11) NOT NULL,
													  `end_time_prog` int(11) NOT NULL,
													  `medecin` varchar(25) NOT NULL,
													  `protocole` varchar(25) NOT NULL,
													  `room_id` int(4) NOT NULL,
													  `create_by` varchar(25) NOT NULL,
													  `service_id` int(3) NOT NULL,
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_protocole']="
													CREATE TABLE IF NOT EXISTS `agt_protocole` (
													  `id_protocole` int(10) NOT NULL auto_increment,
													  `protocole` varchar(75) NOT NULL,
													  `desc_detail` varchar(50) default ' ',
													  `service` varchar(10) NOT NULL,
													  `duree` int(4) NOT NULL,
													  `date_deb` date default '2000-01-01',
													  `date_fin` date default '2000-01-01',
													  PRIMARY KEY  (`id_protocole`),
													  KEY `protocole` (`protocole`),
													  KEY `service` (`service`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_repeat']="
													CREATE TABLE IF NOT EXISTS `agt_repeat` (
													  `id` int(11) NOT NULL auto_increment,
													  `start_time` int(11) NOT NULL default '0',
													  `end_time` int(11) NOT NULL default '0',
													  `rep_type` int(11) NOT NULL default '0',
													  `end_date` int(11) NOT NULL default '0',
													  `rep_opt` varchar(32) NOT NULL default '',
													  `room_id` int(11) NOT NULL default '1',
													  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
													  `create_by` varchar(25) NOT NULL default '',
													  `beneficiaire_ext` varchar(200) NOT NULL default '',
													  `beneficiaire` varchar(100) NOT NULL default '',
													  `name` varchar(80) NOT NULL default '',
													  `type` char(2) NOT NULL default 'A',
													  `description` text,
													  `rep_num_weeks` tinyint(4) default '0',
													  `overload_desc` text,
													  `jours` tinyint(2) NOT NULL default '0',
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_room']="
														CREATE TABLE IF NOT EXISTS `agt_room` (
														  `id` int(11) NOT NULL auto_increment,
														  `service_id` int(11) NOT NULL default '0',
														  `room_name` varchar(60) NOT NULL default '',
														  `room_alias` varchar(100)  NULL,
														  `description` varchar(60) NOT NULL default '',
														  `capacity` int(11) NOT NULL default '0',
														  `max_booking` smallint(6) NOT NULL default '-1',
														  `statut_room` char(1) NOT NULL default '1',
														  `show_fic_room` char(1) NOT NULL default 'n',
														  `picture_room` varchar(50) NOT NULL default '',
														  `comment_room` text,
														  `delais_max_resa_room` smallint(6) NOT NULL default '-1',
														  `delais_min_resa_room` smallint(6) NOT NULL default '0',
														  `allow_action_in_past` char(1) NOT NULL default 'n',
														  `dont_allow_modify` char(1) NOT NULL default 'n',
														  `order_display` smallint(6) NOT NULL default '0',
														  `delais_option_reservation` smallint(6) NOT NULL default '0',
														  `type_affichage_reser` smallint(6) NOT NULL default '0',
														  `moderate` tinyint(1) default '0',
														  `qui_peut_reserver_pour` char(1) NOT NULL default '5',
														  `active_ressource_empruntee` char(1) NOT NULL default 'y',
														  PRIMARY KEY  (`id`),
														  KEY `service_id` (`service_id`)
														) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_room_idp']="
													CREATE TABLE IF NOT EXISTS `agt_room_idp` (
													  `id` int(11) NOT NULL auto_increment,
													  `room_id` int(11) NOT NULL,
													  `start_time_idp` varchar(25) NOT NULL,
													  `end_time_idp` varchar(25) NOT NULL,
													  `motif` varchar(250)  NULL,
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_service']="
													CREATE TABLE IF NOT EXISTS `agt_service` (
													  `id` int(11) NOT NULL auto_increment,
													  `affichage` varchar(25) default NULL,
													  `service_name` varchar(30) NOT NULL,
													  `noposte` varchar(3) default NULL,
													  `grp_service` varchar(35) default NULL,
													  `grp_localisation` varchar(35) default NULL,
													  `pole` varchar(35) default NULL,
													  `access` char(1) NOT NULL default '',
													  `order_display` smallint(6) NOT NULL default '0',
													  `ip_adr` varchar(15) NOT NULL default '',
													  `morningstarts_area` smallint(6) NOT NULL default '0',
													  `eveningends_area` smallint(6) NOT NULL default '0',
													  `duree_max_resa_area` int(11) NOT NULL default '-1',
													  `resolution_area` int(11) NOT NULL default '0',
													  `eveningends_minutes_area` smallint(6) NOT NULL default '0',
													  `weekstarts_area` smallint(6) NOT NULL default '0',
													  `twentyfourhour_format_area` smallint(6) NOT NULL default '0',
													  `calendar_default_values` char(1) NOT NULL default 'y',
													  `enable_periods` char(1) NOT NULL default 'n',
													  `display_days` varchar(7) NOT NULL default 'yyyyyyy',
													  `id_type_par_defaut` int(11) NOT NULL default '-1',
													  `duree_par_defaut_reservation_area` int(11) NOT NULL default '0',
													  `mail_msi` varchar(250) default NULL,
													  `urm` varchar(5) default NULL,
													  `uh` varchar(255) NOT NULL,
													  `duree_previsionnel` int(5) NULL ,
													  `disponibilite` int(1) default NULL,
													  `etat` varchar(1) default NULL,
													  PRIMARY KEY  (`id`)
													) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_type_area']="
														CREATE TABLE IF NOT EXISTS `agt_type_area` (
														  `id` int(11) NOT NULL auto_increment,
														  `type_name` varchar(30) NOT NULL default '',
														  `order_display` smallint(6) NOT NULL default '0',
														  `couleur` smallint(6) NOT NULL default '0',
														  `type_letter` char(2) NOT NULL default '',
														  PRIMARY KEY  (`id`)
														) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$V2_220120014['agt_utilisateurs']="
													CREATE TABLE IF NOT EXISTS `agt_utilisateurs` (
													  `login` varchar(40) NOT NULL default '',
													  `nom` varchar(30) NOT NULL default '',
													  `prenom` varchar(30) NOT NULL default '',
													  `password` varchar(32) NOT NULL default '',
													  `email` varchar(100) NOT NULL default '',
													  `statut` varchar(30) NOT NULL default '',
													  `etat` varchar(20) NOT NULL default '',
													  `default_area` smallint(6) NOT NULL default '0',
													  `default_room` smallint(6) NOT NULL default '0',
													  `default_style` varchar(50) NOT NULL default '',
													  `default_list_type` varchar(50) NOT NULL default '',
													  `default_language` char(3) NOT NULL default '',
													  `source` varchar(10) NOT NULL default 'local',
													  `medecin` int(1) NOT NULL default '0',
													  PRIMARY KEY  (`login`)
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

/*
==============================================================================================

	Definition des anciennes tablse LIST
	
==============================================================================================
*/
//$V1_Tables['agt_prog']                   ="agt_prog";
$V1_Tables['grr_area']                   ="grr_area";
$V1_Tables['grr_area_periodes']          ="grr_area_periodes";
$V1_Tables['grr_calendar']               ="grr_calendar";
$V1_Tables['grr_calendrier_jours_cycle'] ="grr_calendrier_jours_cycle";
$V1_Tables['grr_entry']                  ="grr_entry";
$V1_Tables['grr_entry_moderate']         ="grr_entry_moderate";
$V1_Tables['grr_entry_parametres']       ="grr_entry_parametres";
$V1_Tables['grr_j_mailuser_room']        ="grr_j_mailuser_room";
$V1_Tables['grr_j_type_area']            ="grr_j_type_area";
$V1_Tables['grr_j_useradmin_area']       ="grr_j_useradmin_area";
$V1_Tables['grr_j_user_area']            ="grr_j_user_area";
$V1_Tables['grr_j_user_room']            ="grr_j_user_room";
$V1_Tables['grr_log']                    ="grr_log";
$V1_Tables['grr_medecin']                ="grr_medecin";
$V1_Tables['grr_overload']               ="grr_overload";
$V1_Tables['grr_overload_data']          ="grr_overload_data";
$V1_Tables['grr_pat']                    ="grr_pat";
$V1_Tables['grr_protocole']              ="grr_protocole";
$V1_Tables['grr_repeat']                 ="grr_repeat";
$V1_Tables['grr_room']                   ="grr_room";
$V1_Tables['grr_room_idp']               ="grr_room_idp";
$V1_Tables['grr_setting']                ="grr_setting";
$V1_Tables['grr_type_area']              ="grr_type_area";
$V1_Tables['grr_utilisateurs']           ="grr_utilisateurs";

/*
==============================================================================================

	SQL EXPORTS
	Dans certains tables les collones sont changés dont on passe par propre SQL
==============================================================================================
*/

$sql_update[agt_config]="INSERT INTO agt_config (NAME,VALUE) SELECT NAME,VALUE FROM grr_setting";
$sql_update[agt_service]="INSERT INTO agt_service (id,service_name,access,order_display,ip_adr,morningstarts_area,eveningends_area,duree_max_resa_area,resolution_area,eveningends_minutes_area,weekstarts_area,twentyfourhour_format_area,calendar_default_values,enable_periods,display_days,id_type_par_defaut,duree_par_defaut_reservation_area,mail_msi,urm,uh,duree_previsionnel,disponibilite,etat) SELECT id,area_name,access,order_display,ip_adr,morningstarts_area,eveningends_area,duree_max_resa_area,resolution_area,eveningends_minutes_area,weekstarts_area,twentyfourhour_format_area,calendar_default_values,enable_periods,display_days,id_type_par_defaut,duree_par_defaut_reservation_area,mail_msi,urm,uh,duree_previsionnel,disponibilite,etat FROM grr_area";
$sql_update[agt_calendar]="INSERT INTO agt_calendar (DAY) SELECT DAY FROM grr_calendar";
$sql_update[agt_calendrier_jours_cycle]="INSERT INTO agt_calendrier_jours_cycle (DAY,Jours) SELECT DAY,Jours FROM grr_calendrier_jours_cycle";
$sql_update[agt_loc]="INSERT INTO agt_loc (id,id_prog,noip,nda,start_time,end_time,room_id,timestamp,create_by,name,type,protocole,description,statut_entry,medecin,uh,gilda_id,de_source,ds_source) SELECT id,id_prog,noip,nda,start_time,end_time,room_id,timestamp,create_by,name,type,protocole,description,statut_entry,medecin,uh,gilda_id,de_source,ds_source FROM grr_entry";
$sql_update[agt_entry_moderate]="INSERT INTO agt_entry_moderate (id,login_moderateur,motivation_moderation,start_time,end_time,entry_type,repeat_id,room_id,timestamp,create_by,beneficiaire_ext,beneficiaire,name,type,description,statut_entry,option_reservation,overload_desc,moderate) SELECT id,login_moderateur,motivation_moderation,start_time,end_time,entry_type,repeat_id,room_id,timestamp,create_by,beneficiaire_ext,beneficiaire,name,type,description,statut_entry,option_reservation,overload_desc,moderate FROM grr_entry_moderate";
$sql_update[agt_loc_parametres]="INSERT INTO agt_loc_parametres (id,service_id,date,medecin_id,details,service_fermee) SELECT id,area_id,date,medecin_id,details,service_fermee FROM grr_entry_parametres";
$sql_update[agt_j_mailuser_room]="INSERT INTO agt_j_mailuser_room (login,id_room) SELECT login,id_room FROM grr_j_mailuser_room";
$sql_update[agt_j_type_area]="INSERT INTO agt_j_type_area (id_type,id_area) SELECT id_type,id_area FROM grr_j_type_area";
$sql_update[agt_j_useradmin_area]="INSERT INTO agt_j_useradmin_area (login,id_area) SELECT login,id_area FROM grr_j_useradmin_area";
$sql_update[agt_j_user_area]="INSERT INTO agt_j_user_area (login,id_area) SELECT login,id_area FROM grr_j_user_area";
$sql_update[agt_j_user_room]="INSERT INTO agt_j_user_room (login,id_room) SELECT login,id_room FROM grr_j_user_room";
$sql_update[agt_log]="INSERT INTO agt_log (LOGIN,START,SESSION_ID,REMOTE_ADDR,USER_AGENT,REFERER,AUTOCLOSE,END) SELECT LOGIN,START,SESSION_ID,REMOTE_ADDR,USER_AGENT,REFERER,AUTOCLOSE,END FROM grr_log";
$sql_update[agt_medecin]="INSERT INTO agt_medecin (id_medecin,titre,nom,prenom,tel,email,service,area_id) SELECT id_medecin,titre,nom,prenom,tel,email,service,area_id FROM grr_medecin";
$sql_update[agt_overload]="INSERT INTO agt_overload (id,id_area,fieldname,fieldtype,fieldlist,obligatoire,affichage,confidentiel,overload_mail) SELECT id,id_area,fieldname,fieldtype,fieldlist,obligatoire,affichage,confidentiel,overload_mail FROM grr_overload";
$sql_update[agt_overload_data]="INSERT INTO agt_overload_data (id,entry_id,field_name,field_data) SELECT id,entry_id,field_name,field_data FROM grr_overload_data";
$sql_update[agt_pat]="INSERT INTO agt_pat (id_pat,noip,nom,prenom,nomjf,ddn,sex,adresse,ville,codepostal,age,tel) SELECT id_pat,noip,nom,prenom,nomjf,ddn,sex,adresse,ville,codepostal,age,tel FROM grr_pat";
$sql_update[agt_protocole]="INSERT INTO agt_protocole (id_protocole,protocole,desc_detail,service,duree,date_deb,date_fin) SELECT id_protocole,protocole,desc_detail,service,duree,date_deb,date_fin FROM grr_protocole";
$sql_update[agt_repeat]="INSERT INTO agt_repeat (id,start_time,end_time,rep_type,end_date,rep_opt,room_id,timestamp,create_by,beneficiaire_ext,beneficiaire,name,type,description,rep_num_weeks,overload_desc,jours) SELECT id,start_time,end_time,rep_type,end_date,rep_opt,room_id,timestamp,create_by,beneficiaire_ext,beneficiaire,name,type,description,rep_num_weeks,overload_desc,jours FROM grr_repeat";
$sql_update[agt_room]="INSERT INTO agt_room (id,service_id,room_name,description,capacity,max_booking,statut_room,show_fic_room,picture_room,comment_room,delais_max_resa_room,delais_min_resa_room,allow_action_in_past,dont_allow_modify,order_display,delais_option_reservation,type_affichage_reser,moderate,qui_peut_reserver_pour,active_ressource_empruntee) SELECT id,area_id,room_name,description,capacity,max_booking,statut_room,show_fic_room,picture_room,comment_room,delais_max_resa_room,delais_min_resa_room,allow_action_in_past,dont_allow_modify,order_display,delais_option_reservation,type_affichage_reser,moderate,qui_peut_reserver_pour,active_ressource_empruntee FROM grr_room";
$sql_update[agt_room_idp]="INSERT INTO agt_room_idp (id,room_id,start_time_idp,end_time_idp) SELECT id,room_id,start_time_idp,end_time_idp FROM grr_room_idp";
$sql_update[agt_type_area]="INSERT INTO agt_type_area (id,type_name,order_display,couleur,type_letter) SELECT id,type_name,order_display,couleur,type_letter FROM grr_type_area";
$sql_update[agt_utilisateurs]="INSERT INTO agt_utilisateurs (login,nom,prenom,password,email,statut,etat,default_area,default_room,default_style,default_list_type,default_language,source,medecin) SELECT login,nom,prenom,password,email,statut,etat,default_area,default_room,default_style,default_list_type,default_language,source,medecin FROM grr_utilisateurs	";


/*
==============================================================================================

	Creation de nouvelle structure
	
==============================================================================================
*/
echo "<HR><h1>Creation nouvelle structure </h1><br>";
foreach ( $V2_220120014 as $key =>$sql)
{
	echo "<br>Table =>".$key. ":: ";
	$db->insert($sql);
	echo " Table  crée ou present";
}

/*
==============================================================================================

	export data from OLD=> NEW
	
==============================================================================================
*/
echo "<HR><h1>Exportation des données vers nouvelles structure </h1><br>";
foreach ( $sql_update as $key =>$sql)
{
	echo "<br>Table =>".$key." ::";
	$chk=$db->select("SELECT * from ".$key." LIMIT 5");
	if (count($chk) > 0)
	{
		echo " Erreur => Données present dans le table ".$key." Import impossible !";
	}else
	{
		$db->insert($sql);
		echo " Sauf erreur SQL , les données importées correctement";
	}
}

/*
==============================================================================================

	gestion des des modis spl
	gestion couloir => panier 

==============================================================================================
*/
echo "<br>Gestion Couloir=>Panier<br>";
$sql="UPDATE agt_room set room_name='Panier', room_alias='Panier' where room_name='Couloir'";
$db->update($sql);
$sql="UPDATE agt_room set room_alias=room_name";
$db->update($sql);


echo "<br>Nom du service et NO poste<br>";
$sql="UPDATE agt_service set noposte=RIGHT(service_name,3) ";
$db->update($sql);
$sql="UPDATE agt_service set grp_service=SUBSTRING(service_name,1,length(service_name)-4) ";
$db->update($sql);



echo "<h3 color='red'>Veuillez vérigfier les erreur SQL<br>sinon l'immport est bien passée</h3>";
exit;






/*
==============================================================================================

	preparation des SQL
	
==============================================================================================
*/
/*
echo "<pre>";
foreach($V1_Tables as $OldTable){	
	$result = $db->select("SHOW COLUMNS FROM $OldTable");
	if (!$result) {
	   echo 'Impossible d\'exécuter la requête : ' . mysql_error();
	}else{
			//prepare SQL;
			$NewTable=str_replace("grr_","agt_",$OldTable);
			$Debut = "INSERT INTO $NewTable (";
			$Fields="";
			for($f=0; $f < count($result); $f++)
			{
				if ($f > 0)	$Fields .=",";				
				$Fields .=$result[$f]['Field'];
			}
      $sql = $Debut.$Fields.") SELECT ".$Fields." FROM $OldTable";
      echo "<hr>".$sql;
      
	}
	
}
*/
?>	

