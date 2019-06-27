<?php
declare(strict_types=1);

namespace CardanoSL\API\Wallets;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\AccountException;
use CardanoSL\Exception\API_Exception;
use CardanoSL\Response\AccountInfo;
use CardanoSL\Response\AddressesList;
use CardanoSL\Response\AddressInfo;
use CardanoSL\Response\LovelaceAmount;
use CardanoSL\Response\TransactionsList;
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

    /** @var null|bool */
    private $_isDeleted;

    /**
     * Account constructor.
     * @param CardanoSL $node
     * @param Wallet $wallet
     * @param int $accountIndex
     * @param bool $preloadInfo
     * @param AccountInfo|null $info
     * @throws API_Exception
     * @throws AccountException
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct(CardanoSL $node, Wallet $wallet, int $accountIndex, bool $preloadInfo = true, ?AccountInfo $info = null)
    {
        self::isValidIndex($accountIndex);

        $this->node = $node;
        $this->wallet = $wallet;
        $this->accountIndex = $accountIndex;

        if ($preloadInfo) {
            $this->info();
        } else {
            if ($info) {
                $this->info = $info;
            }
        }
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [sprintf('AccountIndex: %d', $this->accountIndex)];
    }

    /**
     * @return int
     */
    public function index(): int
    {
        return $this->accountIndex;
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
                    throw new AccountException('Cannot create address, wallet.spendingPassword is not defined');
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
     * @param int $page
     * @param int $perPage
     * @param string|null $addressFilter
     * @return AddressesList
     * @throws API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     */
    public function addresses(int $page = 1, int $perPage = 10, ?string $addressFilter = null): AddressesList
    {
        $payload = [
            "page" => $page,
            "per_page" => $perPage
        ];

        if ($addressFilter) {
            $payload["address"] = $addressFilter;
        }

        $endpoint = sprintf('/api/v1/wallets/%s/accounts/%d/addresses', $this->wallet->id, $this->accountIndex);
        $res = $this->node->http()->get($endpoint, $payload);
        return new AddressesList($res->payload["data"]["addresses"] ?? null, $res->meta->pagination);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param string|null $idFilter
     * @param string|null $createdAtFilter
     * @param string|null $sortBy
     * @return TransactionsList
     * @throws API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function transactions(int $page = 1, int $perPage = 10, ?string $idFilter = null, ?string $createdAtFilter = null, ?string $sortBy = null): TransactionsList
    {
        $payload = [
            "wallet_id" => $this->wallet->id,
            "account_index" => $this->accountIndex,
            "page" => $page,
            "per_page" => $perPage
        ];

        if ($idFilter) {
            $payload["id"] = $idFilter;
        }

        if ($createdAtFilter) {
            $payload["created_at"] = $createdAtFilter;
        }

        if ($sortBy) {
            $payload["sort_by"] = $sortBy;
        }

        $res = $this->node->http()->get('/api/v1/transactions', $payload);
        $txList = new TransactionsList($res);
        return $txList;
    }

    /**
     * @return LovelaceAmount
     * @throws \CardanoSL\Exception\AmountException
     */
    public function balance(): LovelaceAmount
    {
        $endpoint = sprintf('/api/v1/wallets/%s/accounts/%d/amount', $this->wallet->id, $this->accountIndex);
        $res = $this->node->http()->get($endpoint);
        return new LovelaceAmount($res->payload["data"]["amount"] ?? null, sprintf('account[%d].balance', $this->accountIndex));
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
     * @param string|null $newName
     * @return AccountInfo
     * @throws API_Exception
     * @throws AccountException
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function update(string $newName = null): AccountInfo
    {
        $this->isAccountDeleted();

        if (!Validate::AccountName($newName)) {
            throw new AccountException('New account name is invalid');
        }

        $endpoint = sprintf('/api/v1/wallets/%s/accounts/%d', $this->wallet->id, $this->accountIndex);
        $payload = [
            "name" => $newName
        ];

        $res = $this->node->http()->put($endpoint, $payload);
        $this->info = new AccountInfo($res);
        return $this->info;
    }

    /**
     * @return void
     */
    public function delete(): void
    {
        $endpoint = sprintf('/api/v1/wallets/%s/accounts/%d', $this->wallet->id, $this->accountIndex);
        $this->node->http()->delete($endpoint);
        $this->_isDeleted = true;
    }

    /**
     * @throws AccountException
     */
    private function isAccountDeleted(): void
    {
        if ($this->_isDeleted) {
            throw new AccountException(sprintf('Account "%d" is deleted, cannot perform requested op', $this->accountIndex));
        }
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