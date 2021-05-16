<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class AssetHolding
 * @package FurqanSiddiqui\Cardano\Response
 */
class AssetHolding
{
    /** @var string */
    public string $policyId;
    /** @var string */
    public string $assetName;
    /** @var int */
    public int $quantity;

    /**
     * @param $data
     * @return static
     * @throws API_ResponseException
     */
    public static function fromResponse($data): self
    {
        if (!is_array($data)) {
            throw new API_ResponseException('Cannot create AssetHolding; argument not object');
        }

        $policyId = $data["policy_id"];
        if (!Validate::PolicyId($policyId)) {
            throw new API_ResponseException('Invalid policy ID for asset');
        }

        $assetName = $data["asset_name"];
        if (!is_string($assetName) || !$assetName) {
            throw new API_ResponseException('Invalid asset name');
        }

        $quantity = $data["quantity"];
        if (!is_int($quantity)) {
            throw new API_ResponseException('Invalid asset quantity');
        }


        $aH = new self();
        $aH->policyId = $policyId;
        $aH->assetName = $assetName;
        $aH->quantity = $quantity;
        return $aH;
    }
}
