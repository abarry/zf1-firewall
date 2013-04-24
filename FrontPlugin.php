<?php

namespace Devlopnet\Zf1\Firewall;

class FrontPlugin extends \Zend_Controller_Plugin_Abstract
{
    protected $_acl;
    protected $_userRole;
    protected $_resourceDelimiter = ':';
    protected $_resourceAllowAll = '*';
    protected $_onDenyCallback;

    public function __construct()
    {
        $actionHelper = new ActionPlugin($this);
        \Zend_Controller_Action_HelperBroker::addHelper($actionHelper);
        $this->_acl = new \Zend_Acl;
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
            throw new \InvalidArgumentException('Invalid route format');
        }
        return $strRoute;
    }

    public function addRole($name, $inheritName = null)
    {
        $acl = $this->_acl;
        $role = new \Zend_Acl_Role($name);
        $acl->addRole($role, $inheritName);

        return $this;
    }

    public function allow($role, $route)
    {
        $acl = $this->_acl;

        if (!$acl->hasRole($role)) {
            throw new \InvalidArgumentException('Unknown role');
        }

        $resource = $this->formatRoute($route);

        if (!$acl->has($resource)) {
            $acl->addResource($resource);
        }
        $acl->allow($role, $resource);

        return $this;
    }

    public function allowMulti($role, array $routes)
    {
        foreach ($routes as $route) {
            $this->allow($role, $route);
        }

        return $this;
    }

    public function checkRequest(
        \Zend_Controller_Request_Abstract $request = null,
        \Zend_Controller_Response_Abstract $response = null
    ) {
        $request = is_null($request) ? $this->getRequest() : $request;
        $response = is_null($response) ? $this->getResponse() : $response;
        $requestElements = array(
            'module' => $request->getModuleName(),
            'controller' => $request->getControllerName(),
            'action' => $request->getActionName()
        );

        if (!$this->isAllowed($requestElements, $this->_userRole)) {
            if (!$this->_onDenyCallback) {
                throw new ForbiddenException;
            }
            call_user_func_array($this->_onDenyCallback, array($this->_userRole, $request, $response));
        }

    }

    public function onDeny($callback = null)
    {
        if(!is_null($callback) && !is_callable($callback)) {
            throw new \InvalidArgumentException('Expected callback or null');
        }

        $this->_onDenyCallback = $callback;
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
