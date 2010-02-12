<?php
/**
 * Original file from internalmail mod...modified to work with eMail mod
 *
 * Mod By: Michael Avelar
 **/
 
echo "\n\n<script language=\"JavaScript\" type=\"text/javascript\">";
echo "\n<!-- // Non-Static Javascript functions";

echo "\n function addAllContacts(emails) {";

$idc = optional_param('id', 0, PARAM_INT); 
$option = optional_param('option', 0, PARAM_INT); 

if($option==1) {
    echo "\n   field=window.document.theform.destiny;";
    echo "\n   field.value=emails;";
//	echo "\n   for (i=0;i<emails.length;i++){";
//    echo "\n     field.value= field.value+ \",\" + emails[i];";		
//	echo "\n   }";
} else {
    echo "location.href=\"$CFG->wwwroot/email/view.php?id=$courseid&mailid=$mailid&folderid=$folderid&filterid=$filterid&folderoldid=$folderoldid&action=$action\"";
}

echo "\n }";

echo "\n function addContact(email, cid, sendto) { ";

#$query=$_SERVER[QUERY_STRING];
#$aux=split('&',$query);
#$aux2=split('=',$aux[0]);
#$idc=$aux2[1];
#$aux2=split('=',$aux[1]);
#$option=$aux2[1];

$idc = optional_param('id', 0, PARAM_INT); 
$option = optional_param('option', 0, PARAM_INT); 

if($option==1) {
    echo "\n   field=window.document.theform.destiny;";
    echo "\n   if(field.value==\"\"){";
    echo "\n     field.value= email;";
    echo "\n   } else {";
    echo "\n    var bool=0;";
    echo "\n    var comprova=field.value.split(\",\");";
    echo "\n    var n=comprova.length;";
    echo "\n    var i=0;";

    echo "\n    while(i<=n && bool==0){";
    echo "\n      if (email==comprova[i]) {";
    echo "\n        bool=1;";
    echo "\n      }";
    echo "\n      i++;";
    echo "\n    }";

    echo "\n    if (bool==0) {";
    echo "\n      field.value= field.value + \",\" + email;";
    echo "\n    }";
    echo "\n   }";
} else {
    //echo "location.href=\"$CFG->wwwroot/email/view.php?id=$courseid&mailid=$mailid&folderid=$folderid&filterid=$filterid&folderoldid=$folderoldid&action=$action\"";
}
// Adds the contact to the list and the hidden id field.  Will ignore click event if the
// ID/contact exists in to[], cc[], or bcc[] fields
echo "  if (sendto == 'bcc') {
            var field = window.document.sendmail.namebcc;
        } else if (sendto == 'cc') {
            var field = window.document.sendmail.namecc;
        } else if (sendto == 'to') {
            var field = window.document.sendmail.nameto;
        } else if (sendto == 'remove') {
            var userremoved = false;
            if (removeContact(cid, 'to', window.document.sendmail.nameto, email)) {
                userremoved = true;
            }
            if (removeContact(cid,'cc', window.document.sendmail.namecc, email)) {
                userremoved = true;
            }
            if (removeContact(cid,'bcc', window.document.sendmail.namebcc, email)) {
                userremoved = true;
            }

            return userremoved;
        }

        // Checks if the user is already sending to clicked user in some way
        if (alreadySending('to', cid)) {
            return false;
        }
        if (alreadySending('cc', cid)) {
            return false;
        }
        if (alreadySending('bcc', cid)) {
            return false;
        }

        // Adds the user id to the hidden fields for submit
        // I Very Hate IE...had to do this ugly hack to get this to work for IE 6+ :(
        var contacts = window.document.createElement(\"span\");
        //var contacts = window.document.createElement(\"input\");
        //contacts.setAttribute(\"type\", \"hidden\");
        //contacts.setAttribute(\"value\", cid);
        //contacts.setAttribute(\"name\", sendto + \"[]\");
        window.document.getElementById('id_name'+sendto).parentNode.appendChild(contacts);
        contacts.innerHTML = '<input type=\"hidden\" value=\"'+cid+'\" name=\"'+sendto+'[]\">';
        
        // Adds Name to sendto list
        if (field.value == '') {
            field.value = email;
        } else {
            // Checks for valid string entry for post-send validation
            if ((field.value.charAt(field.value.length-2) != ',')) {
                if ((field.value.charAt(field.value.length-1) != ',')) {
                    email = ', '+email;
                } else {
                    email = ' '+email;
                }
            }
            field.value = field.value + email;
        }
        return true;
        ";

echo "\n }";

?>

// This function removes an added contact
function removeContact(cid, type, txtfield, email) {
    if (existing = window.document.getElementsByName(type+'[]')) {
        for (var i=0; i < existing.length; i++) {
            if (cid == existing[i].value) {
                var parent = window.document.getElementById('id_name'+type).parentNode;
                var txtfieldval = txtfield.value;
                // Removes this element and returns boolean true
                parent.removeChild(existing[i].parentNode);
                // Removes the name from the contacts list
                if (txtfieldval.indexOf(',') == -1) {
                    txtfield.value = '';
                } else {
                    var firstindex = txtfieldval.indexOf(email);
                    // Not first name...so remove the comma as well
                    if (firstindex != 0) {
                        email = ', '+email;
                    } else {
                        email = email+', ';
                    }
                    txtfieldval = txtfieldval.replace(email, '');
                    txtfield.value = txtfieldval;
                }
                
                return true;
            }
        }
    }
    // Not found...user not added
    return false;
}

// This function checks if user is added already
// @param string sento 'to', 'cc', 'bcc'
// @param integer cid ID of user
// @return boolean true if already sending, false if new contact
function alreadySending(type, cid) {
    var old = null;
    
    if (old = window.document.getElementsByName(type+'[]')) {
        for (var i=0; i < old.length; i++) {
            if (cid == old[i].value) {
                return true;
            }
        }
    } else {
        return false;
    }
}

//posa una direcció directament al per, eliminant-ne la resta
    function setContact(email) {
    window.document.theform.destiny.value = email;
   }

//cid: id de l'element a canviar-li el contingut
function changeme (cid,txt) {
    document.getElementById(cid).innerHTML = txt;
}
	
//cid: element on assignat el valor
function setPage (cid,txt) {
    document.getElementById(cid).value = txt;
}
	
//el mític toggle per modificar la visibilitat
function toggle(obj) {
    var el = window.document.getElementById(obj);
                           alert(el + obj);
    if ( el.style.display != 'none' ) {
	el.style.display = 'none';
    } else {
	el.style.display = '';
    }
}
// done hiding -->
</script>

<?php

?>