<?php

namespace Arc\ManyLinks\Dashboard\Action;

use Arc\ManyLinks\Bitly\User;
use Arc\ManyLinks\Link\Service;
use MongoDB\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Zend\Session\Container;

class Dashboard
{
    /**
     * @var Twig
     */
    private $view;

    /**
     * @var Client
     */
    private $db;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Service
     */
    private $linkService;

    public function __construct(Twig $view, Client $db, Container $session, Service $linkService)
    {
        $this->view = $view;
        $this->db = $db;
        $this->user = $session['user'];
        $this->linkService = $linkService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $links = $this->linkService->getLinks($this->user->getLinks());
        $this->view->render($response, '@dashboard/dashboard.html.twig', ['links' => $links]);
    }
}