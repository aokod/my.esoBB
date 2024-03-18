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

/**
 * Basic page initialization: include configuration settings, check the
 * version, require essential files, start a session, fix magic quotes/
 * register_globals, sanitize request data, include the eso controller,
 * skin file, language file, and load plugins.
 */
if (!defined("IN_ESO")) exit;

// Start a page load timer. We don't make use of it by default, but a plugin can if it needs to.
define("PAGE_START_TIME", microtime(true));

// By default, only report important errors (no warnings or notices.)
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);

// Make sure a default timezone is set... silly PHP 5.
if (ini_get("date.timezone") == "") date_default_timezone_set("GMT");

// Define directory constants.
if (!defined("PATH_ROOT")) define("PATH_ROOT", realpath(__DIR__ . "/.."));
if (!defined("PATH_CONTROLLERS")) define("PATH_CONTROLLERS", PATH_ROOT."/controllers");
if (!defined("PATH_LIBRARY")) define("PATH_LIBRARY", PATH_ROOT."/lib");
if (!defined("PATH_VIEWS")) define("PATH_VIEWS", PATH_ROOT."/views");

// Include our config files.
require PATH_ROOT."/config.php";

// Compare the forum base URL's host to the actual request's host.  If they differ, redirect to the base URL.
// i.e. if baseURL is www.example.com and the forum is accessed from example.com, redirect to www.example.com.
if (isset($_SERVER["HTTP_HOST"])) {
	$urlParts = parse_url($config["baseURL"]);
	if (isset($urlParts["port"])) $urlParts["host"] .= ":{$urlParts["port"]}";
	if ($urlParts["host"] != $_SERVER["HTTP_HOST"]) {
		header("Location: " . $config["baseURL"] . substr($_SERVER["REQUEST_URI"], strlen($urlParts["path"])));
		exit;
	}
}

// Require essential files.
require PATH_LIBRARY."/functions.php";
require PATH_LIBRARY."/database.php";
require PATH_LIBRARY."/classes.php";
require PATH_LIBRARY."/formatter.php";

// Start a session if one does not already exist.
if (!session_id()) {
	session_name("{$config["cookieName"]}_Session");
	session_start();
	$_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];
	if (empty($_SESSION["token"])) regenerateToken();
}

// Prevent session highjacking: check the current IP address against the one that initiated the session.
if ($_SERVER["REMOTE_ADDR"] != $_SESSION["ip"]) session_destroy();
// Check the current user agent against the one that initiated the session.
if (md5($_SERVER["HTTP_USER_AGENT"]) != $_SESSION["userAgent"]) session_destroy();

// Undo register_globals.
undoRegisterGlobals();

// Do we want to force HTTPS?
//header("Location: https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);

// Replace GET values with ones from the request URI.  (ex. index.php/test/123 -> ?q1=test&q2=123)
if (isset($_SERVER["REQUEST_URI"])) {
	$parts = processRequestURI($_SERVER["REQUEST_URI"]);
	for ($i = 1, $count = count($parts); $i <= $count; $i++) $_GET["q$i"] = $parts[$i - 1];
}

// Sanitize the request data using sanitize().
$_POST = sanitize($_POST);
$_GET = sanitize($_GET);
$_COOKIE = sanitize($_COOKIE);

// Include and set up the main controller.
require "controllers/eso.controller.php";
$eso = new eso();
$eso->eso =& $eso;

?>
