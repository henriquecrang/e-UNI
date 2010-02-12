<?php

/**
 * This page return the result of the request of users, if no query returns all users.
 *
 * @author
 * @version 1.1
 * @package email
 */


	require_once( "../../../config.php" );
	require_once($CFG->dirroot.'/blocks/email_list/email/tablelib.php');
	require_once($CFG->dirroot.'/blocks/email_list/email/lib.php');
	require_once($CFG->libdir.'/searchlib.php');
	require_once($CFG->libdir.'/grouplib.php');


	$courseid		= optional_param('id', SITEID, PARAM_INT);    	// Course ID
	$currentgroup  	= optional_param('group', 0, PARAM_INT);		// Selected Group
	$roleid			= optional_param('roleid', 0, PARAM_INT);		// Role ID
	$page			= optional_param('page', 0, PARAM_INT);			// Page
	$perpage		= optional_param('perpage', 7, PARAM_INT);		// Max rows per page
	$search			= optional_param('search', '', PARAM_RAW);		// Searching users

	$firstinitial 	= optional_param('fname', '', PARAM_ALPHA);		// Order by fistname
 	$lastinitial 	= optional_param('lname', '', PARAM_ALPHA);	// Order by lastname


	// Get course, if exist
	if (! $course = get_record('course', 'id', $courseid)) {
		print_error('invalidcourseid', 'block_email_list');
	}

	if ($course->id == SITEID) {
        $context = get_context_instance(CONTEXT_SYSTEM, SITEID);   // SYSTEM context
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);   // Course context
    }

   	// Only return, if user have login
	require_login($course);

    // Need capability viewaparticipants.
    require_capability('moodle/course:viewparticipants', $context);


	$tablecolumns = array( 'user', 'to', 'cc', 'bcc', 'dropall' );

    $urlto = '<a href="#" onclick="action_all_users(\'to\');" > '.get_string('toall','block_email_list').' <img src="'.$CFG->wwwroot.'/blocks/email_list/email/images/user_green.png" height="16" width="16" alt="'.get_string("course").'" /> </a>';
    $urlcc = '<a href="#" onclick="action_all_users(\'cc\');" >'.get_string('ccall','block_email_list').' <img src="'.$CFG->wwwroot.'/blocks/email_list/email/images/user_gray.png" height="16" width="16" alt="'.get_string("course").'" /> </a>';
    $urlbcc = '<a href="#" onclick="action_all_users(\'bcc\');" >'.get_string('bccall','block_email_list').' <img src="'.$CFG->wwwroot.'/blocks/email_list/email/images/user_suit.png" height="16" width="16" alt="'.get_string("course").'" /> </a>';
    $urlremove = '<a href="#" onclick="action_all_users(\'remove\');" >'.get_string('removeall','block_email_list').' <img src="'.$CFG->wwwroot.'/blocks/email_list/email/images/user_red.png" height="16" width="16" alt="'.get_string("course").'" /> </a>';

    $tableheaders = array( get_string('user'), $urlto, $urlcc, $urlbcc, $urlremove );
	$baseurl = 'participants.php?id='.$courseid.'&amp;roleid='.$roleid.'&amp;group='.$currentgroup.'&amp;perpage='.$perpage.'&amp;search='.$search.'&amp;fname='.$firstinitial.'&amp;lname='.$lastinitial;

	$table = new email_flexible_table('participants');

	$table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl);

    $table->set_attribute('align', 'center');

    $table->setup();

    $sitecontext = get_context_instance(CONTEXT_SYSTEM);
    $frontpagectx = get_context_instance(CONTEXT_COURSE, SITEID);

    /// front page course is different
    $rolenames = array();
	$avoidroles = array();

    if ($roles = get_roles_used_in_context($context, true)) {
        $canviewroles    = get_roles_with_capability('moodle/course:view', CAP_ALLOW, $context);
        $doanythingroles = get_roles_with_capability('moodle/site:doanything', CAP_ALLOW, $sitecontext);

        if ($context->id == $frontpagectx->id) {
            //we want admins listed on frontpage too
            foreach ($doanythingroles as $dar) {
                $canviewroles[$dar->id] = $dar;
            }
            $doanythingroles = array();
        }

        if ( ! $CFG->email_add_admins ) {
        	$adminsroles = get_roles_with_capability('moodle/legacy:admin', CAP_ALLOW, $sitecontext);
        }

        foreach ($roles as $role) {
            if (!isset($canviewroles[$role->id])) {   // Avoid this role (eg course creator)
                $avoidroles[] = $role->id;
                unset($roles[$role->id]);
                continue;
            }
            if (isset($doanythingroles[$role->id])) {   // Avoid this role (ie admin)
                $avoidroles[] = $role->id;
                unset($roles[$role->id]);
                continue;
            }

            if ( ! $CFG->email_add_admins ) {
            	if (isset($adminsroles[$role->id])) {   // Avoid this role (ie admin)
	                $avoidroles[] = $role->id;
	                unset($roles[$role->id]);
	                continue;
	            }
            }
            $rolenames[$role->id] = strip_tags(role_get_name($role, $context));   // Used in menus etc later on
        }
    }

    if ($context->id == $frontpagectx->id and $CFG->defaultfrontpageroleid) {
        // default frontpage role is assigned to all site users
        unset($rolenames[$CFG->defaultfrontpageroleid]);
    }

    // no roles to display yet?
    // frontpage course is an exception, on the front page course we should display all users
    if (empty($rolenames) && $context->id != $frontpagectx->id) {
        error ('No participants found for this course');
    }

