<?php

namespace Arc\ManyLinks\Dashboard;

use Arc\ManyLinks\Authentication\Middleware\LoggedInUserRequired;
use Arc\ManyLinks\BaseModule;
use Arc\ManyLinks\Dashboard\Action\Dashboard;
use Arc\ManyLinks\Link\Service;
use Psr\Container\ContainerInterface;
use Slim\App;

/**
 * Dashboard Module
 * @package Arc\ManyLinks\Dashboard
 * @codeCoverageIgnore
 */
class Module extends BaseModule
{
    protected function configureContainer(ContainerInterface $container)
    {
        // Add template paths to the view
        /** @var \Twig_Loader_Filesystem $loader */
        $loader = $container->get('view')->getLoader();
        $loader->addPath('../src/Dashboard/templates/', 'dashboard');

        // Add action factories
        $container[Dashboard::class] = function (ContainerInterface $container) {
            return new Dashboard(
                $container->get('view'),
                $container->get('db'),
                $container->get('user_session'),
                $container->get(Service::class)
            );
        };
    }

    protected function addRoutes(App $app)
    {
        $app->get('/', Dashboard::class)->setName('dashboard')->add(LoggedInUserRequired::class);
    }

    protected function addMiddlewares(App $app)
    {
        // NOOP
    }
}