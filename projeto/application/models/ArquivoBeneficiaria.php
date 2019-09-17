<?php

class Application_Model_ArquivoBeneficiaria
{

    private $table = null;

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_ArquivoBeneficiaria();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null)
    {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function buscarArquivos($where = array(), $order = null, $limit = null)
    {
        $select = $this->getTable()->select()->from(
            array('aq' => 'VALE_CULTURA.S_ARQUIVO_BENEFICIARIA'),
            array(
                'idArquivoBeneficiaria' => 'ID_ARQUIVO_BENEFICIARIA'
            , 'idBeneficiaria' => 'ID_BENEFICIARIA'
            , 'dsCaminhoArquivo' => 'DS_CAMINHO_ARQUIVO'
            , 'dsArquivo' => 'DS_ARQUIVO'
            , 'dtUploadArquivo' => 'DT_UPLOAD_ARQUIVO'
            , 'idResponsavel' => 'ID_RESPONSAVEL'
            )
        )->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        //xd($select->assemble());

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function find($id)
    {
        return $this->getTable()->find($id)->current();
    }

    public function insert(array $request)
    {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $id)
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where["idArquivoBeneficiaria = ?"] = $id;
        }
        return $this->getTable()->update($request, $where);
    }

    public function delete($id)
    {
        return $this->getTable()->find($id)->current()->delete();
    }

}