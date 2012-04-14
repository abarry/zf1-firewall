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
    
    public function getMessages($type = self::ALL_TYPE, $typeGroup = false)
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
        } else {
            if ($typeGroup == false)
            {
                $tmpMsg = array();
                foreach ($messages as $type=>$messagesTyped)
                {
                    foreach ($messagesTyped as $message)
                    {
                        $tmpMsg[] = $message;
                    }
                }
                $messages = $tmpMsg;
            }
        }
        return $messages;
        
    }
    
    public function hasMessages($type = self::ALL_TYPE)
    {
        if (count($this->getMessages($type)) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function clearMessages()
    {
        $this->_storage->unsetAll();
        return $this;
    }
    
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
    
    public function addMessage($message, $type, $isHTML = false, $pushBeforeOthers = false)
    {
        if (!is_scalar($message) || !is_scalar($type) || !is_bool($isHTML) || !is_bool($pushBeforeOthers)) {
            throw new InvalidArgumentException('Please respect Arguments Type');
        }
        $storage = $this->_getMessagesFromStore();
        $typeStorage = isset($storage[$type]) ? $storage[$type] : array();
        $row = array(
            'message'   => $message,
            'type'      => $type,
            'isHtml'    => $isHTML
        );
        if ($pushBeforeOthers == true) {
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