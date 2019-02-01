<?php
declare(strict_types=1);

namespace CardanoSL\API;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\AmountException;
use CardanoSL\Validate;

/**
 * Class LovelaceAmount
 * @package CardanoSL\API
 */
class LovelaceAmount extends \CardanoSL\Response\LovelaceAmount
{
    /**
     * LovelaceAmount constructor.
     * @param int $amount
     * @throws \CardanoSL\Exception\API_Exception
     */
    public function __construct(int $amount = 0)
    {
        parent::__construct($amount);
    }

    /**
     * @param string $amount
     * @return LovelaceAmount
     * @throws AmountException
     * @throws \CardanoSL\Exception\API_Exception
     */
    public static function ADA(string $amount): self
    {
        if (!Validate::BcAmount($amount, CardanoSL::SCALE, false)) {
            throw new AmountException('Invalid ADA amount');
        }

        $lovelaceAmount = new self();
        $lovelaceAmount->ada = $amount;
        $lovelaceAmount->lovelace = intval(bcmul($amount, bcpow(10, strval(CardanoSL::SCALE), 0), CardanoSL::SCALE));
        return $lovelaceAmount;
    }

    /**
     * @param int $amount
     * @return LovelaceAmount
     * @throws \CardanoSL\Exception\API_Exception
     */
    public static function Lovelace(int $amount): self
    {
        return new self($amount);
    }
}