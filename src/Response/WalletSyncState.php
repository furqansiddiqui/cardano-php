<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Validate;

/**
 * Class WalletSyncState
 * @package CardanoSL\Response
 */
class WalletSyncState implements ResponseModelInterface
{
    /** @var string */
    public $tag;
    /** @var null|QuantityUnitBlock */
    public $estimatedCompletionTime;
    /** @var null|QuantityUnitBlock */
    public $percentage;
    /** @var null|QuantityUnitBlock */
    public $throughput;

    /**
     * @param array $block
     * @return WalletSyncState
     * @throws API_ResponseException
     * @throws \CardanoSL\Exception\API_Exception
     */
    public static function Construct(array $block): self
    {
        $syncState = new self();
        $syncState->tag = $block["tag"] ?? null;
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