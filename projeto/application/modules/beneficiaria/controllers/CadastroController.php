<?php

include_once 'GenericController.php';

class Beneficiaria_CadastroController extends GenericController {

    public function init() {

        // Layout Padr�o
        $this->view->layout()->setLayout('layout');

        // T�tulo
        $this->view->assign('titulo', 'Beneficiadora');

        parent::init();
    }

    public function indexAction() {

        $this->getHelper('layout')->disableLayout();

        $modelCBO               = new Application_Model_CBO();
        $modelFaixaSalaria      = new Application_Model_TipoFaixaSalarial();
        $modelSituacao          = new Application_Model_Situacao();
        $modelTipoLucro         = new Application_Model_TipoLucro();
        $modelCNAE              = new Application_Model_CNAE();
        $modelNaturezaJuridica  = new Application_Model_NaturezaJuridica();

        $CBOs                   = $modelCBO->select(array(), 'NM_CBO');
        $faixasSalarial         = $modelFaixaSalaria->select();
        $operadorasAtivas       = $modelSituacao->selecionaOperadorasAtivas();
        $tipoLucro              = $modelTipoLucro->select(array('ID_TIPO_LUCRO != ?' => 2), 'DS_TIPO_LUCRO');
        $CNAEPrincipal          = $modelCNAE->select(array('NR_NIVEL_HIERARQUIA = ?' => 1), 'ID_CNAE');
        $NaturezaJuridica       = $modelNaturezaJuridica->select(array(), 'DS_NATUREZA_JURIDICA');

        $this->view->assign('CBOs', $CBOs);
        $this->view->assign('faixasSalarial', $faixasSalarial);
        $this->view->assign('operadorasAtivas', $operadorasAtivas);
        $this->view->assign('tipoLucro', $tipoLucro);
        $this->view->assign('CNAEPrincipal', $CNAEPrincipal);
        $this->view->assign('naturezaJuridica', $NaturezaJuridica);
    }

