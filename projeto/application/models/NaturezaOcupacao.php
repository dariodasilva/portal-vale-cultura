<?php

class Application_Model_Usuario {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_NaturezaOcupacao();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null) {

        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;
//        xd($select->assemble());
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
    
    public function criaId() {
        //SELECT * FROM sys.sequences WHERE name = 'TestSequence' ;
        $sql = "SELECT NEXT VALUE FOR SEGURANCA.SQ_USUARIO as IDUSUARIO";

        $statement = $this->getTable()->getAdapter()->query($sql);
        return $statement->fetch();

    }

}

?>