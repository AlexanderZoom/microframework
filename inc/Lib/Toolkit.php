<?php
class Lib_Toolkit
{

	public static function camelize($name)
	{

		$name = preg_split( "/\.|_|-/", $name );
		foreach ( $name as $key => $value )
		{
			$name [$key] = ucfirst( $value );
		}
		
		return implode( "", $name );
	}

	
	public static function JoinList($list, $delemiter = ' :: ',
	                                $dropEmptyValue = true)
	{

		if ($dropEmptyValue)
		{
			$newList = array ();
			foreach ( $list as $key => $value )
			{
				if ($value)
					$newList [] = $value;
			}
			
			$list = $newList;
		}
		
		return implode( $delemiter, $list );
	}

	public static function escape_var4db($var)
	{

		if (is_array( $var ))
		{
			foreach ( $var as $idx => $val )
			{
				$var [$idx] = mysql_escape_string( $val );
			}
		} else
			$var = mysql_escape_string( $var );
		
		return $var;
	}

	/**
	 * Determine if a filesystem path is absolute.
	 *
	 * @param  path $path  A filesystem path.
	 *
	 * @return bool true, if the path is absolute, otherwise false.
	 */
	public static function isPathAbsolute($path)
	{

		if ($path [0] == '/' || $path [0] == '\\' || (strlen( $path ) > 3 && ctype_alpha( $path [0] ) && $path [1] == ':' && ($path [2] == '\\' || $path [2] == '/')))
		{
			return true;
		}
		
		return false;
	}

	/**
	 * Determine if a lock file is present.
	 *
	 * @param  string  $lockFile             Name of the lock file.
	 * @param  integer $maxLockFileLifeTime  A max amount of life time for the lock file.
	 *
	 * @return bool true, if the lock file is present, otherwise false.
	 */
	public static function hasLockFile($lockFile, $maxLockFileLifeTime = 0)
	{

		$isLocked = false;
		if (is_readable( $lockFile ) && ($last_access = fileatime( $lockFile )))
		{
			$now = time();
			$timeDiff = $now - $last_access;
			
			if (! $maxLockFileLifeTime || $timeDiff < $maxLockFileLifeTime)
			{
				$isLocked = true;
			} else
			{
				$isLocked = @unlink( $lockFile ) ? false : true;
			}
		}
		
		return $isLocked;
	}

	/**
	 * Strips comments from php source code
	 *
	 * @param  string $source  PHP source code.
	 *
	 * @return string Comment free source code.
	 */
	public static function stripComments($source)
	{

		if (! Lib_Config::getAppVar( 'app_config_strip_comments' ) || ! function_exists( 'token_get_all' ))
		{
			return $source;
		}
		
		$ignore = array (T_COMMENT => true, T_DOC_COMMENT => true );
		$output = '';
		
		foreach ( token_get_all( $source ) as $token )
		{
			// array
			if (isset( $token [1] ))
			{
				// no action on comments
				if (! isset( $ignore [$token [0]] ))
				{
					// anything else -> output "as is"
					$output .= $token [1];
				}
			} else
			{
				// simple 1-character token
				$output .= $token;
			}
		}
		
		return $output;
	}

	/**
	 * Strip slashes recursively from array
	 *
	 * @param  array $value  the value to strip
	 *
	 * @return array clean value with slashes stripped
	 */
	public static function stripslashesDeep($value)
	{

		return is_array( $value ) ? array_map( array ('Lib_Toolkit', 'stripslashesDeep' ), $value ) : stripslashes( $value );
	}

