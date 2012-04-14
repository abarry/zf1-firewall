<?php

/**
 * @author Devlopnet
 * @licence Free
 * 
 * ShortenText permet de récupérer une partie d'un texte, un extrait.
 * Requis : Vous devez préciser mb_internal_encoding
 * 
 * @example
 * 
 * $this->shortText()->setDefaultConfig(array(
 *      'deleteHtml'    =>  true
 * ));
 * 
 * Va définir par défaut que tous les textes seront purifiés de leurs balises html
 * 
 * $this->shortenText($text, array(
 *      'minLength'     =>  100,
 *      'maxLength'     =>  150
 * ));
 * 
 * Va retourner un extrait de $text avec la configuration par défaut, un texte
 * de 100 à 150 caractères.
 * 
 *  
 */

class Devlopnet_View_Helper_ShortText extends Zend_View_Helper_Abstract {
    
    /**
     * @var Array
     */
    protected $_defaultConfig = array(
        'minLength' =>  250,
        'maxLength' =>  300,
        'deleteHtml'=>  false,
        'delimiters' => array(
            array('.' =>  '. (...)', '!' =>  '! ...', '?' =>  '? ...'),
            array(' ' =>  '...')
         )
    );
    
    /**
     * Définit la configuration par défaut.
     * 
     * @param array $config
     * @return Zend_View_Helper_ShortText 
     */
    public function setDefaultConfig(Array $config)
    {
        $this->_defaultConfig = $this->_getMergeConfig($config);
        return $this;
    }
    
    /**
     * S'occupe de la fusion de la config principale et d'une nouvelle
     * Et vérifie la cohérence
     *
     * @param array $config
     * @return Array 
     * @todo Check values
     */
    protected function _getMergeConfig(Array $config)
    {
        $newConfig = $config + $this->_defaultConfig;
        return $newConfig;
    }
    
    /**
     * Proxy Function
     */
    public function shortText()
    {
        if (func_num_args() == 0) {
            return $this;
        } else {
            return call_user_func_array(array($this, 'shortTextProcess'), func_get_args());
        }
    }
    
    /**
     * 
     * Permet de retourner une chaîne plus courte en fonction de critères :
     * 
     * 1) minLength : La longueur minimale (si le texte donné est plus long que la longueur minimale)
     * 2) maxLength : La longueur maximale
     * 3) delimiters : un tableau de tableaux qui seront executés dans l'ordre et dans lequel vous mettez
     * une liste de délimiteurs (clé) à chercher à concurrence égale (c-a-d le premier trouvé en partant de la fin de
     * la chaîne de caractère sera choisi) avec comme valeur la chaine à mettre à la place après tronquage.
     * 4) deleteHtml : Si non, la chaîne sera traitée comme telle. Si oui, la chaîne sera débarassée
     * des balises HTML afin de ne pas garder la mise en forme (couleurs, sauts de lignes), d'en tirer
     * que le texte, et de ne pas couper la chaîne en créant un texte HTML invalide.
     * 
     * Ex : 
     * 
     * 'delimiters' => array(
     *       array('.' =>  '. (...)', '!' =>  '! ...', '?' =>  '? ...'),
     *       array(' ' =>  '...')
     *    )
     * 
     * Va chercher dans un premier temps le dernier point, exclamation ou interrogation présent entre la borne min et max,
     * puis va chercher la chaine d'espace si aucun point, exclamation ou interrogation n'a été trouvé. S'il trouve un point,
     * il va couper au niveau du point et remplacer par la chaîne mise en valeur, c-a-d '. (...)'. A noter que la chaîne
     * remplaçante n'est pas comptabilisée dans la longueur maximale et qu'une fois rajoutée, la chaine de retour peut dépasser
     * la borne max.
     * 
     * 
     * @param string $text
     * @param array $config
     * @return string 
     */
        
    public function shortTextProcess($text, Array $config = array())
    {
        $config = $this->_getMergeConfig($config);
        $delimitersPools = $config['delimiters'];
        if ($config['deleteHtml'] == true)
        {
            $text = strip_tags($text);
        }

        if (mb_strlen($text) > $config['maxLength'])
        {
            $output = mb_substr($text, 0, $config['minLength']);
            $excess = mb_substr($text, $config['minLength'], $config['maxLength'] - $config['minLength']);
            foreach ($delimitersPools as $delimiters)
            {
                $delimitersString = implode('|', array_map('preg_quote' ,array_keys($delimiters)));
                $regex = '/^(.*)(' . $delimitersString . ')/u';
                preg_match($regex, $excess, $match);
                if (!empty($match))
                {
                    // 1 = la chaine à récup, 2 = le signe trouvé    
                    $output .= $match[1];
                    $delimiterFound = $match[2];
                    $output .= $delimiters[$delimiterFound];
                    break;
                }
                
            }
            $text = isset($delimiterFound) ? $output : mb_substr($output, 0, $config['maxLength']);
        }
        return $text;
    }
    
}