<?
class Lib_Request
{
    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';
    const HEAD   = 'HEAD';

    protected $method          = null;
    protected $format          = '';
    
    protected $formats =
        array (
            'txt' => 'text/plain',
            'js' =>
                array (
                    0 => 'application/javascript',
                    1 => 'application/x-javascript',
                    2 => 'text/javascript',
                ),
               
            'css' => 'text/css',
            'json' =>
                array (
                    0 => 'application/json',
                    1 => 'application/x-json',
                ),
            'xml' =>
                array (
                    0 => 'text/xml',
                    1 => 'application/xml',
                    2 => 'application/x-xml',
                ),
            'rdf' =>  'application/rdf+xml',
            'atom' => 'application/atom+xml',
            'html' => 'text/html',
        );
    
    protected $parameterHolder = null;

    private static $instance;

    private function __clone() {}

    
    /**
     * get Request instance
     *
     * @return Lib_Request_Web
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
        $this->parameterHolder = new Lib_ParameterHolder();
        if (isset($_SERVER['REQUEST_METHOD'])) $this->setMethod($_SERVER['REQUEST_METHOD']);
             
        
        $requestMethod = explode(",", $_SERVER['HTTP_ACCEPT']);
        $requestMethod = $requestMethod[0];
        foreach ($this->formats as $format => $info)
        {
             if (is_array($info) && in_array($requestMethod, array_values($info))) $this->setFormat($format);
             elseif ($requestMethod == $info) $this->setFormat($format);
             
             if ($this->getFormat()) break;
        }
        
    }

    public function getMimeType($format)
    {
        if (isset($this->formats[$format]))
        {
            if (is_string($this->formats[$format])) return $this->formats[$format];
            else return $this->formats[$format][0];
        }
        
        return  null;
    }
    

    /**
   * Gets the request method.
   *
   * @return string The request method
   */
    public function getMethod()
    {
        return $this->method;
    }

    /**
   * Sets the request method.
   *
   * @param string $method  The request method
   *
   * @throws <b>GlobalException</b> - If the specified request method is invalid
   */
    public function setMethod($method)
    {
        if (!in_array(strtoupper($method), array(self::GET, self::POST, self::PUT, self::DELETE, self::HEAD)))
        {
            throw new Lib_Exception_Global(sprintf('Invalid request method: %s.', $method));
        }
        $this->method = strtoupper($method);
    }

    /**
   * Retrieves the parameters for the current request.
   *
   * @return ParameterHolder The parameter holder
   */
    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }

    public function getFormat()
    {
        return $this->format;
    }
    
    public function setFormat($format)
    {
        $this->format = $format;
    }
   
    /**
   * Retrieves a paramater for the current request.
   *
   * @param string $name     Parameter name
   * @param string $default  Parameter default value
   *
   */
    public function getParameter($name, $default = null)
    {
        return $this->parameterHolder->get($name, $default);
    }

    /**
   * Indicates whether or not a parameter exist for the current request.
   *
   * @param  string $name  Parameter name
   *
   * @return bool true, if the paramater exists otherwise false
   */
    public function hasParameter($name)
    {
        return $this->parameterHolder->has($name);
    }

    /**
   * Sets a parameter for the current request.
   *
   * @param string $name   Parameter name
   * @param string $value  Parameter value
   *
   */
    public function setParameter($name, $value)
    {
        $this->parameterHolder->set($name, $value);
    }
}