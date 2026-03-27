<?php

namespace FLEA\Middleware;

class Pipeline
{
    /** @var MiddlewareInterface[] */
    private array $middlewares = [];

    public static function create(): self
    {
        return new self();
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function run(callable $destination): void
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn(callable $carry, MiddlewareInterface $mw) => fn() => $mw->handle($carry),
            $destination
        );

        $pipeline();
    }
}
