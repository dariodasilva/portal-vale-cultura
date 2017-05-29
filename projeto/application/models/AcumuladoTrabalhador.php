<?php

class Application_Model_AcumuladoTrabalhador extends ValeCultura_Db_Table_Abstract {

    private $table = null;
    private $name = 'ACUMULADO_TRABALHADOR';
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoTrabalhador();
        }
        return $this->table;
    }

    public function getTotal($tipo) {
        $select = $this->getTable()->select();
        
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS titulo, DESCRICAO_2 AS tipo, valor'), 'BI_VALE_CULTURA');
        $select->where('TIPO = ?', 1);
        
        return $this->getTable()->fetchAll($select)->toArray();
    }
    
    public function getPorData($tipo = null, $ano = null, $mes = null) {
        $select = $this->getTable()->select();

        $codTipo = ($tipo == 'acumulados') ? 2 : 3;  // acumulados 2, ativos 3        
        $select->where('TIPO = ?', $codTipo);
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
        
        $select->where('TIPO = ?', '4');
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