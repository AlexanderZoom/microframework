<?php
/**
 * Debug provides some method to help debugging a application.
 *
 */
class Lib_Debug
{
  

  /**
   * Returns PHP information as an array.
   *
   * @return array An array of php information
   */
  public static function phpInfoAsArray()
  {
    $values = array(
      'php'        => phpversion(),
      'os'         => php_uname(),
      'extensions' => get_loaded_extensions(),
    );

    // assign extension version
    if ($values['extensions'])
    {
      foreach ($values['extensions'] as $key => $extension)
      {
        $values['extensions'][$key] = phpversion($extension) ? sprintf('%s (%s)', $extension, phpversion($extension)) : $extension;
      }
    }

    return $values;
  }

  /**
   * Returns PHP globals variables as a sorted array.
   *
   * @return array PHP globals
   */
  public static function globalsAsArray()
  {
    $values = array();
    foreach (array('cookie', 'server', 'get', 'post', 'files', 'env', 'session') as $name)
    {
      if (!isset($GLOBALS['_'.strtoupper($name)]))
      {
        continue;
      }

      $values[$name] = array();
      foreach ($GLOBALS['_'.strtoupper($name)] as $key => $value)
      {
        $values[$name][$key] = $value;
      }
      ksort($values[$name]);
    }

    ksort($values);

    return $values;
  }

  /**
   * Returns Config variables as a sorted array.
   *
   * @return array Config variables
   */
  public static function settingsAsArray()
  {
    $config = Lib_Config::getAll();

    ksort($config);

    return $config;
  }

  /**
   * Returns web request parameter holders as an array.
   *
   * @param WebRequest $request A WebRequest instance
   *
   * @return array The request parameter holders
   */
  public static function requestAsArray(Lib_Request $request = null)
  {
    if (!$request)
    {
      return array();
    }

    return array(
      'parameterHolder' => self::flattenParameterHolder($request->getParameterHolder(), true),
    );
  }

  /**
   * Returns web response parameters as an array.
   *
   * @param WebResponse $response A WebResponse instance
   *
   * @return array The response parameters
   */
  public static function responseAsArray(Lib_Response $response = null)
  {
    if (!$response)
    {
      return array();
    }

    return array(
      'options'     => $response->getOptions(),
      'cookies'     => method_exists($response, 'getCookies')     ? $response->getCookies() : array(),
      'httpHeaders' => method_exists($response, 'getHttpHeaders') ? $response->getHttpHeaders() : array(),
    );
  }

  /**
   * Returns user parameters as an array.
   *
   * @param BaseUser $user A User instance
   *
   * @return array The user parameters
   */
  public static function userAsArray(Lib_User $user = null)
  {
    if (!$user)
    {
      return array();
    }

    return array(
      'options'         => $user->getOptions(),
      'attributeHolder' => self::flattenParameterHolder($user->getAttributeHolder(), true),
      'culture'         => $user->getCulture(),
    );
  }

  /**
   * Returns a parameter holder as an array.
   *
   * @param ParameterHolder $parameterHolder A ParameterHolder instance
   * @param boolean $removeObjects when set to true, objects are removed. default is false for BC.
   *
   * @return array The parameter holder as an array
   */
  public static function flattenParameterHolder($parameterHolder, $removeObjects = false)
  {
    $values = array();
    if ($parameterHolder instanceof NamespacedParameterHolder)
    {
      foreach ($parameterHolder->getNamespaces() as $ns)
      {
        $values[$ns] = array();
        foreach ($parameterHolder->getAll($ns) as $key => $value)
        {
          $values[$ns][$key] = $value;
        }
        ksort($values[$ns]);
      }
    }
    else
    {
      foreach ($parameterHolder->getAll() as $key => $value)
      {
        $values[$key] = $value;
      }
    }

    if ($removeObjects)
    {
      $values = self::removeObjects($values);
    }

    ksort($values);

    return $values;
  }

  /**
   * Removes objects from the array by replacing them with a String containing the class name.
   *
   * @param array $values an array
   *
   * @return array The array without objects
   */
  public static function removeObjects($values)
  {
    $nvalues = array();
    foreach ($values as $key => $value)
    {
      if (is_array($value))
      {
        $nvalues[$key] = self::removeObjects($value);
      }
      else if (is_object($value))
      {
        $nvalues[$key] = sprintf('%s Object()', get_class($value));
      }
      else
      {
        $nvalues[$key] = $value;
      }
    }

    return $nvalues;
  }
}
