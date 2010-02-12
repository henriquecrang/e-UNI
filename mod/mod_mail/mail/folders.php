<?php  // $Id: compose.php,v 1.92 2006/04/09 10:59:39 stronk7 Exp $
/// This page prints a message

    require_once("../../config.php");
    require_once("lib.php");
	require_once("locallib.php");

	$id = optional_param('id', 0, PARAM_INT);			//Course Module Id
	$m = optional_param('m', 0, PARAM_INT);            // mail ID
	$delete = optional_param('delete', 0, PARAM_INT);    // Folder ID to delete
	$edit = optional_param('edit', 0, PARAM_INT);
	$addnew = optional_param('addnew', 0, PARAM_BOOL);
	$confirm = optional_param('confirm', 0, PARAM_INT); //si se confirma el delete
    
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
	} else {
        error("Must specify Course Module ID");
    }

    if ($CFG->forcelogin) {
        require_login();
    }
	
	$SESSION->fromurl = $_SERVER["HTTP_REFERER"];
	
	if ($delete) {
		
		if ($folder = get_record("mail_folder", "id", $delete)) {
		
			if ($folder->userid == $USER->id) {
				$nummessagesfolder = count_records("mail_messages", "mailid", $mail->id, "userid", $USER->id , "folderid", $delete);
				
				if (($confirm <> 1) and ($nummessagesfolder > 0)) {
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
					
					notice_yesno (get_string("confirmdeletefolder","mail"), "folders.php?id=$cm->id&amp;delete=$delete&amp;confirm=1", "folders.php?id=$cm->id");
					print_footer($course);
					exit;
				} else {
				
					if (!delete_records('mail_folder', 'id', $delete)) {
						error("Could not delete this folder");
					}
			
					if (!$messages = get_records_sql("SELECT * FROM {$CFG->prefix}mail_messages  
               		WHERE mailid = $mail->id and userid = $USER->id and folderid = $delete")) {
						$messages = array();
					}
				
					foreach ($messages as $message) {
						//actualizar a borrado
						$updatemessage = new object;
						$updatemessage->id = $message->id;
						$updatemessage->borrado = 1;
			
						update_record('mail_messages', $updatemessage);
					}
			
					$urlfolder = $CFG->wwwroot."/mod/mail/folders.php?id=".$id;
					redirect($urlfolder);
				}
			} else {
				error("Could not delete this folder");
			}

		}
		
	}
	
	if ($edit) {
		
		if ($folderedit = get_record("mail_folder", "id", $edit)) {
		
			if ($folderedit->userid != $USER->id) {
				error("Could not edit this folder");
			}
		}
	}
	
	if ($post = data_submitted()) {
		
		if(isset($post->addnew))
    	{
			if (empty($post->name)) {
				error('Could not create new folder');
			}
			
			$newfolder = new object;
            $newfolder->mailid = $mail->id;
			$newfolder->userid = $USER->id;
			$newfolder->name = $post->name;
			$newfolder->timemodified = time();
			
			if (!$newfolderid = insert_record('mail_folder', $newfolder)) {
                error('Could not create new folder');
            }else {
				add_to_log($course->id, "mail", "new folder", "folders.php?id=$id", $mail->id, $cm->id);
			}
		}
		
		if(isset($post->edit))
    	{
			if (empty($post->name)) {
				error('Could not create new folder');
			}
			
			$updatefolder = new object;
            $updatefolder->id = $edit;
			$updatefolder->name = $post->name;
			
			if (!update_record('mail_folder', $updatefolder)) {
                error('Could not update folder');
            }else {
				add_to_log($course->id, "mail", "update folder", "folders.php?id=$id", $mail->id, $cm->id);
			}
		}
		
		$urlfolder = $CFG->wwwroot."/mod/mail/folders.php?id=".$id;
		redirect($urlfolder);
		
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
    
	
/// If no search results then get potential students for this course excluding users already in course

	mail_start_print_table_main($mail, $cm, $course);
	
	print_heading_block("<center>".get_string("folders","mail")."</center>"); 
	
	echo "<br>";
	
	include('folders.html');
	
	mail_end_print_table_main($mail);
	

/// Finish the page

    print_footer($course);

?>

