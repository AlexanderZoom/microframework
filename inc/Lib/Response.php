<?php
class Lib_Response
{
    const
    FIRST  = 'first',
    MIDDLE = '',
    LAST   = 'last',
    ALL    = 'ALL',
    RAW    = 'RAW';

    protected
    $cookies     = array(),
    $statusCode  = 200,
    $statusText  = 'OK',
    $headerOnly  = false,
    $headers     = array();

    protected
    $options    = array(),
    $content    = '';


    static protected $statusTexts = array(
    '100' => 'Continue',
    '101' => 'Switching Protocols',
    '200' => 'OK',
    '201' => 'Created',
    '202' => 'Accepted',
    '203' => 'Non-Authoritative Information',
    '204' => 'No Content',
    '205' => 'Reset Content',
    '206' => 'Partial Content',
    '300' => 'Multiple Choices',
    '301' => 'Moved Permanently',
    '302' => 'Found',
    '303' => 'See Other',
    '304' => 'Not Modified',
    '305' => 'Use Proxy',
    '306' => '(Unused)',
    '307' => 'Temporary Redirect',
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '402' => 'Payment Required',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '405' => 'Method Not Allowed',
    '406' => 'Not Acceptable',
    '407' => 'Proxy Authentication Required',
    '408' => 'Request Timeout',
    '409' => 'Conflict',
    '410' => 'Gone',
    '411' => 'Length Required',
    '412' => 'Precondition Failed',
    '413' => 'Request Entity Too Large',
    '414' => 'Request-URI Too Long',
    '415' => 'Unsupported Media Type',
    '416' => 'Requested Range Not Satisfiable',
    '417' => 'Expectation Failed',
    '500' => 'Internal Server Error',
    '501' => 'Not Implemented',
    '502' => 'Bad Gateway',
    '503' => 'Service Unavailable',
    '504' => 'Gateway Timeout',
    '505' => 'HTTP Version Not Supported',
    );
    
    private static $instance;

    private function __clone() {}

    
    /**
     * get Response instance
     *
     * @return Lib_Response
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    

    public function __construct()
    {
        
                
    }

    /**
   * Initializes this Response.
   *
   * Available options:
   *
   *  * charset:           The charset to use (utf-8 by default)
   *  * content_type:      The content type (text/html by default)
   *  * send_http_headers: Whether to send HTTP headers or not (true by default)
   *  * http_protocol:     The HTTP protocol to use for the response (HTTP/1.0 by default)
   *
   * @param  EventDispatcher $dispatcher  An EventDispatcher instance
   * @param  array             $options     An array of options
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   *
   *
   * @see Response
   */
    public function init($options = array())
    {



        if (!isset($this->options['charset']))
        {
            $this->options['charset'] = 'utf-8';
        }

        if (!isset($this->options['send_http_headers']))
        {
            $this->options['send_http_headers'] = true;
        }

        if (!isset($this->options['http_protocol']))
        {
            $this->options['http_protocol'] = 'HTTP/1.0';
        }

        $this->options['content_type'] = $this->fixContentType(isset($this->options['content_type']) ? $this->options['content_type'] : 'text/html');
    }

    /**
   * Sets if the response consist of just HTTP headers.
   *
   * @param bool $value
   */
    public function setHeaderOnly($value = true)
    {
        $this->headerOnly = (boolean) $value;
    }

    /**
   * Returns if the response must only consist of HTTP headers.
   *
   * @return bool returns true if, false otherwise
   */
    public function isHeaderOnly()
    {
        return $this->headerOnly;
    }

    /**
   * Sets a cookie.
   *
   * @param  string  $name      HTTP header name
   * @param  string  $value     Value for the cookie
   * @param  string  $expire    Cookie expiration period
   * @param  string  $path      Path
   * @param  string  $domain    Domain name
   * @param  bool    $secure    If secure
   * @param  bool    $httpOnly  If uses only HTTP
   *
   * @throws <b>GlobalException</b> If fails to set the cookie
   */
    public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        if ($expire !== null)
        {
            if (is_numeric($expire))
            {
                $expire = (int) $expire;
            }
            else
            {
                $expire = strtotime($expire);
                if ($expire === false || $expire == -1)
                {
                    throw new Lib_Exception_Global('Your expire parameter is not valid.');
                }
            }
        }

