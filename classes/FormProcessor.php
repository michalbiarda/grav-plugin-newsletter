<?php

namespace Grav\Plugin\Newsletter;

use Grav\Plugin\Form\Form;

class FormProcessor
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function process(Form $form, array $params = [])
    {
        if (empty($params['handlers'])) {
            $params['handlers'] = ['local' => []];
        }
        foreach ($params['handlers'] as $handler => $data) {
            $handler = $this->container->getSubscribeHandlerFactory()->create($handler, $form, $data);
            $handler->run();
        }
    }
}