<?php
/**
 * mrbs_sql.inc.php
 * Bibliothèque de fonctions propres à l'application GRR
 *
 * Dernière modification : $Date: 2008-07-20 12:29:35 $
 *
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @author    Marc-Henri PAMISEUX <marcori@users.sourceforge.net>
 * @copyright Copyright 2003-2005 Laurent Delineau
 * @copyright Copyright 2008 Marc-Henri PAMISEUX
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   include
 * @version   $Id: mrbs_sql.inc.php,v 1.2 2008-07-20 12:29:35 grr Exp $
 * @filesource
 *
 * This file is part of GRR.
 * D'après http://mrbs.sourceforge.net/
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

/** mrbsCheckFree()
 *
 * Check to see if the time period specified is free
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
 */
 
 date_default_timezone_set('Europe/Paris');
 
function mrbsCheckFree($room_id, $starttime, $endtime)
{
    global $vocab;
    # Select any meetings which overlap ($starttime,$endtime) for this room:
    $sql = "SELECT id, name, start_time FROM agt_loc WHERE
        start_time < '".$endtime."' AND end_time > '".$starttime."'
        AND room_id = '".$room_id."'";

    if ($ignore > 0)
        $sql .= " AND id <> $ignore";
    if ($repignore > 0)
        $sql .= " AND repeat_id <> $repignore";
    $sql .= " ORDER BY start_time";

    $res = grr_sql_query($sql);
    if(! $res)
        return grr_sql_error();
    if (grr_sql_count($res) == 0)
    {
        grr_sql_free($res);
        return "";
    }
    // Get the room's area ID for linking to day, week, and month views:
    $area = mrbsGetServiceIdByRoomId($room_id);

    // Build a string listing all the conflicts:
    $err = "";
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $starts = getdate($row[2]);
        $param_ym = "area=$area&amp;year=$starts[year]&amp;month=$starts[mon]";
        $param_ymd = $param_ym . "&amp;day=$starts[mday]";

        $err .= "<LI><A HREF=\"view_entry.php?id=$row[0]\">$row[1]</A>"
        . " ( " . utf8_strftime('%A %d %B %Y %T', $row[2]) . ") "
        . "(<A HREF=\"day.php?$param_ymd\">".get_vocab("viewday")."</a>"
        . " | <A HREF=\"week.php?room=$room_id&amp;$param_ymd\">".get_vocab("viewweek")."</a>"
        . " | <A HREF=\"month.php?room=$room_id&amp;$param_ym\">".get_vocab("viewmonth")."</a>)\n";
    }
    return $err;
}

/** grrCheckOverlap()
 *
 * Dans le cas d'une réservation avec périodicité,
 * Vérifie que les différents créneaux ne se chevaussent pas.
 *
 * $reps : tableau des débuts de réservation
 * $diff : durée d'une réservation
 */
function grrCheckOverlap($reps, $diff)
{
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


/** grrDelEntryInConflict()
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
 */
function grrDelEntryInConflict($room_id, $starttime, $endtime, $ignore, $repignore, $flag)
{
    global $vocab, $dformat;

    # Select any meetings which overlap ($starttime,$endtime) for this room:
    $sql = "SELECT id FROM agt_loc WHERE
        start_time < '".$endtime."' AND end_time > '".$starttime."'
        AND room_id = '".$room_id."'";
    if ($ignore > 0)
        $sql .= " AND id <> $ignore";
    if ($repignore > 0)
        $sql .= " AND repeat_id <> $repignore";
    $sql .= " ORDER BY start_time";

    $res = grr_sql_query($sql);
    if(! $res)
        return grr_sql_error();
    if (grr_sql_count($res) == 0)
    {
        grr_sql_free($res);
        return "";
    }
    # Efface les résas concernées
    $err = "";
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        if (getSettingValue("automatic_mail") == 'yes') $_SESSION['session_message_error'] = send_mail($row[0],3,$dformat);
        $result = mrbsDelEntry(getUserName(), $row[0], NULL , 1);
    }
    if ($flag == 1) return $result;
}


/** mrbsDelEntry()
 *
 * Delete an entry, or optionally all entrys.
 *
 * $user   - Who's making the request
 * $id     - The entry to delete
 * $series - If set, delete the series, except user modified entrys
 * $all    - If set, include user modified entrys in the series delete
 *
 * Returns:
 *   0        - An error occured
 *   non-zero - The entry was deleted
 */
