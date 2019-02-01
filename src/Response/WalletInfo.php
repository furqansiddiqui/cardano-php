<?php
declare(strict_types=1);

namespace CardanoSL\Response;

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
}