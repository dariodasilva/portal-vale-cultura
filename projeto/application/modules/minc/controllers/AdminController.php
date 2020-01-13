<?php

include_once 'GenericController.php';

class Minc_AdminController extends GenericController {

    private $session;

    public function init() {

        // Layout Padrão
        $this->view->layout()->setLayout('layout');

        // Título
        $this->view->assign('titulo', 'Minc');

        // Sessão
        $session = new Zend_Session_Namespace('user');
        $this->_sessao = $session->usuario;
        $this->view->assign('usuarioLogado', $session->usuario);
//        xd($session->usuario);
        parent::init();
    }

    public function indexAction() {
        // Manter Autenticado
        parent::autenticar(array('A','R','C'));

        $beneficiarias = array();

        if(in_array($this->_sessao['PerfilGeral'], array('R'))){
            $idResponsavel = $this->_sessao["idPessoa"];

            $modelBeneficiaria = new Application_Model_Beneficiaria();

            $where = array(
                'pv.ID_PESSOA_VINCULADA = ?' => $idResponsavel,
                'pv.ST_PESSOA_VINCULADA = ?' => 'A',
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16
            );

            $beneficiarias = $modelBeneficiaria->buscarBeneficiariasDoResponsavel($where, "b.dt_Inscricao desc");
        }
        $this->view->beneficiarias = $beneficiarias;

    }

    public function listarOperadorasResponsavelAction() {
        parent::autenticar(array('R'));

        $idResponsavel = $this->_sessao["idPessoa"];
        // Listar todas as operadoras
        $modelOperadoras = new Application_Model_Operadora();

        $where = array(
            'pv.ID_PESSOA_VINCULADA = ?' => $idResponsavel,
            'pv.ST_PESSOA_VINCULADA = ?' => 'A',
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17
        );

        $operadoras = $modelOperadoras->buscarOperadorasDoResponsavel($where, "o.dt_Inscricao desc");
        $this->view->operadoras = $operadoras;
    }

    public function listarBeneficiariasResponsavelAction() {
        parent::autenticar(array('R'));

        $idResponsavel = $this->_sessao["idPessoa"];

        // Listar todas as operadoras
        $modelBeneficiaria = new Application_Model_Beneficiaria();

        $where = array(
            'pv.ID_PESSOA_VINCULADA = ?' => $idResponsavel,
            'pv.ST_PESSOA_VINCULADA = ?' => 'A',
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16
        );

        $beneficiarias = $modelBeneficiaria->buscarBeneficiariasDoResponsavel($where, "b.dt_Inscricao desc");
        $this->view->beneficiarias = $beneficiarias;

    }

