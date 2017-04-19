<?php

class Application_Model_VwTrabalhadorAdesao {

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
            ->from($this->getTable(), array('TRA_ADESAO_ANO as ano', 'TRA_QUANTIDADE as total'))
            ->order('TRA_ADESAO_ANO');
        return $this->getTable()->fetchAll($select)->toArray();
    }
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_VwTrabalhadorAdesao();
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

    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $id) {
        $where["id = ?"] = $id;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }

}

?>