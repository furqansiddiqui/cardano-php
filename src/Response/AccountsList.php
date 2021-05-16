<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse\Meta\Pagination;

/**
 * Class AccountsList
 * @package CardanoSL\Response
 */
class AccountsList implements \Iterator, \Countable, ResponseModelInterface
{
    /** @var int */
    private int $pos = 0;
    /** @var int */
    private int $count = 0;
    /** @var array */
    private array $accounts = [];
    /** @var Pagination */
    private Pagination $pagination;

    /**
     * AccountsList constructor.
     * @param HttpJSONResponse $res
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(HttpJSONResponse $res)
    {
        $this->pagination = $res->meta->pagination;

        $accounts = $res->payload["data"] ?? null;
        if (!is_array($accounts)) {
            throw API_ResponseException::RequirePropMissing("accountsList");
        }

        foreach ($accounts as $account) {
            $this->accounts[] = new AccountInfo($account);
            $this->count++;
        }
    }

    /**
     * @return AccountInfo|null
     */
    public function first(): ?AccountInfo
    {
        return $this->accounts[0] ?? null;
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
        return $this->accounts;
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
     * @return AccountInfo
     */
    public function current(): AccountInfo
    {
        return $this->accounts[$this->pos];
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
        return isset($this->accounts[$this->pos]);
    }
}
