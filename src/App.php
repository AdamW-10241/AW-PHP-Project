<?php
namespace Adam\AwPhpProject;

use Dotenv\Dotenv;
use \Exception;
use Adam\AwPhpProject\SessionManager;

class App {
    protected $config;
    public $site_name;

    public function __construct()
    {
        // Class constructorr
        $this -> loadConfig();
    }

    private function loadConfig()
    {
        try {
            $app_dir = getcwd();
            $dotenv = Dotenv::createImmutable($app_dir);
            $dotenv->load();
            $this -> site_name = $_ENV['SITENAME'];
            date_default_timezone_set( $_ENV['TIMEZONE'] );
        }
        catch (Exception $exception) {
            $msg = $exception -> getMessage();
            exit($msg);
        }
    }
}
?>