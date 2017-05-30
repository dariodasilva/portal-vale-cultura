<?php

class Application_Model_AcumuladoBeneficiaria extends ValeCultura_Db_Table_Abstract {
    
    const DEFAULT_TOTAL       = 1;
    const DEFAULT_DATA        = 5;
    const DEFAULT_LOCALIZACAO = 10;
    
    private $table = null;
    private $name = 'ACUMULADO_BENEFICIARIA';   
    private $arTipoBusca = array(
        'total' => array(
            'inscritas'       => 1,
            'autorizadas'     => 2,
            'nao-autorizadas' => 3,
            'emitiram-cartao' => 4
        ),
        'data' => array(
            'inscritas'       => 5,
            'autorizadas'     => 6,
            'nao-autorizadas' => 7,
            'emitiram-cartao' => 8,
            'ativas'          => 9
        ),
        'localizacao' => array(
            'inscritas'       => 10,
            'autorizadas'     => 11,
            'nao-autorizadas' => 12,
            'emitiram-cartao' => 13
        )
    );
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoBeneficiaria();
        }
        return $this->table;
    }

    public function getTotal($tipoBusca) {
        $codTipo = (in_array($tipoBusca, array_keys($this->arTipoBusca['total']))) ? $this->arTipoBusca['total'][$tipoBusca] : self::DEFAULT_TOTAL;
        
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', $codTipo);        
        $select->from($this->name, 'valor', 'BI_VALE_CULTURA');
        
        return $this->getTable()->fetchAll($select)->toArray();
    }
    
    public function getPorData($tipoBusca, $ano = null, $mes = null) {
        $codTipo = (in_array($tipoBusca, array_keys($this->arTipoBusca['data']))) ? $this->arTipoBusca['data'][$tipoBusca] : self::DEFAULT_DATA;
        
        $select = $this->getTable()->select();
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

    public function getPorLocalizacao($tipoBusca, $regiao = null, $uf = null) {
        $codTipo = (in_array($tipoBusca, array_keys($this->arTipoBusca['localizacao']))) ? $this->arTipoBusca['localizacao'][$tipoBusca] : self::DEFAULT_LOCALIZACAO;
        
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