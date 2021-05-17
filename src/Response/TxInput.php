<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;

/**
 * Class TxInput
 * @package FurqanSiddiqui\Cardano\Response
 */
class TxInput extends TxOutput
{
    /** @var string */
    public string $id;
    /** @var int */
    public int $index;

    /**
     * TxInput constructor.
     * @param array $data
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $id = $data["id"];
        if (!is_string($id) || !preg_match('/^[a-f0-9]{64}$/i', $id)) {
            throw API_ResponseException::InvalidPropValue("txInput.id", "Hash64");
        }

        $this->id = $id;

        $index = $data["index"];
        if (!is_int($index) || $index < 0) {
            throw API_ResponseException::InvalidPropValue("txInput.index", "Uint", gettype($index));
        }

        $this->index = $index;
    }
}