/// Check to see if groups are being used in this course
/// and if so, set $currentgroup to reflect the current group

    $groupmode    = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = groups_get_course_group($course, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup  = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and $course->groupmodeforce and
                         !has_capability('moodle/site:accessallgroups', $context));



    // we are looking for all users with this role assigned in this context or higher
    if ($usercontexts = get_parent_contexts($context)) {
        $listofcontexts = '('.implode(',', $usercontexts).')';
    } else {
        $listofcontexts = '('.$sitecontext->id.')'; // must be site
    }
    if ($roleid > 0) {
        $selectrole = " AND r.roleid = $roleid ";
    } else {
        $selectrole = " ";
    }

    if ($context->id != $frontpagectx->id) {
        $select = 'SELECT DISTINCT u.id, u.username, u.firstname, u.lastname,
                      r.hidden,
                      ctx.id AS ctxid, ctx.path AS ctxpath,
                      ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel ';
        $select .= $course->enrolperiod?', r.timeend ':'';
    } else {
        if ($roleid >= 0) {
            $select = 'SELECT u.id, u.username, u.firstname, u.lastname, r.hidden,
                          ctx.id AS ctxid, ctx.path AS ctxpath,
                          ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel ';
        } else {
            $select = 'SELECT u.id, u.username, u.firstname, u.lastname,
                          ctx.id AS ctxid, ctx.path AS ctxpath,
                          ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel ';
        }
    }

    if ($context->id != $frontpagectx->id or $roleid >= 0) {
        $from   = "FROM {$CFG->prefix}user u
                LEFT OUTER JOIN {$CFG->prefix}context ctx
                    ON (u.id=ctx.instanceid AND ctx.contextlevel = ".CONTEXT_USER.")
                JOIN {$CFG->prefix}role_assignments r
                    ON u.id=r.userid
                LEFT OUTER JOIN {$CFG->prefix}user_lastaccess ul
                    ON (r.userid=ul.userid and ul.courseid = $course->id) ";
    } else {
        // on frontpage and we want all registered users
        $from = "FROM {$CFG->prefix}user u
                LEFT OUTER JOIN {$CFG->prefix}context ctx
                    ON (u.id=ctx.instanceid AND ctx.contextlevel = ".CONTEXT_USER.") ";
    }

	$hiddensql = has_capability('moodle/role:viewhiddenassigns', $context)? '':' AND r.hidden = 0 ';

    // exclude users with roles we are avoiding
    if ($avoidroles) {
        $adminroles = 'AND r.roleid NOT IN (';
        $adminroles .= implode(',', $avoidroles);
        $adminroles .= ')';
    } else {
        $adminroles = '';
    }

    // join on 2 conditions
    // otherwise we run into the problem of having records in ul table, but not relevant course
    // and user record is not pulled out

    if ($context->id != $frontpagectx->id) {
        $where  = "WHERE (r.contextid = $context->id OR r.contextid in $listofcontexts)
            AND u.deleted = 0 $selectrole
            AND (ul.courseid = $course->id OR ul.courseid IS NULL)
            AND u.username != 'guest'
            $adminroles
            $hiddensql ";
    } else {
        if ($roleid >= 0) {
            $where = "WHERE (r.contextid = $context->id OR r.contextid in $listofcontexts)
                AND u.deleted = 0 $selectrole
                AND u.username != 'guest'";
        } else {
            $where = "WHERE u.deleted = 0
                AND u.username != 'guest'";
        }
    }

    // Searching users
    $sqlsearch = '';
    $like = sql_ilike();

    // General search
    if (! empty($search) ) {
		$sqlsearch = ' AND (u.firstname '.$like.' \'%'.$search.'%\' OR u.lastname '.$like.' \'%'.$search.'%\')';
    }

    // Initial firtname search
	if ($firstinitial) {
		$sqlsearch .= ' AND firstname '. $like .' \''. $firstinitial .'%\'';
	}

	// Initial lastname search
	if ($lastinitial) {
		$sqlsearch .= ' AND lastname '. $like .' \''. $lastinitial .'%\'';
    }

    if ($currentgroup) {    // Displaying a group by choice
        // FIX: TODO: This will not work if $currentgroup == 0, i.e. "those not in a group"
        $from  .= 'LEFT JOIN '.$CFG->prefix.'groups_members gm ON u.id = gm.userid ';
        $where .= ' AND gm.groupid = '.$currentgroup;
    }

	$totalcount = count_records_sql('SELECT COUNT(distinct u.id) '.$from.$where.$sqlsearch);   // Each user could have > 1

    // Define long page.
	$table->pagesize($perpage, $totalcount);

    if ( $courseid ) {
    	$userlist = get_records_sql($select.$from.$where.$sqlsearch.$sort, $table->get_page_start(), $table->get_page_size());

	    if ( $userlist ) {
			foreach ($userlist as $user) {
	           	$pic = print_user_picture($user, $courseid, false, 30, true,false);

    			$query = $_SERVER["QUERY_STRING"];

    			$link = 'compose.php?' . $query;
    			$link = '#';

    			$canseefullname = has_capability('moodle/site:viewfullnames', $context);

		        $table->add_data( array (
			                                    '<a href="#" onClick="if( manageContact(\''.email_fullname($user, $canseefullname).'\', \''.$user->id.'\', \'add\', \'to\')){toggleRemoveAction(\''.$user->id.'\');}">'.$pic.'<span>'.email_fullname($user, $canseefullname).'</span></a>',
			                                    '<div id="addto'.$user->id.'" align="center"><input id="'.email_fullname($user, $canseefullname).'" name="useridto" type="hidden" value="'.$user->id.'"><input id="addto" type="button" value="'.get_string('for','block_email_list').'" onClick="if( manageContact(\''.addslashes(email_fullname($user, $canseefullname)).'\', \''.$user->id.'\', \'add\', \'to\')){toggleRemoveAction(\''.$user->id.'\');}"></div>',
			                                    '<div id="addcc'.$user->id.'" align="center"><input id="'.email_fullname($user, $canseefullname).'" name="useridcc" type="hidden" value="'.$user->id.'"><input id="addcc" type="button" value="'.get_string('cc','block_email_list').'" onClick="if( manageContact(\''.addslashes(email_fullname($user, $canseefullname)).'\', \''.$user->id.'\', \'add\', \'cc\')){toggleRemoveAction(\''.$user->id.'\');}"></div>',
			                                    '<div id="addbcc'.$user->id.'" align="center"><input id="'.email_fullname($user, $canseefullname).'" name="useridbcc" type="hidden" value="'.$user->id.'"><input id="addbcc" type="button" value="'.get_string('bcc','block_email_list').'" onClick="if( manageContact(\''.addslashes(email_fullname($user, $canseefullname)).'\', \''.$user->id.'\', \'add\', \'bcc\')){toggleRemoveAction(\''.$user->id.'\');}"></div>',
			                                    '<div id="removeuser'.$user->id.'" style="visibility:hidden;align:center"><input id="'.email_fullname($user, $canseefullname).'" name="useridremove" type="hidden" value="'.$user->id.'"><a href="#" onClick="if( manageContact(\''.addslashes(email_fullname($user, $canseefullname)).'\', \''.$user->id.'\', \'remove\', \'\')){toggleRemoveAction(\''.$user->id.'\');}"><img src="'.$CFG->pixpath.'/t/emailno.gif" alt="'.get_string('remove','block_email_list').'" title="'.get_string('remove','block_email_list').'"></a></div>' )
			                        );
	        }
	    }
    }

//------------------------- INTERFACE

// Print html
echo '<html>
	<body>';

echo '<script type="text/javascript">';
echo 'parent.changeme("participants","'. $table->get_html(true).'");';
echo 'parent.checkAllRemoveActions();';

echo '</script>';

echo '</body>
	</html>';
?>