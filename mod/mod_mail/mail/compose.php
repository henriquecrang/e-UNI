<?php  // $Id: compose.php,v 1.92 2006/04/09 10:59:39 stronk7 Exp $
/// This page prints a message

    require_once("../../config.php");
    require_once("lib.php");
	require_once("locallib.php");
	require_once("$CFG->libdir/uploadlib.php");
	require_once("$CFG->libdir/filelib.php");

	$id = optional_param('id', 0, PARAM_INT);			//Course Module Id
    $m = optional_param('m', 0, PARAM_INT);            // mail ID
    $clean = optional_param('clean', 0, PARAM_BOOL);
	$adduser = optional_param('adduser', 0, PARAM_BOOL);
    $removeuser = optional_param('removeuser', 0, PARAM_BOOL);
	$addgroup = optional_param('addgroup', 0, PARAM_BOOL);
	$send = optional_param('send', 0, PARAM_BOOL);
	$reply = optional_param('reply', 0, PARAM_INT);
	$forward = optional_param('forward', 0, PARAM_INT);

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
        if (! $mail = get_record("mail", "id", $m)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $mail->course)) {
            error("Could not determine which course this belonged to!");
        }
        if (!$cm = get_coursemodule_from_instance("mail", $mail->id, $course->id)) {
            error("Could not determine which course module this belonged to!");
        }
        $id = $cm->id;
    } else if (!empty($reply)) {
        if (! $message = get_record("mail_messages", "id", $reply)) {
            error("Message Id is incorrect");
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
			error("Could not reply this message!");
		}
        $id = $cm->id;
	} else if (!empty($forward)) {
        if (! $message = get_record("mail_messages", "id", $forward)) {
            error("Message Id is incorrect");
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
			error("Could not reply this message!");
		}
        $id = $cm->id;
	} else {
        error("Must specify mail ID or course module ID");
    }

    if ($CFG->forcelogin) {
        require_login();
    }
	
	$SESSION->fromurl = $_SERVER["HTTP_REFERER"];
	
	if (!isset($SESSION->selectedto)) {
		$SESSION->selectedto = array();
	}
	
	if ($clean) {
		unset($SESSION->selectedto);
		unset($SESSION->subject);
		unset($SESSION->message);
	}
	
	if ($post = data_submitted()) {
		
		$SESSION->subject = stripslashes($post->subject);
		$SESSION->message = stripslashes($post->message);
		
		if ($adduser and !empty($post->selectusers)) {
            
            foreach ($post->selectusers as $adduser) {
                $newuser = clean_param($adduser, PARAM_INT);
                $SESSION->selectedto[$newuser] = $newuser;
            }
			
        } else if ($removeuser and !empty($post->selectedto)) {
            
			$temp = array();
			
			foreach ($SESSION->selectedto as $seluser) {
				if (!in_array($seluser, $post->selectedto)) {
					$temp[$seluser] = $seluser;		
				}
			}
			unset ($SESSION->selectedto);
			$SESSION->selectedto = $temp;
        
		} else if ($addgroup and !empty($post->selectgroups)) {
			
			foreach ($post->selectgroups as $addgroup) {
                $newgroup = clean_param($addgroup, PARAM_INT);
                $membergroups = get_records("mail_members_groups", "groupid", $newgroup);
				foreach ($membergroups as $newuser) {
					$SESSION->selectedto[$newuser->userid] = $newuser->userid;
				}
            }
		}
		
		
		if ($send and !empty($SESSION->selectedto)) {
			
			if (empty($post->subject)) {
				$post->subject = get_string("nosubject", "mail");
			}
			
			$folderoutput = get_record("mail_folder", "mailid", $mail->id, "type", "S");
			$folderinput = get_record("mail_folder", "mailid", $mail->id, "type", "E");
			
			$um = new upload_manager('newfile', false, true, $course, false, $CFG->mail_maxbytes);		
			
			$newmessage = new object;
            $newmessage->mailid = $mail->id;
            $newmessage->userid = $USER->id;
			$newmessage->fromid = $USER->id;
			$newmessage->folderid = $folderoutput->id;
			$newmessage->subject = clean_param(strip_tags($post->subject), PARAM_CLEAN);
			$newmessage->message = $post->message;
			
			if ($_FILES['newfile']['name']) {
				$newmessage->archivo = $_FILES['newfile']['name'];
			} else {
				if ($forward) {
					$newmessage->archivo = $message->archivo;
				} else {
					$newmessage->archivo = "";
				}
			}
				
			$newmessage->leido = 1;
			$newmessage->responded = 0;
			$newmessage->borrado = 0;
			$newmessage->timemodified = time();
			
			if (!$newmessageid = insert_record('mail_messages', $newmessage)) {
                error('Could not create new message');
            }else {
				add_to_log($course->id, "mail", "send message", "compose.php?id=$cm->id", $mail->id, $cm->id);
			}
			
			
			if (!empty($_FILES['newfile']['name']))
        	{
				if ($basedir = file_area($course->id, $mail->id, $newmessageid)) {
					$filearea = file_area_name($course->id, $mail->id, $newmessageid);
            		if(!$res = $um->process_file_uploads($filearea)) {
						//borrar el mensaje insertado
						delete_records('mail_messages', 'id', $newmessageid);
						error(get_string("filenotvalid","mail"));
					} else {
						$updatemessage = new object;
						$updatemessage->id = $newmessageid;
						$updatemessage->archivo = $um->get_new_filename();
						$_FILES['newfile']['name'] = $um->get_new_filename();
					
            			update_record('mail_messages', $updatemessage);
					}
				} else {
					error(get_string("filenotvalid","mail"));
				}
			}
			
			if ($forward and !empty($message->archivo))
        	{
				$filearea = file_area_name($course->id, $mail->id, $message->id);
				
				if ($basedir = file_area($course->id, $mail->id, $newmessageid)) {
					$filearea2 = file_area_name($course->id, $mail->id, $newmessageid);
            		$origen = $CFG->dataroot."/".$filearea."/".$message->archivo;
					$destino = $CFG->dataroot."/".$filearea2."/".$message->archivo;
					copy($origen, $destino);
				}
			}
			
			foreach ($SESSION->selectedto as $seluser) {
				$newto = new object;
            	$newto->messageid = $newmessageid;
            	$newto->toid = $seluser;
				$newto->timemodified = time();

           		if (!$newid = insert_record('mail_to_messages', $newto)) {
                	error('Could not create new to message');
            	}
				
				$newmessage = new object;
            	$newmessage->mailid = $mail->id;
            	$newmessage->userid = $seluser;
				$newmessage->fromid = $USER->id;
				$newmessage->folderid = $folderinput->id;
				$newmessage->subject = clean_param(strip_tags($post->subject), PARAM_CLEAN);
				$newmessage->message = $post->message;
				
				if ($_FILES['newfile']['name']) {
					$newmessage->archivo = $_FILES['newfile']['name'];
				} else {
					if ($forward) {
						$newmessage->archivo = $message->archivo;
					} else {
						$newmessage->archivo = "";
					}
				}
				
				$newmessage->leido = 0;
				$newmessage->responded = 0;
				$newmessage->borrado = 0;
				$newmessage->timemodified = time();
				
				if (!$newid = insert_record('mail_messages', $newmessage)) {
                	error('Could not create new message');
            	}
				
				if (!empty($_FILES['newfile']['name']))
        		{
					if ($basedir = file_area($course->id, $mail->id, $newid)) {
						$filearea2 = file_area_name($course->id, $mail->id, $newid);
            			$origen = $CFG->dataroot."/".$filearea."/".$_FILES['newfile']['name'];
						$destino = $CFG->dataroot."/".$filearea2."/".$_FILES['newfile']['name'];
						copy($origen, $destino);
					}
				}
				
				if ($forward and !empty($message->archivo)) {
					$filearea = file_area_name($course->id, $mail->id, $message->id);
					
					if ($basedir = file_area($course->id, $mail->id, $newid)) {
						$filearea2 = file_area_name($course->id, $mail->id, $newid);
            			$origen = $CFG->dataroot."/".$filearea."/".$message->archivo;
						$destino = $CFG->dataroot."/".$filearea2."/".$message->archivo;
						copy($origen, $destino);
					}
				}	
				
				$newto = new object;
            	$newto->messageid = $newid;
            	$newto->toid = $seluser;
				$newto->timemodified = time();

           		if (!$newid = insert_record('mail_to_messages', $newto)) {
                	error('Could not create new to message');
            	}
				
			}
			
			if ($reply) {
				$updatemessage = new object;
				$updatemessage->id = $message->id;
				$updatemessage->responded = 1;
					
            	update_record('mail_messages', $updatemessage);
			}		
			
			mail_insert_statistics($course->id, $USER->id, $SESSION->selectedto);
			
			$enviado = true;
			
		}
		
		if ($enviado) {
			unset($SESSION->selectedto);
			unset($SESSION->subject);
			unset($SESSION->message);
			redirect("view.php?id=".$id);
		} else {
			//redirect($SESSION->fromurl);
		}
		
    } else {
		if ($reply) {
			$userfrom = get_record("user", "id", $message->fromid);
			$fullname = $userfrom->firstname." ".$userfrom->lastname."(".$userfrom->username.")";
			$SESSION->selectedto[$message->fromid] = $message->fromid;
			$SESSION->subject = "RE: ".$message->subject;
			$SESSION->message = "<br /><br />".$fullname." ".get_string("wrote","mail").":<br /> &lt;--------------------------------------------------<br />".$message->message."<br />--------------------------------------------------&gt;";
		}
		
		if ($forward) {
			$SESSION->subject = "FW: ".$message->subject;
			$SESSION->message = "<br />&lt;--------------------------------------------------<br />".$message->message."<br />--------------------------------------------------&gt;";
		}
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
    
	
/// Get all existing students, teachers and groups for this course.
    
	if (count($SESSION->selectedto) > 0) {
		$existinguserlist = implode(',', $SESSION->selectedto);
	} else {
		$existinguserlist = "";
	}
	
	if (!$students = get_course_students($course->id, "u.lastname ASC, u.firstname ASC, u.username ASC", "", 0, 99999, '', '', NULL, '', 'u.id,u.username,u.firstname,u.lastname',$existinguserlist)) 		
	{
        $students = array();
    }
    
	if (!$teachers = get_course_teachers($course->id, "u.lastname ASC, u.firstname ASC, u.username ASC", $existinguserlist)) {
        $teachers = array();
    }
	
	$numusers = count($students) + count($teachers);
	
	
	if (isteacher($cm->course) or isadmin()) {
		if (!$groups = get_records("mail_groups", "mailid", $mail->id, "name ASC")) {
        	$groups = array();
    	}
	}	
	
/// If no search results then get potential students for this course excluding users already in course


	$usehtmleditor = can_use_html_editor();

	mail_start_print_table_main($mail, $cm, $course);
	
	print_heading_block("<center>".get_string("foldercompose","mail")."</center>");
	
	echo "<br>";
	
	include('compose.html');
	
	mail_end_print_table_main($mail);
	
	
	if ($usehtmleditor) {
		use_html_editor("message");
	}

/// Finish the page

    print_footer($course);

?>

