<?php  // $Id: view.php,v 1.94.2.4 2007/05/15 18:27:01 skodak Exp $

    require_once('../../config.php');
    require_once('lib.php');
    require_once("$CFG->libdir/rsslib.php");


    $id          = optional_param('id', 0, PARAM_INT);       // Course Module ID
    $f           = optional_param('f', 0, PARAM_INT);        // Forum ID
    $mode        = optional_param('mode', 0, PARAM_INT);     // Display mode (for single forum)
    $showall     = optional_param('showall', '', PARAM_INT); // show all discussions on one page
    $changegroup = optional_param('group', -1, PARAM_INT);   // choose the current group
    $page        = optional_param('page', 0, PARAM_INT);     // which page to show
    $search      = optional_param('search', '');             // search string



    if ($id) {

        if (! $cm = get_coursemodule_from_id('forum', $id)) {
            error("Course Module ID was incorrect");
        }
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
        if (! $forum = get_record("forum", "id", $cm->instance)) {
            error("Forum ID was incorrect");
        }
        $strforums = get_string("modulenameplural", "forum");
        $strforum = get_string("modulename", "forum");
        $buttontext = update_module_button($cm->id, $course->id, $strforum);

    } else if ($f) {

        if (! $forum = get_record("forum", "id", $f)) {
            error("Forum ID was incorrect or no longer exists");
        }
        if (! $course = get_record("course", "id", $forum->course)) {
            error("Forum is misconfigured - don't know what course it's from");
        }

        $strforums = get_string("modulenameplural", "forum");
        $strforum = get_string("modulename", "forum");

        if ($cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
            $buttontext = update_module_button($cm->id, $course->id, $strforum);
        } else {
            $cm->id = 0;
            $cm->visible = 1;
            $cm->course = $course->id;
            $buttontext = "";
        }

    } else {
        error('Must specify a course module or a forum ID');
    }

    if (!$buttontext) {
        $buttontext = forum_search_form($course, $search);
    }


    require_course_login($course, true, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);


