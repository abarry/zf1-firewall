<?php

/**
 * @author Devlopnet
 * @license Devlopnet CC
 * @todo La gestion en interne des messages est tarabiscottée. A voir donc pour refaire si évolution. Respecter la Class Interface.
 */

class Devlopnet_View_Helper_Messages extends Zend_View_Helper_HeadScript
{
    const ALL_TYPE = null;
    /**
     *  Stockage format :
     * 
     *  array(
     *      TYPE => array(
     *          array('message' => string, 'type' => string, 'isHtml' => true/false)
     *      )
     * 
     *  );
     * 
     * @var type Zend_Session_Namespace
     */
    protected $_storage = null; 
     
    /**
     *
     * @return array
     */
    protected function _getMessagesFromStore()
    {
        if ($this->_storage === null)
        {
            $this->_storage = new Zend_Session_Namespace(get_class($this));
            if (!isset($this->_storage->messages)) {
                $this->_storage->messages = array();
            }
        }
        return $this->_storage->messages;
    }
    
    protected function _setMessagesToStore($messages)
    {
        if ($this->_storage === null) {
           $this->_getMessagesFromStore();
        }
        $this->_storage->messages = $messages;
    }
    
    /**
     * Retourne la liste des différents types de messages en attente d'être affichés
     * 
     * @return Array 
     */
    public function getTypes()
    {
        return array_keys($this->_getMessagesFromStore());
    }
    
    /**
     *
     * Récupère les messages
     * 
     * Soit un tableau contenant les messages, soit un tableau contenant les types avec leurs messages
     * Les messages sont soit sous forme de tableau (non échappé), soit sous forme de chaîne de caractères (échappé)
     * 
     * @param string|null $type
     * @param bool $stringEscapedFormat
     * @return Array 
     */
    public function getMessages($type = self::ALL_TYPE, $stringEscapedFormat = true)
    {
        if (!is_scalar($type) && !is_null($type)) {
            throw new InvalidArgumentException('Type value invalid');
        }
        $messages = $this->_getMessagesFromStore();
        if ($type !== self::ALL_TYPE)
        {
            if (isset($messages[$type])) {
                $messages = $messages[$type];
            } else {
                $messages = array();
            }            
        }
        if ($stringEscapedFormat == true) {
            $messages = $this->_autoEscape($messages);
        }
        return $messages;
    }
    
    protected function _autoEscape(Array $input)
    {
        if (is_array($input) && !isset($input['message'])) {
            foreach ($input as &$element) {
                $element = $this->_autoEscape($element);
            }
        } else {
            $input = $input['isHtml'] == true ?
                $input['message'] : $this->view->escape($input['message']);
        }
        return $input;
    }
    
    /**
     * Indique s'il y a des messages en attente
     * 
     * @param string|null $type
     * @return bool 
     */
    public function hasMessages($type = self::ALL_TYPE)
    {
        if (count($this->getMessages($type)) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Efface tous les messages en attente
     * 
     * @return Devlopnet_View_Helper_Messages 
     */
    public function clearMessages()
    {
        $this->_storage->unsetAll();
        return $this;
    }
    
    /**
     * Ajoute récursivement, en parcourant le tableau donné (ou la chaine), les
     * messages trouvés
     *
     * @return Devlopnet_View_Helper_Messages 
     */
    public function addRecursiveMessages()
    {
        $args = func_get_args();
        if (!is_array($args[0]))
        {
            call_user_func_array(array($this, 'addMessage'), $args);
        } else {
            foreach ($args[0] as $message)
            {
                $newArgs = $args;
                $newArgs[0] = $message;
                call_user_func_array(array($this, 'addRecursiveMessages'), $newArgs);
            }
        }
        return $this;
    }
    
    /**
     * Ajoute un ou plusieurs messages.
     *
     * @param string $message
     * @param string $type
     * @param bool $isHTML
     * @param bool $pushBeforeOthers
     * @return Devlopnet_View_Helper_Messages 
     */
    public function addMessage($message, $type, $isHTML = false, $pushBeforeOthers = false)
    {
        if (!is_string($message) || !is_string($type) || !is_bool($isHTML) || !is_bool($pushBeforeOthers)) {
            throw new InvalidArgumentException('Please respect Arguments Type');
        }
        $storage = $this->_getMessagesFromStore();
        $typeStorage = isset($storage[$type]) ? $storage[$type] : array();
        $row = array(
            'message'   => (string)$message,
            'isHtml'    => $isHTML
        );
        if ($pushBeforeOthers == false) {
            array_push($typeStorage, $row);
        } else {
            array_unshift($typeStorage, $row);
        }
        $storage[$type] = $typeStorage;
        $this->_setMessagesToStore($storage, $type);
        return $this;
    }
    
    public function messages()
    {
        if (func_num_args() == 0) {
            return $this;
        } else {
            return call_user_func_array(array($this, 'addRecursiveMessages'), func_get_args());
        }
    }

}