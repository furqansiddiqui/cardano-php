<?php
declare(strict_types=1);

namespace CardanoSL\API\Wallets;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\AccountException;
use CardanoSL\Response\AccountInfo;
use CardanoSL\Response\AddressInfo;
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
    private $accountIndex;
    /** @var null|AccountInfo */
    private $info;

    /**
     * Account constructor.
     * @param CardanoSL $node
     * @param Wallet $wallet
     * @param int $accountIndex
     * @param bool $preloadInfo
     * @throws AccountException
     */
    public function __construct(CardanoSL $node, Wallet $wallet, int $accountIndex, bool $preloadInfo = true)
    {
        self::isValidIndex($accountIndex);

        $this->node = $node;
        $this->wallet = $wallet;
        $this->accountIndex = $accountIndex;

        if ($preloadInfo) {

        }
    }

    public function createAddress(): AddressInfo
    {

    }

    /**
     * @param $accountIndex
     * @throws AccountException
     */
    public static function isValidIndex($accountIndex): void
    {
        if (!Validate::AccountIndex($accountIndex)) {
            throw new AccountException('Invalid account index');
        }
    }
}