<?php

include_once 'GenericController.php';

class Minc_ConsultaController extends GenericController
{

    private $session;

    public function init()
    {

        // Layout Padrão
        $this->view->layout()->setLayout('layout');

        // Título
        $this->view->assign('titulo', 'Minc');

        // Manter Autenticado
        parent::autenticar(array('C', 'A'));

        // Inicialização Generic
        parent::init();
    }

    public function indexAction()
    {

    }

    public function listaOperadoraAction()
    {

        $modelOperadoras = new Application_Model_Operadora();
        $modelSituacoes = new Application_Model_TipoSituacao();
        $where = array();

        if ($_POST) {

            $CNPJ = $this->getRequest()->getParam('CNPJ');
            $NOME = $this->getRequest()->getParam('NOME');
            $SITUACAO = $this->getRequest()->getParam('SITUACAO');
            $DTINICIO = $this->getRequest()->getParam('DTINICIO');
            $DTFIM = $this->getRequest()->getParam('DTFIM');

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
        $operadoras = $modelOperadoras->buscarDados($where, "o.DT_INSCRICAO asc");
        $this->view->assign('operadoras', $operadoras);
        $this->view->assign('qtdOperadoras', count($operadoras));

        $situacoes = $modelSituacoes->select();
        $this->view->assign('situacoes', $situacoes);

    }

    public function avaliarOperadoraAction()
    {

        // onde são montado os dados da operadora
        $dadosOperadora = array();

        // id da operadora
        $idOperadora = $this->_request->getParam('operadora');

        // Caso não tenha passado o id da Operadora ele retorna para a lista
        if (empty($idOperadora)) {
            parent::message('Selecione uma operadora!', '/admin/lista-operadora', 'ALERT');
        }

        // Dados da operadora
        $modelSituacao = new Application_Model_Situacao();
        $modelOperadoras = new Application_Model_Operadora();
        $operadora = $modelOperadoras->buscarDados(array('o.ID_OPERADORA = ?' => $idOperadora));
        // Situação atual da operadora
        $stOperadora = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idOperadora, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));
        // Histórico de aprovações
        $historico = $modelSituacao->listarSituacoes(array('ID_PESSOA = ?' => $idOperadora, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));
        foreach ($operadora as $op) {
            // tbOperadora
            $dadosOperadora['idOperadora'] = $op->idOperadora;
            $dadosOperadora['dtInscricao'] = $op->dtInscricao;
            $dadosOperadora['nrComprovanteInscricao'] = $op->nrComprovanteInscricao;
            $dadosOperadora['nrCertificado'] = $op->nrCertificado;
            //tbPessoa
            $dadosOperadora['idpessoa'] = $op->idPessoa;
            $dadosOperadora['dtregistro'] = $op->dtRegistro;
            //tbPessoaJuridica
            $dadosOperadora['nrCnpj'] = $op->nrCnpj;
            $dadosOperadora['nrInscricaoEstadual'] = $op->nrInscricaoEstadual;
            $dadosOperadora['nmRazaoSocial'] = $op->nmRazaoSocial;
            $dadosOperadora['nmFantasia'] = $op->nmFantasia;
            //INSERSAO DO CAMPO URL
            $dadosOperadora['dsSite'] = $op->dsSite;
            $dadosOperadora['nrCei'] = $op->nrCei;
            $dadosOperadora['cdNaturezaJuridica'] = $op->cdNaturezaJuridica;
            //tbEndereco
            $dadosOperadora['dsComplementoEndereco'] = $op->dsComplementoEndereco;
            $dadosOperadora['nrComplemento'] = $op->nrComplemento;
            //tbBairro
            $dadosOperadora['nmBairro'] = $op->nmBairro;
            //tbLogradouro
            $dadosOperadora['logradouro'] = strlen($op->dsLograEndereco) > 3 ? $op->dsLograEndereco : $op->dsTipoLogradouro . ' ' . $op->nmLogradouro;
            $dadosOperadora['cep'] = $op->nrCep;
            $dadosOperadora['Pais'] = $op->nmPais;
            $dadosOperadora['Estado'] = $op->sgUF;
            $dadosOperadora['Municipio'] = $op->nmMunicipio;

            // Situacao do Operador
            $dadosOperadora['dtSituacao'] = isset($stOperadora[0]) ? $stOperadora[0]['dtSituacao'] : '';
            $dadosOperadora['idTipoSituacao'] = isset($stOperadora[0]) ? $stOperadora[0]['idTipoSituacao'] : '';
            $dadosOperadora['dsTipoSituacao'] = isset($stOperadora[0]) ? $stOperadora[0]['dsTipoSituacao'] : '';
            $dadosOperadora['stTipoSituacao'] = isset($stOperadora[0]) ? $stOperadora[0]['stTipoSituacao'] : '';
        }

