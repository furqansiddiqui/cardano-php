<?php
declare(strict_types=1);

namespace CardanoSL\API;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\API_Exception;
use CardanoSL\Response\WalletInfo;
use CardanoSL\Validate;
use furqansiddiqui\BIP39\BIP39;
use furqansiddiqui\BIP39\Mnemonic;

/**
 * Class Wallets
 * @package CardanoSL\API
 */
class Wallets
{
    /** @var CardanoSL */
    private $node;
    /** @var array */
    private $walletInstances;

    /**
     * Wallets constructor.
     * @param CardanoSL $node
     */
    public function __construct(CardanoSL $node)
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
     * @param string $walletId
     * @param bool $forceInstanceRefresh
     * @return Wallet
     * @throws \CardanoSL\Exception\WalletException
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
     * @throws \CardanoSL\Exception\WalletException
     */
    public function get(string $walletId, bool $forceInstanceRefresh = false): Wallet
    {
        return $this->wallet($walletId, $forceInstanceRefresh);
    }

    /**
     * @return Mnemonic
     * @throws \furqansiddiqui\BIP39\Exception\MnemonicException
     * @throws \furqansiddiqui\BIP39\Exception\WordlistException
     */
    public function generateMnemonicCodes(): Mnemonic
    {
        return BIP39::Generate(12);
    }

    /**
     * @param string $name
     * @param Mnemonic $mnemonic
     * @param string|null $password
     * @param string $assuranceLevel
     * @param bool $hashPassword
     * @return Wallet
     * @throws API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\WalletException
     */
    public function create(string $name, Mnemonic $mnemonic, ?string $password = null, string $assuranceLevel = "normal", bool $hashPassword = true): Wallet
    {
        return $this->createOrRestore("create", $name, $mnemonic, $password, $assuranceLevel, $hashPassword);
    }

    /**
     * @param string $name
     * @param Mnemonic $mnemonic
     * @param string|null $password
     * @param string $assuranceLevel
     * @param bool $hashPassword
     * @return Wallet
     * @throws API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\WalletException
     */
    public function restore(string $name, Mnemonic $mnemonic, ?string $password = null, string $assuranceLevel = "normal", bool $hashPassword = true): Wallet
    {
        return $this->createOrRestore("restore", $name, $mnemonic, $password, $assuranceLevel, $hashPassword);
    }

    /**
     * @param string $op
     * @param string $name
     * @param Mnemonic $mnemonic
     * @param string|null $password
     * @param string $assuranceLevel
     * @param bool $hashPassword
     * @return Wallet
     * @throws API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\WalletException
     */
    private function createOrRestore(string $op, string $name, Mnemonic $mnemonic, ?string $password = null, string $assuranceLevel = "normal", bool $hashPassword = true): Wallet
    {
        if (!Validate::AssuranceLevel($assuranceLevel)) {
            throw new API_Exception('Invalid assuranceLevel value');
        }

        // Name
        $name = trim($name);
        if (!Validate::WalletName($name)) {
            throw new API_Exception('Invalid value for wallet name');
        }

        // Mnemonic
        if (!$mnemonic->entropy) {
            throw new API_Exception('Mnemonic entropy not generated');
        } elseif (!is_array($mnemonic->words) || count($mnemonic->words) !== 12) {
            throw new API_Exception(sprintf('Mnemonic codes count must be precise 12, got %d', count($mnemonic->words)));
        }

        // Password
        if ($password) {
            $encodedPassword = $hashPassword ? hash("sha256", $password) : $password;
            if (!Validate::Hash64($encodedPassword)) {
                throw new API_Exception('spendingPassword must be 32 byte hexadecimal string (64 hexits)');
            }
        }

        // Send query
        $payload = [
            "assuranceLevel" => $assuranceLevel,
            "backupPhrase" => $mnemonic->words,
            "name" => $name,
            "operation" => $op
        ];

        if (isset($encodedPassword)) {
            $payload["spendingPassword"] = $encodedPassword;
        }

        // Create wallet
        $res = $this->node->http()->post("/api/v1/wallets", $payload);
        $walletInfo = new WalletInfo($res);
        return new Wallet($this->node, $walletInfo->id, $walletInfo, $mnemonic);
    }
}