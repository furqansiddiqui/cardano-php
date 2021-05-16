<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Http;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse\Meta;

/**
 * Class HttpJSONResponse
 * @package CardanoSL\Http
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
    /** @var Meta */
    public Meta $meta;
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
        $this->meta = new Meta();

        if ($validateAndPopulate) {
            $this->validateAndPopulate();
        }
    }

    /**
     * @return HttpJSONResponse
     * @throws API_ResponseException
     */
    public function validateAndPopulate(): self
    {
        // Check Status
        $this->status = strval($this->payload["status"]);
        $httpCodeStatus = $this->httpCode >= 200 && $this->httpCode < 300;

        // API Error Handling
        if ($this->status !== "success" || !$httpCodeStatus) {
            $msg = $this->payload["message"] ?? null;
            $detailMsg = $this->payload["diagnostic"]["msg"] ?? $this->payload["diagnostic"]["details"]["msg"] ?? null;

            if ($msg && $detailMsg) {
                throw new API_ResponseException(sprintf('[%s]: %s', $msg, $detailMsg), $this->httpCode);
            } elseif ($msg) {
                throw new API_ResponseException(sprintf('Cardano SL API error: %s', $msg), $this->httpCode);
            } else {
                throw new API_ResponseException(
                    sprintf('Cardano SL API call not successful, unknown error, status "%s"', $this->status),
                    $this->httpCode
                );
            }
        }

        // Pagination
        $paginationBlock = $this->payload["meta"]["pagination"] ?? null;
        if (!is_array($paginationBlock)) {
            throw new API_ResponseException('Meta.Pagination block not found');
        }

        $this->meta = new Meta();
        $this->meta->pagination->populate($paginationBlock);

        return $this;
    }

    /**
     * @return array|null
     */
    public function data(): ?array
    {
        return $this->payload["data"] ?? null;
    }

    /**
     * @return Meta
     */
    public function meta(): Meta
    {
        return $this->meta();
    }
}
