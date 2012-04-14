<?php

/**
 * 
 * Le ParamsFactory permet de traiter rapidement des données d'entrée ($_GET, $_POST),
 * des données utilisateurs (tout tableau) et de les sortir rapidement sous forme
 * de paramètres URL, de input hidden, etc
 * 
 * @example
 * 
 * $factory = new Devlopnet_ParamsFactory();
 * $form = new Zend_Form;
 * $form->addElements($factory->setMergePriority (Devlopnet_ParamsFactory::MERGE_PRIORITY_OLD)
                              ->mergeQuery()
                              ->mergeUrlParam()
                              ->assembleHiddenZendElements());
 * 
 * Création Novembre 2011
 * Refactoring Avril 2012 : Ajout de la récursivité, des priorités, refactoring global
 * 
 * @author Devlopnet
 * @licence Free
 * 
 */

class Devlopnet_ParamsFactory
{
    
    const MERGE_MODE_NORMAL = 'normal';
    const MERGE_MODE_RECURSIVE = 'recursive';
    const MERGE_PRIORITY_NEW = 'new';
    const MERGE_PRIORITY_OLD = 'old';
    
    protected $_mergeMode = self::MERGE_MODE_NORMAL;
    protected $_mergePriority = self::MERGE_PRIORITY_NEW;
    protected $_data = array();
    /**
     * @var Zend_Controller_Request_Http
     */
    protected $_request;
    /**
     *
     * @var Zend_View_Abstract 
     */
    protected $_view;
    
