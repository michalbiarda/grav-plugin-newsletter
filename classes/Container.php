<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Config\Config;

class Container extends \Pimple\Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['objectCreator'] = function() {
            return new ObjectCreator();
        };
        $this['formProcessor'] = function() {
            return new FormProcessor($this);
        };
        $this['subscribeHandlerFactory'] = function() {
            return new SubscribeHandlerFactory($this);
        };
        $this['paramsProcessor'] = function() {
            return new ParamsProcessor();
        };
        $this['valuesHydrator'] = function() {
            return new ValuesHydrator();
        };
        $this['fileProcessorFactory'] = function() {
            return new FileProcessorFactory($this);
        };
        $this['hasher'] = function() {
            return new Hasher($this);
        };
    }

    public function getObjectCreator(): ObjectCreator
    {
        return $this['objectCreator'];
    }

    public function getFormProcessor(): FormProcessor
    {
        return $this['formProcessor'];
    }

    public function getSubscribeHandlerFactory(): SubscribeHandlerFactory
    {
        return $this['subscribeHandlerFactory'];
    }

    public function getConfig(): Config
    {
        return $this['config'];
    }

    public function getParamsProcessor(): ParamsProcessor
    {
        return $this['paramsProcessor'];
    }

    public function getValuesHydrator(): ValuesHydrator
    {
        return $this['valuesHydrator'];
    }

    public function getFileProcessorFactory(): FileProcessorFactory
    {
        return $this['fileProcessorFactory'];
    }

    public function getHasher(): Hasher
    {
        return $this['hasher'];
    }
}