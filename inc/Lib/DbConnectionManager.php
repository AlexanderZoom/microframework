<?php
class Lib_DbConnectionManager
{
    protected static $db = array();
    
    
    /**
     *
     * @param string $label
     * @throws Lib_Exception_DbConnectionManager
     * @return Lib_Db
     */
	public static function getDb ($label = 'default'){
	    if (!empty(self::$db[$label])) return self::$db[$label];
		
	    throw new Lib_Exception_DbConnectionManager("Db Connection {$label} not found");
	}
	
	/**
	 *
	 * @param string $label
	 * @param string $config  array(
	 'server_type' => 'mysql',
	 'host' => '',
	 'user' => 'root',
	 'pass' => '',
	 'dbname' => '',
	 'charset' => 'utf8',
	
	 ),
	 * @throws Lib_Exception_DbConnectionManager
	 * @return Lib_Db
	 */
	public static function initDb ($config, $label = 'default'){
	
	    if (!empty(self::$db[$label])){
	        throw new Lib_Exception_DbConnectionManager("Db Connection {$label} exist");
	    }
	
	    if (empty($config['server_type'])){
	        throw new Lib_Exception_DbConnectionManager("Db Connection Manager Error: Server Type is empty");
	    }
	
	    $connectionManagerClass = 'Lib_Db_' . ucfirst(strtolower($config['server_type']));
	
	    if (!class_exists($connectionManagerClass)){
	        throw new Lib_Exception_DbConnectionManager("Db Connection Manager Error: Class {$connectionManagerClass} not found");
	    }
	
	    $connManager = new $connectionManagerClass($config);
	
	    self::$db[$label] = $connManager;
	
	    return $connManager;
	}
}
?>