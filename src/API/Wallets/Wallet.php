<?php
/** @noinspection PhpUnusedPrivateFieldInspection */
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\API\Wallets;

use FurqanSiddiqui\BIP39\Mnemonic;
use FurqanSiddiqui\Cardano\Cardano;
use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Exception\WalletException;
use FurqanSiddiqui\Cardano\Response\TransactionsList;
use FurqanSiddiqui\Cardano\Response\WalletInfo;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class Wallet
 * @package FurqanSiddiqui\Cardano\API\Wallet
 * @property-read null|string $spendingPassword
 * @property-read string $id
 * @property-read bool $hasInfoLoaded
 */
class Wallet
{
    /** @var Cardano */
    private Cardano $node;
    /** @var string */
    private string $id;
    /** @var null|WalletInfo */
    private ?WalletInfo $info = null;
    /** @var null|Mnemonic */
    private ?Mnemonic $mnemonic = null;
    /** @var null|Accounts */
    private ?Accounts $accounts = null;
    /** @var null|string */
    private ?string $spendingPassword = null;

    /** @var null|bool */
    private ?bool $_isDeleted = null;

    /**
     * Wallet constructor.
     * @param Cardano $node
     * @param string $id
     * @param WalletInfo|null $walletInfo
     * @param Mnemonic|null $mnemonic
     * @throws WalletException
     */
    public function __construct(Cardano $node, string $id, ?WalletInfo $walletInfo = null, ?Mnemonic $mnemonic = null)
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
                return isset($this->info);
        }

        throw new WalletException(sprintf('Cannot access unreadable prop "%s"', $prop));
    }

    /**
     * @return $this
     * @throws API_ResponseException
     * @throws WalletException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function load(): self
    {
        $this->info();
        return $this;
    }

    /**
     * @param bool $forceReload
     * @return WalletInfo
     * @throws WalletException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function info(bool $forceReload = false): WalletInfo
    {
        $this->isWalletDeleted();

        if ($this->info && !$forceReload) {
            return $this->info;
        }

        // Get wallet info
        $walletInfo = $this->node->http()->get(sprintf('/api/v1/wallets/%s', $this->id));
        $this->info = new WalletInfo($walletInfo);
        return $this->info;
    }

    /**
     * @param string $assuranceLevel
     * @param string $walletName
     * @return WalletInfo
     * @throws API_ResponseException
     * @throws WalletException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
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
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function changePassword(string $newPassword, string $oldPassword, bool $hashPasswords = true): WalletInfo
    {
        $this->isWalletDeleted();

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
     * @throws WalletException
     */
    public function accounts(): Accounts
    {
        $this->isWalletDeleted();

        if ($this->accounts) {
            return $this->accounts;
        }

        $this->accounts = new Accounts($this->node, $this);
        return $this->accounts;
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param string|null $idFilter
     * @param string|null $createdAtFilter
     * @param string|null $sortBy
     * @return TransactionsList
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function transactions(int $page = 1, int $perPage = 10, ?string $idFilter = null, ?string $createdAtFilter = null, ?string $sortBy = null): TransactionsList
    {
        $payload = [
            "wallet_id" => $this->id,
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
     * @param int $accountIndex
     * @return Account
     * @throws API_ResponseException
     * @throws WalletException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AccountException
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function account(int $accountIndex): Account
    {
        $this->isWalletDeleted();
        return $this->accounts()->get($accountIndex);
    }

    /**
     * @param string $password
     * @param bool $hashPassword
     * @return Wallet
     * @throws WalletException
     */
    public function spendingPassword(string $password, bool $hashPassword = true): self
    {
        $this->isWalletDeleted();

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
