<?php

class Application_Model_Telefone {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Telefone();
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

    public function buscarTelefones($where = array(), $order = null, $limit = null) {

        $select = $this->getTable()->select()
                ->from(array('t' => 'CORPORATIVO.S_TELEFONE'), 
                        array('idTelefone'      => 't.ID_TELEFONE',
                              'idTipoTelefone'  => 't.ID_TELEFONE',
                              'nrTelefone'      => 't.NR_TELEFONE',
                              'nrRamal'         => 't.NR_RAMAL',
                              'dsTelefone'      => 't.DS_TELEFONE',
                              'cdDDD'           => 't.CD_DDD'));

        $select->setIntegrityCheck(false);
        
        $select->joinInner(array('tt' => 'CORPORATIVO.S_TIPO_TELEFONE'), 't.ID_TIPO_TELEFONE = tt.ID_TIPO_TELEFONE',
                            array('idTipoTelefone' => 'tt.ID_TIPO_TELEFONE',
                                  'dsTipoTelefone' => 'tt.DS_TIPO_TELEFONE')
        );

        if ($where) {
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order($order);
        $select->limit($limit);

//              xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }
    
    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }
    
    public function update(array $request, $id) {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where["ID_TELEFONE = ?"] = $id;
        }
        return $this->getTable()->update($request, $where);
    }
    
    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function deleteAll($where = array()) {
        if($where){
            return $this->getTable()->delete($where);
        }
    }
    
}

?>