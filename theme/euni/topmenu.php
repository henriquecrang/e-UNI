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
     
      		
		<li><a href="http://">Fundamentos de Educação Especial</a></li>
		<li><a href="http://">Metodologia Científica</a></li>
		<li><a href="http://">Processos: Políticas e Sistemas</a></li>
		            </ul></div>
 
 
 		<li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Eventos</a>
       <!-- <ul>
     
       		<li><a href="http://">Item 1</a></li>
		<li><a href="http://">Item 2</a></li>
		<li><a href="http://">Item 3</a></li>
		<li><a href="http://">Item 4</a></li>
		            </ul>--></div>
            
            <li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Notícias</a>
        <!--<ul>
     
        	<li><a href="http://">Item 1</a></li>
		<li><a href="http://">Item 2</a></li>
		<li><a href="http://">Item 3</a></li>
		<li><a href="http://">Item 4</a></li>            </ul>--></div>
 
 
 		<li><div><a href="<?php echo $CFG->wwwroot.'/' ?>">Sobre o e-UNI</a>
        <!--<ul>
     
        	<li><a href="http://">Item 1</a></li>
		<li><a href="http://">Item 2</a></li>
		<li><a href="http://">Item 3</a></li>
		<li><a href="http://">Item 4</a></li>            </ul>--></div>
 
 
        
