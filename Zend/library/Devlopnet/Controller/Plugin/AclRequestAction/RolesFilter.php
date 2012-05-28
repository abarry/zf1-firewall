<?php

class Devlopnet_Controller_Plugin_AclRequestAction_RolesFilter
	extends Devlopnet_Controller_Plugin_AclRequestAction_Dispatcher
{

	const DENY_ALL = true;
	const ALLOW_ALL = false;

	protected $_rule = self::ALLOW_ALL;
	protected $_except = array();

	public function setExceptRoles(Array $roles)
	{
		$this->_except = array();
		foreach ($roles as $role)
		{
			$this->addExceptRole($role);
		}
		return $this;
	}
	
	public function addExceptRole($name)
	{
		$this->_except[] = (string)$name;
		return $this;
	}
	

	
	public function setRule($flag)
	{
		$this->_rule = (bool)$flag;
	}


	public function perform(Array $requestElements, $role)
	{
		$rolesAuthorized = array();
		if ($this->_rule == self::DENY_ALL) {
			$rolesAuthorized = $this->_except;
		} else {
			$rolesRegistered = $this->_plugin->getAcl()->getRoles();
			$rolesAuthorized = array_diff($rolesRegistered, $this->_except);
		}
		
		if (in_array($role, $rolesAuthorized)) {
			return parent::perform($requestElements, $role);
		}
		
	}
	


}