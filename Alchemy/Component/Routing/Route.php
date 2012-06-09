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
 * Route url string for a given pattern
 *
 * @author Erik Amaru ortiz <aortiz.erik@gmail.com>
 * @version 1.0
 * @package Routing
 */
class Route
{
    protected $pattern;
    protected $realPattern;
    protected $vars;
    protected $defaults;
    protected $requirements;
    protected $urlString;
    protected $success;
    protected $type;
    protected $resourcePath;

    public $result;

    public function __construct($pattern = null, $defaults = null, $requirements = null, $type = null, $resourcePath = null)
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->setPattern($pattern ? $pattern : '');
        $this->setDefaults($defaults ? $defaults : Array());
        $this->setRequirements($requirements ? $requirements : Array());

        $this->type    = $type;
        $this->success = false;
        $this->result  = array();

        $this->resourcePath = $resourcePath;

        $this->prepare();
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getVars()
    {
        return $this->vars[0];
    }

    public function prepare()
    {
        $this->pattern = addcslashes($this->pattern, '.\/');
        preg_match_all('/\{([\w]+)\}/', $this->pattern, $this->vars);
        $patterns = $replacements = array();

        foreach ($this->vars[1] as $var) {
            $patterns[] = "/\{$var\}/";

            if (isset($this->requirements[$var])) {
                array_push($replacements, "({$this->requirements[$var]})");
            } else {
                array_push($replacements, '([\w\-]+)');
            }
        }

        $this->realPattern = preg_replace($patterns, $replacements, $this->pattern);
    }

    public function match($urlString)
    {
        $this->urlString = urldecode($urlString);

        $this->success = (bool) preg_match("/^{$this->realPattern}$/", $this->urlString, $compiledMatches);

        if ($this->success) {
            if (!(isset($this->vars[1]) && count($compiledMatches) >= count($this->vars[1]))) {
                throw new Exception("Error while matching result, url string given: '$urlString'");
            }

            $varValues = array_slice($compiledMatches, 1);

            foreach ($this->vars[1] as $i => $varName) {
                $this->result[$varName] = $varValues[$i];
                unset($varValues[$i]);
            }

            foreach ($varValues as $varValue) {
                if (substr($varValue, 0, 1) != '?') {
                    $this->result[] = $varValue;
                }
            }

            $this->result = array_merge($this->defaults, $this->result);

            if (strpos($this->urlString, '?') !== false) {
                $params = array();
                list($pattern, $urlParams) = explode('?', $this->urlString);
                $urlParams = explode('&', $urlParams);

                foreach ($urlParams as $urlParam) {
                    list($var, $val) = explode('=', $urlParam);
                    $params[htmlspecialchars(urldecode($var))] = htmlspecialchars(urldecode($val));
                }
                $this->result = array_merge($this->result, $params);
            }
        }
        //var_dump($this->result);
        //var_dump($this->getType());
        if ($this->result) {
            if ($this->getType() == 'resource') {
                if (!isset($this->result['file'])) {
                    throw new \Exception("The \$file var on pattern was not set for resource type");
                }
                $this->result = rtrim($this->resourcePath, DS) . DS . $this->result['file'];
            }
        }

        return $this->success;
    }
}


