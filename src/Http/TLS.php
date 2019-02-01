<?php
declare(strict_types=1);

namespace CardanoSL\Http;

use HttpClient\Request;

/**
 * Class TLS
 * @package CardanoSL\Http
 */
class TLS
{
    /** @var bool */
    private $verify;
    /** @var null|string */
    private $cert;
    /** @var null|string */
    private $privateKey;
    /** @var null|string */
    private $ca;

    /**
     * TLS constructor.
     */
    public function __construct()
    {
        $this->verify = true;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return ["Cardano-SL Node TLS config."];
    }

    /**
     * @param string $cert
     * @param string $privateKey
     * @return TLS
     */
    public function certificate(string $cert, string $privateKey): self
    {
        $this->cert = $cert;
        $this->privateKey = $privateKey;
        return $this;
    }

    /**
     * @param string $file
     * @return TLS
     */
    public function ca(string $file): self
    {
        $this->ca = $file;
        return $this;
    }

    /**
     * @param bool $verify
     * @return TLS
     */
    public function verify(bool $verify): self
    {
        $this->verify = $verify;
        return $this;
    }

    /**
     * @param Request $req
     * @return TLS
     * @throws \HttpClient\Exception\SSLException
     */
    public function apply(Request $req): self
    {
        $req->ssl()->verify($this->verify);

        if ($this->cert) {
            $req->ssl()->certificate($this->cert);
            $req->ssl()->privateKey($this->privateKey);
            if ($this->ca) {
                $req->ssl()->ca($this->ca);
            }
        }

        return $this;
    }
}