<?php

class Application_Model_Acesso {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Acesso();
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

    public function buscarAgente($where = array(), $order = null, $limit = null) {

        $select = $this->getTable()->select()
                ->from(array('a' => 'SEGURANCA.ACESSO'), array('a.id',
            'a.login',
            'a.senha'));

        $select->setIntegrityCheck(false);

        if ($where) {
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order($order);
        $select->limit($limit);

        //      xd($select->assemble());

        return $this->getTable()->fetchAll($select);
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