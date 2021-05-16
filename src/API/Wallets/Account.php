<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\API\Wallets;

use FurqanSiddiqui\Cardano\API\RawTransaction;
use FurqanSiddiqui\Cardano\Cardano;
use FurqanSiddiqui\Cardano\Exception\AccountException;
use FurqanSiddiqui\Cardano\Exception\TransactionException;
use FurqanSiddiqui\Cardano\Response\AccountInfo;
use FurqanSiddiqui\Cardano\Response\AddressesList;
use FurqanSiddiqui\Cardano\Response\AddressInfo;
use FurqanSiddiqui\Cardano\Response\LovelaceAmount;
use FurqanSiddiqui\Cardano\Response\Transaction;
use FurqanSiddiqui\Cardano\Response\TransactionsList;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class Account
 * @package FurqanSiddiqui\Cardano\API\Wallets
 */
class Account
{
    /** @var Cardano */
    private Cardano $node;
    /** @var Wallet */
    private Wallet $wallet;
    /** @var int */
    private int $accountIndex;
    /** @var null|AccountInfo */
    private ?AccountInfo $info = null;

    /** @var null|bool */
    private ?bool $_isDeleted = null;

    /**
     * Account constructor.
     * @param Cardano $node
     * @param Wallet $wallet
     * @param int $accountIndex
     * @param bool $preloadInfo
     * @param AccountInfo|null $info
     * @throws AccountException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(Cardano $node, Wallet $wallet, int $accountIndex, bool $preloadInfo = true, ?AccountInfo $info = null)
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
     * @return RawTransaction
     */
    public function rawTransaction(): RawTransaction
    {
        return new RawTransaction();
    }

    /**
     * @param RawTransaction $tx
     * @return Transaction
     * @throws TransactionException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function spend(RawTransaction $tx): Transaction
    {
        if ($this->wallet->hasInfoLoaded) {
            if ($this->wallet->info()->hasSpendingPassword) {
                if (!$this->wallet->spendingPassword) {
                    throw new TransactionException('Cannot spend tx, wallet.spendingPassword is not defined');
                }
            }
        }

        $payload = [];

        // Destinations
        $destinations = [];
        foreach ($tx->payees as $payee) {
            $destinations[] = [
                "address" => $payee["address"],
                "amount" => $payee["amount"]
            ];
        }

        if (!$destinations) {
            throw new TransactionException('No payees/destinations');
        }

        $payload["destinations"] = $destinations;

        // Grouping Policy
        if ($tx->groupingPolicy) {
            $payload["groupingPolicy"] = $tx->groupingPolicy;
        }

        // Source
        $payload["source"] = [
            "accountIndex" => $this->accountIndex,
            "walletId" => $this->wallet->id
        ];

        if ($this->wallet->spendingPassword) {
            $payload["spendingPassword"] = $this->wallet->spendingPassword;
        }

        // Send transaction
        $res = $this->node->http()->post('/api/v1/transactions', $payload);
        return new Transaction($res);
    }

    /**
     * @return AddressInfo
     * @throws AccountException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     * @throws \FurqanSiddiqui\Cardano\Exception\WalletException
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
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
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
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
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
        return new TransactionsList($res);
    }

    /**
     * @return LovelaceAmount
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
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
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
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
     * @throws AccountException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
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
