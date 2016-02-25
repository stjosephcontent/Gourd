<?php

function call_object_method_array($func, $obj, $params=false){
    if (!method_exists($obj,$func)){        
        // object doesn't have function, return null
        return (null);
    }
    // no params so just return function
    if (!$params){
        return ($obj->$func());
    }        
    // build eval string to execute function with parameters        
    $pstr='';
    $p=0;
    foreach ($params as $param){
        $pstr.=$p>0 ? ', ' : '';
        $pstr.='$params['.$p.']';
        $p++;
    }
    $evalstr='$retval=$obj->'.$func.'('.$pstr.');';
    $evalok=eval($evalstr);
    // if eval worked ok, return value returned by function
    if ($evalok){
        return ($retval);
    } else {
        return (null);
    }        
    return (null);   
}

Abstract class ExtensionBridge
{
    // array containing all the extended classes
    private $_exts = array();
    public $_this;
        
    function __construct(){$_this = $this;}
    
    public function addExt($object)
    {
        $this->_exts[]=$object;
    }
    
    public function __get($varname)
    {
        foreach($this->_exts as $ext)
        {
            if(property_exists($ext,$varname))
            return $ext->$varname;
        }
    }
    
    public function __call($method,$args)
    {
        foreach($this->_exts as $ext)
        {
            if(method_exists($ext,$method))
            return call_object_method_array($method,$ext,$args);
        }
        throw new Exception("This Method {$method} doesn't exists");
    }
    
    
}
?>