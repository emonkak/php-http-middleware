<?php

namespace Emonkak\HttpMiddleware;

use Emonkak\HttpException\MethodNotAllowedHttpException;
use Emonkak\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements MiddlewareInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param RouterInterface    $router
     * @param ContainerInterface $container
     */
    public function __construct(
        RouterInterface $router,
        ContainerInterface $container
    ) {
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $match = $this->router->match($request->getUri()->getPath());
        if ($match === null) {
            return $handler->handle($request);
        }

        list ($handlers, $params) = $match;
        $method = strtoupper($request->getMethod());

        if (!isset($handlers[$method])) {
            throw new MethodNotAllowedHttpException(array_keys($handlers));
        }

        foreach ($params as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $handlerReference = $handlers[$method];

        if (is_array($handlerReference)) {
            list ($class, $method) = $handlerReference;

            $instance = $this->container->get($class);

            return $instance->$method($request);
        } else {
            $handler = $this->container->get($handlerReference);

            return $handler->handle($request);
        }
    }
}
