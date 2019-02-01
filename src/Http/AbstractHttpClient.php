<?php
declare(strict_types=1);

namespace CardanoSL\Http;

/**
 * Class AbstractHttpClient
 * @package CardanoSL\Http
 */
abstract class AbstractHttpClient
{
    /** @var string */
    protected $host;
    /** @var int */
    protected $port;
    /** @var null|TLS */
    protected $tls;

    /**
     * AbstractHttpClient constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return TLS
     */
    public function TLS(): TLS
    {
        if (!$this->tls) {
            $this->tls = new TLS();
        }

        return $this->tls;
    }

    /**
     * @return AbstractHttpClient
     */
    public function noTLS(): self
    {
        $this->tls = null;
        return $this;
    }

    /**
     * @param string $endpoint
     * @return string
     */
    protected function url(string $endpoint = ""): string
    {
        return sprintf(
            '%s://%s:%d/%s',
            $this->tls ? "https" : "http",
            $this->host,
            $this->port,
            ltrim($endpoint, "/")
        );
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array|null $payload
     * @return HttpJSONResponse
     */
    abstract public function call(string $method, string $endpoint, ?array $payload = null): HttpJSONResponse;

    /**
     * @param string $endpoint
     * @param array|null $payload
     * @return HttpJSONResponse
     */
    public function get(string $endpoint, ?array $payload = null): HttpJSONResponse
    {
        return $this->call("GET", $endpoint, $payload);
    }

    /**
     * @param string $endpoint
     * @param array|null $payload
     * @return HttpJSONResponse
     */
    public function post(string $endpoint, ?array $payload = null): HttpJSONResponse
    {
        return $this->call("POST", $endpoint, $payload);
    }

    /**
     * @param string $endpoint
     * @param array|null $payload
     * @return HttpJSONResponse
     */
    public function put(string $endpoint, ?array $payload = null): HttpJSONResponse
    {
        return $this->call("PUT", $endpoint, $payload);
    }

    /**
     * @param string $endpoint
     * @param array|null $payload
     * @return HttpJSONResponse
     */
    public function delete(string $endpoint, ?array $payload = null): HttpJSONResponse
    {
        return $this->call("DELETE", $endpoint, $payload);
    }
}