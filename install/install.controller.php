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
 * Install controller: performs all installation tasks - checks server
 * environment, runs installation queries, creates configuration files...
 */
class Install {

var $step;
var $config;
var $errors = array();
var $queries = array();

// Initialize: perform an action depending on what step the user is at in the installation.
function init()
{
	// Determine which step we're on:
	// If there are fatal errors, then remain on the fatal error step.
	// Otherwise, use the step in the URL if it's available.
	// Otherwise, default to the warning check step.
	if ($this->errors = $this->fatalChecks()) $this->step = "fatalChecks";
	elseif (@$_GET["step"]) $this->step = $_GET["step"];
	else $this->step = "warningChecks";
	
	switch ($this->step) {
		
		// If on the warning checks step and there are no warnings or the user has clicked "Next", go to the next step.
		case "warningChecks":
			if (!($this->errors = $this->warningChecks()) or isset($_POST["next"])) $this->step("info");
			break;
			
		
		// On the "Specify setup information" step, handle the form processing.
		case "info":
		
			// Prepare a list of language packs in the ../languages folder.
			$this->languages = array();
			if ($handle = opendir("../languages")) {
			    while (false !== ($v = readdir($handle))) {
					if (!in_array($v, array(".", "..")) and substr($v, -4) == ".php" and $v[0] != ".") {
						$v = substr($v, 0, strrpos($v, "."));
						$this->languages[] = $v;
					}
				}
			}
//			// Prepare a list of SMTP email authentication options.
//			$this->smtpOptions = array(
//				false => "None at all (internal email)",
//				"ssl" => "SSL",
//				"tls" => "TLS"
//			);
//			// Prepare a list of MySQL storage engines.
//			$this->storageEngines = array(
//				"InnoDB" => "InnoDB (recommended)",
//				"MyISAM" => "MyISAM (less efficient, smaller)"
//			);
//			// Prepare a list of hashing algorithms.
//			$this->hashingMethods = array(
//				"bcrypt" => "bcrypt (recommended)",
//				"md5" => "MD5 (less secure, faster)"
//			);
			
			// If the form has been submitted...
			if (isset($_POST["forumTitle"])) {
				
				// Validate the form data - do not continue if there were errors!
				if ($this->errors = $this->validateInfo()) return;
				
				// Put all the POST data into the session and proceed to the install step.
				$_SESSION["install"] = array(
					"forumTitle" => $_POST["forumTitle"],
					"forumDescription" => $_POST["forumDescription"],
					"forumURL" => $_POST["forumURL"], // custom subdir name
					"language" => $_POST["language"],
					// DB settings
//					"mysqlHost" => $_POST["mysqlHost"],
//					"mysqlUser" => $_POST["mysqlUser"],
//					"mysqlPass" => $_POST["mysqlPass"],
//					"mysqlDB" => $_POST["mysqlDB"],
					// SMTP settings
//					"sendEmail" => $_POST["sendEmail"],
//					"smtpAuth" => $_POST["smtpAuth"],
//					"smtpHost" => $_POST["smtpHost"],
//					"smtpPort" => $_POST["smtpPort"],
//					"smtpUser" => $_POST["smtpUser"],
//					"smtpPass" => $_POST["smtpPass"],
					// Root user settings
					"adminUser" => $_POST["adminUser"],
					"adminEmail" => $_POST["adminEmail"],
					"adminPass" => $_POST["adminPass"],
					"adminConfirm" => $_POST["adminConfirm"],
					// Advanced settings
//					"tablePrefix" => $_POST["tablePrefix"],
//					"characterEncoding" => $_POST["characterEncoding"],
//					"storageEngine" => $_POST["storageEngine"],
//					"hashingMethod" => $_POST["hashingMethod"],
//					"baseURL" => $_POST["baseURL"],
//					"friendlyURLs" => $_POST["friendlyURLs"]
				);
				$this->step("install");
			}
			
			// If the form hasn't been submitted but there's form data in the session, fill out the form with it.
			elseif (isset($_SESSION["install"])) $_POST = $_SESSION["install"];
			break;
			
		
		// Run the actual installation.
		case "install":
		
			// Go back to the previous step if it hasn't been completed.
			if (isset($_POST["back"]) or empty($_SESSION["install"])) $this->step("info");
			
			// Fo the installation. If there are errors, do not continue.
			if ($this->errors = $this->doInstall()) return;
			
			// Log queries to the session and proceed to the final step.
			$_SESSION["queries"] = $this->queries;
			$this->step("finish");
			break;
			
		
		// Finalise the installation and redirect to the forum.
		case "finish":
		
			// If they clicked the 'go to my forum' button, log them in as the administrator and redirect to the forum.
			if (isset($_POST["finish"])) {
//				include "../config/config.php";
//				$user = $_SESSION["user"];
//				session_destroy();
//				session_name("{$config["cookieName"]}_Session");
//				session_start();
//				$_SESSION["user"] = $user;
//				header("Location: ../");
				// tba: redirect to new forum
				exit;
			}
			// Lock the installer.
//			if (($handle = fopen("lock", "w")) === false)
//				$this->errors[1] = "Your forum can't seem to lock the installer. Please manually delete the install folder, otherwise your forum's security will be vulnerable.";
//			else fclose($handle);
	}

}

// Obtain the hardcoded version of eso (ESO_VERSION).
function getVersion()
{
	include "../config.default.php";
	$version = ESO_VERSION;
	return $version;
}

// Generate a default value for the baseURL based on server environment variables.
function suggestBaseUrl()
{
	$dir = substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"));
	$dir = substr($dir, 0, strrpos($dir, "/"));
	if (array_key_exists("HTTPS", $_SERVER) and $_SERVER["HTTPS"] === "on") $baseURL = "https://{$_SERVER["HTTP_HOST"]}{$dir}/";
	else $baseURL = "http://{$_SERVER["HTTP_HOST"]}{$dir}/";
	return $baseURL;
}

// Generate a default value for whether or not to use friendly URLs, depending on if the REQUEST_URI variable is available.
//function suggestFriendlyUrls()
//{
//	return !empty($_SERVER["REQUEST_URI"]);
//}

// Perform a MySQL query, and log it.
public function query($link, $query)
{	
	$result = mysqli_query($link, $query);
	$this->queries[] = $query;
	return $result;
}

// Fetch a sequential array.  $input can be a string or a MySQL result.
public function fetchRow($link, $input)
{
	if ($input instanceof \mysqli_result) return mysqli_fetch_row($input);
	$result = $this->query($link, $input);
	if (!$this->numRows($link, $result)) return false;
	return $this->fetchRow($link, $result);
}

// Return the number of rows in the result.  $input can be a string or a MySQL result.
public function numRows($link, $input)
{
	if (!$input) return false;
	if ($input instanceof \mysqli_result) return mysqli_num_rows($input);
	$result = $this->query($link, $input);
	return $this->numRows($link, $result);
}

// Perform the installation: run installation queries, and write configuration files.
function doInstall()
{
	global $config;
	
	// Make sure the base URL has a trailing slash.
//	if (substr($_SESSION["install"]["baseURL"], -1) != "/") $_SESSION["install"]["baseURL"] .= "/";

	$domainName = "myeso.org";
	// Make sure the forum URL is valid.
	$subDomainName = $_SESSION["install"]["forumURL"];

	// Make sure the language exists.
	if (!file_exists("../languages/{$_SESSION["install"]["language"]}.php"))
		$_SESSION["install"]["language"] = "English (casual)";

	// Since every forum will be hosted on a subdomain, we need to figure out the baseURL using subDomainName.
	$baseURL = $_SESSION["install"]["baseURL"] = "https://" . $subDomainName . "." . $domainName . "/";
	// cookieName will need to be determined from subDomainName, as there may be several forums with the same forumTitle.
	// tba
	$cookieName = preg_replace(array("/\s+/", "/[^\w]/"), array("_", ""), desanitize($_SESSION["install"]["forumTitle"]));
	
	// Prepare the $config variable with the installation settings.
	$config = array(
		"forumTitle" => $_SESSION["install"]["forumTitle"],
		"forumDescription" => $_SESSION["install"]["forumDescription"],
		"language" => $_SESSION["install"]["language"],
		// Every forum will rely on a default config, so DB host is unnecessary.
//		"mysqlHost" => desanitize($_SESSION["install"]["mysqlHost"]),
		"mysqlUser" => desanitize($_SESSION["install"]["mysqlUser"]),
		"mysqlPass" => desanitize($_SESSION["install"]["mysqlPass"]),
		// Every forum has its own database.
		"mysqlDB" => desanitize($_SESSION["install"]["mysqlDB"]),
		// SMTP settings
//		"emailFrom" => "do_not_reply@{$_SERVER["HTTP_HOST"]}",
//		"sendEmail" => !empty($_SESSION["install"]["sendEmail"]),
		// Advanced settings
		// These will be defined in the config.default.php.
//		"tablePrefix" => desanitize($_SESSION["install"]["tablePrefix"]),
//		"characterEncoding" => desanitize($_SESSION["install"]["characterEncoding"]),
//		"storageEngine" => desanitize($_SESSION["install"]["storageEngine"]),
//		"hashingMethod" => desanitize($_SESSION["install"]["hashingMethod"]),
		"baseURL" => $baseURL,
		"cookieName" => $cookieName,
//		"useFriendlyURLs" => !empty($_SESSION["install"]["friendlyURLs"]),
//		"useModRewrite" => !empty($_SESSION["install"]["friendlyURLs"]) and function_exists("apache_get_modules") and in_array("mod_rewrite", apache_get_modules())
	);

	// SMTP will be added in the near future.
//	$smtpConfig = array(
//		"smtpAuth" => desanitize($_SESSION["install"]["smtpAuth"]),
//		"smtpHost" => desanitize($_SESSION["install"]["smtpHost"]),
//		"smtpPort" => desanitize($_SESSION["install"]["smtpPort"]),
//		"smtpUser" => desanitize($_SESSION["install"]["smtpUser"]),
//		"smtpPass" => desanitize($_SESSION["install"]["smtpPass"]),
//	);
//	if (!empty($_SESSION["install"]["smtpAuth"])) $config = array_merge($config, $smtpConfig);
	
	// Create the MySQL user and pass.
	// There needs to be a MySQL user that is only able to create databases and users.
	$mysqlHost = "localhost";
	$mysqlUser = "myeso_createdb";
	$mysqlPass = "pass";
	$characterEncoding = "utfmb4";

	$createDb = @mysqli_connect($mysqlHost, $mysqlUser, $mysqlPass, $config["mysqlDB"]);
	mysqli_set_charset($createDb, $characterEncoding);

	// Database user will be the subdomain name prefixed by "myeso_user_" for identification purposes.
	$newDomainName = mysqli_real_escape_string($createDb, $subDomainName);
	$newUser = "myeso_user_" . $newDomainName;
	// Generate a 32 character length pseudo random password.
	$newPass = bin2hex(openssl_random_pseudo_bytes(16));
	// do a similar thing for db name
	$newDB = "myeso_db_" . $newDomainName;

//	include "query_createDb.php";
	$createQueries = array();
	$createQueries[] = "CREATE USER '{$newUser}'@'{$mysqlHost}' IDENTIFIED BY '{$newPass}'";
	$createQueries[] = "CREATE DATABASE {$newDB}";
	$createQueries[] = "GRANT ALL PRIVILEGES ON {$newDB}.* TO '{$newUser}'@'{$mysqlHost}'";
	foreach ($createQueries as $query) {
		if (!$this->query($createDb, $query)) return array(1 => "<code>" . sanitizeHTML(mysqli_error($createDb)) . "</code><p><strong>The query that caused this error was</strong></p><pre>" . sanitizeHTML($query) . "</pre>");
	}
	// Make sure to close the connection!
	$createDb->close();
 
	// Connect to the MySQL database.
	$db = @mysqli_connect($mysqlHost, $newUser, $newPass, $newDB);
	mysqli_set_charset($db, $config["characterEncoding"]);
	
	// Run the queries one by one and halt if there's an error!
	include "queries.php";
	foreach ($queries as $query) {
		if (!$this->query($db, $query)) return array(1 => "<code>" . sanitizeHTML(mysqli_error($db)) . "</code><p><strong>The query that caused this error was</strong></p><pre>" . sanitizeHTML($query) . "</pre>");
	}

	// Actually install the forum software into a subdirectory.
	$forumFolder = "myeso_forum_" . $subDomainName;
	$forumDir = "/var/www/" . $forumFolder;

	// First, we need to create the folder.
	mkdir($forumRoot, 0755);
	mkdir($forumRoot . "/sessions", 0755);
	// Download and unzip the forum software into the folder.
	shell_exec("wget --directory-prefix=" . $forumDir . "/ https://github.com/geteso/eso/releases/download/1.0.0d2/1.0.0d2.tar.gz | tar -xvzf 1.0.0d2.tar.gz | mv 1.0.0d2/* . | rm -r 1.0.0d2 1.0.0d2.tar.gz");
	// Set file ownership and permissions.
	shell_exec("chown www-data:www-data " . $forumDir . " " . $forumDir . "/avatars " . $forumDir . "/config " . $forumDir . "/sessions");
	shell_exec("chmod 755 " . $forumDir . " " . $forumDir . "/avatars " . $forumDir . "/config " . $forumDir . "/sessions");
	// Use symbolic links to keep key files in one place.
	$templateDir = "/var/www/myeso_template";
	symlink($templateDir . "/index.php", $forumRoot . "/index.php");
	symlink($templateDir . "/ajax.php", $forumRoot . "/ajax.php");
	symlink($templateDir . "/sitemap.php", $forumRoot . "/sitemap.php");
	symlink($templateDir . "/manifest.php", $forumRoot . "/manifest.php");
	symlink($templateDir . "/config.default.php", $forumRoot . "/config.default.php");
	symlink($templateDir . "/controllers", $forumRoot . "/controllers");
	symlink($templateDir . "/js", $forumRoot . "/js");
	symlink($templateDir . "/languages", $forumRoot . "/languages");
	symlink($templateDir . "/lib", $forumRoot . "/lib");
	symlink($templateDir . "/plugins", $forumRoot . "/plugins");
	symlink($templateDir . "/skins", $forumRoot . "/skins");
	symlink($templateDir . "/views", $forumRoot . "/views");
	// clean up code in future
	// avatars, config and sessions remain unique
	// install, upgrade excluded
	$nginxDir = "/etc/nginx";
	$leDir = "/etc/letsencrypt/live/" . $subDomainName . "." . $domainName;
	// Create NGINX configuration
	shell_exec("echo -e 'server {
		listen 80;
		listen [::]:80;
		server_name " . $subDomainName . "." . $domainName . ";
		return 301 https://\$host\$request_uri;
	} server {
		listen 443 ssl http2;
		listen [::]:443 ssl http2;
		server_name " . $subDomainName . "." . $domainName . ";
		ssl_certificate " . $leDir . "/fullchain.pem;
		ssl_certificate_key " . $leDir . "/privkey.pem;
		root " . $forumDir . ";
		index index.php;
		location / {
			try_files \$uri \$uri/ /index.php?\$query_string;
		}
		location ~ \.php$ {
			include snippets/fastcgi-php.conf;
			fastcgi_pass unix:/run/php/php8.0-fpm.sock;
			fastcgi_param PHP_VALUE \"open_basedir=" . $forumDir . "\";
			fastcgi_param PHP_VALUE \"upload_tmp_dir=" . $forumDir . "/sessions\";
			fastcgi_param PHP_VALUE \"session.save_path=" . $forumDir . "/sessions\";
		}
	}' > " . $nginxDir . "\/sites-available\/" . $forumFolder . "conf");
	// Obtain HTTPS certificate
	shell_exec("certbot certonly --nginx -d " . $subDomainName . "." . $domainName);
	// Enable NGINX configuration and reload
	shell_exec("ln -s " . $nginxDir . "\/sites-available\/" . $forumFolder . ".conf " . $nginxDir . "\/sites-enabled\/" . $forumFolder . ".conf | systemctl reload nginx");

	// Write the $config variable to config.php.
	writeConfigFile($forumDir . "/config/config.php", '$config', $config);
	
	// Write the plugins.php file, which contains plugins enabled by default.
	$enabledPlugins = array("Emoticons");
	if ((extension_loaded("gd") or extension_loaded("gd2")) and function_exists("imagettftext"))
		$enabledPlugins[] = "Captcha";
	if (!file_exists($forumDir . "/config/plugins.php")) writeConfigFile($forumDir . "/config/plugins.php", '$config["loadedPlugins"]', $enabledPlugins);
	
	// Write the skin.php file, which contains the enabled skin, and custom.php.
	if (!file_exists($forumDir . "/config/skin.php")) writeConfigFile($forumDir . "/config/skin.php", '$config["skin"]', "Plastic");
	if (!file_exists($forumDir . "/config/custom.php")) writeFile($forumDir . "/config/custom.php", '<?php
if (!defined("IN_ESO")) exit;
// Any language declarations, messages, or anything else custom to this forum goes in this file.
// Examples:
// $language["My settings"] = "Preferences";
// $messages["incorrectLogin"]["message"] = "Oops! The login details you entered are incorrect. Did you make a typo?";
?>');
	// Write custom.css and index.html as empty files (if they're not already there.)
	if (!file_exists($forumDir . "/config/custom.css")) writeFile($forumDir . "/config/custom.css", "");
	if (!file_exists($forumDir . "/config/index.html")) writeFile($forumDir . "/config/index.html", "");
	
	// Write the versions.php file with the current version.
	include $forumDir . "/config.default.php";
	writeConfigFile($forumDir . "/config/versions.php", '$versions', array("eso" => ESO_VERSION));
	
	// Write a .htaccess file if they are using friendly URLs (and mod_rewrite).
//	if ($config["useModRewrite"]) {
//		writeFile(PATH_ROOT."/.htaccess", "# Generated by esoBB (https://geteso.org)
//<IfModule mod_rewrite.c>
//RewriteEngine On
//RewriteCond %{REQUEST_FILENAME} !-f
//RewriteRule ^(.*)$ index.php/$1 [QSA,L]
//</IfModule>");
//	}
	
	// Write a robots.txt file.
	writeFile($forumDir . "/robots.txt", "User-agent: *
Disallow: /search/
Disallow: /online/
Disallow: /join/
Disallow: /forgot-password/
Disallow: /conversation/new/
Disallow: /site.webmanifest/
Sitemap: {$config["baseURL"]}sitemap.php");
	
	// Prepare to log in the administrator.
	// Don't actually log them in, because the current session gets renamed during the final step.
//	$_SESSION["user"] = array(
//		"memberId" => 1,
//		"name" => $_SESSION["install"]["adminUser"],
//		"account" => "Administrator",
//		"color" => $color,
//		"emailOnPrivateAdd" => false,
//		"emailOnStar" => false,
//		"language" => $_SESSION["install"]["language"],
//		"avatarAlignment" => "alternate",
//		"avatarFormat" => "",
//		"disableJSEffects" => false
//	);
}

// Validate the information entered in the 'Specify setup information' form.
function validateInfo()
{
	$errors = array();

	// Forum title must contain at least one character.
	if (!strlen($_POST["forumTitle"])) $errors["forumTitle"] = "Your forum title must consist of at least one character";

	// Forum description also must contain at least one character.
	if (!strlen($_POST["forumDescription"])) $errors["forumDescription"] = "Your forum description must consist of at least one character";

	// Forum URL must be valid.
	if (in_array(strtolower($_POST["forumURL"]), array("myeso", "myesobb", "eso", "esobb", "esotalk", "geteso", "support", "help", "docs", "about", "info", "forum", "official", "tormater", "www"))) $errors["forumURL"] = "The subdomain you have entered is reserved and cannot be used";
	if (!strlen($_POST["forumURL"])) $errors["forumURL"] = "Your forum's subdomain can't be empty";
	if (preg_match("/^[a-zA-Z0-9\s]*$/", $_POST["forumURL"])) $errors["forumURL"] = "Your forum's subdomain must be alphanumeric";
	if (strlen($_POST["forumURL"]) > 25) $errors["forumURL"] = "Your forum's subdomain can't be greater than 25 characters";

	// Username must not be reserved, and must not contain special characters.
	if (in_array(strtolower($_POST["adminUser"]), array("guest", "member", "members", "moderator", "moderators", "administrator", "administrators", "suspended", "everyone", "myself"))) $errors["adminUser"] = "The name you have entered is reserved and cannot be used";
	if (!strlen($_POST["adminUser"])) $errors["adminUser"] = "You must enter a name";
	if (preg_match("/[" . preg_quote("!/%+-", "/") . "]/", $_POST["adminUser"])) $errors["adminUser"] = "You can't use any of these characters in your name: ! / % + -";
	
	// Email must be valid.
	if (!preg_match("/^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i", $_POST["adminEmail"])) $errors["adminEmail"] = "You must enter a valid email address";
	
	// Password must be at least 6 characters.
	if (strlen($_POST["adminPass"]) < 6) $errors["adminPass"] = "Your password must be at least 6 characters";
	
	// Password confirmation must match.
	if ($_POST["adminPass"] != $_POST["adminConfirm"]) $errors["adminConfirm"] = "Your passwords do not match";
	
	// Try and connect to the database.
//	$db = @mysqli_connect($mysqlHost, $_POST["mysqlUser"], $_POST["mysqlPass"], $_POST["mysqlDB"]);
//	if (!$db) $errors["mysql"] = "The installer could not connect to the MySQL server.";
//	The error returned was:<br/> " . mysqli_connect_error();

	// Check to see if there are any conflicting tables already in the database.
	// If there are, show an error with a hidden input. If the form is submitted again with this hidden input,
	// proceed to perform the installation regardless.
//	elseif ($_POST["tablePrefix"] != @$_POST["confirmTablePrefix"] and !count($errors)) {
//		$theirTables = array();
//		$result = $this->query($db, "SHOW TABLES");
//		while (list($table) = $this->fetchRow($db, $result)) $theirTables[] = $table;
//		$ourTables = array("{$_POST["tablePrefix"]}conversations", "{$_POST["tablePrefix"]}posts", "{$_POST["tablePrefix"]}status", "{$_POST["tablePrefix"]}members", "{$_POST["tablePrefix"]}tags");
//		$conflictingTables = array_intersect($ourTables, $theirTables);
//		if (count($conflictingTables)) {
//			$_POST["showAdvanced"] = true;
//			$errors["tablePrefix"] = "The installer has detected that there is another installation of the software in the same MySQL database with the same table prefix. The conflicting tables are: <code>" . implode(", ", $conflictingTables) . "</code>.<br/><br/>To overwrite this installation, click 'Next step' again. <strong>All data will be lost.</strong><br/><br/>If you wish to create another installation alongside the existing one, <strong>change the table prefix</strong>.<input type='hidden' name='confirmTablePrefix' value='{$_POST["tablePrefix"]}'/>";
//		}
//	}
	
	if (count($errors)) return $errors;
}

// Redirect to a specific step.
function step($step)
{
	//tba
	header("Location: install.php?step=$step");
	exit;
}

// Check for fatal errors.
function fatalChecks()
{
	$errors = array();
	
	// Make sure the installer is not locked.
	if (@$_GET["step"] != "finish" and file_exists("lock")) $errors[] = "<strong>Your forum is already installed.</strong><br/><small>To reinstall your forum, you must remove <strong>install/lock</strong>.</small>";
	
	// Check the PHP version.
	if (!version_compare(PHP_VERSION, "4.3.0", ">=")) $errors[] = "Your server must have <strong>PHP 4.3.0 or greater</strong> installed to run your forum.<br/><small>Please upgrade your PHP installation (preferably to version 5) or request that your host or administrator upgrade the server.</small>";
	
	// Check for the MySQLi extension.
	if (!extension_loaded("mysqli")) $errors[] = "You must have <strong>MySQL 5.7 or greater</strong> installed and the <a href='https://php.net/manual/en/mysqli.installation.php' target='_blank'>MySQLi extension enabled in PHP</a>.<br/><small>Please install/upgrade both of these requirements or request that your host or administrator install them.</small>";
	
	// Check file permissions.
//	$fileErrors = array();
//	$filesToCheck = array("", "avatars/", "plugins/", "skins/", "config/", "install/", "upgrade/");
//	foreach ($filesToCheck as $file) {
//		if ((!file_exists("../$file") and !@mkdir("../$file")) or (!is_writable("../$file") and !@chmod("../$file", 0777))) {
//			$realPath = realpath("../$file");
//			$fileErrors[] = $file ? $file : substr($realPath, strrpos($realPath, "/") + 1) . "/";
//		}
//	}
//	if (count($fileErrors)) $errors[] = "The following files/folders are not writeable: <strong>" . implode("</strong>, <strong>", $fileErrors) . "</strong>.<br/><small>To resolve this, you must navigate to these files/folders in your FTP client and <strong>chmod</strong> them to <strong>777</strong> or <strong>755</strong> (recommended).</small>";
	
	// Check for PCRE UTF-8 support.
	if (!@preg_match("//u", "")) $errors[] = "<strong>PCRE UTF-8 support</strong> is not enabled.<br/><small>Please ensure that your PHP installation has PCRE UTF-8 support compiled into it.</small>";
	
	// Check for the gd extension.
	if (!extension_loaded("gd") and !extension_loaded("gd2")) $errors[] = "The <strong>GD extension</strong> is not enabled.<br/><small>This is required to save avatars and generate captcha images. Get your host or administrator to install/enable it.</small>";
	
	if (count($errors)) return $errors;
}

// Perform checks which will throw a warning.
function warningChecks()
{
	$errors = array();
	
	// We don't like register_globals!
//	if (ini_get("register_globals")) $errors[] = "PHP's <strong>register_globals</strong> setting is enabled.<br/><small>While your forum can run with this setting on, it is recommended that it be turned off to increase security and to prevent your forum from having problems.</small>";
	
	// Can we open remote URLs as files?
//	if (!ini_get("allow_url_fopen")) $errors[] = "The PHP setting <strong>allow_url_fopen</strong> is not on.<br/><small>Without this, avatars cannot be uploaded directly from remote websites.</small>";
	
	// Check for safe_mode.
//	if (ini_get("safe_mode")) $errors[] = "<strong>Safe mode</strong> is enabled.<br/><small>This could potentially cause problems with your forum, but you can still proceed if you cannot turn it off.</small>";
	
	if (count($errors)) return $errors;
}

}

?>
