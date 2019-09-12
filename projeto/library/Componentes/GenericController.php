<?php

/**
 * GenericController
 * Controle padr�o responsável pela autenticação e controle de acesso
 * @author Tarcísio XTI <tarcisio.angelo@gmail.com>
 * @since 01/09/2013
 * @version 1.0
 * @package application
 * @subpackage application.default.controller
 * @copyright (C) 2011 - Todos os direitos reservados.
 * @link http://www.xti.com.br
 */
class GenericController extends Zend_Controller_Action {

    /**
     * Variável com a mensagem
     * @var $_msg
     */
    protected $_msg;
 
    /**
     * Variável com a página de redirecionamento
     * @var $_url
     */
    protected $_url;

    /**
     * Variável com o tipo de mensagem
     * Valores: ALERT, CONFIRM, ERROR ou vazio
     * @var $_type
     */
    protected $_type;
    
    public $_sessao;

    /**
     * Reescreve o mátodo init() para aceitar 
     * as mensagens e redirecionamentos. 
     * Teremos que chama-lo dentro do 
     * método init() da classe filha assim: parent::init();
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        
        $this->_msg     = $this->_helper->getHelper('FlashMessenger');
        $this->_url     = $this->_helper->getHelper('Redirector');
        $this->_type    = $this->_helper->getHelper('FlashMessengerType');
        header ('Content-type: text/html; charset=ISO-8859-1'); 
        
    } // fecha init()

    public function autenticar($perfilAcesso = array()) {
        
        $session = new Zend_Session_Namespace('user');
        $autenticado = false;

        if(isset($session->usuario) && !empty($session->usuario)) {
            $this->view->assign('usuarioLogado', $session->usuario);
            $this->_sessao = $session->usuario;
            $autenticado = true;
            
            if (isset($perfilAcesso) && !empty($perfilAcesso)) {
                $autenticado = false;
            
                if(in_array($session->usuario["PerfilGeral"], $perfilAcesso)){
                    $autenticado = true;
                }
            }
        }
        
        if(!$autenticado){
            $this->message('Acesso n�o permitido!','/','error');
        }
        
    }
    
    /**
     * Método para chamar as mensagens e fazer o redirecionamento
     * @access protected
     * @param string $msg
     * @param string $url
     * @param string $type
     * @return void
     */
    protected function message($msg, $url, $type = null) {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->flashMessenger->addMessage($msg);
        $this->_helper->flashMessengerType->addMessage($type);
        $this->_redirect($url);
        exit();
    } // fecha message()

    /**
     * Reescreve o método postDispatch() que � respons�vel 
     * por executar uma ação após a execução de um método
     * @access public
     * @param void
     * @return void
     */
    public function postDispatch() {
        if ($this->_msg->hasMessages()) {
            $this->view->message = implode("<br />", $this->_msg->getMessages());
        }

        if ($this->_type->hasMessages()) {
            $this->view->message_type = implode("<br />", $this->_type->getMessages());
        }

        parent::postDispatch(); // chama o método pai
		
    } // fecha postDispatch()

    /**
     * Reescreve o método preDispatch()
     * Esse método é executado antes da controller ser carregada
     * @access public
     * @param void
     */
    public function preDispatch() {
        
    } // fecha método preDispatch()

    
}// fecha class