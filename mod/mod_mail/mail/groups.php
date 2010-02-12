<?php  // $Id: compose.php,v 1.92 2006/04/09 10:59:39 stronk7 Exp $
/// This page prints a message

    require_once("../../config.php");
    require_once("lib.php");
	require_once("locallib.php");

	$id = optional_param('id', 0, PARAM_INT);			//Course Module Id
	$m = optional_param('m', 0, PARAM_INT);            // mail ID
	$delete = optional_param('delete', 0, PARAM_INT);    // Group ID to delete
	$edit = optional_param('edit', 0, PARAM_INT);
	$addnew = optional_param('addnew', 0, PARAM_BOOL);
	$clean = optional_param('clean', 0, PARAM_BOOL);
	$adduser = optional_param('adduser', 0, PARAM_BOOL);
    $removeuser = optional_param('removeuser', 0, PARAM_BOOL);
	$save = optional_param('save', 0, PARAM_BOOL);
    
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
	
	if (!isset($SESSION->selectedto)) {
		$SESSION->selectedto = array();
	}
	
	if ($clean) {
		unset($SESSION->selectedto);
		unset($SESSION->groupname);
	}
	
	if ($delete) {
		
		if ($groups = get_record("mail_groups", "id", $delete)) {
		
			if (isteacher($course->id) or isadmin()) {
			
				if (!delete_records('mail_groups', 'id', $delete)) {
					error("Could not delete this group");
				}
			
				delete_records('mail_members_groups', 'groupid', $delete);
				
				$urlfolder = $CFG->wwwroot."/mod/mail/groups.php?id=".$id;
				redirect($urlfolder);
			} else {
				error("Could not delete this group");
			}

		}
		
	}
	
	if ($edit) {
		if (isteacher($course->id) or isadmin()) {
			if (!$groupedit = get_record("mail_groups", "id", $edit)) {
				error("Could not edit this group");
			} 
		}
	}
	
	if ($post = data_submitted()) {
		
		$SESSION->groupname = stripslashes($post->name);
		
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
        
		}
		
		if (isset($post->save)) {
			if(isset($post->addnew) and !empty($SESSION->selectedto))
    		{
				if (empty($post->name)) {
					error('Could not create new group');
				}
			
				$newgroup = new object;
            	$newgroup->mailid = $mail->id;
				$newgroup->name = $post->name;
				$newgroup->timemodified = time();
			
				if (!$newgroupid = insert_record('mail_groups', $newgroup)) {
                	error('Could not create new group');
            	}else {
					add_to_log($course->id, "mail", "new group", "groups.php?id=$id", $mail->id, $cm->id);
				}
				
				foreach ($SESSION->selectedto as $seluser) {
					$newto = new object;
            		$newto->groupid = $newgroupid;
            		$newto->userid = $seluser;
					$newto->timemodified = time();

           			$newid = insert_record('mail_members_groups', $newto);
                	
				}
				
			}
		
			if(isset($post->edit) and !empty($SESSION->selectedto))
    		{
				$updategroup = new object;
            	$updategroup->id = $edit;
				$updategroup->name = $post->name;
			
				if (!update_record('mail_groups', $updategroup)) {
                	error('Could not update group');
            	}else {
					add_to_log($course->id, "mail", "update group", "groups.php?id=$id", $mail->id, $cm->id);
				}
				
				delete_records('mail_members_groups', 'groupid', $edit);
				
				foreach ($SESSION->selectedto as $seluser) {
					$newto = new object;
            		$newto->groupid = $edit;
            		$newto->userid = $seluser;
					$newto->timemodified = time();

           			$newid = insert_record('mail_members_groups', $newto);
                	
				}
				
			}
			
			$salvado = true;
		
		}
		
		if ($salvado) {
			unset($SESSION->selectedto);
			unset($SESSION->groupname);
			$urlfolder = $CFG->wwwroot."/mod/mail/groups.php?id=".$id;
			redirect($urlfolder);
		} else {
			//redirect($SESSION->fromurl);
		}
		
    } else {
		if ($edit) {
			unset($SESSION->selectedto);
			if ($membersgroupedit = get_records("mail_members_groups", "groupid", $edit)) {
				foreach ($membersgroupedit as $membergroupedit) {
					$SESSION->selectedto[$membergroupedit->userid] = $membergroupedit->userid;
				}
			}
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
    
	
/// If no search results then get potential students for this course excluding users already in course

	if (count($SESSION->selectedto) > 0) {
		$existinguserlist = implode(',', $SESSION->selectedto);
	} else {
		$existinguserlist = "";
	}
	
	if (!$students = get_course_students($course->id, "u.firstname ASC, u.lastname ASC, u.username ASC", "", 0, 99999, '', '', NULL, '', 'u.id,u.username,u.firstname,u.lastname',$existinguserlist)) 		
	{
        $students = array();
    }
    
	if (!$teachers = get_course_teachers($course->id, '', $existinguserlist)) {
        $teachers = array();
    }
	
	$numusers = count($students) + count($teachers);
	

	mail_start_print_table_main($mail, $cm, $course);
	
	print_heading_block("<center>".get_string("groups","mail")."</center>"); 
	
	echo "<br>";
	
	include('groups.html');
	
	mail_end_print_table_main($mail);
	

/// Finish the page

    print_footer($course);

?>

