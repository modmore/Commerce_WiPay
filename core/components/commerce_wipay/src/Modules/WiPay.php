<?php

namespace modmore\Commerce_WiPay\Modules;

use modmore\Commerce\Events\Gateways;
use modmore\Commerce\Modules\BaseModule;
use modmore\Commerce_WiPay\Gateways\WiPay as WiPayGateway;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class WiPay extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_wipay:default');
        return $this->adapter->lexicon('commerce_wipay');
    }

    public function getAuthor()
    {
        return 'modmore';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_wipay.description');
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_wipay:default');

        // Add template path to twig
        $root = dirname(__DIR__, 2);
        $this->commerce->view()->addTemplatesPath($root . '/templates/');

        $dispatcher->addListener(\Commerce::EVENT_GET_PAYMENT_GATEWAYS, [$this, 'registerGateway']);
    }

    public function registerGateway(Gateways $event)
    {
        $event->addGateway(WiPayGateway::class, 'WiPay');
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];

        return $fields;
    }
}
