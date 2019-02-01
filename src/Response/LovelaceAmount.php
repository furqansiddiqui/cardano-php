<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\API_ResponseException;

/**
 * Class LovelaceAmount
 * @package CardanoSL\Response
 */
class LovelaceAmount implements ResponseModelInterface
{
    /** @var int */
    public $lovelace;
    /** @var string */
    public $ada;

    /**
     * LovelaceAmount constructor.
     * @param null $lovelaceAmount
     * @param string|null $which
     * @throws API_ResponseException
     */
    public function __construct($lovelaceAmount = null, ?string $which = null)
    {
        if ($which) {
            $which = sprintf('"%s" ', $which);
        }

        if (!is_int($lovelaceAmount)) {
            throw new API_ResponseException($which . 'Lovelace amount must be an integer');
        } elseif ($lovelaceAmount > CardanoSL::MAX_LOVELACE) {
            throw new API_ResponseException($which . sprintf('Lovelace amount cannot exceed %d', CardanoSL::MAX_LOVELACE));
        }

        $this->lovelace = $lovelaceAmount;
        $this->ada = bcdiv(strval($this->lovelace), bcpow("10", strval(CardanoSL::SCALE), 0), CardanoSL::SCALE);
    }
}