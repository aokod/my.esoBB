<?php
/**
 * This file is part of the esoBB project, a derivative of esoTalk.
 * It has been modified by several contributors.  (contact@geteso.org)
 * Copyright (C) 2023 esoTalk, esoBB.  <https://geteso.org>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
if (!defined("IN_ESO")) exit;

/**
 * eso controller: handles global actions such as logging in/out,
 * preparing the bar, and collecting messages.
 */
class eso extends Controller {

var $db;
var $action;
var $allowedActions = array("create", "delete", "home", "legal", "policy", "stats");
var $controller;
var $view = "wrapper.php";
var $language;
var $head = "";
var $scripts = array();
var $jsLanguage = array();
var $jsVars = array();
var $footer = array();
var $styleSheets = array();


// Class constructor: connect to the database and perform other initializations.
function __construct()
{	
	global $config;

	// Connect to the database by setting up the database class.
	$this->db = new Database($config["mysqlHost"], $config["mysqlUser"], $config["mysqlPass"], $config["mysqlDb"]);
	$this->db->eso =& $this;
	if ($this->db->connectError())
		$this->fatalError($config["verboseFatalErrors"] ? $this->db->connectError() : "", "mysql");
}

// Initialize: set up the user and initialize the bar and other components of the page.
function init()
{
	global $config;

	// Set up some default JavaScript files and language definitions.
	$this->addScript("js/eso.js", -1);
//	$this->addLanguageToJS("ajaxRequestPending", "ajaxDisconnected");
	
	$this->callHook("init");
}

// Check the first parameter of the URL against $name, and instigate the controller it refers to if they match.
function registerController($name, $file)
{
	if (@$_GET["q1"] == $name) {
		require_once $file;
		$this->action = $name;
		$this->controller = new $name;
		$this->controller->eso =& $this;
	}
}

// Get an array of language packs from the languages/ directory.
function getLanguages()
{
	$languages = array();
	if ($handle = opendir("languages")) {
		while (false !== ($v = readdir($handle))) {
			if (!in_array($v, array(".", "..")) and substr($v, -4) == ".php" and $v[0] != ".") {
				$v = substr($v, 0, strrpos($v, "."));
				$languages[] = $v;
			}
		}
	}
	sort($languages);
	return $languages;
}

// Halt page execution and show a fatal error message.
function fatalError($message, $esoSearch = "")
{
	global $language, $config;
	$this->callHook("fatalError", array(&$message));
	if (defined("AJAX_REQUEST")) {
		header("HTTP/1.0 500 Internal Server Error");
		echo strip_tags("{$language["Fatal error"]} - $message");
	} else {
		$messageTitle = isset($language["Fatal error"]) ? $language["Fatal error"] : "Fatal error";
		$messageBody = sprintf($language["fatalErrorMessage"], $esoSearch) . ($message ? "<div class='info'>$message</div>" : "");
		include "views/message.php";
	}
	exit;
}

// Add a message to the messages language definition array.
function addMessage($key, $class, $message)
{
	global $messages;
	if (!isset($messages[$key])) $messages[$key] = array("class" => $class, "message" => $message);
	return $key;
}

// Display a message (referred to by $key) on the page. $arguments will be used to fill out placeholders in a message.
function message($key, $disappear = true, $arguments = false)
{
	$this->callHook("message", array(&$key, &$disappear, &$arguments));
	$_SESSION["messages"][] = array("message" => $key, "arguments" => $arguments, "disappear" => $disappear);
}

// Generate the HTML of a single message.
function htmlMessage($key, $arguments = false)
{
	global $messages;
	$m = $messages[$key];
	if (!empty($arguments)) $m["message"] = is_array($arguments) ? vsprintf($m["message"], $arguments) : sprintf($m["message"], $arguments);
	return "<div class='msg {$m["class"]}'>{$m["message"]}</div>";
}

// Generate the HTML of all of the collected messages to be displayed at the top of the page.
function getMessages()
{
	global $messages;
	
	// Loop through the messages and append the HTML of each one.
	$html = "<div id='messages'>";
	foreach ($_SESSION["messages"] as $m) $html .= $this->htmlMessage($m["message"], $m["arguments"]) . "\n";
	$html .= "</div>";
	
	// Add JavaScript code to register the messages individually in the Messages object.
	$html .= "<script type='text/javascript'>
Messages.init();";
	foreach ($_SESSION["messages"] as $m) {
		if (!empty($m["arguments"])) $text = is_array($m["arguments"]) ? vsprintf($messages[$m["message"]]["message"], $m["arguments"]) : sprintf($messages[$m["message"]]["message"], $m["arguments"]);
		else $text = $messages[$m["message"]]["message"];
		$html .= "Messages.showMessage(\"{$m["message"]}\", \"{$messages[$m["message"]]["class"]}\", \"" . escapeDoubleQuotes($text) . "\", " . ($m["disappear"] ? "true" : "false") . ");\n";
	}
	$html .= "</script>";
	
	return $html;
}

// Set a JavaScript variable so it can be accessed by JavaScript code on the page.
function addVarToJS($key, $val)
{
	$this->jsVars[$key] = $val;
}

// Add a JavaScript file to be included in the page.
function addScript($script, $position = false)
{
	if (in_array($script, $this->scripts)) return false;
	addToArray($this->scripts, $script, $position);
}

// Add a string of HTML to be outputted inside of the <head> tag.
function addToHead($string)
{
	$this->head .= "\n$string";
}

// Generate all of the HTML to be outputted inside of the <head> tag.
function head()
{
	global $config, $language;
	
	$head = "<!-- This page was generated by myesoBB (https://myeso.org) -->\n";
	
	// Base URL and RSS Feeds.
	$head .= "<base href='{$config["baseURL"]}'/>\n";
//	$head .= "<link href='{$config["baseURL"]}" . makeLink("feed") . "' rel='alternate' type='application/rss+xml' title='{$language["Recent posts"]}'/>\n";

	// Stylesheets.
	ksort($this->styleSheets);
	foreach ($this->styleSheets as $styleSheet) {
		// If media is ie6 or ie7, use conditional comments.
		if ($styleSheet["media"] == "ie6" or $styleSheet["media"] == "ie7")
			$head .= "<!--[if " . ($styleSheet["media"] == "ie6" ? "lte IE 6" : "IE 7") . "]><link rel='stylesheet' href='{$styleSheet["href"]}' type='text/css'/><![endif]-->\n";
		// If not, use media as an attribute for the link tag.
		else $head .= "<link rel='stylesheet' href='{$styleSheet["href"]}' type='text/css'" . (!empty($styleSheet["media"]) ? " media='{$styleSheet["media"]}'" : "") . "/>\n";
	}

	// Custom favicon if any or skin favicon.
//	$head .= "<link rel='shortcut icon' type='image/ico' href='" . (!empty($config["shortcutIcon"]) ? $config["shortcutIcon"] : "skins/{$config["skin"]}/" . (isset($this->skin->favicon) ? $this->skin->favicon : "favicon.ico")) . "'/>";

	// JavaScript: add the scripts collected in the $this->scripts array (via $this->addScript()).
 	ksort($this->scripts);
 	foreach ($this->scripts as $script) $head .= "<script type='text/javascript' src='$script'></script>\n";

 	// Conditional browser comments to detect IE.
 //	$head .= "<!--[if lte IE 6]><script type='text/javascript' src='js/ie6TransparentPNG.js'></script><script type='text/javascript'>var isIE6=true</script><![endif]-->\n<!--[if IE 7]><script type='text/javascript'>var isIE7=true</script><![endif]-->";

 	// Output all necessary config variables and language definitions, as well as other variables.
	$esoJS = array(
		"baseURL" => $config["baseURL"],
//		"language" => $this->jsLanguage,
		"token" => $_SESSION["token"]
	) + $this->jsVars;
	$head .= "<script type='text/javascript'>// <![CDATA[
var eso=" . json($esoJS) . ",isIE6,isIE7// ]]></script>\n";
	
	// Finally, append the custom HTML string constructed via $this->addToHead().
	$head .= $this->head;
	
	$this->callHook("head", array(&$head));

	return $head;
}

// Add a string of HTML to the page footer.
function addToFooter($html, $position = false)
{
	addToArray($this->footer, $html, $position);
}

// Add a CSS file to be included on the page.
function addCSS($styleSheet, $media = false) 
{
	if (in_array(array("href" => $styleSheet, "media" => $media), $this->styleSheets)) return false;
	addToArray($this->styleSheets, array("href" => $styleSheet, "media" => $media));
}

}

?>
