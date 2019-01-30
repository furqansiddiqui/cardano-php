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
    private $certPassword;
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
     * @param string $pem
     * @param string|null $password
     * @return TLS
     */
    public function certificate(string $pem, ?string $password = null): self
    {
        $this->cert = $pem;
        $this->certPassword = $password;
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
        if (!$this->verify) {
            return $this;
        }

        if ($this->cert) {
            $req->ssl()->certificate($this->cert, $this->certPassword);
            if ($this->ca) {
                $req->ssl()->ca($this->ca);
            }
        }

        return $this;
    }
}