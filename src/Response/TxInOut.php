<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class TxInOut
 * @package CardanoSL\Response
 */
class TxInOut
{
    /** @var string|mixed */
    public string $address;
    /** @var LovelaceAmount */
    public LovelaceAmount $amount;

    /**
     * TxInOut constructor.
     * @param array $data
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(array $data)
    {
        $address = $data["address"];
        if (!Validate::Address($address)) {
            throw API_ResponseException::InvalidPropValue("txInOut.address");
        }

        $this->address = $address;
        $this->amount = new LovelaceAmount($data["amount"]);
    }
}
