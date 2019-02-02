<?php
declare(strict_types=1);

namespace CardanoSL\Http\HttpJSONResponse;

use CardanoSL\Http\HttpJSONResponse\Meta\Pagination;

/**
 * Class Meta
 * @package CardanoSL\Http\HttpJSONResponse
 */
class Meta
{
    /** @var Pagination */
    public $pagination;

    /**
     * Meta constructor.
     */
    public function __construct()
    {
        $this->pagination = new Pagination();
    }
}