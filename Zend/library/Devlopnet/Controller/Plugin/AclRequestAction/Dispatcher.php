<?php

class Devlopnet_Controller_Plugin_AclRequestAction_Dispatcher
	extends Devlopnet_Controller_Plugin_AclRequestAction_Abstract
{

	protected $_actions = array();
	
	public function addAction(Devlopnet_Controller_Plugin_AclRequestAction_Abstract $action)
	{
		$this->_actions[] = $action;
        return $this;
	}
	
	public function setActions(Array $actions)
	{
		$this->_actions = array();
		foreach ($actions as $action)
		{
			$this->addAction($action);
		}
	}
	
	public function perform(Array $requestElements, $role)
	{
		foreach ($this->getActions() as $action)
		{
			$action->setPlugin($this->_plugin);
			$action->perform($requestElements, $role);
		}
	}
	
	public function getActions()
	{
		return $this->_actions;
	}

}