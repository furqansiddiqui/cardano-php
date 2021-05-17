<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Cardano;
use FurqanSiddiqui\Cardano\Exception\AmountException;

/**
 * Class LovelaceAmount
 * @package FurqanSiddiqui\Cardano\Response
 */
class LovelaceAmount implements ResponseModelInterface
{
    /** @var int */
    public int $lovelace;
    /** @var string */
    public string $ada;

    /**
     * LovelaceAmount constructor.
     * @param $data
     * @param string|null $which
     * @throws AmountException
     */
    public function __construct($data, ?string $which = null)
    {
        if ($which) {
            $which = sprintf('"%s" ', $which);
        }

        if (!is_array($data)) {
            throw new AmountException($which . 'Invalid amount object');
        }

        if ($data["unit"] !== "lovelace") {
            throw new AmountException($which . 'Amount object unit not lovelace, got "' . $data["unit"] . '"');
        }

        $quantity = $data["quantity"];
        if (!is_int($quantity)) {
            throw new AmountException($which . 'Lovelace amount must be an integer');
        } elseif ($quantity > Cardano::MAX_LOVELACE) {
            throw new AmountException($which . sprintf('Lovelace amount cannot exceed %d', Cardano::MAX_LOVELACE));
        }

        $this->lovelace = $quantity;
        $this->ada = (string)bcdiv(strval($this->lovelace), bcpow("10", strval(Cardano::SCALE), 0), Cardano::SCALE);
    }
}
