<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class TxOutput
 * @package FurqanSiddiqui\Cardano\Response
 */
class TxOutput
{
    /** @var string|null */
    public ?string $address = null;
    /** @var LovelaceAmount|null */
    public ?LovelaceAmount $amount = null;
    /** @var array */
    public array $assets = [];

    /**
     * TxOutput constructor.
     * @param array $data
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(array $data)
    {
        if (array_key_exists("address", $data)) {
            $address = $data["address"];
            if (!Validate::Address($address)) {
                throw API_ResponseException::InvalidPropValue("txInOut.address");
            }

            $this->address = $address;
        }

        if (array_key_exists("amount", $data)) {
            if (!is_array($data["amount"])) {
                throw API_ResponseException::InvalidPropValue("txInOut.amount", "Array", gettype($data["amount"]));
            }

            $this->amount = new LovelaceAmount($data["amount"]);
        }

        // Assets
        $assets = $data["assets"] ?? null;
        if (is_array($assets) && $assets) {
            foreach ($assets as $asset) {
                $this->assets[] = AssetHolding::fromResponse($asset);
            }
        }
    }
}
