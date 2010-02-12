<?php  // $Id: compose.php,v 1.92 2006/04/09 10:59:39 stronk7 Exp $
/// This page prints a message

    require_once("../../config.php");
    require_once("lib.php");
	require_once("locallib.php");
	require_once("$CFG->libdir/uploadlib.php");
	require_once("$CFG->libdir/filelib.php");

	$id = optional_param('id', 0, PARAM_INT);			//Course Module Id
	$m = optional_param('m', 0, PARAM_INT);			//Message Id
    $f = optional_param('f', 0, PARAM_INT);            // Folder ID
	$delete = optional_param('delete', 0, PARAM_INT);    // Message ID to delete
	$confirm = optional_param('confirm', 0, PARAM_INT); //si se confirma el delete
	//$restore = optional_param('restore', 0, PARAM_INT);
	$move = optional_param('move', 0, PARAM_INT);
	$markread = optional_param('markread', 'nothing', PARAM_ALPHA);
	$page = optional_param('page', 0, PARAM_INT);
	$sort = optional_param('sort', 'date', PARAM_ALPHA);
	$dir = optional_param('dir', 'DESC', PARAM_ALPHA);
	
	$perpage = 10;
    
	if (!empty($id)) {
		if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
        if (! $mail = get_record("mail", "id", $cm->instance)) {
            error("Course module is incorrect");
        }
    } else if (!empty($m)) {
        if (! $message = get_record("mail_messages", "id", $m)) {
            error("Message ID was incorrect");
        }
		if (! $mail = get_record("mail", "id", $message->mailid)) {
            error("Course module is incorrect");
        }
		if (! $course = get_record("course", "id", $mail->course)) {
            error("Could not determine which course this belonged to!");
        }
        if (!$cm = get_coursemodule_from_instance("mail", $mail->id, $course->id)) {
            error("Could not determine which course module this belonged to!");
        }
		if ($USER->id <> $message->userid) {
			error("Could not view this message!");
		}
    } else if (!empty($f)) {
		if (! $folder = get_record("mail_folder", "id", $f)) {
            error("Folder Id is incorrect");
        }
		if (! $mail = get_record("mail", "id", $folder->mailid)) {
            error("Course module is incorrect");
        }
		if (! $course = get_record("course", "id", $mail->course)) {
            error("Could not determine which course this belonged to!");
        }
        if (!$cm = get_coursemodule_from_instance("mail", $mail->id, $course->id)) {
            error("Could not determine which course module this belonged to!");
        }
        if (($USER->id <> $folder->userid) and (($folder->type <> "E") and ($folder->type <> "S"))) {
			error("Could not view this folder!");
		}
	} else if (!empty($delete)) {
        if (! $message = get_record("mail_messages", "id", $delete)) {
            error("Message Id is incorrect");
        }
		if (! $mail = get_record("mail", "id", $message->mailid)) {
            error("Course module is incorrect");
        }
		if (! $folder = get_record("mail_folder", "id", $message->folderid)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $mail->course)) {
            error("Could not determine which course this belonged to!");
        }
        if (!$cm = get_coursemodule_from_instance("mail", $mail->id, $course->id)) {
            error("Could not determine which course module this belonged to!");
        }
		if ($USER->id <> $message->userid) {
			error("Could not delete this message!");
		}
        $id = $cm->id;
	} else {
        error("Must specify message ID or folder ID");
    }

    if ($CFG->forcelogin) {
        require_login();
    }
	
	$SESSION->fromurl = $_SERVER["HTTP_REFERER"];
	
	if ($delete) {
		
		if ($message->borrado) {
		
			if ($confirm <> 1) {
			
				$navigation = "";
    			if ($course->category) {
        			$navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
        			require_login($course->id);
    			}
    			if (!$cm->visible and !isteacher($course->id)) {
        			print_header();
        			notice(get_string("activityiscurrentlyhidden"));
    			}
    			add_to_log($course->id, "mail", "view", "view.php?id=$cm->id", $mail->id, $cm->id);

				/// Printing the heading
    			$strmails = get_string("modulenameplural", "mail");
    			$strmail = get_string("modulename", "mail");

    			$navigation = "<a href=\"index.php?id=$course->id\">$strmails</a> ->";

    			print_header_simple(format_string($mail->name), "",
                 "$navigation ".format_string($mail->name), "", "", true, update_module_button($cm->id, $course->id, $strmail), navmenu($course, $cm));
					
				notice_yesno (get_string("confirmdeletemessage","mail"), "messages.php?delete=$delete&amp;confirm=1", "messages.php?m=$delete");
				print_footer($course);
				exit;
			
			} else {
				if (!delete_records('mail_messages', 'id', $delete)) {
					error('Could not delete this message');
				} else {
					delete_records('mail_to_messages', 'messageid', $delete);
				}
			
				$dir = $CFG->dataroot.'/'.$mail->course.'/moddata/mail/'.$mail->id.'/'.$delete;
    			$result = fulldelete($dir);
			
				$urlfolder = $CFG->wwwroot."/mod/mail/messages.php?id=".$id;
				redirect($urlfolder);
			}
			
		} else {
			//actualizar a borrado
			$updatemessage = new object;
			$updatemessage->id = $delete;
			$updatemessage->borrado = 1;
			
			update_record('mail_messages', $updatemessage);
			
			$urlfolder = $CFG->wwwroot."/mod/mail/messages.php?f=".$folder->id;
			redirect($urlfolder);
		}
		
	}
	
	if ($post = data_submitted()) {
		
		if(isset($post->delete))
    	{
			if (count($post->ch) > 0) {
				foreach ($post->ch as $sel) {
					if ($message = get_record("mail_messages", "id", $sel)) {
            		
						if ($message->borrado) {
							delete_records('mail_messages', 'id', $sel);
							delete_records('mail_to_messages', 'messageid', $sel);
		
							$dir = $CFG->dataroot.'/'.$mail->course.'/moddata/mail/'.$mail->id.'/'.$sel;
    						$result = fulldelete($dir);
			
						} else {
							//actualizar a borrado
							$updatemessage = new object;
							$updatemessage->id = $sel;
							$updatemessage->borrado = 1;
			
							update_record('mail_messages', $updatemessage);
        				}	
					}
				}
			}	
		}
		
		/*if(isset($post->restore))
    	{
			foreach ($post->ch as $sel) {
				//actualizar a borrado
				$updatemessage = new object;
				$updatemessage->id = $sel;
				$updatemessage->borrado = 0;
			
				update_record('mail_messages', $updatemessage);
			}
		}*/
		
		if(isset($post->markread))
    	{
			if (count($post->ch) > 0 ) {
				if ($post->markread == "read") {
					foreach ($post->ch as $sel) {
						//actualizar a leido
						$updatemessage = new object;
						$updatemessage->id = $sel;
						$updatemessage->leido = 1;
			
						update_record('mail_messages', $updatemessage);
					}
				}
			
				if ($post->markread == "noread") {
					foreach ($post->ch as $sel) {
						//actualizar a no leido
						$updatemessage = new object;
						$updatemessage->id = $sel;
						$updatemessage->leido = 0;
			
						update_record('mail_messages', $updatemessage);
					}
				}
			}
		}
		
		if(isset($post->move) and ($post->move >= 0))
    	{
			if (count($post->ch) > 0 ) {
				foreach ($post->ch as $sel) {
					//actualizar a la carpeta en cuestión
					$updatemessage = new object;
					$updatemessage->id = $sel;
					$updatemessage->folderid = $post->move;
				
					if ($post->move == 0) {
						$updatemessage->borrado = 1;	
					} else {
						$updatemessage->borrado = 0;
					}
			
					update_record('mail_messages', $updatemessage);
				}
			}
		}
		
		redirect($SESSION->fromurl);
		
    }
	

