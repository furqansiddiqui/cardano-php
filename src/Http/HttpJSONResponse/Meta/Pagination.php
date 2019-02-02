<?php
declare(strict_types=1);

namespace CardanoSL\Http\HttpJSONResponse\Meta;

use CardanoSL\Exception\API_ResponseException;

/**
 * Class Pagination
 * @package CardanoSL\Http\HttpJSONResponse\Meta
 */
class Pagination
{
    /** @var int */
    public $totalPages;
    /** @var int */
    public $page;
    /** @var int */
    public $perPage;
    /** @var int */
    public $totalEntries;

    /**
     * @param array $paginationBlock
     * @throws API_ResponseException
     */
    public function populate(array $paginationBlock): void
    {
        foreach (["totalPages", "page", "perPage", "totalEntries"] as $prop) {
            $value = $paginationBlock[$prop] ?? null;
            if (!is_int($value)) {
                throw new API_ResponseException(
                    sprintf('Meta.Pagination required prop "%s" missing/invalid', $prop)
                );
            }

            $this->$prop = $value;
        }
    }
}