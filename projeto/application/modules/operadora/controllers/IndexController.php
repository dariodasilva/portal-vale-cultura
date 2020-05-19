<?php

include_once 'GenericController.php';

class Operadora_IndexController extends GenericController {

    private $session;

    public function init() {

        // Layout Padr�o
        $this->view->layout()->setLayout('layout');

        // T�tulo
        $this->view->assign('titulo', 'Operadora');

        parent::autenticar(array('R','A'));

        $this->view->assign('admin', false);
        if ($this->_sessao["PerfilGeral"] == 'A') {
            $this->view->assign('admin', true);
        }

        parent::init();

    }

    public function indexAction() {

        $operadoraSimular    = $this->getRequest()->getParam('operadora');

        $session = new Zend_Session_Namespace('user');
        $sessao = $this->_sessao;

        if ($this->_sessao["PerfilGeral"] == 'A') {
            $sessao["operadora"]    = $operadoraSimular;
            $session->usuario       = $sessao;
            $this->_redirect('/operadora/index/dadosoperadora');
        }else{

            if($this->validarAcessoOperadora($operadoraSimular, $sessao["idPessoa"])){
               $sessao["operadora"]     = $operadoraSimular;
               $session->usuario        = $sessao;
               $this->_redirect('/operadora/index/dadosoperadora');
            }else{
               $sessao["operadora"]     = '';
               $session->usuario        = $sessao;
                parent::message('Operadora n�o foi localizada!', '/minc/admin', 'error');
            }
        }


    }

    function validarAcessoOperadora($idOperadora, $idPessoa){
        $retorno = false;
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $where = array(
            'ID_PESSOA = ?'             => $idOperadora,
            'ID_PESSOA_VINCULADA = ?'   => $idPessoa,
            'ST_PESSOA_VINCULADA = ?'   => 'A'
        );
        $existeVinculoAtivo = $modelPessoaVinculada->select($where);

        if(count($existeVinculoAtivo) > 0){
            $retorno = true;
        }

        return $retorno;
    }

