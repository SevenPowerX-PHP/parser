<?php
/**
 * Created by PhpStorm.
 * User: serik_lav
 * Date: 14.11.2018
 * Time: 5:50 PM
 */

// HTTP
define('HTTP_SERVER', 'http://hendi.horeca-partner.loc/');

// HTTPS
define('HTTPS_SERVER', 'https://hendi.horeca-partner.loc/');

// DIR
define('DIR_PATH', '/Volumes/Data/www/hendi.horeca-partner.loc/');
define('DIR_SYSTEM', DIR_PATH . 'system/');
define('DIR_MODEL', DIR_PATH . 'model/');
define('DIR_DOWNLOAD', DIR_SYSTEM . 'download/');

// LOGS
define('DIR_LOGS', DIR_SYSTEM . 'logs/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'horeca_partner');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// Parser
define('PARSE_URL', 'http://www.hendi.pl/site_map');
define('PARSE_SITE', 'http://www.hendi.pl/');


// Connect Driver
require_once (DIR_SYSTEM . "library/db/mysql.php");
require_once (DIR_SYSTEM . "library/db/mysqli.php");

// Connect to DB
require_once (DIR_SYSTEM . "library/db.php");

// Log
require_once (DIR_SYSTEM . "library/log.php");
$log = new Log('parser_hendi.log');


// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);