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
    }

    /**
     * @return ObjectCreator
     */
    public function getObjectCreator()
    {
        return $this['objectCreator'];
    }

    /**
     * @return FormProcessor
     */
    public function getFormProcessor()
    {
        return $this['formProcessor'];
    }

    /**
     * @return SubscribeHandlerFactory
     */
    public function getSubscribeHandlerFactory()
    {
        return $this['subscribeHandlerFactory'];
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this['config'];
    }

    /**
     * @return ParamsProcessor
     */
    public function getParamsProcessor()
    {
        return $this['paramsProcessor'];
    }

    /**
     * @return ValuesHydrator
     */
    public function getValuesHydrator()
    {
        return $this['valuesHydrator'];
    }

    /**
     * @return FileProcessorFactory
     */
    public function getFileProcessorFactory()
    {
        return $this['fileProcessorFactory'];
    }
}