<?php

class JohnPaul_View_Helper_FormNumber extends Zend_View_Helper_FormText
{
    public function formNumber()
    {
        $call = call_user_func_array('parent::formText', func_get_args());
        return str_replace('<input type="text"', '<input type="number"', $call);
    }
}