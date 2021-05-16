<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Exception;

/**
 * Class API_ResponseException
 * @package FurqanSiddiqui\Cardano\Exception
 */
class API_ResponseException extends API_Exception
{
    /**
     * @param string $which
     * @return API_ResponseException
     */
    public static function RequirePropMissing(string $which): self
    {
        return new self(sprintf('Required prop. "%s" not found in API response', $which));
    }
}
