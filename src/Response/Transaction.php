<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;
use CardanoSL\Validate;

/**
 * Class Transaction
 * @package CardanoSL\Response
 */
class Transaction implements ResponseModelInterface
{
    /** @var string */
    public $id;
    /** @var LovelaceAmount */
    public $amount;
    /** @var int */
    public $confirmations;
    /** @var string */
    public $creationTime;
    /** @var string */
    public $direction;
    /** @var array */
    public $inputs;
    /** @var array */
    public $outputs;
    /** @var TxStatus */
    public $status;
    /** @var string */
    public $type;

    /**
     * Transaction constructor.
     * @param $data
     * @throws API_ResponseException
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct($data)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $data->payload["data"] ?? null;
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        $this->id = $data["id"] ?? null;
        if (!Validate::Hash64($this->id)) {
            throw API_ResponseException::InvalidPropValue("tx.id", "Hash64", gettype($this->id));
        }

        $smallTxId = substr($this->id, 0, 6);
        $smallTxProp = sprintf("tx[%s...]", $smallTxId);

        $this->amount = new LovelaceAmount($data["amount"] ?? null, $smallTxProp . "amount");
        $this->confirmations = $data["confirmations"] ?? null;
        if (!is_int($this->confirmations)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "confirmations", "int", gettype($this->confirmations));
        }

        $this->creationTime = $data["creationTime"] ?? null;
        if (!is_string($this->creationTime)) {
            throw API_ResponseException::InvalidPropValue($smallTxProp . "creationTime");
        }

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