<?php
class Lib_Helper_Valid {
    
    static public function not_empty($value){
        if (strlen($value) > 0) return true;

        return false;
    }
    
    static public function length_interval($value, $min, $max){
        $length = strlen($value);
        
        if ($length < $min) return false;
        
        if ($max > 0 && $length > $max) return false;
        
        return true;
    }
    
    static public function length($value, $length){
        return (strlen($value) == $length ? true : false);
    }
    
    static public function length_min($value, $length){
        return (strlen($value) < $length ? false : true);
    }
    
    
    static public function positive_int($value){
        $nvalue = (int) $value;
        
        return (($nvalue > 0 && strcmp($nvalue,$value) ==0 ) ? true : false);
    }
    
    static public function validation($value, $validationInfo = array()){
        if (empty($validationInfo)) return true;
        elseif (is_string($validationInfo[0])){
            
            $method = '';
            $args = array();
            $return = '';
            
            if  (count($validationInfo) == 3 && is_array($validationInfo[1])){
                list($method, $args, $return) = $validationInfo;
                array_unshift($args, $value);
            }
            elseif (count($validationInfo) == 2 && is_array($validationInfo[1])){
                list($method, $args) = $validationInfo;
                array_unshift($args, $value);
            }
            elseif (count($validationInfo) == 2 && is_string($validationInfo[1])){
                list($method, $return) = $validationInfo;
                $args[0] = $value;
            }
            elseif (count($validationInfo) == 1){
                $method = $validationInfo[0];
                $args[0] = $value;
            }
            else return false;

            
            if (!call_user_func_array('Lib_Helper_Valid::' . $method, $args)){
                if ($return){
                    $trList = array(':value' => $value);
                    foreach ($args as $idx => $val) $trList[':param' . $idx] = $val;
                    
                    return strtr($return, $trList);
                }
                else return false;
            }
            return true;
        }
        elseif (is_array($validationInfo)){
            foreach ($validationInfo as $valid){
                if (($return = self::validation($value, $valid)) !== true) return $return;
            }
            
            return true;
            
        }
        else ;
        
        return false;
    }
}