/// Print header.
    $navigation = "<a href=\"index.php?id=$course->id\">$strforums</a> ->";
    print_header_simple(format_string($forum->name), "",
                 "$navigation ".format_string($forum->name), "", "<style type='text/css'>
<!--
#left-column ul.list li a.forum {
background-color: #fff;
margin-top: -1px;
_margin-top: -7px;
color: #000;
width: 100%;
border-left: 1px solid #ccc !important;
border-bottom: 1px solid #ccc !important;
border-top: 1px solid #ccc !important;
border-right: none !important;
}
-->
</style>", true, $buttontext, navmenu($course, $cm));
				 
				 
				 	//////////////////////////

    require_once('../../course/lib.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->libdir.'/ajax/ajaxlib.php');
    require_once($CFG->dirroot.'/mod/forum/lib.php');

    $id          = optional_param('id', 0, PARAM_INT);
    $name        = optional_param('name', '', PARAM_RAW);
    $edit        = optional_param('edit', -1, PARAM_BOOL);
    $hide        = optional_param('hide', 0, PARAM_INT);
    $show        = optional_param('show', 0, PARAM_INT);
    $idnumber    = optional_param('idnumber', '', PARAM_RAW);
    $section     = optional_param('section', 0, PARAM_INT);
    $move        = optional_param('move', 0, PARAM_INT);
    $marker      = optional_param('marker',-1 , PARAM_INT);
    $switchrole  = optional_param('switchrole',-1, PARAM_INT);





    if (!$context = get_context_instance(CONTEXT_COURSE, $course->id)) {
        print_error('nocontext');
    }

    if ($switchrole == 0) {  // Remove any switched roles before checking login
        role_switch($switchrole, $context);
    }

    require_login($course->id);

    if ($switchrole > 0) {
        role_switch($switchrole, $context);
        require_login($course->id);   // Double check that this role is allowed here
    }

    //If course is hosted on an external server, redirect to corresponding
    //url with appropriate authentication attached as parameter 
    if (file_exists($CFG->dirroot .'/course/externservercourse.php')) {
        include $CFG->dirroot .'/course/externservercourse.php';
        if (function_exists('extern_server_course')) {
            if ($extern_url = extern_server_course($course)) {
                redirect($extern_url);
            }
        }
    }


    require_once($CFG->dirroot.'/calendar/lib.php');    /// This is after login because it needs $USER

    add_to_log($course->id, 'course', 'view', "view.php?id=$course->id", "$course->id");

    $course->format = clean_param($course->format, PARAM_ALPHA);
    if (!file_exists($CFG->dirroot.'/course/format/'.$course->format.'/format.php')) {
        $course->format = 'weeks';  // Default format is weeks
    }

    $PAGE = page_create_object(PAGE_COURSE_VIEW, $course->id);
    $pageblocks = blocks_setup($PAGE, BLOCKS_PINNED_BOTH);


    if (!isset($USER->editing)) {
        $USER->editing = 0;
    }
    if ($PAGE->user_allowed_editing()) {
        if (($edit == 1) and confirm_sesskey()) {
            $USER->editing = 1;
        } else if (($edit == 0) and confirm_sesskey()) {
            $USER->editing = 0;
            if(!empty($USER->activitycopy) && $USER->activitycopycourse == $course->id) {
                $USER->activitycopy       = false;
                $USER->activitycopycourse = NULL;
            }
        }

        if ($hide && confirm_sesskey()) {
            set_section_visible($course->id, $hide, '0');
        }

        if ($show && confirm_sesskey()) {
            set_section_visible($course->id, $show, '1');
        }

        if (!empty($section)) {
            if (!empty($move) and confirm_sesskey()) {
                if (!move_section($course, $section, $move)) {
                    notify('An error occurred while moving a section');
                }
            }
        }
    } else {
        $USER->editing = 0;
    }

    $SESSION->fromdiscussion = $CFG->wwwroot .'/course/view.php?id='. $course->id;


    if ($course->id == SITEID) {
        // This course is not a real course.
        redirect($CFG->wwwroot .'/');
    }
	
	    get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);

    if (! $sections = get_all_sections($course->id)) {   // No sections found
        // Double-check to be extra sure
        if (! $section = get_record('course_sections', 'course', $course->id, 'section', 0)) {
            $section->course = $course->id;   // Create a default section.
            $section->section = 0;
            $section->visible = 1;
            $section->id = insert_record('course_sections', $section);
        }
        if (! $sections = get_all_sections($course->id) ) {      // Try again
            error('Error finding or creating section structures for this course');
        }
    }


    if (empty($course->modinfo)) {
        // Course cache was never made.
        rebuild_course_cache($course->id);
        if (! $course = get_record('course', 'id', $course->id) ) {
            error("That's an invalid course id");
        }
    }
	
	
	
	
	
	///////////////////////////
	
	
		/////////////////////

    $week = optional_param('week', -1, PARAM_INT);

    // Bounds for block widths
    // more flexible for theme designers taken from theme config.php
    $lmin = (empty($THEME->block_l_min_width)) ? 100 : $THEME->block_l_min_width;
    $lmax = (empty($THEME->block_l_max_width)) ? 210 : $THEME->block_l_max_width;
    $rmin = (empty($THEME->block_r_min_width)) ? 100 : $THEME->block_r_min_width;
    $rmax = (empty($THEME->block_r_max_width)) ? 210 : $THEME->block_r_max_width;

    define('BLOCK_L_MIN_WIDTH', $lmin);
    define('BLOCK_L_MAX_WIDTH', $lmax);
    define('BLOCK_R_MIN_WIDTH', $rmin);
    define('BLOCK_R_MAX_WIDTH', $rmax);
  
    $preferred_width_left  = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]),  
                                            BLOCK_L_MAX_WIDTH);
    $preferred_width_right = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), 
                                            BLOCK_R_MAX_WIDTH);

    if ($week != -1) {
        $displaysection = course_set_display($course->id, $week);
    } else {
        if (isset($USER->display[$course->id])) {
            $displaysection = $USER->display[$course->id];
        } else {
            $displaysection = course_set_display($course->id, 0);
        }
    }

    $streditsummary  = get_string('editsummary');
    $stradd          = get_string('add');
    $stractivities   = get_string('activities');
    $strshowallweeks = get_string('showallweeks');
    $strweek         = get_string('week');
    $strgroups       = get_string('groups');
    $strgroupmy      = get_string('groupmy');
    $editing         = $PAGE->user_is_editing();

    if ($editing) {
        $strstudents = moodle_strtolower($course->students);
        $strweekhide = get_string('weekhide', '', $strstudents);
        $strweekshow = get_string('weekshow', '', $strstudents);
        $strmoveup   = get_string('moveup');
        $strmovedown = get_string('movedown');
    }

    $context = get_context_instance(CONTEXT_COURSE, $course->id);
