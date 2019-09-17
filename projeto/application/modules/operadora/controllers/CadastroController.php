<?php

include_once 'GenericController.php';

class Operadora_CadastroController extends GenericController
{

    public function init()
    {

        // Layout Padrão
        $this->view->layout()->setLayout('layout');

        // Título
        $this->view->assign('titulo', 'Operadora');

        parent::init();
    }

    public function indexAction()
    {
        $this->getHelper('layout')->disableLayout();

        $modelCBO = new Application_Model_CBO();
        $CBOs = $modelCBO->select(array(), 'NM_CBO');

        $this->view->CBOs = $CBOs;
    }

    public function gerarcaptchaAction()
    {
        $this->getHelper('layout')->disableLayout();
        $captcha = new Zend_Captcha_Image(); // Este Ã© o nome da classe, no secrets...
        $captcha->setWordlen(5) // quantidade de letras, tente inserir outros valores
        ->setImgDir('imagens/captcha')// o caminho para armazenar as imagens
        ->setGcFreq(10)//especifica a cada quantas vezes o garbage collector vai rodar para eliminar as imagens inválidas
        ->setExpiration(1200)// tempo de expiração em segundos.
        ->setHeight(70) // tamanho da imagem de captcha
        ->setWidth(200)// largura da imagem
        ->setLineNoiseLevel(1) // o nivel das linhas, quanto maior, mais dificil fica a leitura
        ->setDotNoiseLevel(2)// nivel dos pontos, experimente valores maiores
        ->setFontSize(15)//tamanho da fonte em pixels
        ->setFont('font/arial.ttf'); // caminho para a fonte a ser usada
        $this->view->idCaptcha = $captcha->generate(); // passamos aqui o id do captcha para a view
        $this->view->captcha = $captcha->render($this->view); // e o proprio captcha para a view
    }

    public function cadastrarAction()
    {
        if ($_POST) {
            $this->getHelper('layout')->disableLayout();
            set_time_limit('120');

            $modelPessoaJuridica = new Application_Model_PessoaJuridica();
            $modelEndereco = new Application_Model_Endereco();
            $modelLogradouro = new Application_Model_Logradouro();
            $modelOperadora = new Application_Model_Operadora();
            $modelArquivoOperadora = new Application_Model_ArquivoOperadora();
            $modelPessoaVinculada = new Application_Model_PessoaVinculada();
            $modelTelefone = new Application_Model_Telefone();
            $modelEmail = new Application_Model_Email();
            $modelSite = new Application_Model_Site();
            $modelUsuario = new Application_Model_Usuario();
            $modelUsuarioPerfil = new Application_Model_UsuarioPerfil();
            $modelSituacao = new Application_Model_Situacao();
            $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();
            $modelDDD = new Application_Model_DDD();

            //Recuperando form
            $IDPJ = $this->getRequest()->getParam('IDPJ');
            $IDPF = $this->getRequest()->getParam('IDPF');
            $IDENDERECO = $this->getRequest()->getParam('ID_ENDERECO');
            $NRCEP = str_replace('-', '', $this->getRequest()->getParam('EMPRESA_CEP'));
            $NRCPF = retornaDigitos($this->getRequest()->getParam("RESPONSAVEL_CPF"));
            $NRCNPJ = retornaDigitos($this->getRequest()->getParam("EMPRESA_CNPJ"));
            $NMFANTASIA = $this->getRequest()->getParam('EMPRESA_NMFANTASIA');
            $DSCOMPLEMENTOENDERECO = trim($this->getRequest()->getParam('EMPRESA_COMPLEMENTO'));
            $NRCOMPLEMENTO = trim($this->getRequest()->getParam('EMPRESA_NUMERO'));
            $IDBAIRRO = $this->getRequest()->getParam('EMPRESA_BAIRRO');

            $ARFAX = explode(" ", $this->getRequest()->getParam("RESPONSAVEL_FAX"));
            $CDDDDFAX = retornaDigitos($ARFAX[0]);
            $NRFAX = retornaDigitos($ARFAX[1]);

            $ARTEL = explode(" ", $this->getRequest()->getParam("RESPONSAVEL_FONE"));
            $CDDDD = retornaDigitos($ARTEL[0]);
            $NRTELEFONE = retornaDigitos($ARTEL[1]);

            $DSEMAIL = $this->getRequest()->getParam('RESPONSAVEL_EMAIL');
            $DSEMAILINSTITUCIONAL = $this->getRequest()->getParam('INSTITUCIONAL_EMAIL');
            $DSSITE = $this->getRequest()->getParam('DS_SITE');
            $CDCBO = $this->getRequest()->getParam('RESPONSAVEL_CARGO');
            $SAC_TELEFONE = $this->getRequest()->getParam('SAC_TELEFONE');
            $DDD_SAC = $this->getRequest()->getParam('SAC_DDD');
            $COMPLEMENTO_SAC = $this->getRequest()->getParam('SAC_COMPLEMENTO');

            $INICIO_COMERCIALIZACAO = implode('-', array_reverse(explode('/',  $this->getRequest()->getParam('INICIO_COMERCIALIZACAO'))));

            $NAORESPONSAVEL = $this->getRequest()->getParam("responsavel_empresa") == 0;

            $IDPF_NAO_EMPRESA = $this->getRequest()->getParam("ID_NAO_EMPRESA_PF");
            $RESPONSAVEL_NAO_EMPRESA_CPF = retornaDigitos($this->getRequest()->getParam("RESPONSAVEL_NAO_EMPRESA_CPF"));
            $RESPONSAVEL_NAO_EMPRESA_CARGO = $this->getRequest()->getParam("RESPONSAVEL_NAO_EMPRESA_CARGO");
            $RESPONSAVEL_NAO_EMPRESA_EMAIL = $this->getRequest()->getParam("RESPONSAVEL_NAO_EMPRESA_EMAIL");

            $ARTELNAORES = explode(" ", $this->getRequest()->getParam("RESPONSAVEL_NAO_EMPRESA_FONE"));
            $CDDDDNAORES = retornaDigitos($ARTELNAORES[0]);
            $NRTELEFONENAORES = retornaDigitos($ARTELNAORES[1]);

            $ARTELNAOFAX = explode(" ", $this->getRequest()->getParam("RESPONSAVEL_NAO_EMPRESA_FAX"));
            $CDDDDFAXNAORES = retornaDigitos($ARTELNAOFAX[0]);
            $NRTELEFONEFAXNAORES = retornaDigitos($ARTELNAOFAX[1]);

            // Validando Form
            $ERROR = array();

            if ($IDPJ == '0') {
                $ERROR['BENEFICIÁRIA'] = 'CNPJ não encontrado!';
            }

            if ($IDPF == '0') {
                $ERROR['RESPONSÁVEL'] = 'CPF não encontrado!';
            }

            if ($CDDDD) {
                $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $CDDDD));

                if (count($verificaDDD) == 0) {
                    $ERROR['DDDRESPONSAVEL'] = 'DDD do responsável é inv&aacute;lido!';
                }
            }

