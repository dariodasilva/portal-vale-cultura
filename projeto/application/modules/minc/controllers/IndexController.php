<?php

include_once 'GenericController.php';

class Minc_IndexController extends GenericController {

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

    public function esqueceusenhaAction() {
        $this->getHelper('layout')->disableLayout();
    }

    public function recuperarSenhaAction()
    {
        $this->getHelper('layout')->disableLayout();

        if ($_POST) {

            $modelUsuario = new Application_Model_Usuario;
            $modelEmail = new Application_Model_Email;

            $CPF = $this->getRequest()->getParam('CPF');
            $CPF = str_replace('.', '', $CPF);
            $CPF = str_replace('-', '', $CPF);

            if (!$CPF) {
                parent::message('Informe o CPF', '/', 'error');
            }

            //BUSCA USUARIO
            $recuperaUsuario = $modelUsuario->dadosLogin(array('DS_LOGIN = ?' => $CPF));

            if (isset($recuperaUsuario[0])) {
                $recuperaEmails = $modelEmail->select(array('ID_PESSOA = ?' => $recuperaUsuario[0]->idPessoa, 'ST_EMAIL_PRINCIPAL = ?' => 'S'));
                if (count($recuperaEmails) > 0) {
                    foreach ($recuperaEmails as $email) {
                        $DSEMAIL = $email['DS_EMAIL'];
                    }
                    try {
                        $senhaTemp = gerarCodigo();
                        $url = 'http://vale.cultura.gov.br/minc/index/novasenha/cod/' . $senhaTemp . $CPF;

                        $cols = array('DS_SENHA_TEMP' => md5($senhaTemp));

                        if ($modelUsuario->update($cols, $recuperaUsuario[0]->idUsuario)) {
                            $htmlEmail = emailNovaSenhaHTML();
                            $htmlEmail = str_replace('#URL#', $url, $htmlEmail);
                            $htmlEmail = str_replace('#NOME_USUARIO#', $recuperaUsuario[0]->NM_PESSOA_FISICA, $htmlEmail);
                            $enviarEmail = $modelEmail->enviarEmail($DSEMAIL, 'Acesso ao sistema Vale Cultura', $htmlEmail);
                            parent::message('Um e-mail foi enviado para ' . $DSEMAIL, '/', 'confirm');
                        } else {
                            parent::message('Falha na solicitação', '/', 'error');
                        }
                    } catch (Exception $exc) {
                        parent::message('Falha na solicitação', '/', 'error');
                        echo $exc->getTraceAsString();
                    }
                } else {
                    parent::message('Nenhum e-mail cadastrado para este CPF', '/', 'error');
                }
            } else {
                parent::message('CPF não cadastrado', '/', 'error');
            }
        } else {
            parent::message('Nenhuma informação enviada', '/', 'error');
        }
    }

    public function novasenhaAction() {
        
        $modelUsuario = new Application_Model_Usuario();
        
        $session        = new Zend_Session_Namespace('user');
        $atualizaSenha  = false;
        $cod            = $this->getRequest()->getParam('cod');
        $cpf            = substr($cod, strlen($cod) - 11);
        $validador      = substr($cod, 0, strlen($cod) - 11);

        if (!isset($session->usuario["idUsuario"])) {

            if (strlen($validador) > 5) {
                if (strlen($cpf) == 11) {
                    //BUSCA USUARIO
                    $where = array('DS_LOGIN = ?' => $cpf);
                    $recuperaUsuario = $modelUsuario->dadosLogin($where);
                    if (count($recuperaUsuario) > 0) {
                        if ($recuperaUsuario[0]->DS_SENHA_TEMP == md5($validador)) {
                            $atualizaSenha = true;
                            $obj['idUsuario'] = $recuperaUsuario[0]->idUsuario;
                            $session = new Zend_Session_Namespace('user');
                            $session->usuario = $obj;
                        } else {
                            $atualizaSenha = false;
                        }
                        $cols = array(
                            'DS_SENHA_TEMP' => NULL
                        );
                        $modelUsuario->update($cols, $recuperaUsuario[0]->idUsuario);
                    } else {
                        parent::message('Código inválido ', '/', 'error');
                    }
                } else {
                    parent::message('Código inválido ', '/', 'error');
                }
            } else {
                parent::message('Código inválido', '/', 'error');
            }

            if (!$atualizaSenha) {
                parent::message('Código inválido', '/', 'error');
            }
        } else {
            $cols = array(
                'DS_SENHA_TEMP' => NULL
            );
            $modelUsuario->update($cols, $session->usuario["idUsuario"]);
        }
    }

