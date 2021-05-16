<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\API;

use FurqanSiddiqui\BIP39\BIP39;
use FurqanSiddiqui\BIP39\Mnemonic;
use FurqanSiddiqui\Cardano\API\Wallets\Wallet;
use FurqanSiddiqui\Cardano\Cardano;
use FurqanSiddiqui\Cardano\Exception\API_Exception;
use FurqanSiddiqui\Cardano\Response\WalletInfo;
use FurqanSiddiqui\Cardano\Response\WalletsList;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class Wallets
 * @package FurqanSiddiqui\Cardano\API
 */
class Wallets
{
    /** @var Cardano */
    private Cardano $node;
    /** @var array */
    private array $walletInstances;

    /**
     * Wallets constructor.
     * @param Cardano $node
     */
    public function __construct(Cardano $node)
    {
        $this->node = $node;
        $this->walletInstances = [];
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return ['Wallets API Instance'];
    }

    /**
     * @return WalletsList
     * @throws API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function list(): WalletsList
    {
        return new WalletsList($this->node->http()->get("/v2/wallets"));
    }

    /**
     * @param WalletsList $list
     * @return Wallets
     */
    public function load(WalletsList $list): self
    {
        foreach ($list as $walletInfo) {
            $this->walletInstances[$walletInfo->id] = $walletInfo;
        }

        return $this;
    }

    /**
     * @param string $walletId
     * @param bool $forceInstanceRefresh
     * @return Wallet
     * @throws \FurqanSiddiqui\Cardano\Exception\WalletException
     */
    public function wallet(string $walletId, bool $forceInstanceRefresh = false): Wallet
    {
        Wallet::isValidIdentifier($walletId);

        // Search existing instance
        if (!$forceInstanceRefresh) {
            foreach ($this->walletInstances as $id => $instance) {
                if ($walletId === $id) {
                    return $instance;
                }
            }
        }

        // Create new Wallet API instance
        $wallet = new Wallet($this->node, $walletId);
        $this->walletInstances[$walletId] = $wallet;
        return $wallet;
    }

    /**
     * @param string $walletId
     * @param bool $forceInstanceRefresh
     * @return Wallet
     * @throws \FurqanSiddiqui\Cardano\Exception\WalletException
     */
    public function get(string $walletId, bool $forceInstanceRefresh = false): Wallet
    {
        return $this->wallet($walletId, $forceInstanceRefresh);
    }

    /**
     * @return Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     * @throws \FurqanSiddiqui\BIP39\Exception\WordListException
     */
    public function generateMnemonicCodes(): Mnemonic
    {
        return BIP39::Generate(12);
    }

    /**
     * @param string $name
     * @param Mnemonic $mnemonic
     * @param string|null $password
     * @param bool $hashPassword
     * @return Wallet
     * @throws API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     * @throws \FurqanSiddiqui\Cardano\Exception\WalletException
     */
    public function create(string $name, Mnemonic $mnemonic, ?string $password = null, bool $hashPassword = true): Wallet
    {
        return $this->createOrRestore($name, $mnemonic, $password, $hashPassword);
    }

    /**
     * @param string $name
     * @param Mnemonic $mnemonic
     * @param string|null $password
     * @param bool $hashPassword
     * @return Wallet
     * @throws API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     * @throws \FurqanSiddiqui\Cardano\Exception\WalletException
     */
    public function restore(string $name, Mnemonic $mnemonic, ?string $password = null, bool $hashPassword = true): Wallet
    {
        return $this->createOrRestore($name, $mnemonic, $password, $hashPassword);
    }

    /**
     * @param string $name
     * @param Mnemonic $mnemonic
     * @param string|null $passphrase
     * @param bool $hashPassword
     * @param int $addrPoolGap
     * @return Wallet
     * @throws API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     * @throws \FurqanSiddiqui\Cardano\Exception\WalletException
     * @noinspection PhpSameParameterValueInspection
     */
    private function createOrRestore(string $name, Mnemonic $mnemonic, ?string $passphrase = null, bool $hashPassword = true, int $addrPoolGap = 20): Wallet
    {
        // Name
        $name = trim($name);
        if (!Validate::WalletName($name)) {
            throw new API_Exception('Invalid value for wallet name');
        }

        // Mnemonic
        if (!$mnemonic->entropy) {
            throw new API_Exception('Mnemonic entropy not generated');
        } elseif (!is_array($mnemonic->words) || count($mnemonic->words) < 15 || count($mnemonic->words) > 24) {
            throw new API_Exception(sprintf('Mnemonic codes must be within 15-24, got %d', count($mnemonic->words)));
        }

        // Password
        if ($passphrase) {
            $passphrase = $hashPassword ? hash("sha256", $passphrase, false) : $passphrase;
        }

        // Address Pool Gap
        if ($addrPoolGap < 10 || $addrPoolGap > 10000) {
            throw new API_Exception('Invalid argument for address_pool_gap');
        }

        // Send query
        $payload = [
            "name" => $name,
            "mnemonic_sentence" => $mnemonic->words,
            "passphrase" => $passphrase,
            "address_pool_gap" => $addrPoolGap
        ];

        // Create wallet
        $res = $this->node->http()->post("/v2/wallets", $payload);
        $walletInfo = new WalletInfo($res);
        return new Wallet($this->node, $walletInfo->id, $walletInfo, $mnemonic);
    }
}
