<?php
declare(strict_types=1);

namespace CardanoSL\Exception;

/**
 * Class API_Exception
 * @package CardanoSL\Exception
 */
class API_Exception extends CardanoSLException
{
    /**
     * @param string $which
     * @param string|null $expected
     * @param string|null $got
     * @return API_Exception
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