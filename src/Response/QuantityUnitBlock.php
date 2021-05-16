<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;

/**
 * Class QuantityUnitBlock
 * @package FurqanSiddiqui\Cardano\Response
 */
class QuantityUnitBlock
{
    /** @var int */
    public int $quantity;
    /** @var string */
    public string $unit;

    /**
     * @param string $which
     * @param array|null $block
     * @return static
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
