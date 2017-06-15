<?php

namespace Arc\ManyLinks\Authentication\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;
use Zend\Session\Container;

class Logout
{
    /**
     * @var Container
     */
    private $session;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(Container $session, RouterInterface $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        unset($this->session['user']);

        $loginUrl = sprintf(
            '%s://%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $this->router->pathFor('login')
        );

        return $response->withStatus(302)->withHeader('Location', $loginUrl);
    }
}