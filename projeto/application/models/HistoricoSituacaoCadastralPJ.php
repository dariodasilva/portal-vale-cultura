<?php

class Application_Model_HistoricoSituacaoCadastralPJ {

    private $table = null;

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_HistoricoSituacaoCadastralPJ();
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

    public function ultimaSituacaoCadastral($idPessoaJuridica, $dbg = null) {

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a' => 'H_SITUACAO_CADASTRAL_PJ'),
                        array('a.ID_SITUACAO_CADASTAL_PJ',
                              'DT_SITUACAO_CADASTRAL' => new Zend_Db_Expr('CONVERT(VARCHAR(10),DT_SITUACAO_CADASTRAL,103)')),'CORPORATIVO'
        );

        $select->joinInner(array('b' => 'S_TIPO_SITUACAO_CADASTRAL_PJ'),'a.CD_SITUACAO_CADASTRAL = b.CD_SITUACAO_CADASTRAL',
                array('b.DS_SITUACAO_CADASTRAL'),'CORPORATIVO'
        );


        $select->where('a.ID_PESSOA_JURIDICA = ?', $idPessoaJuridica);

        $select->order('a.DT_SITUACAO_CADASTRAL DESC');

        $select->limit(1);

        if($dbg){
            xd($select->assemble());
        }

        return $this->getTable()->fetchAll($select)->toArray();
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