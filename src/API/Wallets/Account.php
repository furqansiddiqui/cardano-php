<?php
declare(strict_types=1);

namespace CardanoSL\API\Wallets;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\AccountException;
use CardanoSL\Exception\API_Exception;
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
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct(CardanoSL $node, Wallet $wallet, int $accountIndex, bool $preloadInfo = true)
    {
        self::isValidIndex($accountIndex);

        $this->node = $node;
        $this->wallet = $wallet;
        $this->accountIndex = $accountIndex;

        if ($preloadInfo) {
            $this->info();
        }
    }

    /**
     * @return AddressInfo
     * @throws API_Exception
     * @throws AccountException
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     * @throws \CardanoSL\Exception\WalletException
     */
    public function createAddress(): AddressInfo
    {
        if ($this->wallet->hasInfoLoaded) {
            if ($this->wallet->info()->hasSpendingPassword) {
                if (!$this->wallet->spendingPassword) {
                    throw new AccountException('Cannot create account, wallet.spendingPassword is not defined');
                }
            }
        }

        $payload = [
            "accountIndex" => $this->accountIndex,
            "walletId" => $this->wallet->id
        ];

        if ($this->wallet->spendingPassword) {
            $payload["spendingPassword"] = $this->wallet->spendingPassword;
        }

        $res = $this->node->http()->post('/api/v1/addresses', $payload);
        return new AddressInfo($res);
    }

    /**
     * @param bool $forceReload
     * @return AccountInfo
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function info(bool $forceReload = false): AccountInfo
    {
        if ($this->info && !$forceReload) {
            return $this->info;
        }

        $endpoint = sprintf('/api/v1/wallets/%s/accounts/%d', $this->wallet->id, $this->accountIndex);
        $res = $this->node->http()->get($endpoint);
        $this->info = new AccountInfo($res, $res->meta->pagination);
        return $this->info;
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