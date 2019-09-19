<?php

class Application_Model_VwTrabalhadorCargaUltima
{

    private $table = null;

    public function getAllAtivos()
    {
        $select = $this->getTable()->select()
            ->setIntegrityCheck(false)
            ->distinct()
            ->from($this->getTable(), array('TRA_LOC_REGIAO as regiao', 'TRA_LOC_UF as uf', 'TRA_QUANTIDADE as total'))
            ->order('TRA_LOC_REGIAO')
            ->order('TRA_LOC_UF');
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getTotalAtivos()
    {
        $select = $this->getTable()->select();
        $intTotal = $select->from($this->getTable(), 'count(*) as total')
            ->where('DT_CARREGAMENTO >= DATEADD(MONTH, -4, GETDATE())')
            ->query()->fetchColumn();
        return $intTotal;
    }


    public function getTotalInativos()
    {
        $select = $this->getTable()->select();
        $intTotal = $select->from($this->getTable(), 'count(*) as total')
            ->where('DT_CARREGAMENTO < DATEADD(MONTH, -4, GETDATE())')
            ->query()->fetchColumn();
        return $intTotal;
    }

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_VwTrabalhadorCargaUltima();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null)
    {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function find($id)
    {
        return $this->getTable()->find($id)->current();
    }
}