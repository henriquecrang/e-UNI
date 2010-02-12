<?PHP // $Id: version.php,v 1.23.2.1 2006/05/17 06:19:17 martinlanghoff Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of portafolio
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2006083000;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2005021600;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)

?>