        $this->cookies[$name] = array(
        'name'     => $name,
        'value'    => $value,
        'expire'   => $expire,
        'path'     => $path,
        'domain'   => $domain,
        'secure'   => $secure ? true : false,
        'httpOnly' => $httpOnly,
        );
    }

    /**
   * Sets response status code.
   *
   * @param string $code  HTTP status code
   * @param string $name  HTTP status text
   *
   */
    public function setStatusCode($code, $name = null)
    {
        $this->statusCode = $code;
        $this->statusText = null !== $name ? $name : self::$statusTexts[$code];
    }

    /**
   * Retrieves status text for the current web response.
   *
   * @return string Status text
   */
    public function getStatusText()
    {
        return $this->statusText;
    }

    /**
   * Retrieves status code for the current web response.
   *
   * @return integer Status code
   */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
   * Sets a HTTP header.
   *
   * @param string  $name     HTTP header name
   * @param string  $value    Value (if null, remove the HTTP header)
   * @param bool    $replace  Replace for the value
   *
   */
    public function setHttpHeader($name, $value, $replace = true)
    {
        $name = $this->normalizeHeaderName($name);

        if (is_null($value))
        {
            unset($this->headers[$name]);

            return;
        }

        if ('Content-Type' == $name)
        {
            if ($replace || !$this->getHttpHeader('Content-Type', null))
            {
                $this->setContentType($value);
            }

            return;
        }

        if (!$replace)
        {
            $current = isset($this->headers[$name]) ? $this->headers[$name] : '';
            $value = ($current ? $current.', ' : '').$value;
        }

        $this->headers[$name] = $value;
    }

    /**
   * Gets HTTP header current value.
   *
   * @param  string $name     HTTP header name
   * @param  string $default  Default value returned if named HTTP header is not found
   *
   * @return array
   */
    public function getHttpHeader($name, $default = null)
    {
        $name = $this->normalizeHeaderName($name);

        return isset($this->headers[$name]) ? $this->headers[$name] : $default;
    }

    /**
   * Checks if response has given HTTP header.
   *
   * @param  string $name  HTTP header name
   *
   * @return bool
   */
    public function hasHttpHeader($name)
    {
        return array_key_exists($this->normalizeHeaderName($name), $this->headers);
    }

    /**
   * Sets response content type.
   *
   * @param string $value  Content type
   *
   */
    public function setContentType($value)
    {
        $this->headers['Content-Type'] = $this->fixContentType($value);
    }

    /**
   * Gets the current charset as defined by the content type.
   *
   * @return string The current charset
   */
    public function getCharset()
    {
        return $this->options['charset'];
    }

    /**
   * Gets response content type.
   *
   * @return array
   */
    public function getContentType()
    {
        return $this->getHttpHeader('Content-Type', $this->options['content_type']);
    }

    /**
   * Sends HTTP headers and cookies.
   *
   */
    public function sendHttpHeaders()
    {
        if (isset($this->options['send_http_headers']) && !$this->options['send_http_headers'])
        {
            return;
        }

        // status
        $status = @$this->options['http_protocol'].' '.$this->statusCode.' '.$this->statusText;
        header($status);


        // headers
        if (!$this->getHttpHeader('Content-Type') && isset($this->options['content_type']))
        {
            $this->setContentType($this->options['content_type']);
        }
        foreach ($this->headers as $name => $value)
        {
            header($name.': '.$value);

        }

        // cookies
        foreach ($this->cookies as $cookie)
        {
            setrawcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);


        }
    }

    /**
   * Send content for the current web response.
   *
   */
    public function sendContent()
    {
        if (!$this->headerOnly)
        {
            echo $this->content;
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
   * Sends the HTTP headers and the content.
   */
    public function send()
    {
        $this->sendHttpHeaders();
        $this->sendContent();
    }

    /**
   * Retrieves a normalized Header.
   *
   * @param  string $name  Header name
   *
   * @return string Normalized header
   */
    protected function normalizeHeaderName($name)
    {
        return preg_replace('/\-(.)/e', "'-'.strtoupper('\\1')", strtr(ucfirst(strtolower($name)), '_', '-'));
    }

    /**
   * Retrieves a formated date.
   *
   * @param  string $timestamp  Timestamp
   * @param  string $type       Format type
   *
   * @return string Formatted date
   */
    static public function getDate($timestamp, $type = 'rfc1123')
    {
        $type = strtolower($type);

        if ($type == 'rfc1123')
        {
            return substr(gmdate('r', $timestamp), 0, -5).'GMT';
        }
        else if ($type == 'rfc1036')
        {
            return gmdate('l, d-M-y H:i:s ', $timestamp).'GMT';
        }
        else if ($type == 'asctime')
        {
            return gmdate('D M j H:i:s', $timestamp);
        }
        else
        {
            throw new InvalidArgumentException('The second getDate() method parameter must be one of: rfc1123, rfc1036 or asctime.');
        }
    }

    /**
   * Adds vary to a http header.
   *
   * @param string $header  HTTP header
   */
    public function addVaryHttpHeader($header)
    {
        $vary = $this->getHttpHeader('Vary');
        $currentHeaders = array();
        if ($vary)
        {
            $currentHeaders = preg_split('/\s*,\s*/', $vary);
        }
        $header = $this->normalizeHeaderName($header);

        if (!in_array($header, $currentHeaders))
        {
            $currentHeaders[] = $header;
            $this->setHttpHeader('Vary', implode(', ', $currentHeaders));
        }
    }

    /**
   * Adds an control cache http header.
   *
   * @param string $name   HTTP header
   * @param string $value  Value for the http header
   */
    public function addCacheControlHttpHeader($name, $value = null)
    {
        $cacheControl = $this->getHttpHeader('Cache-Control');
        $currentHeaders = array();
        if ($cacheControl)
        {
            foreach (preg_split('/\s*,\s*/', $cacheControl) as $tmp)
            {
                $tmp = explode('=', $tmp);
                $currentHeaders[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
            }
        }
        $currentHeaders[strtr(strtolower($name), '_', '-')] = $value;

        $headers = array();
        foreach ($currentHeaders as $key => $value)
        {
            $headers[] = $key.(null !== $value ? '='.$value : '');
        }

        $this->setHttpHeader('Cache-Control', implode(', ', $headers));
    }



    /**
   * Retrieves cookies from the current web response.
   *
   * @return array Cookies
   */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
   * Retrieves HTTP headers from the current web response.
   *
   * @return string HTTP headers
   */
    public function getHttpHeaders()
    {
        return $this->headers;
    }

    /**
   * Cleans HTTP headers from the current web response.
   */
    public function clearHttpHeaders()
    {
        $this->headers = array();
    }

    /**
   * Copies all properties from a given WebResponse object to the current one.
   *
   * @param WebResponse $response  An WebResponse instance
   */
    public function copyProperties(Response $response)
    {
        $this->options     = $response->getOptions();
        $this->headers     = $response->getHttpHeaders();

    }

    /**
   * Sets the response content
   *
   * @param string $content
   */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
   * Gets the current response content
   *
   * @return string Content
   */
    public function getContent()
    {
        return $this->content;
    }


    /**
   * @see Response
   */
    public function serialize()
    {
        return serialize(array($this->statusCode, $this->statusText, $this->options, $this->cookies, $this->headerOnly, $this->headers));
    }

    /**
   * @see Response
   */
    public function unserialize($serialized)
    {
        list($this->content, $this->statusCode, $this->statusText, $this->options, $this->cookies, $this->headerOnly, $this->headers) = unserialize($serialized);
    }



    /**
   * Fixes the content type by adding the charset for text content types.
   *
   * @param  string $contentType  The content type
   *
   * @return string The content type with the charset if needed
   */
    protected function fixContentType($contentType)
    {
        // add charset if needed (only on text content)
        if (false === stripos($contentType, 'charset') && (0 === stripos($contentType, 'text/') || strlen($contentType) - 3 === strripos($contentType, 'xml')))
        {
            $contentType .= '; charset='.$this->options['charset'];
        }

        // change the charset for the response
        if (preg_match('/charset\s*=\s*(.+)\s*$/', $contentType, $match))
        {
            $this->options['charset'] = $match[1];
        }

        return $contentType;
    }
}
?>