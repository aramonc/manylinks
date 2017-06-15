<?php

namespace Arc\ManyLinks\Link;

use Arc\ManyLinks\Authentication\Middleware\LoggedInUserRequired;
use Arc\ManyLinks\BaseModule;
use Arc\ManyLinks\Bitly\Service as UserService;
use Arc\ManyLinks\Link\Action\CreateLink;
use Arc\ManyLinks\Link\Action\ExpandLink;
use Arc\ManyLinks\Link\Middleware\ValidatesLinkData;
use Arc\ManyLinks\Link\Service as LinkService;
use Psr\Container\ContainerInterface;
use Slim\App;

class Module extends BaseModule
{

    protected function configureContainer(ContainerInterface $container)
    {
        // VIEW LOADER
        /** @var \Twig_Loader_Filesystem $loader */
        $loader = $container->get('view')->getLoader();
        $loader->addPath('../src/Link/templates/', 'link');

        // SERVICES
        $container[LinkService::class] = function (ContainerInterface $container) {
            return new LinkService($container->get('db'));
        };

        // MIDDLEWARE
        $container[ValidatesLinkData::class] = function (ContainerInterface $container) {
            return new ValidatesLinkData($container->get('router'));
        };

        // ACTIONS
        $container[CreateLink::class] = function (ContainerInterface $container) {
            return new CreateLink(
                $container->get(LinkService::class),
                $container->get(UserService::class),
                $container->get('user_session'),
                $container->get('router')
            );
        };
        $container[ExpandLink::class] = function (ContainerInterface $container) {
            return new ExpandLink($container->get(LinkService::class), $container->get('view'));
        };
    }

    protected function addRoutes(App $app)
    {
        $app->post('/link', CreateLink::class)
            ->setName('create-link')
            ->add(LoggedInUserRequired::class)
            ->add(ValidatesLinkData::class);
        $app->get('/link/{linkId}', ExpandLink::class)
            ->setName('expand-link');
    }

    protected function addMiddlewares(App $app)
    {
        // NOOP
    }
}