    public function mudarSituacaoAction()
    {

        // Manter Autenticado
        parent::autenticar(array('A'));

        $id = $this->_request->getParam('id');
        $idOperadora = $this->_request->getParam('idOperadora');
        $opcao = $this->_request->getParam('opcao');
        $idTipoSituacao = $this->_request->getParam('situacao');
        $dsJustificativa = $this->_request->getParam('dsJustificativa');

        try {

            $modelSituacao = new Application_Model_Situacao();
            $modelOperadora = new Application_Model_Operadora();
            $modelBeneficiaria = new Application_Model_Beneficiaria();
            $modelPessoaVinculada = new Application_Model_PessoaVinculada();
            $modelEmail = new Application_Model_Email();
            $modelTelefone = new Application_Model_Telefone();
            $modelPessoaJuridica = new Application_Model_PessoaJuridica();
            $modelPessoaFisica = new Application_Model_PessoaFisica();

//            xd($this->getRequest()->getParams());
            $dados = array('ID_PESSOA' => $id,
                'DS_JUSTIFICATIVA' => $dsJustificativa,
                'ID_USUARIO' => $this->_sessao['idUsuario'],
                'ID_TIPO_SITUACAO' => $idTipoSituacao);

            if ($opcao == 'operadora') {
                $dados['TP_ENTIDADE_VALE_CULTURA'] = 'O';
            } else {
                $dados['TP_ENTIDADE_VALE_CULTURA'] = 'B';
            }

            $atualiza = $modelSituacao->insert($dados);

            if ($opcao == 'operadora') {

                if ($atualiza) {
                    // Gerar numeração do certificado
                    if ($idTipoSituacao == 2) {
                        $operadora = $modelOperadora->select(array('ID_OPERADORA = ?' => $id));
                        if ($operadora[0]['NR_CERTIFICADO'] == '' || $operadora[0]['NR_CERTIFICADO'] == null) {
                            $nrCertificado = $modelOperadora->criaNrCertificado();
                            $modelOperadora->update(array('NR_CERTIFICADO' => $nrCertificado['nrCertificado']), $id);
                        }
                    }
                }

                if ($idTipoSituacao == 2) {

                    $responsaveis = $modelPessoaVinculada->select(array('ID_PESSOA = ?' => $id));
                    $pessoaJuridica = $modelPessoaJuridica->select(array('ID_PESSOA_JURIDICA = ?' => $id));

                    if (count($pessoaJuridica) > 0) {
                        $nomeEmpresa = $pessoaJuridica[0]['NM_RAZAO_SOCIAL'];
                    } else {
                        $nomeEmpresa = '';
                    }

                    //Enviar e-mail
                    $htmlEmail = emailAprovacaoHTML();
                    $htmlEmail = str_replace('#PERFIL#', $opcao, $htmlEmail);
                    $htmlEmail = str_replace('#NOMEEMPRESA#', $nomeEmpresa, $htmlEmail);
                    $htmlEmail = str_replace('#URL#', 'http://vale.cultura.gov.br', $htmlEmail);

                    if (count($responsaveis) > 0) {
                        $responsavel = $responsaveis[0]['ID_PESSOA_VINCULADA'];
                        $eamils = $modelEmail->select(array('ID_PESSOA = ?' => $responsavel));
                        foreach ($eamils as $email) {
                            $enviarEmail = $modelEmail->enviarEmail($email['DS_EMAIL'], 'Acesso ao sistema Vale Cultura', $htmlEmail);
                        }
                    }
                }
                parent::message('Operação realizada com sucesso!', '/minc/admin/avaliar-operadora/operadora/' . $id, 'success');
            } else {

                if ($atualiza) {
                    // Gerar numeração do certificado
                    if ($idTipoSituacao == 2) {
                        $beneficiaria = $modelBeneficiaria->select(array('ID_OPERADORA = ?' => $id));
                        if ($beneficiaria[0]['NR_CERTIFICADO'] == '' || $beneficiaria[0]['NR_CERTIFICADO'] == null) {
                            $nrCertificado = $modelOperadora->criaNrCertificado();
                            $modelBeneficiaria->update(array('NR_CERTIFICADO' => $nrCertificado['nrCertificado']), $id);
                        }
                    }
                }

                if ($idTipoSituacao == 2) {

                    // Beneficiária
                    $responsaveis = $modelPessoaVinculada->select(array('ID_PESSOA = ?' => $id, 'ST_PESSOA_VINCULADA = ?' => 'A'));
                    $pessoaJuridica = $modelPessoaJuridica->select(array('ID_PESSOA_JURIDICA = ?' => $id));
                    // dados da operadora escolhida
                    $operadora = $modelPessoaJuridica->select(array('ID_PESSOA_JURIDICA = ?' => $idOperadora));
                    // Responsaveis operadora escolhida
                    $where = array(
                        'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16,
                        'pv.ID_PESSOA = ?' => $id,
                        'pv.ST_PESSOA_VINCULADA = ?' => 'A',
                        'up.id_Perfil = ?' => 2
                    );

                    $responsaveisBeneficiarias = $modelPessoaVinculada->buscarDadosResponsavel($where);
                    // Responsaveis operadora escolhida
                    $where = array(
                        'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17,
                        'pv.ID_PESSOA = ?' => $idOperadora,
                        'pv.ST_PESSOA_VINCULADA = ?' => 'A',
                        'up.id_Perfil = ?' => 3
                    );

                    $responsaveisOperadora = $modelPessoaVinculada->buscarDadosResponsavel($where);
                    // Telefones Operadora SAC
                    $sacOperadora = '';
                    $listaSacOperadora = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $idOperadora));

                    foreach ($listaSacOperadora as $sac) {
                        if ($sac->cdDDD != '') {
                            $sacOperadora.= '(' . $sac->cdDDD . ')' . $sac->nrTelefone;
                        } else {
                            $sacOperadora.= $sac->nrTelefone;
                        }
                        $sacOperadora.= '  ';
                    }

                    $nomeOperadora = $operadora[0]['NM_RAZAO_SOCIAL'];
                    $cnpjOperadora = $operadora[0]['NR_CNPJ'];

                    if (count($pessoaJuridica) > 0) {
                        $nomeEmpresa = $pessoaJuridica[0]['NM_RAZAO_SOCIAL'];
                    } else {
                        $nomeEmpresa = '';
                    }

                    //Enviar e-mail para o responsável da beneficiária
                    $htmlEmail = emailAprovacaoBeneficiariaHTML();
                    $htmlEmail = str_replace('#PERFIL#', $opcao, $htmlEmail);
                    $htmlEmail = str_replace('#NOMEEMPRESA#', $nomeEmpresa, $htmlEmail);
                    $htmlEmail = str_replace('#URL#', 'http://vale.cultura.gov.br', $htmlEmail);

                    $htmlEmail = str_replace('#NOMEOPERADORA#', $nomeOperadora, $htmlEmail);
                    $htmlEmail = str_replace('#SAC#', $sacOperadora, $htmlEmail);

                    // Tem que enviar para todos os responsáveis da beneficiaria
                    foreach ($responsaveis as $respB) {
                        $eamils = $modelEmail->select(array('ID_PESSOA = ?' => $respB['ID_PESSOA_VINCULADA']));
                        foreach ($eamils as $email) {
                            $enviarEmail = $modelEmail->enviarEmail($email['DS_EMAIL'], 'Acesso ao sistema Vale Cultura', $htmlEmail);
                        }
                    }

                    //Enviar e-mail para o responsável da operadora
                    $txtResponsavel = '';
                    foreach ($responsaveisBeneficiarias as $ro) {

                        $txtResponsavel.= $ro->nmPessoaFisica;
                        $tels = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $ro->idPessoaVinculada));
                        if (count($tels) > 0) {
                            foreach ($tels as $t) {
                                $txtResponsavel.= '  ';
                                $txtResponsavel.= '(' . $t->cdDDD . ')' . $t->nrTelefone;
                                $txtResponsavel.= '  ';
                            }
                        }

                        $txtResponsavel.= '<br><br>';
                    }

