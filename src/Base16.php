<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano;

/**
 * Class Base16
 * @package FurqanSiddiqui\Cardano
 */
class Base16
{
    /**
     * @param string $str
     * @return string
     */
    public static function Encode(string $str): string
    {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $hex .= dechex(ord($str[$i]));
        }

        return $hex;
    }

    /**
     * @param string $hex
     * @return string
     */
    public static function Decode(string $hex): string
    {
        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $str;
    }
}