    public function alterarsenhaactionAction() {
        if ($_POST) {
            
            $modelUsuario   = new Application_Model_Usuario;
            $session        = new Zend_Session_Namespace('user');

            $NOVA_SENHA          = $this->getRequest()->getParam('NOVA_SENHA');
            $NOVA_SENHA_CONFIRMA = $this->getRequest()->getParam('NOVA_SENHA_CONFIMA');

            if (isset($session->usuario["idUsuario"])) {
                $idUser = $session->usuario["idUsuario"];

                if (!$NOVA_SENHA) {
                    parent::message('Informe a nova senha', '/minc/index/novasenha/', 'error');
                }
                if ($NOVA_SENHA != $NOVA_SENHA_CONFIRMA) {
                    parent::message('Senha de confirmação incorreta', '/minc/index/novasenha/', 'error');
                }

                $cols = array('DS_SENHA' => md5($NOVA_SENHA));
                if ($modelUsuario->update($cols, $idUser)) {
                    $session = new Zend_Session_Namespace('user');
                    $session->usuario = array();
                    parent::message('Senha atualizada com sucesso', '/', 'confirm');
                }
            } else {
                $session = new Zend_Session_Namespace('user');
                $session->usuario = array();
                parent::message('Falha na autenticação', '/minc/index/novasenha/', 'error');
            }
        }
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

    public function listaOperadoraAction() {
        
        $this->getHelper('layout')->disableLayout();
        
        $modelSituacao = new Application_Model_Situacao();
        $modelTelefone = new Application_Model_Telefone();
        
        $listaOperadoras  = array();
        $operadorasAtivas = $modelSituacao->selecionaOperadorasAtivas();
        $i = 0;
        foreach($operadorasAtivas as $op){
            
            $listaOperadoras[$i]['idOperadora']  = $op['idOperadora'];
            $listaOperadoras[$i]['nrCNPJ']       = addMascara($op['nrCNPJ']);
            $listaOperadoras[$i]['nmFantasia']   = $op['nmFantasia'];
            $listaOperadoras[$i]['idSituacaoXX'] = $op['idSituacaoXX'];
            $listaOperadoras[$i]['dsSite']       = $op['dsSite'];
            
            $listaTelefones = array();
            $t = 0;
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $op['idOperadora'], 'tt.ID_TIPO_TELEFONE = ?' => 7));
            
            if(count($telefones) > 0){
                foreach($telefones as $tel){
                    $listaTelefones[$t]['idTipoTelefone']   = $tel['idTipoTelefone'];
                    $listaTelefones[$t]['nrTelefone']       = $tel['nrTelefone'];
                    $listaTelefones[$t]['cdDDD']            = $tel['cdDDD'];
                    $listaTelefones[$t]['dsTelefone']       = $tel['dsTelefone'];
                    $listaTelefones[$t]['dsTipoTelefone']   = $tel['dsTipoTelefone'];
                    $t++;
                }
            }
            
            $listaOperadoras[$i]['telefones'] = $listaTelefones;
            $i++;
        }
        
        $this->view->assign('operadorasAtivas', $listaOperadoras);
        
    }
    
    public function listaBeneficiariaAction() {
        $where = array();
        if($_POST){
            
            $CNPJ       = $this->getRequest()->getParam('CNPJ');
            $NOME       = $this->getRequest()->getParam('NOME');
            $OPERADORA  = $this->getRequest()->getParam('OPERADORA');
            $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
            $DTFIM      = $this->getRequest()->getParam('DTFIM');

            if ($CNPJ) {
                $where['pj.nr_Cnpj = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.nm_Razao_Social like ?'] = '%' . $NOME . '%';
            }

            if ($OPERADORA > 0) {
                $where['id_Operadora = ?'] = $OPERADORA;
            }

            if (strlen($DTINICIO) == 10) {
                $DTINICIO = explode('/', $DTINICIO);
                if (checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    $where['dtInscricao >= ?'] = $DTINICIO[2] . '-' . $DTINICIO[1] . '-' . $DTINICIO[0];
                }
            }

            if (strlen($DTFIM) == 10) {
                $DTFIM = explode('/', $DTFIM);
                if (checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    $DTFIMQuery = explode('/', date('d/m/Y', strtotime("+1 days", strtotime($DTFIM[0] . '-' . $DTFIM[1] . '-' . $DTFIM[2]))));
                    $where['dtInscricao < ?'] = $DTFIMQuery[2] . '-' . $DTFIMQuery[1] . '-' . $DTFIMQuery[0];
                }
            }

            $this->view->assign('cnpj', $CNPJ);
            $this->view->assign('nome', $NOME);
            $this->view->assign('operadora', $OPERADORA);
            
            if (is_array($DTINICIO)) {
                $this->view->assign('dtInicio', $DTINICIO[0] . '/' . $DTINICIO[1] . '/' . $DTINICIO[2]);
            } else {
                $this->view->assign('dtInicio', '');
            }

            if (is_array($DTFIM)) {
                $this->view->assign('dtFim', $DTFIM[0] . '/' . $DTFIM[1] . '/' . $DTFIM[2]);
            } else {
                $this->view->assign('dtFim', '');
            }

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/admin/lista-beneficiarios/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/admin/lista-beneficiarios/', 'error');
                }
            }
            
        } else {
            
            $this->view->assign('cnpj', '');
            $this->view->assign('nome', '');
            $this->view->assign('operadora', '');
            $this->view->assign('dtInicio', '');
            $this->view->assign('dtFim', '');
        }
        
        $this->getHelper('layout')->disableLayout();
        $modelSituacao          = new Application_Model_Situacao();
        $beneficiariasAtivas    = $modelSituacao->selecionaBeneficiariasAtivas($where);
        $operadoras             = $modelSituacao->selecionaOperadorasAtivasInativas();
        
        $this->view->assign('beneficiariasAtivas', $beneficiariasAtivas);
        $this->view->assign('operadorasAtivas', $operadoras);
    }
    
}

