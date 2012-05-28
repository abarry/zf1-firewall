<?php

class Devlopnet_Controller_Action_Helper_AclRequest extends Zend_Controller_Action_Helper_Abstract
{
    
    protected $_aclPlugin;
    
    public function __construct(Devlopnet_Controller_Plugin_AclRequest $aclPlugin) {
        $this->_aclPlugin = $aclPlugin;
    }
   
    public function preDispatch()
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        if (!method_exists($dispatcher->getControllerClass($this->getRequest()), $dispatcher->getActionMethod($this->getRequest()))) {
            throw new Zend_Controller_Action_Exception('Page not found', 404);
        }
        $this->_aclPlugin->checkRequest();
    }
    
}
