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
 * Class that enroutes your http request and handle them
 *
 * @author Erik Amaru ortiz <aortiz.erik@gmail.com.org>
 * @version 1.0
 * @package Routing
 */

class Mapper
{
    public $routeList; // routes list
    public $routePreferredList;
    public $route; // for matched route

    public function __construct()
    {
        $this->routeList          = Array();
        $this->routePreferredList = Array();
    }

    public function connect($name, $pattern, $defaults = null, $requirements = null, $type = null, $resourcePath = null)
    {
        if ($type == 'resource') {
            $this->routePreferredList[] = array(
                'name'  => $name,
                'route' => new Route($pattern, $defaults, $requirements, $type, $resourcePath)
            );
        } else {
            $this->routeList[] = array(
                'name'  => $name,
                'route' => new Route($pattern, $defaults, $requirements)
            );
        }
    }

    public function match($pattern)
    {
        foreach ($this->routePreferredList as $item) {
            if ($item['route']->match($pattern)) {
                $this->route = $item['route'];
                return $item['route']->result;
            }
        }

        foreach ($this->routeList as $item) {
            if ($item['route']->match($pattern)) {
                $this->route = $item['route'];
                return $item['route']->result;
            }
        }

        return false;
    }

    public function preOrder()
    {
        //TODO store the first preordering set in cache, it need to order just one time
        foreach ($this->routeList as $i => $item) {
            $this->routeList[$i]['patCount'] = count(explode('/', $item['route']->getPattern()));
            $this->routeList[$i]['reqCount'] = count($item['route']->getRequirements());
            $this->routeList[$i]['varCount'] = count($item['route']->getVars());
            $this->routeList[$i]['pop'] = trim(array_pop(explode('/', $item['route']->getPattern())));
        }

        $list = $this->routeList;
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

        $this->routeList = $list;
    }

    public function swap_values(&$a, &$b)
    {
        $x = $a;
        $a = $b;
        $b = $x;
    }
}

