<?php

include_once "GenericController.php";

class Beneficiaria_CadastroController extends GenericController
{

    public function init()
    {

        // Layout Padrão
        $this->view->layout()->setLayout("layout");

        // Título
        $this->view->assign("titulo", "Beneficiadora");

        parent::init();
    }

    public function indexAction()
    {

        $this->getHelper("layout")->disableLayout();

        $modelCBO = new Application_Model_CBO();
        $modelFaixaSalaria = new Application_Model_TipoFaixaSalarial();
        $modelSituacao = new Application_Model_Situacao();
        $modelTipoLucro = new Application_Model_TipoLucro();
        $modelCNAE = new Application_Model_CNAE();
        $modelNaturezaJuridica = new Application_Model_NaturezaJuridica();

        $CBOs = $modelCBO->select(array(), "NM_CBO");
        $faixasSalarial = $modelFaixaSalaria->select();
        $operadorasAtivas = $modelSituacao->selecionaOperadorasAtivas();
        $tipoLucro = $modelTipoLucro->select(array("ID_TIPO_LUCRO != ?" => 2), "DS_TIPO_LUCRO");
        $CNAEPrincipal = $modelCNAE->select(array("NR_NIVEL_HIERARQUIA = ?" => 1), "ID_CNAE");
        $NaturezaJuridica = $modelNaturezaJuridica->select(array(), "DS_NATUREZA_JURIDICA");

        $this->view->assign("CBOs", $CBOs);
        $this->view->assign("faixasSalarial", $faixasSalarial);
        $this->view->assign("operadorasAtivas", $operadorasAtivas);
        $this->view->assign("tipoLucro", $tipoLucro);
        $this->view->assign("CNAEPrincipal", $CNAEPrincipal);
        $this->view->assign("naturezaJuridica", $NaturezaJuridica);
    }

