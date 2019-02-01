<?php
declare(strict_types=1);

namespace CardanoSL\Http;

/**
 * Class HttpJSONResponse
 * @package CardanoSL\Http
 */
class HttpJSONResponse
{
    /** @var int */
    public $httpCode;
    /** @var array */
    public $payload;
    /** @var null|string */
    public $body;
    /** @var null|string */
    public $headers;
}