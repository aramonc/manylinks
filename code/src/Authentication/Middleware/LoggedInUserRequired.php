<?php

namespace Arc\ManyLinks\Authentication\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;
use Zend\Session\Container;

class LoggedInUserRequired
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

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        if (!isset($this->session['user'])) {
            $redirectPath = $request->getUri()->getPath();
            if (!empty($request->getUri()->getQuery())) {
                $redirectPath .= '?' . $request->getUri()->getQuery();
            }

            $loginUrl = $redirectUrl = sprintf(
                '%s://%s%s?%s',
                $request->getUri()->getScheme(),
                $request->getUri()->getHost(),
                $this->router->pathFor('login'),
                http_build_query(['redirect' => $redirectPath])
            );

            return $response->withStatus(302)->withHeader('Location', $loginUrl);
        }

        return $next($request, $response);
    }
}