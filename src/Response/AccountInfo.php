<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;

/**
 * Class AccountInfo
 * @package CardanoSL\Response
 */
class AccountInfo
{

    public function __construct($data)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $res->payload["data"] ?? null;
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }
    }
}