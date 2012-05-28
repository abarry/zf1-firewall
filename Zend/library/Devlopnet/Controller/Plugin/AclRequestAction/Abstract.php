<?php

abstract class Devlopnet_Controller_Plugin_AclRequestAction_Abstract
{

	protected $_plugin;
	
	public function __construct(Array $options = array())
	{
		foreach ($options as $key => $args)
		{
			$this->{'set'.ucfirst($key)}($args);
		}
		$this->init();
	}
	
	public function init()
	{
	
	}
	
	public function setPlugin(Devlopnet_Controller_Plugin_AclRequest $plugin)
	{
		$this->_plugin = $plugin;
		return $this;
	}
	
	abstract public function perform(Array $requestElements, $role);

}