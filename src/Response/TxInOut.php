<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Validate;

/**
 * Class TxInOut
 * @package CardanoSL\Response
 */
class TxInOut
{
    /** @var mixed|null */
    public $address;
    /** @var LovelaceAmount */
    public $amount;

    /**
     * TxInOut constructor.
     * @param array $data
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct(array $data)
    {
        $this->address = $data["address"] ?? null;
        if (!Validate::Address($this->address)) {
            throw API_ResponseException::InvalidPropValue("txInOut.address");
        }

        $this->amount = new LovelaceAmount($data["amount"]);
    }
}