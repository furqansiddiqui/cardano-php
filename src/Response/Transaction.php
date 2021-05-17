<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class Transaction
 * @package FurqanSiddiqui\Cardano\Response
 */
class Transaction implements ResponseModelInterface
{
    /** @var string */
    public string $id;
    /** @var string */
    public string $status;
    /** @var LovelaceAmount|null */
    public ?LovelaceAmount $amount = null;
    /** @var LovelaceAmount|null */
    public ?LovelaceAmount $fee = null;
    /** @var LovelaceAmount|null */
    public ?LovelaceAmount $deposit = null;
    /** @var array|null */
    public ?array $insertedAt = null;
    /** @var array|null */
    public ?array $expiresAt = null;
    /** @var array|null */
    public ?array $pendingSince = null;
    /** @var array|null */
    public ?array $depth = null;
    /** @var array|null */
    public ?array $withdrawals = null;
    /** @var string */
    public string $direction;
    /** @var array */
    public array $inputs;
    /** @var array */
    public array $outputs;
    /** @var array */
    private array $_raw;


    /**
     * Transaction constructor.
     * @param $data
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct($data)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $data->data();
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        // Transaction ID
        $txId = $data["id"];
        if (!Validate::Hash64($txId)) {
            throw API_ResponseException::InvalidPropValue("tx.id", "Hash64", gettype($txId));
        }

        $smallTxId = substr($txId, 0, 6);
        $smallTxProp = sprintf("tx[%s...]", $smallTxId);
        $this->id = $txId;

        // Amounts
        $amount = $data["amount"];
        if (is_array($amount)) {
            $this->amount = new LovelaceAmount($amount, $smallTxProp . "amount");
        }

        $fee = $data["fee"] ?? null;
        if (is_array($fee)) {
            $this->fee = new LovelaceAmount($fee, $smallTxProp . "fee");
        }

        $deposit = $data["deposit"] ?? null;
        if (is_array($deposit)) {
            $this->deposit = new LovelaceAmount($deposit, $smallTxProp . "deposit");
        }

        // Status
        $status = $data["status"];
        if (!in_array($status, ["pending", "in_ledger", "expired"])) {
            throw API_ResponseException::InvalidPropValue("tx.status", "pending/in_ledger/expired", $status);
        }

        $this->status = $status;

        // Direction
        $this->direction = strtolower(strval($data["direction"] ?? ""));
        if (!in_array($this->direction, ["outgoing", "incoming"])) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "direction");
        }

        // Timeline
        if (isset($data["inserted_at"]) && is_array($data["inserted_at"])) {
            $this->insertedAt = $data["inserted_at"];
        }

        if (isset($data["expires_at"]) && is_array($data["expires_at"])) {
            $this->expiresAt = $data["expires_at"];
        }

        if (isset($data["pending_since"]) && is_array($data["pending_since"])) {
            $this->pendingSince = $data["pending_since"];
        }

        // Depth
        if (isset($data["depth"]) && is_array($data["depth"])) {
            $this->depth = $data["depth"];
        }

        // Withdrawals
        if (isset($data["withdrawals"]) && is_array($data["withdrawals"])) {
            $this->withdrawals = $data["withdrawals"];
        }

        // Inputs
        $this->inputs = [];
        $inputs = $data["inputs"] ?? null;
        if (!is_array($inputs)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "inputs", "Array", gettype($inputs));
        }

        foreach ($inputs as $input) {
            $this->inputs[] = new TxInput($input);
        }

        // Outputs
        $this->outputs = [];
        $outputs = $data["outputs"] ?? null;
        if (!is_array($outputs)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "outputs", "Array", gettype($inputs));
        }

        foreach ($outputs as $output) {
            $this->outputs[] = new TxOutput($output);
        }

        $this->_raw = $data;
    }

    /**
     * @return array
     */
    public function raw(): array
    {
        return $this->_raw;
    }
}
