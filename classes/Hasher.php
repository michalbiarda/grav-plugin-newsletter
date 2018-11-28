<?php

namespace Grav\Plugin\Newsletter;

class Hasher
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function hash(string $string): string
    {
        return md5($string . $this->container->getConfig()->value('security.salt'));
    }
}