<?php

class Application_Model_Descredenciamento extends Zend_Db_Table_Abstract
{
    private $table = null;
    // protected $_primary= 'PK_S_DESCREDENCIAMENTO';

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Descredenciamento();
        }
        return $this->table;
    }

    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function getDescredencimantoRealizado($idBeneficiaria) {
        $select = $this->getTable()->select();
        $select->where('ID_BENEFICIARIA = ?', $idBeneficiaria);
        // $select->from($this->name, 'valor', 'VALE_CULTURA');
        /*$codTipo = ($idBeneficiaria == 'acumulados') ? self::COD_TOTAL : self::COD_ATIVO;
        $select->where('TIPO = ?', $codTipo);*/

        // caso especial: ativos atual é o valor registrado para o último mês
        /*if ($idBeneficiaria == 'ativos') {
            $select->order(array('DESCRICAO_1 DESC', 'DESCRICAO_2 DESC'));
            $select->limit(1);
        }*/
        
        return $this->getTable()->fetchAll($select)->toArray();
    }

    /*public function select($where = array(), $order = null, $limit = null) {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        return $this->getTable()->fetchAll($select)->toArray();
    }*/

}