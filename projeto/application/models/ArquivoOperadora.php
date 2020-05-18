<?php

class Application_Model_ArquivoOperadora {

    private $table = null;

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_ArquivoOperadora();
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

    public function buscarArquivos($where = array(), $order = null, $limit = null) {
        $select = $this->getTable()->select()->from(
                     array('aq' => 'VALE_CULTURA.S_ARQUIVO_OPERADORA'),
                     array(
                         'idArquivo'        => 'ID_ARQUIVO'
                        ,'idOperadora'      => 'ID_OPERADORA'
                        ,'dsCaminhoArquivo' => 'DS_CAMINHO_ARQUIVO'
                        ,'dsArquivo'        => 'DS_ARQUIVO'
                        ,'dtUploadArquivo'  => 'DT_UPLOAD_ARQUIVO'
                    )
                )->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        //xd($select->assemble());

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function find($id) {
        return $this->getTable()->find($id)->current();
    }

    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $id) {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where["idArquivo = ?"] = $id;
        }
        return $this->getTable()->update($request, $where);
    }

    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }

}

?>