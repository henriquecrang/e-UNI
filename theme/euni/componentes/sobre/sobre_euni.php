<?php
require_once($CFG->dirroot ."../../../../config.php");
//require_once($CFG->libdir.'../../blocklib.php');
require_once($CFG->dirroot .'/lib/blocklib.php');

//require_login();

print_header( 'e-UNI: Universidade Eletrônica',
'Sobre o e-UNI',
'Sobre o e-UNI');
//---------------------------------------------------
$CFG->showblock=1;
$inme=me();

if($CFG->showblock==1){

//Now I create a table for the main part of the page with two colums, one for the Block

//and other for the really content of the mod

echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";

echo "<tr><td width=\"150\" valign=\"top\">";

//This is the first colum

//I Include my left Blocks

$PAGE = page_create_object(PAGE_COURSE_VIEW, SITEID);
$pageblocks = blocks_setup($PAGE);

if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
}

//This is the second colum for the content of the mod
echo "</td><td valign=\"top\">O e-UNI é o AVA desenvolvido pela UNIRIO baseadoo no Moodle. Participaram da equipe de desenvolvimento Diogo Martins, Henrique Andrade e Monica Lopes";

}

if($CFG->showblock==1){
echo "</td></tr></table>";
}
$CFG->showblock=0;
//----------------------------------------------------
print_footer();

?>
