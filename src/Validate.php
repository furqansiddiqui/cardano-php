<?php
declare(strict_types=1);

namespace CardanoSL;

/**
 * Class Validate
 * @package CardanoSL
 */
class Validate
{
    /**
     * @param $hash
     * @return bool
     */
    public static function Hash64($hash): bool
    {
        return is_string($hash) && preg_match('/^[a-f0-9]{64}$/i', $hash) ? true : false;
    }
}