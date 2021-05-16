<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse\Meta\Pagination;

/**
 * Class AddressesList
 * @package FurqanSiddiqui\Cardano\Response
 */
class AddressesList implements \Iterator, \Countable, ResponseModelInterface
{
    /** @var int */
    private int $pos = 0;
    /** @var int */
    private int $count = 0;
    /** @var array */
    private array $addresses = [];
    /** @var Pagination|null */
    private ?Pagination $pagination;

    /**
     * AddressesList constructor.
     * @param $list
     * @param Pagination|null $pagination
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     */
    public function __construct($list, ?Pagination $pagination = null)
    {
        $this->pagination = $pagination;

        if (!is_array($list)) {
            throw new API_ResponseException('AddressesList first argument must be an Array');
        }

        foreach ($list as $addrInfo) {
            $this->addresses[] = new AddressInfo($addrInfo);
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
        return $this->addresses;
    }

    /**
     * @return Pagination|null
     */
    public function pagination(): ?Pagination
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
     * @return AddressInfo
     */
    public function current(): AddressInfo
    {
        return $this->addresses[$this->pos];
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
        return isset($this->addresses[$this->pos]);
    }
}