    public function cadastrarAction() {
        if ($_POST) {

            $this->getHelper('layout')->disableLayout();

            $modelPessoaJuridica = new Application_Model_PessoaJuridica();
            $modelPessoaJuridicaLucro = new Application_Model_PessoaJuridicaLucro();
            $modelEndereco = new Application_Model_Endereco();
            $modelLogradouro = new Application_Model_Logradouro();
            $modelBeneficiaria = new Application_Model_Beneficiaria();
            $modelPessoaVinculada = new Application_Model_PessoaVinculada();
            $modelTelefone = new Application_Model_Telefone();
            $modelEmail = new Application_Model_Email();
            $modelUsuario = new Application_Model_Usuario();
            $modelUsuarioPerfil = new Application_Model_UsuarioPerfil();
            $modelSituacao = new Application_Model_Situacao();
            $modelCBOPessoaFisica = new Application_Model_CBOPessoaFisica();
            $modelTrabalhadorFaixaSalarial = new Application_Model_FaixaSalarialBeneficiaria();
            $modelDDD = new Application_Model_DDD();
            $modelBeneficiariaHistorico = new Application_Model_BeneficiariaHistorico();
            $modelTrabalhadorFaixaSalarialHistorico = new Application_Model_FaixaSalarialBeneficiariaHistorico();

            //Recuperando form
            $IDPJ = $this->getRequest()->getParam('IDPJ');
            $IDPF = $this->getRequest()->getParam('IDPF');
            $IDENDERECO = $this->getRequest()->getParam('ID_ENDERECO');
            $IDLOGRADOURO = $this->getRequest()->getParam('ID_LOGRADOURO');
            $NRCEP = str_replace('-', '', $this->getRequest()->getParam('EMPRESA_CEP'));
            $NRCPF = str_replace('.', '', str_replace('-', '', $this->getRequest()->getParam('RESPONSAVEL_CPF')));
            $NRCNPJ = str_replace('/', '', str_replace('.', '', str_replace('-', '', $this->getRequest()->getParam('EMPRESA_CNPJ'))));
            $DSCOMPLEMENTOENDERECO = trim($this->getRequest()->getParam('EMPRESA_COMPLEMENTO'));
            $NRCOMPLEMENTO = trim($this->getRequest()->getParam('EMPRESA_NUMERO'));
            $DSLOGRAENDERECO = trim($this->getRequest()->getParam('EMPRESA_ENDERECO'));
            $IDBAIRRO = $this->getRequest()->getParam('EMPRESA_BAIRRO');
            $CDDDDFAX = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('RESPONSAVEL_FAX')))))), 0, 2);
            $NRFAX = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('RESPONSAVEL_FAX')))))), 2);
            $CDDDD = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('RESPONSAVEL_FONE')))))), 0, 2);
            $NRTELEFONE = (int) substr(str_replace('-', '', (str_replace(' ', '', str_replace('(', '', str_replace(')', '', $this->getRequest()->getParam('RESPONSAVEL_FONE')))))), 2);
            $DSEMAIL = trim($this->getRequest()->getParam('RESPONSAVEL_EMAIL'));
            $CDCBO = (int) $this->getRequest()->getParam('RESPONSAVEL_CARGO');
            $IDTIPOLUCRO = $this->getRequest()->getParam('EMPRESA_TIPO_LUCRO');
            $NMFANTASIA = $this->getRequest()->getParam('EMPRESA_NMFANTASIA');
            $IDOPERADORA = $this->getRequest()->getParam('EMPRESA_OPERADORA');
            $FAIXASALARIAL = $this->getRequest()->getParam('IDFAIXASALARIAL');
            $AUTORIZO_OPERADORA = $this->getRequest()->getParam('AUTORIZO_OPERADORA');
            $AUTORIZO_MINC = $this->getRequest()->getParam('AUTORIZO_MINC');

            // Validando Form
            $ERROR = array();

            // Validando as faixas salariais
            $erroFaixa = 1;
            foreach ($FAIXASALARIAL as $k => $v) {
                if ((int) $v > 0) {
                    $erroFaixa = 0;
                }
            }

            if ($erroFaixa == 1) {
                $ERROR['FAIXAS SALARIAIS'] = 'Informar pelo menos uma faixa salarial!';
            }

            if ($IDPJ == '0') {
                $ERROR['BENEFICI�RIA'] = 'CNPJ n�o encontrado!';
            }

            if ($IDPF == '0') {
                $ERROR['RESPONS�VEL'] = 'CPF n�o encontrado!';
            }

            if ($CDDDD) {

                $verificaDDD = $modelDDD->select(array('CD_DDD = ?' => $CDDDD));
                if (count($verificaDDD) == 0) {
                    $ERROR['DDD'] = 'DDD inv&aacute;lido!';
                }
            }

            if (!$this->getRequest()->getParam('ConfimaLei')) {
                $ERROR['LEI'] = 'Confirme a veracidade de todas as informa��es';
            }

            $logradouro = $modelLogradouro->selectEndereco(array('NR_CEP = ?' => "" . $NRCEP . ""));

            if (count($logradouro) < 1 || strlen($NRCEP) != 8) {
                $ERROR['CEP'] = 'CEP inv�lido';
            } else {
                $IDLOGRADOURO = $logradouro[0]['ID_LOGRADOURO'];
                $STLOGRADOURO = $logradouro[0]['ST_LOGRADOURO'];
                $IDBAIRRO = (!empty($IDBAIRRO)) ? $IDBAIRRO : $logradouro[0]['ID_BAIRRO_INICIO'];
            }

            if (!validaCPF($NRCPF)) {
                $ERROR['CPF'] = 'CPF inv�lido';
            }

            if (!validaCNPJ($NRCNPJ)) {
                $ERROR['CNPJ'] = 'CNPJ inv�lido';
            }

            if (strlen($NRTELEFONE) < 8) {
                $ERROR['TELEFONE'] = 'Informe o telefone';
            }

            if (!$IDTIPOLUCRO) {
                $ERROR['TIPOLUCRO'] = 'Informe o tipo de tributa��o';
            }

            if (!$IDOPERADORA) {
                $ERROR['OPERADORA'] = 'Informe a operadora';
            }

            if (!$DSEMAIL) {
                $ERROR['EMAIL'] = 'Informe o e-mail';
            }

            if (!validaEmail($DSEMAIL)) {
                $ERROR['EMAIL'] = 'Email inv�lido!';
            }

            if ($CDCBO < 1) {
                $ERROR['CBO'] = 'Informe o cargo';
            }

            if (isset($_POST['captcha'])) {

                // Instancia novamente um captcha para validar os dados enviados
                $captcha = new Zend_Captcha_Image();

                if ($captcha->isValid($this->getRequest()->getParam('captcha'))) {
                    //$this->view->assign('msg', 'captcha ok');
                } else {
                    $ERROR['VALIDADOR'] = 'C�digo captcha incorreto';
                }
            } else {
                $ERROR['VALIDADOR'] = 'Informe o c�digo verificador';
            }

            if (count($ERROR) > 0) {
                $this->view->error = $ERROR;
                return;
            } else {

                $db = Zend_Db_Table::getDefaultAdapter();
                $db->beginTransaction();

                try {

                    // O ID j� vem do cadastro
                    $idPessoaJuridica = $IDPJ;

                    // Passo 0 - Alterar nome fantasi
                    if(trim($NMFANTASIA) != ''){
                        $Cols = array(
                            'NM_FANTASIA' => $NMFANTASIA
                        );

                        $modelPessoaJuridica->update($Cols, array('ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica));
                    }

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

                    // Passo 2 - Endere�o
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

                    // Pega a situa��o da benefici�ria tentando se cadastrar
                    // para verificar se ela est� em situa��o inativa ou n�o autorizada
                    // nessas situa��es � permitido o recadastramento
                    $eBeneficiariaInativa = $modelSituacao->buscarSituacao(array('ID_PESSOA = ?' => $idPessoaJuridica, 'TP_ENTIDADE_VALE_CULTURA = ?' => 'B'));
                    // Passo 3 - Salvando os dados como Benefici�ria
                    if(count($eBeneficiariaInativa) > 0 &&
                        ($eBeneficiariaInativa[0]['idTipoSituacao'] == '3' ||
                        $eBeneficiariaInativa[0]['idTipoSituacao'] == '4')) {
                        $beneficiariaInativa = $modelBeneficiaria->find($idPessoaJuridica);
                        $modelBeneficiariaHistorico->insert(array(
                            'DT_HISTORICO' => new Zend_Db_Expr('getdate()'),
                            'ID_BENEFICIARIA' => $beneficiariaInativa['ID_BENEFICIARIA'],
                            'ID_OPERADORA' => $beneficiariaInativa['ID_OPERADORA'],
                            'DT_INSCRICAO' => $beneficiariaInativa['DT_INSCRICAO'],
                            'NR_COMPROVANTE_INSCRICAO' => $beneficiariaInativa['NR_COMPROVANTE_INSCRICAO'],
                            'NR_CERTIFICADO' => $beneficiariaInativa['NR_CERTIFICADO'],
                            'ST_DIVULGAR_DADOS' => $beneficiariaInativa['ST_DIVULGAR_DADOS'],
                            'ST_ATUALIZADO_OPERADORA' => $beneficiariaInativa['ST_ATUALIZADO_OPERADORA'],
                            'ST_AUTORIZA_MINC' => $beneficiariaInativa['ST_AUTORIZA_MINC']
                        ));

                        $Cols = array(
                            'ID_BENEFICIARIA' => $idPessoaJuridica,
                            'ID_OPERADORA' => $IDOPERADORA == 'N' ? new Zend_Db_Expr('NULL') : $IDOPERADORA,
                            'DT_INSCRICAO' => new Zend_Db_Expr('getdate()'),
                            'ST_DIVULGAR_DADOS' => (int) $AUTORIZO_OPERADORA,
                            'ST_AUTORIZA_MINC' => $AUTORIZO_MINC ? 1 : 2
                        );
                        $modelBeneficiaria->update($Cols, $idPessoaJuridica);
                    }else if(count($eBeneficiariaInativa) === 0){
                        $Cols = array(
                            'ID_BENEFICIARIA' => $idPessoaJuridica,
                            'ID_OPERADORA' => $IDOPERADORA == 'N' ? new Zend_Db_Expr('NULL') : $IDOPERADORA,
                            'ST_DIVULGAR_DADOS' => (int) $AUTORIZO_OPERADORA,
                            'ST_AUTORIZA_MINC' => $AUTORIZO_MINC ? 1 : 2
                        );
                        $modelBeneficiaria->insert($Cols);
                    }

                    // Passo 4 - Cadastra Faixa Salarial
                    if(count($eBeneficiariaInativa) > 0 &&
                        ($eBeneficiariaInativa[0]['idTipoSituacao'] == '3' ||
                        $eBeneficiariaInativa[0]['idTipoSituacao'] == '4')){

                        foreach ($FAIXASALARIAL as $k => $v) {
                            if((int) $v > 0){
                                $Cols = array(
                                    'DT_HISTORICO' => new Zend_Db_Expr('getdate()'),
                                    'ID_BENEFICIARIA' => $idPessoaJuridica,
                                    'ID_TIPO_FAIXA_SALARIAL' => $k,
                                    'QT_TRABALHADOR_FAIXA_SALARIAL' => (int) $v
                                );
                                $modelTrabalhadorFaixaSalarialHistorico->insert($Cols);
                            }
                        }
                        foreach ($FAIXASALARIAL as $k => $v) {
                            if ((int) $v > 0) {
                                $Cols = array(
                                    'QT_TRABALHADOR_FAIXA_SALARIAL' => (int) $v
                                );
                                $modelTrabalhadorFaixaSalarial->update($Cols, $idPessoaJuridica, $k);
                            }
                        }
                    }else if(count($eBeneficiariaInativa) === 0){
                        foreach ($FAIXASALARIAL as $k => $v) {
                            if ((int) $v > 0) {
                                $Cols = array(
                                    'ID_BENEFICIARIA' => $idPessoaJuridica,
                                    'ID_TIPO_FAIXA_SALARIAL' => $k,
                                    'QT_TRABALHADOR_FAIXA_SALARIAL' => (int) $v
                                );
                                $modelTrabalhadorFaixaSalarial->insert($Cols);
                            }
                        }
                    }

                    // Vincular o respons�vel
                    // Pega o IDPF da p�gina de cadastro
                    $idPessoaFisica = $IDPF;

                    // Passo 5 - Salvar dados do CDCBO
                    if ($CDCBO) {

                        // Verifica se j� existe esse registro para n�o duplicar
                        $whereCDCBO = array(
                            'ID_PESSOA_FISICA = ?' => $idPessoaFisica,
                            'ID_PESSOA_JURIDICA = ?' => $idPessoaJuridica,
                            'CD_CBO = ?' => $CDCBO
                        );

                        $existeCDCBO = $modelCBOPessoaFisica->select($whereCDCBO);

                        if (count($existeCDCBO) == 0) {

                            $Cols = array(
                                'ID_PESSOA_FISICA' => $idPessoaFisica,
                                'ID_PESSOA_JURIDICA' => $idPessoaJuridica,
                                'CD_CBO' => $CDCBO
                            );

                            $modelCBOPessoaFisica->insert($Cols);
                        }
                    }

                    // Passo 6 - Salvando o Telefone
                    if ($NRTELEFONE) {
                        $where = array(
                            'ID_PESSOA = ?' => $idPessoaFisica,
                            'NR_TELEFONE = ?' => $NRTELEFONE,
                            'SG_PAIS = ?' => 'BRA',
                            'ID_TIPO_TELEFONE = ?' => 2,
                            'CD_DDD = ?' => $CDDDD
                        );

                        $existeTelefone = $modelTelefone->select($where);

                        if (count($existeTelefone) == 0) {

                            $Cols = array(
                                'ID_PESSOA' => $idPessoaFisica,
                                'NR_TELEFONE' => $NRTELEFONE,
                                'SG_PAIS' => 'BRA',
                                'ID_TIPO_TELEFONE' => 2,
                                'CD_DDD' => $CDDDD
                            );

                            $modelTelefone->insert($Cols);
                        }
                    }

                    // Passo 7 - Salvando o Fax
                    if ($NRFAX) {
                        $where = array(
                            'ID_PESSOA = ?' => $idPessoaFisica,
                            'NR_TELEFONE = ?' => $NRFAX,
                            'SG_PAIS = ?' => 'BRA',
                            'ID_TIPO_TELEFONE = ?' => 4,
                            'CD_DDD = ?' => $CDDDDFAX
                        );

                        $existeFax = $modelTelefone->select($where);

                        if (count($existeFax) == 0) {
                            $Cols = array(
                                'ID_PESSOA' => $idPessoaFisica,
                                'NR_TELEFONE' => $NRFAX,
                                'SG_PAIS' => 'BRA',
                                'ID_TIPO_TELEFONE' => 4,
                                'CD_DDD' => $CDDDDFAX
                            );

                            $modelTelefone->insert($Cols);
                        }
                    }

                    // Passo 8 - Salvando o Email
                    if ($DSEMAIL) {
                        $where = array(
                            'ID_PESSOA = ?' => $idPessoaFisica,
                            'DS_EMAIL = ?' => $DSEMAIL,
                            'ID_TIPO_EMAIL = ?' => 2
                        );

                        $existeEmail = $modelEmail->select($where);

                        if (count($existeEmail) == 0) {

                            $Cols = array(
                                'ID_PESSOA' => $idPessoaFisica,
                                'DS_EMAIL' => $DSEMAIL,
                                'ID_TIPO_EMAIL' => 2,
                                'ST_EMAIL_PRINCIPAL' => 'S'
                            );

                            $modelEmail->insert($Cols);
                        }
                    }


                    //============== VINCULANDO EMPRESA E RESPONSAVEL ==================
                    //Verifica se ja existe vinculo

                    $where = array(
                        'ID_PESSOA = ?' => $idPessoaJuridica,
                        'ID_PESSOA_VINCULADA = ?' => $idPessoaFisica,
                        'ID_TIPO_VINCULO_PESSOA = ?' => 16
                    );

                    $vinculo = $modelPessoaVinculada->select($where);

                    // Passo 9 - Vinculando o respons�vel a empresa
                    if (count($vinculo) < 1) {

                        $Cols = array(
                            'ID_PESSOA' => $idPessoaJuridica,
                            'ID_PESSOA_VINCULADA' => $idPessoaFisica,
                            'ID_TIPO_VINCULO_PESSOA' => 16
                        );

                        $modelPessoaVinculada->insert($Cols);
                    }

                    //==================== CRIANDO USUARIO =============================
                    $usuario = $modelUsuario->select(array('ID_PESSOA_FISICA = ?' => $idPessoaFisica));

                    // Passo 10 - Criando um usu�rio para o respons�vel
                    if (count($usuario) > 0) {
                        $idUsuario = $usuario[0]['ID_USUARIO'];
                        $enviaEmailSenha = false;
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
                        $enviaEmailSenha = true;
                    }

                    //Verifica se usuario j� tem o perfil
                    $where = array(
                        'ID_USUARIO = ?' => $idUsuario,
                        'ID_PERFIL = ?' => 2
                    );

                    $usuarioPerfil = $modelUsuarioPerfil->select($where);

                    // Passo 11 - Criando um perfil
                    if (count($usuarioPerfil) < 1) {
                        $Cols = array(
                            'ID_USUARIO' => $idUsuario,
                            'ID_PERFIL' => 2
                        );
                        $modelUsuarioPerfil->insert($Cols);
                    }

                    // Passo 12 - Cria Situa��o para a Beneficiaria
                    $Cols = array(
                        'ID_PESSOA' => $idPessoaJuridica,
                        'ID_USUARIO' => $idUsuario,
                        'DS_JUSTIFICATIVA' => 'Cadastro realizado',
                        'ID_TIPO_SITUACAO' => 1,
                        'TP_ENTIDADE_VALE_CULTURA' => 'B'
                    );

                    $modelSituacao->insert($Cols);

                    $db->commit();

                    // Passo 13 - Enviar email para o respons�vel
                    if ($enviaEmailSenha) {
                        $htmlEmail = emailSenhaHTML();
                        $htmlEmail = str_replace('#PERFIL#', 'Benefici�ria', $htmlEmail);
                        $htmlEmail = str_replace('#URL#', 'http://vale.cultura.gov.br/', $htmlEmail);
                        $htmlEmail = str_replace('#Senha#', $senha, $htmlEmail);
                        $enviarEmail = $modelEmail->enviarEmail($DSEMAIL, 'Acesso ao sistema Vale Cultura', $htmlEmail);
                    } else {
                        $htmlEmail = emailNoSenhaHTML();
                        $htmlEmail = str_replace('#PERFIL#', 'Benefici�ria', $htmlEmail);
                        $htmlEmail = str_replace('#URL#', 'http://vale.cultura.gov.br/', $htmlEmail);
                        $enviarEmail = $modelEmail->enviarEmail($DSEMAIL, 'Acesso ao sistema Vale Cultura', $htmlEmail);
                    }

                    $sucesso['CADASTRO'] = "Benefici�ria cadastrada com sucesso!";
                    $sucesso['DSEMAIL'] = $DSEMAIL;
                    $this->view->sucesso = $sucesso;
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                    $db->rollBack();
                    $ERROR['CADASTRO'] = "Houve um erro no cadastro";
                    $this->view->error = $ERROR;
                }
            }
        }
    }

    public function gerarcaptchaAction() {
        $this->getHelper('layout')->disableLayout();
        $captcha = new Zend_Captcha_Image(); // Este � o nome da classe, no secrets...
        $captcha->setWordlen(5) // quantidade de letras, tente inserir outros valores
                ->setImgDir('imagens/captcha')// o caminho para armazenar as imagens
                ->setGcFreq(10)//especifica a cada quantas vezes o garbage collector vai rodar para eliminar as imagens inv�lidas
                ->setExpiration(600000)// tempo de expira��o em segundos.
                ->setHeight(70) // tamanho da imagem de captcha
                ->setWidth(200)// largura da imagem
                ->setLineNoiseLevel(1) // o nivel das linhas, quanto maior, mais dificil fica a leitura
                ->setDotNoiseLevel(2)// nivel dos pontos, experimente valores maiores
                ->setFontSize(15)//tamanho da fonte em pixels
                ->setFont('font/arial.ttf'); // caminho para a fonte a ser usada
        $this->view->idCaptcha = $captcha->generate(); // passamos aqui o id do captcha para a view
        $this->view->captcha = $captcha->render($this->view); // e o proprio captcha para a view
    }

    public function recuperaSegundoNivelCnaeAction() {
        $this->getHelper('layout')->disableLayout();

        $modelCNAE = new Application_Model_CNAE();
        $IDCNAE = $this->getRequest()->getParam('IDCNAE');
        $retorno = array();
        if ($IDCNAE) {
            $where = array();
            $where['NR_NIVEL_HIERARQUIA = ?'] = 2;
            $where['ID_CNAE_HIERARQUIA = ?'] = $IDCNAE;
            $CNAESecundario = $modelCNAE->select($where, 'ID_CNAE');
            if ($CNAESecundario > 0) {
                $retorno['CNAEs'] = $CNAESecundario;
                //$retorno['error'] = false;
            } else {
                $retorno['error'] = true;
                $retorno['dsError'] = 'N�o foi localizado Informar CNAE principal';
            }
        } else {
            $retorno['error'] = true;
            $retorno['dsError'] = 'Informar CNAE principal';
        }
        echo json_encode(convertArrayKeysToUtf8($retorno));
    }

}

