<?php
class Lib_Exception_Global extends Exception
{

    protected
        $wrappedException = null;

    static protected
        $lastException = null;

    /**
   * Wraps an Exception.
   *
   * @param Exception $e An Exception instance
   *
   * @return Lib_Exception_Global An Lib_Exception_Global instance that wraps the given Exception object
   */
    static public function createFromException(Exception $e)
    {
        $exception = new Lib_Exception_Global(sprintf('Wrapped %s: %s', get_class($e), $e->getMessage()));
        $exception->setWrappedException($e);

        return $exception;
    }

    
        /**
       * Sets the wrapped exception.
       *
       * @param Exception $e An Exception instance
       */
      public function setWrappedException(Exception $e)
      {
        $this->wrappedException = $e;
    
        self::$lastException = $e;
      }
    
      /**
       * Gets the last wrapped exception.
       *
       * @return Exception An Exception instance
       */
      static public function getLastException()
      {
        return self::$lastException;
      }
    
      /**
       * Clears the $lastException property (added for #6342)
       */
      static public function clearLastException()
      {
        self::$lastException = null;
      }
    
    
    public function printStackTrace()
    {
        $exception = is_null($this->wrappedException) ? $this : $this->wrappedException;

        if (!Lib_Config::getVar('app_exception_display'))
        {
            // log all exceptions in php log
            error_log($exception->getMessage());

            // clean current output buffer
            while (ob_get_level())
            {
                if (!ob_end_clean())
                {
                    break;
                }
            }

            ob_start();

            if (php_sapi_name() != 'cli') header('HTTP/1.0 500 Internal Server Error');
        }

        try
        {
            $this->outputStackTrace($exception);
        }
        catch (Exception $e)
        {
        	throw $e;
        }

        if (!Lib_Config::getVar('app_exception_display'))
        {
            exit(1);
        }
    }

    /**
   * Gets the stack trace for this exception.
   */
    static protected function outputStackTrace(Exception $exception)
    {
        $format = 'html';
        $code   = '500';
        $text   = 'Internal Server Error';

        
        if (class_exists('Lib_Dispatcher', false) &&
            Lib_Dispatcher::getInstance() &&
            php_sapi_name() != 'cli' &&
            is_object($request = Lib_Request::getInstance()) &&
            is_object($response = Lib_Response::getInstance()))
        {
            $dispatcher = Lib_Dispatcher::getInstance();
            $response->init();

            if ($response->getStatusCode() < 300)
            {
                // status code has already been sent, but is included here for the purpose of testing
                $response->setStatusCode(500);
            }

            $response->setContentType('text/html');


            $code = $response->getStatusCode();
            $text = $response->getStatusText();

            $format = $request->getFormat();
            if (!$format)
            {
                $format = 'html';
            }

            if ($mimeType = $request->getMimeType($format))
            {
                $response->setContentType($mimeType);
            }
        }
        else
        {
            ;
        }


        if (($template = self::getTemplatePathForError($format, Lib_Config::getVar('app_exception_display') ? true : false))
             && php_sapi_name() != 'cli'){
                     
            if (Lib_Config::getVar('app_exception_display')){
                $message = is_null($exception->getMessage()) ? 'n/a' : $exception->getMessage();
                $name    = get_class($exception);
                $traces  = self::getTraces($exception, 'html' != $format || 0 == strncasecmp(PHP_SAPI, 'cli', 3) ? 'plain' : 'html');
                
            // dump main objects values
            $sf_settings = '';
            $settingsTable = $requestTable = $responseTable = $globalsTable = $userTable = '';
          
            if (class_exists('Lib_Dispatcher', false) && Lib_Dispatcher::getInstance())
            {
              $context = Lib_Dispatcher::getInstance();
              $settingsTable = self::formatArrayAsHtml(Lib_Debug::settingsAsArray());
              $requestTable  = self::formatArrayAsHtml(Lib_Debug::requestAsArray(Lib_Request::getInstance()));
              $responseTable = self::formatArrayAsHtml(Lib_Debug::responseAsArray(Lib_Response::getInstance()));
              $globalsTable  = self::formatArrayAsHtml(Lib_Debug::globalsAsArray());
            }
            
            }
            
            include $template;
            if (Lib_Config::getVar('app_exception_display')) exit;
            
            return;
            
            
        }
        else
        {
            print $exception->getMessage() . "\n" .$exception->getTraceAsString();
            return;
        }


        
    }

