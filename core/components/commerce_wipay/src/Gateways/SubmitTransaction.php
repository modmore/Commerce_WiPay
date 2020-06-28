<?php

namespace modmore\Commerce_WiPay\Gateways;

use modmore\Commerce\Gateways\Interfaces\RedirectTransactionInterface;
use modmore\Commerce\Gateways\Interfaces\TransactionInterface;

class SubmitTransaction implements TransactionInterface, RedirectTransactionInterface {
    /**
     * @var array
     */
    private $submitData;
    /**
     * @var string
     */
    private $endpoint;

    public function __construct(string $endpoint, array $submitData)
    {
        $this->submitData = $submitData;
        $this->endpoint = $endpoint;
    }

    public function isRedirect()
    {
        return true;
    }

    public function getRedirectMethod()
    {
        return 'POST';
    }

    public function getRedirectUrl()
    {
        return $this->endpoint;
    }

    public function getRedirectData()
    {
        return $this->submitData;
    }

    public function isPaid()
    {
        return false;
    }

    public function isAwaitingConfirmation()
    {
        return true;
    }

    public function isFailed()
    {
        return false;
    }

    public function isCancelled()
    {
        return false;
    }

    public function getErrorMessage()
    {
        return '';
    }

    public function getPaymentReference()
    {
        return ''; // not yet available
    }

    public function getExtraInformation()
    {
        return [];
    }

    public function getData()
    {
        return [
            'endpoint' => $this->endpoint,
            'submitData' => $this->submitData,
        ];
    }
}