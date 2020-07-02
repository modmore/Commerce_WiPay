<?php

namespace modmore\Commerce_WiPay\Gateways;

use Commerce;
use comOrder;
use comPaymentMethod;
use comTransaction;
use modmore\Commerce\Adapter\AdapterInterface;
use modmore\Commerce\Adapter\Revolution;
use modmore\Commerce\Admin\Widgets\Form\CheckboxField;
use modmore\Commerce\Admin\Widgets\Form\PasswordField;
use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Gateways\Exceptions\TransactionException;
use modmore\Commerce\Gateways\Helpers\GatewayHelper;
use modmore\Commerce\Gateways\Interfaces\GatewayInterface;

class WiPay implements GatewayInterface
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
        return '';
    }

    public function submit(comTransaction $transaction, array $data)
    {
        $order = $transaction->getOrder();
        if (!$order) {
            throw new TransactionException('Missing order');
        }
        $billingAddress = $order->getBillingAddress();
        if (!$billingAddress) {
            throw new TransactionException('Missing billing address');
        }

        $firstName = $billingAddress->get('firstname');
        $lastName = $billingAddress->get('lastname');
        $fullName = $billingAddress->get('fullname');
        GatewayHelper::normalizeNames($firstName, $lastName, $fullName);
        $phone = $billingAddress->get('phone') ?: $billingAddress->get('mobile');

        $submitData = [
            'total' => number_format($transaction->get('amount') / 100, 2, '.', ''),
            'phone' => $phone,
            'email' => $billingAddress->get('email'),
            'name' => $fullName,
            'order_id' => $order->get('id'),
            'return_url' => GatewayHelper::getReturnUrl($transaction),
            'developer_id' => $this->method->getProperty('developer_id'),
        ];

        return new SubmitTransaction($this->getEndpoint(), $submitData);
    }

    public function returned(comTransaction $transaction, array $data)
    {
        $status = $data['status'] ?? 'notfound';
        if (!isset($data['status']) && strpos($data['transaction'], '?status=') !== false) {
            $status = $data['status'] = substr($data['transaction'], strpos($data['transaction'], '?status=') + strlen('?status='));
        }
        $hash = $data['hash'] ?? '';
        $transactionId = $data['transaction_id'] ?? '';
        $reasonCode = (int)($data['reasonCode'] ?? 0);
        $reasonDescription = $data['reasonDescription'] ?? '';
        $responseCode = (int)($data['responseCode'] ?? 0);

        // These variables don't seem needed.
        // - Rather than trusting the URL param for the total, we check the transaction
        // - No idea what "D=TT" is supposed to mean
        // - Who cares about a timestamp provided by the provider
//        $total = $data['total'] ?? 0;
//        $d = $data['D'] ?? ''; // ?????
//        $date = $data['date'] ?? '';

        $order = $transaction->getOrder();
        if (!$order) {
            throw new TransactionException('Missing order');
        }
        return new ReturnedTransaction(
            $status,
            $transactionId,
            $reasonCode,
            $reasonDescription,
            $responseCode,
            $transaction->get('amount'),
            $order->get('id'),
            $this->method->getProperty('merchant_key'),
            $hash
        );
    }

    public function getEndpoint(): string
    {
        if ($this->method->getProperty('sandbox')) {
            return 'https://sandbox.wipayfinancial.com/v1/gateway';
        }
        return 'https://wipayfinancial.com/v1/gateway_live';
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