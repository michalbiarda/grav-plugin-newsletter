<?php

namespace Grav\Plugin\Newsletter\SubscribeHandler;

use Grav\Common\Grav;
use Grav\Plugin\Newsletter\FileProcessorInterface;
use Grav\Plugin\Newsletter\SubscribeHandlerAbstract;
use Grav\Plugin\Newsletter\UnsubscribeHandlerInterface;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Local extends SubscribeHandlerAbstract implements UnsubscribeHandlerInterface
{
    const DEFAULT_FIELDS = ['email'];
    const DEFAULT_FILEPATH = 'newsletter' . DIRECTORY_SEPARATOR . 'subscriptions.csv';
    const DEFAULT_TYPE = 'csv';
    const DEFAULT_IDENTIFIER = 'email';

    public function subscribe(): void
    {
        $fields = $this->getFields();
        $identifier = $this->getIdentifier();
        $values = $this->container->getValuesHydrator()->getValues($this->form, $fields);
        $this->getFileProcessor()->upsert(
            $this->getFilepath(),
            array_merge($values, ['hash' => $this->container->getHasher()->hash($values[$identifier])]),
            $identifier
        );
    }

    public function unsubscribe(array $data): bool
    {
        return $this->getFileProcessor()->cut($this->getFilepath(), $data['hash']);
    }

    private function getFields(): array
    {
        return $this->container->getParamsProcessor()
            ->getStringsArrayParamValue('fields', self::DEFAULT_FIELDS, $this->params);
    }

    private function getIdentifier(): string
    {
        return $this->container->getParamsProcessor()
            ->getStringParamValue('identifier', self::DEFAULT_IDENTIFIER, $this->params);
    }

    private function getFilepath(): string
    {
        $filepath = $this->container->getParamsProcessor()
            ->getStringParamValue('filepath', self::DEFAULT_FILEPATH, $this->params);
        /** @var UniformResourceLocator $locator */
        $locator = Grav::instance()['locator'];
        return $locator->findResource('user://data', true) . DIRECTORY_SEPARATOR . $filepath;
    }

    private function getFileProcessor(): FileProcessorInterface
    {
        $type = $this->container->getParamsProcessor()
            ->getStringParamValue('type', self::DEFAULT_TYPE, $this->params);
        return $this->container->getFileProcessorFactory()->create($type);
    }
}