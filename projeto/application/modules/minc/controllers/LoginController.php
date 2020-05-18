<?php

include_once 'GenericController.php';

class Minc_LoginController extends GenericController {

    public function init() {

        // Layout padrão
        $this->view->layout()->setLayout('layout');

        // Título
        $this->view->assign('titulo', 'Minc');

        parent::init();
    }

    public function indexAction() {
        
    }
    
    public function logoutAction() {
        session_destroy();
        $this->_redirect('/');
    }

    public function authAction() {

        $obj = array();
        $this->getHelper('layout')->disableLayout();

        // Buscar na tabela tbUsuario e verificar o perfil na tabela tbUsuarioPerfil
        if ($_POST) {

            $login = str_replace('-', '', str_replace('.', '', $this->_request->getParam('acessologin')));
            $passw = $this->_request->getParam('acessopsw');

            $modelUsuario = new Application_Model_Usuario();
            $modelUsuarioPerfil = new Application_Model_UsuarioPerfil();

            $where['DS_LOGIN = ?'] = $login;
            $where['DS_SENHA = ?'] = md5($passw);
            $result = $modelUsuario->dadosLogin($where);

            if (count($result) > 0) {
                $obj['idUsuario']       = $result[0]->idUsuario;
                $obj['idPessoa']        = $result[0]->idPessoa;
                $obj['dtCadastro']      = $result[0]->dtRegistro;
                $obj['Nome']            = $result[0]->NM_PESSOA_FISICA;
                $obj['PerfilGeral']     = 'C';
                $obj['beneficiaria']    = '';
                $obj['operadora']       = '';

                $perfil = $modelUsuarioPerfil->listarPerfis(array('ID_USUARIO = ?' => $result[0]->idUsuario, 'ST_USUARIO_PERFIL = ?' => 'A'));
                
                if (count($perfil) > 0) {
                    foreach ($perfil as $p) {
                        // Verifica se é responsável
                        if (($p->IDPERFIL == 2) || ($p->IDPERFIL == 3)) {
                            $obj['PerfilGeral'] = 'R';
                        }else if ($p->IDPERFIL == 1) {
                            $obj['PerfilGeral'] = 'A';
                        }
                    }
                }
                
            } else {
                parent::message('Login Inv&aacute;lido', '/', 'error');
            }

            // Adicionar o Usuario na sessao
            $session = new Zend_Session_Namespace('user');
            $session->usuario = $obj;
            
            // Enviar o usuario para o perfil 
            if ($obj['PerfilGeral'] == '4') {
                $this->_redirect('/minc/consulta');
            } else {
                $this->_redirect('/minc/admin');
            }
        }
        
        die();
    }

}