    public function dadosoperadoraAction() {

        $dadosOperadora = array();
        $idOperadora = $this->_sessao['operadora'];
        if (empty($idOperadora)) {
            parent::message('Operadora n�o foi localizada!', '/minc/index', 'error');
        }

        // Dados da operadora
        $modelOperadoras    = new Application_Model_Operadora();
        $modelSituacao      = new Application_Model_Situacao();

        $situacao = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idOperadora, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));

        if ($situacao[0]["idTipoSituacao"] != 2 && isset($situacao[0]["idTipoSituacao"])) {
            $this->view->bloqueiaForm = false;
        } else {
            $this->view->bloqueiaForm = true;
        }
        $operadora = $modelOperadoras->buscarDados(array('o.ID_OPERADORA = ?' => $idOperadora));

        $historico = $modelSituacao->listarSituacoes(array('ID_PESSOA = ?' => $idOperadora, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));

        foreach ($operadora as $op) {
            // tbOperadora
            $dadosOperadora['idOperadora']              = $op->idOperadora;
            $dadosOperadora['dtInscricao']              = $op->dtInscricao;
            $dadosOperadora['nrComprovanteInscricao']   = $op->nrComprovanteInscricao;
            $dadosOperadora['nrCertificado']            = $op->nrCertificado;
            $dadosOperadora['situacao']                 = $op->situacao;
            $dadosOperadora['inicioComercializacao']    = $op->dtInicioComercializacao;
            //tbPessoa
            $dadosOperadora['idpessoa']                 = $op->idPessoa;
            $dadosOperadora['dtregistro']               = $op->dtRegistro;
            //tbPessoaJuridica
            $dadosOperadora['nrCnpj']                   = addMascara($op->nrCnpj, 'cnpj');
            $dadosOperadora['nrInscricaoEstadual']      = $op->nrInscricaoEstadual;
            $dadosOperadora['nmRazaoSocial']            = $op->nmRazaoSocial;
            $dadosOperadora['nmFantasia']               = $op->nmFantasia;
			$dadosOperadora['dsSite']                   = $op->dsSite;
            $dadosOperadora['nrCei']                    = $op->nrCei;
            //tbNaturezaJuridica
            $dadosOperadora['cdNaturezaJuridica']       = $op->cdNaturezaJuridica;
            $dadosOperadora['dsNaturezaJuridica']       = $op->dsNaturezaJuridica;
            //tbEndereco
            $dadosOperadora['dsComplementoEndereco']    = $op->dsComplementoEndereco;
            $dadosOperadora['nrComplemento']            = $op->nrComplemento;
            //tbBairro
            $dadosOperadora['nmBairro']                 = $op->nmBairro;
            //tbLogradouro
            $dadosOperadora['logradouro']               = $op->nmLogradouro;
            $dadosOperadora['cep']                      = addMascara($op->nrCep, 'cep');
            $dadosOperadora['Pais']                     = $op->nmPais;
            $dadosOperadora['nmUf']                     = $op->nmUF;
            $dadosOperadora['sgUF']                     = $op->sgUF;
            $dadosOperadora['nmMunicipio']              = $op->nmMunicipio;
            $dadosOperadora['idMunicipio']              = $op->idMunicipio;
        }

        // Arquivos da operadora
        $modelArquivos = new Application_Model_ArquivoOperadora();
        $arquivos = $modelArquivos->buscarArquivos(array('ID_OPERADORA = ?' => $idOperadora));
        $this->view->assign('arquivosOperadora', $arquivos);

        // Email Institucional
        $emailInstitucional = array();
        $modelEmail = new Application_Model_Email();
        $email = $modelEmail->select(array('ID_TIPO_EMAIL = ?' => 2, 'ID_PESSOA = ?' => $idOperadora), 'DS_EMAIL', 1);
        if(count($email) > 0){
            $emailInstitucional = $email[0];
        }
        $this->view->assign('emailInstitucional', $emailInstitucional);

        // Envia as informa��es para a view
        $this->view->assign('operadora', $dadosOperadora);
        $this->view->assign('historico', $historico);
    }

    public function telefonesAction() {
        $idOperadora = $this->_sessao['operadora'];
        $modelTelefones = new Application_Model_Telefone();
        $listaTelefones = $modelTelefones->buscarTelefones(array('ID_PESSOA = ?' => $idOperadora));
        $this->view->assign('telefones', $listaTelefones);
        $this->view->assign('idOperadora', $idOperadora);
    }

    public function addTelefoneAction(){

        if ($_POST) {

            $modelTelefone  = new Application_Model_Telefone();
            $modelDDD       = new Application_Model_DDD();

            $idOperadora    = $this->_sessao['operadora'];
            $DDD            = $this->getRequest()->getParam('DDD');
            $TELEFONE       = $this->getRequest()->getParam('TELEFONE');
            $COMPLEMENTO    = $this->getRequest()->getParam('COMPLEMENTO');

            if($TELEFONE == ''){
                parent::message('O campo telefone � obrigat�rio!', '/operadora/index/telefones/operadora/'.$idOperadora, 'error');
            }

            if($DDD){
                $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $DDD));
                if(count($verificaDDD) < 1 ){
                    parent::message('DDD inv&aacute;lido!', '/operadora/index/telefones/operadora/'.$idOperadora, 'error');
                }
            }

            try {

                // Verificar se j� existe
                $where = array(
                    'ID_PESSOA = ?'         => $idOperadora,
                    'SG_PAIS = ?'           => 'BRA',
                    'NR_TELEFONE = ?'       => $TELEFONE,
                    'ID_TIPO_TELEFONE = ?'  => 7
                );

                if($DDD){
                    $where['CD_DDD = ?'] = $DDD;
                }else{
                    $where['CD_DDD ?'] = new Zend_Db_Expr('IS NULL');
                }

                $existeTelefone = $modelTelefone->select($where);

                if(count($existeTelefone) == 0){

                    $Cols = array(
                        'ID_PESSOA'         => $idOperadora,
                        'SG_PAIS'           => 'BRA',
                        'NR_TELEFONE'       => $TELEFONE,
                        'DS_TELEFONE'       => $COMPLEMENTO,
                        'ID_TIPO_TELEFONE'  => 7,
                        'CD_DDD'            => $DDD == '' ? new Zend_Db_Expr('NULL') : $DDD
                    );

                    $modelTelefone->insert($Cols);
                    parent::message('Telefone cadastrado com sucesso!', '/operadora/index/telefones/operadora/'.$idOperadora, 'confirm');
                }else{
                    parent::message('O Telefone informado j� est� cadastrado!', '/operadora/index/telefones/operadora/'.$idOperadora, 'error');
                }

            } catch (Exception $exc) {
                xd($exc->getMessage());
                parent::message('Erro ao cadastrar o telefone!', '/operadora/index/telefones/operadora/'.$idOperadora, 'error');
            }

        }else{
            parent::message('Dados n�o encontrados!', '/operadora/index/telefones/operadora/'.$idOperadora, 'alert');
        }

    }

    public function delTelefoneAction(){

        $modelTelefone  = new Application_Model_Telefone();

        $idOperadora    = $this->_sessao['operadora'];
        $IDTELEFONE     = $this->getRequest()->getParam('IDTELEFONE');

        if($IDTELEFONE == ''){
            parent::message('O campo telefone � obrigat�rio!', '/operadora/index/telefones/operadora/'.$idOperadora, 'alert');
        }

        try {
            $modelTelefone->delete($IDTELEFONE);
            parent::message('Telefone exclu�do com sucesso!', '/operadora/index/telefones/operadora/'.$idOperadora, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao excluir o telefone!', '/operadora/index/telefones/operadora/'.$idOperadora, 'error');
        }

    }

    public function atualizarDadosOperadoraAction() {

        $idOperadora = $this->_sessao['operadora'];

        if ($_POST) {

            $modelEndereco      = new Application_Model_Endereco();
            $modelLogradouro    = new Application_Model_Logradouro();
            $modelEmail         = new Application_Model_Email();

            $NRCEP                  = str_replace('-', '', $this->getRequest()->getParam('cep'));
            $DSCOMPLEMENTOENDERECO  = trim($this->getRequest()->getParam('dsComplementoEndereco'));
            $NRCOMPLEMENTO          = trim($this->getRequest()->getParam('nrComplemento'));
            $DSLOGRAENDERECO        = trim($this->getRequest()->getParam('logradouro'));
            $IDEMAILINSTITUCIONAL   = $this->getRequest()->getParam('ID_EMAIL_INSTITUCIONAL');
            $EMAILINSTITUCIONAL     = trim($this->getRequest()->getParam('EMAIL_INSTITUCIONAL'));
            $IDBAIRRO               = $this->getRequest()->getParam('nmBairro');

            $logradouro = $modelLogradouro->selectEndereco(array('NR_CEP = ?' => "" . $NRCEP . ""));
            if (count($logradouro) < 1 || strlen($NRCEP) != 8) {
                parent::message('CEP inv�lido', '/operadora/index/dadosoperadora/', 'error');
            } else {
                $IDLOGRADOURO = $logradouro[0]['ID_LOGRADOURO'];
                $STLOGRADOURO = $logradouro[0]['ST_LOGRADOURO'];
                $IDBAIRRO = (!empty($IDBAIRRO)) ? $IDBAIRRO : $logradouro[0]['ID_BAIRRO_INICIO'];
            }

            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();

            $idPessoaJuridica = $idOperadora;

            try {

                //Inserindo na model endereco
                $logradouro = $modelLogradouro->selectEndereco(array('NR_CEP = ?' => "" . $NRCEP . ""));
                $Cols = array(
                    'DS_COMPLEMENTO_ENDERECO'   => $DSCOMPLEMENTOENDERECO,
                    'ID_LOGRADOURO'             => $IDLOGRADOURO,
                    'NR_COMPLEMENTO'            => $NRCOMPLEMENTO,
                    'DS_LOGRA_ENDERECO'         => $DSLOGRAENDERECO,
                    'ID_SERVICO'                => 1
                );

                if ($STLOGRADOURO == 0) {
                    $Cols['DS_LOGRA_ENDERECO']  = $DSLOGRAENDERECO;
                    $Cols['ID_BAIRRO']          = $IDBAIRRO;
                } else {
                    $Cols['ID_BAIRRO']          = $IDBAIRRO;
                    $Cols['DS_BAIRRO_ENDERECO'] = $IDBAIRRO;
                }

                if(isset($EMAILINSTITUCIONAL) && !empty($EMAILINSTITUCIONAL)){


                    if(!validaEmail($EMAILINSTITUCIONAL)){
                        parent::message('O Email institucional � inv�lido!', '/operadora/index/dadosoperadora/', 'error');
                    }

                    $dadosEmail = array(
                        'ID_TIPO_EMAIL'         => 2,
                        'ID_PESSOA'             => $idPessoaJuridica,
                        'DS_EMAIL'              => $EMAILINSTITUCIONAL,
                        'ST_EMAIL_PRINCIPAL'    => 'S'
                    );


                    if(isset($IDEMAILINSTITUCIONAL) && !empty($IDEMAILINSTITUCIONAL)){
                        $modelEmail->update($dadosEmail, $IDEMAILINSTITUCIONAL);
                    }else{
                        $modelEmail->insert($dadosEmail);
                    }

                }

                $modelEndereco->update($Cols, array('ID_PESSOA = ?' => $idPessoaJuridica, 'CD_TIPO_ENDERECO = ?' => '01'));

                $db->commit();
                parent::message('Dados atualizados com sucesso', '/operadora/index/dadosoperadora/', 'confirm');

            } catch (Exception $exc) {
                xd($exc->getMessage());
                $db->rollBack();
                parent::message('Falha ao tentar atualizar dados', '/operadora/index/dadosoperadora/', 'error');
            }
        }
    }

    public function emitircertificadoAction() {

        $dadosOperadora = array();

        $textoCertificado = carregaHTMLCertificado();

        $idOperadora = $this->_sessao['operadora'];
        // Dados da operadora
        $modelOperadoras = new Application_Model_Operadora();
        $modelSituacao = new Application_Model_Situacao();
        $situacao = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idOperadora, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'O'));

        if (is_array($situacao[0])) {
            $idSituacao = isset($situacao[0]['idTipoSituacao']) ? $situacao[0]['idTipoSituacao'] : 1;
            $dsSituacao = isset($situacao[0]['dsTipoSituacao']) ? $situacao[0]['dsTipoSituacao'] : NULL;
        } else {
            $idSituacao = 1;
            $dsSituacao = isset($situacao[0]['dsTipoSituacao']) ? $situacao[0]['dsTipoSituacao'] : NULL;
        }

        if ($idSituacao == ID_SITUACAO_AUTORIZADO) {
            $this->getHelper('layout')->disableLayout();
            $operadora = $modelOperadoras->buscarDados(array('o.ID_OPERADORA = ?' => $idOperadora));

            $dadosOperadora = array();
            foreach ($operadora as $op) {
                // tbOperadora
                $dadosOperadora['ID_OPERADORA']             = $op->idOperadora;
                $dadosOperadora['DT_INSCRICAO']             = $op->dtInscricao;
                $dadosOperadora['NR_COMPROVANTE_INSCRICAO'] = $op->nrComprovanteInscricao;
                $dadosOperadora['NR_CERTIFICADO']           = $op->nrCertificado;
                //tbPessoa
                $dadosOperadora['ID_PESSOA']                = $op->idPessoa;
                $dadosOperadora['DT_REGISTRO']              = $op->dtRegistro;
                //tbPessoaJuridica
                $dadosOperadora['NR_CNPJ']                  = $op->nrCnpj;
                $dadosOperadora['NR_INSCRICAO_ESTADUAL']    = $op->nrInscricaoEstadual;
                $dadosOperadora['NM_RAZAO_SOCIAL']          = $op->nmRazaoSocial;
                $dadosOperadora['NM_FANTASIA']              = $op->nmFantasia;
                $dadosOperadora['DS_SITE']                  = $op->dsSite;
                $dadosOperadora['NR_CEI']                   = $op->nrCei;
                //tbNaturezaJuridica
                $dadosOperadora['CD_NATUREZA_JURIDICA ']    = $op->cdNaturezaJuridica;
                $dadosOperadora['DS_NATUREZA_JURIDICA ']    = $op->dsNaturezaJuridica;
                //tbEndereco
                $dadosOperadora['DS_COMPLEMENTO_ENDERECO']  = $op->dsComplementoEndereco;
                $dadosOperadora['NR_COMPLEMENTO']           = $op->nrComplemento;
                //tbBairro
                $dadosOperadora['NM_BAIRRO']                = $op->nmBairro;
                //tbLogradouro
                $dadosOperadora['logradouro']               = $op->nmLogradouro;
                $dadosOperadora['cep']                      = $op->nrCep;
                $dadosOperadora['Pais']                     = $op->nmPais;
                $dadosOperadora['Estado']                   = $op->nmUF;
                $dadosOperadora['Municipio']                = $op->nmMunicipio;
            }

            // Dados do respons�vel da operadora
            $modelPessoaVinculada = new Application_Model_PessoaVinculada();

            $where = array(
                'pv.id_Pessoa = ?'              => $idOperadora,
                'up.id_Perfil = ?'              => 3,
                'up.st_Usuario_Perfil = ?'      => 'A',
                'pv.ST_PESSOA_VINCULADA = ?'    => 'A',
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17
            );

            $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

            $txtResp = '';
            foreach ($responsavel as $re) {

                $txtResp .= '<table style="width: 100%">
                                <tr>
                                    <td align="center" width="400px">
                                        '.uc_latin1($re->nmPessoaFisica).'<br>
                                        Nome do Respons�vel pela Empresa junto ao Minist�rio da Cultura
                                    </td>
                                    <td align="center" width="400px">
                                        '.uc_latin1($re->nrCpf).'<br>
                                        CPF do Respons�vel pela Empresa junto ao Minist�rio da Cultura
                                    </td>
                                    <td align="center" width="400px">
                                        '.uc_latin1($re->nmCbo).'<br>
                                        Cargo do Respons�vel pela Empresa junto ao Minist�rio da Cultura
                                    </td>
                                </tr>
                            </table><br><br>';

            }

            $textoCertificado = str_replace('#DATA#', date('d/m/Y'), $textoCertificado);
            $textoCertificado = str_replace('#ANO_CERTIFICADO#', date('Y'), $textoCertificado);
            $textoCertificado = str_replace('#N_CERTIFICADO#', $dadosOperadora['NR_CERTIFICADO'], $textoCertificado);
            $textoCertificado = str_replace('#CNPJ#', addMascara($dadosOperadora['NR_CNPJ'], 'cnpj'), $textoCertificado);
            $textoCertificado = str_replace('#RAZAO#', uc_latin1($dadosOperadora['NM_RAZAO_SOCIAL']), $textoCertificado);
            $textoCertificado = str_replace('#FANTASIA#', uc_latin1($dadosOperadora['NM_FANTASIA']), $textoCertificado);
            $textoCertificado = str_replace('#ENDERECO#', uc_latin1($dadosOperadora['logradouro']), $textoCertificado);
            $textoCertificado = str_replace('#BAIRRO#', uc_latin1($dadosOperadora['NM_BAIRRO']), $textoCertificado);
            $textoCertificado = str_replace('#CEP#', addMascara($dadosOperadora['cep'], 'cep'), $textoCertificado);
            $textoCertificado = str_replace('#PAIS#', $dadosOperadora['Pais'], $textoCertificado);
            $textoCertificado = str_replace('#ESTADO#', uc_latin1($dadosOperadora['Estado']), $textoCertificado);
            $textoCertificado = str_replace('#MUNICIPIO#', uc_latin1($dadosOperadora['Municipio']), $textoCertificado);
            $textoCertificado = str_replace('#RESPONSAVEIS#', $txtResp, $textoCertificado);

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

    public function arquivosAction() {
        $idOperadora = $this->_sessao['operadora'];
        // Arquivos da operadora
        $modelArquivos = new Application_Model_ArquivoOperadora();
        $arquivos = $modelArquivos->buscarArquivos(array('ID_OPERADORA = ?' => $idOperadora));
        $this->view->assign('arquivosOperadora', $arquivos);
    }

    public function arquivosUploadAction() {
        $idOperadora = $this->_sessao['operadora'];
        $modelArquivoOperadora = new Application_Model_ArquivoOperadora;

        $uploaddir = defined('UPLOAD_DIR') ? UPLOAD_DIR : "/var/arquivos/arquivos-valecultura/";

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {

            foreach ($_FILES as $k => $v) {
                if ($k == 'ANEXO_1' || $k == 'ANEXO_2' || $k == 'ANEXO_3' || $k == 'ANEXO_4' || $k == 'ANEXO_5' || $k == 'ANEXO_6' || $k == 'ANEXO_7' || $k == 'ANEXO_8') {
                    if ($_FILES[$k]['error'] != 0) {
                        $ERROR['ERROR'] = 'Documento obrigat�rio n�o enviado';
                    }
                }
                if ($_FILES[$k]['error'] == 0) {

                    if ($_FILES[$k]["size"] >= 5242880) {
                        $ERROR['ERROR'] = 'Tamanho maximo do arquivo deve ser de 5mb';
                    }

                    if (strpos($_FILES[$k]['type'], 'pdf') === false) {
                        $ERROR['ERROR'] = 'Apenas aquivos no formato PDF s�o validos';
                    }
                } else {
                    if ($_FILES[$k]['error'] != 4) {
                        $ERROR['ERROR'] = 'Falha no envio de documento';
                    }
                }
            }

            if (count($ERROR) > 0) {
                parent::message($ERROR['ERROR'], '/operadora/index/arquivos/', 'error');
                return;
            }

            foreach ($_FILES as $k => $v) {
                if ($_FILES[$k]['error'] == 0) {
                    $dsArquivo = $this->getRequest()->getParam('ANEXO_NOME');
                    if (strpos($k, 'ANEXO') === false) {
                        $where = array();
                        $where['ID_ARQUIVO = ?'] = $k;
                        $where['ID_OPERADORA = ?'] = $idOperadora;
                        $arquivoAtual = $modelArquivoOperadora->select($where);
                        if (count($arquivoAtual) > 0) {
                            $mnArquivo = explode('\\', $arquivoAtual[0]["DS_CAMINHO_ARQUIVO"]);
                            $mnArquivo = $mnArquivo[count($mnArquivo) - 1];
                            $dsArquivo = ($dsArquivo) ? $dsArquivo : $arquivoAtual[0]["DS_ARQUIVO"];
                            $whereUpdate = array(
                                'ID_ARQUIVO = ?' => $arquivoAtual[0]["ID_ARQUIVO"]
                            );
                            $acao = 'update';
                        } else {
                            parent::message('Sem permiss�o para substitui��o', '/operadora/index/arquivos/', 'error');
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
                        parent::message('Erro ao salvar arquivo', '/operadora/index/arquivos/', 'error');
                        return;
                    }
                } else {
                    parent::message('Erro ao salvar arquivo', '/operadora/index/arquivos/', 'error');
                }
            }

            if ($this->_sessao["PerfilGeral"] != 'A') {

                // Atualiza o status
                $modelSituacao                  = new Application_Model_Situacao();
                $dadosSituacao = array(
                        'ID_PESSOA'                     => $idOperadora,
                        'DS_JUSTIFICATIVA'              => 'Arquivos enviados alterados.',
                        'ID_USUARIO'                    => $this->_sessao['idUsuario'],
                        'TP_ENTIDADE_VALE_CULTURA'      => 'O',
                        'ID_TIPO_SITUACAO'              => 1
                    );

                $modelSituacao->insert($dadosSituacao);

            }


            $db->commit();
            parent::message('Arquivo atualizado com sucesso!', '/operadora/index/arquivos/', 'confirm');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $db->rollBack();
            parent::message('Erro ao salvar arquivo', '/operadora/index/arquivos/', 'error');
        }
    }

    public function novoResponsavelAction() {
        $idOperadora = $this->_sessao['operadora'];
        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('idOperadora', $idOperadora);
    }

    public function salvarResponsavelAction() {

        $idOperadora = $this->_sessao['operadora'];

        set_time_limit('120');

        $modelPessoaVinculada   = new Application_Model_PessoaVinculada();
        $modelTelefone          = new Application_Model_Telefone();
        $modelEmail             = new Application_Model_Email();
        $modelUsuario           = new Application_Model_Usuario();
        $modelUsuarioPerfil     = new Application_Model_UsuarioPerfil();
        $modelSituacao          = new Application_Model_Situacao();
        $modelCBOPessoaFisica   = new Application_Model_CBOPessoaFisica();
        $modelDDD               = new Application_Model_DDD();

        //Recuperando form
        $IDPF           = $this->getRequest()->getParam('IDPF');
        $NRCPF          = str_replace('.', '', str_replace('-', '', $this->getRequest()->getParam('RESPONSAVEL_CPF')));
        $CDDDDFAX       = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('FaxResponsavel')))))), 0, 2);
        $NRFAX          = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('FaxResponsavel')))))), 2);
        $CDDDD          = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 0, 2);
        $NRTELEFONE     = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 2);
        $DSEMAIL        = $this->getRequest()->getParam('emailResponsavel');
        $CDCBO          = $this->getRequest()->getParam('CDCBO');

        // Validando Form
        if ($IDPF == '0') {
            parent::message('CPF n�o encontrado!', '/operadora/index/novo-responsavel', 'error');
        }

        if (strlen($NRTELEFONE) < 8) {
            parent::message('Informe o telefone', '/operadora/index/novo-responsavel', 'error');
        }

        if($CDDDD){
            $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $CDDDD));
            if(count($verificaDDD) == 0 ){
                parent::message('DDD inv&aacute;lido!', '/operadora/index/novo-responsavel', 'error');
            }
        }

        if (!$DSEMAIL) {
            parent::message('Informe o e-mail', '/operadora/index/novo-responsavel', 'error');
        }

        if (!validaEmail($DSEMAIL)) {
            parent::message('Email inv�lido!', '/operadora/index/novo-responsavel', 'error');
        }

