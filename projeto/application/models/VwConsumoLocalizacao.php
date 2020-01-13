<?php

class Application_Model_VwConsumoLocalizacao
{
    private $table = null;

    public function getTotal()
    {
        $select = $this->getTable()->select();
        $intTotal = $select->from($this->getTable(), 'count(*) as total')->query()->fetchColumn();
        return $intTotal;
    }

    public function getAll()
    {
        $select = $this->getTable()->select()
            ->setIntegrityCheck(false)
            ->distinct()
            ->from($this->getTable(), array('CONS_REGIAO as regiao', 'CON_ESTADO as estado', 'CON_VALOR as valor'))
            ->order('CONS_REGIAO')
            ->order('CON_ESTADO')
            ->order('CON_VALOR');
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_VwConsumoLocalizacao();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null) {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function find($id) {
        return $this->getTable()->find($id)->current();
    }
}