                    // Enviar email para o responsável da operadora escolhida
                    $htmlEmailOperadora = emailAprovacaoBeneficiariaParaOperadoraHTML();
                    $htmlEmailOperadora = str_replace('#PERFIL#', $opcao, $htmlEmailOperadora);
                    $htmlEmailOperadora = str_replace('#NOMEBENEFICIARIA#', $nomeEmpresa, $htmlEmailOperadora);
                    $htmlEmailOperadora = str_replace('#CNPJBENEFICIARIA#', addMascara($pessoaJuridica[0]['NR_CNPJ'], 'cnpj'), $htmlEmailOperadora);
                    $htmlEmailOperadora = str_replace('#NOMEOPERADORA#', $nomeOperadora, $htmlEmailOperadora);
                    $htmlEmailOperadora = str_replace('#RESPONSAVEIS#', $txtResponsavel, $htmlEmailOperadora);
                    $htmlEmailOperadora = str_replace('#URL#', 'http://vale.cultura.gov.br', $htmlEmailOperadora);

                    foreach ($responsaveisOperadora as $ro) {
                        $eamils = $modelEmail->select(array('ID_PESSOA = ?' => $ro->idPessoaVinculada));
                        foreach ($eamils as $email) {
                            $enviarEmail = $modelEmail->enviarEmail($email['DS_EMAIL'], 'Acesso ao sistema Vale Cultura', $htmlEmailOperadora);
                        }
                    }
                } else if ($idTipoSituacao == 3) { // Caso o cadastro da empresa seja reprovado
                    $responsaveis = $modelPessoaVinculada->select(array('ID_PESSOA = ?' => $id, 'ST_PESSOA_VINCULADA = ?' => 'A'));

                    foreach ($responsaveis as $respB) {
                        $pessoa = $modelPessoaFisica->select(array('ID_PESSOA_FISICA = ?' => $respB['ID_PESSOA_VINCULADA']));

                        $eamils = $modelEmail->select(array('ID_PESSOA = ?' => $respB['ID_PESSOA_VINCULADA']));

                        $htmlEmail = emailReprovacaoBeneficiariaHTML();
                        $htmlEmail = str_replace('#NOMERESPONSAVEL#', $pessoa[0]['NM_PESSOA_FISICA'], $htmlEmail);

                        foreach ($eamils as $email) {
                            $enviarEmail = $modelEmail->enviarEmail($email['DS_EMAIL'], 'Acesso ao sistema Vale Cultura', $htmlEmail);
                        }
                    }
                }

                parent::message('Operação realizada com sucesso!', '/minc/admin/avaliar-beneficiaria/beneficiaria/' . $id, 'success');
            }
        } catch (Exception $e) {
            if ($opcao == 'operadora') {
                parent::message('Erro ao atualizar', '/minc/admin/avaliar-operadora/operadora/' . $id, 'error');
            } else {
                parent::message('Erro ao atualizar', '/minc/admin/avaliar-beneficiaria/beneficiaria/' . $id, 'error');
            }
        }
    }

    public function listaOperadoraAction() {

        // Manter Autenticado
        parent::autenticar(array('A'));

        $modelOperadoras    = new Application_Model_Operadora();
        $modelSituacoes     = new Application_Model_TipoSituacao();
        $where              = array();

        if ($_POST) {

            $CNPJ       = $this->getRequest()->getParam('CNPJ');
            $NOME       = $this->getRequest()->getParam('NOME');
            $SITUACAO   = $this->getRequest()->getParam('SITUACAO');
            $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
            $DTFIM      = $this->getRequest()->getParam('DTFIM');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.NM_FANTASIA like ? OR pj.NM_RAZAO_SOCIAL like ? '] = '%' . $NOME . '%';                
            }

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if (strlen($DTINICIO) == 10) {
                $DTINICIO = explode('/', $DTINICIO);
                if (checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    $where['DT_INSCRICAO >= ?'] = $DTINICIO[2] . '-' . $DTINICIO[1] . '-' . $DTINICIO[0];
                }
            }

            if (strlen($DTFIM) == 10) {
                $DTFIM = explode('/', $DTFIM);
                if (checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    $DTFIMQuery = explode('/', date('d/m/Y', strtotime("+1 days", strtotime($DTFIM[0] . '-' . $DTFIM[1] . '-' . $DTFIM[2]))));
                    $where['DT_INSCRICAO < ?'] = $DTFIMQuery[2] . '-' . $DTFIMQuery[1] . '-' . $DTFIMQuery[0];
                }
            }

            $this->view->assign('cnpj', $CNPJ);
            $this->view->assign('nome', $NOME);
            $this->view->assign('situacao', $SITUACAO);

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
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/consulta/lista-beneficiarios/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/consulta/lista-beneficiarios/', 'error');
                }
            }

        } else {
            $this->view->assign('cnpj', '');
            $this->view->assign('nome', '');
            $this->view->assign('situacao', '');
            $this->view->assign('dtInicio', '');
            $this->view->assign('dtFim', '');
        }

        // Listar todas as operadoras
        $operadoras         = $modelOperadoras->buscarDados($where, array('situacao',"o.DT_INSCRICAO asc"));
        $this->view->assign('operadoras', $operadoras);
        $this->view->assign('qtdOperadoras', count($operadoras));

        $situacoes = $modelSituacoes->select();
        $this->view->assign('situacoes', $situacoes);
    }

    public function avaliarOperadoraAction() {

        // Manter Autenticado
        parent::autenticar(array('A'));

        // Onde são montado os dados da operadora
        $dadosOperadora = array();

        // id da operadora
        $idOperadora = $this->_request->getParam('operadora');

        // Caso não tenha passado o id da Operadora ele retorna para a lista
        if (empty($idOperadora)) {
            parent::message('Selecione uma operadora!', '/admin/lista-operadora', 'ALERT');
        }

        $modelSituacao      = new Application_Model_Situacao();
        $modelOperadoras    = new Application_Model_Operadora();

        // Dados da operadora
        $operadora = $modelOperadoras->buscarDados(array('o.ID_OPERADORA = ?' => $idOperadora));
        // Situação atual da operadora
        $stOperadora = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idOperadora, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));
        // Histórico de aprovações
        $historico = $modelSituacao->listarSituacoes(array('ID_PESSOA = ?' => $idOperadora, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));
