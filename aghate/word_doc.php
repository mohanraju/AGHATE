<?php                                                                                                         
if(isset($_POST['nom_du_bouton']))                                                                            
{                                                                                                             
	$nom_fic = $_POST['Nom_de_mon_fichier'];                                                                      
	$var1= $_POST['var1'];                                                                                        
	$var2= $_POST['var2'];                                                                                        
	                                                                                                              
	$fp=fopen("reminder.rtf","r"); /*ouvre le document de base en lecture*/                                       
	$new=fopen("doc/".$nom_fic.".rtf","w+"); /* Créer le nouveau document dans le répertoire doc.*/               
	                                                                                                              
	while(!feof($fp)) { /*Tant que je ne suis pas a la fin de mon fichier je lis ligne par ligne.*/               
		$Ligne = fgets($fp,255); /* On récupère ligne par ligne les données.*/                                        
		$Ligne = preg_replace("#ma_balise_1#", $var1, $Ligne);                                                        
		$Ligne = preg_replace("#ma_balise_2#", $var2, $Ligne);                                                        
		fputs($new,$Ligne); /* Apres avoir remplacé les balises par les valeur je les écris dans le novueau document*/
	}                                                                                                             
	                                                                                                              
	fclose($fp);/*Fermeture du fichier de base*/                                                                  
	fclose($new);/*Fermeture du nouveau fichier*/                                                                 
}                                                                                                              
?>                                                                                                            
