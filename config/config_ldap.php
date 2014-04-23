<?php
# Les quatre lignes suivantes sont à modifier selon votre configuration
# ligne suivante : l'adresse de l'annuaire LDAP.
# Si c'est le même que celui qui heberge les scripts, mettre "localhost"
$ldap_adresse="wprod.ds.aphp.fr";
# ligne suivante : le port utilisé
$ldap_port="389";
# ligne suivante : l'identifiant et le mot de passe dans le cas d'un accès non anonyme
$ldap_login="CN=s-nck-psupervision,OU=100-utilisateurs,OU=-standard,OU=NCK0,DC=wprod,DC=ds,DC=aphp,DC=fr";
# Remarque : des problèmes liés à un mot de passe contenant un ou plusieurs caractères accentués ont déjà été constatés.
$ldap_pwd="azerty12";
# ligne suivante : le chemin d'accès dans l'annuaire
$ldap_base="DC=wprod,DC=ds,DC=aphp,DC=fr";
# ligne suivante : filtre LDAP supplémentaire (facultatif)
$ldap_filter="";
# ligne suivante : utiliser TLS
$use_tls=FALSE;
# Attention : si vous configurez manuellement ce fichier (sans passer par la configuration en ligne)
# vous devez tout de même activer LDAP en choisissant le "statut par défaut des utilisateurs importés".
# Pour cela, rendez-vous sur la page : configuration -> Configuration LDAP.
?>
