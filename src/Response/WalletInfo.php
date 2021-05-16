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
    public string $assuranceLevel;
    /** @var LovelaceAmount */
    public LovelaceAmount $balance;
    /** @var string */
    public string $createdAt;
    /** @var bool|null */
    public ?bool $hasSpendingPassword = null;
    /** @var string */
    public string $id;
    /** @var string|null */
    public ?string $name = null;
    /** @var string|null */
    public ?string $spendingPasswordLastUpdate = null;
    /** @var WalletSyncState */
    public WalletSyncState $syncState;
    /** @var string */
    public string $type;

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
            $data = $data->payload["data"] ?? null;
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        $walletId = $data["id"];
        if (!Validate::WalletIdentifier($walletId)) {
            throw API_ResponseException::InvalidPropValue("wallet.id");
        }

        $this->id = $walletId;

        $assuranceLevel = $data["assuranceLevel"];
        if (!Validate::AssuranceLevel($assuranceLevel)) {
            throw API_ResponseException::InvalidPropValue("wallet.assuranceLevel");
        }

        $this->assuranceLevel = $assuranceLevel;
        $this->balance = new LovelaceAmount($data["balance"] ?? null, "wallet.Balance");
        $this->createdAt = $data["createdAt"];
        $this->hasSpendingPassword = array_key_exists("hasSpendingPassword", $data) && is_bool($data["hasSpendingPassword"]) ?
            $data["hasSpendingPassword"] : null;
        $this->name = $data["name"] ?? null;
        $this->spendingPasswordLastUpdate = $data["spendingPasswordLastUpdate"] ?? null;
        $this->type = strval($data["type"]);

        $syncState = $data["syncState"] ?? null;
        if (!is_array($syncState) || !$syncState) {
            throw API_ResponseException::RequirePropMissing("wallet.syncState");
        }

        $this->syncState = WalletSyncState::Construct($syncState);
    }
}
