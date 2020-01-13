<?php

class Application_Model_Pessoa
{

    private $table = null;

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Pessoa();
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
        $where["id = ?"] = $id;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id)
    {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function criaId()
    {
        //SELECT * FROM sys.sequences WHERE name = 'TestSequence' ;
        $sql = "SELECT NEXT VALUE FOR CORPORATIVO.SQ_PESSOA as idPessoa";

        $statement = $this->getTable()->getAdapter()->query($sql);
        return $statement->fetch();
    }

    public function consultarReceitaFederal($montarUrl, $forcar)
    {

        $webservices = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('webservices');
        $webservicePessoa = $webservices['receita-federal']['consultar']['pessoa'];

        $username = $webservicePessoa['username'];
        $password = $webservicePessoa['password'];

        $modelServico = new Application_Model_PropriedadeAplicacao();

        if ($forcar) {
            $montarUrl .= "?forcarBuscaNaReceita=true";
        }

        // Busca url padrão
        $servico = $modelServico->urlServicoPessoa('corporativo.url.servicos');

        $url = $servico->DS_VALOR . $montarUrl;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $resultCurl = curl_exec($curl);
        curl_close($curl);
        $result = new ArrayObject(json_decode($resultCurl, true));

        return $result;
    }

}

?>