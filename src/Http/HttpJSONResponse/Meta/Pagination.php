<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Http\HttpJSONResponse\Meta;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;

/**
 * Class Pagination
 * @package FurqanSiddiqui\Cardano\Http\HttpJSONResponse\Meta
 */
class Pagination
{
    /** @var int */
    public int $totalPages;
    /** @var int */
    public int $page;
    /** @var int */
    public int $perPage;
    /** @var int */
    public int $totalEntries;

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
