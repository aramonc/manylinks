<?php

namespace Arc\ManyLinks;

use Psr\Container\ContainerInterface;
use Slim\App;

abstract class BaseModule
{
    public function bootstrap(App $app)
    {
        $this->configureContainer($app->getContainer());
        $this->addMiddlewares($app);
        $this->addRoutes($app);
    }

    abstract protected function configureContainer(ContainerInterface $container);

    abstract protected function addRoutes(App $app);

    abstract protected function addMiddlewares(App $app);
}