<?php

namespace Emonkak\HttpMiddleware\Internal;

use Emonkak\HttpException\NotFoundHttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 */
class Pipeline implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->index >= count($this->middlewares)) {
            throw new NotFoundHttpException('No middleware available for processing');
        }

        $middleware = $this->middlewares[$this->index++];

        return $middleware->process($request, $this);
    }
}
