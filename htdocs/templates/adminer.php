<?php
class AdminerAutoLogin
{
    public static $instance;

    function name()
    {
        $adminer = self::$instance;
        require 'adminer-auth.php';
        return "TWAdminer";
    }
    
    function getCfg($name) {
        $cfg = \TWLan\TWLan::$config;
        return $cfg->getVal($name);
    }
    
    function getServer() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $server = ".";
        } else {
            $server = "127.0.0.1";
        };
        return $server.':'.\TWLan\TWLan::getMySQLSocket();
    }
    
    function getUsername()
    {
        return $this->getCfg("db.user");
    }
    
    function getPassword()
    {
        return $this->getCfg("db.password");
    }
    
    function getDatabase()
    {
        return $this->getCfg("db.database");
    }
    
    function credentials()
    {
        return array($this->getServer(), $this->getUsername(), $this->getPassword());
    }
}
$plugin = new AdminerAutoLogin();
$_GET['server'] = $plugin->getServer();
$_GET['db'] = $plugin->getDatabase();
$_GET['username'] = $plugin->getUsername();

function adminer_object()
{
    global $plugin;
    require_once 'adminer-plugin.php';
    $plugins = [$plugin];
    AdminerAutoLogin::$instance = new AdminerPlugin($plugins);
    return AdminerAutoLogin::$instance;
}
require 'adminer-4.2.1.php';
