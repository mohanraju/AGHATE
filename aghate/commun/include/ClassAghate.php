<?php
#########################################################################
#                         ClassAghate.php                        		#
#				Fonction qui effectue les requêtes					    #
#                dans la base Aghate 								    #
#                 									                    #
#																        #
#########################################################################


class Aghate  extends MySQL
{
	
	var $FileName;
	var $FilePath;
	var $NomCouloir;
	var $AffcheTraceSurEcran;
	var $DureePrevisionel;
	var $Aghate;
	var $ColNames;
	var $ServiceLitArray;
	var $ServiceUhArray;
	var $NomTableLoc;
	var $Settings;

	//========================================================================
	// constructeur
	//========================================================================	
	function Aghate($NomTableLoc="")
	{
		parent::MySQL(); //initlaise connexion Mysql
		$this->NomCouloir="Panier";
		$this->DureePrevisionel='5';
		if(strlen($NomTableLoc) > 0)
			$this->NomTableLoc=$NomTableLoc;
		$this->InitSettings();	
	}	
	//========================================================================
	// function vérify isTableLoc initialisé 
	//========================================================================	
	function CheckTableInit(){
		if(strlen($this->NomTableLoc)<1){
			echo "Table réservation non défini";
			exit;
		}
	}	
	
	//========================================================================
	// function InitSettings()
	//========================================================================	
	function InitSettings()
	{
		$res=$this->select("Select * from agt_config order by name");
		$nbr=count($res);
		
		for($i=0; $i < count($res); $i++)
		{
			$Settings[$res[$i]['NAME']]=$res[$i]['VALUE'];
		}
		$this->Settings=$Settings;
 
	}	

	//========================================================================
	// function GetSettings($Name)
	//========================================================================	
	function GetSettings($Name)
	{
		return $this->Settings[$Name];
 
	}		
	/*######################################################################################
			SERVICE INFO
	*######################################################################################*/
	
	/*
	========================================================================
	function GetAllArea()
	retourne l'ensemble des services repertoriés dans agt_service
	========================================================================	 
	*/
	function GetAllArea()
	{
		$sql = "select * from agt_service where   etat='n' order by service_name ";
		$res = $this->select($sql);
		return $res;
	}
	
	/*
	========================================================================
	function GetServiceAutoriser()
	retourne l'ensemble des services repertoriés dans agt_service
	========================================================================	 
	*/
	function GetAllServiceAuthoriser($user,$droit="",$enable_periods=true){
		if(!$enable_periods)
		$Cond=" AND enable_periods <> 'y'";
		
		if ($droit=="administrateur")
		{
			return $this->select("select * from agt_service WHERE 1=1 $Cond and etat ='n' order by service_name ");
		}else
		{
			$sql="select agt_service.* from agt_service,agt_j_user_area where agt_service.id=agt_j_user_area.id_area and login='".$user."' $Cond and etat ='n' order by service_name";
			return $this->select($sql);
		}
	}	
	
		/*
	============================================================================
	function GetServiceInfoByServiceId ($service_id)
	Retourne les infos sur l'area passé en paramètre
	============================================================================
	*/
	// edit_entry.php, day.php, year.php
	function GetServiceInfoByServiceId ($service_id) {
		$sql = "select * from agt_service 
				where id='".$service_id."' and etat='n'
				order by service_name";
		$res = parent :: select($sql);
		return $res;
	}
	
	/*
	============================================================================
	function GetServiceInfoByValRech($param,$service_autoriser,$table)
	Retourne les infos sur l'area passé en paramètre
	============================================================================
	*/
	function GetServiceInfoByValRech($param,$service_autoriser,$table){
		$sql="SELECT id, service_name from ".$table."
					WHERE service_name LIKE '%".$param."%'				
					AND enable_periods != 'y' 
					AND etat='n'
					AND id IN (".$service_autoriser.") 
					ORDER by service_name";
		$res = parent::select($sql);
		return $res;
	}
	
		
	/*
	============================================================================
	function GetAreaInfoByServiceIdEnable() 
		* 	peu utiliser
	============================================================================
	*/
	//edit_entry.php
	function GetAreaInfoByServiceIdEnable() {
		$sql = "select id, service_name from agt_service where enable_periods != 'y' and etat='n' order by service_name";
		$res = parent :: select ($sql);
		return $res;
	}
	
	/*
	===================================================
	CheckDefaultAreaValide($default_area){
	===================================================
	*/
	function CheckDefaultAreaValide($default_area){
		$sql = "SELECT id FROM agt_service where id = '".$default_area."'";
		$res = $this->select($sql);
		if(count($res)>0)
			return true;
		else
			return false;
	}
	
		
	/*
	============================================================================
	function GetUhByServiceId($id)
	Retourne les uh du service
	============================================================================
	*/
	function GetUhByServiceId($service_id){
		$sql="select uh,urm
				from agt_service  
				where id='".$service_id."'";
		$res = parent :: select ($sql);
		$tab = explode('|',$res[0]['uh']);
		return $tab;		   
	}	
	
	
	/*========================================================================
	 Function GetServiceInfoByServiceName($service_name)
		 paramètre : $service_name : numero de poste/service
		 renvoie l'id du poste correspondant dans agt_service
	========================================================================*/
	function GetServiceInfoByServiceName($service_name)
	{
		$req = "SELECT id,duree_previsionnel FROM agt_service WHERE service_name LIKE '%$service_name%'";
		$info = $this->select($req);
		return $info;
	}
	
	/*========================================================================
	 Function GetServiceInfoByNoPost($NoPoste)
	========================================================================*/
	function GetServiceInfoByNoPost($NoPoste)
	{
		$req = "SELECT id,duree_previsionnel FROM agt_service WHERE noposte= '$NoPoste'";
		$info = $this->select($req);
		if (count($info) == 0) 
			return 0;
		else
			return $info[0];
	}
	
	
	/*========================================================================
	 Function GetServiceInfoByUh($uh)
	========================================================================*/
	function GetServiceInfoByUh($uh)
	{
		$req = "SELECT * FROM agt_service WHERE uh LIKE '%".trim($uh)."%'";
		$info = $this->select($req);
		if (count($info) == 0) return 0;
		else
			return $info[0];
	}
	
	

	
	/*========================================================================
	 Function GetServiceInfoByRoomName($nolit)
	========================================================================*/
	function GetServiceInfoByRoomName($nolit)
	{
		$sql = "SELECT agt_service.id,service_name,duree_previsionnel from agt_room,agt_service 
					where room_name LIKE '$nolit'
					and agt_room.service_id = agt_service.id";
		$res = $this->select($sql);
		return $res[0];
	}
	
	
	/*========================================================================
	// function IsExistService($nopost)
	// paramètre : $nopost : numero de poste
	// verifie si $nopost est un nouveau poste dans agt_service
	* renvoie true si vrai sinon faux
	========================================================================*/
	function IsExistService($NomService)
	{
		$sql = "SELECT * FROM agt_service WHERE service_name='$NomService'";
		$chk = $this->select($sql);
		if (count($chk) == 0) return true;
		else
			return false;
	}
	
	
	/*######################################################################################
			FIN SERVICE INFO
	 *######################################################################################*/
	
	
	
	
	
	/*######################################################################################
			ROOM INFO
	 *######################################################################################*/
				
	/*========================================================================
	// function CompteLit ($AreaId)
	// $AreaId : service id
	// $_date : a voir
	========================================================================*/
	
	function CompteLit ($AreaId){ 
		$req = "select count(*) as nbr_room FROM agt_room WHERE service_id = '" .$AreaId."' and room_name not in('Panier')"; // pour chaque id, cherche le nombre de lits donc pour chaque service on cherche le nombre de lit
		$resultat=parent::select($req);			
		if ($resultat) {
			return $resultat[0]['nbr_room'];
		}
		return 0;
	}
	
	/*========================================================================
	// function CompteLitOccupe ($AreaId,$_date)
	// $AreaId : service id
	// $_date : date a laquelle on veut le nombre de lits
	========================================================================*/
	
