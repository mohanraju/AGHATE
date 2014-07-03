<?php
#########################################################################
#                    admin_calendar.php                                 												   #
#                                                                      														   #
#            interface permettant la la réservation en bloc             											   #
#                  de journées entières                                 												   #
#               Dernière modification : 06/12/2007                     											   #
#                                                                       													   #
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
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassGilda1.php";
include "./commun/include/ClassGildaToAghate.php";
include "./commun/include/ClassAghate.php";


$sql ="select NOLIT,NOCHAM,NOSERV,to_char(DDVALI,'YYYY-MM-DD') as DDVALI,
			  to_char(DFVALI,'YYYY-MM-DD') as DFVALI,NOPOST,HHDIDP,HHFIDP,CDITEM 
				FROM IDP";
				
$debut = time();

echo "<pre>";
				
// Initialisation
$Mysql = new MySQL();
$Aghate= new GildaToAghate();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$Gilda= new Gilda($ConnexionStringGILDA);

// Récupère les données d'idp
//$idp = $Gilda ->GetIdpTab();
//$Aghate->InsertIdp($idp);

print_r($agt->GetRoomsByServiceId(30,1375833600,1388448000));

$fin = time();

$result = $fin - $debut;
echo "<br />Temps de traitement : ";
echo gmdate("H:i:s", $result); // convertit $result en heure, min et sec

?>
