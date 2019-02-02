<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;

/**
 * Class AccountsList
 * @package CardanoSL\Response
 */
class AccountsList implements \Iterator, \Countable, ResponseModelInterface
{
    /** @var int */
    private $pos;
    /** @var int */
    private $count;
    /** @var array */
    private $accounts;
    /** @var HttpJSONResponse\Meta\Pagination */
    private $pagination;

    /**
     * AccountsList constructor.
     * @param HttpJSONResponse $res
     * @throws API_ResponseException
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct(HttpJSONResponse $res)
    {
        $this->pos = 0;
        $this->count = 0;
        $this->accounts = [];
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