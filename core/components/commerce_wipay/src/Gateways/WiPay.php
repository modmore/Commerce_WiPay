<?php

namespace modmore\WiPay\Gateways;

use Commerce;
use comOrder;
use comPaymentMethod;
use comTransaction;
use modmore\Commerce\Gateways\Interfaces\GatewayInterface;
use modmore\Commerce\Gateways\Interfaces\WebhookGatewayInterface;
use modmore\Commerce\Adapter\AdapterInterface;
use modmore\Commerce\Adapter\Revolution;
use modmore\Commerce\Admin\Widgets\Form\PasswordField;
use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Admin\Widgets\Form\CheckboxField;

class WiPay implements GatewayInterface, WebhookGatewayInterface
{
    /**
     * @var \Commerce
     */
    private $commerce;

    /**
     * @var AdapterInterface|Revolution
     */
    private $adapter;

    /**
     * @var \comPaymentMethod
     */
    private $method;

    /**
     * WiPay constructor
     *
     * @param \Commerce $commerce
     * @param comPaymentMethod $method
     */
    public function __construct(Commerce $commerce, comPaymentMethod $method)
    {
        $this->commerce = $commerce;
        $this->adapter = $this->commerce->adapter;
        $this->method = $method;
    }

    public function view(comOrder $order)
    {
        return $this->commerce->view()->render('wipay/form.twig', [
            // todo: insert info
        ]);
    }

    public function submit(comTransaction $transaction, array $data)
    {
        // todo
    }

    public function returned(comTransaction $transaction, array $data)
    {
        // todo
    }

    public function webhook(comTransaction $transaction, array $data)
    {
        // todo
    }

    public function getGatewayProperties(comPaymentMethod $method)
    {
        $fields = [];

        $fields[] = new CheckboxField($this->commerce, [
            'name' => 'properties[sandbox]',
            'label' => $this->adapter->lexicon('commerce_wipay.sandbox'),
            'description' => $this->adapter->lexicon('commerce_wipay.sandbox_desc'),
            'value' => $method->getProperty('sandbox'),
            'default' => false,
        ]);

        $fields[] = new TextField($this->commerce, [
            'name' => 'properties[developer_id]',
            'label' => $this->adapter->lexicon('commerce_wipay.developer_id'),
            'description' => $this->adapter->lexicon('commerce_wipay.developer_id_desc'),
            'value' => $method->getProperty('developer_id'),
        ]);

        $fields[] = new PasswordField($this->commerce, [
            'name' => 'properties[merchant_key]',
            'label' => $this->adapter->lexicon('commerce_wipay.merchant_key'),
            'description' => $this->adapter->lexicon('commerce_wipay.merchant_key_desc'),
            'value' => $method->getProperty('merchant_key'),
        ]);

        return $fields;
    }
}