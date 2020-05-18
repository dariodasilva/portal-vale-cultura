<?php

class Application_Model_Logradouro {

    private $table = null;

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Logradouro();
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

    public function selectEndereco($where = array(), $order = null, $limit = null) {
       
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('logradouro' => 'CORPORATIVO.S_LOGRADOURO'), 
                    array('NR_CEP',
                          'ID_LOGRADOURO',
                          'ST_LOGRADOURO', 
                          'DS_TIPO_LOGRADOURO',
                          'NM_LOGRADOURO',
                          'DS_COMPLEMENTO',
                          'ID_BAIRRO_INICIO',
                          'ID_BAIRRO_FIM',
                          'TP_CEP',
                          'SG_UF',
                          'ID_MUNICIPIO')
                
        );
        
        $select->joinLeft(array('uf' => 'CORPORATIVO.S_UF'), 'logradouro.SG_UF = uf.SG_UF', 
                            array('NM_UF')
        );
        
        $select->joinLeft(array('municipio' => 'CORPORATIVO.S_MUNICIPIO'), 'logradouro.ID_MUNICIPIO = municipio.ID_MUNICIPIO', 
                            array('NM_MUNICIPIO')
        );
        
        $select->order($order)->limit($limit);
        
        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;
        
//        xd($select->assemble());
        return  $this->getTable()->fetchAll($select)->toArray();
    }
    
}

?>