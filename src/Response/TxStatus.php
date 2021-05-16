<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;

/**
 * Class TxStatus
 * @package FurqanSiddiqui\Cardano\Response
 */
class TxStatus
{
    /** @var mixed|null */
    public $data;
    /** @var string */
    public string $tag;

    /**
     * TxStatus constructor.
     * @param array $data
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     */
    public function __construct(array $data)
    {
        $this->data = $data["data"] ?? null;
        $tag = strtolower(strval($data["tag"] ?? ""));
        if (!in_array($tag, ["applying", "innewestblocks", "persisted", "wontapply", "creating"])) {
            throw API_ResponseException::InvalidPropValue("txStatus.tag");
        }

        $this->tag = $tag;
    }
}
