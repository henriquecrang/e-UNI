<?php
  //this file shows a course contacts list.

  //params:
  // cid: the specific course
  // uid: the user contacts
  // mid: the user mesage contacts
  // stext: search text

  //si no se li passa res, mostrarà tots els contactes accessibles pel curs
  //per la resta és una mòdul normal de Moodle

require_once("../../../../config.php");
require_once("../lib.php");

$id     = optional_param('id', 0, PARAM_INT);       // Course id
$selgroup  = optional_param('group', 0, PARAM_INT);    // Selected group

require_login($id);

if (!$course = get_record('course', 'id', $id)) {
    error('Invalid courseid');
}

// Course context
if ($course->id == SITEID) {
    $context = get_context_instance(CONTEXT_SYSTEM, SITEID);
} else {
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
}

//------------------------- INTERFACE

/// Print the page header

$strnames = get_string("modulenameplural", "email");
$strname  = get_string("modulename", "email");

print_header (get_string('contacts','email'));

//-----només l'alfabet
//if (has_capability('moodle/legacy:admin', $sitecontext, $USER->id, false)) {
echo '<div onclick="switchMenu(\'abcd\')">' .
'<img id="abcd_icon" src="'.$CFG->pixpath.'/t/switch_plus.gif"/>'
.'<LINK href="../email.css" rel="stylesheet" type="text/css">'
.get_string('alphabetical','email').'</div>';
 
echo '<div id="abcd" style="display:none;"><ul>';
//array amb l'abecedari
$alpha  = explode(',', get_string('alphabet'));
 
//alfabet per nom
echo '<li>'.get_string('firstname').': ';
$nch = 0;
$courseid = $course->id;
foreach ($alpha as $ch) {
    if ($nch != 0) {
	echo ', ';
    }
    echo "<a href=\"javascript:reloadiframe('&eid=search_res&pop=si&group=$selgroup&name=$ch')\">$ch</a>";
    $nch++;
}
echo '</li>';
//alfabet per cognom
echo '<li>'.get_string('lastname').': ';
$nch = 0;
foreach ($alpha as $ch) {
    if ($nch!=0) {
	echo ', ';
    }
    echo "<a href=\"javascript:reloadiframe('&eid=search_res&pop=si&group=$selgroup&srname=$ch')\">$ch</a>";
    $nch++;
}
//}

//només els del curs
echo "<li><a href=\"javascript:reloadiframegroup('&eid=search_res&pop=si&group=0&cid=0')\">".get_string('allusersincourse','email')."</a></li>";
echo '</li></ul></div>';

//---posem el formulari de cerca
    echo '<div onclick="switchMenu(\'srch\')">' .
	'<img id="srch_icon" src="'.$CFG->pixpath.'/t/switch_plus.gif"/>'
	.get_string('searchcontact','email').'</div>';
    
    echo '<div id="srch" style="display:none;"><ul>';
    echo '<form method="post" action="search.php?id='.$id.'&pop=si&group='.$selgroup.'" target="bssearch">' .
	'<input id="sfield" type="text" name="search" value="" />' .
	'<input type="submit" name="doit" value="'.get_string('search').'"/>' .
	'</form>';
    echo '</ul></div>';

// Prints group selector for users with a viewallgroups capability if course groupmode is separate
if ($course->groupmode == 1 || $course->groupmode == 2) {
    // Prints all groups if user can see them all
    if (has_capability('block/email_list:viewallgroups', $context) || $course->groupmode == 2) {
        $groups = groups_groupids_to_groups(groups_get_groups($course->id), $course->id);
        $groupsmode = $course->groupmode;
        $currentgroup = $selgroup;
        $urlroot = $CFG->wwwroot.'/email/contacts/list.php?id='.$course->id;
        $showall = 1;
        echo '<br/>';
        print_group_menu($groups, $groupsmode, $currentgroup, $urlroot, $showall);
    } else {
        // Prints only groups current user is a participant of
        $usergroups = groups_get_groups_for_user($USER->id, $course->id);
        // Shows a Show all users to users in multiple groups
        $showall = 0;
        if (count($usergroups) > 1) {
            $showall = 1;
        }
        $usergroups = groups_groupids_to_groups($usergroups, $course->id);
        $urlroot = $CFG->wwwroot.'/email/contacts/list.php?id='.$course->id;
        echo '<br/>';
        print_group_menu($usergroups, $course->groupmode, $selgroup, $urlroot, $showall);
    }
}

//--------- l'frame
echo '<hr />';
echo '<div id="search_res"></div>' .
'<iframe id="idsearch" name="bssearch" src="search.php?id='.$id.'&pop=si&group='.$selgroup.'" style="display:none;"></iframe>' . "\n\n";


