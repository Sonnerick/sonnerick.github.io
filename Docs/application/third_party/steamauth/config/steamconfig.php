<?php
/*
 * [2019-01-25]
 * Attempt to adapt OpenID Steam Auth to CondIgniter
*/

defined('BASEPATH') OR exit('No direct script access allowed');

$config['apikey'] = "CA0CFBD975B78008FEE96657B7D4A6A6"; // Your Steam WebAPI-Key found at https://steamcommunity.com/dev/apikey
$config['domainname'] = "http://stin.loc"; // The main URL of your website displayed in the login page
$config['logoutpage'] = "home.php"; // Page to redirect to after a successful logout (from the directory the config-folder is located in) - NO slash at the beginning!
$config['loginpage'] = "home.php"; // Page to redirect to after a successful login (from the directory the config-folder is located in) - NO slash at the beginning!

 //System stuff
if (empty($config['apikey'])) {die("<div style='display: block; width: 100%; background-color: red; text-align: center;'>config:<br>Please supply an API-Key!<br>Find this in config/SteamConfig.php, Find the '<b>\$config['apikey']</b>' Array. </div>");}
if (empty($config['domainname'])) {$config['domainname'] = $_SERVER['SERVER_NAME'];}
if (empty($config['logoutpage'])) {$config['logoutpage'] = $_SERVER['PHP_SELF'];}
if (empty($config['loginpage'])) {$config['loginpage'] = $_SERVER['PHP_SELF'];}

?>
