<?php
declare(strict_types=1);

namespace CardanoSL\Exception;

/**
 * Class API_ResponseException
 * @package CardanoSL\Exception
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

    /**
     * @param string $which
     * @param string|null $expected
     * @param string|null $got
     * @return API_ResponseException
     */
    public static function InvalidPropValue(string $which, ?string $expected = null, ?string $got = null): self
    {
        $message = sprintf('Invalid value for prop. "%s"', $which);
        if ($expected) {
            $message .= sprintf('; Expected "%s"', $expected);
            if ($got) {
                $message .= sprintf(' got "%s"', $got);
            }
        }

        return new self($message);
    }
}