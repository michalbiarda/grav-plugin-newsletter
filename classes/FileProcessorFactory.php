<?php

namespace Grav\Plugin\Newsletter;

class FileProcessorFactory
{
    /** @var Container  */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $type): FileProcessorInterface
    {
        $className = $this->container->getConfig()->value('plugins.newsletter.subscribe.fileProcessors.' . $type);
        if (!$className) {
            throw new \InvalidArgumentException(sprintf('File processor "%s" is not defined in config.', $type));
        }
        return $this->container->getObjectCreator()->create($className, [$this->container]);
    }
}