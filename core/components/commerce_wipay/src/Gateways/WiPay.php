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
use modmore\Commerce\Admin\Widgets\Form\SelectField;
use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Admin\Widgets\Form\Validation\Enum;
use modmore\Commerce\Admin\Widgets\Form\Validation\Required;
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

        $userData = [
            'name' => $fullName,
            'email' => $billingAddress->get('email'),
            'phone' => $phone,
            'address1' => $billingAddress->get('address1'),
            'address2' => $billingAddress->get('address2'),
            'address3' => $billingAddress->get('address3'),
            'zip' => $billingAddress->get('zip'),
            'city' => $billingAddress->get('city'),
            'state' => $billingAddress->get('state'),
            'country' => $billingAddress->get('country')
        ];

        $submitData = [
            'account_number' => $this->method->getProperty('account_number'),
            'country_code' => $billingAddress->get('country'),
            'currency' => $order->getCurrency()->get('alpha_code'),
            'environment' => $this->method->getProperty('sandbox') ? 'sandbox' : 'live',
            'fee_structure' => 'customer_pay',
            'method' => 'credit_card',
            'total' => number_format($transaction->get('amount') / 100, 2, '.', ''),
            'order_id' => $order->get('id'),
            'origin' => 'modmore_Commerce_MODX',
            'response_url' => GatewayHelper::getReturnUrl($transaction),
            'data' => json_encode($userData)
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
            $this->method->getProperty('api_key'),
            $hash
        );
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        switch ($this->method->getProperty('api_url')) {
            case 'BB':
                return 'https://bb.wipayfinancial.com/plugins/payments/request';

            case 'JM':
                return 'https://jm.wipayfinancial.com/plugins/payments/request';

            default:
                return 'https://tt.wipayfinancial.com/plugins/payments/request';
        }
    }

    /**
     * @param comPaymentMethod $method
     * @return array
     */
    public function getGatewayProperties(comPaymentMethod $method): array
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
            'name' => 'properties[account_number]',
            'label' => $this->adapter->lexicon('commerce_wipay.account_number'),
            'description' => $this->adapter->lexicon('commerce_wipay.account_number_desc'),
            'value' => $method->getProperty('account_number'),
        ]);

        $fields[] = new PasswordField($this->commerce, [
            'name' => 'properties[api_key]',
            'label' => $this->adapter->lexicon('commerce_wipay.api_key'),
            'description' => $this->adapter->lexicon('commerce_wipay.api_key_desc'),
            'value' => $method->getProperty('api_key'),
        ]);

        $fields[] = new SelectField($this->commerce, [
            'name' => 'properties[api_url]',
            'label' => $this->adapter->lexicon('commerce_wipay.select_api_country'),
            'description' => $this->adapter->lexicon('commerce_wipay.select_api_country_desc'),
            'options' => [
                ['value' => 'TT', 'label' => $this->adapter->lexicon('commerce_wipay.trinidad_and_tobago')],
                ['value' => 'BB', 'label' => $this->adapter->lexicon('commerce_wipay.barbados')],
                ['value' => 'JM', 'label' => $this->adapter->lexicon('commerce_wipay.jamaica')],
            ],
            'validation' => [
                new Required(),
                new Enum(['TT', 'BB', 'JM']),
            ],
            'value' => $method->getProperty('api_url')
        ]);

        return $fields;
    }
}