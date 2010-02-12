<?PHP // $Id: index.php,v 1.13 2006/04/05 07:46:54 gustav_delius Exp $

/// This page lists all the instances of correo in a particular course

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (!$course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "mail", "view all", "index.php?id=$course->id", "");


/// Get all required strings

    $strmails = get_string("modulenameplural", "mail");
    $strmail  = get_string("modulename", "mail");


/// Print the header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    print_header("$course->shortname: $strmails", "$course->fullname", "$navigation $strmails", "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $mails = get_all_instances_in_course("mail", $course)) {
        notice("There are no mails", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");
	$strsummary  = get_string("summary");
	$strmailsnoread  = get_string("mailsnoread", "mail");
	
    $table = new stdClass;

	
	if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname, $strsummary, $strmailsnoread);
        $table->align = array ("center", "left", "left", "center");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname, $strsummary, $strmailsnoread);
        $table->align = array ("center", "left", "left", "center");
    } else {
        $table->head  = array ($strname, $strsummary, $strmailsnoread);
        $table->align = array ("left", "left", "center");
    }

    
    foreach ($mails as $mail) {
        if (!$mail->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$mail->coursemodule\">".format_string($mail->name,true)."</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$mail->coursemodule\">".format_string($mail->name,true)."</a>";
        }

		$mails_noread = count_records_select("mail_messages", "mailid = $mail->id AND userid = $USER->id AND leido = 0");
	
        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($mail->section, $link, format_text($mail->summary), $mails_noread);
        } else {
            $table->data[] = array ($link, format_text($mail->summary), $mails_noread);
        }
    }

    echo "<br />";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
