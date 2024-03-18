<?php
if (!defined("IN_ESO")) exit;

class home extends Controller {

var $view = "home.view.php";

// Initialize: perform an action depending on what step the user is at in the installation.
function init()
{
	global $config;

	// Set the title.
	$this->title = "Homepage";
}

}

?>
