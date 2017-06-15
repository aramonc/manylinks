<?php

namespace Arc\ManyLinks\Authentication\Action;

use Arc\ManyLinks\Bitly\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;
use Zend\Session\Container;
use function GuzzleHttp\Psr7\parse_query;

class Login
{
    /**
     * @var Twig
     */
    private $view;

    /**
     * @var Service
     */
    private $bitly;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Container
     */
    private $session;

    public function __construct(Twig $view, Service $bitly, RouterInterface $router, Container $session)
    {
        $this->view = $view;
        $this->bitly = $bitly;
        $this->router = $router;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (isset($this->session['user'])) {
            $dashboardUrl = sprintf(
                '%s://%s%s',
                $request->getUri()->getScheme(),
                $request->getUri()->getHost(),
                $this->router->pathFor('dashboard')
            );

            return $response->withStatus(302)->withHeader('Location', $dashboardUrl);
        }

        $redirectUrl = sprintf(
            '%s://%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $this->router->pathFor('authenticate')
        );

        $queryParams = parse_query($request->getUri()->getQuery());
        $redirectPath = $queryParams['redirect'] ?? '';

        $state = [
            'redirect_path' => $redirectPath
        ];

        return $this->view->render(
            $response,
            '@authentication/login.html.twig',
            ['bitlyUrl' => $this->bitly->buildAuthUrl($redirectUrl, $state)]
        );
    }
}