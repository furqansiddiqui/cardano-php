<?php
declare(strict_types=1);

namespace CardanoSL\API\Wallets;

use CardanoSL\CardanoSL;
use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Exception\WalletException;
use CardanoSL\Response\WalletInfo;
use CardanoSL\Validate;
use furqansiddiqui\BIP39\Mnemonic;

/**
 * Class Wallet
 * @package CardanoSL\API\Wallets
 * @property-read string $id
 * @property-read null|string $spendingPassword
 * @property-read bool $hasInfoLoaded
 */
class Wallet
{
    /** @var CardanoSL */
    private $node;
    /** @var string */
    private $id;
    /** @var null|WalletInfo */
    private $info;
    /** @var null|Mnemonic */
    private $mnemonic;
    /** @var null|Accounts */
    private $accounts;
    /** @var null|string */
    private $spendingPassword;

    /** @var null|bool */
    private $_isDeleted;

    /**
     * Wallet constructor.
     * @param CardanoSL $node
     * @param string $id
     * @param WalletInfo|null $walletInfo
     * @param Mnemonic|null $mnemonic
     * @throws WalletException
     */
    public function __construct(CardanoSL $node, string $id, ?WalletInfo $walletInfo = null, ?Mnemonic $mnemonic = null)
    {
        $this->node = $node;
        $this->id = $id;

        if ($walletInfo) {
            $this->id = $walletInfo->id;
            $this->info = $walletInfo;
            $this->mnemonic = $mnemonic;
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
     * @param string $prop
     * @return string|null
     * @throws WalletException
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "spendingPassword":
                return $this->spendingPassword;
            case "id":
                return $this->id;
            case "hasInfoLoaded":
                return $this->info ? true : false;
        }

        throw new WalletException(sprintf('Cannot access unreadable prop "%s"', $prop));
    }

    /**
     * @param bool $forceReload
     * @return WalletInfo
     * @throws API_ResponseException
     * @throws WalletException
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AmountException
     */
    public function info(bool $forceReload = false): WalletInfo
    {
        $this->isWalletDeleted();

        if ($this->info && !$forceReload) {
            return $this->info;
        }

        // Get wallet info
        $walletInfo = $this->node->http()->get(sprintf('/api/v1/wallets/%s', $this->id));
        return new WalletInfo($walletInfo);
    }

    /**
     * @param string $assuranceLevel
     * @param string $walletName
     * @return WalletInfo
     * @throws API_ResponseException
     * @throws WalletException
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AmountException
     */
    public function update(string $assuranceLevel, string $walletName): WalletInfo
    {
        $this->isWalletDeleted();

        if (!Validate::AssuranceLevel($assuranceLevel)) {
            throw API_ResponseException::InvalidPropValue("assuranceLevel");
        }

        if (!Validate::WalletName($walletName)) {
            throw API_ResponseException::InvalidPropValue("walletName");
        }

        $payload = [
            "assuranceLevel" => $assuranceLevel,
            "name" => $walletName
        ];

        $update = $this->node->http()->put(sprintf('/api/v1/wallets/%s', $this->id), $payload);
        $this->info = new WalletInfo($update);
        return $this->info;
    }

    /**
     * @throws WalletException
     */
    public function delete(): void
    {
        $this->isWalletDeleted();

        $this->node->http()->delete(sprintf('/api/v1/wallets/%s', $this->id));
        $this->_isDeleted = true;
    }

    /**
     * @param string $newPassword
     * @param string $oldPassword
     * @param bool $hashPasswords
     * @return WalletInfo
     * @throws API_ResponseException
     * @throws WalletException
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AmountException
     */
    public function changePassword(string $newPassword, string $oldPassword, bool $hashPasswords = true): WalletInfo
    {
        $encodedNewPassword = $hashPasswords ? hash("sha256", $newPassword) : $newPassword;
        if (!Validate::Hash64($encodedNewPassword)) {
            throw new WalletException('newPassword must be 32 byte hexadecimal string (64 hexits)');
        }

        $encodedOldPassword = "";
        if ($oldPassword) { // If no password is passed, just send an empty string ""
            if ($hashPasswords) {
                $encodedOldPassword = hash("sha256", $oldPassword);
            }

            if (!Validate::Hash64($encodedOldPassword)) {
                throw new WalletException('oldPassword must be 32 byte hexadecimal string (64 hexits)');
            }
        }

        $payload = [
            "new" => $encodedNewPassword,
            "old" => $encodedOldPassword
        ];

        $req = $this->node->http()->put(sprintf('/api/v1/wallets/%s/password', $this->id), $payload);
        $this->info = new WalletInfo($req);
        return $this->info;
    }

    /**
     * ONLY return Mnemonic object for instances result of create/restore operations
     * @return null|Mnemonic
     */
    public function mnemonic(): ?Mnemonic
    {
        return $this->mnemonic;
    }

    /**
     * @return Accounts
     */
    public function accounts(): Accounts
    {
        if ($this->accounts) {
            return $this->accounts;
        }

        $this->accounts = new Accounts($this->node, $this);
        return $this->accounts;
    }

    /**
     * @param int $accountIndex
     * @return Account
     * @throws API_ResponseException
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AccountException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function account(int $accountIndex): Account
    {
        return $this->accounts->get($accountIndex);
    }

    /**
     * @param string $password
     * @param bool $hashPassword
     * @return Wallet
     * @throws WalletException
     */
    public function spendingPassword(string $password, bool $hashPassword = true): self
    {
        $encodedPassword = $hashPassword ? hash("sha256", $password) : $password;
        if (!Validate::Hash64($encodedPassword)) {
            throw new WalletException('spendingPassword must be 32 byte hexadecimal string (64 hexits)');
        }

        $this->spendingPassword = $encodedPassword;
        return $this;
    }

    /**
     * @throws WalletException
     */
    private function isWalletDeleted(): void
    {
        if ($this->_isDeleted) {
            throw new WalletException(sprintf('Wallet "%s" is deleted, cannot perform requested op', $this->id));
        }
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