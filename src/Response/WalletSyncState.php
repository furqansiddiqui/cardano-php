<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class WalletSyncState
 * @package FurqanSiddiqui\Cardano\Response
 */
class WalletSyncState implements ResponseModelInterface
{
    /** @var string */
    public string $tag;
    /** @var null|QuantityUnitBlock */
    public ?QuantityUnitBlock $estimatedCompletionTime = null;
    /** @var null|QuantityUnitBlock */
    public ?QuantityUnitBlock $percentage = null;
    /** @var null|QuantityUnitBlock */
    public ?QuantityUnitBlock $throughput = null;

    /**
     * @param array $block
     * @return static
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     */
    public static function Construct(array $block): self
    {
        $syncState = new self();
        $syncState->tag = strval($block["tag"]);
        if (!Validate::SyncStateTag($syncState->tag)) {
            throw API_ResponseException::InvalidPropValue("wallet.syncState.Tag");
        }

        $syncStateData = $block["data"] ?? null;
        if ($syncState->tag === "restoring" || is_array($syncStateData)) {
            $syncState->estimatedCompletionTime = QuantityUnitBlock::Construct(
                "wallet.syncState.data.estimatedCompletionTime",
                $syncStateData["estimatedCompletionTime"] ?? null
            );

            $syncState->percentage = QuantityUnitBlock::Construct(
                "wallet.syncState.data.percentage",
                $syncStateData["percentage"] ?? null
            );

            $syncState->throughput = QuantityUnitBlock::Construct(
                "wallet.syncState.data.throughput",
                $syncStateData["throughput"] ?? null
            );
        }

        return $syncState;
    }
}
