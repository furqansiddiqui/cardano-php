<?php
declare(strict_types=1);

namespace CardanoSL\API;

use CardanoSL\Base16;
use CardanoSL\CardanoSL;
use CardanoSL\Exception\API_Exception;
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

    public function create(string $name, Mnemonic $mnemonic, ?string $password = null, string $assuranceLevel = "normal", bool $encodePasswordBase16 = true)
    {
        return $this->createOrRestore("create", $name, $mnemonic, $password, $assuranceLevel, $encodePasswordBase16);
    }

    private function createOrRestore(string $op, string $name, Mnemonic $mnemonic, ?string $password = null, string $assuranceLevel = "normal", bool $encodePasswordBase16 = true)
    {
        if (!in_array($assuranceLevel, ["normal", "strict"])) {
            throw new API_Exception('Invalid assuranceLevel value');
        }

        // Name
        $name = trim($name);
        if (!preg_match('/^[\w\s\.\-]{3,32}$/', $name)) {
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
            if (!$encodePasswordBase16) {
                $encodedPassword = $password;
            } else {
                $encodedPassword = Base16::Encode($password);
            }

            if (!preg_match('/^[a-f0-9]{16,64}$/', $encodedPassword)) {
                throw new API_Exception('Invalid Base16/Hex encoded password, or length not within 16-64 hexits');
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


    }
}