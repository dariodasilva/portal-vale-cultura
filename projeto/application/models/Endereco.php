<?php

class Application_Model_Endereco
{

    private $table = null;

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Endereco();
        }
        return $this->table;
    }


    public function buscarEnderecoCompleto($where = array(), $order = null, $limit = null)
    {

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('en' => 'CORPORATIVO.S_ENDERECO'),
            array('en.ID_ENDERECO',
                'en.ID_PESSOA',
                'en.CD_TIPO_ENDERECO',
                'en.ID_SUBDISTRITO_IBGE',
                'en.ID_BAIRRO',
                'en.DS_COMPLEMENTO_ENDERECO',
                'en.NR_COMPLEMENTO',
                'en.DS_LOGRA_ENDERECO',
                'en.DS_BAIRRO_ENDERECO',
                'en.ID_SERVICO')

        );

        $select->joinLeft(
            array('logradouro' => 'CORPORATIVO.S_LOGRADOURO'), 'logradouro.ID_LOGRADOURO = en.ID_LOGRADOURO',
            array('logradouro.NR_CEP',
                'logradouro.ID_LOGRADOURO',
                'logradouro.ST_LOGRADOURO',
                'logradouro.DS_TIPO_LOGRADOURO',
                'logradouro.NM_LOGRADOURO',
                'logradouro.DS_COMPLEMENTO',
                'logradouro.ID_BAIRRO_INICIO',
                'logradouro.ID_BAIRRO_FIM',
                'logradouro.TP_CEP',
                'logradouro.SG_UF',
                'logradouro.ID_MUNICIPIO')
        );

        $select->joinLeft(
            array('uf' => 'CORPORATIVO.S_UF'), 'logradouro.SG_UF = uf.SG_UF',
            array('uf.NM_UF')
        );

        $select->joinLeft(
            array('municipio' => 'CORPORATIVO.S_MUNICIPIO'), 'logradouro.ID_MUNICIPIO = municipio.ID_MUNICIPIO',
            array('municipio.NM_MUNICIPIO')
        );

        $select->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

//        xd($select->assemble());
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function select($where = array(), $order = null, $limit = null)
    {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

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
            $where["ID_ENDERECO = ?"] = $id;
        }
        return $this->getTable()->update($request, $where);
    }

    public function delete($id)
    {
        return $this->getTable()->find($id)->current()->delete();
    }

}

?>