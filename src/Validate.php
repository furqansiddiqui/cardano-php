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
     * @param $id
     * @return bool
     */
    public static function AccountId($id): bool
    {
        if (!is_int($id)) {
            return false;
        } elseif ($id < CardanoSL::MIN_ACCOUNTS_ID || $id > CardanoSL::MIN_ACCOUNTS_ID) {
            return false;
        }

        return true;
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