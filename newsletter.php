<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\Newsletter\Container;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class NewsletterPlugin
 * @package Grav\Plugin
 */
class NewsletterPlugin extends Plugin
{
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

        $this->container = new Container();
        $this->container['config'] = function() {
            return $this->config;
        };

        $this->enable([
            'onFormProcessed' => ['onFormProcessed', 0]
        ]);
    }

    public function onFormProcessed(Event $event)
    {
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        switch ($action) {
            case 'subscribe':
                $this->container->getFormProcessor()->process($form, $params);
        }
    }
}
