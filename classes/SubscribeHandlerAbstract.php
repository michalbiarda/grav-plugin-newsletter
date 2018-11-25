<?php

namespace Grav\Plugin\Newsletter;

use Grav\Plugin\Form\Form;

abstract class SubscribeHandlerAbstract implements SubscribeHandlerInterface
{
    /** @var Container */
    protected $container;

    /** @var Form */
    protected $form;

    /** @var array */
    protected $params;

    public function __construct(Container $container, Form $form, $params = [])
    {
        $this->container = $container;
        $this->form = $form;
        $this->params = $params;
    }
}