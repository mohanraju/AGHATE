<html>
	<body>
		<h1>AGHATE V2.1</h1>
		<H3>Build 20140115</H3>
		<h5>les modification apportés dans cette build</h5>
		<ul>
			<li>le structure de la tables sont modifée, les nom de tables commencent par 'agt' au lieu de 'grr'</li>
			<li>table grr_entry devenu agt_loc</li>
			<li>table grr_area devenu agt_service</li>
		</ul>		
		<ul>
			<li>Gilda.mvt est inclu dans cette modif pour recuparer las paients couché dans  Gilda.coulour </li>
			<li>les sorties des patient sont rércuparès gilda.MVT avec tymvad='SH'</li>
		</ul>

		<h5>Comment mettre a jour cette version</h5>		
		<ul>
			<li>Il est neccessaire de faire un backup complet de base de données </li>
			<li>faire un backup de tous les scripts php de votre dossier d'installation</li>
			<li>unzip les le tar aghate_20140115.tar dans le dossier d'instllation</li>
			<li>mettre a jour les fichier configurations dans le sossier /config</li>			
			<li>Executer le requete "maj_v2_1_20140115.php"</li>
		</ul>

		
	</body>	
</html>