function mrbsDelEntry($user, $id, $series, $all)
{
    $sql = "SELECT id,name,noip FROM agt_loc WHERE ";
	$sql .= "id='".$id."'";
    $res = grr_sql_query($sql);
    $removed = 0;
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
    	if ($i==0){
				$Patient=$row[1];    	
			}
        if (grr_sql_command("DELETE FROM agt_loc WHERE id=" . $row[0]) > 0){
			$Trace_msg="Annulation ou Modification : ".$Patient;
			$sql_log = "insert into agt_log (LOGIN, START,  REMOTE_ADDR, USER_AGENT, REFERER, AUTOCLOSE, END) values (
                '" . $_SESSION['login'] . "',
                '" . date("Y-m-d h:i:s") . "',
                '" . $_SERVER['REMOTE_ADDR'] . "',
                '" . substr($Trace_msg,0,254) . "',
                '" . $_SERVER['HTTP_REFERER'] . "',
                '1',
                '" . $_SESSION['start'] . "'+ interval " . getSettingValue("sessionMaxLength") . " minute
            );";    
			$result = grr_sql_query($sql_log);		
            $removed++;
            }
        grr_sql_command("DELETE FROM agt_overload_data WHERE entry_id=" . $row[0]);
        grr_sql_command("DELETE FROM agt_loc_moderate WHERE id=" . $row[0]);
    }
    //Traces des supression ou modification RDV par Mohan le 28/03/2013
    return $removed > 0;
}


/*
  mrbsGetAreaIdByRoomIdFromRoomId($room_id)
*/

function mrbsGetAreaIdByRoomIdFromRoomId($room_id)
{
  // Avec la room_id on récupère l'service_id
  $sqlstring = "select service_id from agt_room where id=$room_id";
  $result = grr_sql_query($sqlstring);

  if (! $result) fatal_error(1, grr_sql_error());
  if (grr_sql_count($result) != 1) fatal_error(1, get_vocab('roomid') . $id_entry . get_vocab('not_found'));

  $service_id_row = grr_sql_row($result, 0);
  grr_sql_free($result);

  return $service_id_row[0];

}



/** mrbsOverloadGetFieldslist()
 *
 * Return an array with all fields name
 * $id_area - Id of the id_area
 *
 */
