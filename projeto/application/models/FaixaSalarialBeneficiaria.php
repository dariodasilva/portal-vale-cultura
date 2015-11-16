<?php

class Application_Model_FaixaSalarialBeneficiaria {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_FaixaSalarialBeneficiaria();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null) {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

//        xd($select->assemble());
        return $this->getTable()->fetchAll($select);
    }
    

    public function find($id) {
        return $this->getTable()->find($id)->current();
    }

    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $idBeneficiaria, $idTipoFaixaSalarial) {
        $where["ID_BENEFICIARIA = ?"] = $idBeneficiaria;
        $where["ID_TIPO_FAIXA_SALARIAL = ?"] = $idTipoFaixaSalarial;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }
    
    
    public function listaFaixas($where = array(), $order = null, $limit = null) {

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('fs' => 'VALE_CULTURA.S_FAIXA_SALARIAL_BENEFICIARIA'), 
                        array('idTipoFaixaSalarial'           => 'fs.ID_TIPO_FAIXA_SALARIAL',
                              'idBeneficiaria'                => 'fs.ID_BENEFICIARIA',
                              'qtTrabalhadorFaixaSalarial'    => 'fs.QT_TRABALHADOR_FAIXA_SALARIAL')
        );

        $select->joinInner(array('tp' => 'VALE_CULTURA.S_TIPO_FAIXA_SALARIAL'), 'tp.ID_TIPO_FAIXA_SALARIAL = fs.ID_TIPO_FAIXA_SALARIAL', 
                            array('dsTipoFaixaSalarial' => 'tp.DS_TIPO_FAIXA_SALARIAL')
        );

        $select->order($order);
        $select->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;
        
//        xd($select->assemble());
        return $this->getTable()->fetchAll($select);
    }
    
}

?>