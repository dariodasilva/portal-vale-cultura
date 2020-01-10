<?php

include_once 'GenericController.php';

class Beneficiaria_IndexController extends GenericController
{

    private $session;
    private $idBeneficiaria;

    public function init()
    {

        // Layout Padrão
        $this->view->layout()->setLayout('layout');

        // Título
        $this->view->assign('titulo', 'Beneficiaria');

        parent::autenticar(array('R', 'A'));

        $this->view->assign('admin', false);
        if ($this->_sessao["PerfilGeral"] == 'A') {
            $this->view->assign('admin', true);
        }

        parent::init();

    }

    public function indexAction()
    {
        $beneficiariaSimular = $this->getRequest()->getParam('beneficiaria');
        $operadoraSimular = $this->getRequest()->getParam('operadora');

        $session = new Zend_Session_Namespace('user');
        $sessao = $this->_sessao;

        if ($this->_sessao["PerfilGeral"] == 'A') {
            $sessao["beneficiaria"] = $beneficiariaSimular;
            $sessao["operadora"] = $operadoraSimular;
            $session->usuario = $sessao;
            $this->_redirect('/beneficiaria/index/dados-beneficiaria');
        } else {

            if ($this->validarAcessoBeneficiadora($beneficiariaSimular, $sessao["idPessoa"])) {
                $sessao["beneficiaria"] = $beneficiariaSimular;
                $sessao["operadora"] = $operadoraSimular;
                $session->usuario = $sessao;
                $this->_redirect('/beneficiaria/index/dados-beneficiaria');
            } else {
                $sessao["beneficiaria"] = '';
                $sessao["operadora"] = '';
                $session->usuario = $sessao;
                parent::message('Beneficiária não foi localizada!', '/minc/admin', 'error');
            }
        }


    }

