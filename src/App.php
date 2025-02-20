<?php
namespace Adam\AwPhpProject;

use Dotenv\Dotenv;
use \Exception;

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
            $this -> site_name = $_ENV['SITE_NAME'];
        }
        catch (Exception $exception) {
            $msg = $exception -> getMessage();
            exit($msg);
        }
    }
}
?>