function mrbsOverloadGetFieldslist($id_area,$room_id=0)
{
  if ($room_id > 0 ) {
      // il faut rechercher le id_area en fonction du room_id
      $id_area = grr_sql_query1("select service_id from agt_room where id='".$room_id."'");
      if ($id_area == -1) {
          fatal_error(1, get_vocab('error_room') . $room_id . get_vocab('not_found'));
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
  $result = grr_sql_query($sqlstring);
  $fieldslist = array();
  if (! $result) fatal_error(1, grr_sql_error());

  if (grr_sql_count($result) <0) fatal_error(1, get_vocab('error_area') . $id_area . get_vocab('not_found'));
  for ($i = 0; ($field_row = grr_sql_row($result, $i)); $i++)
    {
    if ($id_area == "") {
      $fieldslist[$field_row[0]." (".$field_row[4].")"]["type"] = $field_row[1];
      $fieldslist[$field_row[0]." (".$field_row[4].")"]["id"] = $field_row[2];
      if (trim($field_row[3]) != "") {
          $tab_list = explode("|", $field_row[3]);
          foreach ($tab_list as $value) {
              if (trim($value) != "")
                  $fieldslist[$field_row[0]." (".$field_row[4].")"]["list"][] = trim($value);
          }
      }
      $fieldslist[$field_row[0]." (".$field_row[4].")"]["affichage"] = $field_row[5];
      $fieldslist[$field_row[0]." (".$field_row[4].")"]["overload_mail"] = $field_row[6];
      $fieldslist[$field_row[0]." (".$field_row[4].")"]["obligatoire"] = $field_row[7];
      $fieldslist[$field_row[0]." (".$field_row[4].")"]["confidentiel"] = $field_row[8];
     } else {
      $fieldslist[$field_row[0]]["type"] = $field_row[1];
      $fieldslist[$field_row[0]]["id"] = $field_row[2];
      $fieldslist[$field_row[0]]["affichage"] = $field_row[4];
      $fieldslist[$field_row[0]]["overload_mail"] = $field_row[5];
      $fieldslist[$field_row[0]]["obligatoire"] = $field_row[6];
      $fieldslist[$field_row[0]]["confidentiel"] = $field_row[7];
      if (trim($field_row[3]) != "") {
          $tab_list = explode("|", $field_row[3]);
          foreach ($tab_list as $value) {
              if (trim($value) != "")
                  $fieldslist[$field_row[0]]["list"][] = trim($value);
          }
      }
     }
    }
  return $fieldslist;
}

function GetRoomID_old ($entry_id) {
	$sql = "SELECT room_id FROM agt_loc WHERE id='".$entry_id."'";
	$res = grr_sql_query1($sql);
	return $res;
}
/** mrbsEntryGetOverloadDesc()
 *
 * Return an array with all additionnal fields
 * $id - Id of the entry
 *
 */
function mrbsEntryGetOverloadDesc($id_entry)
{
  $room_id = 0;
  $overload_array = array();
  $overload_desc = "";
  // On récupère les données overload desc dans agt_loc.
  if ($id_entry != NULL) {
	  $room_id = GetRoomID($id_entry);
	  $service_id = mrbsGetAreaIdByRoomIdFromRoomId($room_id);
	  $sql = "SELECT id,entry_id, field_name,field_data FROM agt_overload_data WHERE entry_id='".$id_entry."'";
	  $res = grr_sql_query($sql);
	  for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) 
	  {
		   $overload_array[$row[2]]["valeur"] = $row[3];
		   $sql2 = "SELECT id,affichage,overload_mail,obligatoire,confidentiel 
					FROM agt_overload WHERE id_area='".$service_id."' 
					AND fieldname='".$row[2]."'";
			$res2=grr_sql_query($sql2);
			$row2 = grr_sql_row($res2, 0);
			$overload_array[$row[2]]["id"] = $row2[0];
			$overload_array[$row[2]]["affichage"] = $row2[1];
			$overload_array[$row[2]]["overload_mail"] = $row2[2];
			$overload_array[$row[2]]["obligatoire"] = $row2[3];
			$overload_array[$row[2]]["confidentiel"] = $row2[4];
	  }
  }
  return $overload_array;
}
 /*     //$overload_array = array();
      //$sqlstring = "select overload_desc,room_id from agt_loc where id=".$id_entry.";";
      //$result = grr_sql_query($sqlstring);

      //if (! $result) fatal_error(1, grr_sql_error());
      //if (grr_sql_count($result) != 1) fatal_error(1, get_vocab('entryid') . $id_entry . get_vocab('not_found'));

      //$overload_desc_row = grr_sql_row($result, 0);
      //grr_sql_free($result);

      //$overload_desc = $overload_desc_row[0];
      //$room_id = $overload_desc_row[1];
    }
  if ( $room_id >0 ) {
      $service_id = mrbsGetAreaIdByRoomIdFromRoomId($room_id);
      // Avec l'id_area on récupère la liste des champs additionnels dans agt_overload.
      $fieldslist = mrbsOverloadGetFieldslist($service_id);

      foreach ( $fieldslist as $field=>$fieldtype)  {
//      $begin_string = "<".$fieldslist[$field]["id"].">";   //tructruc
//      $end_string = "</".$fieldslist[$field]["id"].">";    //tructruc
        //$begin_string = "@".$fieldslist[$field]["id"]."@";
        //$end_string = "@/".$fieldslist[$field]["id"]."@";
        $data = GetOverloadData;
        //$begin_pos = strpos($overload_desc,$begin_string);
        //$end_pos = strpos($overload_desc,$end_string);

        //if ( $begin_pos !== false && $end_pos !== false )  {
         // $first = $begin_pos + strlen($begin_string);
          //$data = substr($overload_desc,$first,$end_pos-$first);
//          $overload_array[$field] = base64_decode($data);  //tructruc
        $overload_array[$field]["valeur"] = urldecode($data);
        $overload_array[$field]["id"] = $fieldslist[$field]["id"];
        $overload_array[$field]["affichage"] = grr_sql_query1("select affichage 
												from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["overload_mail"] = grr_sql_query1("select overload_mail 
													from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["obligatoire"] = grr_sql_query1("select obligatoire
													from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["confidentiel"] = grr_sql_query1("select confidentiel
													from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        } 
        else
        {
          $overload_array[$field]["valeur"] = "";
        }
        $overload_array[$field]["id"] = $fieldslist[$field]["id"];
        $overload_array[$field]["affichage"] = grr_sql_query1("select affichage from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["overload_mail"] = grr_sql_query1("select overload_mail from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["obligatoire"] = grr_sql_query1("select obligatoire from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["confidentiel"] = grr_sql_query1("select confidentiel from agt_overload where id = '".$fieldslist[$field]["id"]."'");
      }
      return $overload_array;
  }
  return $overload_array;
}*/

/** mrbsEntryGetOverloadDesc_n()
 *
 * Return an array with all additionnal fields
 * $id - Id of the entry
 *
 */
function mrbsEntryGetOverloadDesc_n($id_entry)
{
  $room_id = 0;
  $overload_array = array();
  $overload_desc = "";
  // On récupère les données overload desc dans agt_loc.
  if ($id_entry != NULL) {
      $overload_array = array();
      $sqlstring = "select overload_desc,room_id from grr_nonvenu where id=".$id_entry.";";
      $result = grr_sql_query($sqlstring);

      if (! $result) fatal_error(1, grr_sql_error());
      if (grr_sql_count($result) != 1) fatal_error(1, get_vocab('entryid') . $id_entry . get_vocab('not_found'));

      $overload_desc_row = grr_sql_row($result, 0);
      grr_sql_free($result);

      $overload_desc = $overload_desc_row[0];
      $room_id = $overload_desc_row[1];
    }
  if ( $room_id >0 ) {
      $service_id = mrbsGetAreaIdByRoomIdFromRoomId($room_id);


      // Avec l'id_area on récupère la liste des champs additionnels dans agt_overload.
      $fieldslist = mrbsOverloadGetFieldslist($service_id);

      foreach ( $fieldslist as $field=>$fieldtype)  {
//      $begin_string = "<".$fieldslist[$field]["id"].">";   //tructruc
//      $end_string = "</".$fieldslist[$field]["id"].">";    //tructruc
        $begin_string = "@".$fieldslist[$field]["id"]."@";
        $end_string = "@/".$fieldslist[$field]["id"]."@";
        $data = "";
        $begin_pos = strpos($overload_desc,$begin_string);
        $end_pos = strpos($overload_desc,$end_string);

        if ( $begin_pos !== false && $end_pos !== false )  {
          $first = $begin_pos + strlen($begin_string);
          $data = substr($overload_desc,$first,$end_pos-$first);
//          $overload_array[$field] = base64_decode($data);  //tructruc
          $overload_array[$field]["valeur"] = urldecode($data);
        } else
          $overload_array[$field]["valeur"] = "";
        $overload_array[$field]["id"] = $fieldslist[$field]["id"];
        $overload_array[$field]["affichage"] = grr_sql_query1("select affichage from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["overload_mail"] = grr_sql_query1("select overload_mail from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["obligatoire"] = grr_sql_query1("select obligatoire from agt_overload where id = '".$fieldslist[$field]["id"]."'");
        $overload_array[$field]["confidentiel"] = grr_sql_query1("select confidentiel from agt_overload where id = '".$fieldslist[$field]["id"]."'");
      }
      return $overload_array;
  }
  return $overload_array;
}

/** grrExtractValueFromOverloadDesc()
 *
 * Extrait la chaine correspondante au champ id de la chaine $chaine
 *
 */
function grrExtractValueFromOverloadDesc($chaine,$id)
{
//    $begin_string = "<".$id.">"; //tructruc
//    $end_string = "</".$id.">";  //tructruc
    $begin_string = "@".$id."@";
    $end_string = "@/".$id."@";
    $data = "";
    $begin_pos = strpos($chaine,$begin_string);
    $end_pos = strpos($chaine,$end_string);
    if ( $begin_pos !== false && $end_pos !== false ) {
        $first = $begin_pos + strlen($begin_string);
        $data = substr($chaine,$first,$end_pos-$first);
//        $data = base64_decode($data); //tructruc
        $data = urldecode($data);
    } else $data = "";
    return $data;
}

/** grrExtractValueFromOverloadDesc()
 *
 * Extrait la chaine correspondante au champ id de la chaine $chaine
 *
 */
function grrExtractValueFromOverloadDesc_v1($chaine)
{
//    $begin_string = "<".$id.">"; //tructruc
//    $end_string = "</".$id.">";  //tructruc
    $begin_string = "@".$id."@";
    $end_string = "@/".$id."@";
    $data = "";
    $begin_pos = strpos($chaine,$begin_string);
    $end_pos = strpos($chaine,$end_string);
    if ( $begin_pos !== false && $end_pos !== false ) {
        $first = $begin_pos + strlen($begin_string);
        $data = substr($chaine,$first,$end_pos-$first);
//        $data = base64_decode($data); //tructruc
        $data = urldecode($data);
    } else $data = "";
    return $data;
}


/** GetFieldName(id)
 * Return the fieldname of the id
 */
 function GetFieldName ($id_field) {
	$sql = "SELECT fieldname FROM agt_overload WHERE id='".$id_field."'";
	$res = grr_sql_query($sql);
	$row = grr_sql_row($res,0);
	return $row[0];
} 
 
 /** mrbsCreateSingleEntry()
 *
 * Create a single (non-repeating) entry in the database
 *
 * $starttime   - Start time of entry
 * $endtime     - End time of entry
 * $entry_type  - Entry type
 * $repeat_id   - Repeat ID
 * $room_id     - Room ID
 * $beneficiaire       - beneficiaire
 * $beneficiaire_ext - bénéficiaire extérieur
 * $name        - Name
 * $type        - Type (Internal/External)
 * $description - Description
 *$rep_jour_c - Le jour cycle d'une réservation, si aucun 0
 *
 * Returns:
 *   0        - An error occured while inserting the entry
 *   non-zero - The entry's ID
 */
function mrbsCreateSingleEntry($starttime, $endtime,$room_id,
                               $creator, $type, $description,$noip,$medecin,$uh,$protocole)
{
   $sql = "INSERT INTO agt_loc (start_time,end_time, room_id,
                                      create_by, type, description,statut_entry,noip,medecin,uh,protocole)
                            VALUES ($starttime, $endtime,$room_id,
                                    '".protect_data_sql($creator)."', '".protect_data_sql($type)."',
                                     '".protect_data_sql($description)."', '','".$noip."','".$medecin."','".$uh."','".$protocole."')";
    if (grr_sql_command($sql) < 0) {
		 fatal_error(0, grr_sql_error());
		 return 0;
	 }
    // s'il s'agit d'une modification d'une ressource déjà modérée et acceptée : on met à jour les infos dans la table agt_loc_moderate
    $new_id = grr_sql_insert_id("agt_loc", "id");
    if ($moderate==2) moderate_entry_do($new_id,1,"","no");
}

function InsertOverloadData($entry_id,$overload_data,$room_id)
{
  $overload_fields_list = mrbsOverloadGetFieldslist(0,$room_id);

  foreach ($overload_fields_list as $field=>$fieldtype)
    {
      $id_field = $overload_fields_list[$field]["id"];
      $field_name = GetFieldName($id_field);
      if (array_key_exists($id_field,$overload_data))
      {
		$sql = "INSERT INTO agt_overload_data (entry_id,field_name,field_data)
                       VALUES ('".$entry_id."','".$field_name."','".$overload_data[$id_field]."')";
		 if (grr_sql_command($sql) < 0){
			 fatal_error(0, grr_sql_error());
			  return 0;
		  }
		$new_id = grr_sql_insert_id("agt_loc", "id");
		if ($moderate==2) moderate_entry_do($new_id,1,"","no");
      }
    }
}
/** mrbsCreateRepeatEntry()
 *
 * Creates a repeat entry in the data base
 *
 * $starttime   - Start time of entry
 * $endtime     - End time of entry
 * $rep_type    - The repeat type
 * $rep_enddate - When the repeating ends
 * $rep_opt     - Any options associated with the entry
 * $room_id     - Room ID
 * $beneficiaire       - beneficiaire
 * $beneficiaire_ext   - beneficiaire extérieur
 * $creator     - celui aui a créé ou modifié la réservation.
 * $name        - Name
 * $type        - Type (Internal/External)
 * $description - Description
  *$rep_jour_c - Le jour cycle d'une réservation, si aucun 0
 *
 * Returns:
 *   0        - An error occured while inserting the entry
 *   non-zero - The entry's ID
 */
function mrbsCreateRepeatEntry($starttime, $endtime, $rep_type, $rep_enddate, $rep_opt,
                               $room_id, $creator, $beneficiaire, $beneficiaire_ext, $name, $type, $description, $rep_num_weeks,$overload_data, $rep_jour_c,$noip,$hds,$medecin)
{
  $overload_data_string = "";
  $service_id = mrbsGetAreaIdByRoomIdFromRoomId($room_id);

  $overload_fields_list = mrbsOverloadGetFieldslist($service_id);

  foreach ($overload_fields_list as $field=>$fieldtype)
    {
      $id_field = $overload_fields_list[$field]["id"];
      if (array_key_exists($id_field,$overload_data))
      {
//      $begin_string = "<".$id_field.">"; //tructruc
//      $end_string = "</".$id_field.">";  //tructruc
      $begin_string = "@".$id_field."@";
      $end_string = "@/".$id_field."@";
//    $overload_data_string .= $begin_string.base64_encode($overload_data[$id_field]).$end_string; // tructruc
    $overload_data_string .= $begin_string.urlencode($overload_data[$id_field]).$end_string; // tructruc

      }
    }
  $sql = "INSERT INTO agt_repeat (
  start_time, end_time, rep_type, end_date, rep_opt, room_id, create_by, beneficiaire, beneficiaire_ext, type, name, description, rep_num_weeks, overload_desc, jours,noip)
  VALUES ($starttime, $endtime,  $rep_type, $rep_enddate, '$rep_opt', $room_id,   '".protect_data_sql($creator)."','".protect_data_sql($beneficiaire)."','".protect_data_sql($beneficiaire_ext)."', '".protect_data_sql($type)."', '".protect_data_sql($name)."', '".protect_data_sql($description)."', '$rep_num_weeks','".protect_data_sql($overload_data_string)."',".$rep_jour_c.",'".$noip."')";


  if (grr_sql_command($sql) < 0)
    {
      return 0;

    }
  return grr_sql_insert_id("agt_repeat", "id");
}


