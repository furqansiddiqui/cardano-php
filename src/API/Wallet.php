<?php
declare(strict_types=1);

namespace CardanoSL\API;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\WalletException;
use CardanoSL\Response\WalletInfo;

/**
 * Class Wallet
 * @package CardanoSL\API
 */
class Wallet
{
    /** @var CardanoSL */
    private $node;
    /** @var string */
    private $id;
    /** @var null|WalletInfo */
    private $info;

    /**
     * Wallet constructor.
     * @param CardanoSL $node
     * @param string $id
     */
    public function __construct(CardanoSL $node, string $id)
    {
        $this->node = $node;
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [sprintf('Cardano SL wallet "%s" API instance', $this->id)];
    }

    public function info(bool $forceReload = false)
    {
        if ($this->info && !$forceReload) {
            return $this->info;
        }

        // Get wallet info
        $walletInfo = $this->node->http()->get(sprintf('/api/v1/wallets/%s', $this->id));
        var_dump($walletInfo);
    }

    /**
     * @param $walletId
     * @throws WalletException
     */
    public static function isValidIdentifier($walletId): void
    {
        if (!is_string($walletId) || !preg_match('/^[a-ZA-Z0-9]{8,128}$/', $walletId)) {
            throw new WalletException('Invalid wallet identifier');
        }
    }
}