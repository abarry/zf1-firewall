<?php

class Devlopnet_Controller_Plugin_AclRequestAction_Exception
	extends Devlopnet_Controller_Plugin_AclRequestAction_Abstract
{

	protected $_exception = 'Exception';
	protected $_message = '';
	protected $_code;
	
	public function setException($name)
	{
		if (!is_string($name) || ($name !== 'Exception' && !is_subclass_of($name, 'Exception'))) {
			throw new InvalidArgumentException('Invalid Exception classname');
		}
		$this->_exception = $name;
	}
	
	public function setMessage($msg)
	{
		$this->_message = (string)$msg;
	}
	
	public function setCode($code)
	{
		$this->_code = (int)$code;
	}

	public function perform(Array $requestElements, $role)
	{
		throw new $this->_exception($this->_message, $this->_code);
	}

}