<?php
/**
 * TWLan
 * Simulate a login
 */
$has_token = $_SESSION["token"];
if (!$has_token) {
	$_SESSION["token"] = rand(1, 1e6); // defense against cross-site request forgery
}
$token = get_token(); ///< @var string CSRF protection

$vendor = 'server';
$server = $this->getServer();
$username = $this->getUsername();
$password = $this->getPassword();
$db = $this->getDatabase();
set_password($vendor, $server, $username, $password);
$_SESSION["db"][$vendor][$server][$username][$db] = true;
