<?php

class Application_Model_PessoaVinculada {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_PessoaVinculada();
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

    public function buscarDadosResponsavel($where = array(), $order = null, $limit = null) {

        $select = $this->getTable()->select()
                                   ->from(array('pv' => 'CORPORATIVO.S_PESSOA_VINCULADA'), 
                                           array('pv.ID_PESSOA',
                                                 'idPessoaVinculada' => 'pv.ID_PESSOA_VINCULADA',
                                                 'CONVERT(VARCHAR(10),pv.DT_INICIO ,103) as dtInicio',
                                                 'CONVERT(VARCHAR(10),pv.DT_FIM  ,103) as dtFim',
                                                 'pv.ST_PESSOA_VINCULADA'));

        $select->setIntegrityCheck(false);
        $select->joinInner(array('tpv' => 'CORPORATIVO.S_TIPO_VINCULO_PESSOA'), 'pv.ID_TIPO_VINCULO_PESSOA = tpv.ID_TIPO_VINCULO_PESSOA',
                            array('tpv.ID_TIPO_VINCULO_PESSOA','tpv.DS_TIPO_VINCULO_PESSOA')
        );
        
        $select->joinInner(array('p' => 'CORPORATIVO.S_PESSOA'), 'pv.ID_PESSOA_VINCULADA = p.ID_PESSOA',
                            array('CONVERT(VARCHAR(10),p.DT_REGISTRO ,103) as dtregistro')
        );
         
        $select->joinInner(array('u' => 'seguranca.S_Usuario'), 'p.id_Pessoa = u.id_Pessoa_Fisica',
                            array('u.id_Usuario')
        );
        
        $select->joinInner(array('up' => 'seguranca.S_Usuario_Perfil'), 'u.id_Usuario = up.id_Usuario',
                            array('up.id_Perfil')
        );
        
        $select->joinInner(array('pf' => 'CORPORATIVO.S_PESSOA_FISICA'), 'p.ID_PESSOA = pf.ID_PESSOA_FISICA',
                            array('nmPessoaFisica' => 'pf.NM_PESSOA_FISICA',
                                  'nrCpf' => 'pf.NR_CPF')
        );
        
        $select->joinLeft(array('pfcbo' => 'CORPORATIVO.S_PESSOA_FISICA_JURIDICA_CBO'), 'pv.ID_PESSOA_VINCULADA = pfcbo.ID_PESSOA_FISICA AND pv.ID_PESSOA = pfcbo.ID_PESSOA_JURIDICA',
                            array('cdCbo' => 'pfcbo.CD_CBO')
        );
        
        $select->joinLeft(array('cbo' => 'CORPORATIVO.S_CBO'), 'pfcbo.CD_CBO = cbo.CD_CBO',
                            array('nmCbo' => 'cbo.NM_CBO')
        );

        if ($where) {
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order($order);
        $select->limit($limit);

//        xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }

    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $idPessoa, $idPessoaVinculada) {
        $where["ID_PESSOA = ?"] = $idPessoa;
        $where["ID_PESSOA_VINCULADA = ?"] = $idPessoaVinculada;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }

}

?>