<?php

namespace Arc\ManyLinks\Authentication\Action;

use Arc\ManyLinks\Bitly\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;
use Zend\Session\Container;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\Psr7\parse_query;

class Authenticate
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Service
     */
    private $bitly;

    /**
     * @var Container
     */
    private $session;

    public function __construct(RouterInterface $router, Service $bitly, Container $session)
    {
        $this->router = $router;
        $this->bitly = $bitly;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getUri()->getQuery();
        $params = parse_query($query);
        $redirectUrl = sprintf(
            '%s://%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $this->router->pathFor('authenticate')
        );

        if (empty($params['code'])) {
            $loginUrl = sprintf(
                '%s://%s%s',
                $request->getUri()->getScheme(),
                $request->getUri()->getHost(),
                $this->router->pathFor('login')
            );

            return $response->withStatus(302)->withHeader('Location', $loginUrl);
        }

        $user = $this->bitly->getTokenWithCode($params['code'], $redirectUrl);

        $this->session['user'] = $user;

        $state = [];
        if (isset($params['state'])) {
            $state = json_decode($params['state'], true);

        }

        $redirectPath = $state['redirect_path'] ?? $this->router->pathFor('dashboard');

        $redirectUrl = $redirectUrl = sprintf(
            '%s://%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $redirectPath
        );

        return $response->withStatus(302)->withAddedHeader('Location', $redirectUrl);
    }
}