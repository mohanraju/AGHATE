<?php
	if(strlen($day) < 2 && $day != "")
		$day = str_pad($day,2,'0',STR_PAD_LEFT);
	
	if(strlen($month) < 2 && $month != "")
		$month = str_pad($month,2,'0',STR_PAD_LEFT);
	
	if($day != ""){
		$date_deb=$day."/".$month."/".$year;
		$heure_deb= $hour.":".$minute;
	}
		
	$Resservice=$Aghate->GetServiceInfoByServiceId($area);
	$service=$Resservice[0]['service_name'];

	// premier fois charger les Rooms	par rapport le service
	$row = $Aghate->GetAllRooms($area,true);
	foreach($row as $key )
	{
		$ListRoom[]=$key['id']."|". $key['room_name'];
		$default_room=($key['room_name']=='Panier')?$key['id']:$default_room;
	}
	$default_room =(strlen($room)< 1)?$default_room:$room;

?>
<table align="center" border="0" width="700px" cellspacing=0>
	<tr>
		<td id="SectionTitle">Réservation</td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>
			<table>
				<tr>
					<td>
							<span class="add-on"><b>Patient :</b> </span>
							<!--?php Print $Html->InputTextBox("val_rech",$val_rech,25,10,"title='Recherche par Nom ou NIP ou NDA' class='span5'" );?-->
							<?php Print $Html->InputSearchIdentity("val_rech",$val_rech,"title='Recherche par Nom ou NIP ou NDA' class='span5'");?>
							<div id="DivRecherchPatients" style="z-index:500;position:absolute;display:none;border:2px solid #D4D0C8;background-color:#F7F7F7;   height: 300px;   max-height: 300px; overflow-y: scroll;">
						    	Veuillez Saisir le Nom ou le Nip du patient puis tappez Entrez pour charcher un patient
							</div>
					 
					</td>
				</tr>
				<tr>
					<td>
					<div class="input-prepend input-append">
						<span class="add-on"><b>Description :</b> </span>
						
						<?php Print $Html->InputTextBox("description",$description,255,10,"class='span3'" );?>
					</div>	
					</td>
				</tr>
				<tr>
					<td>
						<div id="pat_info" style='background-color:#FFFFFF;display:none;padding:10px;padding-top:0px;overflow-y:auto;height:150px;width:100%;'></div>
					</td>
				</tr>
				<tr>
					<td>
					 <div class="input-prepend input-append">
						<b>Spécialité :</b>&nbsp;&nbsp;
						<span class="add-on"><b>Spécialité :</b>&nbsp;&nbsp;&nbsp;&nbsp;</span>
						<?php 
							print $Html->InputHiddenBox("id_service",$id_service);
							print $Html->InputCompletSimple(service,"",$service,"agt_service","../commun/ajax/ajax_resa_autocomplet_service.php","Onchange='RechargeRooms(\"#DivRooms\")'");
							echo "<div id='DivRooms'>";
							echo '<span class="add-on"><b>Lit :</b>&nbsp;&nbsp;&nbsp;&nbsp;</span>';							
							print  $Html->InputSelect($ListRoom,$room,"room","../commun/ajax/ajax_resa_autocomplet_service.php","class='input-small'" );
							echo "</div>";
						?>
					</div>	
						</td>
				</tr>
				 
				<tr>
					<td>
					<div class="input-prepend input-append">
					<span class="add-on"><b>Intervention :</b>&nbsp;&nbsp;</span>
						<?php 
							print $Html->InputHiddenBox("id_protocole",$id_protocole);
							print $Html->InputCompletSimple(protocole,$protocole,"","agt_protocole","../commun/ajax/ajax_resa_autocomplet_protocole.php","title='Recherche Protocole  par libellé'");
						?>
					</div>	
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<div class="input-prepend input-append">
						<span class="add-on"><b>Date opération</b>&nbsp;&nbsp;&nbsp;</span>
						<input type="text" class="span2" name="date_deb"   id="date_deb" value="<?php echo $date_deb; ?>"   >
						<input type="text" class="span1" name="heure_deb"   id="heure_deb" value="<?php echo $heure_deb; ?>"   >
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<div class="input-prepend input-append">
						<span class="add-on"><b>Durée prévisionnelle</b>&nbsp;&nbsp;&nbsp;</span>
								<input type="text" class="span1" name="heure_deb"   id="heure_deb" value="<?php echo $heure_deb; ?>"   >
					</div>	
					</td>
				</tr>
				<!--tr>
					<td><b>Sortie prévue</b><br><a style="vertical-align: bottom;" href="#?" onclick="SetColorInput('ms','0');updateForms('','ms','0','A domicile','forms','ms')" class="OptionNotSelected" id="LBL_ms0" cval="A domicile">A domicile</a>	&nbsp;&nbsp;<a style="vertical-align: bottom;" href="#?" onclick="SetColorInput('ms','1');updateForms('','ms','1',' SSR ','forms','ms')" class="OptionNotSelected" id="LBL_ms1" cval=" SSR "> SSR </a>	&nbsp;&nbsp;<a style="vertical-align: bottom;" href="#?" onclick="SetColorInput('ms','2');updateForms('','ms','2',' Long séjour','forms','ms')" class="OptionNotSelected" id="LBL_ms2" cval=" Long séjour"> Long séjour</a>	&nbsp;&nbsp;<input name="ms" id="ms" value="99" type="hidden"></td>
				</tr-->
				<tr>
					<td align="center">
						<div class="noprint">
							<input type="button" id="Enregistrer" name="Enregistrer" value="Enregistrer" class="btn btn-success" onclick="SavePage(this.value)"/> 
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

