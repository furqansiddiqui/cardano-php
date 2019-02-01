<?php
declare(strict_types=1);

namespace CardanoSL\API\Wallets;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\AccountException;
use CardanoSL\Response\AccountInfo;
use CardanoSL\Validate;

/**
 * Class Account
 * @package CardanoSL\API\Wallets
 */
class Account
{
    /** @var CardanoSL */
    private $node;
    /** @var Wallet */
    private $wallet;
    /** @var int */
    private $accountId;
    /** @var null|AccountInfo */
    private $info;

    /**
     * Account constructor.
     * @param CardanoSL $node
     * @param Wallet $wallet
     * @param int $accountId
     * @param bool $preloadInfo
     * @throws AccountException
     */
    public function __construct(CardanoSL $node, Wallet $wallet, int $accountId, bool $preloadInfo = true)
    {
        self::isValidIdentifier($accountId);

        $this->node = $node;
        $this->wallet = $wallet;
        $this->accountId = $accountId;

        if ($preloadInfo) {

        }
    }

    /**
     * @param $accountId
     * @throws AccountException
     */
    public static function isValidIdentifier($accountId): void
    {
        if (!Validate::AccountId($accountId)) {
            throw new AccountException('Invalid account identifier');
        }
    }
}