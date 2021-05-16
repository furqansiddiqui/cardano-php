<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Http;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;

/**
 * Class HttpJSONResponse
 * @package FurqanSiddiqui\Cardano\Http
 */
class HttpJSONResponse
{
    /** @var int */
    public int $httpCode;
    /** @var array */
    public array $payload;
    /** @var null|array */
    public ?array $headers;
    /** @var null|string */
    public ?string $body;
    /** @var string */
    public string $status;

    /**
     * HttpJSONResponse constructor.
     * @param int $httpCode
     * @param array $payload
     * @param array|null $headers
     * @param string|null $rawBody
     * @param bool $validateAndPopulate
     * @throws API_ResponseException
     */
    public function __construct(int $httpCode, array $payload, ?array $headers = null, ?string $rawBody = null, bool $validateAndPopulate = true)
    {
        $this->httpCode = $httpCode;
        $this->payload = $payload;
        $this->headers = $headers;
        $this->body = $rawBody;

        if ($validateAndPopulate) {
            // Check Status
            $httpCodeStatus = $this->httpCode >= 200 && $this->httpCode < 300;

            // API Error Handling
            if (!$httpCodeStatus) {
                $error = sprintf('Cardano API call fail; HTTP code %d', $this->httpCode);
                $msgCode = $this->payload["code"] ?? null;
                if (is_string($msgCode)) {
                    $error .= sprintf(' error "%s"', $msgCode);
                }

                throw new API_ResponseException($error);
            }
        }
    }

    /**
     * @return array|null
     */
    public function data(): array
    {
        return $this->payload;
    }
}
