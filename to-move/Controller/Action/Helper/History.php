<?php

/**
* @licence Libre
* @author Devlopnet
* @todo Refactoring ; Non testé depuis de nombreuses modifications
*
*/

class Devlopnet_Controller_Action_Helper_History extends Zend_Controller_Action_Helper_Abstract
{
    protected $_session;
    
    public function __construct()
    {
        $this->_session = new Zend_Session_Namespace(get_class($this));
        if (!isset($this->_session->backUrl)) $this->_session->backUrl = '';
    }
    
    public function direct()
    {
        return $this;
    }
    
	/**
	* Enregistre l'url de la page précédente.
	*
	*/
    public function saveBack($default = '')
    {
        if (!empty($_SERVER['HTTP_REFERER']) &&
                strpos($_SERVER['HTTP_REFERER'], $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost()) === 0) {
           $this->_session->backUrl = $_SERVER['HTTP_REFERER'];
        } else {
            //$this->_session->backUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$this->_session->backUrl = $default;
		}
        return $this;
    }
	
	/**
	* Enregistre l'url de la page courante en tant que précédente.
	*
	*/
	public function saveCurrentAsBack($keepQueryString = true)
	{
		$this->_session->backUrl = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
		$this->_session->backUrl .= $keepQueryString ? $_SERVER['REQUEST_URI'] : $_SERVER['DOCUMENT_URI'];
		return $this;
	}
    
    public function getBackUrl()
    {
        return $this->_session->backUrl;
    }
    
	/**
	*
	* Redirige le visiteur à la page enregistrée comme page de référence
	* 
	* @param $time : Indique le nombre de secondes avant redirection. Si 0, la redirection est HTTP
	* Sinon, la redirection est en javascript et avec un meta http equiv refresh
	* @param $clear : Indique si l'historique doit être supprimé
	* @param $autoBackIfEmpty : Indique si, malgré l'absence de marquage, 
	*/
    public function redirectToBack($time = 0, $clear = true)
    {
        if (!is_int($time)) throw new InvalidArgumentException ('Time must be Integer');
        $url = $this->getBackUrl() ? $this->getBackUrl() : $_SERVER['HTTP_REFERER'];
		if ($clear === true) $this->clearBack ();
		if ($time > 0)
		{
			$view = $this->getActionController()->view;
			$view->headMeta()->appendHttpEquiv('refresh', $time . '; url=' . $url);
			$view->headScript()->appendScript(sprintf(
				'setTimeout(function() { window.location.replace(%s); }, %s);',
				json_encode($url),
				$time*1000
			));
		}
		else
		{
			$this->getActionController()->getHelper('redirector')->gotoUrlAndExit($url);
		}       
        return $this;
    }
    
    public function clearBack()
    {
        $this->_session->backUrl = '';
        return $this;
    }
    

    
}