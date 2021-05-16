<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;

/**
 * Class WalletsList
 * @package FurqanSiddiqui\Cardano\Response
 */
class WalletsList implements \Iterator, \Countable, ResponseModelInterface
{
    /** @var int */
    private int $pos = 0;
    /** @var int */
    private int $count = 0;
    /** @var array */
    private array $wallets = [];

    /**
     * WalletsList constructor.
     * @param HttpJSONResponse $res
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(HttpJSONResponse $res)
    {
        $wallets = $res->data();
        foreach ($wallets as $wallet) {
            $this->wallets[] = new WalletInfo($wallet);
            $this->count++;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->wallets;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->pos = 0;
    }

    /**
     * @return WalletInfo
     */
    public function current(): WalletInfo
    {
        return $this->wallets[$this->pos];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->pos;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->pos;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->wallets[$this->pos]);
    }
}
