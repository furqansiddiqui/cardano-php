<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;
use CardanoSL\Validate;

/**
 * Class WalletInfo
 * @package CardanoSL\Response
 */
class WalletInfo implements ResponseModelInterface
{
    /** @var string */
    public $assuranceLevel;
    /** @var LovelaceAmount */
    public $balance;
    /** @var string */
    public $createdAt;
    /** @var bool */
    public $hasSpendingPassword;
    /** @var string */
    public $id;
    /** @var string */
    public $name;
    /** @var string|null */
    public $spendingPasswordLastUpdate;
    /** @var WalletSyncState */
    public $syncState;
    /** @var string */
    public $type;

    /**
     * WalletInfo constructor.
     * @param $data
     * @throws API_ResponseException
     */
    public function __construct($data)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $res->payload["data"] ?? null;
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        $this->id = Validate::WalletIdentifier($data["id"] ?? null) ? $data["id"] : null;
        if (!$this->id) {
            throw API_ResponseException::InvalidPropValue("id");
        }

        $this->assuranceLevel = $data["assuranceLevel"] ?? null;
        if (!Validate::AssuranceLevel($this->assuranceLevel)) {
            throw API_ResponseException::InvalidPropValue("wallet.assuranceLevel");
        }

        $this->balance = new LovelaceAmount($data["balance"] ?? null, "wallet.Balance");
        $this->createdAt = $data["createdAt"] ?? null;
        $this->hasSpendingPassword = array_key_exists("hasSpendingPassword", $data) && is_bool($data["hasSpendingPassword"]) ?
            $data["hasSpendingPassword"] : null;
        $this->name = $data["name"] ?? null;
        $this->spendingPasswordLastUpdate = $data["spendingPasswordLastUpdate"] ?? null;
        $this->type = $data["type"] ?? null;

        $syncState = $data["syncState"] ?? null;
        if (!is_array($syncState) || !$syncState) {
            throw API_ResponseException::RequirePropMissing("wallet.syncState");
        }

        $this->syncState = WalletSyncState::Construct($syncState);
    }
}