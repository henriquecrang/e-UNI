<?php  // $Id: view.php,v 1.106.2.19 2009/11/02 06:29:24 moodler Exp $

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


    $buttontext = '';

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
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
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

        if (!$cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
            error("Course Module missing");
        }

        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);

        $strforums = get_string("modulenameplural", "forum");
        $strforum = get_string("modulename", "forum");
        $buttontext = update_module_button($cm->id, $course->id, $strforum);

    } else {
        error('Must specify a course module or a forum ID');
    }

    if (!$buttontext) {
        $buttontext = forum_search_form($course, $search);
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Print header.

    /// Add ajax-related libs
    require_js(array('yui_yahoo', 'yui_event', 'yui_dom', 'yui_connection', 'yui_json'));
    require_js($CFG->wwwroot . '/mod/forum/rate_ajax.js');

    $navigation = build_navigation('', $cm);
    print_header_simple(format_string($forum->name), "",
                 $navigation, "", "", true, $buttontext, navmenu($course, $cm));


/////////////////// GAMBIARRATION //////////////////

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
                redirect($extern_url);echo '</td>';
            }
        }
    }

    require_once($CFG->dirroot.'/calendar/lib.php');    /// This is after login because it needs $USER

    //add_to_log($course->id, 'course', 'view', "view.php?id=$course->id", "$course->id");

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

    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
        echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";
	echo "<tr><td width=\"180\" valign=\"top\">";
       
        
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
	echo "</td><td valign=\"top\">";       
       
    }	

////////////////////// END OF GAMBIARRATION /////////////









/// Some capability checks.
    if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"));
    }

    if (!has_capability('mod/forum:viewdiscussion', $context)) {
        notice(get_string('noviewdiscussionspermission', 'forum'));
    }

/// find out current groups mode
    groups_print_activity_menu($cm, 'view.php?id=' . $cm->id);
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

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


//    print_box_start('forumcontrol clearfix');

//    print_box_start('subscription clearfix');
    echo '<div class="subscription">';

    if (!empty($USER->id) && !has_capability('moodle/legacy:guest', $context, NULL, false)) {
        $SESSION->fromdiscussion = "$FULLME";
        if (forum_is_forcesubscribed($forum)) {
            $streveryoneisnowsubscribed = get_string('everyoneisnowsubscribed', 'forum');
            $strallowchoice = get_string('allowchoice', 'forum');
            echo '<span class="helplink">' . get_string("forcessubscribe", 'forum') . '</span><br />';
            helpbutton("subscription", $strallowchoice, "forum");
            echo '&nbsp;<span class="helplink">';
            if (has_capability('mod/forum:managesubscriptions', $context)) {
                echo "<a title=\"$strallowchoice\" href=\"subscribe.php?id=$forum->id&amp;force=no\">$strallowchoice</a>";
            } else {
                echo $streveryoneisnowsubscribed;
            }
            echo '</span><br />';

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

            if (has_capability('mod/forum:managesubscriptions', $context)) {
                echo "<span class=\"helplink\"><a title=\"$strforcesubscribe\" href=\"subscribe.php?id=$forum->id&amp;force=yes\">$strforcesubscribe</a></span>";
            } else {
                echo '<span class="helplink">'.$streveryonecannowchoose.'</span>';
            }

            if(has_capability('mod/forum:viewsubscribers', $context)){
                echo "<br />";
                echo "<span class=\"helplink\"><a href=\"subscribers.php?id=$forum->id\">$strshowsubscribers</a></span>";
            }

            echo '<div class="helplink" id="subscriptionlink">', forum_get_subscribe_link($forum, $context,
                    array('forcesubscribed' => '', 'cantsubscribe' => '')), '</div>';
        }

        if (forum_tp_can_track_forums($forum)) {
            echo '<div class="helplink" id="trackinglink">'. forum_get_tracking_link($forum). '</div>';
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
//        print_box_start('rsslink');
        echo '<span class="wrap rsslink">';
        rss_print_link($course->id, $userid, "forum", $forum->id, $tooltiptext);
        echo '</span>';
//        print_box_end(); // subscription

    }
//    print_box_end(); // subscription
    echo '</div>';

//    print_box_end();  // forumcontrol

//    print_box('&nbsp;', 'clearer');


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

            $canreply    = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $context);
            $canrate     = has_capability('mod/forum:rate', $context);
            $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);

            echo '&nbsp;'; // this should fix the floating in FF
            forum_print_discussion($course, $cm, $forum, $discussion, $post, $displaymode, $canreply, $canrate);
            break;

        case 'eachuser':
            if (!empty($forum->intro)) {
                $options = new stdclass;
                $options->para = false;
                print_box(format_text($forum->intro, FORMAT_MOODLE, $options), 'generalbox', 'intro');
            }
            echo '<p class="mdl-align">';
            if (forum_user_can_post_discussion($forum, null, -1, $cm)) {
                print_string("allowsdiscussions", "forum");
            } else {
                echo '&nbsp;';
            }
            echo '</p>';
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forum_print_latest_discussions($course, $forum, -1, 'header', '', -1, -1, $page, $CFG->forum_manydiscussions, $cm);
            }
            break;

        case 'teacher':
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forum_print_latest_discussions($course, $forum, -1, 'header', '', -1, -1, $page, $CFG->forum_manydiscussions, $cm);
            }
            break;

        default:
            if (!empty($forum->intro)) {
                $options = new stdclass;
                $options->para = false;
                print_box(format_text($forum->intro, FORMAT_MOODLE, $options), 'generalbox', 'intro');
            }
            echo '<br />';
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forum_print_latest_discussions($course, $forum, -1, 'header', '', -1, -1, $page, $CFG->forum_manydiscussions, $cm);
            }


            break;
    }

//////////////////////////////////////////////////tem que rolar um include pra fechar

echo "</td></tr></table>";
//////////////////////////////////////////////////
    print_footer($course);

?>
