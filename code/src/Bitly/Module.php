<?php

namespace Arc\ManyLinks\Bitly;

use Arc\ManyLinks\BaseModule;
use Arc\ManyLinks\Link\Service as LinkService;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Slim\App;

class Module extends BaseModule
{
    protected function configureContainer(ContainerInterface $container)
    {
        $container[Service::class] = function (ContainerInterface $container) {
            $bitlyConfig = $container->get('bitly');
            return new Service(
                new Client(['base_uri' => Service::URL_API]),
                $bitlyConfig['clientId'],
                $bitlyConfig['clientSecret'],
                $container->get('db'),
                $container->get(LinkService::class)
            );
        };
    }

    protected function addRoutes(App $app)
    {
        // NOOP
    }

    protected function addMiddlewares(App $app)
    {
        // NOOP
    }
}