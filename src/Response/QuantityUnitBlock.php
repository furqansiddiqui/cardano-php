<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;

/**
 * Class QuantityUnitBlock
 * @package CardanoSL\Response
 */
class QuantityUnitBlock
{
    /** @var int */
    public $quantity;
    /** @var string */
    public $unit;

    /**
     * @param string $which
     * @param array $block
     * @return QuantityUnitBlock
     * @throws API_ResponseException
     */
    public static function Construct(string $which, ?array $block = null): self
    {
        if (!is_array($block) || !$block) {
            throw API_ResponseException::RequirePropMissing($which);
        }

        $quantity = $block["quantity"] ?? null;
        $unit = $block["unit"] ?? null;

        if (!is_int($quantity)) {
            throw new API_ResponseException(sprintf('Invalid "quantity" for block "%s"', $which));
        } elseif (!is_string($unit) || !$unit) {
            throw new API_ResponseException(sprintf('Invalid "unit" for block "%s"', $which));
        }

        $obj = new self();
        $obj->quantity = $quantity;
        $obj->unit = $unit;
        return $obj;
    }
}