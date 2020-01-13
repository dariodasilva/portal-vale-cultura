<?php

include_once 'GenericController.php';

class Minc_ContatoController extends GenericController {

    public function init() {
        // Layout Padrão
        $this->view->layout()->setLayout('layout');
        // Título
        $this->view->assign('titulo', 'Minc');
        
        parent::init();
    }

    public function indexAction() {
        $this->getHelper('layout')->disableLayout();
    }

    public function emailContatoAction() {
        $this->getHelper('layout')->disableLayout();
        $retorno = array();
        if ($_POST) {
            try {
                $modelEmail = new Application_Model_Email();
                $nome       = $this->getRequest()->getParam('nome');
                $email      = $this->getRequest()->getParam('email');
                $assunto    = $this->getRequest()->getParam('assunto');
                $mensagem   = $this->getRequest()->getParam('mensagem');

                $htmlEmail = emailContatoHTML();
                $htmlEmail = str_replace('#NOME#', $nome, $htmlEmail);
                $htmlEmail = str_replace('#EMAIL#', $email, $htmlEmail);
                $htmlEmail = str_replace('#ASSUNTO#', $assunto, $htmlEmail);
                $htmlEmail = str_replace('#MENSAGEM#', $mensagem, $htmlEmail);
                
                if ($modelEmail->enviarEmail('valecultura@cultura.gov.br', 'Contato via sistema - Vale Cultura', $htmlEmail)) {
                    $retorno['mensagem'] = '<b>E-mail enviado com sucesso!</b>';
                } else {
                    $retorno['mensagem'] = '<b style="color: #f00">Erro no envio do E-mail</b>';
                }
                
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
                $retorno['mensagem'] = '<b style="color: #f00">Erro no envio do E-mail</b>';
            }
        } else {
            $retorno['mensagem'] = '<b style="color: #f00">Erro no envio do E-mail</b>';
        }

        echo json_encode($retorno);
    }    
}

