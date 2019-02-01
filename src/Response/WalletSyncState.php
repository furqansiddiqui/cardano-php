<?php
declare(strict_types=1);

namespace CardanoSL\Response;

/**
 * Class WalletSyncState
 * @package CardanoSL\Response
 */
class WalletSyncState implements ResponseModelInterface
{
    /** @var string */
    public $tag;
    /** @var QuantityUnitBlock */
    public $estimatedCompletionTime;
    /** @var QuantityUnitBlock */
    public $percentage;
    /** @var QuantityUnitBlock */
    public $throughput;
}