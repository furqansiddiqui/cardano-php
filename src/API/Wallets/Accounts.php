<?php
declare(strict_types=1);

namespace CardanoSL\API\Wallets;

use CardanoSL\CardanoSL;

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
     * @param int $accountId
     * @param bool $forceInstanceRefresh
     * @param bool $preloadInfo
     * @return Account
     * @throws \CardanoSL\Exception\AccountException
     */
    public function account(int $accountId, bool $forceInstanceRefresh = false, bool $preloadInfo = true): Account
    {
        Account::isValidIdentifier($accountId);

        // Search existing instance
        if (!$forceInstanceRefresh) {
            foreach ($this->accountInstances as $id => $instance) {
                if ($accountId === $id) {
                    return $instance;
                }
            }
        }

        // Create new Account API instance
        $account = new Account($this->node, $this->wallet, $accountId, $preloadInfo);
        $this->accountInstances[$accountId] = $account;
        return $account;
    }

    /**
     * @param int $accountId
     * @param bool $forceInstanceRefresh
     * @param bool $preloadInfo
     * @return Account
     * @throws \CardanoSL\Exception\AccountException
     */
    public function get(int $accountId, bool $forceInstanceRefresh = false, bool $preloadInfo = true): Account
    {
        return $this->account($accountId, $forceInstanceRefresh, $preloadInfo);
    }
}