// '<iframe id="idsearch" name="isearch" src="search.php?id='.$cm->id.'&cid='.$course->id.'" style="display:none;"></iframe>';

?>
<script type="text/javascript" language="JavaScript" >
 <!-- // Non-Static Javascript functions

// This function checks all added users and enables the remove user if they have
// already been added to the email
function checkAllRemoveActions() {
    var addedids = null;
    var icon = null;

    if (addedids = window.opener.document.getElementsByName('to[]')) {
        for (var i=0; i < addedids.length; i++) {
            if (icon = document.getElementById('deluser'+addedids[i].value)) {
                toggleRemoveAction(addedids[i].value);
            }
        }
    }
    if (addedids = window.opener.document.getElementsByName('cc[]')) {
        for (var i=0; i < addedids.length; i++) {
            if (icon = document.getElementById('deluser'+addedids[i].value)) {
                toggleRemoveAction(addedids[i].value);
            }
        }
    }
    if (addedids = window.opener.document.getElementsByName('bcc[]')) {
        for (var i=0; i < addedids.length; i++) {
            if (icon = document.getElementById('deluser'+addedids[i].value)) {
                toggleRemoveAction(addedids[i].value);
            }
        }
    }
}

// This function enables/disables a remove icon for a particular user on the contact list
// Also changes the style to .useradded
function toggleRemoveAction(userid) {
    var icon = document.getElementById('deluser'+userid)
    var row = document.getElementById('row'+userid);
    var buttonto = document.getElementById('addto'+userid);
    var buttoncc = document.getElementById('addcc'+userid);
    var buttonbcc = document.getElementById('addbcc'+userid);

    if (icon.style.visibility == 'hidden') {
        icon.style.visibility = '';
        row.className = 'useradded';
        buttonto.style.visibility = 'hidden';
        buttoncc.style.visibility = 'hidden';
        buttonbcc.style.visibility = 'hidden';
    } else {
        icon.style.visibility = 'hidden';
        row.className = '';
        buttonto.style.visibility = '';
        buttoncc.style.visibility = '';
        buttonbcc.style.visibility = '';
    }
}
 //funció per posar el resultat
function changeme (cid,txt) {
    document.getElementById(cid).innerHTML = txt;
}

// Also resets the groupdropmenu if its there
function reloadiframegroup(params) {
    if (document.getElementById('selectgroup_jump')) {
        document.getElementById('selectgroup_jump').selectedIndex=0;
    }
    reloadiframe(params);
}

//recarrega la cosa amb els paràmetres
function reloadiframe (params) {
    var url = "search.php?id=<?php echo $id;?>"+params;
    document.getElementById("idsearch").src = url;
    // document.write( "Somthing" + url);
    //document.getElementById("search_res").innerHTML = url;
}
	
//mostra/oculta un div
function switchMenu(obj) {
    var el = document.getElementById(obj);
    if ( el.style.display != 'none' ) {
	el.style.display = 'none';
    } else {
	el.style.display = '';
    }
    //això és extra
    var im = document.getElementById(obj+"_icon");
    var srcb = "<?php echo $CFG->pixpath.'/t/'; ?>";
    if ( im.src == srcb+"switch_plus.gif" ) {
	im.src = srcb+"switch_minus.gif";
    }  else {
	im.src = srcb+"switch_plus.gif";
    }
}

/**
 * This function applys an action to all users in the contact popup
 *
 * @param string action 'to' = sendto all, 'cc' = sendcc all, 'bcc' = sendbcc all, 'remove' = remove all
 **/
function action_all_users(action) {
    // Gets all rows of users showing and starts with second (wont include header)
    var allrows = document.getElementsByTagName('tr');
    for (var i = 1; i < allrows.length; i++) {
        // Gets the users name
        var username = allrows[i].childNodes[0].childNodes[0].getElementsByTagName('span')[0].innerHTML;
        var userid   = allrows[i].id.substring(3, allrows[i].id.length);
        // Username via innerHTML, substring for id, and passed action
        if (window.opener.addContact(username, userid, action)) {
            // Changes display of user row to show it was added/removed
            toggleRemoveAction(userid);
        }
    }
}

// done hiding -->	
</script>
<?php

  /// Finish the page
  //print_footer();
// Prints a close window link
echo '<br><center><a href="javascript:window.close();">'.get_string('closewindow').'</a></center>';
print_footer($course);
//include ($CFG->themedir.current_theme().'/footer.html');
//echo '</body></html>';

?>