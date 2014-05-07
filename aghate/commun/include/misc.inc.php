<?php
#########################################################################
#                           misc.inc.php                                #
#                                                                       #
#                       fichier de variables diverses                   #
#                                                                       #
#                  Dernière modification : 10/07/2006                   #
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

################################
# Development information
#################################
$grr_devel_email = "mohanraju.souprayane@sls.aphp.fr";
$grr_devel_url = "http://www.sls.ap-hop-paris.fr/";
// Numéro de version actuel
$version_grr = "1.9.5";
// Numéro de sous-version actuel (a, b, ...)
// Utilisez cette variable pour des versions qui corrigent la la version finale sans toucher à la base.
$sous_version_grr = "c";
// Numéro de la release candidate (doit être strictement inférieure à 9). Laisser vide s'il s'agit de la version stable.
$version_grr_RC = "20140505";

# Liste des tables
$liste_tables = array(
"agt_service_periodes",
"agt_type_area",
"agt_j_type_area",
"agt_j_mailuser_room",
"agt_j_user_area",
"agt_j_user_room",
"agt_log",
"agt_service",
"agt_loc",
"agt_repeat",
"agt_room",
"agt_config",
"agt_utilisateurs",
"agt_j_useradmin_area",
"grr_calendar",
"agt_overload",
"agt_loc_moderate"
);

# Liste des feuilles de style
$liste_themes = array(
"default",
"forestier",
"or",
"orange",
"argent",
"volcan",
"toulouse"
);

# Liste des noms des styles
$liste_name_themes = array(
"Grand bleu",
"Forestier",
"Doré",
"Orange",
"Argent",
"Volcan",
"Toulouse"
);

# Liste des langues
$liste_language = array(
"fr",
"de",
"en",
"it",
"es"
);

# Liste des noms des langues
$liste_name_language = array(
"Français",
"Deutch",
"English",
"Italiano",
"Spanish"
);

################################################
# Configuration du planning : valeurs par défaut
# Une interface en ligne permet une configuration domaine par domaine de ces valeurs
################################################
# Resolution - quel bloc peut être réservé, en secondes
# remarque : 1800 secondes = 1/2 heure.
$resolution = 900;

# Durée maximale de réservation, en minutes
# -1 : désactivation de la limite
$duree_max_resa = -1 ;

# Début et fin d'une journée : valeur entières uniquement de 0 à 23
# morningstarts doit être inférieur à  < eveningends.
$morningstarts = 8;
$eveningends   = 19;

# Minutes à ajouter à l'heure $eveningends pour avoir la fin réelle d'une journée.
# Examples: pour que le dernier bloc réservable de la journée soit 16:30-17:00, mettre :
# eveningends=16 et eveningends_minutes=30.
# Pour avoir une journée de 24 heures avec un pas de 15 minutes mettre :
# morningstarts=0; eveningends=23;
# eveningends_minutes=45; et resolution=900.
$eveningends_minutes = 0;

# Début de la semaine: 0 pour dimanche, 1 pou lundi, etc.
$weekstarts = 1;

# Format d'affichage du temps : valeur 0 pour un affichage « 12 heures » et valeur 1 pour un affichage  « 24 heure ».
$twentyfourhour_format = 1;

# Ci-dessous des fonctions non officielles (non documentées) de GRR
# En attendant qu'elles soient implémentées dans GRR avec une interface en ligne

# Vous pouvez indiquer ci-dessous l'id d'une ressource qui sera réservable, même par un simple visiteur
$id_room_autorise = "";

# Possibilité de désactiver le bandeau supérieur dans le cas de simples visiteurs
# Pour se connecter il est alors nécessaire de se rendre directement à l'adresse du type http://mon-site.fr/grr/login.php
# Mettre ci-dessous $desactive_bandeau_sup = 1;  pour désactiver le bandeau supérieur pour les simples visiteurs.
# Mettre ci-dessous $desactive_bandeau_sup = 0;  pour ne pas désactiver le bandeau supérieur pour les simples visiteurs.
$desactive_bandeau_sup = 0;

?>
