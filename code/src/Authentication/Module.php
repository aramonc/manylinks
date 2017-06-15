<?php

namespace Arc\ManyLinks\Authentication;

use Arc\ManyLinks\Authentication\Action\Authenticate;
use Arc\ManyLinks\Authentication\Action\Login;
use Arc\ManyLinks\Authentication\Action\Logout;
use Arc\ManyLinks\Authentication\Middleware\LoggedInUserRequired;
use Arc\ManyLinks\BaseModule;
use Arc\ManyLinks\Bitly\Service;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RKA\Middleware\IpAddress;
use Slim\App;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Validator;

class Module extends BaseModule
{
    protected function configureContainer(ContainerInterface $container)
    {
        // Add template paths to the view
        /** @var \Twig_Loader_Filesystem $loader */
        $loader = $container->get('view')->getLoader();
        $loader->addPath('../src/Authentication/templates/', 'authentication');

        // Add action factories
        $container[Login::class] = function (ContainerInterface $container) {
            return new Login(
                $container->get('view'),
                $container->get(Service::class),
                $container->get('router'),
                $container->get('user_session')
            );
        };
        $container[Authenticate::class] = function (ContainerInterface $container) {
            return new Authenticate(
                $container->get('router'),
                $container->get(Service::class),
                $container->get('user_session')
            );
        };
        $container[Logout::class] = function (ContainerInterface $container) {
            return new Logout(
                $container->get('user_session'),
                $container->get('router')
            );
        };

        // Add Middleware factories
        $container[LoggedInUserRequired::class] = function (ContainerInterface $container) {
            return new LoggedInUserRequired($container->get('user_session'), $container->get('router'));
        };

        // Add service factories
        $container['user_session'] = function (ContainerInterface $container) {
            return $this->buildUserSessionContainer(
                $container->get(SessionManager::class),
                $container->get('session_config'),
                $container->get('request')
            );
        };
    }

    protected function addRoutes(App $app)
    {
        $app->get('/login', Login::class)->setName('login');
        $app->get('/authenticate', Authenticate::class)->setName('authenticate');
        $app->get('/logout', Logout::class)
            ->setName('logout')
            ->add($app->getContainer()->get(LoggedInUserRequired::class));
    }

    protected function buildUserSessionContainer(
        SessionManager $manager,
        array $config,
        ServerRequestInterface $request
    ): Container {
        $container = new Container('ml_user', $manager);

        if (isset($container->init)) {
            return $container;
        }

        $manager->regenerateId(true);
        $container->init = 1;
        $container['remote_address'] = $request->getAttribute('remote_address');
        $container['user_agent'] = $request->getHeader('User-Agent')[0] ?? 'unknown';

        if (empty($config['manager']) || empty($container['manager']['validators'])) {
            return $container;
        }

        $chain = $manager->getValidatorChain();

        foreach ($config['manager']['validators'] as $validator) {
            switch ($validator) {
                case Validator\HttpUserAgent::class:
                    $validator = new $validator($container['user_agent']);
                    break;
                case Validator\RemoteAddr::class:
                    $validator = new $validator($container['remote_address']);
                    break;
                default:
                    $validator = new $validator();
            }

            $chain->attach('session.validate', array($validator, 'isValid'));
        }

        return $container;
    }

    protected function addMiddlewares(App $app)
    {
        $app->add(new IpAddress(true, [], 'remote_address'));
    }
}