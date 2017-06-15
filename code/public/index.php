<?php

use Arc\ManyLinks\BaseModule;
use Slim\App;
use Slim\Container;

require '../vendor/autoload.php';

chdir(__DIR__);

$config = require_once '../config/conf.php';

$app = new App(new Container($config));

foreach ($config['modules'] as $moduleClass) {
    /** @var BaseModule $module */
    $module = new $moduleClass;
    $module->bootstrap($app);
}

$app->run();