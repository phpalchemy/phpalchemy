<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Component\Routing;

/**
 * Class Mapper
 *
 * This class enroutes your http requests and handle them
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
class Mapper
{
    public $routes = array();

    public function __construct()
    {
    }

    public function connect($name, Route $route)
    {
        $this->routes[$name] = $route;
    }

    public function match($pattern)
    {
        foreach ($this->routes as $name => $route) {
            if (($params = $route->match($pattern)) !== false) {
                return $params;
            }
        }

        throw new ResourceNotFoundException($this->urlString);
    }

    public function preOrder()
    {
        //TODO store the first preordering set in cache, it need to order just one time
        foreach ($this->routes as $i => $item) {
            $this->routes[$i]['patCount'] = count(explode('/', $item['route']->getPattern()));
            $this->routes[$i]['reqCount'] = count($item['route']->getRequirements());
            $this->routes[$i]['varCount'] = count($item['route']->getVars());
            $this->routes[$i]['pop'] = trim(array_pop(explode('/', $item['route']->getPattern())));
        }

        $list = $this->routes;
        // first, order by separator number '/'
        usort($list, function ($a, $b)
        {
            if ($b['patCount'] == $a['patCount']) return 0;
            return $b['patCount'] < $a['patCount'] ? 1 : -1;
        });

        $n = count($list);
        for($i = 1; $i < $n; $i++) {
            $j= $i - 1;
            while ($j>=0 && $list[$j]['patCount'] == $list[$i]['patCount'] && $list[$j]['varCount'] > $list[$i]['varCount']) {
                $this->swap_values($list[$j+1],$list[$j]);
                $j--;
            }
        }

        for($i = 1; $i < $n; $i++) {
            $j= $i - 1;
            while ($j>=0 && $list[$j]['patCount'] == $list[$i]['patCount'] && $list[$j]['varCount'] >= $list[$i]['varCount'] && $list[$j]['reqCount'] < $list[$i]['reqCount']) {
                $this->swap_values($list[$j+1],$list[$j]);
                $j--;
            }
        }

        $this->routes = $list;
    }

    public function swap_values(&$a, &$b)
    {
        $x = $a;
        $a = $b;
        $b = $x;
    }
}

