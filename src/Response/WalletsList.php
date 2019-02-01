<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;

/**
 * Class WalletsList
 * @package CardanoSL\Response
 */
class WalletsList implements \Iterator, \Countable, ResponseModelInterface
{
    /** @var int */
    private $pos;
    /** @var int */
    private $count;
    /** @var array */
    private $wallets;
    /** @var HttpJSONResponse\Meta\Pagination */
    private $pagination;

    /**
     * WalletsList constructor.
     * @param HttpJSONResponse $res
     * @throws API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct(HttpJSONResponse $res)
    {
        $this->pos = 0;
        $this->count = 0;
        $this->wallets = [];
        $this->pagination = $res->meta->pagination;

        $wallets = $res->payload["data"] ?? null;
        if (!is_array($wallets)) {
            throw API_ResponseException::RequirePropMissing("walletsList");
        }

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
     * @return HttpJSONResponse\Meta\Pagination
     */
    public function pagination(): HttpJSONResponse\Meta\Pagination
    {
        return $this->pagination;
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