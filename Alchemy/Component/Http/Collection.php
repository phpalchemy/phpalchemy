<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Component\Http;

/**
 * Class Collection
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class Collection
{
    public $data = array();

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function all()
    {
        return $this->data;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function add(array $data = array())
    {
        $this->data = array_replace($this->data, $data);
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}

