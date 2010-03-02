<div id="aardvark_menu_date">
<a href="<?php echo $CFG->wwwroot.'/calendar/view.php' ?>"><script language="Javascript" type="text/javascript">
//<![CDATA[
<!--

// Get today's current date.
var now = new Date();

// Array list of days.
var days = new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

// Array list of months.
var months = new Array('January','February','March','April','May','June','July','August','September','October','November','December');

// Calculate the number of the current day in the week.
var date = ((now.getDate()<10) ? "0" : "")+ now.getDate();

// Calculate four digit year.
function fourdigits(number)     {
        return (number < 1000) ? number + 1900 : number;
                                                                }

// Join it all together
today =  days[now.getDay()] + " " +
              date + " " +
                          months[now.getMonth()] + " " +               
                (fourdigits(now.getYear())) ;

// Print out the data.
document.write("" +today+ " ");
  
//-->
//]]>
</script></a>
	
	</div>
    
<ul>
     
       <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>"><img width="18" height="17" src="<?php echo $CFG->httpswwwroot.'/theme/'.current_theme() ?>/images/menu/home_icon.png" alt=""/></a></div>
       </li> 
 
        <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Menu One</a>
					
        <ul>
        <h4>Subtitle Text</h4>

        <?php

 $text ='<li><a href="">Item One</a></li>';
 $text .='<li><a href="">Item Two</a></li>';
 $text .='<li><a href="">Item Three</a></li>';
 $text .='<li><a href="">Item Four</a></li>';
 $text .='<li><a href="">Item Five</a></li>';
 $text .='<li><a href="">Item Six</a></li>';
 $text .='<li><a href="">Item Seven</a></li>';
 $text .='<li><a href="">Item Eight</a></li>';
 
 echo $text;
?>

           </ul></div>
 
        <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Menu Two</a>
					
        <ul>
        <h4>Subtitle Text</h4>

        <?php

 $text ='<li><a href="">Item One</a></li>';
 $text .='<li><a href="">Item Two</a></li>';
 $text .='<li><a href="">Item Three</a></li>';
 $text .='<li><a href="">Item Four</a></li>';
 $text .='<li><a href="">Item Five</a></li>';
 $text .='<li><a href="">Item Six</a></li>';
 $text .='<li><a href="">Item Seven</a></li>';
 $text .='<li><a href="">Item Eight</a></li>';
 
 echo $text;
?>

           </ul></div>
           
        <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Menu Three</a>
					
        <ul>
        <h4>Subtitle Text</h4>

        <?php

 $text ='<li><a href="">Item One</a></li>';
 $text .='<li><a href="">Item Two</a></li>';
 $text .='<li><a href="">Item Three</a></li>';
 $text .='<li><a href="">Item Four</a></li>';
 $text .='<li><a href="">Item Five</a></li>';
 $text .='<li><a href="">Item Six</a></li>';
 $text .='<li><a href="">Item Seven</a></li>';
 $text .='<li><a href="">Item Eight</a></li>';
 
 echo $text;
?>
	

           </ul></div>
                <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Menu Four</a>
					
        <ul>
        <h4>Subtitle Text</h4>

        <?php

 $text ='<li><a href="">Item One</a></li>';
 $text .='<li><a href="">Item Two</a></li>';
 $text .='<li><a href="">Item Three</a></li>';
 $text .='<li><a href="">Item Four</a></li>';
 $text .='<li><a href="">Item Five</a></li>';
 $text .='<li><a href="">Item Six</a></li>';
 $text .='<li><a href="">Item Seven</a></li>';
 $text .='<li><a href="">Item Eight</a></li>';
 
 echo $text;
?>
           </ul></div>
           
        <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Menu Five</a>
					
        <ul>
        <h4>Subtitle Text</h4>

        <?php

 $text ='<li><a href="">Item One</a></li>';
 $text .='<li><a href="">Item Two</a></li>';
 $text .='<li><a href="">Item Three</a></li>';
 $text .='<li><a href="">Item Four</a></li>';
 $text .='<li><a href="">Item Five</a></li>';
 $text .='<li><a href="">Item Six</a></li>';
 $text .='<li><a href="">Item Seven</a></li>';
 $text .='<li><a href="">Item Eight</a></li>';
 
 echo $text;
?> 			

           </ul></div>