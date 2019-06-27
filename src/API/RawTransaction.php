<?php
declare(strict_types=1);

namespace CardanoSL\API;

use CardanoSL\Exception\TransactionException;
use CardanoSL\Validate;

/**
 * Class RawTransaction
 * @package CardanoSL\API
 * @property-read array $payees
 * @property-read string|null $groupingPolicy
 */
class RawTransaction
{
    /** @var array */
    private $payees;
    /** @var string|null */
    private $groupingPolicy;

    /**
     * RawTransaction constructor.
     */
    public function __construct()
    {
        $this->payees = [];
    }

    /**
     * @param string $address
     * @param LovelaceAmount $amount
     * @return RawTransaction
     * @throws TransactionException
     */
    public function dest(string $address, LovelaceAmount $amount): self
    {
        if (!Validate::Address($address)) {
            throw new TransactionException('Invalid destination/payee address');
        }

        $this->payees[] = [
            "address" => $address,
            "amount" => $amount->lovelace
        ];

        return $this;
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "payees":
            case "groupingPolicy":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param string $policy
     * @return RawTransaction
     * @throws TransactionException
     */
    public function groupingPolicy(string $policy): self
    {
        if (!in_array($policy, ["OptimizeForSecurity", "OptimizeForHighThroughput"])) {
            throw new TransactionException('Invalid transaction grouping policy');
        }

        $this->groupingPolicy = $policy;

        return $this;
    }
}