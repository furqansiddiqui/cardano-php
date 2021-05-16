<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class WalletInfo
 * @package FurqanSiddiqui\Cardano\Response
 */
class WalletInfo implements ResponseModelInterface
{

    /** @var string */
    public string $id;
    /** @var int */
    public int $addressPoolGap;
    /** @var WalletBalance */
    public WalletBalance $balance;
    /** @var WalletAssets */
    public WalletAssets $assets;
    /** @var string|null */
    public ?string $name = null;
    /** @var array|null */
    public ?array $delegation = null;
    /** @var array|null */
    public ?array $tip = null;
    /** @var array|null */
    public ?array $state = null;
    /** @var bool */
    public bool $stateReady = false;

    /**
     * WalletInfo constructor.
     * @param $data
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct($data)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $data->data();
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        $this->id = strval($data["id"]);
        if (!Validate::WalletIdentifier($this->id)) {
            throw API_ResponseException::InvalidPropValue("wallet.id");
        }

        $this->addressPoolGap = intval($data["address_pool_gap"]);

        // Balances
        $this->balance = new WalletBalance();
        $avB = $data["balance"]["available"]["quantity"] ?? null;
        if (is_int($avB)) {
            $this->balance->available = new LovelaceAmount($avB);
        }

        $avR = $data["balance"]["reward"]["quantity"] ?? null;
        if (is_int($avR)) {
            $this->balance->reward = new LovelaceAmount($avR);
        }

        $avT = $data["balance"]["total"]["quantity"] ?? null;
        if (is_int($avT)) {
            $this->balance->total = new LovelaceAmount($avT);
        }

        // Assets
        $this->assets = new WalletAssets();
        foreach (["available", "total"] as $assetI) {
            $assetsList = $data["assets"][$assetI] ?? null;
            if (is_array($assetsList) && $assetsList) {
                $i = -1;
                foreach ($assetsList as $asset) {
                    $i++;
                    try {
                        array_push($this->assets->$assetI, AssetHolding::fromResponse($asset));
                    } catch (API_ResponseException $e) {
                        throw new API_ResponseException('Assets.available.%d; %s', $i, $e->getMessage());
                    }
                }
            }
        }

        // More props
        $this->name = $data["name"] ?? null;
        if (isset($data["delegation"]) && is_array($data["delegation"])) {
            $this->delegation = $data["delegation"];
        }

        if (isset($data["state"]) && is_array($data["state"])) {
            $this->state = $data["state"];
        }

        $this->stateReady = isset($this->state["status"]) && $this->state["status"] === "ready";

        if (isset($data["tip"]) && is_array($data["tip"])) {
            $this->tip = $data["tip"];
        }
    }
}