        // Dados do responsável da operadora
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();

        $where = array(
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17,
            'pv.id_Pessoa = ?' => $idOperadora,
            'up.id_Perfil = ?' => 3,
            'up.st_Usuario_Perfil = ?' => 'A'
        );

        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        $listaResponsaveis = array();
        $r = 0;
        foreach ($responsavel as $re) {

            $listaResponsaveis[$r]['idResponsavel'] = $re->idPessoaVinculada;
            $listaResponsaveis[$r]['nmResponsavel'] = $re->nmPessoaFisica;
            $listaResponsaveis[$r]['nrCpfResponsavel'] = $re->nrCpf;
            $listaResponsaveis[$r]['cargoResponsavel'] = $re->nmCbo;
            $listaResponsaveis[$r]['stAtivo'] = $re->ST_PESSOA_VINCULADA;

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
            $modelTelefone = new Application_Model_Telefone();
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));

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

        // Arquivos da operadora
        $modelArquivos = new Application_Model_ArquivoOperadora();
        $arquivos = $modelArquivos->buscarArquivos(array('ID_OPERADORA = ?' => $idOperadora));

        // Envia as informações para a view
        $this->view->assign('arquivosOperadora', $arquivos);
        $this->view->assign('operadora', $dadosOperadora);
        $this->view->assign('idOperadora', $idOperadora);
        $this->view->assign('historico', $historico);
    }

    public function listaBeneficiariosAction()
    {
        // Listar todas as operadoras
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $modelSituacoes = new Application_Model_TipoSituacao();
        $where = array();

        if ($_POST) {

            $CNPJ = $this->getRequest()->getParam('CNPJ');
            $NOME = $this->getRequest()->getParam('NOME');
            $SITUACAO = $this->getRequest()->getParam('SITUACAO');
            $DTINICIO = $this->getRequest()->getParam('DTINICIO');
            $DTFIM = $this->getRequest()->getParam('DTFIM');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.NM_FANTASIA like ?'] = '%' . $NOME . '%';
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

        $beneficiarias = $modelBeneficiaria->buscarDados($where, "b.DT_INSCRICAO desc");

        $pagina = intval($this->_getParam('pagina', 1));
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

    public function avaliarBeneficiariaAction()
    {

        // onde são montado os dados da operadora
        $dadosBeneficiaria = array();

        // id da operadora
        $idBeneficiaria = $this->_request->getParam('beneficiaria');

        // Caso não tenha passado o id da Operadora ele retorna para a lista
        if (empty($idBeneficiaria)) {
            parent::message('Selecione uma beneficiaria!', '/consulta/lista-beneficiarios', 'ALERT');
        }

        // Dados da operadora
        $modelSituacao = new Application_Model_Situacao();
        $modelOperadora = new Application_Model_Operadora();
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $beneficiaria = $modelBeneficiaria->buscarDados(array('b.ID_BENEFICIARIA = ?' => $idBeneficiaria));
        // Situação atual da beneficiaria
        $stBeneficiaria = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idBeneficiaria, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));
        // Histórico de aprovações
        $historico = $modelSituacao->listarSituacoes(array('ID_PESSOA = ?' => $idBeneficiaria, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));
        foreach ($beneficiaria as $b) {

            // tbOperadora
            $dadosBeneficiaria['idOperadora'] = $b->idOperadora;
            // tbBeneficiaria
            $dadosBeneficiaria['idBeneficiaria'] = $b->idBeneficiaria;
            $dadosBeneficiaria['dtInscricao'] = $b->dtInscricao;
            $dadosBeneficiaria['nrComprovanteInscricao'] = $b->nrComprovanteInscricao;
            $dadosBeneficiaria['nrCertificado'] = $b->nrCetificado;
            //tbPessoa
            $dadosBeneficiaria['idpessoa'] = $b->idPessoa;
            $dadosBeneficiaria['dtregistro'] = $b->dtRegistro;
            //tbPessoaJuridica
            $dadosBeneficiaria['nrCnpj'] = $b->nrCnpj;
            $dadosBeneficiaria['nrInscricaoEstadual'] = $b->nrInscricaoEstadual;
            $dadosBeneficiaria['nmRazaoSocial'] = $b->nmRazaoSocial;
            $dadosBeneficiaria['nmFantasia'] = $b->nmFantasia;
            $dadosBeneficiaria['nrCei'] = $b->nrCei;
            $dadosBeneficiaria['cdNaturezaJuridica'] = $b->cdNaturezaJuridica;
            $dadosBeneficiaria['dsNaturezaJuridica'] = $b->dsNaturezaJuridica;
            $dadosBeneficiaria['dsTipoLucro'] = $b->dsTipoLucro;
            $dadosBeneficiaria['idTipoLucro'] = $b->idTipoLucro;
            //tbEndereco
            $dadosBeneficiaria['dsComplementoEndereco'] = $b->dsComplementoEndereco;
            $dadosBeneficiaria['nrComplemento'] = $b->nrComplemento;
            //tbBairro
            $dadosBeneficiaria['nmBairro'] = $b->nmBairro;
            //tbLogradouro
            $dadosBeneficiaria['logradouro'] = strlen($b->dsLograEndereco) > 3 ? $b->dsLograEndereco : $b->dsTipoLogradouro . ' ' . $b->nmLogradouro;
            $dadosBeneficiaria['cep'] = $b->nrCep;
            $dadosBeneficiaria['Pais'] = $b->nmPais;
            $dadosBeneficiaria['Estado'] = $b->sgUF;
            $dadosBeneficiaria['Municipio'] = $b->nmMunicipio;
            // Situacao do Operador
            $dadosBeneficiaria['dtSituacao'] = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['dtSituacao'] : '';
            $dadosBeneficiaria['idTipoSituacao'] = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['idTipoSituacao'] : '';
            $dadosBeneficiaria['dsTipoSituacao'] = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['dsTipoSituacao'] : '';
            $dadosBeneficiaria['stTipoSituacao'] = isset($stBeneficiaria[0]) ? $stBeneficiaria[0]['stTipoSituacao'] : '';


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
            $faixaSalarial = $modelFaixaSalarial->listaFaixas(array('ID_BENEFICIARIA = ?' => $b->idPessoa), 'tp.DS_TIPO_FAIXA_SALARIAL asc');

            $dadosBeneficiaria['faixaSalarial'] = $faixaSalarial;
        }

        // Dados do responsável da operadora
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();

        $where = array(
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16,
            'pv.id_Pessoa = ?' => $idBeneficiaria,
            'up.id_Perfil = ?' => 2,
            'up.st_Usuario_Perfil = ?' => 'A'
        );

        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        $listaResponsaveis = array();
        $r = 0;
        foreach ($responsavel as $re) {

            $listaResponsaveis[$r]['idResponsavel'] = $re->idPessoaVinculada;
            $listaResponsaveis[$r]['nmResponsavel'] = $re->nmPessoaFisica;
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
            $modelTelefone = new Application_Model_Telefone();
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));

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
        $operadora = $modelOperadora->buscarDados(array('o.ID_OPERADORA = ?' => $dadosBeneficiaria['idOperadora']));

        $stOperadora = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $dadosBeneficiaria['idOperadora'], 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));

        $dadosBeneficiaria['NM_FANTASIA_OPERADORA'] = $operadora[0]['nmFantasia'];
        $dadosBeneficiaria['NM_RAZAO_SOCIAL_OPERADORA'] = $operadora[0]['nmRazaoSocial'];
        // Situacao do Operador
        if (count($stOperadora) > 0) {
            $dadosBeneficiaria['dtSituacaoOperadora'] = $stOperadora[0]['dtSituacao'];
            $dadosBeneficiaria['idTipoSituacaoOperadora'] = $stOperadora[0]['idTipoSituacao'];
            $dadosBeneficiaria['dsTipoSituacaoOperadora'] = $stOperadora[0]['dsTipoSituacao'];
            $dadosBeneficiaria['stTipoSituacaoOperadora'] = $stOperadora[0]['stTipoSituacao'];
        }
        // Envia as informações para a view
        $this->view->assign('beneficiaria', $dadosBeneficiaria);
        $this->view->assign('idbeneficiaria', $idBeneficiaria);
        $this->view->assign('historico', $historico);
    }

    public function abrirArquivoAction()
    {
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

    public function alterarsenhaAction()
    {

    }

    public function alterarsenhaactionAction()
    {
        if ($_POST) {

            $modelUsuario = new Application_Model_Usuario;

            $NOVA_SENHA = $this->getRequest()->getParam('NOVA_SENHA');
            $NOVA_SENHA_CONFIRMA = $this->getRequest()->getParam('NOVA_SENHA_CONFIMA');
            $SENHA_ATUAL = $this->getRequest()->getParam('SENHA');

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
                'ID_USUARIO = ?' => $this->_sessao['idUsuario'],
                'DS_SENHA = ?' => md5($SENHA_ATUAL)
            );

            $recuperaUsuario = $modelUsuario->select($where);
            if (count($recuperaUsuario) > 0) {
                $cols = array(
                    'DS_SENHA' => md5($NOVA_SENHA)
                );
                if ($modelUsuario->update($cols, $this->_sessao['idUsuario'])) {
                    parent::message('Senha atualizada com sucesso', '/minc/admin/alterarsenha/', 'confirm');
                }
            } else {
                parent::message('Senha atual incorreta', '/minc/admin/alterarsenha/', 'error');
            }
        }
    }

    public function relatorioBeneficiariasAction()
    {
        // Listar todas as operadoras
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $modelSituacoes = new Application_Model_TipoSituacao();
        $modelUf = new Application_Model_Uf();
        $modelSituacao = new Application_Model_Situacao();

        $where = array();
        if ($_POST) {

            $CNPJ = $this->getRequest()->getParam('CNPJ');
            $NOME = $this->getRequest()->getParam('NOME');
            $SITUACAO = $this->getRequest()->getParam('SITUACAO');
            $UF = $this->getRequest()->getParam('UF');
            $OPERADORA = $this->getRequest()->getParam('OPERADORA');
            $DTINICIO = $this->getRequest()->getParam('DTINICIO');
            $DTFIM = $this->getRequest()->getParam('DTFIM');
            $DTINICIO = $this->getRequest()->getParam('DTINICIO');
            $DTFIM = $this->getRequest()->getParam('DTFIM');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.NM_FANTASIA like ?'] = '%' . $NOME . '%';
            }

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($UF) {
                $where['uf.SG_UF = ?'] = $UF;
            }

            if ($OPERADORA > 0) {
                $where['b.ID_OPERADORA = ?'] = $OPERADORA;
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
            $this->view->assign('situacao', $SITUACAO);
            $this->view->assign('uf', $UF);
            $this->view->assign('operadora', $OPERADORA);
            $this->view->assign('dtInicio', $DTINICIO[0] . '/' . $DTINICIO[1] . '/' . $DTINICIO[2]);
            $this->view->assign('dtFim', $DTFIM[0] . '/' . $DTFIM[1] . '/' . $DTFIM[2]);

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/consulta/relatorio-beneficiarias/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/consulta/relatorio-beneficiarias/', 'error');
                }
            }

        } else {
            $this->view->assign('cnpj', '');
            $this->view->assign('nome', '');
            $this->view->assign('situacao', '');
            $this->view->assign('uf', '');
            $this->view->assign('operadora', '');
            $this->view->assign('dtInicio', '');
            $this->view->assign('dtFim', '');
        }

        //$beneficiarias = $modelBeneficiaria->buscarDados($where, array("uf.nmUf asc", "mu.nmMunicipio", "pj.nmFantasia asc"));
        $beneficiarias = $modelBeneficiaria->buscarDados($where, array("pj.NM_FANTASIA asc"));
        $situacoes = $modelSituacoes->select();
        $ufs = $modelUf->select(array(), 'NM_UF asc');
        $operadorasAtivas = $modelSituacao->selecionaOperadorasAtivas();

        $this->view->assign('beneficiarias', $beneficiarias);
        $this->view->assign('situacoes', $situacoes);
        $this->view->assign('ufs', $ufs);
        $this->view->assign('operadorasAtivas', $operadorasAtivas);
        $this->view->assign('qtdBeneficiarias', count($beneficiarias));
    }

    public function gerarPdfAction()
    {
        $this->getHelper('layout')->disableLayout();
        if ($_POST) {
            $html = $this->getRequest()->getParam('HTML');
            $url = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'index.php'));

            $html = utf8_encode(str_replace('../../', $url, $html));
            $html = explode('#QUEBRAPAGINA#', $html);

            $tableTag = '<table id="listaBeneficiarias" width="100%" cellpadding="0"  class="tablesorter" border="0" style="font-size: 7pt;">';
            $trTag = '<tr class="cresceFonte">';

            $rodape = $html[2];
            $cabecalho = preg_replace('@<table [\/\!]*?[^<>]*?>@si', '', $html[0]);
            $cabecalho = str_replace('-->', '', $cabecalho);
            $cabecalho = str_replace('<!--', '', $cabecalho);


            $nomeDoc = $this->getRequest()->getParam('nomeArquivo');
            include('MPDF/mpdf.php');
            $mpdf = new mPDF();

            $linhas = explode($trTag, $html[1]);

            //$mpdf->AddPage('L');
            //unset($html[0]);
            //$pg = 1;
            $qtLinhas = 0;
            $qtMaxLinhas = 25;
            $arrPaginas = array();
            $conteudoPagina = null;

            foreach ($linhas as $linha) {
                if ($qtLinhas >= $qtMaxLinhas) {
                    array_push($arrPaginas, $conteudoPagina);
                    $conteudoPagina = null;
                    $qtLinhas = 0;
                }
                $conteudoPagina .= $trTag . $linha;
                $qtLinhas++;
            }

            if (!empty($conteudoPagina)) {
                array_push($arrPaginas, $conteudoPagina);
                $conteudoPagina = null;
                $qtLinhas = 0;
            }

            $pg = 1;
            foreach ($arrPaginas as $pagina) {
                $paginaPrint = $tableTag . $cabecalho . $pagina . "</tbody></table>";
                if ($pg >= count($arrPaginas)) {
                    $paginaPrint .= $rodape;
                }
                $paginaPrint = str_replace('-->', '', $paginaPrint);
                $paginaPrint = str_replace('<!--', '', $paginaPrint);
                $paginaPrint = str_replace('<thead>', '', $paginaPrint);
                $paginaPrint = str_replace('</thead>', '', $paginaPrint);
                $paginaPrint = str_replace('<tbody>', '', $paginaPrint);
                $paginaPrint = str_replace('</tbody>', '', $paginaPrint);

                $mpdf->AddPage();
                $mpdf->WriteHTML($paginaPrint);
                $mpdf->SetHTMLFooter('<div style="font-size:7pt"><b>Pagina ' . $pg . '</b></div>');
                $pg++;
            }

            $mpdf->Output($nomeDoc . '.pdf', 'D');
        }
        exit();
        die;
    }

}