/** same_day_next_month
 *  Return the number of days to step forward for a "monthly repeat,
 *  corresponding day" series - same week number and day of week next month.
 *  This function always returns either 28 or 35.
 *  For dates after the 28th day of a month, the results are undefined.
 */
function same_day_next_month($time)
{
    $days_in_month = date("t", $time);
    $day = date("d", $time);
    $weeknumber = (int)(($day - 1) / 7) + 1;
    if ($day + 7 * (5 - $weeknumber) <= $days_in_month) return 35;
    else return 28;
}

/** mrbsGetRepeatEntryList
 *
 * Returns a list of the repeating entrys
 *
 * $time     - The start time
 * $enddate  - When the repeat ends
 * $rep_type - What type of repeat is it
 * $rep_opt  - The repeat entrys
 * $max_ittr - After going through this many entrys assume an error has occured
 * *$rep_jour_c - Le jour cycle d'une réservation, si aucun 0
 *
 * Returns:
 *   empty     - The entry does not repeat
 *   an array  - This is a list of start times of each of the repeat entrys
 */
function mrbsGetRepeatEntryList($time, $enddate, $rep_type, $rep_opt, $max_ittr, $rep_num_weeks, $rep_jour_c)
{
    $sec   = date("s", $time);
    $min   = date("i", $time);
    $hour  = date("G", $time);
    $day   = date("d", $time);
    $month = date("m", $time);
    $year  = date("Y", $time);

    $entrys = "";
    $entrys_return = "";
    $k=0;
    for($i = 0; $i < $max_ittr; $i++)
    {
        $time = mktime($hour, $min, $sec, $month, $day, $year);
        if ($time > $enddate)
            break;
        $time2 = mktime(0, 0, 0, $month, $day, $year);

        if (!(est_hors_reservation($time2))) {
            $entrys_return[$k] = $time;
            $k++;
        }
        $entrys[$i] = $time;
        switch($rep_type)
        {
            // Daily repeat
            case 1:
                $day += 1;
                break;

            // Weekly repeat
            case 2:
                $j = $cur_day = date("w", $entrys[$i]);
                // Skip over days of the week which are not enabled:
                while ((($j = ($j + 1) % (7*$rep_num_weeks)) != $cur_day && $j<7 &&!$rep_opt[$j]) or ($j>=7))
                {
                    $day += 1;
                }

                $day += 1;
                break;

            // Monthly repeat
            case 3:
                $month += 1;
                break;

            // Yearly repeat
            case 4:
                $year += 1;
                break;

            // Monthly repeat on same week number and day of week
            case 5:
                $day += same_day_next_month($time);
                break;

            // Si la périodicité est par Jours/Cycle
            case 6:
                $sql = "SELECT * FROM agt_calendrier_jours_cycle WHERE DAY >= '".$time2."' AND DAY <= '".$enddate."' AND Jours = '".$rep_jour_c."'";
                $result = mysql_query($sql);
                $kk = 0;
                $tableFinale = array();
                while($table = mysql_fetch_array($result)){
                    $day   = date("d", $table['DAY']);
                    $month = date("m", $table['DAY']);
                    $year  = date("Y", $table['DAY']);
                    $tableFinale[$kk] = mktime($hour, $min, $sec, $month, $day, $year);
                    $kk++;
                }
                return $tableFinale;
                break;

            // Unknown repeat option
            default:
                return;
        }
    }

    return $entrys_return;
}

