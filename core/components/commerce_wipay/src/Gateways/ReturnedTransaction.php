<?php

namespace modmore\Commerce_WiPay\Gateways;

use modmore\Commerce\Gateways\Interfaces\TransactionInterface;

class ReturnedTransaction implements TransactionInterface
{
    /**
     * @var string
     */
    private $expectedHash;
    /**
     * @var bool
     */
    private $hashValid;
    /**
     * @var string
     */
    private $hash;
    /**
     * @var string
     */
    private $merchantKey;
    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $transactionId;
    /**
     * @var int
     */
    private $reasonCode;
    /**
     * @var string
     */
    private $reasonDescription;
    /**
     * @var int
     */
    private $responseCode;
    /**
     * @var int
     */
    private $expectedTotal;
    /**
     * @var int
     */
    private $orderId;

    public function __construct(
        string $status,
        string $transactionId,
        int $reasonCode,
        string $reasonDescription,
        int $responseCode,
        int $expectedTotal,
        int $orderId,
        string $merchantKey,
        string $hash = ''
    ) {
        $this->hash = $hash;
        $this->merchantKey = $merchantKey;
        $this->status = $status;
        $this->transactionId = $transactionId;
        $this->reasonCode = $reasonCode;
        $this->reasonDescription = $reasonDescription;
        $this->responseCode = $responseCode;
        $this->expectedTotal = $expectedTotal;
        $this->orderId = $orderId;

        $this->checkHash();
    }

    public function isPaid()
    {
        return $this->status === 'success' && $this->hashValid;
    }

    public function isAwaitingConfirmation()
    {
        return false;
    }

    public function isFailed()
    {
        return $this->status === 'failed' || empty($this->hash) || !$this->hashValid;
    }

    public function isCancelled()
    {
        return false;
    }

    public function getErrorMessage()
    {
        return $this->reasonDescription;
    }

    public function getPaymentReference()
    {
        return $this->transactionId;
    }

    public function getExtraInformation()
    {
        return [
            'status' => $this->status,
            'reasonCode' => $this->reasonCode,
            'reasonDescription' => $this->reasonDescription,
            'responseCode' => $this->responseCode,
            'hash' => $this->hash,
            'expected_hash' => $this->expectedHash
        ];
    }

    public function getData()
    {
        return $this->getExtraInformation();
    }

    private function checkHash()
    {
        $total = number_format($this->expectedTotal / 100, 2, '.', '');
        $this->expectedHash = md5($this->orderId . $total . $this->merchantKey);
        $this->hashValid = $this->expectedHash === $this->hash;
    }
}