<?php

/**
 * @author Devlopnet
 * @license Free Open-source
 * @version 1.1
 * 
 * Tests Unitaires : Aucun 
 * 
 */

class Devlopnet_Db_Table_Rowset extends Zend_Db_Table_Rowset
{
    
    const TOARRAY_FROM_NONE = 'none';
    const TOARRAY_FROM_ALL = 'all';
    
    /**
     * Cette méthode étend Zend_Db_Table_Rowset::toArray() pour permettre
     * de retourner des tableaux partiels, associatifs ou non, pour divers
     * usages.
     * 
     * @example
     * 
     * Nous avons une table "services" contenant tous les services possibles
     * Nous souhaitons rapidement créer un objet de formulaire de type SELECT
     * en proposant la sélection de l'un de ces services. Nous devons donc
     * mettre la valeur "multioptions" à array(id => nom du service, ...)
     * 
     * Pour se faire, nous effectuons un $table->fetchAll() pour récupérer tous
     * nos services de notre table, et nous pouvons rapidement le convertir :
     * 
     * $table->fetchAll()->toArray('id', array('nom_service'), 'none', false)
     * 
     * La méthode est suffisamment riche pour pouvoir être utilisée à diverses
     * occasions, voire être appelée depuis des méthodes spécifiques :
     * 
     * public function toMultiOption($idCol, $valCol)
     * {
     *      return $rowset->toArray($idCol, array($valCol), 'none', false);
     * }
     * 
     * soit :
     * 
     * $table->fetchAll()->toMultiOption('id', 'nom_service)
     * 
     * 
     * @param $columnKey Null|String : Précise la colonne qui servira de clé pour chaque élément du tableau représentant une ligne du jeu de résultat. Utilisez Null pour un tableau indexé (aucune colonne)
     * @param $columnsData Array : Précise le ou les colonnes de données, soit à ajouter, soit à enlever (voir l'argument suivant).
     * @param $columnsDataType mixed Vous trouverez les valeurs possibles dans les constantes de classe TOARRAY_FROM_* . Cette valeur indique si les colonnes précisées dans l'argument précédent doivent être considérées comme celles devant être exportées ou comme celles ne le devant pas (par rapport aux colonnes du jeu de résultat).
     * @param $forceArray boolean Force le type des valeurs du tableau retourné. Si forceArray est à false, le nombre d'éléments présents dans l'argument $columsData (0, 1, n) donnera les types (null, string, array)
     * @return Array
     * @throws InvalidArgumentException : Un argument d'entrée n'est pas valide
     * @throws DomainException : $columnsDataType n'est pas l'une des constantes de classe TOARRAY_FROM_*
     * @throws OutOfRangeException : Un nom de colonne donné n'existe pas
     * @throws Zend_*_Exception : Autres exceptions
     * @see Zend_Db_Table_Rowset::toArray()
     * 
     * 
     */
    
    public function toArray($columnKey = null, Array $columnsData = array(), $columnsDataType = self::TOARRAY_FROM_ALL, $forceArray = true)
    {
        /**
         * Prepare Output Var
         */
        $toOut = array();
        if ($this->count() == 0) return $toOut;
        
        /**
         * Input Var Type Hinting and Prepare Input Var
         * $columnKey and $columsData will be tested with Zend
         */
        if ($columnsDataType != self::TOARRAY_FROM_ALL && $columnsDataType != self::TOARRAY_FROM_NONE)
        {
            throw new DomainException('ColumnsDataType invalid Value');
        }
        if (!is_bool($forceArray))
        {
            throw new InvalidArgumentException('forceArray must be Boolean');
        }
        
        /**
         * Prepare Work
         */
        $firstRow = $this->current();        
        $firstRowKeys = array_keys($firstRow->toArray());
        if ($columnsDataType == self::TOARRAY_FROM_ALL)
        {
            // retourne toutes les colonnes du 1er résultat sauf celles citées dans $columnsData
            $columnsData = array_diff($firstRowKeys, $columnsData);	
        }
        if ($columnKey !== null && $firstRow->offsetExists($columnKey) == false)
        {
            // Si un nom de colonne Clé a été précisé, on vérifie qu'il existe dans le jeu de résultat
            throw new OutOfRangeException ('ColumnKey doesn\'t exist in Rows');
        }
        foreach ($columnsData as $columnData) {
            // On fait de même pour toutes les colonnes
            if ($firstRow->offsetExists($columnData) == false)
            {
                throw new OutOfRangeException ("ColumnData $columnData on ColumnsData doesn't exist");
            }
        }

        /**
         * GO WORK !!!!!!!!!!!!!!!!!!!!!!!!!
         */
        foreach ($this as $row)
        {
            switch (count($columnsData))
            {
                case 0:
                    $value = $forceArray == true ? array() : null;
                    break;
                case 1:
                    $value = $row->offsetGet($columnsData[0]);
                    if ($forceArray == true)
                    {
                        $value = array($columnsData[0] => $value);
                    }
                    break;
                default:
                    $value = array();
                    foreach ($columnsData as $columnName)
                    {
                        $value[$columnName] = $row->offsetGet($columnName);
                    }
            }

            if ($columnKey === null)
            {
                $toOut[] = $value;
            }
            else
            {
                $toOut[$row->offsetGet($columnKey)] = $value;
            }
        }
        
        return $toOut;
    }
    
    
    
    
}