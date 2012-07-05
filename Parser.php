<?php
namespace Alchemy\Component\UI;

/**
 * Class Parser
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
class Parser
{
    protected $file   = '';
    protected $defs   = array();
    protected $blocks = array();
    protected $defaultBlock = '';
    protected $currentBlock = array();

    protected $data = array();

    const T_DEF      = 'def';
    const T_VAR      = 'var';
    const T_BLOCK    = 'block';
    const T_END      = 'end';
    const T_ITERATOR = 'iterator';
    const T_GLOBAL   = 'global';
    const T_SETCONF  = 'setconf';

    public function __construct($file = '')
    {
        // set this->file property and parse genscript file
        if (! empty($file)) {
            $this->file = $file;
            $this->parse();
        }


    }

    public function setScriptFile($file)
    {
        $this->file = $file;
    }

    public function getScriptFile()
    {
        return $this->file;
    }

    public function setDefaultBlock($block)
    {
        if (!isset($this->blocks[$block])) {
            throw new \InvalidArgumentException(sprintf(
                'Error: trying set as default to undefined block: "%s"', $block
            ));
        }

        $this->defaultBlock = $block;
    }

    public function getGlobals()
    {
        return $this->defs[Parser::T_GLOBAL];
    }

    public function getIterators()
    {
        return $this->defs[Parser::T_ITERATOR];
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getDef($name)
    {
        if (! array_key_exists($name, $this->defs)) {
            return '';
        }

        return $this->defs[$name];
    }

    public function getDefConf($name)
    {
        if (empty($this->defs[Parser::T_SETCONF]) || ! array_key_exists($name, $this->defs[Parser::T_SETCONF])) {
            return '';
        }

        return $this->defs[Parser::T_SETCONF][$name];
    }

    public function getBlock($name)
    {
        if (isset($this->blocks[$name])) {
            return $this->blocks[$name];
        }

        if (!empty($this->defaultBlock)) {
            return $this->blocks[$this->defaultBlock];
        }

        throw new \InvalidArgumentException(sprintf('Error: Undefined template block: "%s"', $name));
    }

    public function generate($name, $data)
    {
        $default = $this->getDefConf('default_block');

        $this->setDefaultBlock($default);

        $this->currentBlock     = $this->getBlock($name);
        $this->currentBlockName = $name;
        $this->data = $data;
        $generated  = array();

        foreach ($this->currentBlock as $varName => $template) {
            $content = $this->buildIterators($template);
            $content = $this->replaceData($content, $data);
            $generated[$varName] = $content;
        }

        return $generated;
    }

    /*
     * PRIVATE/PROTECTED METHODS
     */

    public function parse()
    {
        if (!is_file($this->file)) {
            throw new \Exception(sprintf(
                'Template "%s" file doesn\'t exist!', $this->file
            ));
        }

        $fp = fopen($this->file, 'r');

        $lineCount       = 0;
        $nextToken       = '';
        $block           = '';
        $currentValue    = '';
        $stringComposing = false;
        $skipMultilineComment = false;

        while (($line = fgets($fp)) !== false) {
            $lineCount++;

            if ($stringComposing) {
                if (substr($line, 0, 3) === '>>>') {
                    $this->blocks[$block][$name] = rtrim($value);
                    $block = '';
                    $value = '';
                    $stringComposing = false;
                } else {
                    $value .= $line;
                }

                continue;
            }

            $line = trim($line);

            if (substr($line, 0, 1) === '#' || $line === '' || substr($line, 0, 2) === '//') {
                continue; // skip single comments or empty lines
            } elseif (substr($line, 0, 2) === '/*') { // start multiline comment
                if (strpos($line, '*/') === false) {
                    $skipMultilineComment = true; // activate multiline comments skipping
                } else {
                    continue; // just skip this line, it has the form: /* ... */
                }
            } elseif (strpos($line, '*/') !== false) { // end multiline comment
                $skipMultilineComment = false;
                continue; // just skip the end multiline comment
            }

            if ($skipMultilineComment) {
                continue; //skip multiline comments segment
            }

            $kwPattern = '^@(?<keyword>\w+)';

            if (!preg_match('/'.$kwPattern.'.*/', $line, $matches)) {
                throw new \Exception(sprintf(
                    'Parse Error: Unknow keyword, lines must starts with a valid keyword, near: %s, on line %s',
                    $line, $lineCount
                ));
            }

            $keyword = $matches['keyword'];

            switch ($keyword) {
                case Parser::T_DEF:
                    $pattern = '/'.$kwPattern.'\s+(?<type>[\w]+)\s+(?<name>[\w.]+)\s+(?<value>.+)/';

                    if (!preg_match($pattern, $line, $matches)) {
                        throw new \Exception(sprintf(
                            "Syntax Error: near: '%s', on line %s.\n" .
                            "Syntax should be: @def <type> <name> <value>",
                            $line, $lineCount
                        ));
                    }

                    $keyword = $matches['keyword'];
                    $type    = $matches['type'];
                    $name    = $matches['name'];
                    $value   = $matches['value'];

                    $tmp     = explode('.', $name);
                    $defName = $tmp[0];
                    $defProp = isset($tmp[1]) ? $tmp[1] : '';

                    switch ($type) {
                        case Parser::T_GLOBAL:
                        case Parser::T_ITERATOR:
                        case Parser::T_SETCONF:
                            if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
                                $value = trim($value, '"');
                            } elseif (substr($value, 0, 1) == "'" && substr($value, -1) == "'") {
                                $value = trim($value, "'");
                            }

                            if ($value === '\n' || $value === '\r\n') {
                                $value = "\n";
                            }

                            $value = self::castValue($value);

                            if (empty($defProp)) {
                                $this->defs[$type][$defName] = $value;
                            } else {
                                $this->defs[$type][$defName][$defProp] = $value;
                            }
                            break;
                        default:
                            throw new \Exception(sprintf(
                                'Parse Error: unknow definition type: "%s" on line %s.', $type, $lineCount
                            ));
                    }
                    break;
                case Parser::T_BLOCK:
                    $pattern = '/'.$kwPattern.'\s+(?<block>[\w]+)/';

                    if (!preg_match($pattern, $line, $matches)) {
                        throw new Exception(sprintf(
                            "Parse Error: Syntax error near: %s, on line %s.\n." .
                            "Syntax should be: @def <type> <name> <value>",
                            substr($line, 0, 20).'...', $lineCount
                        ));
                    }
                    $block = $matches['block'];

                    if (!empty($nextToken)) {
                        throw new \Exception(sprintf(
                            'Parse Error: expected: @"%s", given: @"%s"', $nextToken, Parser::T_BLOCK
                        ));
                    }

                    $nextToken = Parser::T_END;
                    break;
                case Parser::T_END:
                    if (empty($nextToken)) {
                        throw new \Exception(sprintf(
                            'Parse Error: close keyword: @"%s" given, but any block was started.', Parser::T_BLOCK
                        ));
                    }

                    if ($nextToken !== Parser::T_END) {
                        throw new \Exception(sprintf(
                            'Parse Error: expected: @"%s", given: @"%s"', $nextToken, Parser::T_END
                        ));
                    }

                    $nextToken = '';
                    break;
                case Parser::T_VAR:
                    $pattern = '/'.$kwPattern.'\s+(?<name>[\w]+)\s+(?<value>.+)/';

                    if (!preg_match($pattern, $line, $matches)) {
                        throw new Exception(sprintf(
                            "Parse Error: Syntax error near: %s, on line %s.\n." .
                            "Syntax should be: @var <name> <value>\n or \n" .
                            "@var <name> <<<\nsome large string\nmultiline...\n>>>\n\n",
                            substr($line, 0, 20).'...', $lineCount
                        ));
                    }

                    $keyword = $matches['keyword'];
                    $name    = $matches['name'];
                    $value   = $matches['value'];

                    if (substr($value, 0, 3) === '<<<' && $value !== '<<<') {
                        throw new \Exception(sprintf(
                            "Syntax Error: multiline string must starts on new line after open braces <<<\n" .
                            "near: '%s', on line %s", $line, $lineCount
                        ));
                    }

                    if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
                        $value = trim($value, '"');
                    } elseif (substr($value, 0, 1) == "'" && substr($value, -1) == "'") {
                        $value = trim($value, "'");
                    }

                    if ($value !== '<<<') {
                        $this->blocks[$block][$name] = self::castValue($value);
                    } else {
                        $value = '';
                        $stringComposing = true;
                    }
                    break;
                default:
                    throw new \Exception(sprintf(
                        'Parse Error: unknow definition type: %s, on line %s', $type, $lineCount
                    ));
            }

        }

        if ($stringComposing) {
            throw new \Exception(sprintf(
                "Parse Error: Multiline string closing braces are missing '>>>',\nfor @block: '%s', @var: '%s' " .
                "until end of file.", $block, $name
            ));
        }
    }

    private static function castValue($val)
    {
        if (is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::castValue($value);
            }
        } elseif (is_string($val)) {
            $tmp = strtolower($val);

            if ($tmp === 'false' || $tmp === 'true') {
                $val = $tmp === 'true';
            } elseif (is_numeric($val)) {
                return $val + 0;
            }
        }

        return $val;
    }

    protected function buildIterators($template)
    {
        $pattern = '/@@(?<iterator>\w+)\(\{(?<var>\w+)\}\)/';
        $result  = preg_replace_callback(
            $pattern,
            array($this, 'parseTemplate'),
            $template
        );

        return $result;
    }

    protected function parseTemplate($matches)
    {
        $iterators  = $this->getIterators();

        if (!isset($iterators[$matches['iterator']])) {
            throw new Exception(sprintf(
                'Parse Error: Trying to use undefinded iterator "%s"',
                $matches['iterator']
            ));
        }

        if (!array_key_exists($matches['var'], $this->data)) {
            throw new \InvalidArgumentException(sprintf(
                "Compile Error: Undefined variable '%s' for block: '%s'", $matches['var'], $this->currentBlockName
            ));
        }

        $iterator = $iterators[$matches['iterator']];
        $composed = array();
        $indent   = '';
        $data     = $this->data[$matches['var']];

        // verify if the template is multiline
        if ($iterator['sep'] === "\n") {
            $tplLines = explode("\n", $this->currentBlock['template']);

            foreach ($tplLines as $tplLine) {
                if (($pos = strpos($tplLine, $matches[0])) !== false) {
                    $indent = str_repeat(' ', $pos);
                    break;
                }
            }
        }

        foreach ($data as $key => $value) {
            $strComposed = '';

            if (!is_array($value)) {
                $value = array(
                    '_key'   => $key,
                    '_value' => $value
                );
            }

            if (!empty($indent) && count($composed) !== 0) {
                $strComposed .= $indent;
            }

            $strComposed .=  $this->replaceData($iterator['tpl'], $value);
            $composed[]   = $strComposed;
        }

        return implode($iterator['sep'], $composed);
    }

    protected function replaceData($template, $data)
    {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $template = str_replace('{'.$key.'}', $value, $template);
            }
        }

        return $template;
    }
}
