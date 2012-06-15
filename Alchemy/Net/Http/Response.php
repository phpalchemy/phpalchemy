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
 * This Class is a simplified version of \Symfony\Component\HttpFoundation\Response
 * Code subject to the MIT license
 * Copyright (c) 2004-2012 Fabien Potencier
 */

namespace Alchemy\Net\Http;

/**
 * Class Response
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class Response
{
    public $headers;

    protected $content;
    protected $version;
    protected $statusCode;
    protected $statusText;
    protected $charset;

    static public $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = new Collection($headers);
        $this->prepareHeaders();
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');

        if (!$this->headers->has('Date')) {
            $this->setDate(new \DateTime(null, new \DateTimeZone('UTC')));
        }
    }

    public function prepare()
    {
        if ($this->isInformational() || in_array($this->statusCode, array(204, 304))) {
            $this->setContent('');
        }

        // Fix Content-Type
        $charset = $this->charset ?: 'UTF-8';
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/html; charset='.$charset);
        } elseif (0 === strpos($this->headers->get('Content-Type'), 'text/') && false === strpos($this->headers->get('Content-Type'), 'charset')) {
            // add the charset
            $this->headers->set('Content-Type', $this->headers->get('Content-Type').'; charset='.$charset);
        }

        // Fix Content-Length
        if ($this->headers->has('Transfer-Encoding')) {
            $this->headers->remove('Content-Length');
        }
    }

    public function prepareHeaders()
    {
        if (!$this->headers->has('cache-control')) {
            $this->headers->set('cache-control', '');
        }

    }

    public function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return;
        }

        $this->prepare();

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText));

        // headers

        foreach ($this->headers->all() as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header($name.': '.$v, false);
                }
            } else {
                header($name.': '.$value, false);
            }
        }

        // cookies
        // foreach ($this->headers->getCookies() as $cookie) {
        //     setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        // }
    }

    public function setProtocolVersion($version)
    {
        $this->version = $version;
    }

    public function setDate(\DateTime $date)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s').' GMT');
    }

    public function setStatusCode($code, $text = null)
    {
        $this->statusCode = (int) $code;
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        $this->statusText = false === $text ? '' : (null === $text ? self::$statusTexts[$this->statusCode] : $text);
    }

    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new \UnexpectedValueException('The Response content must be a string or object implementing __toString(), "'.gettype($content).'" given.');
        }

        $this->content = (string) $content;
    }

    public function sendContent()
    {
        echo $this->content;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    public function isNotModified(Request $request)
    {
        $lastModified = $request->headers->get('If-Modified-Since');
        $notModified = false;

        if ($etags = $request->getEtags()) {
            $notModified = (in_array($this->getEtag(), $etags) || in_array('*', $etags)) && (!$lastModified || $this->headers->get('Last-Modified') == $lastModified);
        } elseif ($lastModified) {
            $notModified = $lastModified == $this->headers->get('Last-Modified');
        }

        if ($notModified) {
            $this->setNotModified();
        }

        return $notModified;
    }

    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
    /**
     * @return Boolean
     *
     * @api
     */
    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isOk()
    {
        return 200 === $this->statusCode;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isForbidden()
    {
        return 403 === $this->statusCode;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }

    /**
     * @param string $location
     *
     * @return Boolean
     *
     * @api
     */
    public function isRedirect($location = null)
    {
        return in_array($this->statusCode, array(201, 301, 302, 303, 307)) && (null === $location ?: $location == $this->headers->get('Location'));
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isEmpty()
    {
        return in_array($this->statusCode, array(201, 204, 304));
    }
}