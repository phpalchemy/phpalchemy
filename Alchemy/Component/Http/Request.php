<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * This Class is a simplified version of \Symfony\Component\HttpFoundation\Request
 * Code subject to the MIT license
 * Copyright (c) 2004-2012 Fabien Potencier
 */

namespace Alchemy\Component\Http;

/**
 * Class Request
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class Request
{
    static protected $trustProxy   = false;

    /**
     * @var Collection
     */
    public $request;
    /**
     * @var Collection
     */
    public $query;
    /**
     * @var Collection
     */
    public $attributes;
    /**
     * @var Collection
     */
    public $cookies;
    /**
     * @var Collection
     */
    public $files;
    /**
     * @var Collection
     */
    public $server;
    /**
     * @var Collection
     */
    public $headers;

    public $content                = array();
    public $languages              = null;
    public $charsets               = null;
    public $acceptableContentTypes = null;
    public $pathInfo               = null;
    public $requestUri             = null;
    public $baseUrl                = null;
    public $basePath               = null;
    public $method                 = null;
    public $cacheControl           = array();

    public static $formats = array(
        'html' => array('text/html', 'application/xhtml+xml'),
        'txt'  => array('text/plain'),
        'js'   => array('application/javascript', 'application/x-javascript', 'text/javascript'),
        'css'  => array('text/css'),
        'json' => array('application/json', 'application/x-json'),
        'xml'  => array('text/xml', 'application/xml', 'application/x-xml'),
        'rdf'  => array('application/rdf+xml'),
        'atom' => array('application/atom+xml'),
    );

    public function __construct(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    ) {
        $this->init($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    public function init(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    ) {
        $this->request    = new Collection($request);
        $this->query      = new Collection($query);
        $this->attributes = new Collection($attributes);
        $this->cookies    = new Collection($cookies);
        $this->files      = new Collection($files);
        $this->server     = new Collection($server);
        $this->headers    = new Collection($this->getHeaders());
        $this->cacheControl = new Collection($this->parseCacheControl($server));
        $this->content    = $content;
        $this->languages  = null;
        $this->charsets   = null;
        $this->pathInfo   = null;
        $this->requestUri = null;
        $this->baseUrl    = null;
        $this->basePath   = null;
        $this->method     = null;
        $this->format     = null;

        $this->acceptableContentTypes = null;
    }

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return Request A new request
     *
     * @api
     */
    static public function createFromGlobals()
    {
        $request = new static($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);

        if (0 === strpos($request->server->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE'))
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new Collection($data);
        }

        return $request;
    }

    /**
     * Creates a Request based on a given URI and configuration.
     *
     * @param string $uri        The URI
     * @param string $method     The HTTP method
     * @param array  $parameters The query (GET) or request (POST) parameters
     * @param array  $cookies    The request cookies ($_COOKIE)
     * @param array  $files      The request files ($_FILES)
     * @param array  $server     The server parameters ($_SERVER)
     * @param string $content    The raw body data
     *
     * @return Request A Request instance
     *
     * @api
     */
    public static function create($uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null)
    {
        $defaults = array(
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_USER_AGENT'      => 'PhpAlchemy/1.0',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'          => '127.0.0.1',
            'SCRIPT_NAME'          => '',
            'SCRIPT_FILENAME'      => '',
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_TIME'         => time(),
        );

        $components = parse_url($uri);
        if (isset($components['host'])) {
            $defaults['SERVER_NAME'] = $components['host'];
            $defaults['HTTP_HOST'] = $components['host'];
        }

        if (isset($components['scheme'])) {
            if ('https' === $components['scheme']) {
                $defaults['HTTPS'] = 'on';
                $defaults['SERVER_PORT'] = 443;
            }
        }

        if (isset($components['port'])) {
            $defaults['SERVER_PORT'] = $components['port'];
            $defaults['HTTP_HOST'] = $defaults['HTTP_HOST'].':'.$components['port'];
        }

        if (isset($components['user'])) {
            $defaults['PHP_AUTH_USER'] = $components['user'];
        }

        if (isset($components['pass'])) {
            $defaults['PHP_AUTH_PW'] = $components['pass'];
        }

        if (!isset($components['path'])) {
            $components['path'] = '';
        }

        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $defaults['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
            case 'PATCH':
                $request = $parameters;
                $query = array();
                break;
            default:
                $request = array();
                $query = $parameters;
                break;
        }

        if (isset($components['query'])) {
            parse_str(html_entity_decode($components['query']), $qs);
            $query = array_replace($qs, $query);
        }
        $queryString = http_build_query($query, '', '&');

        $uri = $components['path'].('' !== $queryString ? '?'.$queryString : '');

        $server = array_replace($defaults, $server, array(
            'REQUEST_METHOD' => strtoupper($method),
            'PATH_INFO'      => '',
            'REQUEST_URI'    => $uri,
            'QUERY_STRING'   => $queryString,
        ));

        return new static($query, $request, array(), $cookies, $files, $server, $content);
    }

    public function getBaseUrl()
    {
        if (!empty($this->baseUrl)) {
            return $this->baseUrl;
        }

        $filename = basename($this->server->get('SCRIPT_FILENAME'));

        if (basename($this->server->get('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('SCRIPT_NAME');
        } elseif (basename($this->server->get('PHP_SELF')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF');
        } elseif (basename($this->server->get('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = $this->server->get('PHP_SELF', '');
            $file    = $this->server->get('SCRIPT_FILENAME', '');
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?

        $requestUri = $this->getRequestUri();

        if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $baseUrl;
        }

        if ($baseUrl && 0 === strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim(dirname($baseUrl), '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (
            strlen($requestUri) >= strlen($baseUrl) &&
            ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))
        ) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        $this->baseUrl = rtrim($baseUrl, '/');

        return $this->baseUrl;
    }

    public function getMethod()
    {
        if (empty($this->method)) {
            $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

            if ('POST' === $this->method) {
                $this->method = strtoupper(
                    $this->headers->get('X-HTTP-METHOD-OVERRIDE',
                    $this->request->get('_method', 'POST'))
                );
            }
        }

        return $this->method;
    }

    public function getHttpHost()
    {
        //$scheme = $this->getScheme();
        $protocol = $this->isSecure() ? 'https' : 'http';
        $port     = $this->getPort();

        if (($protocol === 'http' && $port === 80) || ($protocol === 'https' && $port === 443)) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    public function isSecure()
    {
        return (
            (strtolower($this->server->get('HTTPS')) == 'on' || $this->server->get('HTTPS') == 1) ||
            (
                self::$trustProxy &&
                strtolower($this->headers->get('SSL_HTTPS')) == 'on' ||
                $this->headers->get('SSL_HTTPS') == 1
            ) ||
            (self::$trustProxy && strtolower($this->headers->get('X_FORWARDED_PROTO')) == 'https')
        );
    }

    public function getPort()
    {
        if (self::$trustProxy && $this->headers->has('X-Forwarded-Port')) {
            return intval($this->headers->get('X-Forwarded-Port'));
        }

        return intval($this->server->get('SERVER_PORT'));
    }

    public function getHost()
    {
        if (self::$trustProxy && $host = $this->headers->get('X_FORWARDED_HOST')) {
            $elements = explode(',', $host);

            $host = trim($elements[count($elements) - 1]);
        } else {
            if (!$host = $this->headers->get('HOST')) {
                if (!$host = $this->server->get('SERVER_NAME')) {
                    $host = $this->server->get('SERVER_ADDR', '');
                }
            }
        }

        //TODO improve this to don't use preg_match
        $host = preg_replace('/:\d+$/', '', $host); // Remove port number from host

        return trim($host);
    }

    public function getHeaders()
    {
        $headers = array();
        $parameters = $this->server->all();

        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
            // CONTENT_* are not prefixed with HTTP_
            elseif (in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                $headers[$key] = $value;
            }
        }

        if (isset($this->parameters['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $this->parameters['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($this->parameters['PHP_AUTH_PW']) ? $this->parameters['PHP_AUTH_PW'] : '';
        } else {
            /*
             * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
             * For this workaround to work, add this line to your .htaccess file:
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             *
             * A sample .htaccess file:
             * RewriteEngine On
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             * RewriteCond %{REQUEST_FILENAME} !-f
             * RewriteRule ^(.*)$ app.php [QSA,L]
             */

            $authorizationHeader = null;
            if (isset($this->parameters['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $this->parameters['HTTP_AUTHORIZATION'];
            } elseif (isset($this->parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $this->parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW
            if (null !== $authorizationHeader) {
                $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
                if (count($exploded) == 2) {
                    list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                }
            }
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']);
        }

        return $headers;
    }

    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    protected function preparePathInfo()
    {
        $baseUrl = $this->getBaseUrl();

        $requestUri = $this->getRequestUri();

        if ($requestUri === null) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        $pos = strpos($requestUri, '?');

        if ($pos !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        $pathInfo = substr(urldecode($requestUri), strlen(urldecode($baseUrl)));

        if ($baseUrl !== null && $pathInfo === false) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif ($baseUrl === null) {
            $pathInfo = $requestUri;
        }

        if (strlen($pathInfo) > 1 && substr($pathInfo, -1) === '/') {
            $pathInfo = substr($pathInfo, 0, -1);
        }

        return $pathInfo;
    }

    public function getRequestUri()
    {
        if ($this->requestUri === null) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ($this->headers->has('X_REWRITE_URL') && false !== stripos(PHP_OS, 'WIN')) {
            // check this first so IIS will catch
            $requestUri = $this->headers->get('X_REWRITE_URL');
        } elseif ($this->server->get('IIS_WasUrlRewritten') == '1' && $this->server->get('UNENCODED_URL') != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
            // HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
            $schemeAndHttpHost = $this->getScheme().'://'.$this->getHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ($this->server->get('QUERY_STRING')) {
                $requestUri .= '?'.$this->server->get('QUERY_STRING');
            }
        }

        return $requestUri;
    }

    public function getMimeType($format)
    {
        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
    }

    public function getContent($asResource = false)
    {
        if ($this->content === false || ($asResource === true && $this->content !== null)) {
            throw new \LogicException('getContent() can only be called once when using the resource return type.');
        }

        if ($asResource === true) {
            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if ($this->content === null) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    public function getEtags()
    {
        return preg_split('/\s*,\s*/', $this->headers->get('if_none_match'), null, PREG_SPLIT_NO_EMPTY);
    }

    public function isNoCache()
    {
        return $this->cacheControl->has('no-cache') || 'no-cache' == $this->headers->get('Pragma');
    }

    protected function parseCacheControl($server)
    {
        if (empty($server['HTTP_CACHE_CONTROL'])) {
            return array();
        }

        $header = $server['HTTP_CACHE_CONTROL'];
        $cacheControl = array();
        preg_match_all('#([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?#', $header, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $cacheControl[strtolower($match[1])] = isset($match[2]) && $match[2] ?
                                                   $match[2] : (isset($match[3]) ? $match[3] : true);
        }

        return $cacheControl;
    }

    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  -> format defined by the user (with setRequestFormat())
     *  -> _format request parameter
     *  -> $default
     *
     * @param string  $default     The default format
     * @return string The request format
     */
    public function getRequestFormat($default = 'html')
    {
        if (null === $this->format) {
            $this->format = $this->get('_format', $default);
        }

        return $this->format;
    }

    /**
     * Gets a "parameter" value.
     *
     * This method is mainly useful for libraries that want to provide some flexibility.
     *
     * Order of precedence: GET, PATH, POST, COOKIE
     * Avoid using this method in controllers:
     *  * slow
     *  * prefer to get from a "named" source
     *
     * @param string    $key        the key
     * @param mixed     $default    the default value
     * @param type      $deep       is parameter deep in multidimensional array
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->query->get(
            $key,
            $this->attributes->get(
                $key,
                $this->request->get(
                    $key,
                    $default
                )
            )
        );
    }


    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library set an X-Requested-With HTTP header.
     * It is known to work with Prototype, Mootools, jQuery.
     *
     * @return Boolean true if the request is an XMLHttpRequest, false otherwise
     *
     * @api
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->get('X_REQUESTED_WITH');
    }

    function isMobile()
    {
        $is_mobile = '0';

        if (preg_match(
            '/(googlebot-mobile|android|up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|mobile)/i',
            strtolower($_SERVER['HTTP_USER_AGENT']))
        ) {
            $is_mobile = 1;
        }

        if (
            strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0 ||
            isset($_SERVER['HTTP_X_WAP_PROFILE']) ||
            isset($_SERVER['HTTP_PROFILE'])
        ) {
            $is_mobile = 1;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4 ));
        $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','andr','audi','avan','benq','bird','blac','blaz','brew','cell',
            'cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c',
            'lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt',
            'noki','oper','palm','pana','pant','phil','play','port','prox','qwap','sage','sams','sany','sch-',
            'sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli',
            'tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-'
        );

        if (in_array($mobile_ua, $mobile_agents)) {
            $is_mobile = 1;
        }

        if (isset($_SERVER['ALL_HTTP'])) {
            if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {
                $is_mobile = 1;
            }
        }

        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0) {
            $is_mobile = 0;
        }

        return $is_mobile;
    }

    function isIphone()
    {
        if (preg_match('/iphone/', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }

        return false;
    }

    function isIpad()
    {
        if (preg_match('/ipad/', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }

        return false;
    }

}

