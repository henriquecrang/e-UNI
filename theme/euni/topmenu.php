<div id="top_menu_date">
<a href="<?php echo $CFG->wwwroot.'/calendar/view.php' ?>"><script language="Javascript" type="text/javascript">
//<![CDATA[
<!--

// Get today's current date.
var now = new Date();

// Array list of days.
var days = new Array('Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira, ','Sexta-feira','Sábado');

// Array list of months.
var months = new Array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');

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
     
       <li class="home"><div><a href="<?php echo $CFG->wwwroot.'/' ?>"><img width="18" height="17" src="<?php echo $CFG->httpswwwroot.'/theme/'.current_theme() ?>/images/home_icon.png" alt=""/></a></div>
       </li> 
 
        <li class="bkg_b03"><div><a href="<?php echo $CFG->wwwroot.'/user/view.php?id='.$USER->id.'&course=1' ?>">Meu Perfil</a></div>

 
 	<li class="bkg_b04"><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Disciplinas</a>
       		<ul>
     
<?php

//Imprimindo as disciplinas no menu dropdown

$result = get_records_sql("SELECT cr.fullname, cr.id FROM mdl_course cr, mdl_role_assignments ra, mdl_context ct WHERE ct.contextlevel = 50 AND ct.instanceid = cr.id AND ra.contextid = ct.id AND ra.userid = $USER->id ORDER BY cr.fullname ASC");
foreach ($result as $courses) {
	$course_name = $courses->fullname;
	$course_id = $courses->id;
	?>

	<li><a href="<?php print $CFG->wwwroot; ?>/course/view.php?id=<?php print $course_id; ?>"><?php print $course_name; ?></a></li>


<?php
}
?>       		
		
		            </ul></div>
 
 
 	<!--<li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Eventos</a></div>-->
        <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>theme/euni/componentes/noticias/noticias.php">Notícias</a></div> 
 	<li><div><a href="<?php echo $CFG->wwwroot.'/' ?>theme/euni/componentes/sobre/sobre_euni.php">Sobre o e-UNI</a></div>
 
 
        
