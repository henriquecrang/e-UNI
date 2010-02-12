<?php
/**
 *  This file is called by the YUI ajax script to populate the dropdown list
 *  of matching users to the search query.
 *  Seperate group functionality:
 *  Course students can only view those in their current group && teachers
 *  Teachers and > can view any user in any group
 *
 * @author: Michael Avelar
*/
    require_once('../../../../config.php');
    require_once('../lib.php');
   	
    $courseid	= optional_param('course', SITEID, PARAM_INT); 				// Course ID
    $query = optional_param('query','',PARAM_ALPHANUM);
     
    if (! $course = get_record("course", "id", $courseid)) {
    	print_error('invalidcourseid', 'block_email_list');
    }
    
    if ($course->id == SITEID) {
        $context = get_context_instance(CONTEXT_SYSTEM, SITEID);   // SYSTEM context
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);   // Course context
    }

    $canseefullname = has_capability('moodle/site:viewfullnames', $context);

    require_login($course->id, false); // No autologin guest

    // Get all records (up to 5) that even remotely match the search query.
    $JSON_str = '';
    if (($course->id != SITEID) && ($results = email_search_course_users($course, $query))) {
        $count = count($results);

        // Create the JSON string
        $JSON_str = '{ "ResultSet":
                        { "totalResultsAvailable":"5",
                          "totalResultsReturned":'.$count.',
                          "firstResultPosition":1,
                          "Result": [';
        if ($count > 0) {
            if (is_array($results)) {
                foreach ($results as $result) {
                    $JSON_str .= ' {"Username": "<span style=\"font-weight:bold\">'.email_fullname($result, $canseefullname).'</span>",
                                    "Userid":'.$result->id.'
                                   }';
                    $count--;
                    if ($count != 0) {
                        $JSON_str .= ',';
                    }
                }
            } else {
                $JSON_str .= '{"Username" : "'.$results->firstname.' '.$results->lastname.'",
                                "Userid" : '.$results->id.'
                              }';
            }
        }

        $JSON_str .= '  ]
                          }
                      }';
    }

    echo $JSON_str;
?>