<?php
require_once 'vendor/autoload.php';

use Adam\AwPhpProject\App;
use Adam\AwPhpProject\SessionManager;

$app = new App();

SessionManager::kill();

header("location: /");
?>