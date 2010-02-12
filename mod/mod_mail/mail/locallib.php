<?php
/// mnielsen
/// locallib.php is the new lib file for mail module.
/// including locallib.php is the same as including the old lib.php

//////////////////////////////////////////////////////////////////////////////////////
/// Any other mail functions go here.  Each of them must have a name that
/// starts with mail_
//////////////////////////////////////////////////////////////////////////////////////


function mail_print_name_user($u, $course) {
	global $CFG;
	
	$usuario = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user 
               WHERE id = $u");
    
	if ($usuario) {
		$textname = "<a href=\"../../user/view.php?id=$usuario->id&amp;course=$course\">".$usuario->firstname." ".$usuario->lastname."</a>";
	} else {
		$textname = "";
	}
	
	return $textname;
}

function mail_print_name_user_message($u, $course) {
	global $CFG;
	
	$usuario = get_record_sql("SELECT id, firstname, lastname, username FROM {$CFG->prefix}user 
               WHERE id = $u");
    
	if ($usuario) {
		$textname = "<a href=\"../../user/view.php?id=$usuario->id&amp;course=$course\">".$usuario->firstname." ".$usuario->lastname." (".$usuario->username.")</a>";
	} else {
		$textname = "";
	}
	
	return $textname;
}

function mail_print_name_user_message_sort($u) {
	global $CFG;
	
	$usuario = get_record_sql("SELECT id, firstname, lastname, username FROM {$CFG->prefix}user 
               WHERE id = $u");
    
	if ($usuario) {
		$textname = strtolower($usuario->lastname." ".$usuario->firstname." (".$usuario->username.")");
	} else {
		$textname = "";
	}
	
	return $textname;
}

function mail_get_messages_noread($mailid, $userid) {
	global $CFG;
	
	$messages_noread = count_records("mail_messages", "mailid", $mailid, "userid", $userid , "leido", 0);
	
	return $messages_noread;
	
}

function mail_get_messages_course_noread($courseid, $userid) {
	global $CFG;
	
	if ($mail = get_record("mail", "course", $courseid)) {
	
		$messages_noread = count_records("mail_messages", "mailid", $mail->id, "userid", $userid , "leido", 0);
	}  else {
		$messages_noread = 0;
	}
	
	return $messages_noread;
	
}

function mail_get_messagesfolder_noread($folderid, $mailid, $userid) {
	global $CFG;
	
	$messages_noread = count_records_select("mail_messages", "mailid = $mailid and userid = $userid and folderid = $folderid and borrado = 0 and leido = 0");
	
	return $messages_noread;
	
}

function mail_get_messagesfolder($folderid, $mailid, $userid) {
	global $CFG;
	
	$messages = count_records_select("mail_messages", "mailid = $mailid and userid = $userid and folderid = $folderid and borrado = 0");
	
	return $messages;
	
}

function mail_get_messages_delete_noread($mailid, $userid) {
	global $CFG;
	
	$messages_delete = count_records_select("mail_messages", "mailid = $mailid and userid = $userid and borrado = 1 and leido = 0");
	
	return $messages_delete;
	
}

function mail_get_messages_delete($mailid, $userid) {
	global $CFG;
	
	$messages_delete = count_records("mail_messages", "mailid", $mailid, "userid", $userid, "borrado", 1);
	
	return $messages_delete;
	
}

function mail_start_print_table_main($mail, $cm, $course) {

global $CFG;
global $USER;
	
	$ruta = $CFG->wwwroot."/mod/mail/images";
	
	$table1 = new stdClass;

	$strwelcome = "<img align='absmiddle' src='".$ruta."/logo.gif' border='0'> ".get_string("welcome","mail",$course->fullname);

    $table1->head  = array ($strwelcome);
    $table1->align = array ("center");
	$table1->width = "100%";

	print_table($table1);
	
	$table2 = new stdClass;
	
	$textooptions = mail_get_options($mail);
	$textouser = get_string('name').': '.mail_print_name_user($USER->id, $mail->course);
	
    $table2->align = array ("left", "right");
	$table2->width = "100%";
	
	$table2->data[] = array ($textooptions, $textouser);
	
	print_table($table2);
	
	$texto = "<table cellpadding='5' cellspacing='1' class='generaltable' width='100%'>
				<tr><td class='cell' width='25%' align='left'>";
	$texto .= mail_get_menu($mail, $USER->id, $cm);
	
	$texto .= "</td><td class='cell' align='left'>";
	
	echo $texto;
	
}

function mail_end_print_table_main() {
	echo "</td></tr></table>";
}

function mail_get_options($mail) {

global $CFG;

$ruta = $CFG->wwwroot."/mod/mail";

$texto = "<a href='".$ruta."/view.php?m=".$mail->id."'><img align='absmiddle' src='".$ruta."/images/home.gif' border='0' alt='".get_string('home','mail')."'>".get_string('home','mail')."</a><img align='absmiddle' src='".$ruta."/images/separador.gif' border='0'>";

$texto .= "<a href='".$ruta."/compose.php?m=".$mail->id."&amp;clean=1'><img align='absmiddle' src='".$ruta."/images/redactar.gif' border='0' alt='".get_string('foldercompose','mail')."'>".get_string('foldercompose','mail')."</a><img align='absmiddle' src='".$ruta."/images/separador.gif' border='0'>";

if (isteacher($mail->course) or isadmin()) {
	$texto .= "<a href='".$ruta."/groups.php?m=".$mail->id."'><img align='absmiddle' src='".$ruta."/images/grupos.gif' border='0' alt='".get_string('admgroups','mail')."'>".get_string('admgroups','mail')."</a><img align='absmiddle' src='".$ruta."/images/separador.gif' border='0'>";
}

$texto .= "<a href='".$ruta."/folders.php?m=".$mail->id."'><img align='absmiddle' src='".$ruta."/images/adm_carpetas.gif' border='0' alt='".get_string('admfolders','mail')."'>".get_string('admfolders','mail')."</a>";


return $texto;

}

function mail_get_menu($mail, $u, $cm) {

global $CFG;

$ruta = $CFG->wwwroot."/mod/mail";

$texto = "<table border='0' cellpadding='0' cellspacing='0'>";

if($messages_noread = mail_get_messages_noread($mail->id, $u)) {
	$texto .= "<tr><td colspan='2' align='left'><img align='absmiddle' src='".$ruta."/images/carpetas.gif' border='0' alt='".$mail->name."'>".$mail->name." (<b>".$messages_noread."</b>)</td></tr>";
} else {
	$texto .= "<tr><td colspan='2' align='left'><img align='absmiddle' src='".$ruta."/images/carpetas.gif' border='0' alt='".$mail->name."'>".$mail->name."</td></tr>";
}

$texto .= "<tr><td width='30'>&nbsp;</td>";

if ($mailinput = get_record("mail_folder", "mailid", $mail->id, "type", "E")) {
	if($messages_noread = mail_get_messagesfolder_noread($mailinput->id, $mail->id, $u)) {
		$texto .= "<td align='left'><a href='".$ruta."/messages.php?f=".$mailinput->id."'><img align='absmiddle' src='".$ruta."/images/entrantes.gif' border='0' alt='".get_string('folderinput','mail')."'>".get_string('folderinput','mail')." (<b>".$messages_noread."</b>)</a></td></tr>";
	} else {
		$texto .= "<td align='left'><a href='".$ruta."/messages.php?f=".$mailinput->id."'><img align='absmiddle' src='".$ruta."/images/entrantes.gif' border='0' alt='".get_string('folderinput','mail')."'>".get_string('folderinput','mail')."</a></td></tr>";
	}
} else {
	//mostrar error porque no tiene carpeta de entrada
}

$texto .= "<tr><td>&nbsp;</td>";

if ($mailoutput = get_record("mail_folder", "mailid", $mail->id, "type", "S")) {
	if($messages_noread = mail_get_messagesfolder_noread($mailoutput->id, $mail->id, $u)) {
		$texto .= "<td align='left'><a href='".$ruta."/messages.php?f=".$mailoutput->id."'><img align='absmiddle' src='".$ruta."/images/enviados.gif' border='0' alt='".get_string('folderoutput','mail')."'>".get_string('folderoutput','mail')." (<b>".$messages_noread."</b>)</a></td></tr>";
	} else {
		$texto .= "<td align='left'><a href='".$ruta."/messages.php?f=".$mailoutput->id."'><img align='absmiddle' src='".$ruta."/images/enviados.gif' border='0' alt='".get_string('folderoutput','mail')."'>".get_string('folderoutput','mail')."</a></td></tr>";
	}
} else {
	//mostrar error porque no tiene carpeta de entrada
}

$texto .= "<tr><td>&nbsp;</td>";

if($messages_delete = mail_get_messages_delete_noread($mail->id, $u)) {
	$texto .= "<td align='left'><a href='".$ruta."/messages.php?id=".$cm->id."'><img align='absmiddle' src='".$ruta."/images/borrados.gif' border='0' alt='".get_string('folderdelete','mail')."'>".get_string('folderdelete','mail')." (<b>".$messages_delete."</b>)</a></td></tr>";
} else {
	$texto .= "<td align='left'><a href='".$ruta."/messages.php?id=".$cm->id."'><img align='absmiddle' src='".$ruta."/images/borrados.gif' border='0' alt='".get_string('folderdelete','mail')."'>".get_string('folderdelete','mail')."</a></td></tr>";
}

if($folders = get_records_sql("SELECT id, name FROM {$CFG->prefix}mail_folder WHERE mailid = $mail->id and userid = $u and type = 'O'")) {

	foreach($folders as $folder){
		$texto .= "<tr><td>&nbsp;</td>";

		if($messages_noread = mail_get_messagesfolder_noread($folder->id, $mail->id, $u)) {
			$texto .= "<td align='left'><a href='".$ruta."/messages.php?f=".$folder->id."'><img align='absmiddle' src='".$ruta."/images/otras.gif' border='0' alt='".$folder->name."'>".$folder->name." (<b>".$messages_noread."</b>)</a></td></tr>";
		} else {
			$texto .= "<td align='left'><a href='".$ruta."/messages.php?f=".$folder->id."'><img align='absmiddle' src='".$ruta."/images/otras.gif' border='0' alt='".$folder->name."'>".$folder->name."</a></td></tr>";
		}
	}
}

$texto .= "</table>";

return $texto;

}

function mail_get_img_message($message) {
global $CFG;	

	$ruta = $CFG->wwwroot."/mod/mail";
    
	if ($message->responded) {
		$textoimg = "<img align='middle' src='".$ruta."/images/responded.gif' border='0' alt='".get_string("messagereply","mail")."'/>";
	} else {
		if ($message->leido) {       
			$textoimg = "<img align='middle' src='".$ruta."/images/read.gif' border='0' alt='".get_string("messageread","mail")."'/>";
		} else {
			$textoimg = "<img align='middle' src='".$ruta."/images/noread.gif' border='0' alt='".get_string("messagenoread","mail")."'/>";
		}
	}
	
	if (!empty($message->archivo)) {
		$textoimg .= "<img align='middle' src='".$ruta."/images/adjunto.gif' border='0' alt='".get_string("messageattachment","mail")."'/>";
	}
	
	return $textoimg;
}


function mail_print_mark_message_sort($read, $responded) {
	
	
	if ($responded) {
		$mark = 1;
	} else {
		if ($read) {       
			$mark = 2;
		} else {
			$mark = 0;
		}
	}
	
	return $mark;
}

function mail_get_list_members_group($courseid, $groupid) {

global $CFG;

	if (!$members = get_records_sql("SELECT * FROM {$CFG->prefix}mail_members_groups WHERE groupid=$groupid")) 		
	{
        $members = array();
    }
		
	$listtousers = "";
	$i = 0;
		
	foreach ($members as $member) {
		$fullname = mail_print_name_user_message($member->userid, $courseid);
            
		if ($i == 0) {
			$listtousers .= $fullname;
		} else {
			$listtousers .= ", ".$fullname;
		}
			
		$i++;
	}
	
	return $listtousers;
}

function mail_get_list_to_users($courseid, $messageid) {

global $CFG;

	if (!$tousers = get_records_sql("SELECT * FROM {$CFG->prefix}mail_to_messages WHERE messageid=$messageid")) 		
	{
        $tousers = array();
    }
		
	$listtousers = "";
	$i = 0;
		
	foreach ($tousers as $touser) {
		$fullname = mail_print_name_user_message($touser->toid, $courseid);
            
		if ($i == 0) {
			$listtousers .= $fullname;
		} else {
			$listtousers .= ", ".$fullname;
		}
			
		$i++;
	}
	
	return $listtousers;
}

function mail_get_list_to_users_sort($messageid) {

global $CFG;

	if (!$tousers = get_records_sql("SELECT * FROM {$CFG->prefix}mail_to_messages WHERE messageid=$messageid")) 		
	{
        $tousers = array();
    }
		
	$listtousers = "";
	$i = 0;
		
	foreach ($tousers as $touser) {
		$fullname = mail_print_name_user_message_sort($touser->toid);
            
		if ($i == 0) {
			$listtousers .= $fullname;
		} else {
			$listtousers .= ", ".$fullname;
		}
			
		$i++;
	}
	
	return $listtousers;
}


/**
* Creates a directory file name, suitable for make_upload_directory()
*
* @return string path to file area
*/
function file_area_name($courseid, $mailid, $messageid) {
   global $CFG;
    
   return $courseid.'/'.$CFG->moddata.'/mail/'.$mailid.'/'.$messageid;
}

/**
* Makes an upload directory
*
* @param $userid int The user id
* @return string path to file area.
*/
function file_area($courseid, $mailid, $messageid) {
   return make_upload_directory( file_area_name($courseid, $mailid, $messageid) );
}


function mail_get_imgfile($courseid, $mailid, $messageid, $file) {
global $CFG;	

	$filearea = file_area_name($courseid, $mailid, $messageid);
	$icon = mimeinfo('icon', $file);
                    
	if ($CFG->slasharguments) {
		$ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
	} else {
		$ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
	}
                
	$output = '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" height="16" width="16" alt="'.$icon.'" />'.'<a target="_blank" href="'.$ffurl.'" >'.$file.'</a>';
	
	return $output;
}

function mail_sort_array_messages($messages, $sort, $dir) {
	
	$nummessages = count($messages);
	
	$dir = strtolower($dir);
	$sort = strtolower($sort);
	
	switch ($sort) {
		case "mark": $sort="mark"; 
				break; 
		case "from": $sort="fromtext"; 
				break; 
		case "to": $sort="totext"; 
				break; 
		case "subject": $sort="subjectlower"; 
				break; 
		default: $sort="timemodified"; 
				break;
	}
	
	for($i=0; $i < $nummessages; $i++){
    	for($a=0; $a < $nummessages-1; $a++){
			if ($dir == "asc") {
				if($messages[$a]->$sort > $messages[$a+1]->$sort){
					$tmp=$messages[$a+1];
            		$messages[$a+1]=$messages[$a];
            		$messages[$a]=$tmp;
        		}
			} else {
				if($messages[$a]->$sort < $messages[$a+1]->$sort){
					$tmp=$messages[$a+1];
            		$messages[$a+1]=$messages[$a];
            		$messages[$a]=$tmp;
				}
			}
    	}
	}
	
	return $messages;
}

function mail_print_enlace_head_sort($folderid, $id, $textohead, $column, $sort, $dir, $page) {

global $CFG;

	$ruta = $CFG->wwwroot."/mod/mail";
	
	$dir = strtolower($dir);
	$sort = strtolower($sort);

	if ($sort != $column) {
    	$columnicon = "";
        if ($column == "date") {
        	$columndir = "desc";
        } else {
           	$columndir = "asc";
        }		
  	} else {
       	$columndir = $dir == "asc" ? "desc":"asc";
       	if ($column == "date") {
          	$columnicon = $dir == "asc" ? "up":"down";
       	} else {
           	$columnicon = $dir == "asc" ? "down":"up";
       	}
        $columnicon = " <img src='".$ruta."/images/".$columnicon.".gif' alt='".get_string($columnicon,"mail")."'/>";

  	}
    
	if (!empty($folderid)) {
		$textoenlace = "<a href='messages.php?f=".$folderid."&amp;sort=".$column."&amp;dir=".$columndir."&amp;page=".$page."'>".$textohead."</a>".$columnicon;
	} else if(!empty($id)) {
		$textoenlace = "<a href='messages.php?id=".$id."&amp;sort=".$column."&amp;dir=".$columndir."&amp;page=".$page."'>".$textohead."</a>".$columnicon;
	}
     
	return $textoenlace;
}

function mail_print_options_folder($mail, $u, $folder, $id) {

global $CFG;

	if ($folder) {
		$mailinput = get_record("mail_folder", "mailid", $mail->id, "type", "E");
		if ($mailinput->id <> $folder->id) {
			echo "<option value='".$mailinput->id."'>".get_string('folderinput','mail')."</option>";
		}
		$mailoutput = get_record("mail_folder", "mailid", $mail->id, "type", "S");
		if ($mailoutput->id <> $folder->id) {
			echo "<option value='".$mailoutput->id."'>".get_string('folderoutput','mail')."</option>";
		}
		echo "<option value='0'>".get_string('folderdelete','mail')."</option>";
		
		$folders = get_records_sql("SELECT id, name FROM {$CFG->prefix}mail_folder WHERE mailid = $mail->id and userid = $u and type = 'O' and id <> $folder->id");

		foreach($folders as $folder){
			echo "<option value='".$folder->id."'>".$folder->name."</option>";
		}
	} else if ($id) {
		$mailinput = get_record("mail_folder", "mailid", $mail->id, "type", "E");
		echo "<option value='".$mailinput->id."'>".get_string('folderinput','mail')."</option>";
		
		$mailoutput = get_record("mail_folder", "mailid", $mail->id, "type", "S");
		echo "<option value='".$mailoutput->id."'>".get_string('folderoutput','mail')."</option>";
		
		$folders = get_records_sql("SELECT id, name FROM {$CFG->prefix}mail_folder WHERE mailid = $mail->id and userid = $u and type = 'O'");

		foreach($folders as $folder){
			echo "<option value='".$folder->id."'>".$folder->name."</option>";
		}
	}
}

function mail_insert_statistics($courseid, $u, $tousers) {

global $CFG;
	
	if ($statistics = get_record("mail_statistics", "course", $courseid, "userid", $u)) {
		$updatestatistics = new object;
		$updatestatistics->id = $statistics->id;
		$updatestatistics->send = $statistics->send + 1;
		$updatestatistics->timemodified = time();		
					
        update_record('mail_statistics', $updatestatistics);
	} else {
		$newstatistics = new object;
        $newstatistics->course = $courseid;
        $newstatistics->userid = $u;
		$newstatistics->send = 1;
		$newstatistics->received = 0;
		$newstatistics->timemodified = time();
		
        $newid = insert_record('mail_statistics', $newstatistics);        	
	}
	
	foreach ($tousers as $touser) {
		if ($statistics = get_record("mail_statistics", "course", $courseid, "userid", $touser)) {
			$updatestatistics = new object;
			$updatestatistics->id = $statistics->id;
			$updatestatistics->received = $statistics->received + 1;
			$updatestatistics->timemodified = time();		
					
        	update_record('mail_statistics', $updatestatistics);
		} else {
			$newstatistics = new object;
        	$newstatistics->course = $courseid;
        	$newstatistics->userid = $touser;
			$newstatistics->send = 0;
			$newstatistics->received = 1;
			$newstatistics->timemodified = time();
		
        	$newid = insert_record('mail_statistics', $newstatistics);        	
		}
	}
	
}

?>