/** mrbsCreateRepeatingEntrys()
 *
 * Creates a repeat entry in the data base + all the repeating entrys
 *
 * $starttime   - Start time of entry
 * $endtime     - End time of entry
 * $rep_type    - The repeat type
 * $rep_enddate - When the repeating ends
 * $rep_opt     - Any options associated with the entry
 * $room_id     - Room ID
 * $beneficiaire       - beneficiaire
 * $beneficiaire_ext - bénéficiaire extérieur
 * $name        - Name
 * $type        - Type (Internal/External)
 * $description - Description
  *$rep_jour_c - Le jour cycle d'une réservation, si aucun 0
 *
 * Returns:
 *   0        - An error occured while inserting the entry
 *   non-zero - The entry's ID
 */
function mrbsCreateRepeatingEntrys($starttime, $endtime, $rep_type, $rep_enddate, $rep_opt,
                                   $room_id, $creator, $beneficiaire, $beneficiaire_ext, $name, $type, $description, $rep_num_weeks, $option_reservation,$overload_data, $moderate, $rep_jour_c,$noip,$hds,$medecin)
{
    global $max_rep_entrys, $id_first_resa;
    $reps = mrbsGetRepeatEntryList($starttime, $rep_enddate, $rep_type, $rep_opt, $max_rep_entrys, $rep_num_weeks, $rep_jour_c);
    if(count($reps) > $max_rep_entrys)
        return 0;

    if(empty($reps))
    {
        mrbsCreateSingleEntry($starttime, $endtime, 0, 0, $room_id, $creator, $beneficiaire, $beneficiaire_ext, $name, $type, $description, $option_reservation,$overload_data,$moderate, $rep_jour_c);
        $id_first_resa = grr_sql_insert_id("agt_loc", "id");
        return;
    }

    $ent = mrbsCreateRepeatEntry($starttime, $endtime, $rep_type, $rep_enddate, $rep_opt, $room_id, $creator, $beneficiaire, $beneficiaire_ext, $name, $type, $description, $rep_num_weeks,$overload_data, $rep_jour_c,$noip,$hds,$medecin);
    if($ent)
    {
        $diff = $endtime - $starttime;

        for($i = 0; $i < count($reps); $i++) {
            mrbsCreateSingleEntry($reps[$i], $reps[$i] + $diff, 1, $ent,
                 $room_id, $creator, $beneficiaire, $beneficiaire_ext, $name, $type, $description, $option_reservation,$overload_data, $moderate, $rep_jour_c);
            $id_new_resa = grr_sql_insert_id("agt_loc", "id");
            // s'il s'agit d'une modification d'une ressource déjà modérée et acceptée : on met à jour les infos dans la table agt_loc_moderate
            if ($moderate==2) moderate_entry_do($id_new_resa,1,"","no");
            // On récupère l'id de la première réservation de la série et qui sera utilisé pour l'enoi d'un mail
            if ($i == 0) $id_first_resa = $id_new_resa;
            }
    }

    return $ent;
}

