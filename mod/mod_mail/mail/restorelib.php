<?PHP //$Id: restorelib.php,v 1.27 2006/05/01 19:02:38 michaelpenne Exp $

    //This php script contains all the stuff to backup/restore
    //mail mods
	//
	//This is the "graphical" structure of the mail mod:
    //
   	//                     mail
    //                    (CL,pk->id)             
	//                        |
    //                        |
    //                 mail_folder 
    //           (UL,pk->id, fk->mailid, fk->userid)
	//                        |
    //                        |
    //                 mail_messages
    //           (UL,pk->id, fk->mailid, fk->userid, fk->fromidm fk->folderid, files)
	//                        |
    //                        |
    //                 mail_to_messages 
    //           (UL,pk->id, fk->messageid, fk->toid)
	//                        |
    //                        |
    //                 mail_groups 
    //           (UL,pk->id, fk->mailid)
	//                        |
    //                        |
    //                 mail_members_groups
    //           (UL,pk->id, fk->groupid, fk->userid)
	//                        |
    //                        |
    //                 mail_statistics
    //           (UL,pk->id, fk->course, fk->userid)
	//
	//
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the restore procedure about this mod
    function mail_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            //traverse_xmlize($info);                                                              //Debug
            //print_object ($GLOBALS['traverse_array']);                                           //Debug
            //$GLOBALS['traverse_array']="";                                                       //Debug
			
			$oldid = backup_todb($info['#']['ID']['0']['#']);

            //Now, build the mail record structure
            $mail->course = $restore->course_id;
            $mail->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $mail->summary = backup_todb($info['MOD']['#']['SUMMARY']['0']['#']);
            $mail->maxbytes = backup_todb($info['MOD']['#']['MAXBYTES']['0']['#']);
            $mail->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);

            //The structure is equal to the db, so insert the mail
            $newid = insert_record("mail", $mail);

            //Do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","mail")." \"".format_string(stripslashes($mail->name),true)."\"</li>";
            }
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newid);
                //We have to restore the mail which are held in their logical order...
                $userdata = restore_userdata_selected($restore,"mail",$mod->id);
                
				$status = mail_folders_restore_mods($mod->id,$newid,$info,$restore,$userdata);
				
                //...and the user grades, high scores, and timer (if required)
                if ($status) {
                    if ($userdata) {
                        if (!mail_groups_restore_mods($newid,$info,$restore)) {
							return false;
                        }
						if (!mail_statistics_restore_mods($restore->course_id,$info,$restore)) {
							return false;
                        }
                    }
                }
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }
        return $status;
    }

    //This function restores the mail_folders
    function mail_folders_restore_mods($oldmailid,$mailid,$info,$restore,$userdata) {

        global $CFG;

        $status = true;

        //Get the mail_elements array
        $folders = $info['MOD']['#']['FOLDERS']['0']['#']['FOLDER'];

        //Iterate over mail folders (they are held in their logical order)

        for($i = 0; $i < sizeof($folders); $i++) {
            $folder_info = $folders[$i];
         
            //We'll need this later!!
            $oldid = backup_todb($folder_info['#']['ID']['0']['#']);
			$olduserid = backup_todb($folder_info['#']['USERID']['0']['#']);

            //Now, build the mail_folder record structure
            $folder->mailid = $mailid;
			$folder->userid = backup_todb($folder_info['#']['USERID']['0']['#']);
            $folder->name = backup_todb($folder_info['#']['NAME']['0']['#']);
			$folder->type = backup_todb($folder_info['#']['TYPE']['0']['#']);
            $folder->timemodified = backup_todb($folder_info['#']['TIMEMODIFIED']['0']['#']);

			//We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$folder->userid);
            if ($user) {
                $folder->userid = $user->new_id;
            }

			if ($userdata or $folder->type <> "O") {

              //The structure is equal to the db, so insert the mail_folder
              $newid = insert_record ("mail_folder",$folder);

              //Do some output
              if (($i+1) % 10 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 200 == 0) {
                        echo "<br>";
                    }
                }
                backup_flush(300);
              }

              if ($newid) {
                //We have the newid, update backup_ids (restore logs will use it!!)
                backup_putid($restore->backup_unique_code,"mail_folder", $oldid, $newid);
            
                
                // backup branch table info for branch tables.
                if ($status and $userdata) {
                    if (!mail_messages_restore_mods($oldmailid,$mailid,$newid,$folder_info,$restore)) {
                        return false;
                    }
                }
              } else {
                $status = false;
              }
			
			}
        }

        return $status;
    }

	//This function restores the mail_messages
    function mail_messages_restore_mods($oldmailid,$mailid,$folder_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the posts array
        $messages = $info['#']['MESSAGES']['0']['#']['MESSAGE'];

        //Iterate over posts
        for($i = 0; $i < sizeof($messages); $i++) {
            $message_info = $messages[$i];
           
            //We'll need this later!!
            $oldid = backup_todb($message_info['#']['ID']['0']['#']);
			$olduserid = backup_todb($message_info['#']['USERID']['0']['#']);
			$oldfromid = backup_todb($message_info['#']['FROMID']['0']['#']);

            //Now, build the mail_messages record structure
            $message->mailid = $mailid;
			$message->folderid = $folder_id;
			$message->userid = backup_todb($message_info['#']['USERID']['0']['#']);
			$message->fromid = backup_todb($message_info['#']['FROMID']['0']['#']);
            $message->subject = backup_todb($message_info['#']['SUBJECT']['0']['#']);
			$message->message = backup_todb($message_info['#']['MESSAGE']['0']['#']);
			$message->archivo = backup_todb($message_info['#']['ARCHIVO']['0']['#']);
			$message->leido = backup_todb($message_info['#']['LEIDO']['0']['#']);
			$message->responded = backup_todb($message_info['#']['RESPONDED']['0']['#']);
			$message->borrado = backup_todb($message_info['#']['BORRADO']['0']['#']);
            $message->timemodified = backup_todb($message_info['#']['TIMEMODIFIED']['0']['#']);

			//We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$message->userid);
            if ($user) {
                $message->userid = $user->new_id;
            }
			
			$from = backup_getid($restore->backup_unique_code,"user",$message->fromid);
            if ($from) {
                $message->fromid = $from->new_id;
            }

            //The structure is equal to the db, so insert the forum_posts
            $newid = insert_record ("mail_messages",$message);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

			if ($newid) {
                //We have the newid, update backup_ids (restore logs will use it!!)
                backup_putid($restore->backup_unique_code,"mail_messages", $oldid, $newid);
                
                // backup branch table info for branch tables.
                if ($status) {
                    if (!mail_to_messages_restore_mods($newid,$message_info,$restore)) {
                        return false;
                    }
					
					mail_restore_files($oldmailid, $mailid, $oldid, $newid, $restore);
                    
                }
            } else {
                $status = false;
            }

        }
		
        return $status;
    }
	
	//This function restores the mail_to_messages
    function mail_to_messages_restore_mods($message_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the posts array
        $to_messages = $info['#']['TO_MESSAGES']['0']['#']['TO_MESSAGE'];

        //Iterate over posts
        for($i = 0; $i < sizeof($to_messages); $i++) {
            $to_message_info = $to_messages[$i];
           
            //We'll need this later!!
            $oldid = backup_todb($to_message_info['#']['ID']['0']['#']);
			$olduserid = backup_todb($to_message_info['#']['TOID']['0']['#']);
	
            //Now, build the mail_messages record structure
			$to_message->messageid = $message_id;
			$to_message->toid = backup_todb($to_message_info['#']['TOID']['0']['#']);
            $to_message->timemodified = backup_todb($to_message_info['#']['TIMEMODIFIED']['0']['#']);

			//We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$to_message->toid);
            if ($user) {
                $to_message->toid = $user->new_id;
            }

            //The structure is equal to the db, so insert the forum_posts
            $newid = insert_record ("mail_to_messages",$to_message);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

			if (!$newid) {
                $status = false;
            }

        }
		
        return $status;
    }
	
	function mail_groups_restore_mods($mailid,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the mail_elements array
        $groups = $info['MOD']['#']['GROUPS']['0']['#']['GROUP'];

        //Iterate over mail groups (they are held in their logical order)

        for($i = 0; $i < sizeof($groups); $i++) {
            $group_info = $groups[$i];
         
            //We'll need this later!!
            $oldid = backup_todb($group_info['#']['ID']['0']['#']);

            //Now, build the mail_group record structure
            $group->mailid = $mailid;
            $group->name = backup_todb($group_info['#']['NAME']['0']['#']);
            $group->timemodified = backup_todb($group_info['#']['TIMEMODIFIED']['0']['#']);

            //The structure is equal to the db, so insert the mail_group
            $newid = insert_record ("mail_groups",$group);

            //Do some output
            if (($i+1) % 10 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 200 == 0) {
                        echo "<br>";
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids (restore logs will use it!!)
                backup_putid($restore->backup_unique_code,"mail_groups", $oldid, $newid);
                
                // backup branch table info for branch tables.
                if ($status) {
                    if (!mail_members_groups_restore_mods($newid,$group_info,$restore)) {
                        return false;
                    }
                }
            } else {
                $status = false;
            }
			
        }

        return $status;
    }
	
	//This function restores the mail_members_groups
    function mail_members_groups_restore_mods($group_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the posts array
        $members_groups = $info['#']['MEMBERS_GROUPS']['0']['#']['MEMBER_GROUP'];

        //Iterate over posts
        for($i = 0; $i < sizeof($members_groups); $i++) {
            $member_group_info = $members_groups[$i];
           
            //We'll need this later!!
            $oldid = backup_todb($member_group_info['#']['ID']['0']['#']);
			$olduserid = backup_todb($member_group_info['#']['USERID']['0']['#']);
	
            //Now, build the mail_members_groups record structure
			$member_group->groupid = $group_id;
			$member_group->userid = backup_todb($member_group_info['#']['USERID']['0']['#']);
            $member_group->timemodified = backup_todb($member_group_info['#']['TIMEMODIFIED']['0']['#']);

			//We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$member_group->userid);
            if ($user) {
                $member_group->userid = $user->new_id;
            }

            //The structure is equal to the db, so insert the forum_posts
            $newid = insert_record ("mail_members_groups",$member_group);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

			if (!$newid) {
                $status = false;
            }

        }
		
        return $status;
    }
	
	function mail_statistics_restore_mods($courseid,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the mail_elements array
        $statistics = $info['MOD']['#']['STATISTICS']['0']['#']['STATISTIC'];

        //Iterate over mail statistics (they are held in their logical order)

        for($i = 0; $i < sizeof($statistics); $i++) {
            $statistic_info = $statistics[$i];
         
            //We'll need this later!!
            $oldid = backup_todb($statistic_info['#']['ID']['0']['#']);

            //Now, build the mail_statistic record structure
            $statistic->course = $courseid;
			$statistic->userid = backup_todb($statistic_info['#']['USERID']['0']['#']);
            $statistic->received = backup_todb($statistic_info['#']['RECEIVED']['0']['#']);
            $statistic->send = backup_todb($statistic_info['#']['SEND']['0']['#']);
            $statistic->timemodified = backup_todb($statistic_info['#']['TIMEMODIFIED']['0']['#']);

			//We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$statistic->userid);
            if ($user) {
                $statistic->userid = $user->new_id;
            }

            //The structure is equal to the db, so insert the mail_statistic
            $newid = insert_record ("mail_statistics",$statistic);

            //Do some output
            if (($i+1) % 10 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 200 == 0) {
                        echo "<br>";
                    }
                }
                backup_flush(300);
            }

            if (!$newid) {
                $status = false;
            }
			
        }

        return $status;
    }
	
    
	//This function copies the mail related info from backup temp dir to course moddata folder,
    //creating it if needed and recoding everything (mail id and user id) 
    function mail_restore_files ($oldmailid, $newmailid, $oldmessageid, $newmessageid, $restore) {

        global $CFG;


        $status = true;
        $todo = false;
        $moddata_path = "";
        $mail_path = "";
        $temp_path = "";

        //First, we check to "course_id" exists and create is as necessary
        //in CFG->dataroot
        $dest_dir = $CFG->dataroot."/".$restore->course_id;
        $status = check_dir_exists($dest_dir,true);

        //Now, locate course's moddata directory
        $moddata_path = $CFG->dataroot."/".$restore->course_id."/".$CFG->moddata;
   
        //Check it exists and create it
        $status = check_dir_exists($moddata_path,true);

        //Now, locate assignment directory
        if ($status) {
            $mail_path = $moddata_path."/mail";
            //Check it exists and create it
            $status = check_dir_exists($mail_path,true);
        }

        //Now locate the temp dir we are gong to restore
        if ($status) {
            $temp_path = $CFG->dataroot."/temp/backup/".$restore->backup_unique_code.
                         "/moddata/mail/".$oldmailid."/".$oldmessageid;
            //Check it exists
            if (is_dir($temp_path)) {
                $todo = true;
            }
        }

        //If todo, we create the neccesary dirs in course moddata/mail
        if ($status and $todo) {
            //First this assignment id
            $this_mail_path = $mail_path."/".$newmailid;
            $status = check_dir_exists($this_mail_path,true);
            //Now this message id
            $message_mail_path = $this_mail_path."/".$newmessageid;
            //And now, copy temp_path to message_mail_path
            $status = backup_copy_file($temp_path, $message_mail_path); 
        }
       
        return $status;
    }
	
    //Return a content decoded to support interactivities linking. Every module
    //should have its own. They are called automatically from
    //mail_decode_content_links_caller() function in each module
    //in the restore process
    function mail_decode_content_links ($content,$restore) {
            
        global $CFG;
            
        $result = $content;
                
        //Link to the list of mails
                
        $searchstring='/\$@(MAILINDEX)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$content,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course id)
                $rec = backup_getid($restore->backup_unique_code,"course",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(MAILINDEX)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/mail/index.php?id='.$rec->new_id,$result);
                } else { 
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/mail/index.php?id='.$old_id,$result);
                }
            }
        }

        //Link to mail view by moduleid

        $searchstring='/\$@(MAILVIEWBYID)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$result,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course_modules id)
                $rec = backup_getid($restore->backup_unique_code,"course_modules",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(MAILVIEWBYID)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/mail/view.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/mail/view.php?id='.$old_id,$result);
                }
            }
        }

        return $result;
    }

    //This function makes all the necessary calls to xxxx_decode_content_links()
    //function in each module, passing them the desired contents to be decoded
    //from backup format to destination site/course in order to mantain inter-activities
    //working in the backup/restore process. It's called from restore_decode_content_links()
    //function in restore process
    function mail_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;
		
		//Process every Mail in the course
        if ($mails = get_records_sql ("SELECT m.id, m.summary
                                   FROM {$CFG->prefix}mail m
                                   WHERE m.course = $restore->course_id")) {
            //Iterate over each forum->intro
            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($mails as $mail) {
                //Increment counter
                $i++;
                $content_summary = $mail->summary;
                $result_summary = restore_decode_content_links_worker($content_summary,$restore);
                if ($result_summary != $content_summary) {
                    //Update record
                    $mail->summary = addslashes($result_summary);
                    $status = update_record("mail",$mail);
                    if ($CFG->debug>7) {
                        if (!defined('RESTORE_SILENTLY')) {
                            echo '<br /><hr />'.s($content_summary).'<br />changed to<br />'.s($result_summary).'<hr /><br />';
                        }
                    }
                }
                //Do some output
                if (($i+1) % 5 == 0) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo ".";
                        if (($i+1) % 100 == 0) {
                            echo "<br />";
                        }
                    }
                    backup_flush(300);
                    }
            }
        }

        return $status;
    }

    //This function returns a log record with all the necessay transformations
    //done. It's used by restore_log_module() to restore modules log.
    function mail_restore_logs($restore,$log) {

        $status = false;

        //Depending of the action, we recode different things
        switch ($log->action) {
        case "add":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "update":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view":
            if ($log->cmid) {
                //Get the new_id of the page (to recode the url field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        default:
            if (!defined('RESTORE_SILENTLY')) {
                echo "action (".$log->module."-".$log->action.") unknown. Not restored<br>";                 //Debug
            }
            break;
        }

        if ($status) {
            $status = $log;
        }
        return $status;
    }
?>
