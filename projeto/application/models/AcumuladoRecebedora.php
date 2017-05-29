<?php

class Application_Model_AcumuladoRecebedora extends ValeCultura_Db_Table_Abstract {

    private $table = null;
    private $name = 'ACUMULADO_RECEBEDORA';
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoRecebedora();
        }
        return $this->table;
    }

    public function getTotal() {
        
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', 1);
        $select->from($this->name, new Zend_Db_Expr('valor'), 'BI_VALE_CULTURA');
        
        return $this->getTable()->fetchAll($select)->toArray();
    }
    
    public function getPorData($ano = null, $mes = null) {
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', 2);
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS ano, DESCRICAO_2 AS mes, valor'), 'BI_VALE_CULTURA');
        
        if ($ano) {
            $select->where('DESCRICAO_1 = ?', $ano);
        }
        if ($mes) {
            $select->where('DESCRICAO_2 = ?', $mes);
        }
        
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getPorLocalizacao($regiao = null, $uf = null) {
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', 3);
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS regiao, DESCRICAO_2 AS uf, valor'), 'BI_VALE_CULTURA');
        
        if ($regiao) {

            $select->where('DESCRICAO_1 = ?', $regiao);
        }
        if ($uf) {
            $select->where('DESCRICAO_2 = ?', $uf);
        }        
        
        return $this->getTable()->fetchAll($select)->toArray();
    }    
}