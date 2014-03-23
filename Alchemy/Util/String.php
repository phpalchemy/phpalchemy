<?php
namespace Alchemy\Util\String;

/**
 * Class String
 *
 * @author Erik Amaru Ortiz <aortiz.erik@gmail.com
 * @copyright Copyright 2014 Erik Amaru Ortiz
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package Alchemy\Util\String
 */
class String
{
    /**
     * Converts a camelized string to underscored
     *
     * @param string $str A camelized string
     * @return string
     */
    public static function toUnderscored($str)
    {
        return strtolower(ltrim(preg_replace('/([A-Z])/', '_$1', $str), "_"));
    }

    /**
     * Converts a underscored string to CamelCase
     *
     * @param string $str A underscored string
     * @param bool $firstInLowerCase By true, the first char will be changed to lower case
     * @return string
     */
    public static function toCamelCase($str, $firstInLowerCase = false)
    {
        $str = str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
        return $firstInLowerCase ? lcfirst($str) : $str;
    }
}