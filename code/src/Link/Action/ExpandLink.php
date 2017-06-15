<?php

namespace Arc\ManyLinks\Link\Action;

use Arc\ManyLinks\Link\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class ExpandLink
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var Twig
     */
    private $view;

    public function __construct(Service $service, Twig $view)
    {
        $this->service = $service;
        $this->view = $view;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $link = $this->service->getLink($args['linkId'] ?? '');

        return $this->view->render(
            $response,
            '@link/expand-link.html.twig',
            ['urls' => $link->getUrls()]
        );
    }
}