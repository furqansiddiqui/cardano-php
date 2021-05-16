<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\API;

use FurqanSiddiqui\Cardano\Cardano;
use FurqanSiddiqui\Cardano\Exception\AmountException;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class LovelaceAmount
 * @package FurqanSiddiqui\Cardano\API
 */
class LovelaceAmount extends \FurqanSiddiqui\Cardano\Response\LovelaceAmount
{
    /**
     * LovelaceAmount constructor.
     * @param int $amount
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(int $amount = 0)
    {
        parent::__construct($amount);
    }

    /**
     * @param string $amount
     * @return static
     * @throws AmountException
     */
    public static function ADA(string $amount): self
    {
        if (!Validate::BcAmount($amount, Cardano::SCALE, false)) {
            throw new AmountException('Invalid ADA amount');
        }

        $lovelaceAmount = new self();
        $lovelaceAmount->ada = $amount;
        $lovelaceAmount->lovelace = intval(bcmul($amount, bcpow("10", strval(Cardano::SCALE), 0), Cardano::SCALE));
        return $lovelaceAmount;
    }

    /**
     * @param int $amount
     * @return static
     * @throws AmountException
     */
    public static function Lovelace(int $amount): self
    {
        return new self($amount);
    }
}
