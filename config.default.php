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
 * Default configuration: don't edit this.  If you wish to change a
 * config setting, copy it into config/config.php and change it there.
 */

// The version of the code.
define("MYESOBB_VERSION", "1.0.0-pre1");

$defaultConfig = array(
// This following block is filled out by the installer in config/config.php.
"mysqlHost" => "localhost",
"mysqlUser" => "",
"mysqlPass" => "",
"mysqlDB" => "",
"tablePrefix" => "et_",
"characterEncoding" => "utf8mb4",
"connectionOptions" => "",
"storageEngine" => "InnoDB",
"hashingMethod" => "bcrypt",
);

?>
