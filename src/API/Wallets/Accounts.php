<?php
declare(strict_types=1);

namespace CardanoSL\API\Wallets;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\API_Exception;
use CardanoSL\Response\AccountInfo;
use CardanoSL\Response\AccountsList;

/**
 * Class Accounts
 * @package CardanoSL\API\Wallets
 */
class Accounts
{
    /** @var CardanoSL */
    private $node;
    /** @var Wallet */
    private $wallet;
    /** @var array */
    private $accountInstances;
    /** @var null|int */
    private $firstAccountIndex;

    /**
     * Accounts constructor.
     * @param CardanoSL $node
     * @param Wallet $wallet
     */
    public function __construct(CardanoSL $node, Wallet $wallet)
    {
        $this->node = $node;
        $this->wallet = $wallet;
        $this->accountInstances = [];
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return AccountsList
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function list(int $page = 1, int $perPage = 10): AccountsList
    {
        $payload = [
            "page" => $page,
            "per_page" => $perPage
        ];

        $res = $this->node->http()->get(sprintf('/api/v1/wallets/%s/accounts', $this->wallet->id), $payload);
        $accountsList = new AccountsList($res);
        if ($page === 1) {
            $firstFromList = $accountsList->first();
            if ($firstFromList instanceof AccountInfo) {
                $this->firstAccountIndex = $firstFromList->index;
            }
        }

        return $accountsList;
    }

    /**
     * @return Account
     * @throws API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AccountException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function primary(): Account
    {
        if (!$this->firstAccountIndex) {
            // Retrieve first/primary account
            $this->list(1);
        }

        if (!$this->firstAccountIndex) {
            $smallWalletId = substr($this->wallet->id, 0, 12);
            throw new API_Exception(sprintf('Primary account for wallet "%s..." not defined', $smallWalletId));
        }

        return $this->account($this->firstAccountIndex);
    }

    /**
     * @param int $accountIndex
     * @param bool $forceInstanceRefresh
     * @param bool $preloadInfo
     * @return Account
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AccountException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function account(int $accountIndex, bool $forceInstanceRefresh = false, bool $preloadInfo = true): Account
    {
        Account::isValidIndex($accountIndex);

        // Search existing instance
        if (!$forceInstanceRefresh) {
            foreach ($this->accountInstances as $id => $instance) {
                if ($accountIndex === strval($id)) {
                    return $instance;
                }
            }
        }

        // Create new Account API instance
        $account = new Account($this->node, $this->wallet, $accountIndex, $preloadInfo);
        $this->accountInstances[strval($accountIndex)] = $account;
        return $account;
    }

    /**
     * @param int $accountIndex
     * @param bool $forceInstanceRefresh
     * @param bool $preloadInfo
     * @return Account
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AccountException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function get(int $accountIndex, bool $forceInstanceRefresh = false, bool $preloadInfo = true): Account
    {
        return $this->account($accountIndex, $forceInstanceRefresh, $preloadInfo);
    }
}