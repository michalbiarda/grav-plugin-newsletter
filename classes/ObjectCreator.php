<?php

namespace Grav\Plugin\Newsletter;

class ObjectCreator
{
    public function create(string $className, array $params = [])
    {
        return new $className(...$params);
    }
}