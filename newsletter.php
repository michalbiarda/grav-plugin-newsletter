<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\Controller\NewsletterController;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class NewsletterPlugin
 * @package Grav\Plugin
 */
class NewsletterPlugin extends Plugin
{
    const UNSUBSCRIBE_PATH = '/newsletter/unsubscribe';

    /** @var Container */
    private $container;
    
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            return;
        }

        $this->setContainer();

        $this->enable([
            'onFormProcessed' => ['onFormProcessed', 0],
            'onPageInitialized' => ['onPageInitialized']
        ]);
    }

    public function onFormProcessed(Event $event)
    {
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        switch ($action) {
            case 'subscribe':
                $handlers = $this->container->getFormProcessor()->getHandlers($form, $params);
                $this->container->getFormProcessor()->processSubscribe($handlers);
        }
    }

    public function onPageInitialized(Event $event)
    {
        if ($this->grav['uri']->path() === self::UNSUBSCRIBE_PATH) {
            $controller = new NewsletterController($this->container);
            $controller->execute('unsubscribe');
        }
    }

    private function setContainer(): void
    {
        $this->container = new Container();
        $this->container['grav'] = function () {
            return $this->grav;
        };
        $this->container['config'] = function () {
            return $this->config;
        };
    }
}
