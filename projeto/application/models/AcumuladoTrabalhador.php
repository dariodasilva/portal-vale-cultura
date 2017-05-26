<?php

class Application_Model_AcumuladoTrabalhador extends ValeCultura_Db_Table_Abstract {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoTrabalhador();
        }
        return $this->table;
    }
}