<?php
#########################################################################
#                         cas.inc.php                                   #
#     script de redirection vers l'authentification CAS                 #
#                                                                       #
#                  Dernière modification : 22/12/2005                   #
#                                                                       #
#########################################################################
/*
 * Copyright 2005 Olivier Mounier
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

// Le package phpCAS doit etre stocké dans un sous-répertoire « CAS »
// dans un répertoire correspondant a l'include_path du php.ini (exemple : /var/lib/php)
include_once('CAS/CAS.php');

// cas.sso est le fichier d'informations de connexions au serveur cas
// Le fichier cas.sso doit etre stocké dans un sous-répertoire « CAS »
// dans un répertoire correspondant a l'include_path du php.ini (exemple : /var/lib/php)
include('CAS/cas.sso');

// declare le script comme un client CAS
// Le dernier argument (true par défaut) donne la possibilité à phpCAS d'ouvrir une session php.
// Si tel est le cas, l'authentification CAS n'est pas répercutée dans GRR (et il faut se réauthentifier dans l'appli),
// car le "start_session()" de l'application (environ ligne 232 dans le fichier "session.inc.php") ne marche pas ===> la session a été ouverte par phpCAS
// et les variables de session positionnées par la suite par grr ne sont pas récupérables.
phpCAS::client(CAS_VERSION_2_0,$serveurSSO,$serveurSSOPort,$serveurSSORacine,false);

phpCAS::setLang('french');

// Set the fixed URL that will be set as the CAS service parameter. When this method is not called, a phpCAS script uses its own URL.
if (isset($Url_CAS_setFixedServiceURL) and ($Url_CAS_setFixedServiceURL != ''))
    phpCAS::setFixedServiceURL($Url_CAS_setFixedServiceURL) ;

// redirige vers le serveur CAS si nécessaire
phpCAS::forceAuthentication();

// A ce stade, l'utilisateur est authentifié
$user=phpCAS::getUser();
$login=phpCAS::getUser();
$user_ext_authentifie = 'cas';
?>