/* mrbsGetEntryInfo()
 *
 * Get the booking's entrys
 *
 * @param integer $id : The ID for which to get the info for.
 * @return variant    : nothing = The ID does not exist
 *    array   = The bookings info
 */
function mrbsGetEntryInfo($id)
{
    $sql = "SELECT start_time, end_time, room_id,
                   timestamp,name, type, description
           FROM agt_loc
           WHERE id = '".$id."'";
    $res = grr_sql_query($sql);
   if (! $res)
     return;

   $ret = '';
    if(grr_sql_count($res) > 0)
    {
        $row = grr_sql_row($res, 0);

        $ret["start_time"]  = $row[0];
        $ret["end_time"]    = $row[1];
        $ret["room_id"]     = $row[2];
        $ret["timestamp"]   = $row[3];
        $ret["name"]        = $row[4];
        $ret["type"]        = $row[5];
        $ret["description"] = $row[6];

    }
    grr_sql_free($res);

    return $ret;
}

function mrbsGetServiceIdByRoomId($id)
{
    $id = grr_sql_query1("SELECT service_id FROM agt_room WHERE (id = '".$id."')");
    if ($id <= 0) return 0;
    return $id;
}

 function moderate_entry_do($_id,$_moderate,$_description,$send_mail="yes")
 {
global $dformat;

// On vérifie que l'utilisateur a bien le droit d'être ici
$room_id = grr_sql_query1("select room_id from agt_loc where id='".$_id."'");
if (authGetUserLevel(getUserName(),$room_id) < 3)
{
    fatal_error(0,"Opération interdite");
    exit();
}


// j'ai besoin de $repeat_id '
$sql = "select repeat_id from agt_loc where id =".$_id;
$res = grr_sql_query($sql);
if (! $res) fatal_error(0, grr_sql_error());
$row = grr_sql_row($res, 0);
$repeat_id = $row['0'];

// Initialisation
$series = 0;
if ($_moderate == "S1") {
     $_moderate = "1";
     $series = 1;
}
if ($_moderate == "S0") {
     $_moderate = "0";
     $series = 1;
}

if ($series==0) {
    //moderation de la ressource
    if ($_moderate == 1) {
        $sql = "update agt_loc set moderate = 2 where id = ".$_id;
    } else {
        $sql = "update agt_loc set moderate = 3 where id = ".$_id;
    }
    $res = grr_sql_query($sql);
    if (! $res) fatal_error(0, grr_sql_error());

    if (!(grr_backup($_id,$_SESSION['login'],$_description))) fatal_error(0, grr_sql_error());
    $tab_id_moderes = array();
} else { // cas d'une série
    // on constitue le tableau des id de la périodicité
    $sql = "select id from agt_loc where repeat_id=".$repeat_id;
    $res = grr_sql_query($sql);
    if (! $res) fatal_error(0, grr_sql_error());
    $tab_entry = array();
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        $tab_entry[] = $row['0'];
    }
    $tab_id_moderes = array();
    // Boucle sur les résas
    foreach ($tab_entry as $entry_tom) {
        $test = grr_sql_query1("select count(id) from agt_loc_moderate where id = '".$entry_tom."'");
        // Si il existe déjà une entrée dans agt_loc_moderate, cela signifie que la réservation a déjà été modérée.
        // Sinon :
        if ($test == 0) {
            //moderation de la ressource
            if ($_moderate == 1) {
                $sql = "update agt_loc set moderate = 2 where id = '".$entry_tom."'";
            } else {
                $sql = "update agt_loc set moderate = 3 where id = '".$entry_tom."'";
           }
           $res = grr_sql_query($sql);
           if (! $res) fatal_error(0, grr_sql_error());

           if (!(grr_backup($entry_tom,$_SESSION['login'],$_description))) fatal_error(0, grr_sql_error());           // Backup : on enregistre les infos dans agt_loc_moderate
           // On constitue un tableau des réservations modérées
           $tab_id_moderes[] = $entry_tom;
        }
    }
}

