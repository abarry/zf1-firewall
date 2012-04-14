<?php

/**
 * @author Devlopnet
 * @license Free
 */

class Devlopnet_View_Helper_FullUrl extends Zend_View_Helper_Url
{
  
    /**
     *
     * Aide de vue permettant de créer des liens absolus incluants le nom de domaine
     * 
     * @param array $urlOptions
     * @param string|null $name
     * @param bool $reset
     * @param bool $encode
     * @return string 
     * 
     * @see Zend_View_Helper_Url
     */
    
    public function fullUrl (array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        /**
         * Zend_Controller_Request_Http::getHttpHost() tient compte du port, et l'affiche si non "standard"
         */
        $request = Zend_Controller_Front::getInstance()->getRequest();
        return $request->getScheme() . '://' . $request->getHttpHost() . call_user_func_array('parent::url', func_get_args());
    }
   
}