/// Layout the whole page as three big columns.
    echo '<table id="layout-table" height="100%" cellspacing="0" summary="'.get_string('layouttable').'"><tr>';
    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':
 
/// The left column ...

    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
        echo '<td style="width:'.$preferred_width_left.'px" id="left-column">';
		echo '<span class="servicos">Servi&ccedil;os:</span>';
		echo '<span id="sb-2" class="skip-block-to"></span><div  id="inst10" class="block_admin sideblock"><div class="header"><a href="#sb-3" class="skip-block" title="Saltar Administração">
<span class="accesshide">Saltar Administração</span>
</a><div class="title"><div class="hide-show"></div><h2>Administração</h2></div></div><div class="content"><ul class="list">
<li class="r0"><div class="icon column c0"><img src="http://localhost/moodle/theme/e-uni/pix/i/grades.gif" class="icon" alt="" /></div><div class="column c1"><a title="Agenda" href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">Agenda</a></div></li></ul></div></div>';

        if (!empty($THEME->roundcorners)) {
            echo '<div class="bt"><div></div></div>';
            echo '<div class="i1"><div class="i2"><div class="i3">';
        }
        
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);

        if (!empty($THEME->roundcorners)) {
            echo '</div></div></div>';
            echo '<div class="bb"><div></div></div>';
        }

		echo '<span id="sb-2" class="skip-block-to"></span><div  id="inst10" class="block_admin sideblock"><div class="header"><a href="#sb-3" class="skip-block" title="Saltar Administração">
<span class="accesshide">Saltar Administração</span>
</a><div class="title"><div class="hide-show"></div><h2>Administração</h2></div></div><div class="content"><ul class="list">
<li class="r0"><div class="icon column c0"><img src="http://localhost/moodle/theme/e-uni/pix/i/grades.gif" class="icon" alt="" /></div><div class="column c1"><a title="Informa&ccedil;&otilde;es" href="'.$CFG->wwwroot.'/course/inf.php?id='.$course->id.'">Informa&ccedil;&otilde;es</a></div></li></ul></div></div>';

        echo '</td>';
    }
            break;
            case 'middle':
/// Start main column
    echo '<td id="middle-column">';

/// Some capability checks.
    if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"));
    }
    
    if (!has_capability('mod/forum:viewdiscussion', $context)) {
        notice(get_string('noviewdiscussionspermission', 'forum'));
    }
    
/// find out current groups mode
    $groupmode = groupmode($course, $cm);
    $currentgroup = setup_and_print_groups($course, $groupmode, 'view.php?id=' . $cm->id);

/// Okay, we can show the discussions. Log the forum view.
    if ($cm->id) {
        add_to_log($course->id, "forum", "view forum", "view.php?id=$cm->id", "$forum->id", $cm->id);
    } else {
        add_to_log($course->id, "forum", "view forum", "view.php?f=$forum->id", "$forum->id");
    }



