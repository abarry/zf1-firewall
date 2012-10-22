<?php

class Devlopnet_Controller_Plugin_AclRequest extends Zend_Controller_Plugin_Abstract
{
    
    protected $_acl;
    protected $_userRole;
    protected $_resourceDelimiter = ':';
    protected $_resourceAllowAll = '*';
    protected $_allowActionsDispatcher;
    protected $_denyActionsDispatcher;
    
    public function __construct()
    {
        $actionHelper = new Devlopnet_Controller_Action_Helper_AclRequest($this);
        Zend_Controller_Action_HelperBroker::addHelper($actionHelper);
        $this->_acl = new Zend_Acl;
    }   
    
    public function getAcl()
    {
        return $this->_acl;
    }
    
    public function setCurrentRole($role)
    {
        $this->_userRole = $role;
        return $this;
    }
    
    public function getCurrentRole()
    {
        return $this->_userRole;
    }
    
    public function formatRoute($route)
    {
        if (is_string($route)) {
            $strRoute = $route;
        } elseif (is_array($route)) {
            $arrayRoute = array();
            foreach (array('module', 'controller', 'action') as $elementName) {
                if (isset($route[$elementName])) {
                    $arrayRoute[] = $route[$elementName];
                }
            }
            if (empty ($arrayRoute)) {
                $arrayRoute = $route;
            }
            $strRoute = implode($this->_resourceDelimiter, $arrayRoute);
        } else {
            throw new InvalidArgumentException('Invalid route format');
        }
        return $strRoute;
    }
    
    public function addRole($name, $inheritName, Array $routes)
    {
        $acl = $this->_acl;
        $role = new Zend_Acl_Role($name);
        $resources = array();
        foreach ($routes as $route)
        {
            $resources[] = $this->formatRoute($route);
        }
        $acl->addRole($role, $inheritName);
        foreach ($resources as $resource)
        {
            if (!$acl->has($resource)) {
                $acl->addResource($resource);
            }
            $acl->allow($role, $resource);
        }

        return $this;
    }
    
    public function checkRequest(Zend_Controller_Request_Abstract $request = null)
    {       
        $request = is_null($request) ? $this->getRequest() : $request;
        $requestElements = array(
            'module'        => $request->getModuleName(),
            'controller'    => $request->getControllerName(),
            'action'        => $request->getActionName()
        );
        
        if ($this->isAllowed($requestElements, $this->_userRole)) {
            return $this->_checkEvtAllowed($requestElements, $this->_userRole);
        } else {
            return $this->_checkEvtDenied($requestElements, $this->_userRole);
        }
       
    }
    
    /**
     *
     * @return Devlopnet_Controller_Plugin_AclRequestAction_Dispatcher
     */
    public function getAllowActionDispatcher()
    {
        if (!$this->_allowActionsDispatcher) {
            $this->_allowActionsDispatcher = new Devlopnet_Controller_Plugin_AclRequestAction_Dispatcher;
        }
        return $this->_allowActionsDispatcher;
    }
    /**
     *
     * @return Devlopnet_Controller_Plugin_AclRequestAction_Dispatcher
     */
    public function getDenyActionDispatcher()
    {
        if (!$this->_denyActionsDispatcher) {
            $this->_denyActionsDispatcher = new Devlopnet_Controller_Plugin_AclRequestAction_Dispatcher;
			$exceptionAction = new Devlopnet_Controller_Plugin_AclRequestAction_Exception(array(
                'exception' =>  'Zend_Controller_Action_Exception',
                'message'   =>  'Forbidden',
                'code'      =>  403
            ));
            $this->_denyActionsDispatcher->addAction($exceptionAction);
        }
        return $this->_denyActionsDispatcher;
    }
        
    protected function _checkEvtAllowed($requestElements, $userRole)
    {
        $this->getAllowActionDispatcher()
             ->setPlugin($this) 
             ->perform($requestElements, $userRole);
    }
    
    protected function _checkEvtDenied($requestElements, $userRole)
    {
        $this->getDenyActionDispatcher()
             ->setPlugin($this) 
             ->perform($requestElements, $userRole);
    }
    
    public function isAllowed ($route, $role)
    {
        $strRequest = $this->formatRoute($route);
        $requestElements = explode($this->_resourceDelimiter, $strRequest);
        $requestToCheck = array(
            $this->formatRoute(array($requestElements[0], $requestElements[1], $requestElements[2])),
            $this->formatRoute(array($requestElements[0], $requestElements[1], $this->_resourceAllowAll)),
            $this->formatRoute(array($requestElements[0], $this->_resourceAllowAll, $this->_resourceAllowAll))
        );
        $isAllowed = false;
        foreach ($requestToCheck as $resource)
        {
            if ($this->_acl->has($resource))
            {
                if ($this->_acl->isAllowed($role, $resource))
                {
                    $isAllowed = true;
                    break;
                }
            }
        }
        return $isAllowed;
    }
    
    
}