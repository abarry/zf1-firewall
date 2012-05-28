<?php

class Johnpaul_View_Helper_FootScript extends Zend_View_Helper_HeadScript
{
    protected $_regKey = 'Helper_FootScript';
    
    public function footScript()
    {
        return call_user_func_array(array($this, 'headScript'), func_get_args());
    }
}