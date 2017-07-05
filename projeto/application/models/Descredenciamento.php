<?php

class Application_Model_Descredenciamento extends Zend_Db_Table_Abstract
{
    private $table = null;

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Descredenciamento();
        }
        return $this->table;
    }

    
}