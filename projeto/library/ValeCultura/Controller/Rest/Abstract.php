<?php

/**
 * Classe para controlar API de Servi�os.
 * 
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright � 2016 - Minist�rio da Cultura - Todos os direitos reservados.
 */
abstract class ValeCultura_Controller_Rest_Abstract extends Zend_Rest_Controller{

    /**
     * Chave secreta para criptografar e descriptografar os dados.
     * 
     * @var string
     */
    protected $encryptHash;

    /**
     * Chave p�blica usada por aplicativos para consumir os servi�os.
     * @todo transformar em lista quando houver v�rias aplica��es consumindo dados.
     * 
     * @var string
     */
    protected $publicKey;
    
    /**
     * Chave de acesso utilizada pelo usu�rio logado no sistema.
     * 
     * @var string
     */
    protected $authorization;
    
    /**
     * C�digo �nico do dispositivo conectado fornecido pelo servi�o GCM.
     * 
     * @var string
     */
    protected $registrationId;

    /**
     * Dados do usu�rio que est� consumindo o servi�o.
     * 
     * @var Sgcacesso 
     */
    protected $usuario;
    
    /**
     * M�todos que podem ser acessados sem autentica��o.
     * 
     * @var array
     */
    protected $arrPublicMethod = array();
    
    public function init(){
//        $this->publicKey = Zend_Registry::get('config')->resources->view->service->salicMobileHash;
//        $this->encryptHash = Zend_Registry::get('config')->resources->view->service->encryptHash;
//        $this->registrationId = $this->getRequest()->getHeader('registrationId');
//
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setHeader('Access-Control-Allow-Origin', '*');
        $this->getResponse()->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, DELETE, HEAD, PUT');
        $this->getResponse()->setHeader('Access-Control-Allow-Headers', 'x-requested-with, Content-Type, origin, authorization, accept, client-security-token');

//        $this->authorization = $this->getRequest()->getHeader('Authorization');
//        if(!$this->getRequest()->getHeader('ApplicationKey') || $this->publicKey != $this->getRequest()->getHeader('ApplicationKey')) {
//            $this->_forward('error-forbiden');
//        } else if(!$this->authorization && !in_array($this->getRequest()->getActionName(), $this->getPublicMethod())) {
//            $this->_forward('error-forbiden');
//        }
//        if($this->authorization){
//            $this->carregarUsuario();
//        }
//        $this->salvarUltimoAcesso();

        parent::init();
    }

    /**
     * Responde requisicao de opcoes de metodos suportados ao servico.
     *
     * @return JSON
     */
    public function optionsAction(){
        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setHeader('Access-Control-Allow-Origin', '*');
        $this->getResponse()->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, DELETE, HEAD, PUT');
        $this->getResponse()->setHeader('Access-Control-Allow-Headers', 'x-requested-with, Content-Type, origin, authorization, accept, client-security-token');
    }
}