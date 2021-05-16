<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano;

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
     * @param $addr
     * @return bool
     */
    public static function Address($addr): bool
    {
        if (is_string($addr) && preg_match('/^[a-zA-Z0-9]{8,256}$/', $addr)) {
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
        return is_string($name) && preg_match('/^[\w\s.\-]{3,32}$/', $name);
    }

    /**
     * @param $level
     * @return bool
     */
    public static function AssuranceLevel($level): bool
    {
        return is_string($level) && in_array($level, ["normal", "strict"]);
    }

    /**
     * @param $tag
     * @return bool
     */
    public static function SyncStateTag($tag): bool
    {
        return is_string($tag) && in_array($tag, ["restoring", "synced"]);
    }

    /**
     * @param $ownership
     * @return bool
     */
    public static function AddressOwnership($ownership): bool
    {
        return is_string($ownership) && in_array($ownership, ["isOurs", "ambiguousOwnership"]);
    }

    /**
     * @param $index
     * @return bool
     */
    public static function AccountIndex($index): bool
    {
        if (!is_int($index)) {
            return false;
        } elseif ($index < Cardano::MIN_ACCOUNTS_INDEX || $index > Cardano::MAX_ACCOUNTS_INDEX) {
            return false;
        }

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    public static function AccountName($name): bool
    {
        return is_string($name) && preg_match('/^[\w\s.\-:]{1,32}$/', $name);
    }

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
            /** @noinspection RegExpRedundantEscape */
            $pattern = '\-?' . $pattern;
        }

        return preg_match('/^' . $pattern . '$/', $amount);
    }

    /**
     * @param $hash
     * @return bool
     */
    public static function Hash64($hash): bool
    {
        return is_string($hash) && preg_match('/^[a-f0-9]{64}$/i', $hash);
    }
}
