<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

/**
 * Class WalletBalance
 * @package FurqanSiddiqui\Cardano\Response
 */
class WalletBalance
{
    /** @var LovelaceAmount|null */
    public ?LovelaceAmount $available = null;
    /** @var LovelaceAmount|null */
    public ?LovelaceAmount $reward = null;
    /** @var LovelaceAmount|null */
    public ?LovelaceAmount $total = null;
}
