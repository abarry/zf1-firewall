<?php

class Devlopnet_Controller_Plugin_AclRequestAction_Exception
	extends Devlopnet_Controller_Plugin_AclRequestAction_Abstract
{

	protected $_callback;
	
	public function setCallback($callback)
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Callback must be callable');
		}
		$this->_callback = $callback;
	}

	public function perform(Array $requestElements, $role)
	{
		call_user_func($this->_callback, $requestElements, $role);
	}

}