// Avant d'effacer la réservation, on procède à la notification par mail, uniquement si la salle n'a pas déjà été modérée.
if ($send_mail=="yes")
   send_mail($_id,6,$dformat,$tab_id_moderes);

//moderation de la ressource
if ($_moderate != 1) {
    // on efface l'entrée de la base
    if ($series==0) {
        $sql = "delete from agt_loc where id = ".$_id;
        $res = grr_sql_query($sql);
        if (! $res) fatal_error(0, grr_sql_error());
    } else {
        // On sélectionne toutes les réservation de la périodicité
        $res = grr_sql_query("select id from agt_loc where repeat_id='".$repeat_id."'");
        if (! $res) fatal_error(0, grr_sql_error());
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
            $entry_tom = $row['0'];
            // Pour chaque réservation, on teste si celle-ci a été refusée
            $test = grr_sql_query1("select count(id) from agt_loc_moderate where id = '".$entry_tom."' and moderate='3'");
            // Si oui, on supprime la réservation
            if ($test > 0)
                $del = grr_sql_query("delete from agt_loc where id = '".$entry_tom."'");
        }
        // On supprime l'info de périodicité
        $del_repeat = grr_sql_query("delete from agt_repeat where id='".$repeat_id."'");
        $dupdate_repeat = grr_sql_query("update agt_loc set repead_id = '0' where repead_id='".$repeat_id."'");
    }
}
}
//=================== non venu par mohan
function nonvenu_entry_do($_id,$_moderate,$_description,$cause_desc,$cause)
 {
	global $dformat;
	
	// On vérifie que l'utilisateur a bien le droit d'être ici
	$room_id = grr_sql_query1("select room_id from agt_loc where id='".$_id."'");
	if (authGetUserLevel(getUserName(),$room_id) < 3)
	{
	    fatal_error(0,"Opération interdite");
	    exit();
	}
	
	// récupare les champs de table agt_loc
	$sql = "select * from agt_loc where id =".$_id;
	$res = grr_sql_query($sql);
	if (! $res) fatal_error(0, grr_sql_error());
	$row = grr_sql_row($res, 0);
	$repeat_id = $row['0'];

	// transfer
    grr_sql_begin();			
	$sql = "INSERT INTO `grr_nonvenu` (`start_time`, `end_time`, `entry_type`, `repeat_id`, `room_id`, `timestamp`, `create_by`, `beneficiaire_ext`, `beneficiaire`, `name`, `type`, `protocole`, `description`, `statut_entry`, `option_reservation`, `overload_desc`, `moderate`, `jours`, `pmsi`) 
	(SELECT `start_time`, `end_time`, `entry_type`, `repeat_id`, `room_id`, `timestamp`, `create_by`, `beneficiaire_ext`, `beneficiaire`, `name`, `type`, `protocole`, `description`, `statut_entry`, `option_reservation`, `overload_desc`, `moderate`, `jours`, `pmsi` from agt_loc where id=".$_id.")";
	$res = grr_sql_query($sql);
	if (! $res) fatal_error(0, grr_sql_error());

	// get last id
	$sql="select max(id) from grr_nonvenu";
	$res = grr_sql_query($sql);
	$row = grr_sql_row($res, 0);
	$n_id = $row['0'];	
	if (! $res) fatal_error(0, grr_sql_error());	
	// update le cause 
	$sql="UPDATE grr_nonvenu set cause='".$cause."',desc_cause='".$cause_desc."' where id=".$n_id;
	$res = grr_sql_query($sql);
	if (! $res) fatal_error(0, grr_sql_error());	



    grr_sql_commit();


    grr_sql_begin();
    // On vérifie les dates
    $room_id = grr_sql_query1("SELECT agt_loc.room_id FROM agt_loc, agt_room WHERE agt_loc.room_id = agt_room.id AND agt_loc.id='".$id."'");
    $date_now = time();
    get_planning_area_values($area); // Récupération des données concernant l'affichage du planning du domaine
    $result = mrbsDelEntry(getUserName(), $_id, 0, 1);
    grr_sql_commit();


}


