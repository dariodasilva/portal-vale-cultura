<?php

class Application_Model_UsuarioPerfil {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_UsuarioPerfil();
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
    
    public function listarPerfis($where = array(), $order = null, $limit = null) {
        
        $select = $this->getTable()->select()->from(
                        array('up' => 'SEGURANCA.S_USUARIO_PERFIL'),
                                array('up.ST_USUARIO_PERFIL'))
                                    ->setIntegrityCheck(false);

        
        $select->joinInner(array('p' => 'SEGURANCA.S_PERFIL'), 'up.ID_PERFIL = p.ID_PERFIL',
                            array('IDPERFIL' => 'p.ID_PERFIL',
                                  'NMPERFIL' => 'p.NM_PERFIL',
                                  'CONVERT(VARCHAR(10),p.DT_VALIDADE_PERFIL,110) as DTVALIDADEPERFIL')
        );

//        $select->joinInner(array('ps' => 'SEGURANCA.S_PERFIL_SERVICO'), 'p.ID_PERFIL = ps.ID_PERFIL AND ps.ID_SERVICO = 1',
//                            array()
//        );
        
        if($where){
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order($order);
        $select->limit($limit);

//        xd($select->assemble());

        return $this->getTable()->fetchAll($select);
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