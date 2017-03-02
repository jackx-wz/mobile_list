<?php
define('APP_PATH', realpath(dirname(__DIR__)));
define('FILES_PATH', APP_PATH.'/public/apps');

$app = new Yaf_Application(APP_PATH.'/conf/application.ini');
$app->run();
