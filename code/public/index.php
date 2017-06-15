<?php

use Arc\ManyLinks\BaseModule;
use Slim\App;
use Slim\Container;

require '../vendor/autoload.php';

chdir(__DIR__);

if (getenv('APP_ENV') !== 'prod') {
    $env = new \Dotenv\Dotenv(__DIR__ . '/../config/');
    $env->load();
    $env->required('MONGODB_URI')->notEmpty();
}

$config = require_once '../config/conf.php';

$app = new App(new Container($config));

foreach ($config['modules'] as $moduleClass) {
    /** @var BaseModule $module */
    $module = new $moduleClass;
    $module->bootstrap($app);
}

$app->run();