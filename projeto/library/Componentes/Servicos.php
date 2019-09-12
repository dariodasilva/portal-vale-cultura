<?php

/**
 * Description of Servicos
 *
 * @author Tarcisio
 */
class Servicos {
    
    
    /**
     * Usuário servico webservice
     * @var $_username
     */
    protected $_username;
 
    /**
     * Senha servico webservice
     * @var $_password
     */
    protected $_password;
    
    
    public function init() {
        
    }
    
    
    /**
     * Consultar CPF ou CNPJ na Receita Federal por WebService
     * @var $cpfCnpj
     * @var $tipoPessoa
     * @var $forcar
     */
    public function consultarPessoaReceitaFederal($cpfCnpj, $tipoPessoa = 'Fisica', $forcar = null){
        $nomeDaclasse = 'Application_Model_Pessoa'.$tipoPessoa;
        $pessoaClass = new $nomeDaclasse();
        return $pessoaClass->consultarReceitaFederal($cpfCnpj, $forcar);
    }
    
}
