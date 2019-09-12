<?php

class Application_Model_PessoaJuridicaCNAE {

    private $table = null;

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_PessoaJuridicaCNAE();
        }
        return $this->table;
    }

    public function listarCnae($where = array(), $order = null, $limit = null) {

        $select = $this->getTable()->select()
                ->from(array('p' => 'CORPORATIVO.S_PESSOA_JURIDICA_CNAE'), 
                        array('p.ID_PESSOA_JURIDICA','p.ST_CNAE'));
        
        $select->setIntegrityCheck(false);
        
        $select->joinInner(array('cnae' => 'CORPORATIVO.S_CNAE'), 'p.ID_CNAE = cnae.ID_CNAE',
                            array('cnae.ID_CNAE',
                                  'cnae.NR_NIVEL_HIERARQUIA',
                                  'cnae.DS_NIVEL_HIERARQUIA',
                                  'cdCNAE' => 'cnae.CD_CNAE',
                                  'dsCNAE' => 'cnae.DS_CNAE',
                                  'cnae.ID_CNAE_HIERARQUIA',
                                  'cnae.DS_NOTA_EXPLICATIVA')
        );

        if ($where) {
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order('cnae.NR_NIVEL_HIERARQUIA');
        $select->order('cnae.DS_CNAE');
        $select->order($order);
        $select->limit($limit);

//        xd($select->assemble());

        return $this->getTable()->fetchAll($select);
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
    
    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }

}

?>