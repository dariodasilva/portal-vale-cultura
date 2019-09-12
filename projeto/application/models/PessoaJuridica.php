<?php

class Application_Model_PessoaJuridica extends Application_Model_Pessoa
{

    private $table = null;

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_PessoaJuridica();
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

    public function buscarPessoaJuridica($where = array(), $order = null, $limit = null)
    {

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('p' => 'CORPORATIVO.S_PESSOA_JURIDICA'), array('p.ID_PESSOA_JURIDICA',
            'p.ID_TIPO_LUCRO',
            'p.NR_CNPJ',
            'p.NR_INSCRICAO_ESTADUAL',
            'p.NM_RAZAO_SOCIAL',
            'p.NM_FANTASIA',
            'p.NR_CEI'));


        $select->joinLeft(array('nj' => 'CORPORATIVO.S_NATUREZA_JURIDICA'), 'p.CD_NATUREZA_JURIDICA = nj.CD_NATUREZA_JURIDICA', array('nj.CD_NATUREZA_JURIDICA',
            'nj.DS_NATUREZA_JURIDICA')
        );

        if ($where) {
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order('p.NM_RAZAO_SOCIAL');
        $select->order('p.NM_FANTASIA');
        $select->order($order);
        $select->limit($limit);

//        xd($select->assemble());

        return $this->getTable()->fetchAll($select);
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
            $where["ID_PESSOA_JURIDICA = ?"] = $id;
        }
        return $this->getTable()->update($request, $where);
    }

    public function delete($id)
    {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function consultarReceitaFederal($cpfCnpj, $forcar = null)
    {
        if (14 == strlen($cpfCnpj) && !validaCNPJ($cpfCnpj)) {
            throw new InvalidArgumentException("CNPJ inválido");
        }
        $montarUrl = "pessoa_juridica/consultar/" . $cpfCnpj;
        return parent::consultarReceitaFederal($montarUrl, $forcar);
    }

}