    public function dadosBeneficiariaAction()
    {
        $dadosBeneficiaria = array();
        $idBeneficiaria = $this->_sessao['beneficiaria'];
        if (empty($idBeneficiaria)) {
            parent::message('Beneficiária não foi localizada!', '/minc/admin', 'error');
        }

        // Dados da beneficiaria
        $modelSituacao = new Application_Model_Situacao();
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $modelNaturezaJuridica = new Application_Model_NaturezaJuridica();
        $modelTipoLucro = new Application_Model_TipoLucro();
        $modelCNAEPj = new Application_Model_PessoaJuridicaCNAE();

        $situacao = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idBeneficiaria, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));

        $naturezaJuridica = $modelNaturezaJuridica->select(array(), 'DS_NATUREZA_JURIDICA');
        $tipoLucro = $modelTipoLucro->select(array('ID_TIPO_LUCRO != ?' => 2), 'DS_TIPO_LUCRO');
        $operadoras = $modelSituacao->selecionaOperadorasAtivasInativas();

        if ($situacao[0]["idTipoSituacao"] != 2 && isset($situacao["idTipoSituacao"])) {
            $this->view->bloqueiaForm = false;
        } else {
            $this->view->bloqueiaForm = true;
        }

        $beneficiaria = $modelBeneficiaria->buscarDados(array('b.ID_BENEFICIARIA = ?' => $idBeneficiaria));
        $historico = $modelSituacao->listarSituacoes(array('ID_PESSOA = ?' => $idBeneficiaria, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));
        //xd($beneficiaria);
        foreach ($beneficiaria as $op) {
            // tbOperadora
            $dadosBeneficiaria['idBeneficiaria'] = $op->idBeneficiaria;
            $dadosBeneficiaria['dtInscricao'] = $op->dtInscricao;
            $dadosBeneficiaria['nrComprovanteInscricao'] = $op->nrComprovanteInscricao;
            $dadosBeneficiaria['nrCertificado'] = $op->nrCetificado;
            $dadosBeneficiaria['idOperadora'] = $op->idOperadora;
            $dadosBeneficiaria['situacao'] = $op->situacao;
            //tbPessoa
            $dadosBeneficiaria['idpessoa'] = $op->idPessoa;
            $dadosBeneficiaria['dtregistro'] = $op->dtRegistro;
            //tbPessoaJuridica
            $dadosBeneficiaria['nrCnpj'] = addMascara($op->nrCnpj, 'cnpj');
            $dadosBeneficiaria['nrInscricaoEstadual'] = $op->nrInscricaoEstadual;
            $dadosBeneficiaria['nmRazaoSocial'] = $op->nmRazaoSocial;
            $dadosBeneficiaria['nmFantasia'] = $op->nmFantasia;
            $dadosBeneficiaria['nrCei'] = $op->nrCei;
            $dadosBeneficiaria['idTipoLucro'] = $op->idTipoLucro;
            //tbNaturezaJuridica
            $dadosBeneficiaria['cdNaturezaJuridica'] = $op->cdNaturezaJuridica;
            $dadosBeneficiaria['dsNaturezaJuridica'] = $op->dsNaturezaJuridica;
            //tbEndereco
            $dadosBeneficiaria['dsComplementoEndereco'] = $op->dsComplementoEndereco;
            $dadosBeneficiaria['nrComplemento'] = $op->nrComplemento;
            //tbBairro
            $dadosBeneficiaria['idBairro'] = $op->idBairro;
            $dadosBeneficiaria['nmBairro'] = $op->nmBairro;
            //tbLogradouro
            $dadosBeneficiaria['logradouro'] = $op->nmLogradouro;
            $dadosBeneficiaria['cep'] = addMascara($op->nrCep, 'cep');
            $dadosBeneficiaria['Pais'] = $op->nmPais;
            $dadosBeneficiaria['nmU'] = $op->nmUF;
            $dadosBeneficiaria['sgUF'] = $op->sgUF;
            $dadosBeneficiaria['nmMunicipio'] = $op->nmMunicipio;
            $dadosBeneficiaria['idMunicipio'] = $op->idMunicipio;
            $dadosBeneficiaria['ST_DIVULGAR_DADOS'] = $op->stDivulgarDados;

            // CNAE Principal
            $whereP = array('p.ID_PESSOA_JURIDICA = ?' => $op->idBeneficiaria, 'p.ST_CNAE = ?' => 'P');
            $cnaePrincipal = $modelCNAEPj->listarCnae($whereP);
            // CNAEs Secundários
            $whereS = array('p.ID_PESSOA_JURIDICA = ?' => $op->idBeneficiaria, 'p.ST_CNAE = ?' => 'S');
            $cnaeSecundarios = $modelCNAEPj->listarCnae($whereS);
            $dadosBeneficiaria['idCnaeSecundarios'] = array();
        }

        // Envia as informações para a view
        $this->view->assign('beneficiaria', $dadosBeneficiaria);
        $this->view->assign('naturezaJuridica', $naturezaJuridica);
        $this->view->assign('tipoLucro', $tipoLucro);
        $this->view->assign('operadoras', $operadoras);
        $this->view->assign('CNAEPrincipal', $cnaePrincipal);
        $this->view->assign('CNAESecundarios', $cnaeSecundarios);
        $this->view->assign('historico', $historico);
    }

    public function atualizarDadosBeneficiariaAction()
    {

        $idBeneficiaria = $this->_sessao['beneficiaria'];

        if ($_POST) {

            $modelEndereco = new Application_Model_Endereco();
            $modelLogradouro = new Application_Model_Logradouro();
            $modelBeneficiaria = new Application_Model_Beneficiaria();
            $modelPessoaJuridicaLucro = new Application_Model_PessoaJuridicaLucro();
            $modelSituacao = new Application_Model_Situacao();

            //Recuperando form
            $NRCEP = str_replace('-', '', $this->getRequest()->getParam('cep'));
            $DSCOMPLEMENTOENDERECO = trim($this->getRequest()->getParam('dsComplementoEndereco'));
            $NRCOMPLEMENTO = trim($this->getRequest()->getParam('nrComplemento'));
            $DSLOGRAENDERECO = trim($this->getRequest()->getParam('logradouro'));
            $IDBAIRRO = $this->getRequest()->getParam('nmBairro');
            $IDOPERADORAATUAL = $this->getRequest()->getParam('EMPRESA_OPERADORA_ATUAL');
            $IDOPERADORA = $this->getRequest()->getParam('EMPRESA_OPERADORA');
            $AUTORIZO_OPERADORA = $this->getRequest()->getParam('AUTORIZO_OPERADORA');
            $IDTIPOLUCRO = $this->getRequest()->getParam('EMPRESA_TIPO_LUCRO');

            $where['NR_CEP = ?'] = $NRCEP;
            $logradouro = $modelLogradouro->selectEndereco($where);
            if (count($logradouro) < 1 || strlen($NRCEP) != 8) {
                parent::message('CEP inválido', '/beneficiaria/index/dados-beneficiaria/', 'error');
            } else {
                $IDLOGRADOURO = $logradouro[0]['ID_LOGRADOURO'];
                $STLOGRADOURO = $logradouro[0]['ST_LOGRADOURO'];
                $IDBAIRRO = (!empty($IDBAIRRO)) ? $IDBAIRRO : $logradouro[0]['ID_BAIRRO_INICIO'];
            }

            if (!$IDTIPOLUCRO) {
                parent::message('Informe o tipo de tributação', '/beneficiaria/index/dados-beneficiaria/', 'error');
            }

            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();

            $idPessoaJuridica = $idBeneficiaria;

            try {

                // Passo 1 - Pessoa Juridica Lucro
                $where = array('ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica);

                $existePessoaJuridicaLucro = $modelPessoaJuridicaLucro->select($where);

                $Cols = array(
                    'ID_PESSOA_JURIDICA' => $idPessoaJuridica,
                    'ID_TIPO_LUCRO' => $IDTIPOLUCRO
                );

                if (count($existePessoaJuridicaLucro) == 0) {
                    $modelPessoaJuridicaLucro->insert($Cols);
                } else {
                    $modelPessoaJuridicaLucro->update($Cols, array('ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica));
                }

                //Inserindo na model endereco
//                $whereLogradouro['NR_CEP = ?'] = $NRCEP;
//                $logradouro = $modelLogradouro->selectEndereco($whereLogradouro);
                $Cols = array(
                    'DS_COMPLEMENTO_ENDERECO' => $DSCOMPLEMENTOENDERECO,
                    'ID_LOGRADOURO' => $IDLOGRADOURO,
                    'NR_COMPLEMENTO' => $NRCOMPLEMENTO,
                    'ID_SERVICO' => 1
                );

                if ($STLOGRADOURO == 0) {
                    $Cols['DS_LOGRA_ENDERECO'] = $DSLOGRAENDERECO;
                    $Cols['ID_BAIRRO'] = $IDBAIRRO;
                } else {
                    $Cols['ID_BAIRRO'] = $IDBAIRRO;
                    $Cols['DS_BAIRRO_ENDERECO'] = $IDBAIRRO;
                }

                $modelEndereco->update($Cols, array('ID_PESSOA = ?' => $idPessoaJuridica, 'CD_TIPO_ENDERECO = ?' => '01'));

                $Cols = array(
                    'ID_OPERADORA' => $IDOPERADORA,
                    'ST_DIVULGAR_DADOS' => (int)$AUTORIZO_OPERADORA
                );

                $modelBeneficiaria->update($Cols, $idPessoaJuridica);

                if ($IDOPERADORAATUAL != $IDOPERADORA) {

                    if ($this->_sessao["PerfilGeral"] != 'A') {

                        $dadosSituacao = array(
                            'ID_PESSOA' => $idBeneficiaria,
                            'DS_JUSTIFICATIVA' => 'Alteração da Operadora.',
                            'ID_USUARIO' => $this->_sessao['idUsuario'],
                            'TP_ENTIDADE_VALE_CULTURA' => 'B',
                            'ID_TIPO_SITUACAO' => 1
                        );

                        $modelSituacao->insert($dadosSituacao);

                    }

                }

                $db->commit();
                parent::message('Dados atualizados com sucesso', '/beneficiaria/index/dados-beneficiaria/', 'confirm');
            } catch (Exception $e) {
                xd($e->getMessage());

                $db->rollBack();
                parent::message('Falha ao tentar atualizar dados', '/beneficiaria/index/dados-beneficiaria/', 'error');
            }
        }
    }

    public function emitircertificadoAction()
    {
        $dadosBeneficiaria = array();
        $textoCertificado = carregaHTMLCertificadoBeneficiaria();
        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $modelSituacao = new Application_Model_Situacao();
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $modelCNAEPj = new Application_Model_PessoaJuridicaCNAE();


        // Dados da beneficiaria
        $situacao = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idBeneficiaria, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));

        if (is_array($situacao[0])) {
            $idSituacao = isset($situacao[0]['idTipoSituacao']) ? $situacao[0]['idTipoSituacao'] : 1;
            $dsSituacao = isset($situacao[0]['dsTipoSituacao']) ? $situacao[0]['dsTipoSituacao'] : NULL;
        } else {
            $idSituacao = 1;
            $dsSituacao = isset($situacao[0]['dsTipoSituacao']) ? $situacao[0]['dsTipoSituacao'] : NULL;
        }

        if ($idSituacao == ID_SITUACAO_AUTORIZADO) {
            $this->getHelper('layout')->disableLayout();
            $beneficiaria = $modelBeneficiaria->buscarDados(array('b.ID_BENEFICIARIA = ?' => $idBeneficiaria));
            $dadosBeneficiaria = array();
            foreach ($beneficiaria as $op) {
                // tbOperadora
                $dadosBeneficiaria['idBeneficiaria'] = $op->idBeneficiaria;
                $dadosBeneficiaria['dtInscricao'] = $op->dtInscricao;
                $dadosBeneficiaria['nrComprovanteInscricao'] = $op->nrComprovanteInscricao;
                $dadosBeneficiaria['nrCertificado'] = $op->nrCetificado;
                //tbPessoa
                $dadosBeneficiaria['idpessoa'] = $op->idPessoa;
                $dadosBeneficiaria['dtregistro'] = $op->dtRegistro;
                //tbPessoaJuridica
                $dadosBeneficiaria['nrCnpj'] = $op->nrCnpj;
                $dadosBeneficiaria['nrInscricaoEstadual'] = $op->nrInscricaoEstadual;
                $dadosBeneficiaria['nmRazaoSocial'] = $op->nmRazaoSocial;
                $dadosBeneficiaria['nmFantasia'] = $op->nmFantasia;
                $dadosBeneficiaria['nrCei'] = $op->nrCei;
                //tbNaturezaJuridica
                $dadosBeneficiaria['cdNaturezaJuridica'] = $op->cdNaturezaJuridica;
                $dadosBeneficiaria['dsNaturezaJuridica'] = $op->dsNaturezaJuridica;
                //tbEndereco
                $dadosBeneficiaria['dsComplementoEndereco'] = $op->dsComplementoEndereco;
                $dadosBeneficiaria['nrComplemento'] = $op->nrComplemento;
                //tbBairro
                $dadosBeneficiaria['nmBairro'] = $op->nmBairro;
                //tbLogradouro
                $dadosBeneficiaria['logradouro'] = $op->nmLogradouro;
                $dadosBeneficiaria['cep'] = $op->nrCep;
                $dadosBeneficiaria['Pais'] = $op->nmPais;
                $dadosBeneficiaria['Estado'] = $op->nmUF;
                $dadosBeneficiaria['Municipio'] = $op->nmMunicipio;
            }

            // CNAE Principal
            $whereP = array('p.ID_PESSOA_JURIDICA = ?' => $idBeneficiaria, 'p.ST_CNAE = ?' => 'P');
            $cnaePrincipal = $modelCNAEPj->listarCnae($whereP);
            // CNAEs Secundários
            $whereS = array('p.ID_PESSOA_JURIDICA = ?' => $idBeneficiaria, 'p.ST_CNAE = ?' => 'S');
            $cnaeSecundarios = $modelCNAEPj->listarCnae($whereS);
            $dadosBeneficiaria['idCnaeSecundarios'] = array();

            $codCnaeSecundarios = '';

            $i = 0;
            foreach ($cnaeSecundarios as $s) {

                $codCnaeSecundarios .= $s->cdCNAE;
                $i++;

                if ($i < count($cnaeSecundarios)) {
                    $codCnaeSecundarios .= ' - ';
                }
            }

            // Dados do responsável da beneficiaria
            $modelPessoaVinculada = new Application_Model_PessoaVinculada();

            $where = array(
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16,
                'pv.id_Pessoa = ?' => $idBeneficiaria,
                'up.id_Perfil = ?' => 2,
                'up.st_Usuario_Perfil = ?' => 'A',
                'pv.ST_PESSOA_VINCULADA = ?' => 'A'
            );

            $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

            $txtResp = '';
            foreach ($responsavel as $re) {

                $txtResp .= '<table style="width: 100%">
                                <tr>
                                    <td align="center" width="400px">
                                        ' . uc_latin1($re->nmPessoaFisica) . '<br>
                                        Nome do Responsável pela Empresa junto ao Ministério da Cultura
                                    </td>
                                    <td align="center" width="400px">
                                        ' . uc_latin1($re->nrCpf) . '<br>
                                        CPF do Responsável pela Empresa junto ao Ministério da Cultura
                                    </td>
                                    <td align="center" width="400px">
                                        ' . uc_latin1($re->nmCbo) . '<br>
                                        Cargo do Responsável pela Empresa junto ao Ministério da Cultura
                                    </td>
                                </tr>
                            </table><br><br>';

            }

            $textoCertificado = str_replace('#DATA#', date('d/m/Y'), $textoCertificado);
            $textoCertificado = str_replace('#ANO_CERTIFICADO#', date('Y'), $textoCertificado);
            $textoCertificado = str_replace('#N_CERTIFICADO#', $dadosBeneficiaria['nrCertificado'], $textoCertificado);
            $textoCertificado = str_replace('#CNPJ#', addMascara($dadosBeneficiaria['nrCnpj'], 'cnpj'), $textoCertificado);
            $textoCertificado = str_replace('#RAZAO#', uc_latin1($dadosBeneficiaria['nmRazaoSocial']), $textoCertificado);
            $textoCertificado = str_replace('#FANTASIA#', uc_latin1($dadosBeneficiaria['nmFantasia']), $textoCertificado);
            $textoCertificado = str_replace('#ENDERECO#', uc_latin1($dadosBeneficiaria['logradouro']), $textoCertificado);
            $textoCertificado = str_replace('#BAIRRO#', uc_latin1($dadosBeneficiaria['nmBairro']), $textoCertificado);
            $textoCertificado = str_replace('#CEP#', uc_latin1($dadosBeneficiaria['cep'], 'cep'), $textoCertificado);
            $textoCertificado = str_replace('#PAIS#', $dadosBeneficiaria['Pais'], $textoCertificado);
            $textoCertificado = str_replace('#ESTADO#', uc_latin1($dadosBeneficiaria['Estado']), $textoCertificado);
            $textoCertificado = str_replace('#MUNICIPIO#', uc_latin1($dadosBeneficiaria['Municipio']), $textoCertificado);
            $textoCertificado = str_replace('#RESPONSAVEIS#', $txtResp, $textoCertificado);

            if (count($cnaePrincipal) > 0) {
                $textoCertificado = str_replace('#CNAE_PRINCIPAL#', uc_latin1($cnaePrincipal[0]->cdCNAE), $textoCertificado);
                $textoCertificado = str_replace('#CNAE_SECUNDARIOS#', uc_latin1($codCnaeSecundarios), $textoCertificado);
            } else {
                $textoCertificado = str_replace('#CNAE_PRINCIPAL#', '', $textoCertificado);
                $textoCertificado = str_replace('#CNAE_SECUNDARIOS#', '', $textoCertificado);
            }
            $textoCertificado = str_replace('#NATJUR#', uc_latin1($dadosBeneficiaria['dsNaturezaJuridica']), $textoCertificado);

            $textoCertificado = utf8_encode($textoCertificado);
            include('MPDF/mpdf.php');
            $mpdf = new mPDF();
            $mpdf->AddPage('L');
            $mpdf->WriteHTML($textoCertificado);
            $mpdf->Output('Certificado.pdf', 'D');
            exit();
            die;
        } else {
            $this->view->dsSituacao = $dsSituacao;
        }
    }

    public function arquivosAction()
    {
        $idOperadora = $this->_sessao['operadora'];
        // Arquivos da beneficiaria
        $modelArquivos = new Application_Model_ArquivoOperadora();
        $arquivos = $modelArquivos->buscarArquivos(array('ID_OPERADORA = ?' => $idOperadora));
        $this->view->assign('arquivosOperadora', $arquivos);
    }

    public function arquivosUploadAction()
    {
        $idOperadora = $this->_sessao['operadora'];
        $modelArquivoOperadora = new Application_Model_ArquivoOperadora;

        $uploaddir = "arquivos/";
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
            foreach ($_FILES as $k => $v) {
                if ($_FILES[$k]['error'] == 0) {
                    $dsArquivo = $this->getRequest()->getParam('ANEXO_NOME');
                    if (strpos($k, 'ANEXO') === false) {
                        $where = array();
                        $where['ID_ARQUIVO = ?'] = $k;
                        $where['ID_OPERADORA = ?'] = $idOperadora;
                        $arquivoAtual = $modelArquivoOperadora->select($where);
                        if (count($arquivoAtual) > 0) {
                            $mnArquivo = $arquivoAtual[0]["DS_CAMINHO_ARQUIVO"];
                            $dsArquivo = ($dsArquivo) ? $dsArquivo : $arquivoAtual[0]["DS_ARQUIVO"];
                            $whereUpdate = array(
                                'ID_ARQUIVO = ?' => $arquivoAtual[0]["ID_ARQUIVO"]
                            );
                            $acao = 'update';
                        } else {
                            parent::message('Sem permissão para substituição', '/beneficiaria/index/arquivos/', 'error');
                            return;
                        }
                    } else {
                        $acao = 'insert';
                        $mnArquivo = $idOperadora . '_' . $k . '.pdf';
                        $dsArquivo = $this->getRequest()->getParam($k . '_NOME');
                    }

                    $uploadfile = $uploaddir . $mnArquivo;

                    if (move_uploaded_file($_FILES[$k]['tmp_name'], $uploadfile)) {

                        $Cols = array(
                            'ID_OPERADORA' => $idOperadora,
                            'DS_CAMINHO_ARQUIVO' => $mnArquivo,
                            'DS_ARQUIVO' => $dsArquivo
                        );
                        if ($acao == 'update') {
                            $modelArquivoOperadora->update($Cols, $whereUpdate);
                        } else {
                            $modelArquivoOperadora->insert($Cols);
                        }
                    } else {
                        $db->rollBack();
                        parent::message('Erro ao salvar arquivo', '/beneficiaria/index/arquivos/', 'error');
                        return;
                    }
                } else {
                    parent::message('Erro ao salvar arquivo', '/beneficiaria/index/arquivos/', 'error');
                }
            }
            $db->commit();
            parent::message('Arquivo atualizado com sucesso!', '/beneficiaria/index/arquivos/', 'confirm');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $db->rollBack();
            parent::message('Erro ao salvar arquivo', '/beneficiaria/index/arquivos/', 'error');
        }
    }

    public function novoResponsavelAction()
    {
        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('idBeneficiaria', $idBeneficiaria);
    }

    public function salvarResponsavelAction()
    {

        $idBeneficiaria = $this->_sessao['beneficiaria'];

        set_time_limit('120');

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelTelefone = new Application_Model_Telefone();
        $modelEmail = new Application_Model_Email();
        $modelUsuario = new Application_Model_Usuario();
        $modelUsuarioPerfil = new Application_Model_UsuarioPerfil();
        $modelSituacao = new Application_Model_Situacao();
        $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();
        $modelDDD = new Application_Model_DDD();

        //Recuperando form
        $IDPF = $this->getRequest()->getParam('IDPF');
        $NRCPF = str_replace('.', '', str_replace('-', '', $this->getRequest()->getParam('RESPONSAVEL_CPF')));
        $CDDDDFAX = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('FaxResponsavel')))))), 0, 2);
        $NRFAX = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('FaxResponsavel')))))), 2);
        $CDDDD = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 0, 2);
        $NRTELEFONE = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 2);
        $DSEMAIL = $this->getRequest()->getParam('emailResponsavel');
        $CDCBO = $this->getRequest()->getParam('CDCBO');

        // Validando Form
        if ($IDPF == '0') {
            parent::message('CPF não encontrado!', '/beneficiaria/index/novo-responsavel', 'error');
        }

        if (strlen($NRTELEFONE) < 8) {
            parent::message('Informe o telefone', '/beneficiaria/index/novo-responsavel', 'error');
        }

        if ($CDDDD) {
            $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $CDDDD));
            if (count($verificaDDD) == 0) {
                parent::message('DDD inv&aacute;lido!', '/beneficiaria/index/novo-responsavel', 'error');
            }
        }

        if (!$DSEMAIL) {
            parent::message('Informe o e-mail', '/beneficiaria/index/novo-responsavel', 'error');
        }

        if (!validaEmail($DSEMAIL)) {
            parent::message('Email inválido!', '/beneficiaria/index/novo-responsavel', 'error');
        }

        if (count($_FILES) == 0) {
            throw new Exception("Procuração representante da empresa não enviado!", 500);
        }

        foreach ($_FILES as $k => $v) {
            if ($k == 'ANEXO_11') {
                if ($_FILES[$k]['error'] != 0) {
                    throw new Exception("Procuração representante da empresa não enviado!", 500);
                }
            }
            if ($_FILES[$k]['error'] == 0) {
                if ($_FILES[$k]["size"] >= 5242880) {
                    throw new Exception("Tamanho máximo do arquivo deve ser de 5mb!", 500);
                }

                if (strpos($_FILES[$k]['type'], 'pdf') === false) {
                    throw new Exception("Apenas aquivos no formato PDF são válidos!", 500);
                }
            } else {
                if ($_FILES[$k]['error'] != 4) {
                    throw new Exception("Falha no envio de documento!", 500);
                }
            }
        }

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {

            // Vincular o responsável
            $idPessoaFisica = $IDPF;
            $idPessoaJuridica = $idBeneficiaria;

            //Verifica se já existe esse número cadastrado
            $where = array(
                'ID_PESSOA = ?' => $idPessoaFisica,
                'SG_PAIS = ?' => 'BRA',
                'NR_TELEFONE = ?' => $NRTELEFONE,
                'ID_TIPO_TELEFONE = ?' => 2,
                'CD_DDD = ?' => $CDDDD
            );

            $existeTelefone = $modelTelefone->select($where);

            if (count($existeTelefone) == 0) {
                //Inserindo na model Telefone
                $Cols = array(
                    'ID_PESSOA' => $idPessoaFisica,
                    'SG_PAIS' => 'BRA',
                    'NR_TELEFONE' => $NRTELEFONE,
                    'ID_TIPO_TELEFONE' => 2,
                    'CD_DDD' => $CDDDD
                );

                $modelTelefone->insert($Cols);
            }

            if (strlen($NRFAX) > 7) {
                //Verifica se já existe esse número cadastrado
                $where = array(
                    'ID_PESSOA = ?' => $idPessoaFisica,
                    'SG_PAIS = ?' => 'BRA',
                    'NR_TELEFONE = ?' => $NRFAX,
                    'ID_TIPO_TELEFONE = ?' => 4,
                    'CD_DDD = ?' => $CDDDDFAX
                );

                $existeFax = $modelTelefone->select($where);
                if (count($existeFax) == 0) {
                    //Inserindo na model Telefone
                    $Cols = array(
                        'ID_PESSOA' => $idPessoaFisica,
                        'SG_PAIS' => 'BRA',
                        'NR_TELEFONE' => $NRFAX,
                        'ID_TIPO_TELEFONE' => 4,
                        'CD_DDD' => $CDDDDFAX
                    );

                    $modelTelefone->insert($Cols);
                }
            }

            // Verificar se já existe o email
            $where = array(
                'ID_PESSOA = ?' => $idPessoaFisica,
                'DS_EMAIL = ?' => $DSEMAIL,
                'ID_TIPO_EMAIL = ?' => 2,
                'ST_EMAIL_PRINCIPAL = ?' => 'S'
            );

            $existeEmail = $modelEmail->select($where);

            if (count($existeEmail) == 0) {

                //Inserindo Email do responsavel
                $Cols = array(
                    'ID_PESSOA' => $idPessoaFisica,
                    'DS_EMAIL' => $DSEMAIL,
                    'ID_TIPO_EMAIL' => 2,
                    'ST_EMAIL_PRINCIPAL' => 'S'
                );

                $modelEmail->insert($Cols);

            }

            //Inserindo CBO do responsavel
            if ($CDCBO) {
                // Verifica se já existe esse registro para não duplicar
                $whereCDCBO = array(
                    'ID_PESSOA_FISICA = ?' => $idPessoaFisica,
                    'ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica,
                );

                $existeCDCBO = $modelCBOPessoaFisica->select($whereCDCBO);

                $Cols = array(
                    'ID_PESSOA_FISICA' => $idPessoaFisica,
                    'ID_PESSOA_JURIDICA' => $idPessoaJuridica,
                    'CD_CBO' => $CDCBO
                );

                if (count($existeCDCBO) == 0) {
                    $modelCBOPessoaFisica->insert($Cols);
                } else {
                    $modelCBOPessoaFisica->update($Cols, $existeCDCBO[0]['ID_PESSOA_FISICA_CBO']);
                }
            }

            //============== VINCULANDO EMPRESA E RESPONSAVEL ==================
            // Verifica se ja existe vinculo
            $where = array(
                'ID_PESSOA = ?' => $idPessoaJuridica,
                'ID_PESSOA_VINCULADA = ?' => $idPessoaFisica,
                'ID_TIPO_VINCULO_PESSOA = ?' => 16
            );

            $vinculo = $modelPessoaVinculada->select($where);

            if (count($vinculo) == 0) {
                $Cols = array(
                    'ID_PESSOA' => $idPessoaJuridica,
                    'ID_PESSOA_VINCULADA' => $idPessoaFisica,
                    'ID_TIPO_VINCULO_PESSOA' => 16
                );

                $modelPessoaVinculada->insert($Cols);
            }

            //==================== CRIANDO USUARIO =============================
            $where = array(
                'ID_PESSOA_FISICA = ?' => $idPessoaFisica
            );

            $usuario = $modelUsuario->select($where);

            if (count($usuario) > 0) {
                $idUsuario = $usuario[0]['ID_USUARIO'];
                $enviaEmail = false;
            } else {
                $geraID = $modelUsuario->criaId();
                $idUsuario = $geraID['idUsuario'];
                $senha = gerarSenha();

                $Cols = array(
                    'ID_USUARIO' => $idUsuario,
                    'DS_LOGIN' => $NRCPF,
                    'DS_SENHA' => md5($senha),
                    'ID_PESSOA_FISICA' => $idPessoaFisica
                );

                $modelUsuario->insert($Cols);
                $enviaEmail = true;
            }

            //Verifica se usuario já tem o perfil
            $where = array(
                'ID_USUARIO = ?' => $idUsuario,
                'ID_PERFIL   = ?' => 2
            );

            $usuarioPerfil = $modelUsuarioPerfil->select($where);
            if (count($usuarioPerfil) < 1) {
                $Cols = array(
                    'ID_USUARIO' => $idUsuario,
                    'ID_PERFIL' => 2
                );

                $modelUsuarioPerfil->insert($Cols);
            }

            if ($this->_sessao["PerfilGeral"] != 'A') {

                //Cria Situação para a Operadora
                $Cols = array(
                    'ID_PESSOA' => $idPessoaJuridica,
                    'ID_USUARIO' => $idUsuario,
                    'ID_TIPO_SITUACAO' => 1,
                    'TP_ENTIDADE_VALE_CULTURA' => 'B',
                    'DS_JUSTIFICATIVA' => 'Cadastro realizado'
                );

                $modelSituacao->insert($Cols);
            }

            $modelArquivoBeneficiaria = new Application_Model_ArquivoBeneficiaria();
            $uploaddir = defined('UPLOAD_DIR') ? UPLOAD_DIR : "/var/arquivos/arquivos-valecultura/";

            foreach ($_FILES as $k => $v) {
                if ($_FILES[$k]['error'] == 0) {
                    $time = time();
                    $mnArquivo = "{$idPessoaJuridica}_{$k}_{$idPessoaFisica}_{$time}.pdf";
                    $uploadfile = $uploaddir . $mnArquivo;
                    $dsArquivo = $this->getRequest()->getParam("{$k}_NOME_{$idPessoaFisica}");

                    $Cols = array(
                        'ID_BENEFICIARIA' => $idPessoaJuridica,
                        'DS_CAMINHO_ARQUIVO' => $mnArquivo,
                        'DS_ARQUIVO' => $dsArquivo,
                        'ID_RESPONSAVEL' => $idPessoaFisica,
                    );

                    if (move_uploaded_file($_FILES[$k]['tmp_name'], $uploadfile)) {
                        $modelArquivoBeneficiaria->insert($Cols);
                    } else {
                        throw new Exception("Erro ao salvar a Procuração representante da empresa!", 500);
                    }
                }
            }

            if ($enviaEmail) {
                $links = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('link');

                $htmlEmail = emailSenhaHTML();
                $htmlEmail = str_replace('#PERFIL#', 'Operadora', $htmlEmail);
                $htmlEmail = str_replace('#URL#', $links['vale-cultura'], $htmlEmail);
                $htmlEmail = str_replace('#EMAIL#', $links['email-vale-cultura'], $htmlEmail);
                $htmlEmail = str_replace('#Senha#', $senha, $htmlEmail);
                $enviarEmail = $modelEmail->enviarEmail($DSEMAIL, 'Acesso ao sistema Vale Cultura', $htmlEmail);
            }

            $db->commit();
            parent::message('Responsável cadastrado com sucesso!', '/beneficiaria/index/responsavel', 'confirm');

        } catch (Exception $exc) {
            $db->rollBack();
            parent::message('Erro ao cadastrar o responsável.', '/beneficiaria/index/novo-responsavel', 'error');
        }

    }

    public function responsavelAction()
    {

        $dadosResponsavel = array();
        $idBeneficiaria = $this->_sessao['beneficiaria'];

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $where = array(
            'pv.id_Pessoa = ?' => $idBeneficiaria,
            'up.id_Perfil = ?' => 2,
            'up.st_Usuario_Perfil = ?' => 'A',
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16
        );

        // Dados do responsável da beneficiaria
        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);
        $i = 0;
        $ativos = 0;
        foreach ($responsavel as $re) {

            if ($re->ST_PESSOA_VINCULADA == 'A') {
                $ativos++;
            }

            $dadosResponsavel[$i]['idResponsavel'] = $re->idPessoaVinculada;
            $dadosResponsavel[$i]['nmResponsavel'] = $re->nmPessoaFisica;
            $dadosResponsavel[$i]['nrCpfResponsavel'] = addMascara($re->nrCpf, 'cpf');
            $dadosResponsavel[$i]['cargoResponsavel'] = $re->nmCbo;
            $dadosResponsavel[$i]['cdCbo'] = $re->cdCbo;
            $dadosResponsavel[$i]['stAtivo'] = $re->ST_PESSOA_VINCULADA;

            // Email do responsável da operadora
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $re->idPessoaVinculada));

            $listaEmails = array();
            if (count($emails) > 0) {

                $e = 0;
                foreach ($emails as $em) {
                    $listaEmails[$e]['dsEmail'] = $em->dsEmail;
                    $e++;
                }
            }
            $dadosResponsavel[$i]['emailsResponsavel'] = $listaEmails;

            // Telefones do responsável da operadora
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));
            $listaTelefones = array();

            if (count($telefones) > 0) {

                $t = 0;
                foreach ($telefones as $tel) {
                    if ($tel->idTipoTelefone == 2) {
                        $listaTelefones[$t]['idTipoTelefone'] = 2;
                        $listaTelefones[$t]['TelResponsavel'] = 'Tel: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }
                    if ($tel->idTipoTelefone == 4) {
                        $listaTelefones[$t]['idTipoTelefone'] = 4;
                        $listaTelefones[$t]['FaxResponsavel'] = 'Fax: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }

                    $t++;
                }
            }

            $dadosResponsavel[$i]['telefonesResponsavel'] = $listaTelefones;

            $i++;
        }

        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('responsaveis', $dadosResponsavel);
        $this->view->assign('qtdAtivos', $ativos);
    }

    public function editarResponsavelAction()
    {
        $idResponsavel = $this->getRequest()->getParam('id');
        $dadosResponsavel = array();
        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $urlArquivo = null;

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $where = array(
            'pv.ID_PESSOA = ?' => $idBeneficiaria,
            'pv.ID_PESSOA_VINCULADA = ?' => $idResponsavel,
        );

        // Dados do responsável
        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        if (count($responsavel) > 0) {

            $dadosResponsavel['idResponsavel'] = $responsavel[0]->idPessoaVinculada;
            $dadosResponsavel['nmResponsavel'] = $responsavel[0]->nmPessoaFisica;
            $dadosResponsavel['nrCpfResponsavel'] = addMascara($responsavel[0]->nrCpf, 'cpf');
            $dadosResponsavel['cargoResponsavel'] = $responsavel[0]->nmCbo;
            $dadosResponsavel['cdCbo'] = $responsavel[0]->cdCbo;
            $dadosResponsavel['stAtivo'] = $responsavel[0]->ST_PESSOA_VINCULADA;

            // Email do responsável da operadora
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $responsavel[0]->idPessoaVinculada));

            $listaEmails = array();
            if (count($emails) > 0) {
                $e = 0;
                foreach ($emails as $em) {
                    $listaEmails[$e]['idEmail'] = $em->ID_EMAIL;
                    $listaEmails[$e]['dsEmail'] = $em->dsEmail;
                    $e++;
                }
            }

            $dadosResponsavel['emailsResponsavel'] = $listaEmails;

            // Telefones do responsável da operadora
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $responsavel[0]->idPessoaVinculada));
            $listaTelefones = array();

            if (count($telefones) > 0) {
                $t = 0;
                foreach ($telefones as $tel) {
                    if ($tel->idTipoTelefone == 2) {
                        $listaTelefones[$t]['idTelefone'] = $tel->idTelefone;
                        $listaTelefones[$t]['idTipoTelefone'] = 2;
                        $listaTelefones[$t]['TelResponsavel'] = 'Tel: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }
                    if ($tel->idTipoTelefone == 4) {
                        $listaTelefones[$t]['idTelefone'] = $tel->idTelefone;
                        $listaTelefones[$t]['idTipoTelefone'] = 4;
                        $listaTelefones[$t]['FaxResponsavel'] = 'Fax: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }
                    $t++;
                }
            }

            $dadosResponsavel['telefonesResponsavel'] = $listaTelefones;

            $where = array();
            $where['ID_BENEFICIARIA = ?'] = $idBeneficiaria;
            $where['ID_RESPONSAVEL = ?'] = $idResponsavel;

            $modelArquivoBeneficiaria = new Application_Model_ArquivoBeneficiaria();
            $arquivoAtual = $modelArquivoBeneficiaria->select($where, "DT_UPLOAD_ARQUIVO DESC", 1);

            if (count($arquivoAtual) > 0) {
                $arquivo = $arquivoAtual[0]['DS_CAMINHO_ARQUIVO'];
                $urlArquivo = $this->view->url(array('module' => 'beneficiaria',
                    'controller' => 'index',
                    'action' => 'abrir-arquivo',
                    'arquivo' => $arquivo));
            }
        } else {
            parent::message('Erro ao localizar o responsável.', '/beneficiaria/index/responsavel', 'error');
        }

        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('responsavel', $dadosResponsavel);
        $this->view->assign('idBeneficiaria', $idBeneficiaria);
        $this->view->assign('urlArquivo', $urlArquivo);
    }

    public function atualizarDadosResponsavelAction()
    {
        $idResponsavel = $this->_request->getParam('idResponsavel');
        $idBeneficiaria = $this->_request->getParam('idBeneficiaria');
        $CDCBO = $this->_request->getParam('CDCBO');

        $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();

        try {
            $where = array();
            $where['ID_BENEFICIARIA = ?'] = $idBeneficiaria;
            $where['ID_RESPONSAVEL = ?'] = $idResponsavel;

            $modelArquivoBeneficiaria = new Application_Model_ArquivoBeneficiaria();
            $arquivoAtual = $modelArquivoBeneficiaria->select($where, "DT_UPLOAD_ARQUIVO DESC", 1);

            if (count($arquivoAtual) == 0 && $_FILES['ANEXO_11']['error'] == 4) {
                throw new Exception("Procuração representante da empresa não enviado!", 500);
            }

            foreach ($_FILES as $k => $v) {
                if ($_FILES[$k]['error'] == 0) {
                    if ($_FILES[$k]["size"] >= 5242880) {
                        throw new Exception("Tamanho máximo do arquivo deve ser de 5mb!", 500);
                    }

                    if (strpos($_FILES[$k]['type'], 'pdf') === false) {
                        throw new Exception("Apenas aquivos no formato PDF são válidos!", 500);
                    }
                } else {
                    if ($_FILES[$k]['error'] != 4) {
                        throw new Exception("Falha no envio de documento!", 500);
                    }
                }
            }

            if ($CDCBO) {
                // apaga o que for dessa empresa e responsável
                $where = array(
                    'ID_PESSOA_FISICA = ?' => $idResponsavel,
                    'ID_PESSOA_JURIDICA = ?' => $idBeneficiaria
                );

                $modelCBOPessoaFisica->apagar($where);

                $Cols = array(
                    'CD_CBO' => $CDCBO,
                    'ID_PESSOA_FISICA' => $idResponsavel,
                    'ID_PESSOA_JURIDICA' => $idBeneficiaria
                );

                $modelCBOPessoaFisica->insert($Cols);
            }

            $uploaddir = defined('UPLOAD_DIR') ? UPLOAD_DIR : "/var/arquivos/arquivos-valecultura/";

            foreach ($_FILES as $k => $v) {
                if ($_FILES[$k]['error'] == 0) {
                    $time = time();
                    $mnArquivo = "{$idBeneficiaria}_{$k}_{$idResponsavel}_{$time}.pdf";
                    $uploadfile = $uploaddir . $mnArquivo;
                    $dsArquivo = $this->getRequest()->getParam("{$k}_NOME_{$idResponsavel}");

                    $Cols = array(
                        'ID_BENEFICIARIA' => $idBeneficiaria,
                        'DS_CAMINHO_ARQUIVO' => $mnArquivo,
                        'DS_ARQUIVO' => $dsArquivo,
                        'ID_RESPONSAVEL' => $idResponsavel,
                    );

                    if (move_uploaded_file($_FILES[$k]['tmp_name'], $uploadfile)) {
                        $modelArquivoBeneficiaria->insert($Cols);
                    } else {
                        throw new Exception("Erro ao salvar a Procuração representante da empresa!", 500);
                    }
                }
            }


            parent::message('Cargo atualizado com sucesso!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'confirm');

        } catch (Exception $exc) {
            $msg = $exc->getCode() == 500 ? $exc->getMessage() : 'Erro ao atualizar o cargo!';
            parent::message($msg, 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
        }
    }

    public function addTelefoneResponsavelAction()
    {

        $modelTelefone = new Application_Model_Telefone();
        $modelDDD = new Application_Model_DDD();

        $idResponsavel = $this->_request->getParam('idResponsavel');
        $dddFone = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 0, 2);
        $fone = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 2);

        if ($dddFone) {

            $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $dddFone));
            if (count($verificaDDD) == 0) {
                parent::message('DDD inv&aacute;lido!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
            }
        }

        try {

            if ($fone) {
                $where = array(
                    'ID_PESSOA = ?' => $idResponsavel,
                    'SG_PAIS = ?' => 'BRA',
                    'NR_TELEFONE = ?' => $fone,
                    'ID_TIPO_TELEFONE = ?' => 2,
                    'CD_DDD = ?' => $dddFone
                );
                $existeFone = $modelTelefone->select($where);

                if (count($existeFone) == 0) {
                    $Cols = array(
                        'ID_PESSOA' => $idResponsavel,
                        'SG_PAIS' => 'BRA',
                        'NR_TELEFONE' => $fone,
                        'ID_TIPO_TELEFONE' => 2,
                        'CD_DDD' => $dddFone
                    );
                    $modelTelefone->insert($Cols);
                } else {
                    parent::message($this->getRequest()->getParam('TelResponsavel') . '  já está cadastrado!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
                }
            }

            parent::message('Telefone adicionado com sucesso!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao adicionar o telefone!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
        }
    }

    public function delTelefoneResponsavelAction()
    {

        $modelTelefone = new Application_Model_Telefone();
        $idTelefone = $this->_request->getParam('idTelefone');
        $idResponsavel = $this->_request->getParam('idResponsavel');

        try {

            if ($idTelefone) {
                $modelTelefone->delete($idTelefone);
            }

            parent::message('Telefone excluído com sucesso!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao excluir o telefone!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
        }
    }

    public function addEmailResponsavelAction()
    {

        $modelEmail = new Application_Model_Email();
        $idResponsavel = $this->_request->getParam('idResponsavel');
        $email = trim($this->getRequest()->getParam('emailResponsavel'));

        // Faz a verificação usando a função
        if (!validaEmail($email)) {
            parent::message('E-mail inválido!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
        }


        try {

            if ($email) {
                $where = array(
                    'ID_PESSOA = ?' => $idResponsavel,
                    'DS_EMAIL = ?' => $email,
                    'ID_TIPO_EMAIL = ?' => 2,
                    'ST_EMAIL_PRINCIPAL = ?' => 'N'
                );

                $existeEmail = $modelEmail->select($where);

                if (count($existeEmail) == 0) {
                    $Cols = array(
                        'ID_PESSOA' => $idResponsavel,
                        'DS_EMAIL' => $email,
                        'ID_TIPO_EMAIL' => 2,
                        'ST_EMAIL_PRINCIPAL' => 'N'
                    );
                    $modelEmail->insert($Cols);
                } else {
                    parent::message($email . '  já está cadastrado!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
                }

            }

            parent::message('Email adicionado com sucesso!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao adicionar o email!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
        }
    }

    public function delEmailResponsavelAction()
    {

        $modelEmail = new Application_Model_Email();
        $idResponsavel = $this->_request->getParam('idResponsavel');
        $idEmail = $this->getRequest()->getParam('idEmail');

        try {

            if ($idEmail) {
                $modelEmail->delete($idEmail);
            }

            parent::message('Email excluído com sucesso!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao excluir o email!', 'beneficiaria/index/editar-responsavel/id/' . $idResponsavel, 'error');
        }
    }

    public function responsavelUpdateAction()
    {

    }

    public function faixaSalarialAction()
    {

        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $dadosFaixas = array();
        $modelFaixaSalarialBeneficiaria = new Application_Model_FaixaSalarialBeneficiaria();
        $modelTipoFaixaSalarial = new Application_Model_TipoFaixaSalarial();
        $tipoFaixas = $modelTipoFaixaSalarial->select();

        $i = 0;
        foreach ($tipoFaixas as $tf) {
            $dadosFaixas[$i]['idTipoFaixaSalarial'] = $tf->ID_TIPO_FAIXA_SALARIAL;
            $dadosFaixas[$i]['dsTipoFaixaSalarial'] = $tf->DS_TIPO_FAIXA_SALARIAL;
            $dadosFaixas[$i]['nrPercentualDesconto'] = $tf->NR_PERCENTUAL_DESCONTO;
            $dadosFaixas[$i]['siTipoFaixaSalarial'] = $tf->ST_TIPO_FAIXA_SALARIAL;
            $dadosFaixas[$i]['qtTrabalhadorFaixaSalarial'] = '0';

            $where = array('ID_BENEFICIARIA = ?' => $idBeneficiaria, 'ID_TIPO_FAIXA_SALARIAL = ?' => $tf->ID_TIPO_FAIXA_SALARIAL);
            $existe = $modelFaixaSalarialBeneficiaria->select($where);

            if (count($existe) > 0) {
                $dadosFaixas[$i]['qtTrabalhadorFaixaSalarial'] = $existe[0]->QT_TRABALHADOR_FAIXA_SALARIAL;
            }

            $i++;
        }

        $this->view->assign('dadosFaixas', $dadosFaixas);
    }

    public function alterarFaixaSalarialAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $msg = array();
        $msg['msg'] = 'error';

        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $idTipoFaixaSalarial = $this->_request->getParam('idTipoFaixaSalarial');
        $qtTrabalhadorFaixaSalarial = $this->_request->getParam('qtTrabalhadorFaixaSalarial');

        $modelFaixaSalarialBeneficiaria = new Application_Model_FaixaSalarialBeneficiaria();
        $modelSituacao = new Application_Model_Situacao();

        try {

            $where = array('ID_BENEFICIARIA = ?' => $idBeneficiaria, 'ID_TIPO_FAIXA_SALARIAL = ?' => $idTipoFaixaSalarial);
            $existe = $modelFaixaSalarialBeneficiaria->select($where);
            if (count($existe) > 0) {
                // Atualiza
                $atualiza = $modelFaixaSalarialBeneficiaria->update(array('QT_TRABALHADOR_FAIXA_SALARIAL' => $qtTrabalhadorFaixaSalarial), $idBeneficiaria, $idTipoFaixaSalarial);

            } else {
                $dadosFaixa = array(
                    'ID_BENEFICIARIA' => $idBeneficiaria,
                    'ID_TIPO_FAIXA_SALARIAL' => $idTipoFaixaSalarial,
                    'QT_TRABALHADOR_FAIXA_SALARIAL' => $qtTrabalhadorFaixaSalarial
                );
                $atualiza = $modelFaixaSalarialBeneficiaria->insert($dadosFaixa);
            }

            if ($atualiza) {

                if ($this->_sessao["PerfilGeral"] != 'A') {

                    $dadosSituacao = array(
                        'ID_PESSOA' => $idBeneficiaria,
                        'DS_JUSTIFICATIVA' => 'Alteração das faixas salariais.',
                        'ID_USUARIO' => $this->_sessao['idUsuario'],
                        'TP_ENTIDADE_VALE_CULTURA' => 'B',
                        'ID_TIPO_SITUACAO' => 1
                    );

                    $modelSituacao->insert($dadosSituacao);
                }

                $msg['msg'] = 'confirm';
                $msg['id'] = $qtTrabalhadorFaixaSalarial;
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

        echo json_encode($msg);
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
                parent::message('Informe a senha atual', '/beneficiadora/index/alterarsenha/', 'error');
            }
            if (!$NOVA_SENHA) {
                parent::message('Informe a nova senha', '/beneficiaria/index/alterarsenha/', 'error');
            }
            if ($NOVA_SENHA != $NOVA_SENHA_CONFIRMA) {
                parent::message('Senha de confirmaÃ§Ã£o incorreta', '/beneficiaria/index/alterarsenha/', 'error');
            }

            //VALIDA SENHA ATUAL
            $where = array(
                'id_Usuario = ?' => $this->_sessao['idUsuario'],
                'ds_Senha = ?' => md5($SENHA_ATUAL)
            );

            $recuperaUsuario = $modelUsuario->select($where);
            if (count($recuperaUsuario) > 0) {
                $cols = array(
                    'ds_Senha' => md5($NOVA_SENHA)
                );
                if ($modelUsuario->update($cols, $this->_sessao['idUsuario'])) {
                    parent::message('Senha atualizada com sucesso', '/beneficiaria/index/alterarsenha/', 'confirm');
                }
            } else {
                parent::message('Senha atual incorreta', '/beneficiaria/index/alterarsenha/', 'error');
            }
        }
    }


    function validarAcessoBeneficiadora($idBeneficiaria, $idPessoa)
    {
        $retorno = false;
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $where = array(
            'ID_PESSOA = ?' => $idBeneficiaria,
            'ID_PESSOA_VINCULADA = ?' => $idPessoa,
            'ST_PESSOA_VINCULADA = ?' => 'A'
        );
        $existeVinculoAtivo = $modelPessoaVinculada->select($where);

        if (count($existeVinculoAtivo) > 0) {
            $retorno = true;
        }

        return $retorno;

    }

    // ativação dos responsáveis das Beneficiárias
    public function ativacaoResponsavelAction()
    {

        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $idResponsavel = $this->getRequest()->getParam('id');
        $ativacao = $this->getRequest()->getParam('ativar');
        $tipoVinculo = 'A';
        $msg = 'Ativado com sucesso!';

        if ($ativacao == 'N') {
            $tipoVinculo = 'I';
            $msg = 'Desativado com sucesso!';
        }

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();

        try {

            $modelPessoaVinculada->update(array('ST_PESSOA_VINCULADA' => $tipoVinculo), $idBeneficiaria, $idResponsavel);
            parent::message($msg, '/beneficiaria/index/responsavel/', 'confirm');

        } catch (Exception $ex) {
            parent::message('Ops, desculpe mas houve um erro na aplicação.', '/beneficiaria/index/responsavel/', 'error');
        }
    }

    // ativação dos responsáveis das Beneficiárias
    public function ativacaoNaoResponsavelAction()
    {
        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $idResponsavel = $this->getRequest()->getParam('id');
        $ativacao = $this->getRequest()->getParam('ativar');
        $tipoVinculo = 'A';
        $msg = 'Ativado com sucesso!';

        if ($ativacao == 'N') {
            $tipoVinculo = 'I';
            $msg = 'Desativado com sucesso!';
        }

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();

        try {

            $modelPessoaVinculada->update(array('ST_PESSOA_VINCULADA' => $tipoVinculo), $idBeneficiaria, $idResponsavel);
            parent::message($msg, '/beneficiaria/index/nao-responsavel/', 'confirm');

        } catch (Exception $ex) {
            parent::message('Ops, desculpe mas houve um erro na aplicação.', '/beneficiaria/index/nao-responsavel/', 'error');
        }
    }

    public function autorizarDivulgacaoMincAction()
    {
        $modelBeneficiaria = new Application_Model_Beneficiaria();

        $idBeneficiaria = $this->getRequest()->getParam('beneficiaria');
        $autorizacao = $this->getRequest()->getParam('autorizacao');

        if ($this->validarAcessoBeneficiadora($idBeneficiaria, $this->_sessao['idPessoa'])) {
            $where = array(
                'ID_BENEFICIARIA = ?' => $idBeneficiaria
            );
            $beneficiaria = $modelBeneficiaria->select($where);

            if ($autorizacao === "AUTORIZO") {
                $Cols = array(
                    'ST_AUTORIZA_MINC' => 1
                );

                $modelBeneficiaria->update($Cols, $idBeneficiaria);
                parent::message('Atualização realizada com sucesso!', '/minc/admin', 'confirm');
            } else if ($autorizacao === "NÃO AUTORIZO") {
                $Cols = array(
                    'ST_AUTORIZA_MINC' => 2
                );

                $modelBeneficiaria->update($Cols, $idBeneficiaria);
                parent::message('Divulgação não autorizada!', '/minc/admin', 'confirm');
            }
        } else {
            parent::message('Beneficiária não foi localizada!', '/minc/admin', 'error');
        }
    }

    public function abrirArquivoAction()
    {
        $idBeneficiaria = $this->_sessao['beneficiaria'];

        $uploaddir = defined('UPLOAD_DIR') ? UPLOAD_DIR : "/var/arquivos/arquivos-valecultura/";
        $arquivo = $this->getRequest()->getParam('arquivo');

        $beneficiariaArquivo = (int)substr($arquivo, 0, (strpos($arquivo, '_')));

        if ($beneficiariaArquivo == $idBeneficiaria) {
            if (file_exists($uploaddir . $arquivo)) {
                $len = filesize($uploaddir . $arquivo);
                header("Content-type: application/pdf");
                header("Content-Length: $len");
                header("Content-Disposition: inline; filename= $arquivo");
                $existente = readfile($uploaddir . $arquivo);
            } else {
                parent::message('Arquivo não encontrado', '/beneficiaria/index/dados-beneficiaria', 'error');
            }
        } else {
            parent::message('Acesso não permitido', '/beneficiaria/index/dados-beneficiaria', 'error');
        }
        die;
    }

    public function naoResponsavelAction()
    {

        $dadosResponsavel = array();
        $idBeneficiaria = $this->_sessao['beneficiaria'];

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $where = array(
            'pv.id_Pessoa = ?' => $idBeneficiaria,
            'up.id_Perfil = ?' => 2,
            'up.st_Usuario_Perfil = ?' => 'A',
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 13
        );

        // Dados do responsável da beneficiaria
        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);
        $i = 0;
        $ativos = 0;
        foreach ($responsavel as $re) {

            if ($re->ST_PESSOA_VINCULADA == 'A') {
                $ativos++;
            }

            $dadosResponsavel[$i]['idResponsavel'] = $re->idPessoaVinculada;
            $dadosResponsavel[$i]['nmResponsavel'] = $re->nmPessoaFisica;
            $dadosResponsavel[$i]['nrCpfResponsavel'] = addMascara($re->nrCpf, 'cpf');
            $dadosResponsavel[$i]['cargoResponsavel'] = $re->nmCbo;
            $dadosResponsavel[$i]['cdCbo'] = $re->cdCbo;
            $dadosResponsavel[$i]['stAtivo'] = $re->ST_PESSOA_VINCULADA;

            // Email do responsável da operadora
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $re->idPessoaVinculada));

            $listaEmails = array();
            if (count($emails) > 0) {

                $e = 0;
                foreach ($emails as $em) {
                    $listaEmails[$e]['dsEmail'] = $em->dsEmail;
                    $e++;
                }
            }
            $dadosResponsavel[$i]['emailsResponsavel'] = $listaEmails;

            // Telefones do responsável da operadora
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));
            $listaTelefones = array();

            if (count($telefones) > 0) {

                $t = 0;
                foreach ($telefones as $tel) {
                    if ($tel->idTipoTelefone == 2) {
                        $listaTelefones[$t]['idTipoTelefone'] = 2;
                        $listaTelefones[$t]['TelResponsavel'] = 'Tel: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }
                    if ($tel->idTipoTelefone == 4) {
                        $listaTelefones[$t]['idTipoTelefone'] = 4;
                        $listaTelefones[$t]['FaxResponsavel'] = 'Fax: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }

                    $t++;
                }
            }

            $dadosResponsavel[$i]['telefonesResponsavel'] = $listaTelefones;

            $i++;
        }

        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('responsaveis', $dadosResponsavel);
        $this->view->assign('qtdAtivos', $ativos);
    }

    public function novoNaoResponsavelAction()
    {
        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('idBeneficiaria', $idBeneficiaria);
    }

    public function salvarNaoResponsavelAction()
    {
        $idBeneficiaria = $this->_sessao['beneficiaria'];

        set_time_limit('120');

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelTelefone = new Application_Model_Telefone();
        $modelEmail = new Application_Model_Email();
        $modelUsuario = new Application_Model_Usuario();
        $modelUsuarioPerfil = new Application_Model_UsuarioPerfil();
        $modelSituacao = new Application_Model_Situacao();
        $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();
        $modelDDD = new Application_Model_DDD();

        //Recuperando form
        $IDPF = $this->getRequest()->getParam('IDPF');
        $NRCPF = str_replace('.', '', str_replace('-', '', $this->getRequest()->getParam('RESPONSAVEL_CPF')));
        $CDDDDFAX = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('FaxResponsavel')))))), 0, 2);
        $NRFAX = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('FaxResponsavel')))))), 2);
        $CDDDD = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 0, 2);
        $NRTELEFONE = (int)substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 2);
        $DSEMAIL = $this->getRequest()->getParam('emailResponsavel');
        $CDCBO = $this->getRequest()->getParam('CDCBO');


        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            // Validando Form
            if ($IDPF == '0') {
                throw new Exception('CPF não encontrado!', 500);
            }

            if (strlen($NRTELEFONE) < 8) {
                throw new Exception('Informe o telefone!', 500);
            }

            if ($CDDDD) {
                $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $CDDDD));
                if (count($verificaDDD) == 0) {
                    throw new Exception('DDD inválido!', 500);
                }
            }

            if (!$DSEMAIL) {
                throw new Exception('Informe o e-mail!', 500);
            }

            if (!validaEmail($DSEMAIL)) {
                throw new Exception('Email inválido!', 500);
            }

            // Vincular o responsável
            $idPessoaFisica = $IDPF;
            $idPessoaJuridica = $idBeneficiaria;

            //Verifica se já existe esse número cadastrado
            $where = array(
                'ID_PESSOA = ?' => $idPessoaFisica,
                'SG_PAIS = ?' => 'BRA',
                'NR_TELEFONE = ?' => $NRTELEFONE,
                'ID_TIPO_TELEFONE = ?' => 2,
                'CD_DDD = ?' => $CDDDD
            );

            $existeTelefone = $modelTelefone->select($where);

            if (count($existeTelefone) == 0) {
                //Inserindo na model Telefone
                $Cols = array(
                    'ID_PESSOA' => $idPessoaFisica,
                    'SG_PAIS' => 'BRA',
                    'NR_TELEFONE' => $NRTELEFONE,
                    'ID_TIPO_TELEFONE' => 2,
                    'CD_DDD' => $CDDDD
                );

                $modelTelefone->insert($Cols);
            }

            if (strlen($NRFAX) > 7) {
                //Verifica se já existe esse número cadastrado
                $where = array(
                    'ID_PESSOA = ?' => $idPessoaFisica,
                    'SG_PAIS = ?' => 'BRA',
                    'NR_TELEFONE = ?' => $NRFAX,
                    'ID_TIPO_TELEFONE = ?' => 4,
                    'CD_DDD = ?' => $CDDDDFAX
                );

                $existeFax = $modelTelefone->select($where);
                if (count($existeFax) == 0) {
                    //Inserindo na model Telefone
                    $Cols = array(
                        'ID_PESSOA' => $idPessoaFisica,
                        'SG_PAIS' => 'BRA',
                        'NR_TELEFONE' => $NRFAX,
                        'ID_TIPO_TELEFONE' => 4,
                        'CD_DDD' => $CDDDDFAX
                    );

                    $modelTelefone->insert($Cols);
                }
            }

            // Verificar se já existe o email
            $where = array(
                'ID_PESSOA = ?' => $idPessoaFisica,
                'DS_EMAIL = ?' => $DSEMAIL,
                'ID_TIPO_EMAIL = ?' => 2,
                'ST_EMAIL_PRINCIPAL = ?' => 'S'
            );

            $existeEmail = $modelEmail->select($where);

            if (count($existeEmail) == 0) {

                //Inserindo Email do responsavel
                $Cols = array(
                    'ID_PESSOA' => $idPessoaFisica,
                    'DS_EMAIL' => $DSEMAIL,
                    'ID_TIPO_EMAIL' => 2,
                    'ST_EMAIL_PRINCIPAL' => 'S'
                );

                $modelEmail->insert($Cols);

            }

            //Inserindo CBO do responsavel
            if ($CDCBO) {

                // Verifica se já existe esse registro para não duplicar
                $whereCDCBO = array(
                    'ID_PESSOA_FISICA = ?' => $idPessoaFisica,
                    'ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica,
                );

                $existeCDCBO = $modelCBOPessoaFisica->select($whereCDCBO);

                $Cols = array(
                    'ID_PESSOA_FISICA' => $idPessoaFisica,
                    'ID_PESSOA_JURIDICA' => $idPessoaJuridica,
                    'CD_CBO' => $CDCBO
                );

                if (count($existeCDCBO) == 0) {
                    $modelCBOPessoaFisica->insert($Cols);
                } else {
                    $modelCBOPessoaFisica->update($Cols, $existeCDCBO[0]['ID_PESSOA_FISICA_CBO']);
                }
            }

            //============== VINCULANDO EMPRESA E RESPONSAVEL ==================
            // Verifica se ja existe vinculo
            $where = array(
                'ID_PESSOA = ?' => $idPessoaJuridica,
                'ID_PESSOA_VINCULADA = ?' => $idPessoaFisica,
                'ID_TIPO_VINCULO_PESSOA = ?' => 13
            );

            $vinculo = $modelPessoaVinculada->select($where);

            if (count($vinculo) == 0) {
                $Cols = array(
                    'ID_PESSOA' => $idPessoaJuridica,
                    'ID_PESSOA_VINCULADA' => $idPessoaFisica,
                    'ID_TIPO_VINCULO_PESSOA' => 13
                );

                $modelPessoaVinculada->insert($Cols);
            }

            //==================== CRIANDO USUARIO =============================
            $where = array(
                'ID_PESSOA_FISICA = ?' => $idPessoaFisica
            );

            $usuario = $modelUsuario->select($where);

            if (count($usuario) > 0) {
                $idUsuario = $usuario[0]['ID_USUARIO'];
                $enviaEmail = false;
            } else {
                $geraID = $modelUsuario->criaId();
                $idUsuario = $geraID['idUsuario'];
                $senha = gerarSenha();

                $Cols = array(
                    'ID_USUARIO' => $idUsuario,
                    'DS_LOGIN' => $NRCPF,
                    'DS_SENHA' => md5($senha),
                    'ID_PESSOA_FISICA' => $idPessoaFisica
                );

                $modelUsuario->insert($Cols);
                $enviaEmail = true;
            }

            //Verifica se usuario já tem o perfil
            $where = array(
                'ID_USUARIO = ?' => $idUsuario,
                'ID_PERFIL   = ?' => 2
            );

            $usuarioPerfil = $modelUsuarioPerfil->select($where);
            if (count($usuarioPerfil) < 1) {
                $Cols = array(
                    'ID_USUARIO' => $idUsuario,
                    'ID_PERFIL' => 2
                );

                $modelUsuarioPerfil->insert($Cols);
            }

            if ($this->_sessao["PerfilGeral"] != 'A') {

                //Cria Situação para a Operadora
                $Cols = array(
                    'ID_PESSOA' => $idPessoaJuridica,
                    'ID_USUARIO' => $idUsuario,
                    'ID_TIPO_SITUACAO' => 1,
                    'TP_ENTIDADE_VALE_CULTURA' => 'B',
                    'DS_JUSTIFICATIVA' => 'Cadastro realizado'
                );

                $modelSituacao->insert($Cols);
            }

            if ($enviaEmail) {
                $links = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('link');

                $htmlEmail = emailSenhaHTML();
                $htmlEmail = str_replace('#PERFIL#', 'Operadora', $htmlEmail);
                $htmlEmail = str_replace('#URL#', $links['vale-cultura'], $htmlEmail);
                $htmlEmail = str_replace('#EMAIL#', $links['email-vale-cultura'], $htmlEmail);
                $htmlEmail = str_replace('#Senha#', $senha, $htmlEmail);
//                $enviarEmail = $modelEmail->enviarEmail($DSEMAIL, 'Acesso ao sistema Vale Cultura', $htmlEmail);
            }

            $db->commit();
            parent::message('Não Responsável cadastrado com sucesso!', '/beneficiaria/index/nao-responsavel', 'confirm');

        } catch (Exception $exc) {
            $mgs = $exc->getCode() == 500 ? $exc->getMessage() : 'Erro ao cadastrar o não responsável.';
            $db->rollBack();
            parent::message($mgs, '/beneficiaria/index/novo-nao-responsavel', 'error');
        }
    }

    public function editarNaoResponsavelAction()
    {
        $idResponsavel = $this->getRequest()->getParam('id');
        $dadosResponsavel = array();
        $idBeneficiaria = $this->_sessao['beneficiaria'];
        $urlArquivo = null;

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $where = array(
            'pv.ID_PESSOA = ?' => $idBeneficiaria,
            'pv.ID_PESSOA_VINCULADA = ?' => $idResponsavel,
        );

        // Dados do responsável
        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        if (count($responsavel) > 0) {

            $dadosResponsavel['idResponsavel'] = $responsavel[0]->idPessoaVinculada;
            $dadosResponsavel['nmResponsavel'] = $responsavel[0]->nmPessoaFisica;
            $dadosResponsavel['nrCpfResponsavel'] = addMascara($responsavel[0]->nrCpf, 'cpf');
            $dadosResponsavel['cargoResponsavel'] = $responsavel[0]->nmCbo;
            $dadosResponsavel['cdCbo'] = $responsavel[0]->cdCbo;
            $dadosResponsavel['stAtivo'] = $responsavel[0]->ST_PESSOA_VINCULADA;

            // Email do responsável da operadora
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $responsavel[0]->idPessoaVinculada));

            $listaEmails = array();
            if (count($emails) > 0) {
                $e = 0;
                foreach ($emails as $em) {
                    $listaEmails[$e]['idEmail'] = $em->ID_EMAIL;
                    $listaEmails[$e]['dsEmail'] = $em->dsEmail;
                    $e++;
                }
            }

            $dadosResponsavel['emailsResponsavel'] = $listaEmails;

            // Telefones do responsável da operadora
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $responsavel[0]->idPessoaVinculada));
            $listaTelefones = array();

            if (count($telefones) > 0) {
                $t = 0;
                foreach ($telefones as $tel) {
                    if ($tel->idTipoTelefone == 2) {
                        $listaTelefones[$t]['idTelefone'] = $tel->idTelefone;
                        $listaTelefones[$t]['idTipoTelefone'] = 2;
                        $listaTelefones[$t]['TelResponsavel'] = 'Tel: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }
                    if ($tel->idTipoTelefone == 4) {
                        $listaTelefones[$t]['idTelefone'] = $tel->idTelefone;
                        $listaTelefones[$t]['idTipoTelefone'] = 4;
                        $listaTelefones[$t]['FaxResponsavel'] = 'Fax: (' . $tel->cdDDD . ') ' . $tel->nrTelefone;
                    }
                    $t++;
                }
            }

            $dadosResponsavel['telefonesResponsavel'] = $listaTelefones;

            $where = array();
            $where['ID_BENEFICIARIA = ?'] = $idBeneficiaria;
            $where['ID_RESPONSAVEL = ?'] = $idResponsavel;
        } else {
            parent::message('Erro ao localizar o responsável.', '/beneficiaria/index/responsavel', 'error');
        }

        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('responsavel', $dadosResponsavel);
        $this->view->assign('idBeneficiaria', $idBeneficiaria);
    }

    public function atualizarDadosNaoResponsavelAction()
    {
        $idResponsavel = $this->_request->getParam('idResponsavel');
        $idBeneficiaria = $this->_request->getParam('idBeneficiaria');
        $CDCBO = $this->_request->getParam('CDCBO');

        $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();

        try {
            $where = array();
            $where['ID_BENEFICIARIA = ?'] = $idBeneficiaria;
            $where['ID_RESPONSAVEL = ?'] = $idResponsavel;

            if ($CDCBO) {
                // apaga o que for dessa empresa e responsável
                $where = array(
                    'ID_PESSOA_FISICA = ?' => $idResponsavel,
                    'ID_PESSOA_JURIDICA = ?' => $idBeneficiaria
                );

                $modelCBOPessoaFisica->apagar($where);

                $Cols = array(
                    'CD_CBO' => $CDCBO,
                    'ID_PESSOA_FISICA' => $idResponsavel,
                    'ID_PESSOA_JURIDICA' => $idBeneficiaria
                );

                $modelCBOPessoaFisica->insert($Cols);
            }

            parent::message('Cargo atualizado com sucesso!', 'beneficiaria/index/editar-nao-responsavel/id/' . $idResponsavel, 'confirm');

        } catch (Exception $exc) {
            $msg = $exc->getCode() == 500 ? $exc->getMessage() : 'Erro ao atualizar o cargo!';
            parent::message($msg, 'beneficiaria/index/editar-nao-responsavel/id/' . $idResponsavel, 'error');
        }
    }
}