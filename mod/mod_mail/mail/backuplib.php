<?php //$Id: backuplib.php,v 1.11 2006/01/13 03:45:29 mjollnir_ Exp $
    //This php script contains all the stuff to backup/restore
    //mail mods

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

    //This function executes all the backup procedure about this mod
    function mail_backup_mods($bf,$preferences) {

        global $CFG;

        $status = true;

        //Iterate over mail table
        $mails = get_records ("mail","course",$preferences->backup_course,"id");
        if ($mails) {
            foreach ($mails as $mail) {
                if (backup_mod_selected($preferences,'mail',$mail->id)) {
                    $status = mail_backup_one_mod($bf,$preferences,$mail);
                    // backup files happens in backup_one_mod now too.
                }
            }
        }
        return $status;  
    }

    function mail_backup_one_mod($bf,$preferences,$mail) {
        
        global $CFG;
    
        if (is_numeric($mail)) {
            $mail = get_record('mail','id',$mail);
        }
    
        $status = true;

        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print mail data
        fwrite ($bf,full_tag("ID",4,false,$mail->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"mail"));
        fwrite ($bf,full_tag("NAME",4,false,$mail->name));
        fwrite ($bf,full_tag("SUMMARY",4,false,$mail->summary));
        fwrite ($bf,full_tag("MAXBYTES",4,false,$mail->maxbytes));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$mail->timemodified));
		
		$userdata = backup_userdata_selected($preferences,'mail',$mail->id);
		
		//back up the Folders
        $status = backup_mail_folders($bf,$preferences,$mail->id,$userdata);
		
        //if we've selected to backup users info
        if ($status) {
        	if ($userdata) {
				
				if(!backup_mail_groups($bf, $preferences, $mail->id)) {
					return false;
            	}
				
				if(!backup_mail_statistics($bf, $preferences, $mail->course)) {
					return false;
            	}
				
				if(!backup_mail_files_instance($bf,$preferences,$mail->id)) {
					return false;
				}
				
			}
			
			//End mod	
			if ($status) {
            	fwrite ($bf,end_tag("MOD",3,true));
        	}
		} 
		
        return $status;
    }


	//Backup mail_folders contents (executed from mail_backup_mods)
    function backup_mail_folders($bf,$preferences,$mailid,$userdata) {

        global $CFG;

        $status = true;
        //Print mail folders
		
		if ($userdata) {
			$folders = get_records('mail_folder', 'mailid', $mailid, 'id');
		} else {
			$folders = get_records_sql ("SELECT * FROM {$CFG->prefix}mail_folder WHERE mailid = $mailid and type <> 'O'");
		}
		
        if ($folders) {
            //Write start tag
            $status =fwrite ($bf,start_tag('FOLDERS',4,true));
            foreach ($folders as $folder) {
                //Start 
                fwrite ($bf,start_tag('FOLDER',5,true));
                //Print data
                fwrite ($bf,full_tag('ID',6,false,$folder->id));
				fwrite ($bf,full_tag("USERID",6,false,$folder->userid));
                fwrite ($bf,full_tag('NAME',6,false,$folder->name));
				fwrite ($bf,full_tag('TYPE',6,false,$folder->type));
                fwrite ($bf,full_tag('TIMEMODIFIED',6,false,$folder->timemodified));
                //End 
				//Now we backup any mail_to_messages
               
                if ($userdata) {
					$status = backup_mail_messages($bf,$preferences,$folder->id);
				}
				
                $status = fwrite ($bf,end_tag('FOLDER',5,true));
            }
            //Write end tag
            $status = fwrite ($bf,end_tag('FOLDERS',4,true));
        }
        return $status;
    }


	//Backup mail_messages contents (executed from mail_backup_mods)
    function backup_mail_messages ($bf, $preferences, $folderid) {

        global $CFG;

        $status = true;

        // get the messages in a set order, the id order
        $mail_messages = get_records("mail_messages", "folderid", $folderid, "id");

        //If there is mail_messages
        if ($mail_messages) {
            //Write start tag
            $status =fwrite ($bf,start_tag("MESSAGES",6,true));
            //Iterate over each element
            foreach ($mail_messages as $message) {
                //Start 
                $status =fwrite ($bf,start_tag("MESSAGE",7,true));
                //Print contents
                fwrite ($bf,full_tag("ID",8,false,$message->id));
                fwrite ($bf,full_tag("USERID",8,false,$message->userid));
				fwrite ($bf,full_tag("FROMID",8,false,$message->fromid));
				fwrite ($bf,full_tag("SUBJECT",8,false,$message->subject));
				fwrite ($bf,full_tag("MESSAGE",8,false,$message->message));
				fwrite ($bf,full_tag("ARCHIVO",8,false,$message->archivo));
				fwrite ($bf,full_tag("LEIDO",8,false,$message->leido));
				fwrite ($bf,full_tag("RESPONDED",8,false,$message->responded));
				fwrite ($bf,full_tag("BORRADO",8,false,$message->borrado));
                fwrite ($bf,full_tag("TIMEMODIFIED",8,false,$message->timemodified));

                //Now we backup any mail_to_messages
               
                $status = backup_mail_to_messages($bf,$preferences,$message->id);
             
                //End 
                $status =fwrite ($bf,end_tag("MESSAGE",7,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("MESSAGES",6,true));
        }
        return $status;
    }

	//Backup mail_to_messages contents (executed from backup_mail_messages)
    function backup_mail_to_messages ($bf,$preferences,$messageid) {

        global $CFG;

        $status = true;

        $messages_to = get_records("mail_to_messages","messageid", $messageid);
        //If there are messages_to
        if ($messages_to) {
            //Write start tag
            $status =fwrite ($bf,start_tag("TO_MESSAGES",8,true));
            //Iterate over each element
            foreach ($messages_to as $message_to) {
                //Start
                $status =fwrite ($bf,start_tag("TO_MESSAGE",9,true));
                //Print
                fwrite ($bf,full_tag("ID",10,false,$message_to->id));       
                fwrite ($bf,full_tag("TOID",10,false,$message_to->toid));       
                fwrite ($bf,full_tag("TIMEMODIFIED",10,false,$message_to->timemodified));
                //End attempt
                $status =fwrite ($bf,end_tag("TO_MESSAGE",9,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("TO_MESSAGES",8,true));
        }
        return $status;
    }
	
	//Backup mail_groups contents (executed from mail_backup_mods)
    function backup_mail_groups ($bf, $preferences, $mailid) {

        global $CFG;

        $status = true;

        // get the groups in a set order, the id order
        $mail_groups = get_records("mail_groups", "mailid", $mailid, "id");

        //If there is mail_groups
        if ($mail_groups) {
            //Write start tag
            $status =fwrite ($bf,start_tag("GROUPS",4,true));
            //Iterate over each element
            foreach ($mail_groups as $group) {
                //Start 
                $status =fwrite ($bf,start_tag("GROUP",5,true));
                //Print contents
                fwrite ($bf,full_tag("ID",6,false,$group->id));
                fwrite ($bf,full_tag("NAME",6,false,$group->name));
                fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$group->timemodified));

                //Now we backup any mail_members_groups
               
                $status = backup_mail_members_groups($bf,$preferences,$group->id);
             
                //End 
                $status =fwrite ($bf,end_tag("GROUP",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("GROUPS",4,true));
        }
        return $status;
    }
	
	//Backup mail_members_groups contents (executed from backup_mail_groups)
    function backup_mail_members_groups ($bf,$preferences,$groupid) {

        global $CFG;

        $status = true;

        $members_groups = get_records("mail_members_groups","groupid", $groupid);
        //If there are members_groups
        if ($members_groups) {
            //Write start tag
            $status =fwrite ($bf,start_tag("MEMBERS_GROUPS",6,true));
            //Iterate over each element
            foreach ($members_groups as $member_group) {
                //Start
                $status =fwrite ($bf,start_tag("MEMBER_GROUP",7,true));
                //Print
                fwrite ($bf,full_tag("ID",8,false,$member_group->id));       
                fwrite ($bf,full_tag("USERID",8,false,$member_group->userid));       
                fwrite ($bf,full_tag("TIMEMODIFIED",8,false,$member_group->timemodified));
                //End attempt
                $status =fwrite ($bf,end_tag("MEMBER_GROUP",7,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("MEMBERS_GROUPS",6,true));
        }
        return $status;
    }
	
	
	//Backup mail_statistics contents (executed from mail_backup_mods)
    function backup_mail_statistics($bf,$preferences,$courseid) {

        global $CFG;

        $status = true;
        //Print mail statistics
		
		$statistics = get_records('mail_statistics', 'course', $courseid, 'id');
		
		
        if ($statistics) {
            //Write start tag
            $status =fwrite ($bf,start_tag('STATISTICS',4,true));
            
			foreach ($statistics as $statistic) {
                //Start 
                fwrite ($bf,start_tag('STATISTIC',5,true));
                //Print data
                fwrite ($bf,full_tag('ID',6,false,$statistic->id));
				fwrite ($bf,full_tag("USERID",6,false,$statistic->userid));
                fwrite ($bf,full_tag('RECEIVED',6,false,$statistic->received));
				fwrite ($bf,full_tag('SEND',6,false,$statistic->send));
                fwrite ($bf,full_tag('TIMEMODIFIED',6,false,$statistic->timemodified));
                //End 
                $status = fwrite ($bf,end_tag('STATISTIC',5,true));
            }
            //Write end tag
            $status = fwrite ($bf,end_tag('STATISTICS',4,true));
        }
        return $status;
    }

	
    //Backup mail files because we've selected to backup user info
    //and files are user info's level
    function backup_mail_files($bf,$preferences) {

        global $CFG;
       
        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        //Now copy the mail dir
        if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
            if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/mail")) {
                $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/mail",
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/mail");
            }
        }

        return $status;

    } 

    function backup_mail_files_instance($bf,$preferences,$instanceid) {

        global $CFG;
       
        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        $status = check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/mail/",true);
        //Now copy the mail dir
        if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
            if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/mail/".$instanceid)) {
                $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/mail/".$instanceid,
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/mail/".$instanceid);
            }
        }

        return $status;

    } 

    //Return an array of info (name,value)
    function mail_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += mail_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        //First the course data
        $info[0][0] = get_string("modulenameplural","mail");
        if ($ids = mail_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
        if ($user_data) {
		 	//Messages
            $info[1][0] = get_string("messages","mail");
            if ($ids = mail_messages_ids_by_course ($course)) {
                $info[1][1] = count($ids);
            } else {
                $info[1][1] = 0;
            }
            //Folders
            $info[2][0] = get_string("folders","mail");
            if ($ids = mail_folders_ids_by_course ($course)) {
                $info[2][1] = count($ids);
            } else {
                $info[2][1] = 0;
            }
			//Groups
            $info[3][0] = get_string("groups","mail");
            if ($ids = mail_groups_ids_by_course ($course)) {
                $info[3][1] = count($ids);
            } else {
                $info[3][1] = 0;
            }
			
        }
        return $info;
    }

    //Return an array of info (name,value)
    function mail_check_backup_mods_instances($instance,$backup_unique_code) {
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';
        if (!empty($instance->userdata)) {
            
			$info[$instance->id.'1'][0] = get_string("messages","mail");
            if ($ids = mail_messages_ids_by_instance ($instance->id)) {
                $info[$instance->id.'1'][1] = count($ids);
            } else {
                $info[$instance->id.'1'][1] = 0;
            }
			$info[$instance->id.'2'][0] = get_string("folders","mail");
            if ($ids = mail_folders_ids_by_instance ($instance->id)) {
                $info[$instance->id.'2'][1] = count($ids);
            } else {
                $info[$instance->id.'2'][1] = 0;
            }
			$info[$instance->id.'3'][0] = get_string("groups","mail");
            if ($ids = mail_groups_ids_by_instance ($instance->id)) {
                $info[$instance->id.'3'][1] = count($ids);
            } else {
                $info[$instance->id.'3'][1] = 0;
            }
			
        }
        return $info;
    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function mail_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of mails
        $buscar="/(".$base."\/mod\/mail\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@MAILINDEX*$2@$',$content);

        //Link to mail view by moduleid
        $buscar="/(".$base."\/mod\/mail\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@MAILVIEWBYID*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of mail id 
    function mail_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT m.id, m.course
                                 FROM {$CFG->prefix}mail m
                                 WHERE m.course = '$course'");
    }
	
	//Returns an array of mail_messages id
    function mail_messages_ids_by_course ($course) {

        global $CFG;

        return get_records_sql ("SELECT e.id , e.mailid
                                 FROM {$CFG->prefix}mail_messages e,
                                      {$CFG->prefix}mail m
                                 WHERE m.course = '$course' AND
                                       e.mailid = m.id");
    }
	
	//Returns an array of mail_messages id
    function mail_messages_ids_by_instance ($instanceid) {

        global $CFG;

        return get_records_sql ("SELECT e.id , e.mailid
                                 FROM {$CFG->prefix}mail_messages e
                                 WHERE e.mailid = $instanceid");
    }
	
	 //Returns an array of mail_folders id
    function mail_folders_ids_by_course ($course) {

        global $CFG;

        return get_records_sql ("SELECT f.id , f.mailid
                                 FROM {$CFG->prefix}mail_folder f,
                                      {$CFG->prefix}mail m
                                 WHERE m.course = '$course' AND
                                       f.mailid = m.id");
    }
	
	//Returns an array of mail_folders id
    function mail_folders_ids_by_instance ($instanceid) {

        global $CFG;

        return get_records_sql ("SELECT f.id , f.mailid
                                 FROM {$CFG->prefix}mail_folder f
                                 WHERE f.mailid = $instanceid");
    }
	
	
	//Returns an array of mail_groups id
    function mail_groups_ids_by_course ($course) {

        global $CFG;

        return get_records_sql ("SELECT g.id , g.mailid
                                 FROM {$CFG->prefix}mail_groups g,
                                      {$CFG->prefix}mail m
                                 WHERE m.course = '$course' AND
                                       g.mailid = m.id");
    }
	
	//Returns an array of mail_groups id
    function mail_groups_ids_by_instance ($instanceid) {

        global $CFG;

        return get_records_sql ("SELECT g.id , g.mailid
                                 FROM {$CFG->prefix}mail_groups g
                                 WHERE g.mailid = $instanceid");
    }
	

?>
