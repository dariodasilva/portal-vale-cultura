<?php

class Application_Model_PessoaFisica extends Application_Model_Pessoa
{

    private $table = null;

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_PessoaFisica();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null)
    {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

//        xd($select->assemble());
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
        $where["ID_PESSOA_FISICA = ?"] = $id;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id)
    {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function consultarReceitaFederal($cpfCnpj, $forcar = null)
    {
        if (11 == strlen($cpfCnpj) && !validaCPF($cpfCnpj)) {
            throw new InvalidArgumentException("CPF inválido");
        }
        $montarUrl = "pessoa_fisica/consultar/" . $cpfCnpj;
        return parent::consultarReceitaFederal($montarUrl, $forcar);
    }

}
