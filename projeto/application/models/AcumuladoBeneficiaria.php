<?php

class Application_Model_AcumuladoBeneficiaria extends ValeCultura_Db_Table_Abstract {

    private $table = null;
    private $name = 'ACUMULADO_BENEFICIARIA';
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoBeneficiaria();
        }
        return $this->table;
    }

    public function getTotal($tipoBusca) {
        // codigo tipo na tabela ACUMULADO_BENEFICIARIA
        $arrTipo = array(
            'inscritas' => 1,
            'autorizadas' => 2,
            'nao-autorizadas' => 3,
            'emitiram-cartao' => 4
        );
        $codTipo = (in_array($tipoBusca, array_keys($arrTipo))) ? $arrTipo[$tipoBusca] : 1;
        
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', $codTipo);        
        $select->from($this->name, new Zend_Db_Expr('valor'), 'BI_VALE_CULTURA');
        
        return $this->getTable()->fetchAll($select)->toArray();
    }
    
    public function getPorData($tipoBusca, $ano = null, $mes = null) {
        // codigo tipo na tabela ACUMULADO_BENEFICIARIA
        $arrTipo = array(
            'inscritas' => 5,
            'autorizadas' => 6,
            'nao-autorizadas' => 7,
            'emitiram-cartao' => 8,
            'ativas' => 9
        );
        $codTipo = (in_array($tipoBusca, array_keys($arrTipo))) ? $arrTipo[$tipoBusca] : 5;
        
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', $codTipo);
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS ano, DESCRICAO_2 AS mes'), 'BI_VALE_CULTURA');
        
        if ($ano) {
            $select->where('DESCRICAO_1 = ?', $ano);
        }
        if ($mes) {
            $select->where('DESCRICAO_2 = ?', $mes);
        }
        
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getPorLocalizacao($tipoBusca, $regiao = null, $uf = null) {
        // codigo tipo na tabela ACUMULADO_BENEFICIARIA
        $arrTipo = array(
            'inscritas' => 10,
            'autorizadas' => 11,
            'nao-autorizadas' => 12,
            'emitiram-cartao' => 13
        );
        $codTipo = (in_array($tipoBusca, array_keys($arrTipo))) ? $arrTipo[$tipoBusca] : 10;
        
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', $codTipo);
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