<?php
/** @noinspection PhpUnusedPrivateFieldInspection */
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\API;

use FurqanSiddiqui\Cardano\Exception\TransactionException;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class RawTransaction
 * @package FurqanSiddiqui\Cardano\API
 */
class RawTransaction
{
    /** @var array */
    private array $payees = [];

    /**
     * RawTransaction constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $address
     * @param LovelaceAmount $amount
     * @return $this
     * @throws TransactionException
     */
    public function nativeTransfer(string $address, LovelaceAmount $amount): self
    {
        if (!Validate::Address($address)) {
            throw new TransactionException('Invalid destination/payee address');
        }

        if (!isset($this->payees[$address])) {
            $this->payees[$address] = [];
        }

        $this->payees[$address]["native"] = $amount->lovelace;
        return $this;
    }

    /**
     * @param string $address
     * @param string $assetPolicyId
     * @param string $assetName
     * @param int $quantity
     * @return $this
     * @throws TransactionException
     */
    public function assetTransfer(string $address, string $assetPolicyId, string $assetName, int $quantity): self
    {
        if (!Validate::Address($address)) {
            throw new TransactionException('Invalid destination/payee address');
        }

        if (!isset($this->payees[$address])) {
            $this->payees[$address] = [];
        }

        if (!Validate::PolicyId($assetPolicyId)) {
            throw new TransactionException('Invalid asset policy identifier');
        }

        if (!preg_match('/^[a-f0-9]+$/i', $assetName)) {
            throw new TransactionException('Invalid asset name must be hex encoded');
        }

        if (!isset($this->payees[$address]["assets"])) {
            $this->payees[$address]["assets"] = [];
        }

        $this->payees[$address]["assets"][$assetPolicyId] = [
            "name" => $assetName,
            "quantity" => $quantity
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getOutputs(): array
    {
        $outputs = [];
        foreach ($this->payees as $address => $transfers) {
            $output = [];
            $output["address"] = $address;
            $nativeTransfer = $transfers["native"] ?? 0;
            $output["amount"] = [
                "quantity" => $nativeTransfer,
                "unit" => "lovelace"
            ];

            if (isset($transfers["assets"]) && $transfers["assets"]) {
                foreach ($transfers["assets"] as $policyId => $assetTransfer) {
                    $output["assets"] = [];
                    $output["assets"][] = [
                        "policy_id" => $policyId,
                        "asset_name" => $assetTransfer["name"],
                        "quantity" => $assetTransfer["quantity"]
                    ];
                }
            }

            $outputs[] = $output;
        }

        return $outputs;
    }
}
