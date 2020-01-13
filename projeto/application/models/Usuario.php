<?php

class Application_Model_Usuario {

    private $table = null;
    
    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Usuario();
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
    
    public function dadosLogin($where = array(), $order = null, $limit = null) {

        //xd($this->getTable());
       $select = $this->getTable()->select()->from(
                        array('u' => 'SEGURANCA.S_USUARIO'),
                                array('idUsuario' => 'u.ID_USUARIO',
                                    'u.ST_USUARIO','u.DS_SENHA_TEMP'))
                                    ->setIntegrityCheck(false);

       
       
        $select->joinInner(array('p' => 'CORPORATIVO.S_PESSOA'), 'u.ID_PESSOA_FISICA = p.ID_PESSOA',
                            array('idPessoa' => 'p.ID_PESSOA',
                                  'CONVERT(VARCHAR(10),p.DT_REGISTRO,103) as dtRegistro')
        );

        $select->joinInner(array('pf' => 'CORPORATIVO.S_PESSOA_FISICA'), 'p.ID_PESSOA = pf.ID_PESSOA_FISICA',
                            array('pf.NM_PESSOA_FISICA')
        );


        if($where){
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order($order);
        $select->limit($limit);

       // xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }
    
    public function buscaPerfinsUsuario($where = array(), $order = null, $limit = null) {

        //xd($this->getTable());
       $select = $this->getTable()->select()->from(
                        array('u' => 'SEGURANCA.S_USUARIO'),
                                array('idUsuario' => 'u.ID_USUARIO',
                                    'u.ST_USUARIO','u.DS_SENHA_TEMP'))
                                    ->setIntegrityCheck(false);

       
       
        $select->joinInner(array('p' => 'CORPORATIVO.S_PESSOA'), 'u.ID_PESSOA_FISICA = p.ID_PESSOA',
                            array('idPessoa' => 'p.ID_PESSOA',
                                  'CONVERT(VARCHAR(10),p.DT_REGISTRO,103) as dtRegistro')
        );

        $select->joinInner(array('pf' => 'CORPORATIVO.S_PESSOA_FISICA'), 'p.ID_PESSOA = pf.ID_PESSOA_FISICA',
                            array('pf.NM_PESSOA_FISICA')
        );
        
        $select->joinInner(array('per' => 'SEGURANCA.S_USUARIO_PERFIL'), 'u.ID_USUARIO = per.ID_USUARIO',
                            array('pf.NM_PESSOA_FISICA')
        );


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
        $where["ID_USUARIO = ?"] = $id;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }
    
    public function criaId() {
        //SELECT * FROM sys.sequences WHERE name = 'TestSequence' ;
        $sql = "SELECT NEXT VALUE FOR SEGURANCA.SQ_USUARIO as idUsuario";

        $statement = $this->getTable()->getAdapter()->query($sql);
        return $statement->fetch();

    }

}

?>
