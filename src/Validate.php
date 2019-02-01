<?php
declare(strict_types=1);

namespace CardanoSL;

use CardanoSL\Exception\API_Exception;

/**
 * Class Validate
 * @package CardanoSL
 */
class Validate
{
    /**
     * @param $id
     * @return bool
     */
    public static function WalletIdentifier($id): bool
    {
        if (is_string($id) && preg_match('/^[a-zA-Z0-9]{8,128}$/', $id)) {
            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public static function WalletName($name): bool
    {
        return is_string($name) && preg_match('/^[\w\s\.\-]{3,32}$/', $name) ? true : false;
    }

    /**
     * @param $level
     * @return bool
     */
    public static function AssuranceLevel($level): bool
    {
        return is_string($level) && in_array($level, ["normal", "strict"]) ? true : false;
    }

    /**
     * @param $tag
     * @return bool
     */
    public static function SyncStateTag($tag): bool
    {
        return is_string($tag) && in_array($tag, ["restoring", "synced"]) ? true : false;
    }

    /**
     * @param $index
     * @return bool
     */
    public static function AccountIndex($index): bool
    {
        if (!is_int($index)) {
            return false;
        } elseif ($index < CardanoSL::MIN_ACCOUNTS_INDEX || $index > CardanoSL::MAX_ACCOUNTS_INDEX) {
            return false;
        }

        return true;
    }

    /**
     * @param $amount
     * @param string|null $which
     * @return bool
     * @throws API_Exception
     */


    /**
     * @param $amount
     * @param int|null $maxScale
     * @param bool $signed
     * @return bool
     */
    public static function BcAmount($amount, ?int $maxScale = null, bool $signed = false): bool
    {
        if (!is_string($amount)) {
            return false;
        }

        $decimalSign = $maxScale ? '{1,' . $maxScale . '}' : '+';
        $pattern = '[0-9]+(\.[0-9]' . $decimalSign . ')?';
        if ($signed) {
            $pattern = '\-?' . $pattern;
        }

        return preg_match('/^' . $pattern . '$/', $amount) ? true : false;
    }

    /**
     * @param $hash
     * @return bool
     */
    public static function Hash64($hash): bool
    {
        return is_string($hash) && preg_match('/^[a-f0-9]{64}$/i', $hash) ? true : false;
    }
}