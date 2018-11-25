<?php

namespace Grav\Plugin\Newsletter\SubscribeHandler;

use Grav\Common\Grav;
use Grav\Plugin\Newsletter\SubscribeHandlerAbstract;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Local extends SubscribeHandlerAbstract
{
    const DEFAULT_FIELDS = ['email'];
    const DEFAULT_FILEPATH = 'newsletter' . DIRECTORY_SEPARATOR . 'subscriptions.csv';
    const DEFAULT_TYPE = 'csv';
    const DEFAULT_IDENTIFIER = 'email';

    public function run(): void
    {
        $paramsProcessor = $this->container->getParamsProcessor();
        $fields = $paramsProcessor
            ->getStringsArrayParamValue('fields', self::DEFAULT_FIELDS, $this->params);
        $identifier = $paramsProcessor
            ->getStringParamValue('identifier', self::DEFAULT_IDENTIFIER, $this->params);
        $filepath = $paramsProcessor
            ->getStringParamValue('filepath', self::DEFAULT_FILEPATH, $this->params);
        $type = $paramsProcessor
            ->getStringParamValue('type', self::DEFAULT_TYPE, $this->params);
        $values = $this->container->getValuesHydrator()->getValues($this->form, $fields);
        $fileProcessor = $this->container->getFileProcessorFactory()->create($type);
        /** @var UniformResourceLocator $locator */
        $locator = Grav::instance()['locator'];
        $fileProcessor->upsert(
            $locator->findResource('user://data', true) . DIRECTORY_SEPARATOR . $filepath,
            $values,
            $identifier
        );
    }
}