<?php

class Application_Model_AcumuladoConsumo extends ValeCultura_Db_Table_Abstract
{

    const COD_TOTAL = 1;
    const COD_DATA = 2;
    const COD_LOCALIZACAO = 3;

    private $table = null;
    private $name = 'ACUMULADO_CONSUMO';

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoConsumo();
        }
        return $this->table;
    }

    public function getTotal()
    {
        $select = $this->getTable()->select();
        $select->from($this->name, 'valor', 'BI_VALE_CULTURA');
        $select->where('TIPO = ?', self::COD_TOTAL);

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getPorData($ano = null, $mes = null)
    {
        $select = $this->getTable()->select();
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS ano, DESCRICAO_2 AS mes, valor'), 'BI_VALE_CULTURA');
        $select->where('TIPO = ?', self::COD_DATA);
        $select->order(array('DESCRICAO_1 ASC', 'DESCRICAO_2 ASC'));

        if ($ano) {
            $select->where('DESCRICAO_1 = ?', $ano);
        }
        if ($mes) {
            $select->where('DESCRICAO_2 = ?', $mes);
        }

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getPorLocalizacao($regiao = null, $uf = null)
    {
        $select = $this->getTable()->select();
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS regiao, DESCRICAO_2 AS uf, valor'), 'BI_VALE_CULTURA');
        $select->where('TIPO = ?', self::COD_LOCALIZACAO);

        if ($regiao) {
            $select->where('DESCRICAO_1 = ?', $regiao);
        }
        if ($uf) {
            $select->where('DESCRICAO_2 = ?', $uf);
        }

        return $this->getTable()->fetchAll($select)->toArray();
    }
}