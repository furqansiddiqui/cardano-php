<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;

/**
 * Class TransactionsList
 * @package CardanoSL\Response
 */
class TransactionsList implements \Iterator, \Countable, ResponseModelInterface
{
    /** @var int */
    private $pos;
    /** @var int */
    private $count;
    /** @var array */
    private $txs;
    /** @var HttpJSONResponse\Meta\Pagination */
    private $pagination;

    /**
     * TransactionsList constructor.
     * @param HttpJSONResponse $res
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\API_ResponseException
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct(HttpJSONResponse $res)
    {
        $this->pos = 0;
        $this->count = 0;
        $this->txs = [];
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