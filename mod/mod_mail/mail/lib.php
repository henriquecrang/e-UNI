<?PHP  // $Id: lib.php,v 1.26.2.1 2006/05/26 11:46:32 skodak Exp $ 
        // modified by mnielsen
        /// Update:  The lib.php now contains only the functions that are
        /// used outside of the portafolio module.  All functions (I hope) that are only local
        /// are now in locallib.php.

/// Library of functions and constants for module portafolio
require_once($CFG->libdir.'/filelib.php');


if (!isset($CFG->mail_maxbytes)) {
    set_config('mail_maxbytes', 512000);  // Default maximum size for all messages
}


/// (replace portafolio with the name of your module and delete this line)

/*******************************************************************/
function mail_add_instance($mail) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.
    global $SESSION;
	global $USER;

    $mail->timemodified = time();
    
    if (!$mail->id = insert_record("mail", $mail)) {
        return false; // bad
    } else {

//se crean las carpetas entrada y salida genéricas que servirán para todos los usuarios de ese mail

		$newobj = new object;
    	$newobj->mailid = $mail->id;
    	$newobj->userid = 0;
		$newobj->name = get_string("folderinput","mail");
		$newobj->type = "E";
		$newobj->timemodified = time();
		if (!$newid = insert_record('mail_folder', $newobj)) {
			error('Could not create the folder input');
		}
	
		$newobj = new object;
    	$newobj->mailid = $mail->id;
    	$newobj->userid = 0;
		$newobj->name = get_string("folderoutput","mail");
		$newobj->type = "S";
		$newobj->timemodified = time();
		if (!$newid = insert_record('mail_folder', $newobj)) {
			error('Could not create the folder output');
		}
	}

    return $mail->id;
}


/*******************************************************************/
function mail_update_instance($mail) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.

    $mail->timemodified = time();
    $mail->id = $mail->instance;
	
    return update_record("mail", $mail);
}


/*******************************************************************/
function mail_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

global $CFG;

    if (! $mail = get_record("mail", "id", "$id")) {
        return false;
    }

    $result = true;

    if (! delete_records("mail", "id", "$mail->id")) {
        $result = false;
    }
	
	if (! delete_records("mail_folder", "mailid", "$mail->id")) {
        $result = false;
    }
	
	if ($messages = get_records_select("mail_messages", "mailid = $mail->id")) {
        foreach ($messages as $message) {
            delete_records("mail_to_messages", "messageid", "$message->id");
			delete_records("mail_messages", "id", "$message->id");
        }
	}
	
	if ($groups = get_records_select("mail_groups", "mailid = $mail->id")) {
        foreach ($groups as $group) {
            delete_records("mail_members_groups", "groupid", "$group->id");
			delete_records("mail_groups", "id", "$group->id");
        }
	}
	
    if (! delete_records("mail_statistics", "course", "$mail->course")) {
        $result = false;
    }

    //$dir = $CFG->dataroot.'/'.$portafolio->course.'/moddata/portafolio/'.$portafolio->id;
    
    //$result = fulldelete($dir);
	
    return $result;
}

/**
 * Given a course object, this function will clean up anything that
 * would be leftover after all the instances were deleted.
 *
 * As of now, this function just cleans the portafolio_default table
 *
 * @param object $course an object representing the course that is being deleted
 * @param boolean $feedback to specify if the process must output a summary of its work
 * @return boolean
 */
function mail_delete_course($course, $feedback=true) {

	$result = true;
    //Se deberán borrar los archivos que se subieron en las carpetas correspondientes
	
    return $result;
}

/*******************************************************************/
function mail_user_outline($course, $user, $mod, $mail) {
/// Return a small object with summary information about what a 
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    if ($mails_noread = count_records_select("mail_messages", "mailid = $mail->id AND fromid <> $user->id AND leido = 0")) {
		
        $return->time = $mail->timemodified;
        $return->info = get_string("mailsnoread", "mail") . ": ". $mailsnoread;
    }
    return $return;
}

/*******************************************************************/
function mail_user_complete($course, $user, $mod, $mail) {
/// Print a detailed representation of what a  user has done with 
/// a given particular instance of this module, for user activity reports.

 /*       print_simple_box_start();
        $table->head = array (get_string("name"),  get_string("page1", "portafolio"),
            get_string("page2", "portafolio"), get_string("score", "portafolio"));
        $table->width = "100%";
        $table->align = array ("left", "center", "center", "center");
        $table->size = array ("*", "*", "*", "*");
        $table->cellpadding = 2;
        $table->cellspacing = 0;

        $nostart = get_string("nostart", "portafolio");
		$process = get_string("process", "portafolio");
		$finally = get_string("finally", "portafolio");
        
		if ($reg_score = get_record_select("portafolio_scores", "portafolioid = $portafolio->id AND userid = $user->id")) {
			$score = number_format($reg_score->score, 2);
		}else {
			$score = 0;
		}
		
        $table->data[] = array($user->name, $finally, $process, $score);
      
        print_table($table);
        print_simple_box_end();
    */
    return true;
}

/*******************************************************************/
function mail_print_recent_activity($course, $isteacher, $timestart) {
/// Given a course and a time, this module should find recent activity 
/// that has occurred in portafolio activities and print it out. 
/// Return true if there was output, or false is there was none.

    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/*******************************************************************/
function mail_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such 
/// as sending out mail, toggling flags etc ... 

    global $CFG;

    return true;
}

/*******************************************************************/
function mail_get_participants($mailid) {
//Must return an array of user records (all data) who are participants
//for a given instance of portafolio. Must include every user involved
//in the instance, independient of his role (student, teacher, admin...)

    global $CFG;
    
    //Get students
    $students = get_records_sql("SELECT DISTINCT u.id, u.id
                                 FROM {$CFG->prefix}user u,
                                      {$CFG->prefix}mail_messages a
                                 WHERE a.mailid = '$mailid' and
                                       u.id = a.fromid");

    //Return students array (it contains an array of unique users)
    return ($students);
}


function mail_get_view_actions() {
    return array('view','view all');
}

function mail_get_post_actions() {
    return array('add message','update message','edit message','delete message');
}


?>
