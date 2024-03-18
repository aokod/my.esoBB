<?php
// GLOBAL CONSTANTS FOR INSTALLER
if (!defined("IN_ESO")) exit;

define("MYESOBB_VERSION", "1.0.0-pre1");

$config = array(
// This following block is filled out by the installer in config/config.php.
"domain" => "myeso.org",
"webroot" => "/var/www/",
"baseURL" => "https://myeso.org/",
"cookieName" => "myeso",
"templateFolder" => "myeso_template",
"forumFolderPrefix" => "myeso_forum_", // The prefix applied before the folders in which individual forums are hosted.
"forumUserPrefix" => "myeso_user_", // The prefix applied before the MySQL user assigned to the database of each forum.
"forumDbPrefix" => "myeso_db_", // The prefix applied before the database name of each forum.
// MySQL credentials.
"mysqlHost" => "localhost",
"mysqlUser" => "myeso_createdb",
"mysqlPass" => "",
"mysqlDb" => "myeso_forums", // This is the database used to keep track of installed forums.
// Default settings.
"mysqlPrefix" => "et_",
"mysqlEncoding" => "utf8mb4",
"mysqlEngine" => "InnoDB",
"hashingMethod" => "bcrypt",
// Cloudflare Turnstile captcha key/secret.
"captchaKey" => "0x4AAAAAAAU4XRfztUUFOaxR",
"captchaSecret" => "0x4AAAAAAAU4XTEL78bI_ZSWQBaLV94sJBY",
// For troubleshooting.
"verboseFatalErrors" => "true",
);

?>
