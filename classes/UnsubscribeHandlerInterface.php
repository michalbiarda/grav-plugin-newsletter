<?php

namespace Grav\Plugin\Newsletter;

interface UnsubscribeHandlerInterface
{
    public function unsubscribe(array $data): bool;
}