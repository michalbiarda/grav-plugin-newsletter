<?php

namespace Grav\Plugin\Newsletter\Controller;

use Grav\Plugin\Newsletter\Container;

abstract class AbstractController
{
    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function execute(string $action): void
    {
        $method = $action . 'Action';
        if (!method_exists($this, $method)) {
            throw new \RuntimeException('Page Not Found', 404);
        }
        call_user_func([$this, $method]);
    }
}