/// Processing standard security processes
    $navigation = "";
    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
        require_login($course->id);
    }
    if (!$cm->visible and !isteacher($course->id)) {
        print_header();
        notice(get_string("activityiscurrentlyhidden"));
    }
    add_to_log($course->id, "portafolio", "view", "view.php?id=$cm->id", $portafolio->id, $cm->id);

/// Processing standard security processes
    $navigation = "";
    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
        require_login($course->id);
    }
    if (!$cm->visible and !isteacher($course->id)) {
        print_header();
        notice(get_string("activityiscurrentlyhidden"));
    }
    add_to_log($course->id, "mail", "view", "view.php?id=$cm->id", $mail->id, $cm->id);

/// Printing the heading
    $strmails = get_string("modulenameplural", "mail");
    $strmail = get_string("modulename", "mail");

    $navigation = "<a href=\"index.php?id=$course->id\">$strmails</a> ->";

    print_header_simple(format_string($mail->name), "",
                 "$navigation ".format_string($mail->name), "", "", true, update_module_button($cm->id, $course->id, $strmail), navmenu($course, $cm));
    

	if ($folder) {
		$limit = sql_paging_limit($page, $perpage);
		
		$numtotalmessages = mail_get_messagesfolder($folder->id, $mail->id, $USER->id);
		
		if (!$messages_all = get_records_sql("SELECT * FROM {$CFG->prefix}mail_messages WHERE mailid=$mail->id and userid=$USER->id and folderid=$f and borrado=0")) 		
		{
        	$messages_all = array();
			$messages = array();
    	}
		
		foreach ($messages_all as $message_all) {
			$messagetemp = new object;
			$messagetemp->id = $message_all->id;
			$messagetemp->mailid = $message_all->mailid;
			$messagetemp->userid = $message_all->userid;
			$messagetemp->mark = mail_print_mark_message_sort($message_all->leido, $message_all->responded);
			$messagetemp->fromid = $message_all->fromid;
			$messagetemp->fromtext = mail_print_name_user_message_sort($message_all->fromid);
			$messagetemp->totext = mail_get_list_to_users_sort($message_all->id);
			$messagetemp->folderid = $message_all->folderid;
			$messagetemp->subject = $message_all->subject;
			$messagetemp->subjectlower = strtolower($message_all->subject);
			$messagetemp->archivo = $message_all->archivo;
			$messagetemp->leido = $message_all->leido;
			$messagetemp->responded = $message_all->responded;
			$messagetemp->borrado = $message_all->borrado;
			$messagetemp->timemodified = $message_all->timemodified;
			$messagestemp[] = $messagetemp;
		}
		
		$messagestemp = mail_sort_array_messages($messagestemp, $sort, $dir);
		
		$limitmessage = ($page+1)*$perpage;
		
		for ($i = $page*$perpage; $i < $limitmessage; $i++) {
			if ($messagestemp[$i]) {
				$messages[] = $messagestemp[$i];
			}
		}
		
	} else if ($message) {
		
		//actualizar a leido
		$updatemessage = new object;
		$updatemessage->id = $message->id;
		$updatemessage->leido = 1;
			
		update_record('mail_messages', $updatemessage);
		
		$listtousers = mail_get_list_to_users($course->id, $message->id);
		
	} else if ($id) {
	//si no se ha puesto ningun $m y tampoco $f, pero si $id, se muestran los mensajes eliminados
		$limit = sql_paging_limit($page, $perpage);
		
		$numtotalmessages = mail_get_messages_delete($mail->id, $USER->id);
		
		if (!$messages_all = get_records_sql("SELECT * FROM {$CFG->prefix}mail_messages WHERE mailid=$mail->id and userid=$USER->id and borrado=1")) 		
		{
        	$messages_all = array();
			$messages = array();
    	}
		
		foreach ($messages_all as $message_all) {
			$messagetemp = new object;
			$messagetemp->id = $message_all->id;
			$messagetemp->mailid = $message_all->mailid;
			$messagetemp->userid = $message_all->userid;
			$messagetemp->mark = mail_print_mark_message_sort($message_all->leido, $message_all->responded);
			$messagetemp->fromid = $message_all->fromid;
			$messagetemp->fromtext = mail_print_name_user_message_sort($message_all->fromid);
			$messagetemp->totext = mail_get_list_to_users_sort($message_all->id);
			$messagetemp->folderid = $message_all->folderid;
			$messagetemp->subject = $message_all->subject;
			$messagetemp->subjectlower = strtolower($message_all->subject);
			$messagetemp->archivo = $message_all->archivo;
			$messagetemp->leido = $message_all->leido;
			$messagetemp->responded = $message_all->responded;
			$messagetemp->borrado = $message_all->borrado;
			$messagetemp->timemodified = $message_all->timemodified;
			$messagestemp[] = $messagetemp;
		}
		
		$messagestemp = mail_sort_array_messages($messagestemp, $sort, $dir);
		
		$limitmessage = ($page+1)*$perpage;
		
		for ($i = $page*$perpage; $i < $limitmessage; $i++) {
			if ($messagestemp[$i]) {
				$messages[] = $messagestemp[$i];
			}
		}
		
	}
	
/// If no search results then get potential students for this course excluding users already in course


	mail_start_print_table_main($mail, $cm, $course);
	
	if ($folder) {
		print_heading_block("<center>".get_string("folder","mail")." ".$folder->name."</center>");
	
	} else if ($id) {
		print_heading_block("<center>".get_string("folder","mail")." ".get_string("folderdelete","mail")."</center>");
	
	} else if ($message) {
		print_heading_block("<center>".get_string("message","mail")."</center>");
	}
	
	echo "<br>";
	
	include('messages.html');
	
	mail_end_print_table_main($mail);
	

/// Finish the page

    print_footer($course);

?>

