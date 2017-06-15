<?php

namespace Arc\ManyLinks\Link\Middleware;

use Arc\ManyLinks\Link\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;

class ValidatesLinkData
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->validator = new Validator();

        $this->validator->context('create', function (Validator $validator) {
            $validator->required('urls')->urlList();
        });

        $this->validator->context('update', function (Validator $validator) {
            $validator->required('id')->string()->alnum()->length(24);
            $validator->required('urls')->urlList();
        });
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        $params = $request->getParsedBody();

        $context = $request->getUri()->getPath() === $this->router->pathFor('create-link') ? 'create' : 'update';

        $result = $this->validator->validate($params, $context);

        if (!$result->isValid()) {
            $dashboardUrl = sprintf(
                '%s://%s%s',
                $request->getUri()->getScheme(),
                $request->getUri()->getHost(),
                $this->router->pathFor('dashboard')
            );
            return $response->withStatus(302)->withHeader('Location', $dashboardUrl);
        }

        $validParams = $result->getValues();
        $validParams['urls'] = explode("\n", str_replace("\r", '', $validParams['urls']));

        $request = $request->withAttribute('link-data', $validParams);

        return $next($request, $response);
    }
}