//=================== Patients ajoute en local par mohan
function Patients_add($noip,$nom,$prenom,$njf,$ddn,$sexe)
 {
	// on vérify le patient existe daja dans le table
	$sql="select * FROM agt_pat where noip='".$noip."'";
	$res = select($sql);
	if (count($res) < 1 ){
		$ddn=date_2Mysql($ddn);
		$sql="INSERT INTO agt_pat (noip,nom,prenom,nomjf,ddn,sex)values('$noip','$nom','$prenom','$njf','$ddn','$sexe' )";
		$res = grr_sql_query($sql);
		if (!$res) fatal_error(0, grr_sql_error());		
	}
}
//=================== GetProtocolesByUrm by mohan 
	function get_protocole($service_id,$overload_desc){
		$sql="Select id from agt_overload where fieldname='Protocole' and id_area='".$service_id."'";
		$res = grr_sql_query($sql);
		$row = grr_sql_row($res, 0);

		$id=$row['0'];	
      $begin_string = "@".$id."@";
      $end_string = "@/".$id."@";
      $data = "";
      $begin_pos = strpos($overload_desc,$begin_string);
      $end_pos = strpos($overload_desc,$end_string);		
 		$first = $begin_pos + strlen($begin_string);
      $data = substr($overload_desc,$first,$end_pos-$first);
		$data=   urldecode($data);
      return $data;
	}
?>