//        xd($operadora);
        foreach ($operadora as $op) {
            // tbOperadora
            $dadosOperadora['idOperadora']              = $op->idOperadora;
            $dadosOperadora['dtInscricao']              = $op->dtInscricao;
            $dadosOperadora['nrComprovanteInscricao']   = $op->nrComprovanteInscricao;
            $dadosOperadora['nrCertificado']            = $op->nrCertificado;
            //tbPessoa
            $dadosOperadora['idpessoa']                 = $op->idPessoa;
            $dadosOperadora['dtregistro']               = $op->dtRegistro;
            //tbPessoaJuridica
            $dadosOperadora['nrCnpj']                   = $op->nrCnpj;
            $dadosOperadora['nrInscricaoEstadual']      = $op->nrInscricaoEstadual;
            $dadosOperadora['nmRazaoSocial']            = $op->nmRazaoSocial;
            $dadosOperadora['nmFantasia']               = $op->nmFantasia;
            $dadosOperadora['nrCei']                    = $op->nrCei;
            $dadosOperadora['cdNaturezaJuridica']       = $op->cdNaturezaJuridica;
            //tbEndereco
            $dadosOperadora['dsComplementoEndereco']    = $op->dsComplementoEndereco;
            $dadosOperadora['nrComplemento']            = $op->nrComplemento;
            //tbBairro
            $dadosOperadora['nmBairro']                 = $op->NM_BAIRRO;
            //tbLogradouro
            $dadosOperadora['logradouro']               = strlen($op->dsLograEndereco) > 3 ? $op->dsLograEndereco : $op->dsTipoLogradouro . ' ' . $op->nmLogradouro;
            $dadosOperadora['cep']                      = $op->nrCep;
            $dadosOperadora['Pais']                     = $op->nmPais;
            $dadosOperadora['Estado']                   = $op->sgUF;
            $dadosOperadora['Municipio']                = $op->nmMunicipio;
            // Situacao do Operador
            $dadosOperadora['dtSituacao']               = isset($stOperadora[0]) ? $stOperadora[0]['dtSituacao'] : '';
            $dadosOperadora['idTipoSituacao']           = isset($stOperadora[0]) ? $stOperadora[0]['idTipoSituacao'] : '';
            $dadosOperadora['dsTipoSituacao']           = isset($stOperadora[0]) ? $stOperadora[0]['dsTipoSituacao'] : '';
            $dadosOperadora['stTipoSituacao']           = isset($stOperadora[0]) ? $stOperadora[0]['stTipoSituacao'] : '';
        }

        // Dados do responsável da operadora
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();

        $where = array(
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17,
            'pv.id_Pessoa = ?'              => $idOperadora,
            'up.id_Perfil = ?'              => 3,
            'up.st_Usuario_Perfil = ?'      => 'A'
        );

        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        $listaResponsaveis = array();
        $r = 0;
        foreach ($responsavel as $re){

            $listaResponsaveis[$r]['idResponsavel']    = $re->idPessoaVinculada;
            $listaResponsaveis[$r]['nmResponsavel']    = $re->nmPessoaFisica;
            $listaResponsaveis[$r]['nrCpfResponsavel'] = $re->nrCpf;
            $listaResponsaveis[$r]['cargoResponsavel'] = $re->nmCbo;
            $listaResponsaveis[$r]['stAtivo']          = $re->ST_PESSOA_VINCULADA;


            // Email do responsável da operadora
            $modelEmail = new Application_Model_Email();
            $listaEmailsResponsaveis = array();
            $em = 0;
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $re->idPessoaVinculada));
            foreach ($emails as $e) {
                $listaEmailsResponsaveis[$em]['emailResponsavel'] = $e->dsEmail;
                $em++;
            }

            $listaResponsaveis[$r]['emailsResponsavel'] = $listaEmailsResponsaveis;


            // Telefones do responsável da operadora
            $modelTelefone  = new Application_Model_Telefone();
            $telefones      = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));

            $listaTelefonesResponsaveis = array();
            $tel = 0;
            foreach ($telefones as $t) {
                if ($t->idTipoTelefone == 2) {
                    $listaTelefonesResponsaveis[$tel]['tipoTel'] = 2;
                    $listaTelefonesResponsaveis[$tel]['TelResponsavel'] = addMascara($t->cdDDD . $t->nrTelefone, 'telefone');
                }
                if ($t->idTipoTelefone == 4) {
                    $listaTelefonesResponsaveis[$tel]['tipoTel'] = 4;
                    $listaTelefonesResponsaveis[$tel]['TelResponsavel'] = addMascara($t->cdDDD . $t->nrTelefone, 'telefone');
                }
                $tel++;
            }

            $listaResponsaveis[$r]['telefonesResponsavel'] = $listaTelefonesResponsaveis;

            $r++;
        }

        $dadosOperadora['responsaveis'] = $listaResponsaveis;

        // Email Institucional
        $emailInstitucional = array();
        $modelEmail = new Application_Model_Email();
        $email = $modelEmail->select(array('ID_TIPO_EMAIL = ?' => 2, 'ID_PESSOA = ?' => $idOperadora), 'DS_EMAIL', 1);
        if(count($email) > 0){
            $emailInstitucional = $email[0];
        }
        $this->view->assign('emailInstitucional', $emailInstitucional);

        // Arquivos da operadora
        $modelArquivos = new Application_Model_ArquivoOperadora();
        $arquivos = $modelArquivos->buscarArquivos(array('ID_OPERADORA = ?' => $idOperadora));

        // Envia as informações para a view
        $this->view->assign('arquivosOperadora', $arquivos);
        $this->view->assign('operadora', $dadosOperadora);
        $this->view->assign('idOperadora', $idOperadora);
        $this->view->assign('historico', $historico);
    }

    public function listaBeneficiariosAction() {
        // Manter Autenticado
        parent::autenticar(array('A'));

        // Listar todas as operadoras
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $modelSituacoes = new Application_Model_TipoSituacao();

        $pagina = intval($this->_getParam('pagina'));
        $session = new Zend_Session_Namespace('postFiltro');
        $where = array();
        if ($_POST) {
            $CNPJ           = $this->getRequest()->getParam('CNPJ');
            $NOME           = $this->getRequest()->getParam('NOME');
            $SITUACAO       = $this->getRequest()->getParam('SITUACAO');
            $DTINICIO       = $this->getRequest()->getParam('DTINICIO');
            $DTFIM          = $this->getRequest()->getParam('DTFIM');
            $STAUTORIZAMINC = $this->getRequest()->getParam('STAUTORIZAMINC');

            $session->filtro = array(
                    'CNPJ'          => $CNPJ,
                    'NOME'          => $NOME,
                    'SITUACAO'      => $SITUACAO,
                    'DTINICIO'      => $DTINICIO,
                    'DTFIM'         => $DTFIM,
                    'STAUTORIZAMINC' => $STAUTORIZAMINC
                );
        } else {
            if($pagina < 1){
                $session->filtro = null;
                $pagina = 1;
            }
            if (is_array($session->filtro)) {
                $CNPJ           = $session->filtro['CNPJ'];
                $NOME           = $session->filtro['NOME'];
                $SITUACAO       = $session->filtro['SITUACAO'];
                $DTINICIO       = $session->filtro['DTINICIO'];
                $DTFIM          = $session->filtro['DTFIM'];
                $STAUTORIZAMINC = $session->filtro['STAUTORIZAMINC'];
            }else{
                $CNPJ           = null;
                $NOME           = null;
                $SITUACAO       = null;
                $DTINICIO       = null;
                $DTFIM          = null;
                $STAUTORIZAMINC = null;
            }
        }

        if ($CNPJ) {
            $where['pj.nr_Cnpj = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
        }

        if ($NOME) {
            $where['pj.nm_Razao_Social like ?'] = '%' . $NOME . '%';
        }

        if ($SITUACAO > 0) {
            $where['ID_SITUACAO = ?'] = $SITUACAO;
        }

        if (strlen($DTINICIO) == 10) {
            $DTINICIO = explode('/', $DTINICIO);
            if (checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                $where['DT_INSCRICAO >= ?'] = $DTINICIO[2] . '-' . $DTINICIO[1] . '-' . $DTINICIO[0];
            }
        }

        if (strlen($DTFIM) == 10) {
            $DTFIM = explode('/', $DTFIM);
            if (checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                $DTFIMQuery = explode('/', date('d/m/Y', strtotime("+1 days", strtotime($DTFIM[0] . '-' . $DTFIM[1] . '-' . $DTFIM[2]))));
                $where['DT_INSCRICAO < ?'] = $DTFIMQuery[2] . '-' . $DTFIMQuery[1] . '-' . $DTFIMQuery[0];
            }
        }

        if (($STAUTORIZAMINC !== null) && ($STAUTORIZAMINC !== 'null')) {
            $where['ST_AUTORIZA_MINC = ?'] = $STAUTORIZAMINC;
        }

        $this->view->assign('cnpj', $CNPJ);
        $this->view->assign('nome', $NOME);
        $this->view->assign('situacao', $SITUACAO);
        $this->view->assign('stAutorizaMinc', $STAUTORIZAMINC);

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

        $beneficiarias = $modelBeneficiaria->buscarDados($where, array('situacao','b.dt_Inscricao desc'));

        $paginator = Zend_Paginator::factory($beneficiarias);
        // Seta a quantidade de registros por página
        $paginator->setItemCountPerPage(50);
        // numero de paginas que serão exibidas
        $paginator->setPageRange(7);
        // Seta a página atual
        $paginator->setCurrentPageNumber($pagina);
        // Passa o paginator para a view
        $this->view->beneficiarias = $paginator;

        $situacoes = $modelSituacoes->select();
        $this->view->assign('situacoes', $situacoes);
        $this->view->assign('qtdBeneficiarias', count($beneficiarias));
    }

    public function avaliarBeneficiariaAction() {
        // Manter Autenticado
        parent::autenticar(array('A'));

        // onde são montado os dados da operadora
        $dadosBeneficiaria = array();

        // id da operadora
        $idBeneficiaria = $this->_request->getParam('beneficiaria');

        // Caso não tenha passado o id da Operadora ele retorna para a lista
        if (empty($idBeneficiaria)) {
            parent::message('Selecione uma beneficiaria!', '/admin/lista-beneficiarios', 'ALERT');
        }

        // Dados da operadora
        $modelSituacao      = new Application_Model_Situacao();
        $modelOperadora     = new Application_Model_Operadora();
        $modelBeneficiaria  = new Application_Model_Beneficiaria();

        $beneficiaria = $modelBeneficiaria->buscarDados(array('b.ID_BENEFICIARIA = ?' => $idBeneficiaria));
        // Situação atual da beneficiaria
        $stBeneficiaria = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idBeneficiaria, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));
        // Histórico de aprovações
        $historico = $modelSituacao->listarSituacoes(array('ID_PESSOA = ?' => $idBeneficiaria, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));

        foreach ($beneficiaria as $b) {
            // tbOperadora
            $dadosBeneficiaria['idOperadora']               = $b->idOperadora;
            // tbBeneficiaria
            $dadosBeneficiaria['idBeneficiaria']            = $b->idBeneficiaria;
            $dadosBeneficiaria['dtInscricao']               = $b->dtInscricao;
            $dadosBeneficiaria['nrComprovanteInscricao']    = $b->nrComprovanteInscricao;
            $dadosBeneficiaria['nrCertificado']             = $b->nrCetificado;
            //tbPessoa
            $dadosBeneficiaria['idpessoa']                  = $b->idPessoa;
            $dadosBeneficiaria['dtregistro']                = $b->dtRegistro;
            //tbPessoaJuridica
            $dadosBeneficiaria['nrCnpj']                    = $b->nrCnpj;
            $dadosBeneficiaria['nrInscricaoEstadual']       = $b->nrInscricaoEstadual;
            $dadosBeneficiaria['nmRazaoSocial']             = $b->nmRazaoSocial;
            $dadosBeneficiaria['nmFantasia']                = $b->nmFantasia;
            $dadosBeneficiaria['nrCei']                     = $b->nrCei;
            $dadosBeneficiaria['cdNaturezaJuridica']        = $b->cdNaturezaJuridica;
            $dadosBeneficiaria['dsNaturezaJuridica']        = $b->dsNaturezaJuridica;
            $dadosBeneficiaria['dsTipoLucro']               = $b->dsTipoLucro;
            $dadosBeneficiaria['idTipoLucro']               = $b->idTipoLucro;
            //tbEndereco
            $dadosBeneficiaria['dsComplementoEndereco']     = $b->dsComplementoEndereco;
            $dadosBeneficiaria['nrComplemento']             = $b->nrComplemento;
            //tbBairro
            $dadosBeneficiaria['nmBairro']                  = $b->nmBairro;
            //tbLogradouro
            $dadosBeneficiaria['logradouro']                = strlen($b->dsLograEndereco) > 3 ? $b->dsLograEndereco : $b->dsTipoLogradouro . ' ' . $b->nmLogradouro;
            $dadosBeneficiaria['cep']                       = $b->nrCep;
            $dadosBeneficiaria['Pais']                      = $b->nmPais;
            $dadosBeneficiaria['Estado']                    = $b->sgUF;
            $dadosBeneficiaria['Municipio']                 = $b->nmMunicipio;
            // Situacao do Operador
            $dadosBeneficiaria['dtSituacao']                = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['dtSituacao'] : '';
            $dadosBeneficiaria['idTipoSituacao']            = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['idTipoSituacao'] : '';
            $dadosBeneficiaria['dsTipoSituacao']            = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['dsTipoSituacao'] : '';
            $dadosBeneficiaria['stTipoSituacao']            = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['stTipoSituacao'] : '';

            // CNAE Principal
            $modelCNAE = new Application_Model_PessoaJuridicaCNAE();
            $whereP = array('p.ID_PESSOA_JURIDICA = ?' => $b->idPessoa, 'p.ST_CNAE = ?' => 'P');
            $cnaePrincipal = $modelCNAE->listarCnae($whereP);
            $dadosBeneficiaria['nmCnaePrincipal'] = $cnaePrincipal;
            // CNAEs Secundários
            $whereS = array('p.ID_PESSOA_JURIDICA = ?' => $b->idPessoa, 'p.ST_CNAE = ?' => 'S');
            $cnaeSecundarios = $modelCNAE->listarCnae($whereS);
            $dadosBeneficiaria['cnaeSecundarios'] = $cnaeSecundarios;

            $modelFaixaSalarial = new Application_Model_FaixaSalarialBeneficiaria();
            $faixaSalarial = $modelFaixaSalarial->listaFaixas(array('ID_BENEFICIARIA = ?' => $b->idPessoa), 'idTipoFaixaSalarial asc');

            $dadosBeneficiaria['faixaSalarial'] = $faixaSalarial;
        }

        // Dados do responsável da operadora
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();

        $where = array(
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16,
                'pv.id_Pessoa = ?'              => $idBeneficiaria,
                'up.id_Perfil = ?'              => 2,
                'up.st_Usuario_Perfil = ?'      => 'A'
            );

        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        $listaResponsaveis = array();
        $r = 0;
        foreach ($responsavel as $re){

            $listaResponsaveis[$r]['idResponsavel']    = $re->idPessoaVinculada;
            $listaResponsaveis[$r]['nmResponsavel']    = $re->nmPessoaFisica;
            $listaResponsaveis[$r]['nrCpfResponsavel'] = $re->nrCpf;
            $listaResponsaveis[$r]['cargoResponsavel'] = $re->nmCbo;


            // Email do responsável da operadora
            $modelEmail = new Application_Model_Email();
            $listaEmailsResponsaveis = array();
            $em = 0;
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $re->idPessoaVinculada));
            foreach ($emails as $e) {
                $listaEmailsResponsaveis[$em]['emailResponsavel'] = $e->dsEmail;
                $em++;
            }

            $listaResponsaveis[$r]['emailsResponsavel'] = $listaEmailsResponsaveis;


            // Telefones do responsável da operadora
            $modelTelefone  = new Application_Model_Telefone();
            $telefones      = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));

            $listaTelefonesResponsaveis = array();
            $tel = 0;
            foreach ($telefones as $t) {
                if ($t->idTipoTelefone == 2) {
                    $listaTelefonesResponsaveis[$tel]['tipoTel'] = 2;
                    $listaTelefonesResponsaveis[$tel]['TelResponsavel'] = addMascara($t->cdDDD . $t->nrTelefone, 'telefone');
                }
                if ($t->idTipoTelefone == 4) {
                    $listaTelefonesResponsaveis[$tel]['tipoTel'] = 4;
                    $listaTelefonesResponsaveis[$tel]['TelResponsavel'] = addMascara($t->cdDDD . $t->nrTelefone, 'telefone');
                }
                $tel++;
            }

            $listaResponsaveis[$r]['telefonesResponsavel'] = $listaTelefonesResponsaveis;

            $r++;
        }

        $dadosBeneficiaria['responsaveis'] = $listaResponsaveis;

        // Dados da Operadora (Bandeira)
        $operadora   = $modelOperadora->buscarDados(array('o.ID_OPERADORA = ?' => $dadosBeneficiaria['idOperadora']));
        $stOperadora = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $dadosBeneficiaria['idOperadora'], 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));

        $dadosBeneficiaria['nmFantasiaOperadora'] = $operadora[0]['nmFantasia'];
        $dadosBeneficiaria['nmRazaoSocialOperadora'] = $operadora[0]['nmRazaoSocial'];
        // Situacao do Operador
        if (count($stOperadora) > 0) {
            $dadosBeneficiaria['dtSituacaoOperadora']     = $stOperadora[0]['dtSituacao'];
            $dadosBeneficiaria['idTipoSituacaoOperadora'] = $stOperadora[0]['idTipoSituacao'];
            $dadosBeneficiaria['dsTipoSituacaoOperadora'] = $stOperadora[0]['dsTipoSituacao'];
            $dadosBeneficiaria['stTipoSituacaoOperadora'] = $stOperadora[0]['stTipoSituacao'];
        }

        $ultimaSituacaoCadastral = $modelBeneficiaria->ultimaSituacaoCadastral($idBeneficiaria);

        $dadosBeneficiaria['dataSituacaoCadastral'] = empty($ultimaSituacaoCadastral) ? '' : $ultimaSituacaoCadastral['DT_SITUACAO_CADASTRAL'];
        $dadosBeneficiaria['descricaoSituacaoCadastral'] = empty($ultimaSituacaoCadastral) ? '' : $ultimaSituacaoCadastral['DS_SITUACAO_CADASTRAL'];

        // Envia as informações para a view
        $this->view->assign('beneficiaria', $dadosBeneficiaria);
        $this->view->assign('idbeneficiaria', $idBeneficiaria);
        $this->view->assign('historico', $historico);
    }

    public function abrirArquivoAction() {
        $uploaddir = "/var/arquivos/arquivos-valecultura/";
        $arquivo = $this->getRequest()->getParam('arquivo');
        if (file_exists($uploaddir . $arquivo)) {
            $len = filesize($uploaddir . $arquivo);
            header("Content-type: application/pdf");
            header("Content-Length: $len");
            header("Content-Disposition: inline; filename= $arquivo");
            $existente = readfile($uploaddir . $arquivo);
        } else {
            parent::message('Arquivo não encontrado', '/operadora/index/arquivos/', 'error');
        }
        die;
    }

    public function alterarsenhaAction() {

    }

    public function alterarsenhaactionAction() {
        if ($_POST) {

            $modelUsuario = new Application_Model_Usuario;
            $idUsuario = $this->_sessao["idUsuario"];

            $NOVA_SENHA          = $this->getRequest()->getParam('NOVA_SENHA');
            $NOVA_SENHA_CONFIRMA = $this->getRequest()->getParam('NOVA_SENHA_CONFIMA');
            $SENHA_ATUAL         = $this->getRequest()->getParam('SENHA');

            if (!$SENHA_ATUAL) {
                parent::message('Informe a senha atual', '/minc/admin/alterarsenha/', 'error');
            }
            if (!$NOVA_SENHA) {
                parent::message('Informe a nova senha', '/minc/admin/alterarsenha/', 'error');
            }
            if ($NOVA_SENHA != $NOVA_SENHA_CONFIRMA) {
                parent::message('Senha de confirmação incorreta', '/minc/admin/alterarsenha/', 'error');
            }

            //VALIDA SENHA ATUAL
            $where = array(
                'id_Usuario = ?'    => $idUsuario,
                'ds_Senha = ?'      => md5($SENHA_ATUAL)
            );

            $recuperaUsuario = $modelUsuario->select($where);
            if (count($recuperaUsuario) > 0) {
                $cols = array(
                    'ds_Senha' => md5($NOVA_SENHA)
                );
                if ($modelUsuario->update($cols, $idUsuario)) {
                    parent::message('Senha atualizada com sucesso', '/minc/admin/alterarsenha/', 'confirm');
                }
            } else {
                parent::message('Senha atual incorreta', '/minc/admin/alterarsenha/', 'error');
            }
        }
    }

    public function relatorioBeneficiariasAction() {
        // Manter Autenticado
        parent::autenticar(array('A'));

        // Listar todas as operadoras
        $modelBeneficiaria  = new Application_Model_Beneficiaria();
        $modelSituacoes     = new Application_Model_TipoSituacao();
        $modelUf            = new Application_Model_Uf();
        $modelSituacao      = new Application_Model_Situacao();

        $where = array();
        if ($_POST) {

            $CNPJ       = $this->getRequest()->getParam('CNPJ');
            $NOME       = $this->getRequest()->getParam('NOME');
            $SITUACAO   = $this->getRequest()->getParam('SITUACAO');
            $UF         = $this->getRequest()->getParam('UF');
            $OPERADORA  = $this->getRequest()->getParam('OPERADORA');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.NM_FATANSIA like ?'] = '%' . $NOME . '%';
            }

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($UF) {
                $where['uf.SG_Uf = ?'] = $UF;
            }

            if ($OPERADORA > 0) {
                $where['b.ID_OPERADORA = ?'] = $OPERADORA;
            }

            $this->view->assign('cnpj', $CNPJ);
            $this->view->assign('nome', $NOME);
            $this->view->assign('situacao', $SITUACAO);
            $this->view->assign('uf', $UF);
            $this->view->assign('operadora', $OPERADORA);
        } else {
            $this->view->assign('cnpj', '');
            $this->view->assign('nome', '');
            $this->view->assign('situacao', '');
            $this->view->assign('uf', '');
            $this->view->assign('operadora', '');
        }

        $beneficiarias      = $modelBeneficiaria->buscarDados($where, array("pj.nmFantasia asc"));
        $situacoes          = $modelSituacoes->select();
        $ufs                = $modelUf->select(array(),'NM_Uf asc');
        $operadorasAtivas   = $modelSituacao->selecionaOperadorasAtivas();

        $this->view->assign('beneficiarias', $beneficiarias);
        $this->view->assign('situacoes', $situacoes);
        $this->view->assign('ufs', $ufs);
        $this->view->assign('operadorasAtivas', $operadorasAtivas);
        $this->view->assign('qtdBeneficiarias', count($beneficiarias));
    }

    public function gerarPdfAction() {
        // Manter Autenticado
        parent::autenticar(array('A'));

        $this->getHelper('layout')->disableLayout();
        if ($_POST) {
            $html = $this->getRequest()->getParam('HTML');
            $url = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'index.php'));

            $html = utf8_encode(str_replace('../../', $url, $html));
            $html = explode('#QUEBRAPAGINA#', $html);
            //$cabecalho = utf8_encode($cabecalho);
            $nomeDoc = $this->getRequest()->getParam('nomeArquivo');
            include('MPDF/mpdf.php');
            $mpdf = new mPDF();

            //$mpdf->AddPage('L');
            unset($html[0]);
            $pg = 1;
            foreach ($html as $pagina) {
                $mpdf->AddPage();
                $mpdf->WriteHTML(str_replace('-->', '', $pagina));
                $mpdf->SetHTMLFooter('<div style="font-size:7pt"><b>Pagina ' . $pg . '</b></div>');
                $pg++;
            }

            $mpdf->Output($nomeDoc . '.pdf', 'D');
        }
        exit();
        die;
    }

    public function atualizarDadosReceitaFederalAction()
    {
        $url = "/admin/avaliar-beneficiaria/beneficiaria/{$this->getRequest()->getParam('beneficiaria')}";
        try {

            $servicos = new Servicos();
            $servicos->consultarPessoaReceitaFederal($this->getRequest()->getParam('cnpj'), 'Juridica', true);
            parent::message('Operação realizada com sucesso!', $url, 'success');
        } catch (InvalidArgumentException $exception) {
            parent::message("Erro ao atualizar: {$exception->getMessage()}", $url, 'error');
        } catch (Exception $exception) {
            parent::message("Erro ao atualizar", $url, 'error');
        }
    }

}