    /**
   * Returns the path for the template error message.
   *
   * @param string  $format The request format
   * @param Boolean $debug  Whether to return a template for the debug mode or not
   *
   * @return string|Boolean false if the template cannot be found for the given format,
   *                        the absolute path to the template otherwise
   */
    static public function getTemplatePathForError($format, $debug)
    {
        $templatePaths = array(
         Lib_Config::getVar('app_path_inc') . '/Data/Errors',
         

        );

        $template = sprintf('%s.%s.php', $debug ? 'exception' : 'error', $format);
        foreach ($templatePaths as $path)
        {
            if (!is_null($path) && is_readable($file = $path.'/'.$template))
            {
                return $file;
            }
        }

        return false;
    }

/**
   * Returns an array of exception traces.
   *
   * @param Exception $exception An Exception implementation instance
   * @param string    $format    The trace format (plain or html)
   *
   * @return array An array of traces
   */
  static protected function getTraces($exception, $format = 'plain')
  {
    $traceData = $exception->getTrace();
    array_unshift($traceData, array(
      'function' => '',
      'file'     => $exception->getFile() != null ? $exception->getFile() : 'n/a',
      'line'     => $exception->getLine() != null ? $exception->getLine() : 'n/a',
      'args'     => array(),
    ));

    $traces = array();
    if ($format == 'html')
    {
      $lineFormat = 'at <strong>%s%s%s</strong>(%s)<br />in <em>%s</em> line %s <a href="#" onclick="toggle(\'%s\'); return false;">...</a><br /><ul id="%s" style="display: %s">%s</ul>';
    }
    else
    {
      $lineFormat = 'at %s%s%s(%s) in %s line %s';
    }
    for ($i = 0, $count = count($traceData); $i < $count; $i++)
    {
      $line = isset($traceData[$i]['line']) ? $traceData[$i]['line'] : 'n/a';
      $file = isset($traceData[$i]['file']) ? $traceData[$i]['file'] : 'n/a';
      $shortFile = preg_replace(array('#^'.preg_quote(Lib_Config::getVar('app_path_inc')).'#', '#^'.preg_quote(realpath(Lib_Config::getVar('path_lib'))).'#'), array('', ''), $file);
      $args = isset($traceData[$i]['args']) ? $traceData[$i]['args'] : array();
      $traces[] = sprintf($lineFormat,
        (isset($traceData[$i]['class']) ? $traceData[$i]['class'] : ''),
        (isset($traceData[$i]['type']) ? $traceData[$i]['type'] : ''),
        $traceData[$i]['function'],
        self::formatArgs($args, false, $format),
        $shortFile,
        $line,
        'trace_'.$i,
        'trace_'.$i,
        $i == 0 ? 'block' : 'none',
        self::fileExcerpt($file, $line)
      );
    }

    return $traces;
  }

  /**
   * Returns an HTML version of an array as YAML.
   *
   * @param array $values The values array
   *
   * @return string An HTML string
   */
  static protected function formatArrayAsHtml($values)
  {
  	Lib_Vendor_Yaml::init();
    return '<pre>'.self::escape(@sfYaml::dump($values)).'</pre>';
  }

  /**
   * Returns an excerpt of a code file around the given line number.
   *
   * @param string $file A file path
   * @param int    $line The selected line number
   *
   * @return string An HTML string
   */
  static protected function fileExcerpt($file, $line)
  {
    if (is_readable($file))
    {
      $content = preg_split('#<br />#', highlight_file($file, true));

      $lines = array();
      for ($i = max($line - 3, 1), $max = min($line + 3, count($content)); $i <= $max; $i++)
      {
        $lines[] = '<li'.($i == $line ? ' class="selected"' : '').'>'.$content[$i - 1].'</li>';
      }

      return '<ol start="'.max($line - 3, 1).'">'.implode("\n", $lines).'</ol>';
    }
  }

  /**
   * Formats an array as a string.
   *
   * @param array   $args   The argument array
   * @param boolean $single
   * @param string  $format The format string (html or plain)
   *
   * @return string
   */
  static protected function formatArgs($args, $single = false, $format = 'html')
  {
    $result = array();

    $single and $args = array($args);

    foreach ($args as $key => $value)
    {
      if (is_object($value))
      {
        $formattedValue = ($format == 'html' ? '<em>object</em>' : 'object').sprintf("('%s')", get_class($value));
      }
      else if (is_array($value))
      {
        $formattedValue = ($format == 'html' ? '<em>array</em>' : 'array').sprintf("(%s)", self::formatArgs($value));
      }
      else if (is_string($value))
      {
        $formattedValue = ($format == 'html' ? sprintf("'%s'", self::escape($value)) : "'$value'");
      }
      else if (is_null($value))
      {
        $formattedValue = ($format == 'html' ? '<em>null</em>' : 'null');
      }
      else
      {
        $formattedValue = $value;
      }
      
      $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", self::escape($key), $formattedValue);
    }

    return implode(', ', $result);
  }
  
  /**
   * Escapes a string value with html entities
   *
   * @param  string  $value
   *
   * @return string
   */
  static protected function escape($value)
  {
    if (!is_string($value))
    {
      return $value;
    }
    
    return htmlspecialchars($value, ENT_QUOTES, Lib_Config::getVar('app_charset'));
  }

}