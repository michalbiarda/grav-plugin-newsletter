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

    public function process(Form $form, array $params = [], string $action = 'subscribe', $data = null): void
    {
        if (empty($params['handlers'])) {
            $params['handlers'] = ['local' => []];
        }
        foreach ($params['handlers'] as $handler => $handlerData) {
            $handler = $this->container->getSubscribeHandlerFactory()->create($handler, $form, $handlerData);
            $handler->{$action}($data);
        }
    }

    public function getFormProcesses(Form $form): array
    {
        return $form['process'];
    }

    /**
     * @param Form $form
     * @param array $params
     * @return SubscribeHandlerInterface[]
     */
    public function getHandlers(Form $form, array $params = []): array
    {
        $handlers = [];
        if (empty($params['handlers'])) {
            $params['handlers'] = ['local' => []];
        }
        foreach ($params['handlers'] as $handler => $handlerData) {
            $handlers[] = $this->container->getSubscribeHandlerFactory()->create($handler, $form, $handlerData);
        }
        return $handlers;
    }

    /**
     * @param SubscribeHandlerInterface[] $handlers
     */
    public function processSubscribe(array $handlers)
    {
        $this->validateSubscribeHandlers($handlers);
        foreach ($handlers as $handler) {
            /** @var $handler SubscribeHandlerInterface */
            $handler->subscribe();
        }
    }

    /**
     * @param SubscribeHandlerInterface[] $handlers
     * @param array $data
     * @return bool
     */
    public function processUnsubscribe(array $handlers, array $data): bool
    {
        $unsubscribeHandlers = $this->filterUnsubscribeHandlers($handlers);
        $result = true;
        foreach ($unsubscribeHandlers as $handler) {
            $result = $handler->unsubscribe($data) && $result;
        }
        return $result;
    }

    /**
     * @param array $handlers
     * @throws \InvalidArgumentException
     */
    private function validateSubscribeHandlers(array $handlers): void
    {
        if (empty($handlers)) {
            throw new \InvalidArgumentException('Empty handlers array');
        }
        foreach ($handlers as $handler) {
            if (!$handler instanceof SubscribeHandlerInterface) {
                throw new \InvalidArgumentException(
                    'Each handler must be an instance of \Grav\Plugin\Newsletter\SubscribeHandlerInterface'
                );
            }
        }
    }

    /**
     * @param array $handlers
     * @return UnsubscribeHandlerInterface[]
     * @throws \InvalidArgumentException
     */
    private function filterUnsubscribeHandlers(array $handlers): array
    {
        $this->validateSubscribeHandlers($handlers);
        $unsubscribeHandlers = [];
        foreach ($handlers as $handler) {
            if ($handler instanceof UnsubscribeHandlerInterface) {
                $unsubscribeHandlers[] = $handler;
            }
        }
        if (empty($unsubscribeHandlers)) {
            throw new \InvalidArgumentException(
                'At least one handler must be and instance of \Grav\Plugin\Newsletter\UnsubscribeHandlerInterface'
            );
        }
        return $unsubscribeHandlers;
    }
}