    /**
     * Construit un nouvel object ParamsFactory
     * 
     * @param Zend_Controller_Request_Http $request
     * @param Zend_View_Abstract $view 
     */
    public function __construct(Zend_Controller_Request_Http $request = null,
                                Zend_View_Abstract $view = null)
    {
        if ($request == null) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
        }
        if ($view == null) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $view = $viewRenderer->view;
        }
        $this->_request = $request;
        $this->_view = $view;
    }
    
    /**
     * Enregistre le mode par défaut de merge
     *
     * @param string $mode
     * @return Devlopnet_ParamsFactory 
     */
    public function setMergeMode ($mode)
    {
        $this->_thrownerMode($mode);
        $this->_mergeMode = $mode;
        return $this;
    }
    
    /**
     * Enregistre la priorité par défaut de merge
     * 
     * @param string $priority 
     * @return Devlopnet_ParamsFactory 
     */
    public function setMergePriority ($priority)
    {
        $this->_thrownerPriority($priority);
        $this->_mergePriority = $priority;
        return $this;
    }
    
    protected function _thrownerMode($mode)
    {
        if (!in_array($mode, array(self::MERGE_MODE_NORMAL, self::MERGE_MODE_RECURSIVE))) {
            throw new DomainException('Mode unknows');
        }
    }
    
    protected function _thrownerPriority($priority)
    {
        if (!in_array($priority, array(self::MERGE_PRIORITY_NEW, self::MERGE_PRIORITY_OLD))) {
            throw new DomainException('Mode unknows');
        }
    }
    
    protected function _thrownerParams($array)
    {
        foreach ($array as $key => $value)
        {
            switch (true)
            {
                case is_array($value):
                    $this->_thrownerMerge($value);
                    break;
                case is_scalar($value):
                    break;
                default:
                    throw new DomainException('Invalid value for param');
            }
        }
    }
    
    /**
     * Fonction pillié du composant, il permet de faire un array_merge entre
     * les données en mémoire et les données en entrée. Selon la configuration,
     * les données en mémoire seront prioritaires ou non, etc. Tout tableau peut
     * être mergé.
     *
     * @param array $array
     * @param string $mode
     * @param string $priority
     * @return Devlopnet_ParamsFactory 
     */
    public function mergeArray(Array $array, $mode = null, $priority = null)
    {
        if ($mode !== null) {
            $this->_thrownerMode($mode);
        } else {
            $mode = $this->_mergeMode;
        }
        if ($priority !== null) {
            $this->_thrownerPriority($priority);
        } else {
            $priority = $this->_mergePriority;
        }
        
        $this->_thrownerParams($array);
        $function = $mode == self::MERGE_MODE_NORMAL ? 'array_merge' : 'array_merge_recursive';
        if ($priority == self::MERGE_PRIORITY_NEW) {
            $this->_data = $function($this->_data, $array);
        } else {
            $this->_data = $function($array, $this->_data);
        }
        return $this;
    }
    
    /**
     * 
     * Fusionne les données avec $_GET ($request->getQuery())
     * 
     * @param $mode string Le mode si différent de celui configuré
     * @param $priority string La priorité si différente de celle configurée
     * @return Devlopnet_ParamsFactory 
     */
    public function mergeQuery($mode = null, $priority = null)
    {
        $array = $this->_request->getQuery();
        return $this->mergeArray($array, $mode, $priority);
    }
    
    /**
     * 
     * Fusionne les données avec $_POST ($request->getPost())
     * 
     * @param $mode string Le mode si différent de celui configuré
     * @param $priority string La priorité si différente de celle configurée
     * @return Devlopnet_ParamsFactory 
     */
    public function mergePost($mode = null, $priority = null)
    {
        $array = $this->_request->getPost();
        return $this->mergeArray($array, $mode, $priority);
    }
    
    /**
     * 
     * Fusionne les données avec les données de l'URL ($request->getUserParams())
     * L'url est sous forme /module/controller/action/param1/value1
     * Vous obtiendrez donc array('module'=>'module', ..., 'param1'=>'value1')
     * 
     * @param $mode string Le mode si différent de celui configuré
     * @param $priority string La priorité si différente de celle configurée
     * @return Devlopnet_ParamsFactory 
     */
    public function mergeUrlParam($mode = null, $priority = null)
    {
        $array = $this->_request->getUserParams();
        return $this->mergeArray($array, $mode, $priority);
    }
    
    /**
     * Efface tous les paramètres
     * 
     * @return Devlopnet_ParamsFactory 
     */
    public function reset()
    {
        $this->_data = array();
        return $this;
    }

    /**
     * Vide les valeurs vides dans un but de purge. Possible récursivité.
     * Une valeur vide est un tableau vide ou une chaîne de caractère vide
     * 
     * @param string|null $mode Le mode à utiliser ou null si le mode par défaut
     * @return Devlopnet_ParamsFactory 
     */
    public function purgeEmpties ($mode = null)
    {
        if ($mode !== null) {
            $this->_thrownerMode($mode);
        } else {
            $mode = $this->_mergeMode;
        }
        $isRecursive = $mode == self::MERGE_MODE_RECURSIVE ? true : false;
        $this->_data = $this->_deleteEmpties($this->_data, $isRecursive);
        return $this;
    }
    
    /**
     *
     * Supprime les valeurs vide, récursivement ou non
     * 
     * @param array $array
     * @param bool $isRecursive
     * @return array 
     */
    protected function _deleteEmpties (Array $array, $isRecursive)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value) && $isRecursive) {
                $value = $this->_deleteEmpties($value, $isRecursive);
            }
            if ($value === '' || (is_array($value) && empty($value))) {
                unset($array[$key]);
            }
        }
        return $array;
    }
    
    /**
     * Supprimer les paramètres qui sont dans le tableau $keys
     * 
     * @param array $keys
     * @return Devlopnet_ParamsFactory 
     */
    public function deleteParams (Array $keys)
    {
        foreach ($keys as $value) {
            unset($this->_data[$value]);
        }
        return $this;
    }
    
    
    /**
     * Supprimer tous les paramètres sauf ceux donnés
     * 
     * @param array $keys
     * @return Devlopnet_ParamsFactory 
     */
    public function keepOnlyParams (Array $keys)
    {
        foreach ($this->_data as $key=>$value) {
            if (!in_array($key, $keys)) {
                unset($this->_data[$key]);
            }
        }
        return $this;
    }

    public function getParams()
    {
        return $this->_data;
    }
    
    /**
     * Assemble les paramètres pour transmettre par GET
     * 
     * @return type string
     */
    public function assembleQuery()
    {
        $query = http_build_query ($this->_data, '', '&');
        return $query;
    }
   
    public function assembleHiddenInputs()
    {
        $query = str_replace(array('%5B', '%5D'), array('[', ']'), $this->assembleQuery());
        $params = explode('&', $query);
        $out = '';
        foreach ($params as $param)
        {
            list($name, $value) = explode('=', $param);
            $out .= '<input type="hidden" name="'
                    . $this->_view->escape($name)
                    . '" value="'
                    . $this->_view->escape($value)
                    . "\" />\n";
        }
        return $out;

    }
    
    /**
     * Assemble les paramètres pour former un tableau d'éléments Zend_Form_Element_Hidden
     * Permettant de passer les paramètres correctement
     * 
     * @return array 
     */
    public function assembleHiddenZendElements()
    {
        $query = str_replace(array('%5B', '%5D'), array('[', ']'), $this->assembleQuery());
        $params = explode('&', $query);
        $out = array();
        foreach ($params as $param)
        {
            list($name, $value) = explode('=', $param);
            preg_match('/^(.*)\\[(.*)\\]$/', $name, $simpleName);
            $belongTo = isset($simpleName[1]) ? $simpleName[1] : '';
            $simpleName = isset($simpleName[2]) ? $simpleName[2] : $name;
            $element = new Zend_Form_Element_Hidden($simpleName, array(
                'value' =>  $value
            ));
            if ($belongTo) $element->setBelongsTo($belongTo);
            $element->setDecorators(array(array('viewHelper')));
            $out[] = $element;
        }
        return $out;

    }

}