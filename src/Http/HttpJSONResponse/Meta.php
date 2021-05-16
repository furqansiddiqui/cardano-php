<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Http\HttpJSONResponse;

use FurqanSiddiqui\Cardano\Http\HttpJSONResponse\Meta\Pagination;

/**
 * Class Meta
 * @package FurqanSiddiqui\Cardano\Http\HttpJSONResponse
 */
class Meta
{
    /** @var Pagination */
    public Pagination $pagination;

    /**
     * Meta constructor.
     */
    public function __construct()
    {
        $this->pagination = new Pagination();
    }
}
