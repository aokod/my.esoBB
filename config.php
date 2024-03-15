<?php
// GLOBAL CONSTANTS FOR INSTALLER
if (!defined("IN_ESO")) exit;

// The version of the code.
if (!defined("MYESOBB_VERSION")) define("MYESOBB_VERSION", "1.0.0-pre1");
// The root domain name and web directory.
if (!defined("MYESOBB_DOMAIN")) define("MYESOBB_DOMAIN", "myeso.org");
if (!defined("MYESOBB_WEBROOT")) define("MYESOBB_WEBROOT", "/var/www/");
// The name of the template forum (must be located in the webroot, e.g. /var/www/myeso_template).
if (!defined("MYESOBB_TEMPLATE")) define("MYESOBB_TEMPLATE", "myeso_template");

// The prefix applied before the folders in which individual forums are hosted.
if (!defined("MYESOBB_FORUM_PREFIX")) define("MYESOBB_FORUM_PREFIX", "myeso_forum_");
// The prefix applied before the MySQL user assigned to the database of each forum.
if (!defined("MYESOBB_FORUM_USER_PREFIX")) define("MYESOBB_FORUM_USER_PREFIX", "myeso_user_");
// The prefix applied before the database name of each forum.
if (!defined("MYESOBB_FORUM_DB_PREFIX")) define("MYESOBB_FORUM_DB_PREFIX", "myeso_db_");

// MySQL credentials.
if (!defined("MYESOBB_SQL_HOST")) define("MYESOBB_SQL_HOST", "localhost");
if (!defined("MYESOBB_SQL_USER")) define("MYESOBB_SQL_USER", "myeso_createdb");
if (!defined("MYESOBB_SQL_PASS")) define("MYESOBB_SQL_PASS", "");
// This is the database used to keep a log of installed forums.
// to be added
if (!defined("MYESOBB_SQL_DB")) define("MYESOBB_SQL_DB", "");

// Default settings.
if (!defined("MYESOBB_SQL_PREFIX")) define("MYESOBB_SQL_PREFIX", "et_");
if (!defined("MYESOBB_SQL_ENCODING")) define("MYESOBB_SQL_ENCODING", "utf8mb4");
if (!defined("MYESOBB_SQL_ENGINE")) define("MYESOBB_SQL_ENGINE", "InnoDB");
if (!defined("MYESOBB_HASH_METHOD")) define("MYESOBB_HASH_METHOD", "bcrypt");

?>
