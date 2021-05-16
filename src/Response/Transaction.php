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
    /** @var LovelaceAmount */
    public LovelaceAmount $amount;
    /** @var int */
    public int $confirmations;
    /** @var string */
    public string $creationTime;
    /** @var string */
    public string $direction;
    /** @var array */
    public array $inputs;
    /** @var array */
    public array $outputs;
    /** @var TxStatus */
    public TxStatus $status;
    /** @var string */
    public string $type;

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
            $data = $data->payload["data"];
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        $txId = $data["id"];
        if (!Validate::Hash64($txId)) {
            throw API_ResponseException::InvalidPropValue("tx.id", "Hash64", gettype($txId));
        }

        $smallTxId = substr($txId, 0, 6);
        $smallTxProp = sprintf("tx[%s...]", $smallTxId);

        $this->id = $txId;
        $this->amount = new LovelaceAmount($data["amount"], $smallTxProp . "amount");
        $confirmations = $data["confirmations"] ?? null;
        if (!is_int($confirmations)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "confirmations", "int", gettype($confirmations));
        }

        $this->confirmations = $confirmations;

        $creationTime = $data["creationTime"] ?? null;
        if (!is_string($creationTime)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "creationTime");
        }

        $this->creationTime = $creationTime;

        $this->direction = strtolower(strval($data["direction"] ?? ""));
        if (!in_array($this->direction, ["outgoing", "incoming"])) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "direction");
        }

        $this->type = strtolower(strval($data["type"] ?? ""));
        if (!in_array($this->type, ["local", "foreign"])) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "type");
        }

        // Inputs
        $this->inputs = [];
        $inputs = $data["inputs"] ?? null;
        if (!is_array($inputs)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "inputs", "Array", gettype($inputs));
        }

        foreach ($inputs as $input) {
            $this->inputs[] = new TxInOut($input);
        }

        // Outputs
        $this->outputs = [];
        $outputs = $data["outputs"] ?? null;
        if (!is_array($outputs)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "outputs", "Array", gettype($inputs));
        }

        foreach ($outputs as $output) {
            $this->outputs[] = new TxInOut($output);
        }

        // Status
        $status = $data["status"] ?? null;
        if (!is_array($status)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "status", "Array", gettype($status));
        }

        $this->status = new TxStatus($status);
    }
}
