<?php

namespace Grav\Plugin\Newsletter;

use Grav\Plugin\Form\Form;

class SubscribeHandlerFactory
{
    /** @var Container  */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $handler, Form $form, $params = []): SubscribeHandlerInterface
    {
        $className = $this->container->getConfig()->value('plugins.newsletter.subscribe.handlers.' . $handler);
        if (!$className) {
            throw new \InvalidArgumentException(sprintf('Handler "%s" is not defined in config.', $handler));
        }
        return $this->container->getObjectCreator()->create($className, [$this->container, $form, $params]);
    }
}