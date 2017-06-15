<?php

namespace Arc\ManyLinks\Link\Action;

use Arc\ManyLinks\Bitly\Service as UserService;
use Arc\ManyLinks\Bitly\User;
use Arc\ManyLinks\Link\Link;
use Arc\ManyLinks\Link\Service as LinkService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;
use Zend\Session\Container;

class CreateLink
{
    /**
     * @var LinkService
     */
    private $linkService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Container
     */
    private $session;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        LinkService $linkService,
        UserService $userService,
        Container $session,
        RouterInterface $router
    ) {
        $this->linkService = $linkService;
        $this->userService = $userService;
        $this->session = $session;
        $this->router = $router;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var User $user */
        $user = $this->session['user'];

        $linkData = $request->getAttribute('link-data');

        $link = new Link();
        $link->setUrls($linkData['urls']);

        $this->linkService->save($link);

        $expandUrl = sprintf(
            '%s://%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $this->router->pathFor('expand-link', ['linkId' => $link->getId()])
        );

        $shortUrl = $this->userService->getShortUrl($expandUrl, $user);

        $link->setBitly($shortUrl);

        $this->linkService->save($link);

        $user->addLink($link);

        $this->userService->saveUser($user);

        return $response->withStatus(302)->withHeader('Location', $this->router->pathFor('dashboard'));
    }
}