	// code from php at moechofe dot com (array_merge comment on php.net)
	/*
    * array arrayDeepMerge ( array array1 [, array array2 [, array ...]] )
    *
    * Like array_merge
    *
    *  arrayDeepMerge() merges the elements of one or more arrays together so
    * that the values of one are appended to the end of the previous one. It
    * returns the resulting array.
    *  If the input arrays have the same string keys, then the later value for
    * that key will overwrite the previous one. If, however, the arrays contain
    * numeric keys, the later value will not overwrite the original value, but
    * will be appended.
    *  If only one array is given and the array is numerically indexed, the keys
    * get reindexed in a continuous way.
    *
    * Different from array_merge
    *  If string keys have arrays for values, these arrays will merge recursively.
    */
	public static function arrayDeepMerge()
	{

		switch (func_num_args())
		{
			case 0 :
				return false;
			case 1 :
				return func_get_arg( 0 );
			case 2 :
				$args = func_get_args();
				$args [2] = array ();
				if (is_array( $args [0] ) && is_array( $args [1] ))
				{
					foreach ( array_unique( array_merge( array_keys( $args [0] ), array_keys( $args [1] ) ) ) as $key )
					{
						$isKey0 = array_key_exists( $key, $args [0] );
						$isKey1 = array_key_exists( $key, $args [1] );
						if ($isKey0 && $isKey1 && is_array( $args [0] [$key] ) && is_array( $args [1] [$key] ))
						{
							$args [2] [$key] = self::arrayDeepMerge( $args [0] [$key], $args [1] [$key] );
						} else if ($isKey0 && $isKey1)
						{
							$args [2] [$key] = $args [1] [$key];
						} else if (! $isKey1)
						{
							$args [2] [$key] = $args [0] [$key];
						} else if (! $isKey0)
						{
							$args [2] [$key] = $args [1] [$key];
						}
					}
					return $args [2];
				} else
				{
					return $args [1];
				}
			default :
				$args = func_get_args();
				$args [1] = Lib_Toolkit::arrayDeepMerge( $args [0], $args [1] );
				array_shift( $args );
				return call_user_func_array( array ('Toolkit', 'arrayDeepMerge' ), $args );
				break;
		}
	}

