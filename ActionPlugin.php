<?php

namespace Devlopnet\Zf1\Firewall;

class ActionPlugin extends \Zend_Controller_Action_Helper_Abstract
{

    protected $_aclPlugin;
protected $_isOnlyFrontControllerRequest = false;

    public function __construct(FrontPlugin $aclPlugin) {
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
        $dispatcher = \Zend_Controller_Front::getInstance()->getDispatcher();

        $className = $dispatcher->getControllerClass($this->getRequest());
        $fullClassName = $dispatcher->formatClassName($this->getRequest()->getModuleName(), $className);
        $methodName = $dispatcher->getActionMethod($this->getRequest());

        if (!method_exists($fullClassName, $methodName) && !method_exists($className, $methodName)) {
            throw new \Zend_Controller_Action_Exception('Page not found', 404);
        }
        if ($this->isOnlyFrontControllerRequest()) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        } else {
        $request = $this->getRequest();
        }
        $this->_aclPlugin->checkRequest($request, $this->getResponse());
    }

}