//        x($this->getRequest()->getParams());

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {

            // Vincular o respons�vel
            $idPessoaFisica     = $IDPF;
            $idPessoaJuridica   = $idOperadora;

            //Verifica se j� existe esse n�mero cadastrado
            $where = array(
                'ID_PESSOA = ?'         => $idPessoaFisica,
                'SG_PAIS = ?'           => 'BRA',
                'NR_TELEFONE = ?'       => $NRTELEFONE,
                'ID_TIPO_TELEFONE = ?'  => 2,
                'CD_DDD = ?'            => $CDDDD
            );

            $existeTelefone = $modelTelefone->select($where);

            if(count($existeTelefone) == 0){
                //Inserindo na model Telefone
                $Cols = array(
                    'ID_PESSOA'         => $idPessoaFisica,
                    'SG_PAIS'           => 'BRA',
                    'NR_TELEFONE'       => $NRTELEFONE,
                    'ID_TIPO_TELEFONE'  => 2,
                    'CD_DDD'            => $CDDDD
                );

                $modelTelefone->insert($Cols);
            }

            if (strlen($NRFAX) > 7) {
                //Verifica se j� existe esse n�mero cadastrado
                $where = array(
                    'ID_PESSOA = ?'         => $idPessoaFisica,
                    'SG_PAIS = ?'           => 'BRA',
                    'NR_TELEFONE = ?'       => $NRFAX,
                    'ID_TIPO_TELEFONE = ?'  => 4,
                    'CD_DDD = ?'            => $CDDDDFAX
                );

                $existeFax = $modelTelefone->select($where);
                if(count($existeFax) == 0){
                    //Inserindo na model Telefone
                    $Cols = array(
                        'ID_PESSOA'         => $idPessoaFisica,
                        'SG_PAIS'           => 'BRA',
                        'NR_TELEFONE'       => $NRFAX,
                        'ID_TIPO_TELEFONE'  => 4,
                        'CD_DDD'            => $CDDDDFAX
                    );

                    $modelTelefone->insert($Cols);
                }
            }

            // Verificar se j� existe o email
            $where = array(
                'ID_PESSOA = ?'             => $idPessoaFisica,
                'DS_EMAIL = ?'              => $DSEMAIL,
                'ID_TIPO_EMAIL = ?'         => 2,
                'ST_EMAIL_PRINCIPAL = ?'    => 'S'
            );

            $existeEmail = $modelEmail->select($where);

            if(count($existeEmail) == 0){

                //Inserindo Email do responsavel
                $Cols = array(
                    'ID_PESSOA'             => $idPessoaFisica,
                    'DS_EMAIL'              => $DSEMAIL,
                    'ID_TIPO_EMAIL'         => 2,
                    'ST_EMAIL_PRINCIPAL'    => 'S'
                );

                $modelEmail->insert($Cols);

            }

            //Inserindo CBO do responsavel
            if ($CDCBO) {

                // Verifica se j� existe esse registro para n�o duplicar
                $whereCDCBO = array(
                        'ID_PESSOA_FISICA = ?'   => $idPessoaFisica,
                        'ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica,
                        'CD_CBO = ?'             => $CDCBO
                );

                $existeCDCBO = $modelCBOPessoaFisica->select($whereCDCBO);

                if(count($existeCDCBO) == 0){

                    $Cols = array(
                        'ID_PESSOA_FISICA'   => $idPessoaFisica,
                        'ID_PESSOA_JURIDICA' => $idPessoaJuridica,
                        'CD_CBO'             => $CDCBO
                    );

                    $modelCBOPessoaFisica->insert($Cols);
                }
            }

            //============== VINCULANDO EMPRESA E RESPONSAVEL ==================
            //Verifica se ja existe vinculo
            $where = array(
                'ID_PESSOA = ?'              => $idPessoaJuridica,
                'ID_PESSOA_VINCULADA = ?'    => $idPessoaFisica,
                'ID_TIPO_VINCULO_PESSOA = ?' => 17
            );

            $vinculo = $modelPessoaVinculada->select($where);

            if (count($vinculo) < 1) {
                $Cols = array(
                    'ID_PESSOA'                 => $idPessoaJuridica,
                    'ID_PESSOA_VINCULADA'       => $idPessoaFisica,
                    'ID_TIPO_VINCULO_PESSOA'    => 17
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
                    'ID_USUARIO'        => $idUsuario,
                    'DS_LOGIN'          => $NRCPF,
                    'DS_SENHA'          => md5($senha),
                    'ID_PESSOA_FISICA'  => $idPessoaFisica
                );

                $modelUsuario->insert($Cols);
                $enviaEmail = true;
            }

            //Verifica se usuario j� tem o perfil
            $where = array(
                'ID_USUARIO = ?'    => $idUsuario,
                'ID_PERFIL   = ?'   => 3
            );

            $usuarioPerfil = $modelUsuarioPerfil->select($where);
            if(count($usuarioPerfil) < 1){
                $Cols = array(
                    'ID_USUARIO'    => $idUsuario,
                    'ID_PERFIL'     => 3
                );

                $modelUsuarioPerfil->insert($Cols);
            }

            if ($this->_sessao["PerfilGeral"] != 'A') {

                //Cria Situa��o para a Operadora
                $Cols = array(
                    'ID_PESSOA'                 => $idPessoaJuridica,
                    'ID_USUARIO'                => $idUsuario,
                    'ID_TIPO_SITUACAO'          => 1,
                    'TP_ENTIDADE_VALE_CULTURA'  => 'O',
                    'DS_JUSTIFICATIVA'          => 'Cadastro realizado'
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
                $enviarEmail = $modelEmail->enviarEmail($DSEMAIL, 'Acesso ao sistema Vale Cultura', $htmlEmail);
            }

            $db->commit();
            parent::message('Respons�vel cadastrado com sucesso!', '/operadora/index/responsavel', 'confirm');

        } catch (Exception $exc) {
            $db->rollBack();
            xd($exc->getMessage());
            parent::message('Erro ao cadastrar o respons�vel.', '/operadora/index/novo-responsavel', 'error');
        }

    }

    public function responsavelAction() {

        $dadosResponsavel = array();
        $idOperadora = $this->_sessao['operadora'];

        // Dados do respons�vel da operadora
        $modelPessoaVinculada   = new Application_Model_PessoaVinculada();
        $modelEmail             = new Application_Model_Email();
        $modelTelefone          = new Application_Model_Telefone();

        $where = array(
            'pv.id_Pessoa = ?'                  => $idOperadora,
            'up.id_Perfil = ?'                  => 3,
            'up.st_Usuario_Perfil = ?'          => 'A',
            'pv.ID_TIPO_VINCULO_PESSOA = ?'     => 17
        );

        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);
        $i = 0;
        $ativos = 0;
        foreach ($responsavel as $re) {

            if($re->ST_PESSOA_VINCULADA == 'A'){
                $ativos++;
            }
            $dadosResponsavel[$i]['idResponsavel']      = $re->idPessoaVinculada;
            $dadosResponsavel[$i]['nmResponsavel']      = $re->nmPessoaFisica;
            $dadosResponsavel[$i]['nrCpfResponsavel']   = addMascara($re->nrCpf, 'cpf');
            $dadosResponsavel[$i]['cargoResponsavel']   = $re->nmCbo;
            $dadosResponsavel[$i]['cdCbo']              = $re->cdCbo;
            $dadosResponsavel[$i]['stAtivo']            = $re->ST_PESSOA_VINCULADA;

            // Email do respons�vel da operadora
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $re->idPessoaVinculada));

            $listaEmails = array();
            if(count($emails) > 0){

                $e = 0;
                foreach ($emails as $em) {
                    $listaEmails[$e]['dsEmail'] = $em->dsEmail;
                    $e++;
                }
            }
            $dadosResponsavel[$i]['emailsResponsavel'] = $listaEmails;

            // Telefones do respons�vel da operadora
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));
            $listaTelefones = array();

            if(count($telefones) > 0){

                $t = 0;
                foreach ($telefones as $tel) {
                    if ($tel->idTipoTelefone == 2) {
                        $listaTelefones[$t]['idTipoTelefone'] = 2;
                        $listaTelefones[$t]['TelResponsavel'] = 'Tel: ('.$tel->cdDDD .') '. $tel->nrTelefone;
                    }
                    if ($tel->idTipoTelefone == 4) {
                        $listaTelefones[$t]['idTipoTelefone'] = 4;
                        $listaTelefones[$t]['FaxResponsavel'] = 'Fax: ('.$tel->cdDDD .') '. $tel->nrTelefone;
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
        $this->view->assign('idOperadora', $idOperadora);
        $this->view->assign('qtdAtivos', $ativos);
    }

    public function editarResponsavelAction() {

        $idResponsavel = $this->getRequest()->getParam('id');
        $dadosResponsavel = array();
        $idOperadora = $this->_sessao['operadora'];

        $modelPessoaVinculada   = new Application_Model_PessoaVinculada();
        $modelEmail             = new Application_Model_Email();
        $modelTelefone          = new Application_Model_Telefone();

        $where = array(
            'pv.ID_PESSOA = ?'              => $idOperadora,
            'pv.ID_PESSOA_VINCULADA = ?'    => $idResponsavel,
        );

        // Dados do respons�vel
        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        if(count($responsavel) > 0){

            $dadosResponsavel['idResponsavel']      = $responsavel[0]->idPessoaVinculada;
            $dadosResponsavel['nmResponsavel']      = $responsavel[0]->nmPessoaFisica;
            $dadosResponsavel['nrCpfResponsavel']   = addMascara($responsavel[0]->nrCpf, 'cpf');
            $dadosResponsavel['cargoResponsavel']   = $responsavel[0]->nmCbo;
            $dadosResponsavel['cdCbo']              = $responsavel[0]->cdCbo;
            $dadosResponsavel['stAtivo']            = $responsavel[0]->ST_PESSOA_VINCULADA;

            // Email do respons�vel da operadora
            $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $responsavel[0]->idPessoaVinculada));

            $listaEmails = array();
            if(count($emails) > 0){
                $e = 0;
                foreach ($emails as $em) {
                    $listaEmails[$e]['idEmail'] = $em->ID_EMAIL;
                    $listaEmails[$e]['dsEmail'] = $em->dsEmail;
                    $e++;
                }
            }

            $dadosResponsavel['emailsResponsavel'] = $listaEmails;

            // Telefones do respons�vel da operadora
            $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $responsavel[0]->idPessoaVinculada));
            $listaTelefones = array();

            if(count($telefones) > 0){
                $t = 0;
                foreach ($telefones as $tel) {
                    if ($tel->idTipoTelefone == 2) {
                        $listaTelefones[$t]['idTelefone']       = $tel->idTelefone;
                        $listaTelefones[$t]['idTipoTelefone']   = 2;
                        $listaTelefones[$t]['TelResponsavel']   = 'Tel: ('.$tel->cdDDD .') '. $tel->nrTelefone;
                    }
                    if ($tel->idTipoTelefone == 4) {
                        $listaTelefones[$t]['idTelefone']       = $tel->idTelefone;
                        $listaTelefones[$t]['idTipoTelefone']   = 4;
                        $listaTelefones[$t]['FaxResponsavel']   = 'Fax: ('.$tel->cdDDD .') '. $tel->nrTelefone;
                    }
                    $t++;
                }
            }

            $dadosResponsavel['telefonesResponsavel'] = $listaTelefones;

        } else {
            parent::message('Erro ao localizar o respons�vel.', '/operadora/index/responsavel', 'error');
        }

        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('responsavel', $dadosResponsavel);
        $this->view->assign('idOperadora', $idOperadora);

    }

    public function atualizarDadosResponsavelAction() {

        $idResponsavel  = $this->_request->getParam('idResponsavel');
        $idOperadora    = $this->_request->getParam('idOperadora');
        $CDCBO          = $this->_request->getParam('CDCBO');

        $modelCBOPessoaFisica   = new Application_Model_CBOPessoaFisica();

        try {

            if ($CDCBO) {
                // apaga o que for dessa empresa e respons�vel
                $where = array(
                    'ID_PESSOA_FISICA = ?'   => $idResponsavel,
                    'ID_PESSOA_JURIDICA = ?' => $idOperadora
                );

                $modelCBOPessoaFisica->apagar($where);

                $Cols = array(
                    'CD_CBO'             => $CDCBO,
                    'ID_PESSOA_FISICA'   => $idResponsavel,
                    'ID_PESSOA_JURIDICA' => $idOperadora
                );

                $modelCBOPessoaFisica->insert($Cols);

                if ($this->_sessao["PerfilGeral"] != 'A') {

                    // Atualiza o status
                    $modelSituacao                  = new Application_Model_Situacao();
                    $dadosSituacao = array(
                            'ID_PESSOA'                     => $idOperadora,
                            'DS_JUSTIFICATIVA'              => 'Mudan�a em respons�veis da operadora.',
                            'ID_USUARIO'                    => $this->_sessao['idUsuario'],
                            'TP_ENTIDADE_VALE_CULTURA'      => 'O',
                            'ID_TIPO_SITUACAO'              => 1
                        );

                    $modelSituacao->insert($dadosSituacao);

                }
            }

            parent::message('Cargo atualizado com sucesso!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao atualizar o cargo!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
        }
    }

    public function addTelefoneResponsavelAction() {

        $modelTelefone  = new Application_Model_Telefone();
        $modelDDD       = new Application_Model_DDD();

        $idResponsavel  = $this->_request->getParam('idResponsavel');
        $dddFone        = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 0, 2);
        $fone           = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('TelResponsavel')))))), 2);

        if($dddFone){
            $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $dddFone));
            if(count($verificaDDD) == 0 ){
                parent::message('DDD inv&aacute;lido!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
            }
        }

        try {

            if($fone){
                $where = array(
                    'ID_PESSOA = ?'          => $idResponsavel,
                    'SG_PAIS = ?'            => 'BRA',
                    'NR_TELEFONE = ?'        => $fone,
                    'ID_TIPO_TELEFONE = ?'   => 2,
                    'CD_DDD = ?'             => $dddFone
                );

                $existeFone = $modelTelefone->select($where);

                if(count($existeFone) == 0){
                    $Cols = array(
                        'ID_PESSOA'          => $idResponsavel,
                        'SG_PAIS'            => 'BRA',
                        'NR_TELEFONE'        => $fone,
                        'ID_TIPO_TELEFONE'   => 2,
                        'CD_DDD'             => $dddFone
                    );
                    $modelTelefone->insert($Cols);

                }else{
                    parent::message($this->getRequest()->getParam('TelResponsavel').'  j� est� cadastrado!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
                }
            }


            parent::message('Telefone adicionado com sucesso!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao adicionar o telefone!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
        }
    }

    public function delTelefoneResponsavelAction() {

        $modelTelefone  = new Application_Model_Telefone();
        $idTelefone     = $this->_request->getParam('idTelefone');
        $idResponsavel  = $this->_request->getParam('idResponsavel');

        try {

            if($idTelefone){
                $modelTelefone->delete($idTelefone);
            }

            parent::message('Telefone exclu�do com sucesso!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao excluir o telefone!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
        }
    }

    public function addEmailResponsavelAction() {

        $modelEmail     = new Application_Model_Email();
        $idResponsavel  = $this->_request->getParam('idResponsavel');
        $email          = trim($this->getRequest()->getParam('emailResponsavel'));

        // Faz a verifica��o usando a fun��o
        if (!validaEmail($email)) {
            parent::message('E-mail inv�lido!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
        }

        try {

            if ($email) {
                $where = array(
                    'ID_PESSOA = ?'           => $idResponsavel,
                    'DS_EMAIL = ?'            => $email,
                    'ID_TIPO_EMAIL = ?'       => 2,
                    'ST_EMAIL_PRINCIPAL = ?'  => 'N'
                );

                $existeEmail = $modelEmail->select($where);

                if(count($existeEmail) == 0){
                    $Cols = array(
                        'ID_PESSOA'           => $idResponsavel,
                        'DS_EMAIL'            => $email,
                        'ID_TIPO_EMAIL'       => 2,
                        'ST_EMAIL_PRINCIPAL'  => 'N'
                    );
                    $modelEmail->insert($Cols);
                }else{
                    parent::message($email.'  j� est� cadastrado!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
                }
            }

            parent::message('Email adicionado com sucesso!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao adicionar o email!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
        }
    }

    public function delEmailResponsavelAction() {

        $modelEmail     = new Application_Model_Email();
        $idResponsavel  = $this->_request->getParam('idResponsavel');
        $idEmail        = $this->getRequest()->getParam('idEmail');

        try {

            if ($idEmail) {
                $modelEmail->delete($idEmail);
            }

            parent::message('Email exclu�do com sucesso!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'confirm');

        } catch (Exception $exc) {
            parent::message('Erro ao excluir o email!', 'operadora/index/editar-responsavel/id/'.$idResponsavel, 'error');
        }
    }

    public function responsavelUpdateAction() {

    }

    public function alterarsenhaAction() {

    }

    public function alterarsenhaactionAction() {
        if ($_POST) {

            $modelUsuario = new Application_Model_Usuario;

            $NOVA_SENHA             = $this->getRequest()->getParam('NOVA_SENHA');
            $NOVA_SENHA_CONFIRMA    = $this->getRequest()->getParam('NOVA_SENHA_CONFIMA');
            $SENHA_ATUAL            = $this->getRequest()->getParam('SENHA');

            if (!$SENHA_ATUAL) {
                parent::message('Informe a senha atual', '/operadora/index/alterarsenha/', 'error');
            }
            if (!$NOVA_SENHA) {
                parent::message('Informe a nova senha', '/operadora/index/alterarsenha/', 'error');
            }
            if ($NOVA_SENHA != $NOVA_SENHA_CONFIRMA) {
                parent::message('Senha de confirma��o incorreta', '/operadora/index/alterarsenha/', 'error');
            }

            //VALIDA SENHA ATUAL
            $where = array(
                'ID_USUARIO = ?'    => $this->_sessao['idUsuario'],
                'DS_SENHA = ?'      => md5($SENHA_ATUAL)
            );

            $recuperaUsuario = $modelUsuario->select($where);
            if (count($recuperaUsuario) > 0) {
                $cols = array(
                    'DS_SENHA' => md5($NOVA_SENHA)
                );
                if ($modelUsuario->update($cols, $this->_sessao['idUsuario'])) {
                    parent::message('Senha atualizada com sucesso', '/operadora/index/alterarsenha/', 'confirm');
                }
            } else {
                parent::message('Senha atual incorreta', '/operadora/index/alterarsenha/', 'error');
            }
        }
    }

    public function abrirArquivoAction() {
        $idOperadora = $this->_sessao['operadora'];

        $uploaddir  = "/var/arquivos/arquivos-valecultura/";
        $arquivo    = $this->getRequest()->getParam('arquivo');

        $operadoraArquivo = (int) substr($arquivo, 0, (strpos($arquivo, '_')));
        if ($operadoraArquivo == $idOperadora) {
            if (file_exists($uploaddir . $arquivo)) {
                $len = filesize($uploaddir . $arquivo);
                header("Content-type: application/pdf");
                header("Content-Length: $len");
                header("Content-Disposition: inline; filename= $arquivo");
                $existente = readfile($uploaddir . $arquivo);
            } else {
                parent::message('Arquivo n�o encontrado', '/operadora/index/arquivos/', 'error');
            }
        } else {
            parent::message('Acesso n�o permitido', '/operadora/index/arquivos/', 'error');
        }
        die;
    }

    public function consultarBeneficiariasAction() {

        $modelBeneficiaria      = new Application_Model_Beneficiaria();
        $modelPessoaVinculada   = new Application_Model_PessoaVinculada();
        $modelSituacoes         = new Application_Model_TipoSituacao();
        $modelEmail             = new Application_Model_Email();
        $modelTelefone          = new Application_Model_Telefone();

        $idOperadora = $this->_sessao['operadora'];

        $CNPJ       = $this->getRequest()->getParam('CNPJ');
        $NOME       = $this->getRequest()->getParam('NOME');
        $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
        $DTFIM      = $this->getRequest()->getParam('DTFIM');
        $filtro     = $this->getRequest()->getParam('filtra');
        $SITUACAO   = $this->getRequest()->getParam('SITUACAO');

        if ($CNPJ) {
            $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
        }

        if ($NOME) {
            $where['pj.nm_Razao_Social like ?'] = '%' . $NOME . '%';
        }

        if (strlen($DTINICIO) == 10) {
            $DTINICIO = explode('/', $DTINICIO);
            if (checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                $where['dt_Inscricao >= ?'] = $DTINICIO[2] . '-' . $DTINICIO[1] . '-' . $DTINICIO[0];
            }
        }

        if (strlen($DTFIM) == 10) {
            $DTFIM = explode('/', $DTFIM);
            if (checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                $DTFIMQuery = explode('/', date('d/m/Y', strtotime("+1 days", strtotime($DTFIM[0] . '-' . $DTFIM[1] . '-' . $DTFIM[2]))));
                $where['dt_Inscricao < ?'] = $DTFIMQuery[2] . '-' . $DTFIMQuery[1] . '-' . $DTFIMQuery[0];
            }
        }

        if ($SITUACAO > 0) {
            $where['ID_SITUACAO = ?'] = $SITUACAO;
            $this->view->assign('situacao', $SITUACAO);
        }else{
            $this->view->assign('situacao', '');
        }

        $this->view->assign('cnpj', $CNPJ);
        $this->view->assign('nome', $NOME);

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

        if ($filtro == 1) {
            $where['ST_DIVULGAR_DADOS <> ?'] = NAO_AUTORIZADO_DIVULGAR_INFORMACAO;
            $where['ID_OPERADORA = ?'] = $idOperadora;
            $beneficiarias = $modelBeneficiaria->buscarDados($where); //, array(), 5
        } else {
            $where['ST_DIVULGAR_DADOS = ?'] = AUTORIZADO_TODAS_OPERADORAS;
            $beneficiarias = $modelBeneficiaria->buscarDados($where);
        }

        $dadosBeneficiarias = array();

        $i = 0;
        foreach($beneficiarias as $b){

            $dadosBeneficiarias[$i]['idBeneficiaria']   = $b['idBeneficiaria'];
            $dadosBeneficiarias[$i]['idOperadora']      = $b['idOperadora'];
            $dadosBeneficiarias[$i]['nmOperadora']      = $b['operadora'];
            $dadosBeneficiarias[$i]['dtInscricao']      = $b['dtInscricao'];
            $dadosBeneficiarias[$i]['qtdFuncionarios']  = $b['qtdFuncionarios'];
            $dadosBeneficiarias[$i]['situacao']         = $b['situacao'];
            $dadosBeneficiarias[$i]['dtRegistro']       = $b['dtRegistro'];
            $dadosBeneficiarias[$i]['nrCnpj']           = $b['nrCnpj'];
            $dadosBeneficiarias[$i]['nmRazaoSocial']    = $b['nmRazaoSocial'];
            $dadosBeneficiarias[$i]['nmFantasia']       = $b['nmFantasia'];

            $responsaveis = array();

            // Buscar os respons�veis da Benefici�ria
            $where = array(
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16,
                'pv.id_Pessoa = ?'              => $b['idBeneficiaria'],
                'up.id_Perfil = ?'              => 2,
                'up.st_Usuario_Perfil = ?'      => 'A'
            );

            $listarResponsaveis = $modelPessoaVinculada->buscarDadosResponsavel($where);

            $r = 0;
            foreach($listarResponsaveis as $lr){

                $responsaveis[$r]['idResponsavel']  = $lr['idPessoaVinculada'];
                $responsaveis[$r]['nmPessoaFisica'] = $lr['nmPessoaFisica'];
                $responsaveis[$r]['dtregistro']     = $lr['dtregistro'];
                $responsaveis[$r]['nrCpf']          = $lr['nrCpf'];
                $responsaveis[$r]['cdCbo']          = $lr['cdCbo'];
                $responsaveis[$r]['nmCbo']          = $lr['nmCbo'];

                // Email do respons�vel da operadora

                $emails = array();
                $listarEmails = $modelEmail->buscarEmails(array('id_Pessoa = ?' => $lr['idPessoaVinculada']));
                $e = 0;
                foreach($listarEmails as $email){
                    $emails[$e]['emailResponsavel'] = $email->dsEmail;
                    $e++;
                }

                $responsaveis[$r]['emailsResponsavel'] = $emails;

                // Telefones do respons�vel da operadora
                $telefones = array();
                $listaTelefones = $modelTelefone->buscarTelefones(array('id_Pessoa = ?' => $lr['idPessoaVinculada']));
                $t = 0;
                foreach($listaTelefones as $tel){

                    $telefones[$t]['telResponsavel'] = '';
                    $telefones[$t]['faxResponsavel'] = '';

                    if ($tel->idTipoTelefone == 2) {
                        $telefones[$t]['telResponsavel'] = $tel->cdDDD . $tel->nrTelefone;
                    }
                    if ($tel->idTipoTelefone == 4) {
                        $telefones[$t]['faxResponsavel'] = $tel->cdDDD . $tel->nrTelefone;
                    }
                    $t++;
                }

                $responsaveis[$r]['telefonesResponsavel'] = $telefones;

                $r++;
            }

            $dadosBeneficiarias[$i]['responsaveis'] = $responsaveis;

            $i++;

        }

        $pagina = intval($this->_getParam('pagina', 1));
        $paginator = Zend_Paginator::factory($dadosBeneficiarias);
        // Seta a quantidade de registros por p�gina
        $paginator->setItemCountPerPage(20);
        // numero de paginas que ser�o exibidas
        $paginator->setPageRange(7);
        // Seta a p�gina atual
        $paginator->setCurrentPageNumber($pagina);
        // Passa o paginator para a view
        $this->view->beneficiarias = $paginator;

        $situacoes = $modelSituacoes->select();
        $this->view->assign('situacoes', $situacoes);
        $this->view->assign('filtro', $filtro);
    }

    public function buscarFaixasSalariaisAction() {
        $this->_helper->layout->disableLayout();
        $modelFaixaSalarialBeneficiaria = new Application_Model_FaixaSalarialBeneficiaria();
        $idBeneficiaria    = $this->getRequest()->getParam('idBeneficiaria');
        try {
            $faixas = $modelFaixaSalarialBeneficiaria->listaFaixas(array('fs.ID_BENEFICIARIA = ?' => $idBeneficiaria));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        $this->view->faixas = $faixas;
    }


    // ativa��o dos respons�veis das Benefici�rias
    public function ativacaoResponsavelAction(){

        $idOperadora    = $this->_sessao['operadora'];
        $idResponsavel  = $this->getRequest()->getParam('id');
        $ativacao       = $this->getRequest()->getParam('ativar');
        $tipoVinculo    = 'A';
        $msg            = 'Ativado com sucesso!';

        if($ativacao == 'N'){
            $tipoVinculo    = 'I';
            $msg            = 'Desativado com sucesso!';
        }

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();

        try{

            $where = array(
                'pv.ID_PESSOA_VINCULADA = ?'    => $idResponsavel,
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17,
                'pv.ST_PESSOA_VINCULADA = ?'    => 'A'
            );

            $eResponsavelOperadora    = $modelPessoaVinculada->buscarDadosResponsavel($where);

            if ((count($eResponsavelOperadora) > 0) && ($ativacao == 'S')) {
                parent::message('O respons�vel j� est� ativo em outra operadora!', '/operadora/index/responsavel/', 'error');
            }

            $modelPessoaVinculada->update(array('ST_PESSOA_VINCULADA' => $tipoVinculo), $idOperadora, $idResponsavel);

            if ($this->_sessao["PerfilGeral"] != 'A') {

                // Atualiza o status
                $modelSituacao                  = new Application_Model_Situacao();
                $dadosSituacao = array(
                        'ID_PESSOA'                     => $idOperadora,
                        'DS_JUSTIFICATIVA'              => 'Mudan�a em respons�veis da operadora.',
                        'ID_USUARIO'                    => $this->_sessao['idUsuario'],
                        'TP_ENTIDADE_VALE_CULTURA'      => 'O',
                        'ID_TIPO_SITUACAO'              => 1
                    );

                $modelSituacao->insert($dadosSituacao);

            }

            parent::message($msg, '/operadora/index/responsavel/', 'confirm');

        } catch (Exception $ex) {
            parent::message('Ops, desculpe mas houve um erro na aplica��o.', '/operadora/index/responsavel/', 'error');
        }


    }

    public function ativacaoNaoResponsavelAction()
    {

        $idOperadora = $this->_sessao['operadora'];
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

            $where = array(
                'pv.ID_PESSOA_VINCULADA = ?' => $idResponsavel,
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 13,
                'pv.ST_PESSOA_VINCULADA = ?' => 'A'
            );

            $eResponsavelOperadora = $modelPessoaVinculada->buscarDadosResponsavel($where);

            if ((count($eResponsavelOperadora) > 0) && ($ativacao == 'S')) {
                parent::message('O n�o respons�vel j� est� ativo em outra operadora!', '/operadora/index/nao-responsavel/', 'error');
            }

            $modelPessoaVinculada->update(array('ST_PESSOA_VINCULADA' => $tipoVinculo), $idOperadora, $idResponsavel);

            if ($this->_sessao["PerfilGeral"] != 'A') {

                // Atualiza o status
                $modelSituacao = new Application_Model_Situacao();
                $dadosSituacao = array(
                    'ID_PESSOA' => $idOperadora,
                    'DS_JUSTIFICATIVA' => 'Mudan�a em respons�veis da operadora.',
                    'ID_USUARIO' => $this->_sessao['idUsuario'],
                    'TP_ENTIDADE_VALE_CULTURA' => 'O',
                    'ID_TIPO_SITUACAO' => 1
                );

                $modelSituacao->insert($dadosSituacao);

            }

            parent::message($msg, '/operadora/index/nao-responsavel/', 'confirm');

        } catch (Exception $ex) {
            parent::message('Ops, desculpe mas houve um erro na aplica��o.', '/operadora/index/nao-responsavel/', 'error');
        }
    }

    public function exportarExcelAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $this->consultarBeneficiariasAction();
        $relatorio = new Minc_Model_Relatorio();
        $nomeArquivo = 'Empresas_Beneficiarias_' . date('d-m-Y-H\h\r\si\m\i\n') . '.xls';
        $relatorio->configuraHeader($nomeArquivo);
        echo $relatorio->consultarBeneficiariaExportarExel($this->view->beneficiarias);
    }


    public function naoResponsavelAction()
    {

        $dadosResponsavel = array();
        $idOperadora = $this->_sessao['operadora'];

        // Dados do respons�vel da operadora
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $where = array(
            'pv.id_Pessoa = ?' => $idOperadora,
            'up.id_Perfil = ?' => 3,
            'up.st_Usuario_Perfil = ?' => 'A',
            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 13
        );

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

            // Email do respons�vel da operadora
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

            // Telefones do respons�vel da operadora
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
        $this->view->assign('idOperadora', $idOperadora);
        $this->view->assign('qtdAtivos', $ativos);
    }

    public function novoNaoResponsavelAction()
    {
        $idOperadora = $this->_sessao['operadora'];
        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('idOperadora', $idOperadora);
    }

    public function salvarNaoResponsavelAction()
    {
        $idOperadora = $this->_sessao['operadora'];

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
                throw new Exception('CPF n�o encontrado!', 500);
            }

            if (strlen($NRTELEFONE) < 8) {
                throw new Exception('Informe o telefone!', 500);
            }

            if ($CDDDD) {
                $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $CDDDD));
                if (count($verificaDDD) == 0) {
                    throw new Exception('DDD inv�lido!', 500);
                }
            }

            if (!$DSEMAIL) {
                throw new Exception('Informe o e-mail!', 500);
            }

            if (!validaEmail($DSEMAIL)) {
                throw new Exception('Email inv�lido!', 500);
            }

            // Vincular o respons�vel
            $idPessoaFisica = $IDPF;
            $idPessoaJuridica = $idOperadora;

            //Verifica se j� existe esse n�mero cadastrado
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
                //Verifica se j� existe esse n�mero cadastrado
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

            // Verificar se j� existe o email
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

                // Verifica se j� existe esse registro para n�o duplicar
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

            //============== VINCULANDO EMPRESA E N�o RESPONSAVEL ==================
            //Verifica se ja existe vinculo
            $where = array(
                'ID_PESSOA = ?' => $idPessoaJuridica,
                'ID_PESSOA_VINCULADA = ?' => $idPessoaFisica,
                'ID_TIPO_VINCULO_PESSOA = ?' => 13
            );

            $vinculo = $modelPessoaVinculada->select($where);

            if (count($vinculo) < 1) {
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

            //Verifica se usuario j� tem o perfil
            $where = array(
                'ID_USUARIO = ?' => $idUsuario,
                'ID_PERFIL   = ?' => 3
            );

            $usuarioPerfil = $modelUsuarioPerfil->select($where);
            if (count($usuarioPerfil) < 1) {
                $Cols = array(
                    'ID_USUARIO' => $idUsuario,
                    'ID_PERFIL' => 3
                );

                $modelUsuarioPerfil->insert($Cols);
            }

            if ($this->_sessao["PerfilGeral"] != 'A') {

                //Cria Situa��o para a Operadora
                $Cols = array(
                    'ID_PESSOA' => $idPessoaJuridica,
                    'ID_USUARIO' => $idUsuario,
                    'ID_TIPO_SITUACAO' => 1,
                    'TP_ENTIDADE_VALE_CULTURA' => 'O',
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
            parent::message('N�o Respons�vel cadastrado com sucesso!', '/operadora/index/nao-responsavel', 'confirm');

        } catch (Exception $exc) {
            $mgs = $exc->getCode() == 500 ? $exc->getMessage() : 'Erro ao cadastrar o n�o respons�vel.';
            $db->rollBack();
            parent::message($mgs, '/operadora/index/novo-nao-responsavel', 'error');
        }
    }


    public function editarNaoResponsavelAction()
    {

        $idResponsavel = $this->getRequest()->getParam('id');
        $dadosResponsavel = array();
        $idOperadora = $this->_sessao['operadora'];
        $urlArquivo = null;

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $where = array(
            'pv.ID_PESSOA = ?' => $idOperadora,
            'pv.ID_PESSOA_VINCULADA = ?' => $idResponsavel,
        );

        // Dados do respons�vel
        $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

        if (count($responsavel) > 0) {

            $dadosResponsavel['idResponsavel'] = $responsavel[0]->idPessoaVinculada;
            $dadosResponsavel['nmResponsavel'] = $responsavel[0]->nmPessoaFisica;
            $dadosResponsavel['nrCpfResponsavel'] = addMascara($responsavel[0]->nrCpf, 'cpf');
            $dadosResponsavel['cargoResponsavel'] = $responsavel[0]->nmCbo;
            $dadosResponsavel['cdCbo'] = $responsavel[0]->cdCbo;
            $dadosResponsavel['stAtivo'] = $responsavel[0]->ST_PESSOA_VINCULADA;

            // Email do respons�vel da operadora
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

            // Telefones do respons�vel da operadora
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
            $where['ID_OPERADORA = ?'] = $idOperadora;
            $where['ID_RESPONSAVEL = ?'] = $idResponsavel;

            $modelArquivoOperadora = new Application_Model_ArquivoOperadora();
            $arquivoAtual = $modelArquivoOperadora->select($where, "DT_UPLOAD_ARQUIVO DESC", 1);

            if (count($arquivoAtual) > 0) {
                $arquivo = $arquivoAtual[0]['DS_CAMINHO_ARQUIVO'];
                $urlArquivo = $this->view->url(array('module' => 'operadora',
                    'controller' => 'index',
                    'action' => 'abrir-arquivo',
                    'arquivo' => $arquivo));
            }
        } else {
            parent::message('Erro ao localizar o respons�vel.', '/operadora/index/responsavel', 'error');
        }

        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO', null);

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('responsavel', $dadosResponsavel);
        $this->view->assign('idOperadora', $idOperadora);
        $this->view->assign('urlArquivo', $urlArquivo);
    }

    public function atualizarDadosNaoResponsavelAction()
    {
        $idResponsavel = $this->_request->getParam('idResponsavel');
        $idOperadora = $this->_request->getParam('idOperadora');
        $CDCBO = $this->_request->getParam('CDCBO');

        $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();

        try {
            if ($CDCBO) {
                // apaga o que for dessa empresa e respons�vel
                $where = array(
                    'ID_PESSOA_FISICA = ?' => $idResponsavel,
                    'ID_PESSOA_JURIDICA = ?' => $idOperadora
                );

                $modelCBOPessoaFisica->apagar($where);

                $Cols = array(
                    'CD_CBO' => $CDCBO,
                    'ID_PESSOA_FISICA' => $idResponsavel,
                    'ID_PESSOA_JURIDICA' => $idOperadora
                );

                $modelCBOPessoaFisica->insert($Cols);

                if ($this->_sessao["PerfilGeral"] != 'A') {
                    // Atualiza o status
                    $modelSituacao = new Application_Model_Situacao();
                    $dadosSituacao = array(
                        'ID_PESSOA' => $idOperadora,
                        'DS_JUSTIFICATIVA' => 'Mudan�a em respons�veis da operadora.',
                        'ID_USUARIO' => $this->_sessao['idUsuario'],
                        'TP_ENTIDADE_VALE_CULTURA' => 'O',
                        'ID_TIPO_SITUACAO' => 1
                    );

                    $modelSituacao->insert($dadosSituacao);
                }
            }

            parent::message('Cargo atualizado com sucesso!', 'operadora/index/editar-nao-responsavel/id/' . $idResponsavel, 'confirm');

        } catch (Exception $exc) {
            $msg = $exc->getCode() == 500 ? $exc->getMessage() : 'Erro ao atualizar o cargo!';
            parent::message($msg, 'operadora/index/editar-nao-responsavel/id/' . $idResponsavel, 'error');
        }
    }
}