/// Print settings and things across the top

    // If it's a simple single discussion forum, we need to print the display
    // mode control.
    if ($forum->type == 'single') {
        if (! $discussion = get_record("forum_discussions", "forum", $forum->id)) {
            if ($discussions = get_records("forum_discussions", "forum", $forum->id, "timemodified ASC")) {
                $discussion = array_pop($discussions);
            }
        }
        if ($discussion) {
            if ($mode) {
                set_user_preference("forum_displaymode", $mode);
            }
            $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);
            forum_print_mode_form($forum->id, $displaymode, $forum->type);
        }
    }


    print_box_start('forumcontrol');

    print_box_start('subscription');

    if (!empty($USER->id) && !has_capability('moodle/legacy:guest', $context, NULL, false)) {
        $SESSION->fromdiscussion = "$FULLME";
        if (forum_is_forcesubscribed($forum->id)) {
            $streveryoneisnowsubscribed = get_string('everyoneisnowsubscribed', 'forum');
            $strallowchoice = get_string('allowchoice', 'forum');
            echo '<span class="helplink">' . get_string("forcessubscribe", 'forum') . '</span><br />';
            helpbutton("subscription", $strallowchoice, "forum");
            echo '&nbsp;<span class="helplink">';
            if (has_capability('moodle/course:manageactivities', $context)) {
                echo "<a title=\"$strallowchoice\" href=\"subscribe.php?id=$forum->id&amp;force=no\">$strallowchoice</a>";
            } else {
                echo $streveryoneisnowsubscribed;
            }
            echo '</span>';

        } else if ($forum->forcesubscribe == FORUM_DISALLOWSUBSCRIBE) {
            $strsubscriptionsoff = get_string('disallowsubscribe','forum');
            echo $strsubscriptionsoff;
            helpbutton("subscription", $strsubscriptionsoff, "forum");
        } else {
            $streveryonecannowchoose = get_string("everyonecannowchoose", "forum");
            $strforcesubscribe = get_string("forcesubscribe", "forum");
            $strshowsubscribers = get_string("showsubscribers", "forum");
            echo '<span class="helplink">' . get_string("allowsallsubscribe", 'forum') . '</span><br />';
            helpbutton("subscription", $strforcesubscribe, "forum");
            echo '&nbsp;';
            if (has_capability('moodle/course:manageactivities', $context)) {
                echo "<span class=\"helplink\"><a title=\"$strforcesubscribe\" href=\"subscribe.php?id=$forum->id&amp;force=yes\">$strforcesubscribe</a></span>";
                echo "<br />";
                echo "<span class=\"helplink\"><a href=\"subscribers.php?id=$forum->id\">$strshowsubscribers</a></span>";
            } else {
                echo '<span class="helplink">'.$streveryonecannowchoose.'</span>';
            }

            if (forum_is_subscribed($USER->id, $forum->id)) {
                $subtexttitle = get_string("subscribestop", "forum");
                $subtext = get_string("unsubscribe", "forum");
            } else {
                $subtexttitle = get_string("subscribestart", "forum");
                $subtext = get_string("subscribe", "forum");
            }
            echo "<br />";
            echo "<span class=\"helplink\"><a title=\"$subtexttitle\" href=\"subscribe.php?id=$forum->id\">$subtext</a></span>";
        }

        if (forum_tp_can_track_forums($forum) && ($forum->trackingtype == FORUM_TRACKING_OPTIONAL)) {
            if (forum_tp_is_tracked($forum, $USER->id)) {
                $trtitle = get_string('notrackforum', 'forum');
                $trackedlink = '<a title="'.get_string('notrackforum', 'forum').'" href="settracking.php?id='.
                               $forum->id.'&amp;returnpage=view.php">'.get_string('forumtracked', 'forum').'</a>';
            } else {
                $trtitle = get_string('trackforum', 'forum');
                $trackedlink = '<a title="'.get_string('trackforum', 'forum').'" href="settracking.php?id='.
                               $forum->id.'&amp;returnpage=view.php">'.get_string('forumtrackednot', 'forum').'</a>';
            }
            echo '<br />';
            echo "<span class=\"helplink\">$trackedlink</span>";
        }

    }

    /// If rss are activated at site and forum level and this forum has rss defined, show link
    if (isset($CFG->enablerssfeeds) && isset($CFG->forum_enablerssfeeds) &&
        $CFG->enablerssfeeds && $CFG->forum_enablerssfeeds && $forum->rsstype and $forum->rssarticles) {

        if ($forum->rsstype == 1) {
            $tooltiptext = get_string("rsssubscriberssdiscussions","forum",format_string($forum->name));
        } else {
            $tooltiptext = get_string("rsssubscriberssposts","forum",format_string($forum->name));
        }
        if (empty($USER->id)) {
            $userid = 0;
        } else {
            $userid = $USER->id;
        }
        print_box_start('rsslink');
        rss_print_link($course->id, $userid, "forum", $forum->id, $tooltiptext);
        print_box_end(); // subscription

    }
    print_box_end(); // subscription

    print_box_end();  // forumcontrol

    print_box('&nbsp;', 'clearer'); 
	
	echo '<span class=recupsenha>F&oacute;rum - '.$forum->name.'</span><span class=subtitulo>Cada t&oacute;pico &eacute; uma liga&ccedil;ão para a discuss&atilde;o com a lista dos coment&aacute;rios relacionados. &eacute; apresentado o autor do t&oacute;pico e o n&uacute;mero de coment&aacute;rios.</span>';


    if (!empty($forum->blockafter) && !empty($forum->blockperiod)) {
        $a->blockafter = $forum->blockafter;
        $a->blockperiod = get_string('secondstotime'.$forum->blockperiod);
        notify(get_string('thisforumisthrottled','forum',$a));
    }

    if ($forum->type == 'qanda' && !has_capability('moodle/course:manageactivities', $context)) {
        notify(get_string('qandanotify','forum'));
    }

    $forum->intro = trim($forum->intro);

    switch ($forum->type) {
        case 'single':
            if (! $discussion = get_record("forum_discussions", "forum", $forum->id)) {
                if ($discussions = get_records("forum_discussions", "forum", $forum->id, "timemodified ASC")) {
                    notify("Warning! There is more than one discussion in this forum - using the most recent");
                    $discussion = array_pop($discussions);
                } else {
                    error("Could not find the discussion in this forum");
                }
            }
            if (! $post = forum_get_post_full($discussion->firstpost)) {
                error("Could not find the first post in this forum");
            }
            if ($mode) {
                set_user_preference("forum_displaymode", $mode);
            }
            $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);
            $canrate = has_capability('mod/forum:rate', $context);
            forum_print_discussion($course, $forum, $discussion, $post, $displaymode, NULL, $canrate);
            break;

        case 'eachuser':
            if (!empty($forum->intro)) {
                print_box(format_text($forum->intro), 'generalbox', 'intro');
            }
            echo '<p align="center">';
            if (forum_user_can_post_discussion($forum)) {
                print_string("allowsdiscussions", "forum");
            } else {
                echo '&nbsp;';
            }
            echo '</p>';
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', $currentgroup, $groupmode);
            } else {
                forum_print_latest_discussions($course, $forum, $CFG->forum_manydiscussions, 'header', '', $currentgroup, $groupmode, $page);
            }
            break;

        case 'teacher':
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', $currentgroup, $groupmode);
            } else {
                forum_print_latest_discussions($course, $forum, $CFG->forum_manydiscussions, 'header', '', $currentgroup, $groupmode, $page);
            }
            break;

        default:
            if (!empty($forum->intro)) {
                print_box(format_text($forum->intro), 'generalbox', 'intro');
            }
            echo '<br />';
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', $currentgroup, $groupmode);
            } else {
                forum_print_latest_discussions($course, $forum, $CFG->forum_manydiscussions, 'header', '', $currentgroup, $groupmode, $page);
            }
            
            
            break;
    }
	
	

		   
    echo '</td>';

            break;
            case 'right':
    // The right column
    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $editing) {
        echo '<td style="width: '.$preferred_width_right.'px;" id="right-column">';

        if (!empty($THEME->roundcorners)) {
            echo '<div class="bt"><div></div></div>';
            echo '<div class="i1"><div class="i2"><div class="i3">';
        }

        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);

        if (!empty($THEME->roundcorners)) {
            echo '</div></div></div>';
            echo '<div class="bb"><div></div></div>';
        }

        echo '</td>';
    }


            break;
        }
    }
    echo '</tr></table>';

/////////////////////



    print_footer($course);

?>
