<?php
declare(strict_types=1);

namespace CardanoSL\API;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\WalletException;
use CardanoSL\Response\WalletInfo;
use CardanoSL\Validate;

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
     * @param WalletInfo|null $walletInfo
     * @throws WalletException
     */
    public function __construct(CardanoSL $node, string $id, ?WalletInfo $walletInfo = null)
    {
        $this->node = $node;
        $this->id = $id;

        if ($walletInfo) {
            $this->id = $walletInfo->id;
            $this->info = $walletInfo;
        }

        self::isValidIdentifier($this->id);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [sprintf('Cardano SL wallet "%s" API instance', $this->id)];
    }

    /**
     * @param bool $forceReload
     * @return WalletInfo
     * @throws \CardanoSL\Exception\API_ResponseException
     */
    public function info(bool $forceReload = false): WalletInfo
    {
        if ($this->info && !$forceReload) {
            return $this->info;
        }

        // Get wallet info
        $walletInfo = $this->node->http()->get(sprintf('/api/v1/wallets/%s', $this->id));
        return new WalletInfo($walletInfo);
    }

    /**
     * @param $walletId
     * @throws WalletException
     */
    public static function isValidIdentifier($walletId): void
    {
        if (!Validate::WalletIdentifier($walletId)) {
            throw new WalletException('Invalid wallet identifier');
        }
    }
}