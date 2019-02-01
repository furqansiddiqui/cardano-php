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
    private $account;

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
     * @param int $accountIndex
     * @param bool $forceInstanceRefresh
     * @param bool $preloadInfo
     * @return Account
     * @throws \CardanoSL\Exception\AccountException
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
     * @throws \CardanoSL\Exception\AccountException
     */
    public function get(int $accountIndex, bool $forceInstanceRefresh = false, bool $preloadInfo = true): Account
    {
        $this->account = $this->account($accountIndex, $forceInstanceRefresh, $preloadInfo);
        return $this->account;
    }
}