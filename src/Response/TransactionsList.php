<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse\Meta\Pagination;

/**
 * Class TransactionsList
 * @package FurqanSiddiqui\Cardano\Response
 */
class TransactionsList implements \Iterator, \Countable, ResponseModelInterface
{
    /** @var int */
    private int $pos = 0;
    /** @var int */
    private int $count = 0;
    /** @var array */
    private array $txs = [];
    /** @var Pagination */
    private Pagination $pagination;

    /**
     * TransactionsList constructor.
     * @param HttpJSONResponse $res
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(HttpJSONResponse $res)
    {
        $this->pagination = $res->meta->pagination;

        $transactions = $res->payload["data"] ?? null;
        if (!is_array($transactions)) {
            throw API_ResponseException::RequirePropMissing("transactionsList");
        }

        foreach ($transactions as $transaction) {
            $this->txs[] = new Transaction($transaction);
            $this->count++;
        }
    }

    /**
     * @return Transaction|null
     */
    public function first(): ?Transaction
    {
        return $this->txs[0] ?? null;
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
        return $this->txs;
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
     * @return Transaction
     */
    public function current(): Transaction
    {
        return $this->txs[$this->pos];
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
        return isset($this->txs[$this->pos]);
    }
}
