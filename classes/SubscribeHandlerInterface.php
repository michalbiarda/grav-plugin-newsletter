<?php

namespace Grav\Plugin\Newsletter;

interface SubscribeHandlerInterface
{
    public function subscribe(): void;
}