            if ($DDD_SAC) {

                $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $DDD_SAC));
                if (count($verificaDDD) == 0) {
                    $ERROR['DDD'] = 'DDD inv&aacute;lido!';
                }
            }

            if (!$DSEMAILINSTITUCIONAL) {
                $ERROR['EMAILINSTITUCIONAL'] = 'Informe o e-mail institucional';
            }

            if (!validaEmail($DSEMAILINSTITUCIONAL)) {
                $ERROR['EMAILINSTITUCIONAL'] = 'Email institucional inválido!';
            }

            if (!$this->getRequest()->getParam('Confirmo_mais_de_3_anos')) {
                $ERROR['MINIMO'] = 'A empresa deve declarar ter qualifica&ccedil;&atilde;o t&eacute;cnica nos termos do inciso II do Art. 5&ordm; do Decreto n&ordm; 8.084 de 2013 e do Art. 4º da Instrução Normativa nº 02';
            }

            if (!$this->getRequest()->getParam('ConfimaLei')) {
                $ERROR['LEI'] = 'Confirme a veracidade de todas as informa&ccedil;&otilde;es';
            }

            if (strlen($this->getRequest()->getParam('INICIO_COMERCIALIZACAO')) != 10 || !$this->isValidDate($INICIO_COMERCIALIZACAO)) {
                $ERROR['INICIO_COMERCIALIZACAO'] = 'Data inv&aacute;lida';
            }

            $where = array('NR_CEP = ?' => "" . $NRCEP . "");
            $logradouro = $modelLogradouro->selectEndereco($where);
            if (count($logradouro) < 1 || strlen($NRCEP) != 8) {
                $ERROR['CEP'] = 'CEP inv&aacute;lido';
            } else {
                $IDLOGRADOURO = $logradouro[0]['ID_LOGRADOURO'];
                $STLOGRADOURO = $logradouro[0]['ST_LOGRADOURO'];
                $IDBAIRRO = (!empty($IDBAIRRO)) ? $IDBAIRRO : $logradouro[0]['ID_BAIRRO_INICIO'];
            }

            if (!$this->validaCPF($NRCPF)) {
                $ERROR['CPF'] = 'CPF inv&aacute;lido';
            }

            if (strlen($NRTELEFONE) < 8) {
                $ERROR['TELEFONE'] = 'Informe o telefone';
            }

            if (!$DSEMAIL) {
                $ERROR['EMAIL'] = 'Informe o e-mail';
            }

            if (!validaEmail($DSEMAIL)) {
                $ERROR['EMAIL'] = 'Email inválido!';
            }

            if (!$this->isCnpjValid($NRCNPJ)) {
                $ERROR['CNPJ'] = 'CNPJ inv&aacute;lido';
            }

            if ($CDCBO < 1) {
                $ERROR['CBO'] = 'Informe o cargo';
            }

            if ($NAORESPONSAVEL) {
                if ($RESPONSAVEL_NAO_EMPRESA_CPF == "0") {
                    $ERROR["CPF_NAO_EMPRESA"] = "CPF do não responsável não encontrado!";
                }

                if (!validaCPF($RESPONSAVEL_NAO_EMPRESA_CPF)) {
                    $ERROR["CPF_NAO_EMPRESA_ERROR"] = "CPF do não responsável inválido";
                }

                if ($RESPONSAVEL_NAO_EMPRESA_CARGO < 1) {
                    $ERROR["CARGO_NAO_EMPRESA"] = "Informe o cargo do não responsável!";
                }

                if (!$RESPONSAVEL_NAO_EMPRESA_EMAIL) {
                    $ERROR["EMAIL_NAO_EMPRESA"] = "Informe o e-mail do não responsável!";
                }

                if (!validaEmail($RESPONSAVEL_NAO_EMPRESA_EMAIL)) {
                    $ERROR["EMAIL_NAO_EMPRESA_ERROR"] = "Email do não responsável inválido!";
                }

                if ($CDDDDNAORES) {
                    $verificaDDD = $modelDDD->select(array("CD_DDD = ?" => $CDDDDNAORES));
                    if (count($verificaDDD) == 0) {
                        $ERROR["DDD_NAO_RESPONSAVEL"] = "DDD do não responsável inv&aacute;lido!";
                    }
                }

                if (strlen($NRTELEFONENAORES) < 8) {
                    $ERROR["TELEFONE_NAO_RESPONSAVEL"] = "Informe o telefone do não responsável!";
                }
            }

            foreach ($_FILES as $k => $v) {
                if ($k == 'ANEXO_1' || $k == 'ANEXO_2' || $k == 'ANEXO_3' || $k == 'ANEXO_4' || $k == 'ANEXO_5' || $k == 'ANEXO_6' || $k == 'ANEXO_7' || $k == 'ANEXO_8' || $k == 'ANEXO_11') {
                    if ($_FILES[$k]['error'] != 0) {
                        $ERROR['ARQUIVOS_OBRIGATORIOS'] = 'Documento obrigatório não enviado';
                    }
                }
                if ($_FILES[$k]['error'] == 0) {

                    if ($_FILES[$k]["size"] >= 5242880) {
                        $ERROR['TAMANHO_ARQUIVO'] = 'Tamanho maximo do arquivo deve ser de 5mb';
                    }

//                    if (strpos($_FILES[$k]['type'], 'pdf') === false) {
//                        $ERROR['ARQUIVOS_TIPO'] = 'Apenas aquivos no formato PDF são validos';
//                    }
                } else {
                    if ($_FILES[$k]['error'] != 4) {
                        $ERROR['ARQUIVOS_ERROR'] = 'Falha no envio de documento';
                    }
                }
            }

            if (!$this->getRequest()->getParam("ConfimaRegras")) {
                $ERROR["PROGRAMA"] = "Confirme as regras do Programa de Cultura do Trabalhador";
            }

            if (!$this->getRequest()->getParam("ConfimaLei")) {
                $ERROR["LEI"] = "Confirme a veracidade de todas as informações";
            }

            if (isset($_POST['captcha'])) {
                $captcha = new Zend_Captcha_Image(); // instancia novamente um captcha para validar os dados enviados
                if (!$captcha->isValid($this->getRequest()->getParam('captcha'))) {
                    $ERROR['VALIDADOR'] = 'Código captcha incorreto';
                }
            } else {
                $ERROR['VALIDADOR'] = 'Informe o código verificador';
            }

            if (count($ERROR) > 0) {
                $this->view->error = $ERROR;
                return;
            }

            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();

            try {
                // O ID já vem do cadastro
                $idPessoaJuridica = $IDPJ;

                $Cols = array(
                    'NM_FANTASIA' => $NMFANTASIA
                );

                $modelPessoaJuridica->update($Cols, array('ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica));

                $Cols = array(
                    'ID_PESSOA' => $idPessoaJuridica,
                    'CD_TIPO_ENDERECO' => '01',
                    'ID_SUBDISTRITO_IBGE' => new Zend_Db_Expr('NULL'),
                    'ID_LOGRADOURO' => $IDLOGRADOURO,
                    'ID_BAIRRO' => $IDBAIRRO,
                    'DS_COMPLEMENTO_ENDERECO' => $DSCOMPLEMENTOENDERECO,
                    'NR_COMPLEMENTO' => $NRCOMPLEMENTO,
                    'DS_LOGRA_ENDERECO' => new Zend_Db_Expr('NULL'),
                    'DS_BAIRRO_ENDERECO' => new Zend_Db_Expr('NULL'),
                    'ID_SERVICO' => 1
                );

                if ($IDENDERECO == '0') {
                    $modelEndereco->insert($Cols);
                } else {
                    $modelEndereco->update($Cols, $IDENDERECO);
                }

                if ($SAC_TELEFONE) {

                    //Verifica se já existe esse número cadastrado
                    $where = array(
                        'ID_PESSOA = ?' => $idPessoaJuridica,
                        'SG_PAIS = ?' => 'BRA',
                        'NR_TELEFONE = ?' => $SAC_TELEFONE,
                        'ID_TIPO_TELEFONE = ?' => 7,
                        'CD_DDD = ?' => $DDD_SAC == '' ? new Zend_Db_Expr('NULL') : $DDD_SAC
                    );
                    $existeFone = $modelTelefone->select($where);

                    if (count($existeFone) == 0) {
                        //Inserindo na model Telefone
                        $Cols = array(
                            'ID_PESSOA' => $idPessoaJuridica,
                            'SG_PAIS' => 'BRA',
                            'NR_TELEFONE' => $SAC_TELEFONE,
                            'ID_TIPO_TELEFONE' => 7,
                            'DS_TELEFONE' => $COMPLEMENTO_SAC == '' ? new Zend_Db_Expr('NULL') : $COMPLEMENTO_SAC,
                            'CD_DDD' => $DDD_SAC == '' ? new Zend_Db_Expr('NULL') : $DDD_SAC
                        );
                        $modelTelefone->insert($Cols);
                    }
                }

                // Verificar se já existe o email
                $where = array(
                    'ID_PESSOA      = ?' => $idPessoaJuridica,
                    'DS_EMAIL       = ?' => $DSEMAIL,
                    'ID_TIPO_EMAIL  = ?' => 2
                );

                $existeEmailInstitucional = $modelEmail->select($where);

                if (count($existeEmailInstitucional) == 0) {

                    //Inserindo Email do responsavel
                    $Cols = array(
                        'ID_PESSOA' => $idPessoaJuridica,
                        'DS_EMAIL' => $DSEMAIL,
                        'ID_TIPO_EMAIL' => 2,
                        'ST_EMAIL_PRINCIPAL' => 'S'
                    );

                    $modelEmail->insert($Cols);
                }
                #Jesse - INICIO
                // Verificar se já existe o email
                $where = array(
                    'ID_PESSOA      = ?' => $idPessoaJuridica,
                    'DS_SITE       = ?' => $DSSITE
                );

                $existeSite = $modelSite->select($where);

                if (count($existeSite) == 0) {

                    //Inserindo Email do responsavel
                    $Cols = array(
                        'ID_PESSOA' => $idPessoaJuridica,
                        'DS_SITE' => $DSSITE
                    );

                    $modelSite->insert($Cols);
                }
                #Jesse - FIM

                $Cols = array(
                    'ID_OPERADORA' => $idPessoaJuridica,
                    'DT_INICIO_COMERCIALIZACAO' => $INICIO_COMERCIALIZACAO
                );
                $modelOperadora->insert($Cols);

                //Incluir Arquivo Operadora
                //SALVAR  UPLOAD
                $uploaddir = defined('UPLOAD_DIR') ? UPLOAD_DIR : "/var/arquivos/arquivos-valecultura/";

                foreach ($_FILES as $k => $v) {
                    if ($_FILES[$k]['error'] == 0) {
                        $mnArquivo = $idPessoaJuridica . '_' . $k . '.pdf';
                        $uploadfile = $uploaddir . $mnArquivo;
                        $dsArquivo = $this->getRequest()->getParam($k . '_NOME');
                        if (move_uploaded_file($_FILES[$k]['tmp_name'], $uploadfile)) {

                            $Cols = array(
                                'ID_OPERADORA' => $idPessoaJuridica,
                                'DS_CAMINHO_ARQUIVO' => $mnArquivo,
                                'DS_ARQUIVO' => $dsArquivo
                            );
                            $modelArquivoOperadora->insert($Cols);
                        } else {
                            $db->rollBack();
                            $ERROR['ARQUIVO'] = "Erro ao salvar arquivo";
                            $this->view->error = $ERROR;
                            return;
                        }
                    }
                }

                ###### CADASTRO RESPONSÁVEL EMPRESA ######
                $arDados = array();
                $arDados['idPessoaFisica'] = $IDPF;
                $arDados['idPessoaJuridica'] = $IDPJ;
                $arDados['nrCpf'] = $NRCPF;
                $arDados['cdCbo'] = $CDCBO;
                $arDados['cdDDD'] = $CDDDD;
                $arDados['nrTelefone'] = $NRTELEFONE;
                $arDados['cdDDDFax'] = $CDDDDFAX;
                $arDados['nrFax'] = $NRFAX;
                $arDados['dsEmail'] = $DSEMAIL;
                $arDados['tpVinculoPessoa'] = 17;
                $this->_adionarResponsavel($arDados);

                if ($NAORESPONSAVEL) {
                    ###### CADASTRO NÂO RESPONSÁVEL EMPRESA ######
                    $arDados = array();
                    $arDados['idPessoaFisica'] = $IDPF_NAO_EMPRESA;
                    $arDados['idPessoaJuridica'] = $IDPJ;
                    $arDados['nrCpf'] = $RESPONSAVEL_NAO_EMPRESA_CPF;
                    $arDados['cdCbo'] = $RESPONSAVEL_NAO_EMPRESA_CARGO;
                    $arDados['cdDDD'] = $CDDDDNAORES;
                    $arDados['nrTelefone'] = $NRTELEFONENAORES;
                    $arDados['cdDDDFax'] = $CDDDDFAXNAORES;
                    $arDados['nrFax'] = $NRTELEFONEFAXNAORES;
                    $arDados['dsEmail'] = $RESPONSAVEL_NAO_EMPRESA_EMAIL;
                    $arDados['tpVinculoPessoa'] = 13;
                    $this->_adionarResponsavel($arDados);
                }

                $db->commit();
                $sucesso['CADASTRO'] = "Operadora cadastrada com sucesso!";
                $sucesso['DSEMAIL'] = $DSEMAIL;
                $this->view->sucesso = $sucesso;

            } catch (Exception $exc) {
                $db->rollBack();
                xd($exc->getMessage());
                $ERROR['CADASTRO'] = "Houve um erro no cadastro";
                $this->view->error = $ERROR;
            }
        }
    }

    public function buscaenderecoporcepAction()
    {
        $this->getHelper('layout')->disableLayout();
        if ($_POST) {

            $cep = str_replace('-', '', $this->getRequest()->getParam('CEP'));

            $where = array('NR_CEP = ?' => addslashes($cep));

            $modelLogradouro = new Application_Model_Logradouro();
            $logradouros = $modelLogradouro->selectEndereco($where);

            $retorno = array();
            if (count($logradouros) > 0) {

                $retorno['dados'] = $logradouros[0];
                if (isset($logradouros[0]['ID_BAIRRO_INICIO'])) {
                    if (isset($logradouros[0]['ID_BAIRRO_FIM'])) {
                        $where = array(
                            'ID_BAIRRO >= ?' => $logradouros[0]['ID_BAIRRO_INICIO'],
                            'ID_BAIRRO <= ?' => $logradouros[0]['ID_BAIRRO_FIM']
                        );
                    } else {
                        $where = array(
                            'ID_BAIRRO = ?' => $logradouros[0]['ID_BAIRRO_INICIO']
                        );
                    }
                    $modelBairro = new Application_Model_Bairro();
                    $bairros = $modelBairro->select($where);
                    $retorno['dados']['bairros'] = $bairros;
                } else {
                    $retorno['dados']['bairros'] = array();
                }

                $retorno['error'] = false;
            } else {
                $retorno['error'] = 'CEP inv&aacute;lido';
            }
            echo json_encode($retorno);
        }
    }

    public function buscaPessoaJuridicaAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $modelPessoaJuridica = new Application_Model_PessoaJuridica();
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $modelOperadora = new Application_Model_Operadora();
        $modelEndereco = new Application_Model_Endereco();
        $modelCNAE = new Application_Model_PessoaJuridicaCNAE();
        $modelSituacao = new Application_Model_Situacao();
        $servicos = new Servicos();

        $retorno = array();
        $erro = 0;
        $cnpj = str_replace('/', '', str_replace('.', '', str_replace('-', '', $this->getRequest()->getParam('EMPRESA_CNPJ'))));
        $tipoPJ = $this->getRequest()->getParam('tipoPJ');

        try {

            if (!validaCNPJ($cnpj)) {
                $retorno['error'] = utf8_encode('CNPJ inválido');
                $erro = 1;
            } else {

                // Busca a PJ por CNPJ
                $pj = $modelPessoaJuridica->buscarPessoaJuridica(array('p.NR_CNPJ = ?' => $cnpj));

                if (count($pj) == 0) {

                    $buscaReceita = $servicos->consultarPessoaReceitaFederal($cnpj, 'Juridica', false);

                    if (!$buscaReceita) {
                        $retorno['error'] = utf8_encode('Empresa não encontrada!');
                        $erro = 1;
                    } else {
                        $pj = $modelPessoaJuridica->buscarPessoaJuridica(array('p.NR_CNPJ = ?' => $cnpj));
                    }

                }

                if ($erro == 0 && count($pj) > 0) {

                    $msg = '';
                    $idPessoa = $pj[0]['ID_PESSOA_JURIDICA'];

                    if ($tipoPJ == 'O') {
                        $eOperadoraOuBeneficiaria = $modelOperadora->find($idPessoa);
                        $msg = 'A empresa já está cadastrada como operadora!';
                    } else if ($tipoPJ == 'B') {
                        $eOperadoraOuBeneficiaria = $modelBeneficiaria->find($idPessoa);
                        $inativoOuRecusado = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idPessoa, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));
                        $msg = 'A empresa já está cadastrada como beneficiária!';
                    }

                    if ($tipoPJ == 'O' && count($eOperadoraOuBeneficiaria) > 0) {
                        $retorno['error'] = utf8_encode($msg);
                    } else if ($tipoPJ == 'B' && count($eOperadoraOuBeneficiaria) > 0 &&
                        ($inativoOuRecusado[0]['idTipoSituacao'] == '1' || $inativoOuRecusado[0]['idTipoSituacao'] == '2')) {
                        $retorno['error'] = utf8_encode($msg);
                    } else {
                        $retorno['dados']['idpj'] = $pj[0]['ID_PESSOA_JURIDICA'];
                        $retorno['dados']['razaosocial'] = utf8_encode($pj[0]['NM_RAZAO_SOCIAL']);
                        $retorno['dados']['nomefantasia'] = utf8_encode($pj[0]['NM_FANTASIA']);
                        $retorno['dados']['idendereco'] = '0';
                        $retorno['dados']['cep'] = 'N';
                        $retorno['dados']['dscomplementoendereco'] = '';
                        $retorno['dados']['nrcomplemento'] = '';
                        $retorno['dados']['naturezajuridica'] = '';
                        $retorno['dados']['situacaocnpj'] = utf8_encode($pj[0]['DS_SITUACAO_CADASTRAL']);

                        if ($pj[0]['CD_NATUREZA_JURIDICA'] != '') {
                            $retorno['dados']['naturezajuridica'] = utf8_encode($pj[0]['DS_NATUREZA_JURIDICA']);
                        }

                        // Verificar se existe um endereço do tipo Comercial
                        $enderecoC = $modelEndereco->buscarEnderecoCompleto(array('en.ID_PESSOA = ?' => $idPessoa, 'en.CD_TIPO_ENDERECO = ?' => '01'));
                        if (count($enderecoC) > 0) {
                            $retorno['dados']['dscomplementoendereco'] = utf8_encode($enderecoC[0]['DS_COMPLEMENTO_ENDERECO']);
                            $retorno['dados']['nrcomplemento'] = utf8_encode($enderecoC[0]['NR_COMPLEMENTO']);
                            $retorno['dados']['idendereco'] = $enderecoC[0]['ID_ENDERECO'];
                            $retorno['dados']['cep'] = $enderecoC[0]['NR_CEP'];
                        }

                        if (count($enderecoC) == 0) {
                            // Verificar se existe um endereço do tipo RF
                            $enderecoR = $modelEndereco->buscarEnderecoCompleto(array('en.ID_PESSOA = ?' => $idPessoa, 'en.CD_TIPO_ENDERECO = ?' => '12'));

                            if (count($enderecoR) > 0) {
                                $retorno['dados']['dscomplementoendereco'] = utf8_encode($enderecoR[0]['DS_COMPLEMENTO_ENDERECO']);
                                $retorno['dados']['nrcomplemento'] = utf8_encode($enderecoR[0]['NR_COMPLEMENTO']);
                                $retorno['dados']['cep'] = $enderecoR[0]['NR_CEP'];
                            }

                        }

                        //  -- Dados do CNAE --
                        // CNAE Primário
                        $where = array(
                            'p.ID_PESSOA_JURIDICA = ?' => $idPessoa,
                            'p.ST_CNAE = ?' => 'P'
                        );

                        $cnaePrimario = $modelCNAE->listarCnae($where);
                        if (count($cnaePrimario) > 0) {
                            $retorno['dados']['CNAEPrimario'] = $cnaePrimario[0]->ID_CNAE . ' - ' . utf8_encode($cnaePrimario[0]->dsCNAE);
                        }

                        // CNAE Secundários
                        $listaCNAESecundarios = '';
                        $where = array(
                            'p.ID_PESSOA_JURIDICA = ?' => $idPessoa,
                            'p.ST_CNAE = ?' => 'S'
                        );

                        $cnaeSecundarios = $modelCNAE->listarCnae($where);
                        if (count($cnaeSecundarios) > 0) {

                            foreach ($cnaeSecundarios as $cs) {
                                $listaCNAESecundarios .= $cs->ID_CNAE . ' - ' . utf8_encode($cs->dsCNAE) . '<br />';
                            }

                            $retorno['dados']['CNAESecundarios'] = $listaCNAESecundarios;
                        }

                        $retorno['error'] = '';
                    }
                } else {
                    $retorno['error'] = utf8_encode('Empresa não encontrada!');
                }
            }

        } catch (InvalidArgumentException $exc) {
            $retorno['error'] = utf8_encode('Empresa não encontrada!');
        } catch (Exception $exc) {
            $retorno['error'] = utf8_encode('Empresa não encontrada!');
        }

        echo json_encode($retorno);
    }

    public function buscaPessoaFisicaAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $modelPessoaUsuario = new Application_Model_Usuario();
        $modelPessoaFisica = new Application_Model_PessoaFisica();
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $servicos = new Servicos();

        $retorno = array();
        $cpf = str_replace('.', '', str_replace('-', '', $this->getRequest()->getParam('RESPONSAVEL_CPF')));
        $tipoVinculo = $this->getRequest()->getParam('TIPO_VINCULO');
        $idEmpresa = $this->getRequest()->getParam('idEmpresa');
        $erro = 0;

        try {

            if (!validaCPF($cpf)) {
                $retorno['error'] = utf8_encode('CPF inválido');
                $erro = 1;
            } else {

                // Busca a PJ por CNPJ
                $pf = $modelPessoaFisica->select(array('NR_CPF = ?' => $cpf));

                if (count($pf) == 0) {

                    $buscaReceita = $servicos->consultarPessoaReceitaFederal($cpf, 'Fisica', false);

                    if (empty($buscaReceita)) {
                        $retorno['error'] = utf8_encode('Pessoa não encontrada!');
                        $erro = 1;
                    } else {
                        $pf = $modelPessoaFisica->select(array('NR_CPF = ?' => $cpf));
                    }

                }

                if ($erro == 0 && count($pf) > 0) {

                    $idPessoa = $pf[0]['ID_PESSOA_FISICA'];
                    $retorno['dados']['idpf'] = $pf[0]['ID_PESSOA_FISICA'];
                    $retorno['dados']['nome'] = utf8_encode($pf[0]['NM_PESSOA_FISICA']);
                    $retorno['error'] = '';

                    $where = array(
                        'pf.ID_PESSOA_FISICA = ?' => $pf[0]['ID_PESSOA_FISICA'],
                        'per.ID_PERFIL NOT IN (?)' => array(2, 3)
                    );

                    $existePerfilAdmin = $modelPessoaUsuario->buscaPerfinsUsuario($where);

                    if (count($existePerfilAdmin) > 0) {
                        $retorno['error'] = utf8_encode($pf[0]['NM_PESSOA_FISICA'] . ' É administrador do sistema!');
                    }

                    if ($tipoVinculo == 'O') {

                        $where = array(
                            'pv.ID_PESSOA_VINCULADA = ?' => $idPessoa,
                            'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17,
                            'pv.ST_PESSOA_VINCULADA = ?' => 'A'
                        );

                        $eResponsavelOperadora = $modelPessoaVinculada->buscarDadosResponsavel($where);

                        if (count($eResponsavelOperadora) > 0) {
                            $retorno['error'] = utf8_encode($pf[0]['NM_PESSOA_FISICA'] . ' já é responsável por uma operadora!');
                        }

                        if (isset($idEmpresa) && !empty($idEmpresa)) {

                            $where = array(
                                'pv.ID_PESSOA = ?' => $idEmpresa,
                                'pv.ID_PESSOA_VINCULADA = ?' => $idPessoa,
                                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 17,
                            );

                            $eResponsavelOperador2 = $modelPessoaVinculada->buscarDadosResponsavel($where);

                            if (count($eResponsavelOperador2) > 0) {
                                $retorno['error'] = utf8_encode($pf[0]['NM_PESSOA_FISICA'] . ' já é responsável por esta operadora!');
                            }

                        }
                    }

                    if ($tipoVinculo == 'BN') {

                        if (isset($idEmpresa) && !empty($idEmpresa)) {

                            $where = array(
                                'pv.ID_PESSOA = ?' => $idEmpresa,
                                'pv.ID_PESSOA_VINCULADA = ?' => $idPessoa,
                                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16
                            );

                            $eResponsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

                            if (count($eResponsavel) > 0) {
                                $retorno['error'] = utf8_encode($pf[0]['NM_PESSOA_FISICA'] . ' já é responsável por esta beneficiária!');
                            }
                        }
                    }
                } else {
                    $retorno['error'] = utf8_encode('Pessoa não encontrada!');
                }

            }

        } catch (InvalidArgumentException $exc) {
            $retorno['error'] = utf8_encode('Pessoa não encontrada!');
        } catch (Exception $exc) {
            $retorno['error'] = utf8_encode('Pessoa não encontrada!');
        }

        echo json_encode($retorno);
    }

    function validaCPF($cpf)
    {

        // Verifica se o número digitado contém todos os digitos
        $cpf = str_pad(preg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);

        if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
            return false;
        } else {

            // Calcula os números para verificar se o CPF são verdadeiro
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }

                $d = ((10 * $d) % 11) % 10;

                if ($cpf{$c} != $d) {
                    return false;
                }
            }

            return true;
        }
    }

    function isValidDate($date, $format = 'Y-m-d')
    {
        if (is_numeric(str_replace('-', '', $date))) {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        } else {
            return false;
        }
    }

    function isCnpjValid($cnpj)
    {
        // Etapa 1: Cria um array com apenas os digitos numéricos,
        // Isso permite receber o cnpj em diferentes formatos como:
        // "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
        $num = array();
        $j = 0;
        for ($i = 0; $i < (strlen($cnpj)); $i++) {
            if (is_numeric($cnpj[$i])) {
                $num[$j] = $cnpj[$i];
                $j++;
            }
        }

        //Etapa 2: Conta os dígitos, um Cnpj válido possui 14 dígitos numéricos.
        if (count($num) != 14) {
            return false;
        }

        //Etapa 3: O número 00000000000 embora não seja um cnpj real resultaria um cnpj válido
        // após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
        if ($num[0] == 0 && $num[1] == 0 && $num[2] == 0 && $num[3] == 0 && $num[4] == 0 && $num[5] == 0 && $num[6] == 0 && $num[7] == 0 && $num[8] == 0 && $num[9] == 0 && $num[10] == 0 && $num[11] == 0) {
            return false;
        } //Etapa 4: Calcula e compara o primeiro dígito verificador.
        else {
            $j = 5;
            for ($i = 0; $i < 4; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 4; $i < 12; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            if ($dg != $num[12]) {
                return false;
            }
        }

        //Etapa 5: Calcula e compara o segundo dígito verificador.
        if (!isset($isCnpjValid)) {
            $j = 6;
            for ($i = 0; $i < 5; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 5; $i < 13; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            if ($dg != $num[13]) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     *
     */
    private function _adionarResponsavel($arDados)
    {
//        xd($arDados);
        $idPessoaFisica = $arDados['idPessoaFisica'];
        $idPessoaJuridica = $arDados['idPessoaJuridica'];
        $nrCpf = $arDados['nrCpf'];
        $cdCbo = $arDados['cdCbo'];
        $cdDDD = $arDados['cdDDD'];
        $nrTelefone = $arDados['nrTelefone'];
        $nrFax = $arDados['nrFax'];
        $cdDDDFax = $arDados['cdDDDFax'];
        $dsEmail = $arDados['dsEmail'];
        $tpVinculoPessoa = $arDados['tpVinculoPessoa'];

        $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();
        $modelTelefone = new Application_Model_Telefone();
        $modelEmail = new Application_Model_Email();
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelUsuario = new Application_Model_Usuario();
        $modelUsuarioPerfil = new Application_Model_UsuarioPerfil();
        $modelSituacao = new Application_Model_Situacao();

        // Vincular o responsável
        // Pega o IDPF da página de cadastro

        //Verifica se já existe esse número cadastrado
        $where = array(
            'ID_PESSOA = ?' => $idPessoaFisica,
            'SG_PAIS = ?' => 'BRA',
            'NR_TELEFONE = ?' => $nrTelefone,
            'ID_TIPO_TELEFONE = ?' => 2,
            'CD_DDD = ?' => $cdDDD
        );
        $existeTelefone = $modelTelefone->select($where);

        if (count($existeTelefone) == 0) {
            //Inserindo na model Telefone
            $Cols = array(
                'ID_PESSOA' => $idPessoaFisica,
                'SG_PAIS' => 'BRA',
                'NR_TELEFONE' => $nrTelefone,
                'ID_TIPO_TELEFONE' => 2,
                'CD_DDD' => $cdDDD
            );

            $modelTelefone->insert($Cols);
        }

        if (strlen($nrFax) > 7) {
            //Verifica se já existe esse número cadastrado
            $where = array(
                'ID_PESSOA = ?' => $idPessoaFisica,
                'SG_PAIS = ?' => 'BRA',
                'NR_TELEFONE = ?' => $nrFax,
                'ID_TIPO_TELEFONE = ?' => 4,
                'CD_DDD = ?' => $cdDDDFax
            );
            $existeFax = $modelTelefone->select($where);

            if (count($existeFax) == 0) {
                //Inserindo na model Telefone
                $Cols = array(
                    'ID_PESSOA' => $idPessoaFisica,
                    'SG_PAIS' => 'BRA',
                    'NR_TELEFONE' => $nrFax,
                    'ID_TIPO_TELEFONE' => 4,
                    'CD_DDD' => $cdDDDFax
                );
                $modelTelefone->insert($Cols);
            }
        }

        // Verificar se já existe o email
        $where = array(
            'ID_PESSOA = ?' => $idPessoaFisica,
            'DS_EMAIL = ?' => $dsEmail,
            'ID_TIPO_EMAIL = ?' => 2
        );

        $existeEmail = $modelEmail->select($where);

        if (count($existeEmail) == 0) {

            //Inserindo Email do responsavel
            $Cols = array(
                'ID_PESSOA' => $idPessoaFisica,
                'DS_EMAIL' => $dsEmail,
                'ID_TIPO_EMAIL' => 2,
                'ST_EMAIL_PRINCIPAL' => 'S'
            );

            $modelEmail->insert($Cols);

        }
        //Inserindo CBO do responsavel
        if ($cdCbo) {

            // Verifica se já existe esse registro para não duplicar
            $whereCDCBO = array(
                'ID_PESSOA_FISICA = ?' => $idPessoaFisica,
                'ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica,
//                'CD_CBO = ?' => $cdCbo
            );

            $existeCDCBO = $modelCBOPessoaFisica->select($whereCDCBO);

            if (count($existeCDCBO) == 0) {

                $Cols = array(
                    'ID_PESSOA_FISICA' => $idPessoaFisica,
                    'ID_PESSOA_JURIDICA' => $idPessoaJuridica,
                    'CD_CBO' => $cdCbo
                );

                $modelCBOPessoaFisica->insert($Cols);
            }
        }

        //============== VINCULANDO EMPRESA E RESPONSAVEL ==================
        //Verifica se ja existe vinculo
        $where = array(
            'ID_PESSOA = ?' => $idPessoaJuridica,
            'ID_PESSOA_VINCULADA = ?' => $idPessoaFisica,
            'ID_TIPO_VINCULO_PESSOA = ?' => $tpVinculoPessoa
        );

        $vinculo = $modelPessoaVinculada->select($where);

        if (count($vinculo) < 1) {
            $Cols = array(
                'ID_PESSOA' => $idPessoaJuridica,
                'ID_PESSOA_VINCULADA' => $idPessoaFisica,
                'ID_TIPO_VINCULO_PESSOA' => $tpVinculoPessoa
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
                'DS_LOGIN' => $nrCpf,
                'DS_SENHA' => md5($senha),
                'ID_PESSOA_FISICA' => $idPessoaFisica
            );
            $modelUsuario->insert($Cols);
            $enviaEmail = true;
        }

        //Verifica se usuario já tem o perfil
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

        //Cria Situação para a Operadora
        $Cols = array(
            'ID_PESSOA' => $idPessoaJuridica,
            'ID_USUARIO' => $idUsuario,
            'ID_TIPO_SITUACAO' => 1,
            'TP_ENTIDADE_VALE_CULTURA' => 'O',
            'DS_JUSTIFICATIVA' => 'Cadastro realizado'
        );

        $modelSituacao->insert($Cols);

        if ($enviaEmail) {
            $htmlEmail = emailSenhaHTML();
            $htmlEmail = str_replace('#PERFIL#', 'Operadora', $htmlEmail);
            $htmlEmail = str_replace('#URL#', 'http://vale.cultura.gov.br', $htmlEmail);
            $htmlEmail = str_replace('#Senha#', $senha, $htmlEmail);
//            $enviarEmail = $modelEmail->enviarEmail($dsEmail, 'Acesso ao sistema Vale Cultura', $htmlEmail);
        }
    }
}