	function CompteLitOccupe ($AreaId,$_date,$heure_deb=0,$heure_fin=0) {
		$this->CheckTableInit();
		$day = substr($_date,0,2);
		$month = substr($_date,3,2);
		$year = substr($_date,6,10);
		$dateDebut = mktime($heure_deb, 0, 0, $month  , $day, $year);
		// modifier quand $heure = 23 faire $day+1 et 00:00:00
		if ($heure_fin == 23) 
			$dateFin = mktime($heure_fin, 59, 0, $month  , $day, $year);
		else
			$dateFin = mktime($heure_fin, 0, 0, $month  , $day, $year);
		$query = "SELECT sum(nbr_room) FROM( SELECT agt_room.id, agt_room.room_name,statut_entry,COUNT( agt_room.id ) as nbr_room
					FROM ".$this->NomTableLoc.", agt_room WHERE agt_room.id = ".$this->NomTableLoc.".room_id AND service_id ='" .$AreaId. "'
					AND statut_entry != 'SUPPRIMER'
					AND (start_time BETWEEN $dateDebut AND $dateFin 
						 OR end_time BETWEEN $dateDebut AND $dateFin
						OR start_time <	 $dateFin AND end_time > $dateDebut)
					 GROUP BY room_id) AS temp;";
		//if ($AreaId == '71') echo $query.'<br/>';
		$resultat=parent::select($query);	
		return $resultat[0][0];}
	
	
	
	/*
	============================================================================
	function GetPanierIdByServiceId ($service_id)
	Retourne panier ID d'un service
	============================================================================
	*/
	function GetPanierIdByServiceId ($service_id){
		$req="SELECT id from agt_room where service_id='$service_id' AND room_name='Panier'";
		$resultat=parent::select($req);
		return $resultat[0]['id'];
	}
	
	/*
	============================================================================
	function GetAreaIdByRoomId($room_id)
	Retourne l'area id en fonction de la room
	============================================================================
	*/
	//edit_entry.php,admin_edit_room
	function GetAreaIdByRoomId($room_id) {
		$sql = "select service_id from agt_room where id=$room_id";
		$res = parent::select($sql);
		return $res[0];
	}
	
	/*
	============================================================================
	function GetRoomInfoByRoomId ($room_id)
	Retourne les informations sur la room
	============================================================================
	*/
	// edit_entry.php, edit_entry_handler.php
	function GetRoomInfoByRoomId ($room_id) {
		$sql = "select * from agt_room where id='".$room_id."'";
		$res = parent :: select($sql);
		return $res[0];
	}
	/*
	============================================================================
	function GetRoomInfoByRoomNameAndService ($room_name,$service_id)
	Retourne les informations sur la room
	============================================================================
	*/
	function GetRoomInfoByRoomNameAndService ($room_name,$service_id) {
		$sql = "select * from agt_room where room_name LIKE '%".$room_name."%' AND
					service_id = ".$service_id;
		$res = parent :: select($sql);
		return $res;
	}
	/*
	============================================================================
	modifié par mohan
	function GetAllRooms ($area,$Panier=false){
	Retourne les informations des rooms dans l'area
	* maj 07/11/2013 on ne prend pas le Panier
	============================================================================
	*/
	//day.php,edit_entry.php
	function GetAllRooms ($Service_id,$Panier=false){

		if(!$Panier)
		$CondPanier="And room_name!='Panier'";

		$sql = "select * from agt_room where service_id='".$Service_id."' $CondPanier
					order by order_display,room_name ";
		$res = parent :: select ($sql);
		return $res;
	}
	
	
	/*
	============================================================================
	function GetMinRoomId ($service_id)
	============================================================================
	*/	
	//week_all.php
	function GetMinRoomId ($service_id) {
		$sql = "select min(id) from agt_room where service_id=$service_id";
		$res = parent::select($sql);
		return $res[0]['min(id)'];
	}	
	
	
	/*
	============================================================================
	function GetServiceIdByRoomId ($room_id) 
	============================================================================
	*/
	function GetServiceIdByRoomId ($room_id) {
		$sql = "SELECT service_id FROM agt_room WHERE id = '".$room_id."'";
		$res = parent :: select ($sql);
		return $res[0]['service_id'];
	}
	
	/*
	============================================================================
	function GetRoomNameByRoomId ($room_id) 
	============================================================================
	*/
	function GetRoomNameByRoomId ($room_id)
	{
		$sql="SELECT room_name FROM agt_room where id=".$room_id;
		$res = parent::select($sql);
		return $res[0]['room_name'];
	}
	
	
	/*
	============================================================================
	function GetRoomsByServiceId ($ServiceId,$WithPanier=false)
	Retourne les informations des rooms dans l'area
	============================================================================
	*/
	function GetRoomsByServiceId ($ServiceId,$WithPanier=false){
		if(!$WithPanier)
			$sql_add = "and room_name != 'Panier'";
		$sql = "select id,room_name,room_alias,description, capacity, statut_room, show_fic_room, 
				delais_option_reservation, moderate,picture_room from agt_room where service_id='".$ServiceId."' 
				$sql_add
				order by order_display,room_name
				";
		$res = parent :: select ($sql);
		return $res;
	}
	
	
	/*========================================================================
	// function GetRoomInfo($room_name)
	// paramètre : $room_name : nom de la room
	* renvoie l'id de la room dans agt_room
	========================================================================*/
	function GetRoomInfo ($room_name,$service_id)
	{
		$sql = "SELECT id FROM agt_room WHERE room_name='".$room_name."' and service_id='".$service_id."'";
		//echo "<br>".$sql;
		$res = $this->select($sql);
		return $res[0];
	}
	
	
	
	/*========================================================================
	// function IsExistRoom($nolit,$service_id)
	//~ // paramètre : $CheckNewRoom : numero de lit
	// verifie si $nolitt est un nouveau lit dans agt_room
	* renvoie true si vrai sinon faux
	========================================================================*/
	function IsExistRoom($RoomName,$service_id)
	{
		$sql = "SELECT * FROM agt_room WHERE room_name='$RoomName' and service_id='$service_id'";
		$chk = $this->select($sql);
		if (count($chk) == 0) 
			return true;
		else
			return false;
	}
	
	
	
	/*
	============================================================================
	function CheckIndispo ($room_id,$start_day)
	* renvoie true si c'est indisponible durant la période défini
	* false sinon
	============================================================================
	*/
	function CheckIndispo ($room_id,$start_day)
	{
		$sql = "SELECT * FROM agt_room_idp WHERE room_id='".$room_id."'
				AND end_time_idp >='".$start_day."'";
		$res = parent::select($sql);
		if ($res){
			for ($i=0;$i<count($res);$i++){
				$start_time_idp = $res[$i]['start_time_idp'];
				$end_time_idp = $res[$i]['end_time_idp'];
				if ($start_day>=$start_time_idp && $start_day<=$end_time_idp){
					return true;
					break;
				}
			}
			return false;
		}
		else
			return false;
	}
	


	/*
	============================================================================
	* function GetAllIdp($room_id)
	* agt_room_idp
	============================================================================
	*/
	
	function GetAllIdp($room_id)
	{
		$today = time();
		$sql = "SELECT * FROM agt_room_idp WHERE room_id='".$room_id."'";
		$res = parent::select($sql);
		return $res;
	}
	
	
	
	/*######################################################################################
			FIN ROOM INFO
	 *######################################################################################*/
	
	/*######################################################################################
			ENTRY INFO
	 *######################################################################################*/
	
	/*
	============================================================================
	FONCTION DE LA CLASSE RESERVATION
	Retourne l'ensemble des reservations durant la période de start_time a end_time
	* dans le service service_id
	============================================================================
	*/
	function GetEntryInfoDate ($start_time,$end_time,$service_id,$entry_id) {
		$this->CheckTableInit();
		$sql_room = "SELECT id from agt_room WHERE service_id='".$service_id."'
						ORDER BY room_name";
		$sql_chk = "SELECT room_id,start_time,end_time FROM ".$this->NomTableLoc."
					WHERE (start_time BETWEEN '".$start_time."' AND '".$end_time."' 
						OR  end_time BETWEEN '".$start_time."' AND '".$end_time."' 
						OR  (start_time < '".$end_time."' AND end_time > '".$start_time."'))
					AND ".$this->NomTableLoc.".room_id IN (".$sql_room.")
					AND statut_entry != 'SUPPRIMER'";
		if (strlen($entry_id)>0) 
			$sql_chk.= " AND ".$this->NomTableLoc.".id <>'".$entry_id."'";
		$resa_res = $this->select($sql_chk);
		return $resa_res;
		
	}
	
	
	
	/**==========================================================================
	 * FONCTION DE LA CLASSE RESERVATION 
	 * GetRoomIdByEntryId()
	 * Return an array with all additionnal fields
	 * $id - Id of the entry
	 *
	 ==========================================================================*/	
	function GetRoomIdByEntryId ($entry_id) {
		$this->CheckTableInit();
		$sql = "SELECT room_id FROM ".$this->NomTableLoc." WHERE id='".$entry_id."'";
		$res = $this->select($sql);
		return $res[0]['room_id'];
	}
	
	

	/*
	============================================================================
	function GetInfoReservation ($entry_id)
	Retourne les infos sur les reservations
	============================================================================
	*/
	// view_entry.php		
	function GetInfoReservation ($entry_id) {
		$this->CheckTableInit();
		$sql = "SELECT 	".$this->NomTableLoc.".*,
						".$this->NomTableLoc.".noip as nip,
						agt_service.urm,
						agt_service.service_name,
						agt_service.id as service_id,
						agt_service.nom_formulaire,
						
						agt_room.room_name,
						agt_room.room_alias,
						agt_room.delais_option_reservation,
						agt_room.active_ressource_empruntee,
						
						agt_pat.nom,
						agt_pat.prenom,
						agt_pat.ddn,
						agt_pat.sex
		FROM   agt_room, agt_service,".$this->NomTableLoc."
		LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip				
		WHERE ".$this->NomTableLoc.".room_id = agt_room.id
		  AND agt_room.service_id = agt_service.id  
				AND ".$this->NomTableLoc.".id='".$entry_id."'";
		$res = parent :: select ($sql);
		return $res[0];	
	}

	/*
	============================================================================
	function GetInfoEntry ($id)
	Retourne les infos de la réservation id
	============================================================================
	*/
	//view_entry.php,edit_entry.php
	function GetInfoEntry ($id){
		$this->CheckTableInit();
		$sql = "SELECT ".$this->NomTableLoc.".*, medecin, nda, nom, prenom, ddn,sex
					FROM ".$this->NomTableLoc."
					LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip		
					WHERE id = '".$id."'";
		$res = parent ::select($sql);
		return $res[0];
	}
	
	
	/*
	============================================================================
	function GetConsultProg($user)
	============================================================================
	*/
	function GetConsultProg($user){
		$sql="SELECT * 
				FROM agt_prog as p,agt_pat as i 
				WHERE create_by='".$user."'
				AND p.noip = i.noip
				AND statut_demande='N'";
		$res = $this->select($sql);
		return $res;
	}
	
	
	
	
	/*========================================================================
	// function GetAgtLocTab()
	* Retourne un tableau avec les données de agt_loc où ds_source 
	* est soit Automate ou soit Programmé
	========================================================================*/
	function GetAgtLocTab ()
	{
		$this->CheckTableInit();
		$sql = "SELECT * FROM ".$this->NomTableLoc." WHERE ds_source IN ('Automate','Programme')
					AND statut_entry != 'SUPPRIMER'";
		$res = $this->select($sql);
		return $res;
	}
	
	
	/*========================================================================
	// function GetEntryInfoByNipNda($date_deb,$nip,$nda)
	// paramètre : $nip : nip patient ,$nda : nda patient
	* renvoie le dernier id
	========================================================================*/
	function GetEntryInfoByNipNda ($date_deb,$nip,$nda)
	{
		$this->CheckTableInit();
		$sql = "SELECT * FROM ".$this->NomTableLoc." 
				WHERE nda='$nda' and noip='$nip'
				AND ds_source!='Gilda'
				AND statut_entry != 'SUPPRIMER'
				and start_time < $date_deb
				ORDER BY end_time DESC LIMIT 1";
		$res = $this->select ($sql);
		return $res[0];
	}	

	
	/*========================================================================
	// function GetEntryToUp()
	* 	Renvoie l'ensemble des convocations qui n'ont pas été validé par une
	* 	date de sortie Gilda
	========================================================================*/
	function GetEntryToUp()
	{
		$this->CheckTableInit();
		list($jour,$mois,$annee)= explode ("/",date("d/m/Y"));
		$date_fin_end = mktime(23,59,59,$mois,$jour,$annee);
		$sql = "SELECT * FROM ".$this->NomTableLoc." 
					WHERE  end_time < '$date_fin_end'
					AND statut_entry != 'SUPPRIMER'
					AND ds_source IN ('Automate')";
		$res = $this->select($sql);
		return $res;
	}
	
	/*========================================================================
	// GetEntryParNIP($noip,$uh,$end_time)
	* 	Renvoie l'ensemble des convocations qui n'ont pas été validé par une
	* 	date de sortie Gilda
	*   start_time <= $end_time and end_time >= $end_time
	*   uh = uh and nip= nip
	
	========================================================================*/
	function GetEntryParNIP($noip,$nda,$uh,$end_time)
	{
		$this->CheckTableInit();
		$sql = "SELECT * FROM ".$this->NomTableLoc." 
					WHERE  start_time < '$end_time'
					and end_time >= '$end_time'
					AND statut_entry != 'SUPPRIMER'
					and uh ='$uh'
					and noip='$noip'
					and nda='$nda'					
					";
	//echo "<br>".$sql;					
		$res = $this->select($sql);
		return $res;
	}
	
	/*========================================================================
	// function GetDateFinProg($entry_id)
	// paramètre : $entry_id : id_programmation
	* Recupère la date fin programmé dans la table agt_prog
	========================================================================*/
	function GetDateFinProg($entry_id)
	{
		$sql = "SELECT end_time FROM agt_prog WHERE id='".$entry_id."'";
		$res = $this->select($sql);
		if (!empty($res[0]['end_time']))
			return $res[0]['end_time'];
		else
			return false;
	}
	
	
	/*
	============================================================================
	function GetIndice($all_entry_nda,$entry_id,$start_time) 
	Retourne la position du nda courant dans le tableau contenant l'ensemble des séjours
	============================================================================
	*/
	function GetIndice ($all_entry_nda,$entry_id) 
	{
		$nb_nda = count($all_entry_nda);
		$i=0;
		while ($i<$nb_nda)
		{
			if ($all_entry_nda[$i]['id'] == $entry_id ) 
			{
				return $i;
				break;
			}
			else
				$i++;
		}
		return false;
	}
	
	// faire la meme fonction et envoyer seulement les données préparer le lien directement dans day
	// recupere l'id et uh
	//
	
	/*
	============================================================================
	function HasPreviousSejour($all_entry_nda,$entry_id,$start_time) 
	Retourne le lien du séjour précedent si il y a un séjour précedent
	* $all_entry_nda = tableau retourné par la fonction GetEntriesByNda ($nda)
	============================================================================
	*/
	function HasPreviousSejour ($all_entry_nda,$entry_id,$start_time)
	{
		$nb_nda = count($all_entry_nda);
		$indice = $this->GetIndice ($all_entry_nda,$entry_id) ;
		$link_uh = array();
		if ($nb_nda > 1 && $indice != 0) {
			if ($all_entry_nda[0]['start_time'] == $start_time && $all_entry_nda[0]['id'] == $entry_id) return false;
			else {
				list($day,$month,$year) = explode("/",date("d/m/Y",$all_entry_nda[$indice-1]['start_time']));
				$area= $this->GetAreaIdByRoomId($all_entry_nda[$indice-1]['room_id']);
				// modifier pour view"view_entry.php?id=".$all_entry_nda[$indice-1]['id']."year=".$year."&month=".$month."&day=".$day."&area=".$area[0];
				$infos_uh = array("year"=>$year,"month"=>$month,"day"=>$day,"area"=>$area[0],"uh"=>$all_entry_nda[$indice-1]['uh']);
				return $infos_uh; 
			}
		}
		else
			return false;
	}
	
	/*
	============================================================================
	function HasNextSejour($all_entry_nda,$entry_id,$start_time) 
	Retourne le lien du séjour suivant si il y a un séjour suivant
	* $all_entry_nda = tableau retourné par la fonction GetEntriesByNda ($nda)
	============================================================================
	*/
	function HasNextSejour ($all_entry_nda,$entry_id,$start_time)
	{
		$nb_nda = count($all_entry_nda);
		$indice = $this->GetIndice ($all_entry_nda,$entry_id) ;
		$link_uh = array();
		if ($nb_nda > 1 && $indice != $nb_nda-1) {
			if ($all_entry_nda[$nb_nda-1]['start_time'] == $start_time && $all_entry_nda[$nb_nda-1]['id'] == $entry_id) return false;
			else {
				list($day,$month,$year) = explode("/",date("d/m/Y",$all_entry_nda[$indice+1]['start_time']));
				$area= $this->GetAreaIdByRoomId($all_entry_nda[$indice+1]['room_id']);
				// modifier pour view"view_entry.php?id=".$all_entry_nda[$indice+1]['id']."year=".$year."&month=".$month."&day=".$day."&area=".$area[0];
				$infos_uh = $infos_uh = array("year"=>$year,"month"=>$month,"day"=>$day,"area"=>$area[0],"uh"=>$all_entry_nda[$indice+1]['uh']);
				return $infos_uh; 
			}
		}
		else
			return false;
	}
	
	
	/*
	============================================================================
	function GetPreviousSejours($all_entry_nda,$entry_id) 
	Retourne L'ensemble des sejours precedent l'entry id 
	* $all_entry_nda = tableau retourné par la fonction GetEntriesByNda ($nda)
	============================================================================
	*/
	//$indice-1 pour pas prendre le séjour courant
	function GetPreviousSejours ($all_entry_nda,$entry_id)
	{
		$nb_nda = count($all_entry_nda);
		$indice = $this->GetIndice ($all_entry_nda,$entry_id) ;
		if ($nb_nda > 1 && $indice != 0){
			if ($all_entry_nda[0]['id'] == $entry_id) return false;
			else {
				list($day,$month,$year) = explode("/",date("d/m/Y",$all_entry_nda[$indice-1]['start_time']));
				$area= $this->GetAreaIdByRoomId($all_entry_nda[$indice-1]['room_id']);
				// modifier pour view"view_entry.php?id=".$all_entry_nda[$indice+1]['id']."year=".$year."&month=".$month."&day=".$day."&area=".$area[0];
				$infos_view=array();
				for ($i=$indice-1;$i>=0;$i--) {
					list($day,$month,$year) = explode("/",date("d/m/Y",$all_entry_nda[$i]['start_time']));
					$area= $this->GetAreaIdByRoomId($all_entry_nda[$i]['room_id']);
					$infos_view[$i]= array("year"=>$year,"month"=>$month,"day"=>$day,"area"=>$area[0],"uh"=>$all_entry_nda[$i]['uh'],
											"id"=>$all_entry_nda[$i]['id']);
				}
				return $infos_view; 
			}
		}
		else
			return false;
	}
	
	/*
	============================================================================
	function GetNextSejours($all_entry_nda,$entry_id) 
	Retourne L'ensemble des sejours qui suivent l'entry id 
	* $all_entry_nda = tableau retourné par la fonction GetEntriesByNda ($nda)
	============================================================================
	*/
	function GetNextSejours ($all_entry_nda,$entry_id) 
	{
		$nb_nda = count($all_entry_nda);
		$indice = $this->GetIndice($all_entry_nda,$entry_id) ;
		if ($nb_nda > 1 && $indice != $nb_nda-1){
			if ($all_entry_nda[$nb_nda-1]['id'] == $entry_id) return false;
			else {
				list($day,$month,$year) = explode("/",date("d/m/Y",$all_entry_nda[$indice+1]['start_time']));
				$area= $this->GetAreaIdByRoomId($all_entry_nda[$indice+1]['room_id']);
				// modifier pour view"view_entry.php?id=".$all_entry_nda[$indice+1]['id']."year=".$year."&month=".$month."&day=".$day."&area=".$area[0];
				$infos_view=array();
				$infos_view["indice"] = $indice;
				for ($i=$indice+1;$i<$nb_nda;$i++) {
					list($day,$month,$year) = explode("/",date("d/m/Y",$all_entry_nda[$i]['start_time']));
					$area= $this->GetAreaIdByRoomId($all_entry_nda[$i]['room_id']);
					$infos_view[$i]= array("year"=>$year,"month"=>$month,"day"=>$day,"area"=>$area[0],"uh"=>$all_entry_nda[$i]['uh'],
											"id"=>$all_entry_nda[$i]['id']);
				}
				return $infos_view; 
			}
		}
		else
			return false;
	}
	
	
	/*=======================================================================================
	 * 
	 * FONCTION DE LA CLASSE RESERVATION
	 * 
	 *======================================================================================*/
	
	
	/*
	============================================================================
	function CheckRoomDispo($room_id,$date_deb,$date_fin="",$duration="",$entry_id){
	$room_id : id room
	* $date_deb : date debut séjour
	* $date_fin  : date fin séjour
	* Verifie place dispo au lit room_id
	* FONCTION DE LA CLASSE RESERVATION
	============================================================================
	*/
	function CheckRoomDispo($room_id,$date_deb,$date_fin="",$duration="",$entry_id){
		$this->CheckTableInit();
		list($day,$month,$year) = explode('/',date('d/m/y',$date_deb));
		list($heure,$min) = explode(':',date('H:i',$date_deb));
		
		// check le room est un panier
		$room_info=$this->GetRoomInfoByRoomId($room_id);
		if($room_info['room_name']=='Panier'){
			return;
			exit;
		}
		if(strlen($duration)>1) 
			$date_fin = $date_deb+$duration;
		
		if (strlen($entry_id) >0) 	
			$CondId=" AND ".$this->NomTableLoc.".id !='".$entry_id."' ";

		$sql_disp = "SELECT *
							FROM ".$this->NomTableLoc." 
							WHERE room_id =  '".$room_id."' 
							AND statut_entry != 'SUPPRIMER'
			 				AND (start_time BETWEEN '".$date_deb."' AND '".$date_fin."' 
			 				OR  end_time BETWEEN '".$date_deb."' AND '".$date_fin."' 
			 				OR  start_time < '".$date_fin."' AND end_time > '".$date_deb."')
			 				$CondId
			 				ORDER BY room_id ";
 
		$res_disp=$this->select($sql_disp);		
		$err="";
		if (count($res_disp) < 1){
			return $err;
		}
		else{
			$err= "ERR|pas de place disponible";
			return $err ;
		}
	}
	
	
	
	/*
	============================================================================
	function CheckLitDispoArea($start_time,$end_time,$service_id){
	$service_id : service_id 
	* $start_time : date debut séjour
	* $end_time  : date fin séjour
	* Verifie lit dispo au service service_id
	* $protocole
	* ajax_edit_entry
	* FONCTION DE LA CLASSE RESERVATION
	============================================================================
	*/
	function CheckLitDispoArea ($start_time,$end_time,$service_id){
		
		$rooms = $this->GetRoomsByServiceId($service_id);
		$i=0;
		$err = "";
		$flag=true;
		do{
			if (strlen($this->CheckRoomDispo($rooms[$i]['id'],$start_time,$end_time)==0)){
				$room_id=$rooms[$i]['id'];
				$flag=false;
			}
			else
				$i++;
		 }while ($i<count($rooms) && $flag==true);
		 if ($flag)
			$err = "ERR | Pas de place disponible, Veuillez choisir une autre date ou un autre service";
		 return  $err;
	}
	
	
	/*
	============================================================================
	function CheckDispoLitInArea($start_time,$end_time,$service_id){
	$service_id : service_id 
	* $start_time : date debut séjour
	* $end_time  : date fin séjour
	* Verifie lit dispo au service service_id / verification
	* $protocole
	* Verifie disponibilité des lits 
	* Envoie true si dispo
	* String avec message d'erreur si indispo
	* FONCTION DE LA CLASSE RESERVATION
	============================================================================
	*/
	function CheckDispoLitInArea ($start_time,$end_time,$service_id,$entry_id=""){
		global $DBName;
		$TableName = $this->NomTableLoc;
		$mem_table_name = "MEM_".$TableName;
		$this->drop_table($mem_table_name);
		$this->CreateMemoryTable($TableName,$DBName);
		$entries = $this->GetEntryInfoDate($start_time,$end_time,$service_id,$entry_id);
				
		$sql_room = "SELECT id from agt_room WHERE service_id='".$service_id."'
						and room_name <> 'Panier ' ORDER BY room_name";
		$room_tab = $this->select($sql_room);
		$bool = $this->InsertDataMemoryTable($TableName,$entries,$room_tab,$service_id);
		$err="";
		if ($bool){
			$i=0;
			$flag=true;
			do{
				if (strlen($this->CheckRoomDispo($room_tab[$i]['id'],$start_time,$end_time,"",$mem_table_name))==0){
					$room_id=$room_tab[$i]['id'];
					//echo "<br>Room place dispo".$room_tab[$i]['id'];
					$flag=false;
				}
				else
					$i++;
			}while ($i<count($room_tab) && $flag==true);
		}
		else{
			$err.="Un problème a eu lieu";
			//echo $err;
			return $err;
		}
		//print_r($this->select('SELECT * FROM MEM_agt_loc'));
		// On supprime la table après avoir obtenu les resultats
		$this->drop_table($mem_table_name);
		if($flag) {
			$err.="pas de place";
			//echo $err;
			return $err;
		}
		else{
			return true;
		}
	}
	
	/*################################################
	 * Memory table
	 * ###############################################*/
	
	
	/*
	============================================================================
	* FONCTION DE LA CLASSE RESERVATION
	Creer une table memory en envoyant le nom de la base et le nom de la table
	* qu'il doit copier
	============================================================================
	*/
		
	function CreateMemoryTable ($TableName,$DataBase="") {

		//$schema = $this->GetSchemaInformation($TableName,$DataBase);
		///$str = $this->PrepareStructureString($schema);
		$str = "room_id int(11),start_time int(11),end_time int(11)";
		$res = $this->CreateTable("MEM_".$TableName,$str,'MEMORY');
	}
	
	/*
	============================================================================
	* FONCTION DE LA CLASSE RESERVATION
	Insert les données dans la table mémory
	* - Recupère les entrées
	* - Check disponibilité dans un lit si le lit est couloir
	* - insère dans le lit
	* Retourne true si le nombre de row dans la table memory
	* correspon au nombre de row dans la table agt_loc
	============================================================================
	*/
	function InsertDataMemoryTable ($TableName,$entries,$room_tab,$service_id) {
		$mem_table_name = "MEM_".$TableName;
		$panier_id = $this->GetPanierIdByServiceId($service_id);
		$nb_entry = count($entries);
		for($i=0;$i<$nb_entry;$i++){
			$room_id = 					$entries[$i]['room_id'];
			$TableData['start_time'] = 	$entries[$i]['start_time'];
			$TableData['end_time'] = 	$entries[$i]['end_time'];
			$TableData['room_id'] = 	$room_id;
			if ($panier_id == $room_id){
				$j=0;
				$flag = true;
				do{
					if (strlen($this->CheckRoomDispo($room_tab[$j]['id'],$entries[$i]['start_time'],
						$entries[$i]['end_time'],"",$mem_table_name))==0){
							
							$TableData['room_id'] = $room_tab[$j]['id'];
							$this->insertion($mem_table_name,$TableData);
							$flag=false;
					}
					else{
						$j++;
					}
				}while ($j<count($room_tab) && $flag==true);
			}
			else {
				$this->insertion($mem_table_name,$TableData);
			}
		}
		$sql_count = "SELECT COUNT(*) as nb_row FROM `".$mem_table_name."`";
		$res = $this->select($sql_count);
		if ($nb_entry == $res[0]['nb_row'])
			return true;
		else
			return false;
	}
	
	/*
	============================================================================
	* FONCTION DE LA CLASSE RESERVATION
	Supprime la table memory
	============================================================================
	*/
	function DropMemoryTable ($TableName){
		$this->drop_table("MEM_".$TableName);
	}
	
	/*################################################
	 *  Fin Memory table
	 * ###############################################*/
	
	
	
	/**============================================================================
	 * FONCTION DE LA CLASSE RESERVATION
	 *  anciennement grrCheckOverlap()
	 *
	 * Dans le cas d'une réservation avec périodicité,
	 * Vérifie que les différents créneaux ne se chevaussent pas.
	 *
	 * $reps : tableau des débuts de réservation
	 * $diff : durée d'une réservation
	 ============================================================================*/
	function CheckOverlap($reps, $diff){
		$err = "";
		for($i = 1; $i < count($reps); $i++) {
			if ($reps[$i] < ($reps[0] + $diff)) {
				$err = "yes";
			}
		}
		if ($err=="")
			return TRUE;
		else
			return FALSE;
	}
	
	/**============================================================================
	 * FONCTION DE LA CLASSE RESERVATION 
	 * grrDelEntryInConflict()
	 *
	 *  Efface les réservation qui sont en partie ou totalement dans le créneau $starttime<->$endtime
	 *
	 * $room_id   - Which room are we checking
	 * $starttime - The start of period
	 * $endtime   - The end of the period
	 * $ignore    - An entry ID to ignore, 0 to ignore no entries
	 * $repignore - A repeat ID to ignore everything in the series, 0 to ignore no series
	 *
	 * Returns:
	 *   nothing   - The area is free
	 *   something - An error occured, the return value is human readable
	 *   if $flag = 1, return the number of erased entries.
	 ============================================================================*/
	function DelEntryInConflict($room_id, $starttime, $endtime, $ignore, $repignore, $flag)
	{
		global $vocab, $dformat;
		$this->CheckTableInit();

		# Select any meetings which overlap ($starttime,$endtime) for this room:
		$sql = "SELECT id FROM ".$this->NomTableLoc." WHERE
			start_time < '".$endtime."' AND end_time > '".$starttime."'
			AND room_id = '".$room_id."'";
		if ($ignore > 0)
			$sql .= " AND id <> $ignore";
		if ($repignore > 0)
			$sql .= " AND repeat_id <> $repignore";
		$sql .= " ORDER BY start_time";

		$row = $this->select($sql);
		if(!$row)
			return grr_sql_error();
		if (count($row) == 0){
			return "";
		}
		# Efface les résas concernées
		$err = "";
		for ($i = 0; $i<count($row); $i++){
			if (getSettingValue("automatic_mail") == 'yes') $_SESSION['session_message_error'] = send_mail($row[$i]['id'],3,$dformat);
			$result = $this->DelEntry(getUserName(), $row[$i]['id'], NULL , 1);
		}
		if ($flag == 1) return $result;
	}
	
	
	/**============================================================================
	 * FONCTION DE LA CLASSE RESERVATION 
	 * anciennement mrbsDelEntry()
	 * Delete an entry, or optionally all entrys.
	 * $user   - Who's making the request
	 * $id     - The entry to delete
	 * $series - If set, delete the series, except user modified entrys
	 * $all    - If set, include user modified entrys in the series delete
	 * Returns:
	 *   0        - An error occured
	 *   non-zero - The entry was deleted
	 ============================================================================*/
	
	function DelEntry($user, $id, $series, $all)
	{
		$this->CheckTableInit();
		$sql = "SELECT id,noip,start_time,end_time FROM ".$this->NomTableLoc." WHERE ";
		$sql .= "id='".$id."'";
		$entry_info = $this->select($sql);
		$removed = 0;
		if (count($entry_info)==1){
			$sql_del = "DELETE FROM ".$this->NomTableLoc." WHERE id='".$entry_info[0]['id']."'";
			$nb_rows_affected = $this->delete_($sql_del);
		}
		if ($nb_rows_affected > 0){
			$removed = $this->CreateLog('Delete',$id,$entry_info[0]);
		}
		$this->delete_("DELETE FROM agt_overload_data WHERE entry_id=" .$entry_info[0]['id']);
		//Traces des supression ou modification RDV par Mohan le 28/03/2013
		return $removed > 0;
	}
	
	/*
	============================================================================
	FONCTION DE LA CLASSE RESERVATION
	function  UpdateEntry($TableName,$TableData) 
	============================================================================
	*/
	function UpdateEntry($TableName,$TableData,$TableCd,$LogName=""){
		$this->CheckTableInit();
		$sql = "SELECT id,noip,start_time,end_time FROM ".$this->NomTableLoc." WHERE ";
		$sql .= "id='".$TableCd['id']."'";
		
		$entry_info = $this->select($sql);
		$removed = 0;
		if (count($entry_info)==1){
			$update_res = $this->update_($TableName,$TableData,$TableCd);
		}
		if($update_res){
			if (strlen($LogName)>1)	
				$update = $this->CreateLog($LogName,$TableCd['id'],$TableData);
			else
				$update = $this->CreateLog('Update',$TableCd['id'],$TableData);
		}
		return $update_res;
	}
	
	
	/*
	============================================================================
	FONCTION DE LA CLASSE RESERVATION
	function  CreateLog($TypeModif,$id,$entry_info)
	============================================================================
	*/
	function CreateLog($TypeModif,$id,$entry_info){
		$Trace_msg="";
		if ($TypeModif == 'Delete'){
			$Trace_msg.="Annulation  - ";
			$Trace_msg.= "Patient : ".$entry_info['noip']." du ".$entry_info['start_time'].
					" (".date('d/m/y H:i',$entry_info['start_time']).") au ".$entry_info['end_time']
					." ( ".date('d/m/y H:i',$entry_info['end_time']).")"; 
		}
		if ($TypeModif == 'Update'){
			$Trace_msg.="Modification  - ";
			$Trace_msg.= "Patient : ".$entry_info['noip']." nouvelle date du ".$entry_info['start_time'].
					" (".date('d/m/y H:i',$entry_info['start_time']).") au ".$entry_info['end_time']
					." ( ".date('d/m/y H:i',$entry_info['end_time']).")"; 
		}
		$sql_log = "insert into agt_log (LOGIN, START,  REMOTE_ADDR, USER_AGENT, REFERER, AUTOCLOSE, END) values (
			'" . $_SESSION['login'] . "',
			'" . date("Y-m-d H:i:s") . "',
			'" . $_SERVER['REMOTE_ADDR'] . "',
			'" . substr($Trace_msg,0,254) . "',
			'" . $_SERVER['HTTP_REFERER'] . "',
			'1',
			'" . $_SESSION['start'] . "'
			);";   
		$result_id = $this->insert($sql_log);		
		$removed++;
		return $removed;
	}
	
	
	/*############################################################
	 * OVERLOAD 
	############################################################*/
	
	
	/**============================================================================
	 * FONCTION DE LA CLASSE RESERVATION 
	 * OverloadGetFieldslist()
	 *
	 * Return an array with all fields name
	 * $id_area - Id of the id_area
	 *
	 ============================================================================*/
	function OverloadGetFieldslist($id_area,$room_id=0)
	{
	  if ($room_id > 0 ) {
		  // il faut rechercher le id_area en fonction du room_id
		  $area_res = $this->select("select service_id from agt_room where id='".$room_id."'");
		  $id_area = $area_res[0]['service_id'];
		  if (!$id_area) {
			  $id_area = "";
		  }
	  }
	  // si l'id de l'area n'est pas précisé, on cherche tous les champs additionnels
	  if ($id_area == "")
		  $sqlstring = "select fieldname ,fieldtype, agt_overload.id, fieldlist, agt_service.service_name,
		   affichage, overload_mail, agt_overload.obligatoire, agt_overload.confidentiel from agt_overload, agt_service
		  where(agt_overload.id_area = agt_service.id) order by fieldname,fieldtype ";
	  else
		  $sqlstring = "select fieldname,fieldtype, id, fieldlist, affichage, overload_mail,
		   obligatoire, confidentiel from agt_overload where id_area='".$id_area."' order by fieldname,fieldtype";
	  $result = $this->select($sqlstring);
	  $field_row = $result;
	  $fieldslist = array();
	  //if (! $result) fatal_error(1, grr_sql_error());
	  $nb_res = count($result);
	 // if ($nb_res<0) fatal_error(1, get_vocab('error_area') . $id_area . get_vocab('not_found'));
	  for ($i = 0; $i<$nb_res; $i++){
		if ($id_area == "") {
		  $fieldslist[$field_row[$i]['fieldname']." (".$field_row[$i]['service_name'].")"]["type"] = $field_row[$i]['fieldtype'];
		  $fieldslist[$field_row[$i]['fieldname']." (".$field_row[$i]['service_name'].")"]["id"] = $field_row[$i]['id'];
		  if (trim($field_row[$i]['fieldlist']) != "") {
			  $tab_list = explode("|", $field_row[$i]['fieldlist']);
			  foreach ($tab_list as $value) {
				  if (trim($value) != "")
					  $fieldslist[$field_row[$i]['fieldname']." (".$field_row[$i]['service_name'].")"]["list"][] = trim($value);
			  }
		  }
		  $fieldslist[$field_row[$i]['fieldname']." (".$field_row[$i]['service_name'].")"]["affichage"] = $field_row[$i]['affichage'];
		  $fieldslist[$field_row[$i]['fieldname']." (".$field_row[$i]['service_name'].")"]["overload_mail"] = $field_row[$i]['overload_mail'];
		  $fieldslist[$field_row[$i]['fieldname']." (".$field_row[$i]['service_name'].")"]["obligatoire"] = $field_row[$i]['obligatoire'];
		  $fieldslist[$field_row[$i]['fieldname']." (".$field_row[$i]['service_name'].")"]["confidentiel"] = $field_row[$i]['confidentiel'];
		 } else {
		  $fieldslist[$field_row[$i]['fieldname']]["type"] = $field_row[$i]['fieldtype'];
		  $fieldslist[$field_row[$i]['fieldname']]["id"] = $field_row[$i]['id'];
		  $fieldslist[$field_row[$i]['fieldname']]["affichage"] = $field_row[$i]['affichage'];
		  $fieldslist[$field_row[$i]['fieldname']]["overload_mail"] = $field_row[$i]['overload_mail'];
		  $fieldslist[$field_row[$i]['fieldname']]["obligatoire"] = $field_row[$i]['obligatoire'];
		  $fieldslist[$field_row[$i]['fieldname']]["confidentiel"] = $field_row[$i]['confidentiel'];
		  if (trim($field_row[$i]['fieldlist']) != "") {
			  $tab_list = explode("|", $field_row[$i]['fieldlist']);
			  foreach ($tab_list as $value) {
				  if (trim($value) != "")
					  $fieldslist[$field_row[$i]['fieldname']]["list"][] = trim($value);
			  }
		  }
		 }
		}
	  return $fieldslist;
	}
	
		
	/***==========================================================================
	 * FONCTION DE LA CLASSE RESERVATION 
	 * EntryGetOverloadDesc()
	 *
	 * Return an array with all additionnal fields
	 * $id - Id of the entry
	 *
	 *==========================================================================*/
	function EntryGetOverloadDesc($id_entry)
	{
	  $room_id = 0;
	  $overload_array = array();
	  $overload_desc = "";
	  // On récupère les données overload desc dans agt_loc.
	  if ($id_entry != NULL) {
		  $room_id = $this->GetRoomIdByEntryId($id_entry);
		  $res= $this->GetAreaIdByRoomId($room_id);
		  $service_id = $res['service_id'];
		  $sql = "SELECT id,entry_id, field_name,field_data FROM agt_overload_data WHERE entry_id='".$id_entry."'";
		  $row = $this->select($sql);
		  for ($i = 0; $i<count($row); $i++) 
		  {
			   $overload_array[$row[$i]['field_name']]["valeur"] = $row[$i]['field_data'];
			   $sql2 = "SELECT id,affichage,overload_mail,obligatoire,confidentiel 
						FROM agt_overload WHERE id_area='".$service_id."' 
						AND fieldname='".$row[$i]['field_name']."'";
				$row2=$this->select($sql2);
				$overload_array[$row[$i]['field_name']]["id"] = 			$row2[0]['id'];
				$overload_array[$row[$i]['field_name']]["affichage"] = 		$row2[0]['affichage'];
				$overload_array[$row[$i]['field_name']]["overload_mail"] = 	$row2[0]['overload_mail'];
				$overload_array[$row[$i]['field_name']]["obligatoire"] = 	$row2[0]['obligatoire'];
				$overload_array[$row[$i]['field_name']]["confidentiel"] = 	$row2[0]['confidentiel'];
		  }
	  }
	  return $overload_array;
	}
	
	
	/***==========================================================================
	 * FONCTION DE LA CLASSE RESERVATION 
	 * GetFieldName(id)
	 * Return the fieldname of the id
	 *==========================================================================*/
	 function GetFieldName ($id_field) {
		$sql = "SELECT fieldname FROM agt_overload WHERE id='".$id_field."'";
		$row = $this->select($sql);
		return $row[0]['field_name'];
	} 

	
	/**==========================================================================
	 * FONCTION DE LA CLASSE RESERVATION
	* InsertOverloadData
	* *==========================================================================*/
	
	function InsertOverloadData($entry_id,$overload_data,$room_id)
	{
	  $overload_fields_list = $this->OverloadGetFieldslist(0,$room_id);

	  foreach ($overload_fields_list as $field=>$fieldtype)
		{
		  $id_field = $overload_fields_list[$field]["id"];
		  $field_name = $this->GetFieldName($id_field);
		  if (array_key_exists($id_field,$overload_data))
		  {
			$sql = "INSERT INTO agt_overload_data (entry_id,field_name,field_data)
						   VALUES ('".$entry_id."','".$field_name."','".$overload_data[$id_field]."')";
			$l_id = $this->insert($sql);
			 if ($l_id < 0){
				 fatal_error(0, grr_sql_error());
				 return 0;
			  }
			$new_id = $l_id;
			if ($moderate==2) moderate_entry_do($new_id,1,"","no");
		  }
		}
	}
	
	/*
	============================================================================
	function GetOverloadData($id)
	$id=entry_id
	============================================================================
	*/
	function GetOverloadData($id)
	{
		$sql="select `entry_id`, group_concat( concat(field_name,':',field_data)  separator '<br />') as  add_data
				from agt_overload_data  
				where entry_id='".$id."' group by `entry_id`";
		$res = parent :: select ($sql);
		return $res[0]['add_data'];		   
			
	}
	
	
	/*############################################################
	 *  FIN OVERLOAD 
	############################################################*/
	
	
	/*========================================================================
	// function GetPatParLit($RoomId,$_date)
	// $RoomId : id du lit
	// $_date : date a laquelle on veut le nombre de lits
	========================================================================*/
	
	function GetPatParLit ($RoomId, $_date,$heure_deb=0,$heure_fin=0) {
		$this->CheckTableInit();
		$day = substr($_date,0,2);
		$month = substr($_date,3,2);
		$year = substr($_date,6,4);
		$dateDebut = mktime($heure_deb, 0, 0, $month  , $day, $year);
		if ($heure_fin == 23) 
				$dateFin = mktime(23, 59, 0, $month  , $day, $year);
		else
				$dateFin = mktime($heure_fin,0,0,$month,$day,$year);
		$req = "SELECT * FROM ".$this->NomTableLoc."
					LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip				
					WHERE room_id ='$RoomId'
					AND statut_entry != 'SUPPRIMER'
					AND (start_time BETWEEN $dateDebut  AND $dateFin 
						OR end_time BETWEEN $dateDebut AND $dateFin
						OR start_time < $dateDebut AND end_time > $dateFin
						)";
 											
		$resultat=parent::select($req);
		return $resultat;
	}

	/*
	============================================================================
	function GetNbPatDay ($area,$compare_to_start,$compare_to_end) {
	Retourne le nb de pat dans l'area entre les deux dates données en param
	============================================================================
	*/
	//day.php	
	function GetNbPatDay ($area,$compare_to_start,$compare_to_end) {
		$this->CheckTableInit();
		$sql = "SELECT agt_room.id,agt_room.room_name, room_alias,start_time, end_time, name, ".$this->NomTableLoc.".id, type, statut_entry, ".$this->NomTableLoc.".description
		   FROM ".$this->NomTableLoc.", agt_room
		   WHERE ".$this->NomTableLoc.".room_id = agt_room.id
		   AND statut_entry != 'SUPPRIMER'
		   AND service_id = '".$area."'
		   AND name not like('ABSENCE%')
		   AND name not like('ABSENT INF.%')
		   AND start_time < ".($compare_to_start)." AND end_time > ".$compare_to_end." ORDER BY start_time";
		$res = parent::select($sql);
//echo $sql;		
		return $res;
	}
	/*
	============================================================================
	function GetNbPatDayExamCompl ($area,$compare_to_start,$compare_to_end) {
	Retourne le nb de pat dans l'area entre les deux dates données en param
	============================================================================
	*/
	//day.php	
	function GetNbPatDayExamCompl ($TableauData) {
		$this->CheckTableInit();
		$compare_to_start 	= $TableauData['compare_to_start']	;
		$compare_to_end 	= $TableauData['compare_to_end']	;
		$service_id 		= $TableauData['service_id']		;
		$MedecinPosMatin	= $TableauData['medecin_pos_matin']	;
		$MedecinPosApm		= $TableauData['medecin_pos_apm']	;
		$sql = "SELECT agt_room.id,agt_room.room_name, room_alias,start_time, end_time, name,
						".$this->NomTableLoc.".id, type, statut_entry, ".$this->NomTableLoc.".description
					   FROM ".$this->NomTableLoc.", agt_room
					   WHERE ".$this->NomTableLoc.".room_id = agt_room.id
					   AND statut_entry != 'SUPPRIMER'
					   AND service_id = '".$service_id."'
					   AND start_time < ".($compare_to_start)." AND end_time > ".$compare_to_end." 
					   AND (plage_pos != ".$MedecinPosMatin." AND plage_pos != ".$MedecinPosApm.")
					   ORDER BY start_time";		
		$res = parent::select($sql);
		return $res;
	}

	/*
	============================================================================
	function GetJour($time)
	Retourne
	============================================================================
	*/
	//day.php
	function GetJour ($time){
		$sql = "SELECT Jours FROM agt_calendrier_jours_cycle WHERE DAY='$time'";
		$res = parent::select($sql);
		if ($res) return $res[0]['Jours'];
		else return -1;
	}

	/*
	============================================================================
	function GetType ($type)
	Retourne
	============================================================================
	*/
	//view_entry.php
	function GetType ($type) {
		$sql = "select type_name from agt_type_area where type_letter='".$type."'";
		$res = parent :: select ($sql);
		return $res[0]['type_name'];
	}

	/*
	============================================================================
	function GetTypeInfoByName ($type_name)
	============================================================================
	*/
	function GetTypeInfoByName ($type_name){
		$sql = "select * from agt_type_area where type_name LIKE '%".$type_name."%'";
		$res = parent :: select ($sql);
		return $res;
	}	
	/*
	============================================================================
	function GetIDOverload($service_id)
	Retourne les id des champs add
	============================================================================
	*/
 
	function GetIDOverload($service_id) {
		$sql="select id from agt_overload where id_area='".$area."' limit 1";
		$res = parent::select($sql);
		return $res;
	}
	


	/*
	============================================================================
	function GetInfoAffichage ($area,$compare_to_start,$compare_to_end) {
	Retourne les informations pour l'affichage dans day.php
	============================================================================
	*/
 
	function GetInfoAffichage ($area,$compare_to_start,$compare_to_end,$tri="") {
		$this->CheckTableInit();
		if($this->NomTableLoc == 'agt_exam_compl' ||$this->NomTableLoc == 'agt_loc'){
			$sql_add.=$this->NomTableLoc.".patient,";
		}
		$sql = "SELECT agt_room.id AS room_id, start_time, end_time, name,de_source,ds_source,uh, protocole, ".$this->NomTableLoc.".id AS entry_id,
					type , ".$this->NomTableLoc.".noip, statut_entry, ".$this->NomTableLoc.".description, ".$this->NomTableLoc.".plage_pos,
					agt_room.room_name,agt_room.room_alias,".$sql_add."
					picture_room, ".$this->NomTableLoc.".nda, agt_pat.nom, agt_pat.prenom, agt_pat.ddn,agt_pat.sex,agt_pat.tel,
					".$this->NomTableLoc.".medecin,".$this->NomTableLoc.".patient
					FROM agt_room,".$this->NomTableLoc."
					LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip					
					WHERE ".$this->NomTableLoc.".room_id = agt_room.id
					AND statut_entry != 'SUPPRIMER'
					AND statut_entry != 'Demande'
					AND service_id = '".$area."'
					AND start_time < ".$compare_to_start."
					AND end_time > ".$compare_to_end." 
					ORDER BY  $tri start_time";
 		//echo $sql;			
		$res = parent :: select ($sql);
		return $res;
	}


	
	/*
	============================================================================
	function GetInfoDemande ($area,$compare_to_start,$compare_to_end) {
	Retourne les informations pour l'affichage dans day.php
	============================================================================
	*/
	function GetInfoDemande ($area,$compare_to_start,$compare_to_end,$tri="") {
		$this->CheckTableInit();
		$sql = "SELECT start_time, end_time, create_by, 
					timestamp, protocole, ".$this->NomTableLoc.".id AS entry_id,
					type , ".$this->NomTableLoc.".noip, statut_entry, ".$this->NomTableLoc.".description,
					".$this->NomTableLoc.".nda,".$this->NomTableLoc.".patient, agt_pat.nom, agt_pat.prenom, agt_pat.ddn,agt_pat.sex,agt_pat.tel,
					".$this->NomTableLoc.".medecin,".$this->NomTableLoc.".patient
					FROM ".$this->NomTableLoc."
					LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip		
					WHERE (statut_entry = 'Demande' OR statut_entry = 'Refuse')
					AND service_id = '".$area."'
					AND start_time < ".$compare_to_start."
					AND end_time > ".$compare_to_end." 
					ORDER BY  $tri start_time";
		$res = parent :: select ($sql);
		return $res;
	}
	
	/*
	============================================================================
	function GetInfoAnnulations ($area,$compare_to_start,$compare_to_end) {
	============================================================================
	*/
	function GetInfoAnnulations ($area,$compare_to_start,$compare_to_end) {
		$this->CheckTableInit();
		$sql = "SELECT agt_room.id AS room_id, start_time, end_time,  protocole, ".$this->NomTableLoc.".id AS entry_id,
					type , ".$this->NomTableLoc.".noip, statut_entry, ".$this->NomTableLoc.".description,
					agt_room.room_name,agt_room.room_alias,".$this->NomTableLoc.".patient,".$this->NomTableLoc.".motif, 
					picture_room, ".$this->NomTableLoc.".nda, agt_pat.nom, agt_pat.prenom, agt_pat.ddn,agt_pat.sex,agt_pat.tel,
					".$this->NomTableLoc.".medecin,".$this->NomTableLoc.".patient
					FROM agt_room,".$this->NomTableLoc." 
					LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip							
					WHERE ".$this->NomTableLoc.".room_id = agt_room.id
						AND statut_entry = 'ANN'
						AND agt_room.service_id = '".$area."'
						AND start_time < ".$compare_to_start."
						AND end_time > ".$compare_to_end." 
					ORDER BY  start_time";
		$res = parent :: select ($sql);
		return $res;
	}	
	/*
	============================================================================
	function GetInfoDemandeById ($entry_id)
	Retourne les infos sur les reservations
	============================================================================
	*/
	function GetInfoDemandeById ($demande_id) {
		$this->CheckTableInit();
		$sql = "SELECT 	".$this->NomTableLoc.".*,
						agt_service.urm,
						agt_service.service_name,
						agt_service.id as service_id,
						agt_service.nom_formulaire,	
						agt_pat.nom,
						agt_pat.prenom,
						agt_pat.ddn,
						agt_pat.sex
				FROM agt_service,".$this->NomTableLoc."
				LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip				
				WHERE ".$this->NomTableLoc.".service_id = agt_service.id 
				AND ".$this->NomTableLoc.".id='".$demande_id."'";
		$res = parent :: select ($sql);
		return $res[0];	
	}	
 	
	/*
	============================================================================
	function GetMedecinInfoByUrm ($urm)
	Retourne les infos sur les medecins en fonction de l'urm
	============================================================================
	*/
	//edit_entry.php
	function GetMedecinInfoByUrm ($urm) {
		$sql = "select titre,nom,prenom from agt_medecin where service= '$urm' order by nom";
		$res = parent::select($sql);
		return $res;
	}
	
	/*
	============================================================================
	function GetMedecinInfo ($medecin)
	Retourne les infos sur le medecin
	============================================================================
	*/
	function GetMedecinInfo ($medecin){
		$sql = "SELECT * FROM agt_medecin WHERE nom='".$medecin."'";
		$res = $this->select($sql);
		return $res;
	}

	/*
	============================================================================
	function GetMedecinById ($medecin)
	Retourne le nom du medecin
	============================================================================
	*/
	function GetMedecinById ($id){
		$sql = "SELECT * FROM agt_medecin WHERE id_medecin='".$id."'";
		$res = $this->select($sql);
		return $res[0]['nom'];
	}	
	
	/*
	============================================================================
	function GetspecialiteByMedecin ($medecin)
	Retourne le nom du medecin
	============================================================================
	*/
	function GetspecialiteByMedecin ($id){
		$sql = "SELECT * FROM agt_medecin WHERE id_medecin='".$id."'";
		$res = $this->select($sql);
		return $res[0]['specialite'];
	}	
	
	/*
	============================================================================
	function GetMedecinById ($medecin)
	Retourne le nom du medecin
	============================================================================
	*/
	function GetInfoMedecinById ($id,$table=""){
		$table = (strlen($table)>0)?$table:"agt_medecin";
		$sql = "SELECT * FROM ".$table." WHERE id_medecin='".$id."'";
		$res = $this->select($sql);
		return $res[0];
	}
	
	/*
	============================================================================
	function GetMedecinInfoByValRech ($param,$table)
	Retourne medecins info
	============================================================================
	*/
	function GetMedecinInfoByValRech ($param,$table){
		$sql = "SELECT *  from ".$table."
					WHERE nom LIKE '%".$param."%'
					OR prenom LIKE '%".$param."%'
					ORDER by nom";
		$res = $this->select($sql);
		return $res;
	}
	
	
	/*
	============================================================================
	function GetMedecinInfoByNomPrenom ($medecin)
	Retourne medecins info
	============================================================================
	*/
	function GetMedecinInfoByNomPrenom ($medecin) {
		$res = explode(" ", $medecin);
		$nom 	= $res[0];
		$prenom = $res[1];
		$sql = "SELECT *  from agt_medecin
					WHERE nom LIKE '%".$nom."%'
					AND prenom LIKE '%".$prenom."%'
					ORDER by nom";
		$res = $this->select($sql);
		return $res;
	}
		

	/*
	============================================================================
	function GetInfoLog ()
	============================================================================
	*/
	function GetInfoLog () {
		$sql = "SELECT DISTINCT login, nom, prenom FROM 
		agt_utilisateurs WHERE  (etat!='inactif' and statut!='visiteur' ) order by nom, prenom";
		$res = parent::select($sql);
		return $res;
	}
	
	/*
	============================================================================
	function InsererProtocole ($service_id)
	Insère les protocoles dans le service
	============================================================================
	*/
	// fonction qui permet d'inserer les protocoles dans la base afin de pouvoir afficher le champs protocole dans les services
	function InsererProtocole ($service_id) {
			$sql = "INSERT INTO agt_overload (`id_area`,`fieldname`,`fieldtype`,
								`obligatoire`,`affichage`,`confidentiel`,`overload_mail`)
					VALUES	('".$service_id."','Protocole','text','y','y','n','n')";
			$insertion = parent::insert($sql);
		}
	
	
	/*
	============================================================================
	function GetWeekInfo ($service_id, $date_start, $date_end) 
	============================================================================
	* */
	function GetWeekInfo ($service_id, $date_start, $date_end) {
		$this->CheckTableInit();
		if($this->NomTableLoc == 'agt_exam_compl' || $this->NomTableLoc == 'agt_loc' ){
			$sql_add.=$this->NomTableLoc.".patient,";
		}
		$sql = "SELECT start_time, end_time,nda, ".$this->NomTableLoc.".noip, type, ".$this->NomTableLoc.".id as entry_id, name, agt_room.id as room_id,type, 
					statut_entry, ".$this->NomTableLoc.".description,agt_room.delais_option_reservation, agt_pat.nom,agt_pat.prenom,".$sql_add." agt_pat.ddn
					,date_format(start_time,'%d/%m/%Y') as dt,agt_pat.sex,".$this->NomTableLoc.".patient,agt_room.room_name,medecin,protocole
				FROM  agt_room, agt_service,".$this->NomTableLoc."
				LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip		
				   where ".$this->NomTableLoc.".room_id=agt_room.id and
				   agt_service.id = agt_room.service_id and
				   agt_service.etat='n' and 
				   statut_entry !='SUPPRIMER' and
				   agt_service.id = '".$service_id."' and
				   start_time <= $date_end AND
				   end_time > $date_start
				   ORDER by start_time, end_time, ".$this->NomTableLoc.".id";
		$res = parent :: select ($sql);
		return $res;		   
	}
	
	
	
	/*
	============================================================================
	function GetServiceIdByRoomId ($room_id) 
	//year.php
	============================================================================
	*/
	function GetInfoYear ($month_start,$month_end,$service_id) {
		$this->CheckTableInit();
		$sql = "SELECT start_time, end_time,".$this->NomTableLoc.".id as entry_id, 
				name,room_name, statut_entry, 
				".$this->NomTableLoc.".description, 
				agt_room.delais_option_reservation, type
				   FROM ".$this->NomTableLoc." inner join agt_room on ".$this->NomTableLoc.".room_id=agt_room.id
				   WHERE (start_time <= $month_end 
						AND end_time > $month_start 
						AND service_id='".$service_id."'
						AND statut_entry !='SUPPRIMER')
				   ORDER by start_time, end_time, agt_room.room_name";
		$res = parent :: select ($sql);
		return $res;	   
	}
	
	/*
	============================================================================
	function GetWeekEntryInfo ($room_id,$week_start,$week_end) 
	
	============================================================================
	*/
	function GetWeekEntryInfo ($room_id,$week_start,$week_end) {
		$this->CheckTableInit();	
		if($this->NomTableLoc == 'agt_exam_compl'){
			$sql_add.=$this->NomTableLoc.".patient, plage_pos,";
		}	
		$sql = "SELECT start_time, end_time,".$this->NomTableLoc.".noip, type, name, id, nda,
					statut_entry, description,agt_pat.nom,agt_pat.prenom,".$sql_add."
					agt_pat.ddn,medecin,protocole
				   FROM ".$this->NomTableLoc."
				   LEFT JOIN agt_pat ON ".$this->NomTableLoc.".noip = agt_pat.noip		
				   WHERE room_id=".$room_id."
				   AND statut_entry != 'SUPPRIMER'
				   AND start_time < ".($week_end)." AND end_time > ".$week_start." 
				   ORDER BY start_time";
		$res = parent::select($sql);
		return $res;
	}
	

	/*
	============================================================================
	function GetProtocolesByUrm($urm)
	Retourne les uh du service
	============================================================================
	*/
	function GetProtocolesByUrm($urm) 
	{
		$sql="select protocole, duree
				from agt_protocole  
				where service='".$urm."'";
		$res = parent :: select ($sql);
		return $res;
	}
	
	/*
	============================================================================
	function GetProtocoleById($id_protocole)
	============================================================================
	*/
	function GetProtocoleDureeById($id_protocole)
	{
		if (strlen($id_protocole)>0)
		{
			$sql="select duree
				from agt_protocole  
				where id_protocole='".$id_protocole."'";
			$res = parent :: select ($sql);
			return $res;
		}
	}
	
	/*
	============================================================================
	function GetProtocoleInfoByName($protocole,$table)
	============================================================================
	*/
	function GetProtocoleInfoByName($protocole,$table)
	{
		if (strlen($protocole)>0)
		{
			$sql="SELECT id_protocole, protocole, duree from ".$table."
						WHERE protocole LIKE '%".$protocole."%'			
						ORDER by protocole";
			$res = parent :: select ($sql);
			return $res;
		}
	}
	
	/*
	============================================================================
	function GetInfoPat($noip)
	Retourne les uh du service
	============================================================================
	*/
	function GetInfoPat($noip)
	{
		$sql="select *
				from agt_pat  
				where noip='".$noip."'";
		$res = parent :: select ($sql);
		return $res[0];
	}

	/*
	============================================================================
	function GetEntriesByNda($nda)
	Retourne l'ensemble des séjours au nda associé
	============================================================================
	*/
	function GetEntriesByNda ($nda) {
		$this->CheckTableInit();
		$sql = "select * from ".$this->NomTableLoc." where nda='".$nda."' order by start_time";
		$res = parent::select($sql);
		return $res;
	}
	

	/*
	============================================================================
	function CreateSingleEntry($nomtable,$data) 
	$nomTable : nom de la table 
	* $data = tableau associatif avec clé=nom du champs et val=valeur du champs 
	============================================================================
	*/
	function CreateSingleEntry($nomTable="",$data="")
	{
		$insert = parent::insertion($nomTable,$data);
		return $insert;
	}

	
	
		
	/*
	============================================================================
	* 	function GetUserInfo ($login){
	============================================================================
	*/
	function GetUserInfo ($login){
		$sql = "SELECT * FROM agt_utilisateurs WHERE login = '".$login."'";
		$res = $this->select($sql);
		return $res;
	}	
	
	
	/*
	============================================================================
	GetResrvationTypes ($Service_id )
	Retourne les coleur informations dans l'area
	* pour divan pourquoi le second boucle for ici ?
	============================================================================
	*/
	function GetResrvationTypes ($Service_id ){

		$sql = "SELECT DISTINCT t.type_name as type_name, t.type_letter as type_letter, t.id as id ,t.couleur as color
						FROM agt_type_area t
						LEFT JOIN agt_j_type_area j on j.id_type=t.id
						WHERE (j.id_area  IS NULL or j.id_area != '".$Service_id."')
						ORDER BY t.order_display";
		$res = parent :: select ($sql);
		$cpt=0;
		for($i=0;$i<count($res);$i++){
			$sql_type = "select id_type from agt_j_type_area where id_type = '".$res[$i]['id']."' and id_area='".$Service_id."'";
			$res_type = parent :: select ($sql_type);
			if (count($res_type)<1 || empty($res_type)) {
				$TabRes[$cpt]['type_letter']	= $res[$i]['type_letter'];
				$TabRes[$cpt]['0']				= $res[$i]['type_letter'];
				$TabRes[$cpt]['type_name'] 		= $res[$i]['type_name'];
				$TabRes[$cpt]['1']				= $res[$i]['type_name'];
				$TabRes[$cpt]['id']				= $res[$i]['id'];
				$TabRes[$cpt]['2']				= $res[$i]['id'];
				$TabRes[$cpt]['color']			= $res[$i]['color'];
				$TabRes[$cpt]['3']				= $res[$i]['color'];
				$cpt++;
			}
		}
		return $TabRes;
	}
	
	

	/*
	============================================================================
	*  function EstEnRetard ($start_time,$end_time,$pixel_cfg="")
	*  return la duree de decalage du retard si il y a retard
	*  false si il n'y a pas de retard
	============================================================================
	*/

	function EstEnRetard($start_time,$end_time,$pixel_cfg="")
	{
		//echo date("H:i:s",$start_time);
		$duree_init = ($end_time - $start_time)/60; // en minutes
		$px_init = ($duree_init*$pixel_cfg)/60;
		//1 heure = 18px => faire un fichier config si modification ex : affichage 30 min
		$tab_res=array();
		if ($start_time < time())	
		{
			$duree = (time() - $start_time)/60; // minutes
			if ($duree>$duree_init)
				$duree = $duree_init;
			$tab_res['duree']=$duree;
			$px = ($duree*$pixel_cfg)/60;
			if($px>$px_init) $px=$px_init;
			$tab_res['px'] = $px;
			return $tab_res;
		}
		else
			return false;
	}
	/*
	============================================================================
	* 	function DepasseHeurePrevu ($start_time,$end_time,$pixel_cfg="")
	============================================================================
	*/
	
	function DepasseHeurePrevu ($start_time,$end_time,$pixel_cfg="")
	{
		$duree_init = ($end_time - $start_time)/60; // en minutes
		$px_init = ($duree_init*$pixel_cfg)/60;
		//1 heure = 18px => faire un fichier config si modification ex : affichage 30 min
		$tab_res=array();
		if ($end_time <= time())	
		{
			$duree = (time() - $end_time)/60; // minutes
			$tab_res['duree'] = $duree;
			$px = ($duree*$pixel_cfg)/60;
			//if($px<1) $px=1;
			if($px>$px_init) $px=$px_init;
			$tab_res['px'] = $px;
			return $tab_res;
		}
		else
			return false;
	}
 
	/*
	============================================================================
	function UpdateDescriptionFromId ($entry_id,$var,$val,$lib) {
	============================================================================
	*/
	function UpdateDescriptionFromId ($entry_id,$var,$val,$lib) {
		$this->CheckTableInit();

		$sql = "SELECT ".$this->NomTableLoc.".description
			  	  FROM ".$this->NomTableLoc."
						WHERE ".$this->NomTableLoc.".id='".$entry_id."'";
		
		$res =parent :: select ($sql);
		if($res[0]['description'])
			$CompData=JsonDecode($res[0]['description'],true);
		$CompData[$var]['Libelle']=$lib;
		$CompData[$var]['Valeur']=$val;
		$CompData=JsonEncode($CompData);

		$sql="update ".$this->NomTableLoc." set ".$this->NomTableLoc.".description='".$CompData."' where ".$this->NomTableLoc.".id='".$entry_id."'";
	
		$res =parent :: update ($sql);

	}		
	/*
	============================================================================
	function GetDescComplementaire($Description)
	le description complementaire est un array JSON
	decode le et envoyer
	ATTN: cette variable comporte les donnes additional et descriotion complemenaire
	
	============================================================================
	*/
	function GetDescComplementaire ($DataJson,$DescriptionComplementaireOnly=false)
	{
		
		$MotCleDescCompl="DESC___COMPL"; //dans reservation ,utilisé pour stoker les donnes complementaires
		$DataPhp=JsonDecode($DataJson,true);
 
		if($DescriptionComplementaireOnly)
		{
			return $DataPhp[$MotCleDescCompl]['Valeur'];

		}else{
			if(is_array($DataPhp))
			{
				foreach($DataPhp as $key=>$val){
					if ($key==$MotCleDescCompl)
						$DescCompl=$val['Valeur']."<br>";
					else{
						if(is_array($DataPhp))
						{
							//echo "<br>'".$val['Valeur']."'".strlen($val['Valeur']);
							if((strlen($val['Valeur']) >0) && ($val['Valeur']!= "99"))
								$retval.="\n  ".$val['Libelle']." :".$val['Valeur']."<br>";	
						}else
						{
							if((strlen($val['Valeur']) >0) || ($val['Valeur']!=99))
								$retval.="\n  ".$key." :".$val."<br>";	
						}
					}
				}
			}
		}
		return $DescCompl." ".$retval;
	}	


	/*==========================================================================
	 * FONCTION DE LA CLASSE RESERVATION
	 *  GetEntryInfo()
	 *
	 * Get the booking's entrys
	 *
	 * @param integer $id : The ID for which to get the info for.
	 * @return variant    : nothing = The ID does not exist
	 *    array   = The bookings info
	 *========================================================================*/
	/*function GetEntryInfo($id)
	{
		$sql = "SELECT *
			   FROM ".$this->NomTableLoc."
			   WHERE id = '".$id."'";
		$res = $this->select($sql);
		return $res;
	}*/
	
	
	 /**FONCTION DE LA CLASSE RESERVATION 
	  * mrbsCreateSingleEntry()
	 *
	 * Create a single (non-repeating) entry in the database
	 * $starttime   - Start time of entry
	 * $endtime     - End time of entry
	 * $entry_type  - Entry type
	 * $repeat_id   - Repeat ID
	 * $room_id     - Room ID
	 * $type        - Type (Internal/External)
	 * $description - Description
	 * $rep_jour_c - Le jour cycle d'une réservation, si aucun 0
	 * Returns:
	 *   0        - An error occured while inserting the entry
	 *   non-zero - The entry's ID
	 */
	/*function mrbsCreateSingleEntry($starttime, $endtime,$room_id,$creator, $type, $description,$noip,$medecin,$uh,$protocole)
	{
		$endtime=$endtime-1; //par mohan pour gerer les conflit de prochane reservation
	   $sql = "INSERT INTO agt_loc (start_time,end_time, room_id,
										  create_by, type, description,statut_entry,noip,medecin,uh,protocole)
								VALUES ($starttime, $endtime,$room_id,
										'".protect_data_sql($creator)."', '".protect_data_sql($type)."',
										 '".protect_data_sql($description)."', '','".$noip."','".$medecin."','".$uh."','".$protocole."')";
		$sql_ins = $this->insert($sql);
		if ($sql_ins< 0) {
			 fatal_error(0, grr_sql_error());
			 return 0;
		 }
		// s'il s'agit d'une modification d'une ressource déjà modérée et acceptée : on met à jour les infos dans la table agt_loc_moderate
		$new_id = $sql_ins;
		if ($moderate==2) moderate_entry_do($new_id,1,"","no");
	}*/


	/*
	============================================================================
	FONCTION DE LA CLASSE RESERVATION
	// Transforme $dur en un nombre entier
	// $dur : durée
	// $units : unité
	============================================================================
	*/
	
	function toTimeString(&$dur, &$units)
	{
		if($dur >= 60)
		{
			$dur = $dur/60;

			if($dur >= 60)
			{
				$dur /= 60;

				if(($dur >= 24) && ($dur % 24 == 0))
				{
					$dur /= 24;

					if(($dur >= 7) && ($dur % 7 == 0))
					{
						$dur /= 7;

						if(($dur >= 52) && ($dur % 52 == 0))
						{
							$dur  /= 52;
							$units = "années";
						}
						else
							$units = "weeks";
					}
					else
						$units = "jours";
				}
				else
					$units = "heures";

			}
			else
				$units = "minutes";
		}
		else
			$units = "secondes";
	}		
	
	
	/*
	============================================================================
	FONCTION DE LA CLASSE RESERVATION
	// Transforme $dur en un nombre entier
	// $dur : durée
	// $units : unité
	============================================================================
	*/
	
	function GetDureeUnit($dur)
	{
		$tab = array();
		if($dur >= 60)
		{
			$dur = $dur/60;

			if($dur >= 60)
			{
				$dur /= 60;

				if(($dur >= 24) )
				{
					$dur /= 24;
					$units = "jours";
				}
				else
					$units = "heures";

			}
			else
				$units = "minutes";
		}
		else
			$units = "secondes";
			
		$tab['duree'] = $dur;
		$tab['units'] = $units;
		return $tab;
	}
			
	/*####################################################################################################################
	 * =========================================================================
	 * Fonctions de la classe Gilda To Aghate
	  * =======================================================================*
	 *####################################################################################################################*/
	
	
	/*
 	=================================================================================================
 	function IsPlaceLibre($RoomId,$DebUnixTime,$FinUnixtime)
 	$RoomId 				: room a vérifier
 	$DebUnixTime			: debtime
 	$FinUnixtime 			: Fin time
 	$NIP 					: Nip a exclure dans le recherche 
 	$ID 					: ID a excure dans le recherche
 	return 
 		vide : si le palce libre 
 		tableau  : place occupé 
 	=================================================================================================
 	*/
	function IsPlaceLibre($RoomId,$DebUnixTime,$FinUnixtime,$NIP="",$ID="")
	{	
		$this->CheckTableInit();
		// Exclure NIP
		if (strlen($NIP) >0)
			$CondNip=" AND noip !='".$NIP."' ";

		// Exclure ID 
		if (strlen($ID) >0)
			$CondId=" AND id !='".$ID."' ";

		
		$sql_chk="SELECT ".$this->NomTableLoc.".id,noip
							FROM ".$this->NomTableLoc.",agt_room 
							WHERE room_id =  '".$RoomId."' 
							AND agt_room.id=".$this->NomTableLoc.".room_id
							AND agt_room.room_name !='Panier'
							AND statut_entry != 'SUPPRIMER'
			 				AND (start_time BETWEEN $DebUnixTime AND $FinUnixtime 
			 				OR  end_time BETWEEN $DebUnixTime AND $FinUnixtime
			 				OR  (start_time < '".$FinUnixtime."' AND end_time > '".$DebUnixTime."'))
			 				$CondNip
			 				$CondID
			 				ORDER BY room_id ";			
		//echo "<br>"	 .$sql_chk;				
		$res_chk=$this->select($sql_chk);		
		return $res_chk ;

	}
	
	
	/*
 	=================================================================================================
 	function InsertConvocation($TableName,$TableauDonnee)
 	* Insère les données du tableau $TableauDonnee dans la table $TableName
 	* $TableauDonnee est  un tableau clé=> valeur:
 	* 	clé = nom de la colonne dans la table
 	=================================================================================================*/
	function InsertConvocation($TableName,$TableauDonnee)
	{	 
			$id = $this->insertion($TableName,$TableauDonnee);	
			return $id;
	}
	
	
	/*
 	=================================================================================================
 	function CheckPatientPresent($nip)
 	* Verifie si le patient est dans agt_pat 
 	* 	return true si il l'est
 	* 	false sinon
 	=================================================================================================
 	*/
 	
	function CheckPatientPresent ($nip) 
	{
		$sql = "SELECT * FROM agt_pat WHERE noip='".$nip."'";
		$res_pat = $this->select($sql);
		$nb_pat = count($res_pat);
		if ($nb_pat > 0) return true;
		else return false;
	}

	/*
 	=================================================================================================
 	function CheckMedecinPresent($TabData) 
 	* Verifie si le patient est dans agt_pat 
 	* 	return true si il l'est
 	* 	false sinon
 	* pour divan par mohan fonction not clear il faut décrire pourquoi cette 
 	=================================================================================================
 	*/
 	
	function CheckMedecinPresent($TabData) 
	{
		$sql = "SELECT * FROM agt_exam_compl WHERE patient LIKE '%".$TabData['patient']."%' and
					room_id = ".$TabData['room_id']." and plage_pos = ".$TabData['plage_pos']." 
					AND (start_time < ".$TabData['end_time']." AND end_time > ".$TabData['start_time'].")";
		$res = $this->select($sql);
		$nb = count($res);
		if ($nb > 0) return true;
		else return false;
	}	
	/*
 	=================================================================================================
 	function CheckSejourPresent($NIP,$DateDeb,$Service_id)
 		returns les  sejours present dans ".$this->NomTableLoc."
 		a partir d'un date données 
 		+- une huere d'interval 
 	=================================================================================================
 	*/
	function CheckSejourPresent($NIP,$DateDeb,$Service_id)
	{	
		$this->CheckTableInit();
		
		$date_tmp=date("dmY",$DateDeb);
		$DateDeb_ = $DateDeb - (60*60*24*2); // -2 jous 
		$DateFin_ = $DateDeb + (60*60*24*2); // +2 jous 	
		
		$sql_chk="SELECT ".$this->NomTableLoc.".*,service_id,agt_room.room_name
					FROM  ".$this->NomTableLoc.",agt_room
					WHERE ".$this->NomTableLoc.".room_id=agt_room.id
					AND statut_entry != 'SUPPRIMER' 
					AND ds_source !='Gilda'
					AND noip='$NIP' 
					AND start_time=$DateDeb
					AND agt_room.service_id='".$Service_id."'";

		$res=$this->select($sql_chk);						
		if(count($res) > 0)				
			return $res;
		else{
			//patients programé et admis dans gilda +- 1 heure ou dans la journée	
			$sql_chk="SELECT ".$this->NomTableLoc.".*,service_id,agt_room.room_name
						FROM  ".$this->NomTableLoc.",agt_room
						WHERE ".$this->NomTableLoc.".room_id=agt_room.id
						AND statut_entry != 'SUPPRIMER' 
						AND noip='$NIP' 
						AND ds_source !='Gilda'						
						AND ( (from_unixtime(start_time,'%d%m%Y')='".$date_tmp."' )
							  OR (start_time between $DateDeb_ AND $DateFin_  )
							)
					AND agt_room.service_id='".$Service_id."' ORDER BY start_time  ";		
			$res=$this->select($sql_chk);						
		echo "<br>Seconde requette to vérifie si le patient est programé, nbr pat programé trouvé :".count($res)."<br>";								
		}	

		return $res;
	}
	
	
	/*========================================================================
	// function CheckEndTime($end_time)
	// paramètre : $end_time : end_time a a verifie
	* 	Fonction qui verifie si end_time est la date courante (aujourdhui) return true si vrai
	* 	false sinon
	========================================================================*/	
	function CheckEndTime ($end_time) 
	{
		list($jour,$mois,$annee)= explode ("/",date("d/m/Y"));
		$date_fin_start = mktime(0,0,0,$mois,$jour,$annee);
		$date_fin_end = mktime(23,59,59,$mois,$jour,$annee);
		if ($end_time >= $date_fin_start AND $end_time <= $date_fin_end)
			return true;
		else
			return false;
	}
	
	/*
 	=================================================================================================
 	function InsertPatient($Tableau)
 	* Insère le patient dans agt_pat
 	* Fait d'abord une vérification avec la fonction CheckPatientPresent  
 	=================================================================================================
 	*/
 	
	function InsertPatient ($PatData) 
	{
		if(!$this->CheckPatientPresent($PatData['NOIP']))
		{
			$sql = "INSERT INTO agt_pat set 
							noip       ='".$PatData['NOIP']."',     
							nom        ='".$PatData['NMMAL']."',    
							prenom     ='".$PatData['NMPMAL']."',   
							ddn        ='".$PatData['DANAIS']."',   
							sex        ='".$PatData['CDSEXM']."',   
							tel        ='".$PatData['NOTLDO']."'";
			$insert_pat = $this->insert($sql);
			$this->AddTrace("| Patient inseré "); 
		}
		else
		{
			$this->AddTrace("| Patient déja present "); 
		}
	}
	
	
		

	/*
 	=================================================================================================
 	function IsServiceDateValide($dfvali)
 	
 	$dfvali : date fin validité
 	check si le service est encore valide
 	return :
 		true : si le service est encore a jour
 		FALSE : sinon
 	=================================================================================================
 	*/
	function IsServiceDateValide ($dfvali)
	{
		list($jour,$mois,$annee)= explode ("/",date("d/m/Y"));
		$date_today = mktime(0,0,0,$mois,$jour,$annee);
		list($annee_df,$mois_df,$jour_df)= explode ("-",substr($dfvali,0,10));// pour Mysql
 
		$date_fin_valid = mktime(0,0,0,$mois_df,$jour_df,$annee_df);
		
		if ($annee_df > $annee)
			return true;
		elseif ($date_fin_valid >= $date_today)
			return true;
		else
			return false;
	}
	
	/*========================================================================
	// function PrepareArray($loc_info)
	* $loc_info = resultat renvoyé par la fonction GetLocTab
	* Prépare un tableau pour l'insertion,mise a jour dans InsertLocConvocation
	* ========================================================================*/
	function PrepareArray ($loc_info)
	{
		$loc_tab = array();
		for ($i=0;$i<count($loc_info);$i++)
		{
			$loc_tab[$loc_info[$i]['NOIP']] =	$loc_info[$i];
		}
		return $loc_tab ;
		
	}

	/*========================================================================
	// function UpdateConvocation($TableName,$TableauDonnee,$TableauCondition)
	// paramètre : $TableName : id_programmation
					* $TableauDonnee : tableau de données
					* $TableauCondition : tableau de conditions
		Met a jour $TableName avec les données de $TableauDonnee et les conditions
		* du  $TableauCondition
	========================================================================*/
	function UpdateConvocation($TableName,$TableauDonnee,$TableauCondition)
	{
		$res = $this->update_($TableName,$TableauDonnee,$TableauCondition);
		if($res)
			$this->AddTrace(" succes update |"."\n");
		else
			$this->AddTrace(" echec update |"."\n");
		
	}
	
	
	/*========================================================================
	// function InsertIdp($idp)
	* ========================================================================*/
	function InsertIdp ($idp)
	{
		for($i=0;$i<count($idp);$i++)
		{
			$room_info = $this->GetRoomInfo($idp[$i]['NOLIT'],$service_id);
			$TableauIdp['room_id'] = 	$room_info['id'];
			list($year,$month,$day)= explode('-',$idp[$i]['DDVALI']);
			$start_time_idp = mktime(0,0,0,$month,$day,$year);
			$TableauIdp['start_time_idp']= $start_time_idp;
			list($year,$month,$day)= explode('-',$idp[$i]['DFVALI']);
			$end_time_idp = mktime(0,0,0,$month,$day,$year);
			$TableauIdp['end_time_idp']=$end_time_idp;
			$id = $this->insertion('agt_room_idp',$TableauIdp);	
		}
	}
	
	
	/*
	===================================================
	Insertion Panier
	* Pour chaque service, un lit Panier est ajouter
	* afin d'effectuer les programmations
	===================================================
	*/
	function InsertPanier ()
	{
		$sql = "SELECT * FROM agt_service";
		$res = $this->select($sql);
		for ($i=0 ; $i<count($res); $i++)
		{
			$service_id = $res[$i]['id'];
			
			//check presence d'un Panier déja pour cette service
			if ($this->IsExistRoom('Panier',$service_id))
			{
				$sql_ins = "";
				$sql_ins = "INSERT INTO `agt_room` (service_id,room_name,room_alias, capacity, max_booking, statut_room,
												show_fic_room, delais_max_resa_room, delais_min_resa_room ,
												allow_action_in_past, dont_allow_modify, order_display,
												delais_option_reservation, type_affichage_reser, moderate,
												qui_peut_reserver_pour, active_ressource_empruntee)
						VALUES  ('".$service_id."','Panier','Panier','0','-1','1','n','-1', '0', 'n', 'n',
								'0', '0', '0', '0', '2', 'n');";
				$this->insert($sql_ins);
				$this->AddTrace("Panier inseré au service :" .$res[$i]['service_name']." , id_service :".$service_id."\n");
				$cpt_r++;
			}else{
				$this->AddTrace("Panier present déjà pour le service :" .$res[$i]['service_name']." , id_service :".$service_id."\n");
			}
		}
	}
	
	
	/*
	========================================================================
	function AddDay ($date,$nbjours)
		Ajoute des jours à la date $date(format timestamp)
	========================================================================
	*/	
	function AddDay ($date,$nbjours)
	{
		return $date+($nbjours*60*60*24);
	}
	
	/*========================================================================
	// function MakeDate($date_p,$heure_p)
	// paramètre : 
	* $date_p : date dd/mm/yy
	* $heure_p : HHHH
	* construit la date et retourne le format timestamp
	* ========================================================================*/
	function MakeDate ($date_p,$heure_p)
	{
		$date_hr = array();
		if(strrpos($date_p, "/"))
			list($jour,$mois,$annee)=explode("/",$date_p); // dd/mm/yyyy
		else	
			list($annee,$mois,$jour)=explode("-",$date_p); // YYYY-mm-dd
		$heure_p	= "0000".$heure_p;
		$heure_p	= substr($heure_p,strlen($heure_p)-4,4);
		$heure		= substr($heure_p,0,2);
		$min		= substr($heure_p,2,2);                    

		$date_		= mktime($heure,$min,0,$mois,$jour,$annee);
		$date_hr 	= array("jour"=>$jour,"mois"=>$mois,"annee"=>$annee,"heure"=>$heure,
						  "min"=>$min,"timestamp"=>$date_);
		return $date_;
	}
	
	/*
	==========================================================================
	Function AddTrace($msg,$OptionPrint)
	Add les traces dans un chaine de char et/ou print sur l'ecran
	==========================================================================
	*/
	function AddTrace($msg)
	{
		$this->Trace .= $msg ;
		if($this->AffcheTraceSurEcran)
			echo str_replace("\n","<br>",$msg);
	}
	
	/*
	==========================================================================
	Initialise le fichier de trace 
	* Crée un fichier de trace avec le nom de la classe
	* et la date et l'heure courrante
	==========================================================================
	*/
	function init_trace_file ()
	{
		$this->Trace.= "\n"."Debut a ".date('d-m-Y H:i:s')."\n";
		$this->FileName = 'AutomateAghate_'.date("Y.m.d.h.i.s").'.txt';
		$this->FilePath = './trace/'.$this->FileName;
		touch($this->FilePath);
	}
	
	/*
	==========================================================================
	Ecrit dans le fichier de trace 
	* Ecrit toutes les données enregistrées lors des traitements
	* dans le fichier crée précedemment
	==========================================================================
	*/
	function write_trace_file()
	{
		$file = fopen($this->FilePath, 'a+');
		fputs($file,date('d-m-Y H:i:s')."\n");
		$this->Trace.="\n"."Fin a ".date('d-m-Y H:i:s');
		fputs($file,$this->Trace."\n");
		$this->Trace = "";
	}
	
	/*
	==========================================================================
	function PrepareFromCsvArray($FileName,$FilePath)
	==========================================================================
	*/
	function PrepareFromCsvArray($FileName,$FilePath,$KeyName,$ValueName)
	{
		$CurrentFile = $FilePath.$FileName;
		$CsvArray = array();
		if (is_file($CurrentFile)){
			if ($handle = fopen($CurrentFile, "r")){
				$count=0;
				while ($Data=fgetcsv($handle,2048, ";")) {
					switch($count)
					{
						case 0:
							$this->ColNames=$Data;
							$NbrColNames=count($this->ColNames);
							$KeyIndice	= $this->GetIndiceFromNom($KeyName);
							$ValueIndice= $this->GetIndiceFromNom($ValueName);
							break;
							
						case ($count>1):
							$Key = $Data[$KeyIndice];
							$CsvArray[$Key] =$Data[$ValueIndice]; 
							break;
							
						default:
							break;	
					}
	  			$Data="";
	  			$count++;
				}
			}		
		}else{
			Print "<br />ExportFile::Erreur d'ouverure de fichier :".$CurrentFile;
			exit;	
		}
		fclose($handle);
		return $CsvArray;	
	}
	
	
	/*
	==========================================================================
	function GetIndiceFromNom($IndiceName)
	==========================================================================
	*/
	function GetIndiceFromNom($IndiceName)
	{
		$ArrayFliped = array_flip($this->ColNames);
		return $ArrayFliped[$IndiceName];
	}
	
	/*========================================================================
	// function CheckAndUpdateEntries()
	// Cherche l'ensemble des entrées où la date de sortie est inférieur ou 
	* 	égale à  la date courrante  et où ds_source est automate ou programmé
	* 	Met à jour la date de sortie avec +1 jour
	========================================================================*/	
	function CheckAndUpdateEntries ()
	{
		$this->CheckTableInit();
		$this->AddTrace("\n##### Fonction CheckAndUpdateEntries ######\nlance à ". date('d-m-Y H:i:s'). " ||"."\n");
		$res = $this->GetEntryToUp(); // ne prend que les dates de sorties données par l'automate
		// ne prend pas les dates programmées => il n'y a que l'intervention humaine ou gilda pour changer cette date
		$TableName = $this->NomTableLoc;
		$ds_source = 'Automate';
		$nb_i = count($res);
		$cpt_updt_sej = 0;
		if($nb_i>0)
		{
			for ($i=0;$i<$nb_i;$i++)
			{
				$end_time = $res[$i]['end_time'];
				$id = $res[$i]['id'];
				if ($this->CheckEndTime ($end_time))
				{
					$new_end_time  = $this->AddDay($end_time,1);
				}
				else
				{
					list ($jour,$mois,$annee) = explode('/',date("d/m/Y")); 	// recupere date courant
					list($heure,$min) = explode(':',date("H:i", $end_time)); 	// recupère heure et min de son ancien end_time
					$new_end_time = mktime($heure,$min,0,$mois,$jour,$annee);
					$new_end_time  = $this->AddDay($new_end_time,1);
				}
				$TableauDonne['end_time'] = $new_end_time ;
				$TableauDonne['ds_source'] = $ds_source;
				
				$TableauCd['id'] = $id;
				$upd = $this->update_($TableName,$TableauDonne,$TableauCd);
				
				$cpt_updt_sej++;
				$this->AddTrace("|| Pat : ".$res[$i]['noip']." || Maj sej :".$res[$i]['id']." new end time : ".$new_end_time."||"."\n");
			}
			$this->AddTrace("Nombre de lignes mises a jour par CheckAndUpdateEntries : ".$cpt_updt_sej."\n");

		}
		else
		{
			$this->AddTrace("\nAucune ligne affecte");
			return false;
		}
	}

	/*
	==========================================================================
	function GetIndiceFromNom($IndiceName)
	==========================================================================
	*/
	function GetColorCodeByDescription($Description){
		$sql = "SELECT * FROM agt_type_area WHERE type_name='".$Description."'";

		$res = $this->select($sql);
		return $res[0]['type_letter'];
	}
	/*
	==========================================================================
	* function GetTempNda()
	* function permet de generer le TEMP NDA en 9 carècters, de la table agt_prog
	* Ex :T00000001
	==========================================================================
	*/
	function GetLastTempNda()
	{
		$compteur=0;
		// recupère last nda 
		$sql = "SELECT nda FROM agt_prog where nda like('T%') order by nda desc limit 1";
		$res = $this->select($sql);
 
		if (strlen($res[0]['nda']) >0){
			$compteur=intval(substr($res[0]['nda'],-8));
 	
		}
 
		$compteur++;
		return "T".str_pad($compteur,8,"0",STR_PAD_LEFT);
	}
	/*
	* ==========================================================================
	* function updateNda($LocID,$InfoMVT[])
	* function permet de mise ajour les TempNDA vers le vrai NDA  et les UH
	* Ex :T00000001 => 761401224
	* ==========================================================================
	*/
	function updateNda($LocID,$InfoMVT)
	{
		$this->CheckTableInit();
		$compteur=0;
		// recupère agt_loc info
		$sql = "SELECT * FROM ".$this->NomTableLoc." where id='".$LocID."'";
		$res_main = $this->select($sql);

		//update agt_loc
		$sql_updt="UPDATE ".$this->NomTableLoc." set nda='".$InfoMVT['NDA']."', uh='".$InfoMVT['UH']."' , de_source='Gilda', statut_entry='Hospitalisé' WHERE id='".$LocID."'";
		$res = $this->update($sql_updt);	

		//update agt_prog
		$sql_updt="UPDATE agt_prog set nda='".$InfoMVT['NDA']."' WHERE id='".$res_main[0]['id_prog']."'";
		$res = $this->update($sql_updt);	
		
	}

	/*
	* ==========================================================================
	* function GetServicePlages($Service_id)
	* ==========================================================================
	*/
	function GetServicePlages($Service_id)
	{
		$sql="select * from agt_service_periodes where service_id='".$Service_id."'";
		return $this->select($sql);	
	}
	
	/*
	* ==========================================================================
	* function IsPlageLibre($PlagePos,$Date,$RoomId)
	* 	Function pour vérifier une plage_pos est libre
	* 	$PlagePos : position du palge
	* 	$Date : format DD/MM/YYYY
	* 	$RoomId :room id
	* 
	* return arry en deux dimention avec l'ensemble d'info du personne a occupé cette palge 
	* si non arry vide
	* ==========================================================================
	*/
	 function IsPlageLibre($PlagePos,$Date,$RoomId)
	{
		//check Plage libre ou un autre utilisateur a mis le patients
		$SqlChk="SELECT * FROM  ".$this->NomTableLoc."  
					WHERE  plage_pos='".$PlagePos."'
					AND FROM_unixtime(start_time  , '%d/%m/%Y' )='".$Date."'
					AND room_id='".$RoomId."'";
		return $this->select($SqlChk);
	}
	
	/*
	* ==========================================================================
	* function CheckPlageLibre($PlagePos,$Date,$RoomId)
	* 	Function pour vérifier une plage_pos est libre
	* 	$PlagePos : position du palge
	* 	$Date : format DD/MM/YYYY
	* 	$RoomId :room id
	* 
	* return arry en deux dimention avec l'ensemble d'info du personne a occupé cette palge 
	* si non arry vide
	* ==========================================================================
	*/
	 function CheckPlageLibre($PlagePos,$date_deb,$date_fin,$RoomId)
	{
		//check Plage libre 
		$SqlChk="SELECT * FROM  ".$this->NomTableLoc."  
					WHERE  plage_pos='".$PlagePos."'
					AND (start_time BETWEEN '".$date_deb."' AND '".$date_fin."' 
			 				OR  end_time BETWEEN '".$date_deb."' AND '".$date_fin."' 
			 				OR  start_time < '".$date_fin."' AND end_time > '".$date_deb."')
					AND room_id='".$RoomId."'";
		return $this->select($SqlChk);
	}
	
	/*
	* ==========================================================================
	* function GetSejoursParNda($Nda)
	* ==========================================================================
	*/
	function GetSejoursParNda($Nda)
	{
		$this->CheckTableInit();
		$sql="select * from ".$this->NomTableLoc." where nda='".$Nda."' and statut_entry !='SUPPRIMER' AND statut_entry !='Demande' order by start_time,id";
		return $this->select($sql);	
	}
					
	/*
	* ==========================================================================
	* function GetUserLevel($UserID,$id,$type='room')
	* Check user level
	* return 
	*  1 visiteur
	*  3 gesttionaire reservation
	*  4 Gestionnaire users + reservation
	*  5 web master
	* ==========================================================================
	*/
	function GetUserLevel($UserID,$id,$type='room')
	{
		$res = $this->select("SELECT statut FROM agt_utilisateurs WHERE login ='".$UserID."'");
		$status = $res[0]['statut'];

		// admin et visteur pas de verif
		if (strtolower($status) == 'visiteur') return 1;
		if (strtolower($status) == 'administrateur') return 5;
		
		// check droit de modifier d'un ROOM
        if ($type == 'room') {
			// On regarde si l'utilisateur est administrateur du domaine auquel la ressource $id appartient
			$sql="select id_room from agt_j_user_room where id_room ='".$id."'  and login ='".$UserID."'";
 
            $res= $this->select($sql);
 
            if (count($res) > 0)
				return 3;
			else
				return 1;

        }

        // On regarde si l'utilisateur est administrateur d'un domaine
        if ($type == 'area') {
            if ($id == '-1') {
                //On regarde si l'utilisateur est administrateur d'un domaine quelconque
                $res2 = $this->select("SELECT u.login FROM agt_utilisateurs u, agt_j_useradmin_area j
										WHERE (u.login=j.login and u.login='".protect_data_sql($user)."')");
                if (count($res2) > 0)
                    return 4;
			} else {
				//On regarde si l'utilisateur est administrateur du domaine dont l'id est $id
				$res3 = $this->select("SELECT u.login FROM agt_utilisateurs u, agt_j_useradmin_area j
				WHERE (u.login=j.login and j.id_area='".protect_data_sql($id)."' and u.login='".protect_data_sql($user)."')");
				if (count($res3) > 0)
					return 4;
			}
			// Sinon il s'agit d'un simple utilisateur
            return 2;
        }
        
        
        // check droit de modifier d'un service a faire
        
		return 1;
	}
	/*
	==========================================================================
	function BackupLoc($Tableau)
	==========================================================================
	*/
	function  BackupLoc($DataLoc)
	{
		// NOTE ::create sql doit etre tranfere vers une page commun
		//$sql create table if not exist
		//---------------------------------
		$Sql_create="CREATE TABLE IF NOT EXISTS loc_backup (
			ID int(11) NOT NULL auto_increment,
			NOIP 	varchar(10) default NULL,
			NMMAL 	varchar(35) default NULL,
			NMPMAL	varchar(35) default NULL,
			DANAIS	date  NOT NULL default '0000-00-00',
			NOTLDO  varchar(30) default NULL,
			NOLIT 	varchar(10) default NULL, 
			NOCHAM 	varchar(10) default NULL, 
			NOPOST 	varchar(10) default NULL, 
			NOSERV 	varchar(10) default NULL, 
			DDLOPT	date  NOT NULL default '0000-00-00',
			CDSEXM 	varchar(2) default NULL, 
			HHLOPT 	varchar(5) default NULL, 
			NDA 	varchar(10) default NULL, 
			DTENT	date  NOT NULL default '0000-00-00',
			HHENT 	varchar(5) default NULL, 
			UH 		varchar(3) default NULL, 
			TYSEJ 	varchar(3) default NULL, 
			TYMVT 	varchar(2) default NULL, 
			NOIDMV 	varchar(15) default NULL, 
			DATE_MAJ timestamp NOT NULL default CURRENT_TIMESTAMP,
			 PRIMARY KEY  (ID)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
		
		$this->create($Sql_create);

		$nbr_rows=count($DataLoc);
		for($t=0; $t < $nbr_rows; $t++)
		{
			$sql_data_ins ="";
			$sql_data_chk ="";
			//$sql Check deja present
			//---------------------------------
			foreach($DataLoc[$t] as $key => $val){
				$sql_data_ins.= $key."='".$val."',";
				$sql_data_chk.= $key." = '".$val."' AND ";
			}		

			// suprime last vircule
			$sql_data_ins = substr($sql_data_ins,0, -1);
			$sql_data_chk = substr($sql_data_chk,0, -4);
			
			$sql_check="select * from loc_backup where " .$sql_data_chk;
			$nbr_res=$this->select($sql_check);
			if (count($nbr_res)< 1 ) 
			{
				$sql_insert="INSERT INTO loc_backup set " .$sql_data_ins;
				$this->insert($sql_insert);			

			}
		}
				

	}			
	/*
	==========================================================================
	function BackupSortie($Tableau)
	==========================================================================
	*/
	function  BackupSortie($GildaSortie)
	{
		// NOTE ::create sql doit etre tranfere vers une page commun

		// modif le 28/04/14 par mohan add TYMAJ dans loc_backup
		$res=$this->select("SELECT column_name FROM information_schema.columns WHERE table_name = 'loc_backup' AND column_name LIKE 'tymaj'");
		// Si il existe
		if (count($res) < 1)
		{
			$this->create("ALTER TABLE  `loc_backup` ADD  `TYMAJ` VARCHAR( 1 ) NOT NULL DEFAULT  'A'");
		}

		// boucle sur les resultat
		$nbr_rows=count($GildaSortie);
		for($t=0; $t < $nbr_rows; $t++)
		{
			// prepare les variables
			$vars="
			    NOIP	= '".$GildaSortie[$t]['NOIP']."' AND 
			    NDA		= '".$GildaSortie[$t]['NODA']."' AND 
			    DTENT	= '".$GildaSortie[$t]['DTSOR']."' AND   
			    HHENT	= '".$GildaSortie[$t]['HHSOR']."' AND  
			    UH		= '".$GildaSortie[$t]['UHENT']."' AND 
			    TYSEJ	= '".$GildaSortie[$t]['TYSEJ']."' AND  
			    TYMVT	= '".$GildaSortie[$t]['TYMVT']."' AND  
			    NOIDMV	= '".$GildaSortie[$t]['NOIDMV']."'"  ;
			// check present dans la table
			$nbr_res=$this->select("select * from loc_backup where ".$vars);
			if (count($nbr_res)< 1 ) 
			{
				// insert les variables
				$sql_insert="INSERT INTO loc_backup set " .str_replace('AND',', ',$vars);
				$this->insert($sql_insert);			

			}
		}
	}			
	
	/*
	==========================================================================
	function GetVersion()
	==========================================================================
	*/
	function  GetVersion()
	{
		$res=$this->select("SELECT VALUE FROM agt_config where NAME='version'");
		return $res[0]['VALUE'];
	}	
	/*
	==========================================================================
	function GetRevision()
	==========================================================================
	*/
	function  GetRevision()
	{
		$res=$this->select("SELECT VALUE FROM agt_config where NAME='versionRC'");
		return $res[0]['VALUE'];
	}	
	
	/*
	==========================================================================
	function GetLocBackupParNda($Nda)
	==========================================================================
	*/
	function GetLocBackupParNda($Nda)
	{
		$sql =	"SELECT * from loc_backup where NDA='".$Nda."' AND TYMAJ='A' order by DTENT,HHENT,DDLOPT,HHLOPT";
		return $this->select($sql);
	}	

	/*
	==========================================================================
	// In MySQL, we avoid table locks, and use low-level locks instead.
	==========================================================================
	*/
	function grr_sql_mutex_lock($name)
	{
		global $sql_mutex_shutdown_registered, $grr_sql_mutex_unlock_name;
		if (!$this->CONN->query("SELECT GET_LOCK('$name', 20)")) return 0;
		$grr_sql_mutex_unlock_name = $name;
		if (empty($sql_mutex_shutdown_registered))
		{
			register_shutdown_function("grr_sql_mutex_cleanup");
			$sql_mutex_shutdown_registered = 1;
		}
		return 1;
	}

	/*
	==========================================================================
	// Release a mutual-exclusion lock on the named table. See grr_sql_mutex_unlock.
	==========================================================================
	*/
	function grr_sql_mutex_unlock($name)
	{
		global $grr_sql_mutex_unlock_name;
		$this->CONN->query("SELECT RELEASE_LOCK('$name')");
		$grr_sql_mutex_unlock_name = "";
	}

	/*
	==========================================================================
	// Shutdown function to clean up a forgotten lock. For internal use only.
	==========================================================================
	*/
	function grr_sql_mutex_cleanup()
	{
		global $sql_mutex_shutdown_registered, $grr_sql_mutex_unlock_name;
		if (!empty($grr_sql_mutex_unlock_name))
		{
			grr_sql_mutex_unlock($grr_sql_mutex_unlock_name);
			$grr_sql_mutex_unlock_name = "";
		}
	}
	
	
}
//fin Objet	
?>
