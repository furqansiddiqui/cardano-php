<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;

/**
 * Class TxStatus
 * @package CardanoSL\Response
 */
class TxStatus
{
    /** @var mixed|null */
    public $data;
    /** @var string */
    public $tag;

    /**
     * TxStatus constructor.
     * @param array $data
     * @throws \CardanoSL\Exception\API_Exception
     */
    public function __construct(array $data)
    {
        $this->data = $data["data"] ?? null;
        $this->tag = strtolower(strval($data["tag"] ?? ""));
        if (!in_array($this->tag, ["applying", "innewestblocks", "persisted", "wontapply", "creating"])) {
            throw API_ResponseException::InvalidPropValue("txStatus.tag");
        }
    }
}