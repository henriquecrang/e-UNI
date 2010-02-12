<?php  // $Id: view.php,v 1.92 2006/04/09 10:59:39 stronk7 Exp $
/// This page prints a particular instance of portafolio
    require_once("../../config.php");
    require_once("lib.php");
	require_once("locallib.php");
	
	global $USER;

    $id = optional_param('id', 0, PARAM_INT);           // Course Module ID
    $m = optional_param('m', 0, PARAM_INT);            // Mail ID

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
        error("Must specify mail ID or course module ID");
    }

    if ($CFG->forcelogin) {
        require_login();
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
    add_to_log($course->id, "mail", "view", "view.php?id=$cm->id", $mail->id, $cm->id);

/// Printing the heading
    $strmails = get_string("modulenameplural", "mail");
    $strmail = get_string("modulename", "mail");

    $navigation = "<a href=\"index.php?id=$course->id\">$strmails</a> ->";

    print_header_simple(format_string($mail->name), "",
                 "$navigation ".format_string($mail->name), "", "", true, update_module_button($cm->id, $course->id, $strmail), navmenu($course, $cm));
    
	
	mail_start_print_table_main($mail, $cm, $course);
	
	if ( $mail->summary ) {
        echo "<br />".format_text($mail->summary);
    } else {
		echo "<br />";
	}
	
	mail_end_print_table_main($mail);
	

/// Finish the page

    print_footer($course);

?>
