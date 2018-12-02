<?php

namespace Grav\Plugin\Newsletter\Controller;

class NewsletterController extends AbstractController
{
    public function unsubscribeAction(): void
    {
        $formName = $this->container->getUri()->query('name');
        if (!$formName) {
            $this->firePageNotFound();
            return;
        }
        $hash = $this->container->getUri()->query('hash');
        if (!$hash) {
            $this->firePageNotFound();
            return;
        }
        $form = $this->container->getFormPlugin()->getForm(['name' => $formName]);
        if (!$form) {
            $this->firePageNotFound();
            return;
        }
        $processes = $this->container->getFormProcessor()->getFormProcesses($form);
        if (empty($processes)) {
            $this->firePageNotFound();
            return;
        }
        $params = null;
        foreach ($processes as $process) {
            if (is_array($process) && key_exists('subscribe', $process)) {
                $params = $process['subscribe'];
                break;
            }
        }
        if (is_null($params)) {
            $this->firePageNotFound();
            return;
        }
        $handlers = $this->container->getFormProcessor()->getHandlers($form, $params);
        $result = $this->container->getFormProcessor()->processUnsubscribe($handlers, ['hash' => $hash]);
        $configPath = 'plugins.newsletter.unsubscribe.' . ($result ? 'success' : 'error') . 'Path';
        $this->container->getGrav()->redirect($this->container->getConfig()->value($configPath));
    }

    private function firePageNotFound(): void
    {
        $this->container->getGrav()->fireEvent('onPageNotFound');
    }
}