<?php
declare(strict_types=1);

namespace CardanoSL\Http {

    use CardanoSL\Exception\API_ResponseException;
    use CardanoSL\Http\HttpJSONResponse\Meta;

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
        public $headers;
        /** @var null|string */
        public $body;
        /** @var Meta */
        public $meta;
        /** @var string */
        public $status;

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
            $this->status = $this->payload["status"] ?? null;
            $httpCodeStatus = $this->httpCode >= 200 && $this->httpCode < 300 ? true : false;

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
}

namespace CardanoSL\Http\HttpJSONResponse {

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
}

namespace CardanoSL\Http\HttpJSONResponse\Meta {

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
}