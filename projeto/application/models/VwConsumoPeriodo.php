<?php

class Application_Model_VwConsumoPeriodo
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
            ->from($this->getTable(), array('CON_ANO as ano', 'CON_MES as mes', 'CON_VALOR as valor'))
            ->order('CON_ANO')
            ->order('CON_MES');
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_VwConsumoPeriodo();
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