    public function cadastrarAction()
    {

        if ($_POST) {

            $this->getHelper("layout")->disableLayout();

            $modelPessoaJuridica = new Application_Model_PessoaJuridica();
            $modelPessoaJuridicaLucro = new Application_Model_PessoaJuridicaLucro();
            $modelEndereco = new Application_Model_Endereco();
            $modelLogradouro = new Application_Model_Logradouro();
            $modelBeneficiaria = new Application_Model_Beneficiaria();
            $modelArquivoBeneficiaria = new Application_Model_ArquivoBeneficiaria();

            $modelSituacao = new Application_Model_Situacao();

            $modelTrabalhadorFaixaSalarial = new Application_Model_FaixaSalarialBeneficiaria();
            $modelDDD = new Application_Model_DDD();
            $modelBeneficiariaHistorico = new Application_Model_BeneficiariaHistorico();
            $modelTrabalhadorFaixaSalarialHistorico = new Application_Model_FaixaSalarialBeneficiariaHistorico();

            //Recuperando form
            $IDPJ = $this->getRequest()->getParam("IDPJ");
            $IDPF = $this->getRequest()->getParam("IDPF");
            $IDENDERECO = $this->getRequest()->getParam("ID_ENDERECO");
            $IDLOGRADOURO = $this->getRequest()->getParam("ID_LOGRADOURO");
            $NRCEP = str_replace("-", "", $this->getRequest()->getParam("EMPRESA_CEP"));
            $NRCPF = retornaDigitos($this->getRequest()->getParam("RESPONSAVEL_CPF"));
            $NRCNPJ = retornaDigitos($this->getRequest()->getParam("EMPRESA_CNPJ"));
            $DSCOMPLEMENTOENDERECO = trim($this->getRequest()->getParam("EMPRESA_COMPLEMENTO"));
            $NRCOMPLEMENTO = trim($this->getRequest()->getParam("EMPRESA_NUMERO"));
            $DSLOGRAENDERECO = trim($this->getRequest()->getParam("EMPRESA_ENDERECO"));
            $IDBAIRRO = $this->getRequest()->getParam("EMPRESA_BAIRRO");

            $ARFAX = explode(" ", $this->getRequest()->getParam("RESPONSAVEL_FAX"));
            $CDDDDFAX = retornaDigitos($ARFAX[0]);
            $NRFAX = retornaDigitos($ARFAX[1]);

            $ARTEL = explode(" ", $this->getRequest()->getParam("RESPONSAVEL_FONE"));
            $CDDDD = retornaDigitos($ARTEL[0]);
            $NRTELEFONE = retornaDigitos($ARTEL[1]);

            $DSEMAIL = trim($this->getRequest()->getParam("RESPONSAVEL_EMAIL"));
            $CDCBO = (int)$this->getRequest()->getParam("RESPONSAVEL_CARGO");
            $IDTIPOLUCRO = $this->getRequest()->getParam("EMPRESA_TIPO_LUCRO");
            $NMFANTASIA = $this->getRequest()->getParam("EMPRESA_NMFANTASIA");
            $IDOPERADORA = $this->getRequest()->getParam("EMPRESA_OPERADORA");
            $FAIXASALARIAL = $this->getRequest()->getParam("IDFAIXASALARIAL");
            $AUTORIZO_OPERADORA = $this->getRequest()->getParam("AUTORIZO_OPERADORA");
            $AUTORIZO_MINC = $this->getRequest()->getParam("AUTORIZO_MINC");
            $OPERADORA_VALE = $this->getRequest()->getParam("OPERADORA_VALE");
            $ST_AUTORIZA_VALE_FUNC = $this->getRequest()->getParam("AUTORIZAR_VALE_TODOS_FUNCIONARIOS");

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

            // Validando as faixas salariais
            $erroFaixa = 1;
            foreach ($FAIXASALARIAL as $k => $v) {
                if ((int)$v > 0) {
                    $erroFaixa = 0;
                }
            }

            if ($erroFaixa == 1) {
                $ERROR["FAIXAS SALARIAIS"] = "Informar pelo menos uma faixa salarial!";
            }

            if ($IDPJ == "0") {
                $ERROR["BENEFICIÁRIA"] = "CNPJ não encontrado!";
            }

            if ($IDPF == "0") {
                $ERROR["RESPONSÁVEL"] = "CPF não encontrado!";
            }

            if ($CDDDD) {

                $verificaDDD = $modelDDD->select(array("CD_DDD = ?" => $CDDDD));
                if (count($verificaDDD) == 0) {
                    $ERROR["DDD"] = "DDD inv&aacute;lido!";
                }
            }

            if (!$this->getRequest()->getParam("ConfimaRegras")) {
                $ERROR["PROGRAMA"] = "Confirme as regras do Programa de Cultura do Trabalhador";
            }

            if (!$this->getRequest()->getParam("ConfimaSecult")) {
                $ERROR["SECULT"] = "Confirme a autorização a SECULT";
            }

            if (!$this->getRequest()->getParam("ConfimaLei")) {
                $ERROR["LEI"] = "Confirme a veracidade de todas as informações";
            }

            $logradouro = $modelLogradouro->selectEndereco(array("NR_CEP = ?" => "" . $NRCEP . ""));

            if (count($logradouro) < 1 || strlen($NRCEP) != 8) {
                $ERROR["CEP"] = "CEP inválido";
            } else {
                $IDLOGRADOURO = $logradouro[0]["ID_LOGRADOURO"];
                $STLOGRADOURO = $logradouro[0]["ST_LOGRADOURO"];
                $IDBAIRRO = (!empty($IDBAIRRO)) ? $IDBAIRRO : $logradouro[0]["ID_BAIRRO_INICIO"];
            }

            if (!validaCPF($NRCPF)) {
                $ERROR["CPF"] = "CPF inválido";
            }

            if (!validaCNPJ($NRCNPJ)) {
                $ERROR["CNPJ"] = "CNPJ inválido";
            }

            if (strlen($NRTELEFONE) < 8) {
                $ERROR["TELEFONE"] = "Informe o telefone";
            }

            if (!$IDTIPOLUCRO) {
                $ERROR["TIPOLUCRO"] = "Informe o tipo de tributação";
            }

            if (!$IDOPERADORA) {
                $ERROR["OPERADORA"] = "Informe a operadora";
            }

            if (!$DSEMAIL) {
                $ERROR["EMAIL"] = "Informe o e-mail";
            }

            if (!validaEmail($DSEMAIL)) {
                $ERROR["EMAIL"] = "Email inválido!";
            }

            if ($CDCBO < 1) {
                $ERROR["CBO"] = "Informe o cargo";
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

            if (isset($_POST["captcha"])) {
                // Instancia novamente um captcha para validar os dados enviados
                $captcha = new Zend_Captcha_Image();

                if (!$captcha->isValid($this->getRequest()->getParam("captcha"))) {
                    $ERROR["VALIDADOR"] = "Código captcha incorreto";
                }
            } else {
                $ERROR["VALIDADOR"] = "Informe o código verificador";
            }

            foreach ($_FILES as $k => $v) {
                if ($k == 'ANEXO_11') {
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

            if (count($ERROR) > 0) {
                $this->view->error = $ERROR;
                return;
            }

            $db = Zend_Db_Table::getDefaultAdapter();

            try {

                $db->beginTransaction();

                // O ID já vem do cadastro
                $idPessoaJuridica = $IDPJ;

                // Passo 0 - Alterar nome fantasi
                if (trim($NMFANTASIA) != "") {
                    $Cols = array(
                        "NM_FANTASIA" => $NMFANTASIA
                    );

                    $modelPessoaJuridica->update($Cols, array("ID_PESSOA_JURIDICA = ?" => $idPessoaJuridica));
                }

                // Passo 1 - Pessoa Juridica Lucro
                $where = array("ID_PESSOA_JURIDICA = ?" => $idPessoaJuridica);

                $existePessoaJuridicaLucro = $modelPessoaJuridicaLucro->select($where);

                $Cols = array(
                    "ID_PESSOA_JURIDICA" => $idPessoaJuridica,
                    "ID_TIPO_LUCRO" => $IDTIPOLUCRO
                );

                if (count($existePessoaJuridicaLucro) == 0) {
                    $modelPessoaJuridicaLucro->insert($Cols);
                } else {
                    $modelPessoaJuridicaLucro->update($Cols, array("ID_PESSOA_JURIDICA = ?" => $idPessoaJuridica));
                }

                // Passo 2 - Endereço
                $Cols = array(
                    "ID_PESSOA" => $idPessoaJuridica,
                    "CD_TIPO_ENDERECO" => "01",
                    "ID_SUBDISTRITO_IBGE" => new Zend_Db_Expr("NULL"),
                    "ID_LOGRADOURO" => $IDLOGRADOURO,
                    "ID_BAIRRO" => $IDBAIRRO,
                    "DS_COMPLEMENTO_ENDERECO" => $DSCOMPLEMENTOENDERECO,
                    "NR_COMPLEMENTO" => $NRCOMPLEMENTO,
                    "DS_LOGRA_ENDERECO" => new Zend_Db_Expr("NULL"),
                    "DS_BAIRRO_ENDERECO" => new Zend_Db_Expr("NULL"),
                    "ID_SERVICO" => 1
                );

                if ($IDENDERECO == "0") {
                    $modelEndereco->insert($Cols);
                } else {
                    $modelEndereco->update($Cols, $IDENDERECO);
                }

                // Pega a situação da beneficiária tentando se cadastrar
                // para verificar se ela está em situação inativa ou não autorizada
                // nessas situações é permitido o recadastramento
                $eBeneficiariaInativa = $modelSituacao->buscarSituacao(array("ID_PESSOA = ?" => $idPessoaJuridica, "TP_ENTIDADE_VALE_CULTURA = ?" => "B"));
                // Passo 3 - Salvando os dados como Beneficiária
                if (count($eBeneficiariaInativa) > 0 &&
                    ($eBeneficiariaInativa[0]["idTipoSituacao"] == "3" ||
                        $eBeneficiariaInativa[0]["idTipoSituacao"] == "4")) {
                    $beneficiariaInativa = $modelBeneficiaria->find($idPessoaJuridica);
                    $modelBeneficiariaHistorico->insert(array(
                        "DT_HISTORICO" => new Zend_Db_Expr("getdate()"),
                        "ID_BENEFICIARIA" => $beneficiariaInativa["ID_BENEFICIARIA"],
                        "ID_OPERADORA" => $beneficiariaInativa["ID_OPERADORA"],
                        "DT_INSCRICAO" => $beneficiariaInativa["DT_INSCRICAO"],
                        "NR_COMPROVANTE_INSCRICAO" => $beneficiariaInativa["NR_COMPROVANTE_INSCRICAO"],
                        "NR_CERTIFICADO" => $beneficiariaInativa["NR_CERTIFICADO"],
                        "ST_DIVULGAR_DADOS" => $beneficiariaInativa["ST_DIVULGAR_DADOS"],
                        "ST_ATUALIZADO_OPERADORA" => $beneficiariaInativa["ST_ATUALIZADO_OPERADORA"],
                        "ST_AUTORIZA_MINC" => $beneficiariaInativa["ST_AUTORIZA_MINC"],
                        "ST_AUTORIZA_VALE_FUNC" => $ST_AUTORIZA_VALE_FUNC,
                        "ID_OPERADORA_AUTORIZADA" => $OPERADORA_VALE
                    ));

                    $Cols = array(
                        "ID_BENEFICIARIA" => $idPessoaJuridica,
                        "ID_OPERADORA" => $OPERADORA_VALE,
                        "DT_INSCRICAO" => new Zend_Db_Expr("getdate()"),
                        "ST_DIVULGAR_DADOS" => (int)$AUTORIZO_OPERADORA,
                        "ST_AUTORIZA_MINC" => $AUTORIZO_MINC ? 1 : 2,
                        "ST_AUTORIZA_VALE_FUNC" => $ST_AUTORIZA_VALE_FUNC,
                        "ID_OPERADORA_AUTORIZADA" => $OPERADORA_VALE
                    );
                    $modelBeneficiaria->update($Cols, $idPessoaJuridica);
                } else if (count($eBeneficiariaInativa) === 0) {
                    $Cols = array(
                        "ID_BENEFICIARIA" => $idPessoaJuridica,
                        "ID_OPERADORA" => $OPERADORA_VALE,
                        "ST_DIVULGAR_DADOS" => (int)$AUTORIZO_OPERADORA,
                        "ST_AUTORIZA_MINC" => $AUTORIZO_MINC ? 1 : 2,
                        "ST_AUTORIZA_VALE_FUNC" => $ST_AUTORIZA_VALE_FUNC,
                        "ID_OPERADORA_AUTORIZADA" => $OPERADORA_VALE
                    );
                    $modelBeneficiaria->insert($Cols);
                }

                // Passo 4 - Cadastra Faixa Salarial
                if (count($eBeneficiariaInativa) > 0 &&
                    ($eBeneficiariaInativa[0]["idTipoSituacao"] == "3" ||
                        $eBeneficiariaInativa[0]["idTipoSituacao"] == "4")) {

                    foreach ($FAIXASALARIAL as $k => $v) {
                        if ((int)$v > 0) {
                            $Cols = array(
                                "DT_HISTORICO" => new Zend_Db_Expr("getdate()"),
                                "ID_BENEFICIARIA" => $idPessoaJuridica,
                                "ID_TIPO_FAIXA_SALARIAL" => $k,
                                "QT_TRABALHADOR_FAIXA_SALARIAL" => (int)$v
                            );
                            $modelTrabalhadorFaixaSalarialHistorico->insert($Cols);
                        }
                    }
                    foreach ($FAIXASALARIAL as $k => $v) {
                        if ((int)$v > 0) {
                            $Cols = array(
                                "QT_TRABALHADOR_FAIXA_SALARIAL" => (int)$v
                            );
                            $modelTrabalhadorFaixaSalarial->update($Cols, $idPessoaJuridica, $k);
                        }
                    }
                } else if (count($eBeneficiariaInativa) === 0) {
                    foreach ($FAIXASALARIAL as $k => $v) {
                        if ((int)$v > 0) {
                            $Cols = array(
                                "ID_BENEFICIARIA" => $idPessoaJuridica,
                                "ID_TIPO_FAIXA_SALARIAL" => $k,
                                "QT_TRABALHADOR_FAIXA_SALARIAL" => (int)$v
                            );
                            $modelTrabalhadorFaixaSalarial->insert($Cols);
                        }
                    }
                }

                //Incluir Arquivo Operadora
                //SALVAR  UPLOAD
                $uploaddir = defined('UPLOAD_DIR') ? UPLOAD_DIR : "/var/arquivos/arquivos-valecultura/";

                foreach ($_FILES as $k => $v) {
                    if ($_FILES[$k]['error'] == 0) {
                        $mnArquivo = "{$idPessoaJuridica}_{$k}.pdf";
                        $uploadfile = $uploaddir . $mnArquivo;
                        $dsArquivo = $this->getRequest()->getParam($k . '_NOME');
                        if (move_uploaded_file($_FILES[$k]['tmp_name'], $uploadfile)) {

                            $Cols = array(
                                'ID_OPERADORA' => $idPessoaJuridica,
                                'DS_CAMINHO_ARQUIVO' => $mnArquivo,
                                'DS_ARQUIVO' => $dsArquivo
                            );

                            if ($k == 'ANEXO_11') {
                                $time = time();
                                $mnArquivo = "{$idPessoaJuridica}_{$k}_{$IDPF}_{$time}.pdf";
                                $dsArquivo = $this->getRequest()->getParam("{$k}_NOME_{$IDPF}");
                                $Cols = array(
                                    'ID_BENEFICIARIA' => $idPessoaJuridica,
                                    'DS_CAMINHO_ARQUIVO' => $mnArquivo,
                                    'DS_ARQUIVO' => $dsArquivo,
                                    'ID_RESPONSAVEL' => $IDPF,
                                );
                            }

                            $modelArquivoBeneficiaria->insert($Cols);
                        } else {
                            $db->rollBack();
                            $ERROR['ARQUIVO'] = "Erro ao salvar arquivo";
                            $this->view->error = $ERROR;
                            return;
                        }
                    }
                }

                ###### CADASTRO RESPONSÁVEL MINC ######
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
                $arDados['tpVinculoPessoa'] = 16;
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

                $sucesso["CADASTRO"] = "Beneficiária cadastrada com sucesso!";
                $sucesso["DSEMAIL"] = $DSEMAIL;

                $db->commit();

                $this->view->sucesso = $sucesso;
            } catch (Exception $exc) {
                $db->rollBack();
                xd($exc->getTraceAsString());
                $ERROR["CADASTRO"] = "Houve um erro no cadastro";
                $this->view->error = $ERROR;
            }
        }
    }

    public function gerarcaptchaAction()
    {
        $this->getHelper("layout")->disableLayout();
        $captcha = new Zend_Captcha_Image(); // Este é o nome da classe, no secrets...
        $captcha->setWordlen(5) // quantidade de letras, tente inserir outros valores
        ->setImgDir("imagens/captcha")// o caminho para armazenar as imagens
        ->setGcFreq(10)//especifica a cada quantas vezes o garbage collector vai rodar para eliminar as imagens inválidas
        ->setExpiration(600000)// tempo de expiração em segundos.
        ->setHeight(70) // tamanho da imagem de captcha
        ->setWidth(200)// largura da imagem
        ->setLineNoiseLevel(1) // o nivel das linhas, quanto maior, mais dificil fica a leitura
        ->setDotNoiseLevel(2)// nivel dos pontos, experimente valores maiores
        ->setFontSize(15)//tamanho da fonte em pixels
        ->setFont("font/arial.ttf"); // caminho para a fonte a ser usada
        $this->view->idCaptcha = $captcha->generate(); // passamos aqui o id do captcha para a view
        $this->view->captcha = $captcha->render($this->view); // e o proprio captcha para a view
    }

    public function recuperaSegundoNivelCnaeAction()
    {
        $this->getHelper("layout")->disableLayout();

        $modelCNAE = new Application_Model_CNAE();
        $IDCNAE = $this->getRequest()->getParam("IDCNAE");
        $retorno = array();
        if ($IDCNAE) {
            $where = array();
            $where["NR_NIVEL_HIERARQUIA = ?"] = 2;
            $where["ID_CNAE_HIERARQUIA = ?"] = $IDCNAE;
            $CNAESecundario = $modelCNAE->select($where, "ID_CNAE");
            if ($CNAESecundario > 0) {
                $retorno["CNAEs"] = $CNAESecundario;
                //$retorno["error"] = false;
            } else {
                $retorno["error"] = true;
                $retorno["dsError"] = "Não foi localizado Informar CNAE principal";
            }
        } else {
            $retorno["error"] = true;
            $retorno["dsError"] = "Informar CNAE principal";
        }
        echo json_encode(convertArrayKeysToUtf8($retorno));
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

        // Passo 5 - Salvar dados do CDCBO
        if ($cdCbo) {
            // Verifica se já existe esse registro para não duplicar
            $whereCDCBO = array(
                "ID_PESSOA_FISICA = ?" => $idPessoaFisica,
                "ID_PESSOA_JURIDICA = ?" => $idPessoaJuridica,
//                "CD_CBO = ?" => $cdCbo
            );

            $existeCDCBO = $modelCBOPessoaFisica->select($whereCDCBO);

            if (count($existeCDCBO) == 0) {

                $Cols = array(
                    "ID_PESSOA_FISICA" => $idPessoaFisica,
                    "ID_PESSOA_JURIDICA" => $idPessoaJuridica,
                    "CD_CBO" => $cdCbo
                );

                $modelCBOPessoaFisica->insert($Cols);
            }
        }

        // Passo 6 - Salvando o Telefone
        if ($nrTelefone) {
            $where = array(
                "ID_PESSOA = ?" => $idPessoaFisica,
                "NR_TELEFONE = ?" => $nrTelefone,
                "SG_PAIS = ?" => "BRA",
                "ID_TIPO_TELEFONE = ?" => 2,
                "CD_DDD = ?" => $cdDDD
            );

            $existeTelefone = $modelTelefone->select($where);

            if (count($existeTelefone) == 0) {

                $Cols = array(
                    "ID_PESSOA" => $idPessoaFisica,
                    "NR_TELEFONE" => $nrTelefone,
                    "SG_PAIS" => "BRA",
                    "ID_TIPO_TELEFONE" => 2,
                    "CD_DDD" => $cdDDD
                );

                $modelTelefone->insert($Cols);
            }
        }

        // Passo 7 - Salvando o Fax
        if ($nrFax) {
            $where = array(
                "ID_PESSOA = ?" => $idPessoaFisica,
                "NR_TELEFONE = ?" => $nrFax,
                "SG_PAIS = ?" => "BRA",
                "ID_TIPO_TELEFONE = ?" => 4,
                "CD_DDD = ?" => $cdDDDFax
            );

            $existeFax = $modelTelefone->select($where);

            if (count($existeFax) == 0) {
                $Cols = array(
                    "ID_PESSOA" => $idPessoaFisica,
                    "NR_TELEFONE" => $nrFax,
                    "SG_PAIS" => "BRA",
                    "ID_TIPO_TELEFONE" => 4,
                    "CD_DDD" => $cdDDDFax
                );

                $modelTelefone->insert($Cols);
            }
        }

        // Passo 8 - Salvando o Email
        if ($dsEmail) {
            $where = array(
                "ID_PESSOA = ?" => $idPessoaFisica,
                "DS_EMAIL = ?" => $dsEmail,
                "ID_TIPO_EMAIL = ?" => 2
            );

            $existeEmail = $modelEmail->select($where);

            if (count($existeEmail) == 0) {

                $Cols = array(
                    "ID_PESSOA" => $idPessoaFisica,
                    "DS_EMAIL" => $dsEmail,
                    "ID_TIPO_EMAIL" => 2,
                    "ST_EMAIL_PRINCIPAL" => "S"
                );

                $modelEmail->insert($Cols);
            }
        }

        //============== VINCULANDO EMPRESA E RESPONSAVEL ==================
        //Verifica se ja existe vinculo

        $where = array(
            "ID_PESSOA = ?" => $idPessoaJuridica,
            "ID_PESSOA_VINCULADA = ?" => $idPessoaFisica,
            "ID_TIPO_VINCULO_PESSOA = ?" => $tpVinculoPessoa
        );

        $vinculo = $modelPessoaVinculada->select($where);

        // Passo 9 - Vinculando o responsável a empresa
        if (count($vinculo) < 1) {

            $Cols = array(
                "ID_PESSOA" => $idPessoaJuridica,
                "ID_PESSOA_VINCULADA" => $idPessoaFisica,
                "ID_TIPO_VINCULO_PESSOA" => $tpVinculoPessoa
            );

            $modelPessoaVinculada->insert($Cols);
        }

        //==================== CRIANDO USUARIO =============================
        $usuario = $modelUsuario->select(array("ID_PESSOA_FISICA = ?" => $idPessoaFisica));

        // Passo 10 - Criando um usuário para o responsável
        if (count($usuario) > 0) {
            $idUsuario = $usuario[0]["ID_USUARIO"];
            $enviaEmailSenha = false;
        } else {
            $geraID = $modelUsuario->criaId();
            $idUsuario = $geraID["idUsuario"];
            $senha = gerarSenha();

            $Cols = array(
                "ID_USUARIO" => $idUsuario,
                "DS_LOGIN" => $nrCpf,
                "DS_SENHA" => md5($senha),
                "ID_PESSOA_FISICA" => $idPessoaFisica
            );

            $modelUsuario->insert($Cols);
            $enviaEmailSenha = true;
        }

        //Verifica se usuario já tem o perfil
        $where = array(
            "ID_USUARIO = ?" => $idUsuario,
            "ID_PERFIL = ?" => 2
        );

        $usuarioPerfil = $modelUsuarioPerfil->select($where);

        // Passo 11 - Criando um perfil
        if (count($usuarioPerfil) < 1) {
            $Cols = array(
                "ID_USUARIO" => $idUsuario,
                "ID_PERFIL" => 2
            );
            $modelUsuarioPerfil->insert($Cols);
        }

        // Passo 12 - Cria Situação para a Beneficiaria
        $Cols = array(
            "ID_PESSOA" => $idPessoaJuridica,
            "ID_USUARIO" => $idUsuario,
            "DS_JUSTIFICATIVA" => "Cadastro realizado",
            "ID_TIPO_SITUACAO" => 1,
            "TP_ENTIDADE_VALE_CULTURA" => "B"
        );

        $modelSituacao->insert($Cols);

        // Passo 13 - Enviar email para o responsável
        if ($enviaEmailSenha) {
            $htmlEmail = emailSenhaHTML();
            $htmlEmail = str_replace("#PERFIL#", "Beneficiária", $htmlEmail);
            $htmlEmail = str_replace("#URL#", "http://vale.cultura.gov.br/", $htmlEmail);
            $htmlEmail = str_replace("#Senha#", $senha, $htmlEmail);
            $modelEmail->enviarEmail($dsEmail, "Acesso ao sistema Vale Cultura", $htmlEmail);
        } else {
            $htmlEmail = emailNoSenhaHTML();
            $htmlEmail = str_replace("#PERFIL#", "Beneficiária", $htmlEmail);
            $htmlEmail = str_replace("#URL#", "http://vale.cultura.gov.br/", $htmlEmail);
            $modelEmail->enviarEmail($dsEmail, "Acesso ao sistema Vale Cultura", $htmlEmail);
        }
    }
}