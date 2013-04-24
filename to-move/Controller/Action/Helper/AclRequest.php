<?php

class Devlopnet_Controller_Action_Helper_AclRequest extends Zend_Controller_Action_Helper_Abstract
{
    
    protected $_aclPlugin;
	protected $_isOnlyFrontControllerRequest = false;
    
    public function __construct(Devlopnet_Controller_Plugin_AclRequest $aclPlugin) {
        $this->_aclPlugin = $aclPlugin;
    }
	
	public function setIsOnlyFrontControllerRequest($flag)
	{
		$this->_isOnlyFrontControllerRequest = (bool)$flag;
	}
	
	public function isOnlyFrontControllerRequest()
	{
		return $this->_isOnlyFrontControllerRequest;
	}
   
    public function preDispatch()
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        if (!method_exists($dispatcher->getControllerClass($this->getRequest()), $dispatcher->getActionMethod($this->getRequest()))) {
            throw new Zend_Controller_Action_Exception('Page not found', 404);
        }
		if ($this->isOnlyFrontControllerRequest()) {
			$request = Zend_Controller_Front::getInstance()->getRequest();
		} else {
			$request = $this->getRequest();
		}
        $this->_aclPlugin->checkRequest($request);
    }
    
}
