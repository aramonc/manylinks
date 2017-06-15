<?php

use MongoDB\Client;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SaveHandler\MongoDB;
use Zend\Session\SaveHandler\MongoDBOptions;
use Zend\Session\SessionManager;

$env = new \Dotenv\Dotenv(__DIR__);
$env->load();
$env->required('DB_HOST')->notEmpty();
$env->required('DB_PORT')->isInteger();

return [
    'view' => function (ContainerInterface $container) {

        $options = [];

        $enableCache = getenv('VIEW_CACHE_ENABLE') === 'true' ? true : false;

        if ($enableCache) {
            $options['cache'] = '../cache/views';
        }
        $view = new Twig(['app' => '../resources/layouts'], $options);

        // Instantiate and add Slim specific extension
        $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
        $view->addExtension(new TwigExtension($container->get('router'), $basePath));

        return $view;
    },
    'db' => function (ContainerInterface $container) {
        $template = '%s:%s';
        $vars = [];

        if ($username = getenv('DB_USERNAME')) {
            $template = '%s:%s@' . $template;
            $vars[] = $username;
            $vars[] = getenv('DB_PASSWORD');
        }

        $vars[] = getenv('DB_HOST');
        $vars[] = getenv('DB_PORT');

        $host = vsprintf('mongodb://' . $template, $vars);

        return new Client($host);
    },
    'modules' => require_once 'modules.conf.php',
    'bitly' => require_once 'bitly.conf.php',
    'session_config' => require_once 'session.conf.php',

    // SERVICES
    SessionManager::class => function (ContainerInterface $container) {
        $config = $container->get('session_config');

        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['config'] ?? []);

        $sessionStorage = null;
        if (!empty($config['storage'])) {
            $sessionStorage = new $config['storage']();
        }

        $mongoDbOptions = new MongoDBOptions([
            'database' => 'many-links',
            'collection' => 'sessions',
        ]);

        $saveHandler = new MongoDB($container->get('db'), $mongoDbOptions);

        return new SessionManager($sessionConfig, $sessionStorage, $saveHandler);
    }
];