	/**
	 * Converts string to array
	 *
	 * @param  string $string  the value to convert to array
	 *
	 * @return array
	 */
	public static function stringToArray($string)
	{

		preg_match_all( '/
      \s*(\w+)              # key                               \\1
      \s*=\s*               # =
      (\'|")?               # values may be included in \' or " \\2
      (.*?)                 # value                             \\3
      (?(2) \\2)            # matching \' or " if needed        \\4
      \s*(?:
        (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
      )
    /x', $string, $matches, PREG_SET_ORDER );
		
		$attributes = array ();
		foreach ( $matches as $val )
		{
			$attributes [$val [1]] = self::literalize( $val [3] );
		}
		
		return $attributes;
	}

	/**
	 * Finds the type of the passed value, returns the value as the new type.
	 *
	 * @param  string $value
	 * @param  bool   $quoted  Quote?
	 *
	 * @return mixed
	 */
	public static function literalize($value, $quoted = false)
	{

		// lowercase our value for comparison
		$value = trim( $value );
		$lvalue = strtolower( $value );
		
		if (in_array( $lvalue, array ('null', '~', '' ) ))
		{
			$value = null;
		} else if (in_array( $lvalue, array ('true', 'on', '+', 'yes' ) ))
		{
			$value = true;
		} else if (in_array( $lvalue, array ('false', 'off', '-', 'no' ) ))
		{
			$value = false;
		} else if (ctype_digit( $value ))
		{
			$value = ( int ) $value;
		} else if (is_numeric( $value ))
		{
			$value = ( float ) $value;
		} else
		{
			$value = self::replaceConstants( $value );
			if ($quoted)
			{
				$value = '\'' . str_replace( '\'', '\\\'', $value ) . '\'';
			}
		}
		
		return $value;
	}

	/**
	 * Replaces constant identifiers in a scalar value.
	 *
	 * @param  string $value  the value to perform the replacement on
	 *
	 * @return string the value with substitutions made
	 */
	public static function replaceConstants($value)
	{

		return is_string( $value ) ? preg_replace_callback( '/%(.+?)%/', create_function( '$v', 'return Lib_Config::has(strtolower($v[1])) ? Lib_Config::get(strtolower($v[1])) : "%{$v[1]}%";' ), $value ) : $value;
	}

	/**
	 * Returns subject replaced with regular expression matchs
	 *
	 * @param mixed $search        subject to search
	 * @param array $replacePairs  array of search => replace pairs
	 */
	public static function pregtr($search, $replacePairs)
	{

		return preg_replace( array_keys( $replacePairs ), array_values( $replacePairs ), $search );
	}

	/**
	 * Checks if array values are empty
	 *
	 * @param  array $array  the array to check
	 * @return boolean true if empty, otherwise false
	 */
	public static function isArrayValuesEmpty($array)
	{

		static $isEmpty = true;
		foreach ( $array as $value )
		{
			$isEmpty = (is_array( $value )) ? self::isArrayValuesEmpty( $value ) : (strlen( $value ) == 0);
			if (! $isEmpty)
			{
				break;
			}
		}
		
		return $isEmpty;
	}

	/**
	 * Checks if a string is an utf8.
	 *
	 * Yi Stone Li<yili@yahoo-inc.com>
	 * Copyright (c) 2007 Yahoo! Inc. All rights reserved.
	 * Licensed under the BSD open source license
	 *
	 * @param string
	 *
	 * @return bool true if $string is valid UTF-8 and false otherwise.
	 */
	public static function isUTF8($string)
	{

		for($idx = 0, $strlen = strlen( $string ); $idx < $strlen; $idx ++)
		{
			$byte = ord( $string [$idx] );
			
			if ($byte & 0x80)
			{
				if (($byte & 0xE0) == 0xC0)
				{
					// 2 byte char
					$bytes_remaining = 1;
				} else if (($byte & 0xF0) == 0xE0)
				{
					// 3 byte char
					$bytes_remaining = 2;
				} else if (($byte & 0xF8) == 0xF0)
				{
					// 4 byte char
					$bytes_remaining = 3;
				} else
				{
					return false;
				}
				
				if ($idx + $bytes_remaining >= $strlen)
				{
					return false;
				}
				
				while ( $bytes_remaining -- )
				{
					if ((ord( $string [++ $idx] ) & 0xC0) != 0x80)
					{
						return false;
					}
				}
			}
		}
		
		return true;
	}

	/**
	 * Returns a reference to an array value for a path.
	 *
	 * @param array  $values   The values to search
	 * @param string $name     The token name
	 * @param array  $default  Default if not found
	 *
	 * @return array reference
	 */
	public static function &getArrayValueForPathByRef(&$values, $name, $default = null)
	{

		if (false === $offset = strpos( $name, '[' ))
		{
			$return = $default;
			if (isset( $values [$name] ))
			{
				$return = &$values [$name];
			}
			return $return;
		}
		
		if (! isset( $values [substr( $name, 0, $offset )] ))
		{
			return $default;
		}
		
		$array = &$values [substr( $name, 0, $offset )];
		
		while ( false !== $pos = strpos( $name, '[', $offset ) )
		{
			$end = strpos( $name, ']', $pos );
			if ($end == $pos + 1)
			{
				// reached a []
				if (! is_array( $array ))
				{
					return $default;
				}
				break;
			} else if (! isset( $array [substr( $name, $pos + 1, $end - $pos - 1 )] ))
			{
				return $default;
			} else if (is_array( $array ))
			{
				$array = &$array [substr( $name, $pos + 1, $end - $pos - 1 )];
				$offset = $end;
			} else
			{
				return $default;
			}
		}
		
		return $array;
	}

	/**
	 * Returns an array value for a path.
	 *
	 * @param array  $values   The values to search
	 * @param string $name     The token name
	 * @param array  $default  Default if not found
	 *
	 * @return array
	 */
	public static function getArrayValueForPath($values, $name, $default = null)
	{

		if (false === $offset = strpos( $name, '[' ))
		{
			return isset( $values [$name] ) ? $values [$name] : $default;
		}
		
		if (! isset( $values [substr( $name, 0, $offset )] ))
		{
			return $default;
		}
		
		$array = $values [substr( $name, 0, $offset )];
		
		while ( false !== $pos = strpos( $name, '[', $offset ) )
		{
			$end = strpos( $name, ']', $pos );
			if ($end == $pos + 1)
			{
				// reached a []
				if (! is_array( $array ))
				{
					return $default;
				}
				break;
			} else if (! isset( $array [substr( $name, $pos + 1, $end - $pos - 1 )] ))
			{
				return $default;
			} else if (is_array( $array ))
			{
				$array = $array [substr( $name, $pos + 1, $end - $pos - 1 )];
				$offset = $end;
			} else
			{
				return $default;
			}
		}
		
		return $array;
	}

	/**
	 * Returns true if the a path exists for the given array.
	 *
	 * @param array  $values  The values to search
	 * @param string $name    The token name
	 *
	 * @return bool
	 */
	public static function hasArrayValueForPath($values, $name)
	{

		if (false === $offset = strpos( $name, '[' ))
		{
			return array_key_exists( $name, $values );
		}
		
		if (! isset( $values [substr( $name, 0, $offset )] ))
		{
			return false;
		}
		
		$array = $values [substr( $name, 0, $offset )];
		while ( false !== $pos = strpos( $name, '[', $offset ) )
		{
			$end = strpos( $name, ']', $pos );
			if ($end == $pos + 1)
			{
				// reached a []
				return is_array( $array );
			} else if (! isset( $array [substr( $name, $pos + 1, $end - $pos - 1 )] ))
			{
				return false;
			} else if (is_array( $array ))
			{
				$array = $array [substr( $name, $pos + 1, $end - $pos - 1 )];
				$offset = $end;
			} else
			{
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Removes a path for the given array.
	 *
	 * @param array  $values   The values to search
	 * @param string $name     The token name
	 * @param mixed  $default  The default value to return if the name does not exist
	 */
	public static function removeArrayValueForPath(&$values, $name, $default = null)
	{

		if (false === $offset = strpos( $name, '[' ))
		{
			if (isset( $values [$name] ))
			{
				$value = $values [$name];
				unset( $values [$name] );
				
				return $value;
			} else
			{
				return $default;
			}
		}
		
		if (! isset( $values [substr( $name, 0, $offset )] ))
		{
			return $default;
		}
		
		$value = &$values [substr( $name, 0, $offset )];
		
		while ( false !== $pos = strpos( $name, '[', $offset ) )
		{
			$end = strpos( $name, ']', $pos );
			if ($end == $pos + 1)
			{
				// reached a []
				if (! is_array( $value ))
				{
					return $default;
				}
				break;
			} else if (! isset( $value [substr( $name, $pos + 1, $end - $pos - 1 )] ))
			{
				return $default;
			} else if (is_array( $value ))
			{
				$parent = &$value;
				$key = substr( $name, $pos + 1, $end - $pos - 1 );
				$value = &$value [$key];
				$offset = $end;
			} else
			{
				return $default;
			}
		}
		
		if ($key)
		{
			unset( $parent [$key] );
		}
		
		return $value;
	}

	/**
	 * Get path to php cli.
	 *
	 * @throws Exception If no php cli found
	 * @return string
	 */
	public static function getPhpCli()
	{

		$path = getenv( 'PATH' ) ? getenv( 'PATH' ) : getenv( 'Path' );
		$suffixes = DIRECTORY_SEPARATOR == '\\' ? (getenv( 'PATHEXT' ) ? explode( PATH_SEPARATOR, getenv( 'PATHEXT' ) ) : array ('.exe', '.bat', '.cmd', '.com' )) : array ('' );
		foreach ( array ('php5', 'php' ) as $phpCli )
		{
			foreach ( $suffixes as $suffix )
			{
				foreach ( explode( PATH_SEPARATOR, $path ) as $dir )
				{
					if (is_file( $file = $dir . DIRECTORY_SEPARATOR . $phpCli . $suffix ) && is_executable( $file ))
					{
						return $file;
					}
				}
			}
		}
		
		throw new Lib_Exception_Global( 'Unable to find PHP executable.' );
	}

	/**
	 * From PEAR System.php
	 *
	 * LICENSE: This source file is subject to version 3.0 of the PHP license
	 * that is available through the world-wide-web at the following URI:
	 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
	 * the PHP License and are unable to obtain it through the web, please
	 * send a note to license@php.net so we can mail you a copy immediately.
	 *
	 * @author     Tomas V.V.Cox <cox@idecnet.com>
	 * @copyright  1997-2006 The PHP Group
	 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
	 */
	public static function getTmpDir()
	{

		if (DIRECTORY_SEPARATOR == '\\')
		{
			if ($var = isset( $_ENV ['TEMP'] ) ? $_ENV ['TEMP'] : getenv( 'TEMP' ))
			{
				return $var;
			}
			if ($var = isset( $_ENV ['TMP'] ) ? $_ENV ['TMP'] : getenv( 'TMP' ))
			{
				return $var;
			}
			if ($var = isset( $_ENV ['windir'] ) ? $_ENV ['windir'] : getenv( 'windir' ))
			{
				return $var;
			}
			
			return getenv( 'SystemRoot' ) . '\temp';
		}
		
		if ($var = isset( $_ENV ['TMPDIR'] ) ? $_ENV ['TMPDIR'] : getenv( 'TMPDIR' ))
		{
			return $var;
		}
		
		return '/tmp';
	}

	/**
	 * Converts strings to UTF-8 via iconv. NB, the result may not by
	 * UTF-8 if the conversion failed.
	 *
	 * This file comes from Prado (BSD License)
	 *
	 * @param  string $string string to convert to UTF-8
	 * @param  string $from   current encoding
	 *
	 * @return string UTF-8 encoded string, original string if iconv failed.
	 */
	static public function I18N_toUTF8($string, $from)
	{

		$from = strtoupper( $from );
		if ($from != 'UTF-8')
		{
			$s = iconv( $from, 'UTF-8', $string ); // to UTF-8
			

			return $s !== false ? $s : $string; // it could return false
		}
		
		return $string;
	}

	/**
	 * Converts UTF-8 strings to a different encoding. NB. The result
	 * may not have been encoded if iconv fails.
	 *
	 * This file comes from Prado (BSD License)
	 *
	 * @param  string $string  the UTF-8 string for conversion
	 * @param  string $to      new encoding
	 *
	 * @return string encoded string.
	 */
	static public function I18N_toEncoding($string, $to)
	{

		$to = strtoupper( $to );
		if ($to != 'UTF-8')
		{
			$s = iconv( 'UTF-8', $to, $string );
			
			return $s !== false ? $s : $string;
		}
		
		return $string;
	}

	/**
	 * Adds a path to the PHP include_path setting.
	 *
	 * @param   mixed  $path     Single string path or an array of paths
	 * @param   string $position Either 'front' or 'back'
	 *
	 * @return  string The old include path
	 */
	static public function addIncludePath($path, $position = 'front')
	{

		if (is_array( $path ))
		{
			foreach ( 'front' == $position ? array_reverse( $path ) : $path as $p )
			{
				self::addIncludePath( $p, $position );
			}
			
			return;
		}
		
		$paths = explode( PATH_SEPARATOR, get_include_path() );
		
		// remove what's already in the include_path
		if (false !== $key = array_search( realpath( $path ),
		                                   array_map( 'realpath', $paths ) )){
			unset( $paths [$key] );
		}
		
		switch ($position)
		{
			case 'front' :
				array_unshift( $paths, $path );
				break;
			case 'back' :
				$paths [] = $path;
				break;
			default :
				throw new InvalidArgumentException(
				          sprintf( 'Unrecognized position: "%s"', $position ) );
		}
		
		return set_include_path( join( PATH_SEPARATOR, $paths ) );
	}

	static public function stripLink($link, $maxChars = 80, $replaceText = '[..]')
	{

		if (strlen( $link ) > $maxChars)
		{
			$right = $left = ( int ) $maxChars / 2;
			//$right = $left + ($maxChars % 2);
			$leftPart = substr( $link, 0, $left );
			$rightPart = substr( $link, strlen( $link ) - $right );
			
			$link = $leftPart . $replaceText . $rightPart;
		
		}
		
		return $link;
	}

	public static function generatePassword($length)
	{

		$charList = array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd',
		                   'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
		                   'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
		                   'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
		                   'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
		                   'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );
		
		shuffle( $charList );
		$maxChars = count( $charList ) - 1;
		$pass = '';
		
		for($i = 0; $i < $length; $i ++)
		{
			$pass .= $charList [mt_rand( 0, $maxChars )];
		}
		
		return $pass;
	}
	

	public static function setType($type, $value)
	{

		switch ($type)
		{
			case 'int' :
				return ( int ) $value;
				break;
			
			case 'float' :
				return ( float ) $value;
				break;
			
			case 'string' :
				return ( string ) $value;
				break;
			
			default :
				return null;
		}
	}
}
?>