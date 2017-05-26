<?php

class Application_Model_AcumuladoTrabalhador extends ValeCultura_Db_Table_Abstract {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoTrabalhador();
        }
        return $this->table;
    }

    public function getPorData($ano = null, $mes = null) {
        $where = array('TIPO = ?' => '2');

        if ($ano) {
            $where['DESCRICAO_1 = ?'] = $ano;
        }
        if ($mes) {
            $where['DESCRICAO_2 = ?'] = $mes;
        }        
        
        return $this->select($where);
    }
}