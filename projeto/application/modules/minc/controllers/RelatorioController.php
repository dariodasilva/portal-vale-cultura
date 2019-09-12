<?php

include_once 'GenericController.php';

class Minc_RelatorioController extends GenericController {

    private $session;

    public function init() {

        // Layout Padrão
        $this->view->layout()->setLayout('layout');

        // Título
        $this->view->assign('titulo', 'Minc');

        // Manter Autenticado
        parent::autenticar(array('C','A'));

        $this->view->assign('admin', false);
        if ($this->_sessao["PerfilGeral"] == 'A') {
            $this->view->assign('admin', true);
        }

        // Inicialização Generic
        parent::init();

    }

    public function relatorioBeneficiariasAction() {
        // Listar todas as operadoras
        $modelBeneficiaria  = new Application_Model_Beneficiaria();
        $modelSituacoes     = new Application_Model_TipoSituacao();
        $modelUf            = new Application_Model_Uf();
        $modelRegiao        = new Application_Model_Regiao();
        $modelSituacao      = new Application_Model_Situacao();
        $pagina             = intval($this->_getParam('pagina'));

        $where = array();

        if ($_POST) {

            $CNPJ       = $this->getRequest()->getParam('CNPJ');
            $NOME       = $this->getRequest()->getParam('NOME');
            $SITUACAO   = $this->getRequest()->getParam('SITUACAO');
            $REGIAO     = $this->getRequest()->getParam('REGIAO');
            $UF         = $this->getRequest()->getParam('UF');
            $MUNICIPIO  = $this->getRequest()->getParam('MUNICIPIO');
            $OPERADORA  = $this->getRequest()->getParam('OPERADORA');
            $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
            $DTFIM      = $this->getRequest()->getParam('DTFIM');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.nmFantasia like ?'] = '%' . $NOME . '%';
            }

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($REGIAO) {
                $where['reg.SG_REGIAO = ?'] = $REGIAO;
            }

            if ($UF) {
                $where['uf.sg_Uf = ?'] = $UF;
            }

            if ($MUNICIPIO) {
                $where['mu.ID_MUNICIPIO = ?'] = $MUNICIPIO;
            }

            if ($OPERADORA > 0) {
                $where['b.id_Operadora = ?'] = $OPERADORA;
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

            $this->view->assign('cnpj', $CNPJ);
            $this->view->assign('nome', $NOME);
            $this->view->assign('situacao', $SITUACAO);
            $this->view->assign('regiao', $REGIAO);
            $this->view->assign('uf', $UF);
            $this->view->assign('municipio', $MUNICIPIO);
            $this->view->assign('operadora', $OPERADORA);
            if (isset($DTINICIO) && is_array($DTINICIO)) {
                $this->view->assign('dtInicio', $DTINICIO[0] . '/' . $DTINICIO[1] . '/' . $DTINICIO[2]);
            }
            if (isset($DTFIM) && is_array($DTFIM)) {
                $this->view->assign('dtFim', $DTFIM[0] . '/' . $DTFIM[1] . '/' . $DTFIM[2]);
            }

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

        } else {
            $this->view->assign('cnpj', '');
            $this->view->assign('nome', '');
            $this->view->assign('situacao', '');
            $this->view->assign('regiao', '');
            $this->view->assign('uf', '');
            $this->view->assign('municipio', '');
            $this->view->assign('operadora', '');
            $this->view->assign('dtInicio', '');
            $this->view->assign('dtFim', '');
        }

        $order = array("pj.NM_FANTASIA", "mu.NM_MUNICIPIO", "uf.SG_UF", "p.DT_REGISTRO", "pj.CD_NATUREZA_JURIDICA", "pjO.NM_FANTASIA");

        $beneficiarias      = $modelBeneficiaria->buscarDados($where, $order);
        $situacoes          = $modelSituacoes->select();
        $ufs                = $modelUf->select(array(), 'nm_Uf asc');
        $regioes            = $modelRegiao->select();
        $operadorasAtivas   = $modelSituacao->selecionaOperadorasAtivas();

        // Paginação
        $paginator = Zend_Paginator::factory($beneficiarias);
        // Seta a quantidade de registros por página
        $paginator->setItemCountPerPage(200);
        // numero de paginas que serão exibidas
        $paginator->setPageRange(7);
        // Seta a página atual
        $paginator->setCurrentPageNumber($pagina);
        // Passa o paginator para a view
        $this->view->beneficiarias = $paginator;
        // Soma a quantidade de Funcionários Geral
        $i = 0;
        foreach ($beneficiarias as $p){
            $i = $i+$p->qtdFuncionarios;
        }
        $this->view->qtdFuncionarios = $i;

        $this->view->assign('situacoes', $situacoes);
        $this->view->assign('ufs', $ufs);
        $this->view->assign('regioes', $regioes);
        $this->view->assign('operadorasAtivas', $operadorasAtivas);
        $this->view->assign('qtdBeneficiarias', count($beneficiarias));
        if($pagina){
            $this->view->assign('pagina', $pagina);
        }
    }

    public function relatorioOperadorasAction() {
        // Listar todas as operadoras
        $modelOperadora     = new Application_Model_Operadora();
        $modelSituacoes     = new Application_Model_TipoSituacao();
        $modelUf            = new Application_Model_Uf();
        $modelRegiao        = new Application_Model_Regiao();
        $modelSituacao      = new Application_Model_Situacao();
        $pagina             = intval($this->_getParam('pagina'));

        $where = array();

        if ($_POST) {

            $CNPJ       = $this->getRequest()->getParam('CNPJ');
            $NOME       = $this->getRequest()->getParam('NOME');
            $SITUACAO   = $this->getRequest()->getParam('SITUACAO');
            $REGIAO     = $this->getRequest()->getParam('REGIAO');
            $UF         = $this->getRequest()->getParam('UF');
            $MUNICIPIO  = $this->getRequest()->getParam('MUNICIPIO');
            $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
            $DTFIM      = $this->getRequest()->getParam('DTFIM');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.nmFantasia like ?'] = '%' . $NOME . '%';
            }

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($REGIAO) {
                $where['reg.SG_REGIAO = ?'] = $REGIAO;
            }

            if ($UF) {
                $where['uf.sg_Uf = ?'] = $UF;
            }

            if ($MUNICIPIO) {
                $where['mu.ID_MUNICIPIO = ?'] = $MUNICIPIO;
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

            $this->view->assign('cnpj', $CNPJ);
            $this->view->assign('nome', $NOME);
            $this->view->assign('situacao', $SITUACAO);
            $this->view->assign('regiao', $REGIAO);
            $this->view->assign('uf', $UF);
            $this->view->assign('municipio', $MUNICIPIO);
            if (isset($DTINICIO) && is_array($DTINICIO)) {
                $this->view->assign('dtInicio', $DTINICIO[0] . '/' . $DTINICIO[1] . '/' . $DTINICIO[2]);
            }
            if (isset($DTFIM) && is_array($DTFIM)) {
                $this->view->assign('dtFim', $DTFIM[0] . '/' . $DTFIM[1] . '/' . $DTFIM[2]);
            }

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

        } else {
            $this->view->assign('cnpj', '');
            $this->view->assign('nome', '');
            $this->view->assign('situacao', '');
            $this->view->assign('regiao', '');
            $this->view->assign('uf', '');
            $this->view->assign('municipio', '');
            $this->view->assign('dtInicio', '');
            $this->view->assign('dtFim', '');
        }

        $operadoras         = $modelOperadora->buscarDados($where);
        $situacoes          = $modelSituacoes->select();
        $ufs                = $modelUf->select(array(), 'nm_Uf asc');
        $regioes            = $modelRegiao->select();

        // Paginação
        $paginator = Zend_Paginator::factory($operadoras);
        // Seta a quantidade de registros por página
        $paginator->setItemCountPerPage(200);
        // numero de paginas que serão exibidas
        $paginator->setPageRange(7);
        // Seta a página atual
        $paginator->setCurrentPageNumber($pagina);
        // Passa o paginator para a view
        $this->view->operadoras = $paginator;

        $this->view->assign('situacoes', $situacoes);
        $this->view->assign('ufs', $ufs);
        $this->view->assign('regioes', $regioes);
        $this->view->assign('qtdOperadoras', count($operadoras));
        if($pagina){
            $this->view->assign('pagina', $pagina);
        }
    }

    public function relatorioHtmlAction() {
        $this->getHelper('layout')->disableLayout();

        // Listar todas as operadoras
        $modelBeneficiaria  = new Application_Model_Beneficiaria();
        $modelSituacoes     = new Application_Model_TipoSituacao();
        $modelUf            = new Application_Model_Uf();
        $modelRegiao        = new Application_Model_Regiao();
        $modelSituacao      = new Application_Model_Situacao();

        $where = array();

        if ($_POST) {

            $CNPJ       = $this->getRequest()->getParam('CNPJ');
            $NOME       = $this->getRequest()->getParam('NOME');
            $SITUACAO   = $this->getRequest()->getParam('SITUACAO');
            $REGIAO     = $this->getRequest()->getParam('REGIAO');
            $UF         = $this->getRequest()->getParam('UF');
            $MUNICIPIO  = $this->getRequest()->getParam('MUNICIPIO');
            $OPERADORA  = $this->getRequest()->getParam('OPERADORA');
            $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
            $DTFIM      = $this->getRequest()->getParam('DTFIM');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($NOME) {
                $where['pj.nmFantasia like ?'] = '%' . $NOME . '%';
            }

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($REGIAO) {
                $where['reg.SG_REGIAO = ?'] = $REGIAO;
            }

            if ($UF) {
                $where['uf.sg_Uf = ?'] = $UF;
            }

            if ($MUNICIPIO) {
                $where['mu.ID_MUNICIPIO = ?'] = $MUNICIPIO;
            }

            if ($OPERADORA > 0) {
                $where['b.id_Operadora = ?'] = $OPERADORA;
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

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

        }

        $order = array("pj.NM_FANTASIA", "mu.NM_MUNICIPIO", "uf.SG_UF", "p.DT_REGISTRO", "pj.CD_NATUREZA_JURIDICA", "pjO.NM_FANTASIA");


        $beneficiarias      = $modelBeneficiaria->buscarDados($where, $order);
        $situacoes          = $modelSituacoes->select();
        $ufs                = $modelUf->select(array(), 'nm_Uf asc');
        $regioes            = $modelRegiao->select();
        $operadorasAtivas   = $modelSituacao->selecionaOperadorasAtivas();

        // Passa o paginator para a view
        $this->view->beneficiarias = $beneficiarias;
        // Soma a quantidade de Funcionários Geral
        $i = 0;
        foreach ($beneficiarias as $p){
            $i = $i+$p->qtdFuncionarios;
        }
        $this->view->qtdFuncionarios = $i;

        $this->view->assign('situacoes', $situacoes);
        $this->view->assign('ufs', $ufs);
        $this->view->assign('regioes', $regioes);
        $this->view->assign('operadorasAtivas', $operadorasAtivas);
        $this->view->assign('qtdBeneficiarias', count($beneficiarias));

    }

    public function gerarPdfAction() {
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


    public function gerarPdfCompletoAction() {
        $this->getHelper('layout')->disableLayout();
        header("Content-type: text/html; charset=iso-8859-1");

        $modelBeneficiaria  = new Application_Model_Beneficiaria();
        $where = array();

        if($_POST){

            $SITUACAO   = $this->getRequest()->getParam('SITUACAO');
            $REGIAO     = $this->getRequest()->getParam('REGIAO');
            $UF         = $this->getRequest()->getParam('UF');
            $MUNICIPIO  = $this->getRequest()->getParam('MUNICIPIO');
            $OPERADORA  = $this->getRequest()->getParam('OPERADORA');
            $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
            $DTFIM      = $this->getRequest()->getParam('DTFIM');

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($REGIAO) {
                $where['reg.SG_REGIAO = ?'] = $REGIAO;
            }

            if ($UF) {
                $where['uf.sg_Uf = ?'] = $UF;
            }

            if ($MUNICIPIO) {
                $where['mu.ID_MUNICIPIO = ?'] = $MUNICIPIO;
            }

            if ($OPERADORA > 0) {
                $where['b.id_Operadora = ?'] = $OPERADORA;
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

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

        }

        $order = array("pj.NM_FANTASIA", "mu.NM_MUNICIPIO", "uf.SG_UF", "p.DT_REGISTRO", "pj.CD_NATUREZA_JURIDICA", "pjO.NM_FANTASIA");
        $beneficiarias      = $modelBeneficiaria->buscarDados($where, $order, 100);

        $html = '<table autosize="2.4" border="1">';
        $html .= '<thead>';
        $html .= '<tr>';
            $html .= '<td style="background: #f3f3f3;"><b>Nome da empresa</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
            $html .= '<td style="background: #f3f3f3;"><b>Município/UF</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
            $html .= '<td style="background: #f3f3f3;"><b>Data de cadastro</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
            $html .= '<td style="background: #f3f3f3;"><b>Status</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
            $html .= '<td style="background: #f3f3f3;"><b>Natureza Jurídica</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
            $html .= '<td style="background: #f3f3f3;"><b>&nbsp;&nbsp;&nbsp;&nbsp;QTD Funcionários</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
            $html .= '<td style="background: #f3f3f3;"><b>Operadora</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        foreach($beneficiarias as $b):
        $html .= '<tr>';
            $html .= '<td>'.$b->nmRazaoSocial.'</td>';
            $html .= '<td>'.$b->nmMunicipio.'/'.$b->sgUF.'</td>';
            $html .= '<td>'.$b->dtInscricao.'</td>';
            $html .= '<td>'.$this->view->verificarSituacao($b->situacao, 'st', 'B').'</td>';
            $html .= '<td>'.$b->dsNaturezaJuridica.'</td>';
            $qtdFuncionarios = $b->qtdFuncionarios > 0 ? $b->qtdFuncionarios : 0;
            $html .= '<td>'.number_format($qtdFuncionarios, 0, ',', '.').'</td>';
            $html .= '<td>'.$b->operadora.'</td>';
        $html .= '</tr>';
        endforeach;
        $html .= '</tbody>';

        $html .= '</table>';

        $nomeDoc = date("ymdhis").'_projeto_li.pdf';

        include('MPDF/mpdf.php');
//        $mpdf= new mPDF('L');

        $mpdf = new mPDF(
                '',    // mode - default ''
                '',    // format - A4, for example, default ''
                10,     // font size - default 0
                'Arial',    // default font family
                15,    // margin_left
                15,    // margin right
                16,     // margin top
                16,    // margin bottom
                9,     // margin header
                9,     // margin footer
                'L');  // L - landscape, P - portrait

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->allow_charset_conversion=true;
        $mpdf->charset_in='iso-8859-1';

        $mpdf->SetFooter('{DATE j/m/Y}|{PAGENO}/{nb}|MINC / VALE CULTURA');

        $mpdf->WriteHTML($html);

        $mpdf->Output($nomeDoc,'D');
        exit();
    }

    public function gerarPdfCompletoFpdfAction() {
        $this->getHelper('layout')->disableLayout();
        header("Content-type: text/html; charset=iso-8859-1");

        $modelBeneficiaria  = new Application_Model_Beneficiaria();
        $where = array();

        if($_POST){

            $SITUACAO   = $this->getRequest()->getParam('SITUACAO');
            $REGIAO     = $this->getRequest()->getParam('REGIAO');
            $UF         = $this->getRequest()->getParam('UF');
            $MUNICIPIO  = $this->getRequest()->getParam('MUNICIPIO');
            $OPERADORA  = $this->getRequest()->getParam('OPERADORA');
            $DTINICIO   = $this->getRequest()->getParam('DTINICIO');
            $DTFIM      = $this->getRequest()->getParam('DTFIM');

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($REGIAO) {
                $where['reg.SG_REGIAO = ?'] = $REGIAO;
            }

            if ($UF) {
                $where['uf.sg_Uf = ?'] = $UF;
            }

            if ($MUNICIPIO) {
                $where['mu.ID_MUNICIPIO = ?'] = $MUNICIPIO;
            }

            if ($OPERADORA > 0) {
                $where['b.id_Operadora = ?'] = $OPERADORA;
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

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

        }

        $order = array("pj.NM_FANTASIA", "mu.NM_MUNICIPIO", "uf.SG_UF", "p.DT_REGISTRO", "pj.CD_NATUREZA_JURIDICA", "pjO.NM_FANTASIA");
        $beneficiarias      = $modelBeneficiaria->buscarDados($where, $order);

        include('FPDF/fpdf.php');

        $pdf= new FPDF("L","pt","A4");
        $pdf->AddPage();

        $pdf->SetFont('arial','B',18);
        $pdf->Cell(0,5,"Relatório",0,1,'C');
        $pdf->Cell(0,5,"","B",1,'C');
        $pdf->Ln(50);

        //cabeçalho da tabela
        $pdf->SetFont('arial','B',12);
        $pdf->Cell(140,20,'Nome da empresa',1,0,"L");
        $pdf->Cell(140,20,'Município/UF',1,0,"L");
        $pdf->Cell(70,20,'Data de cadastro',1,0,"C");
        $pdf->Cell(100,20,'Status',1,0,"L");
        $pdf->Cell(120,20,'Natureza Jurídica',1,0,"L");
        $pdf->Cell(95,20,'QTD Funcionários',1,0,"C");
        $pdf->Cell(0,20,'Operadora',1,1,"L");

        //linhas da tabela
        $pdf->SetFont('arial','',10);

        foreach($beneficiarias as $b){

            $pdf->Cell(140,20,$b->nmRazaoSocial,1,0,"L");
            $pdf->Cell(140,20,$b->nmMunicipio.'/'.$b->sgUF,1,0,"L");
            $pdf->Cell(70,20,$b->dtInscricao,1,0,"C");
            $pdf->Cell(100,20,$this->view->verificarSituacao($b->situacao, 'st', 'B'),1,0,"L");
            $pdf->Cell(120,20,$b->dsNaturezaJuridica,1,0,"L");
            $qtdFuncionarios = $b->qtdFuncionarios > 0 ? $b->qtdFuncionarios : 0;
            $pdf->Cell(95,20,number_format($qtdFuncionarios, 0, ',', '.'),1,0,"C");
            $pdf->Cell(0,20,$b->operadora,1,1,"L");
        }

        $pdf->Output("arquivo.pdf","D");

        exit();
    }

    // Buscar UF por regiao
    public function ufPorRegiaoAction() {
        $this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

        $modelUF    = new Application_Model_Uf;
        $sgRegiao   = $this->getParam('sgregiao');
        $duf        = $this->getParam('uf');

        if($sgRegiao != ''){
            $where['SG_REGIAO = ?'] = $sgRegiao;
        }
        $ufs = $modelUF->select($where);

    	$data = '<option value="">- SELECIONE -</option>';

    	foreach ($ufs as $uf) {

            if($uf['SG_UF'] == $duf){
                $data .= '<option value="' . $uf['SG_UF'] . '" selected="selected">' . $uf['NM_UF'] . '</option>';
            }else{
                $data .= '<option value="' . $uf['SG_UF'] . '">' . $uf['NM_UF'] . '</option>';
            }
    	}

    	echo $data;
    }

    // Buscar Municipio por uf
    public function municipioPorUfAction() {
        $this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

        $modelMunicipio = new Application_Model_Municipio();
        $sgUF           = $this->getParam('sguf');
        $dmunicipio     = $this->getParam('municipio');

        if($sgUF == ''){
            $data = '<option value="">- SELECIONE -</option>';
        }else{
            $where['SG_UF = ?'] = $sgUF;
            $where['TP_LOCALIDADE = ?'] = 'M';

            $municipio = $modelMunicipio->select($where);

            $data = '<option value="">- SELECIONE -</option>';

            foreach ($municipio as $m) {

                if($m['ID_MUNICIPIO'] == $dmunicipio){
                    $data .= '<option value="' . $m['ID_MUNICIPIO'] . '" selected="selected">' . $m['NM_MUNICIPIO'] . '</option>';
                }else{
                    $data .= '<option value="' . $m['ID_MUNICIPIO'] . '">' . $m['NM_MUNICIPIO'] . '</option>';
                }
            }
        }

    	echo $data;

    }

    public function exportarExcelAction()
    {
        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $where = array();

        if ($_POST) {
            $SITUACAO = $this->getRequest()->getParam('SITUACAO');
            $REGIAO = $this->getRequest()->getParam('REGIAO');
            $UF = $this->getRequest()->getParam('UF');
            $MUNICIPIO = $this->getRequest()->getParam('MUNICIPIO');
            $OPERADORA = $this->getRequest()->getParam('OPERADORA');
            $DTINICIO = $this->getRequest()->getParam('DTINICIO');
            $DTFIM = $this->getRequest()->getParam('DTFIM');
            $CNPJ = $this->getRequest()->getParam('CNPJ');

            if ($CNPJ) {
                $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $CNPJ)));
            }

            if ($SITUACAO > 0) {
                $where['ID_SITUACAO = ?'] = $SITUACAO;
            }

            if ($REGIAO) {
                $where['reg.SG_REGIAO = ?'] = $REGIAO;
            }

            if ($UF) {
                $where['uf.sg_Uf = ?'] = $UF;
            }

            if ($MUNICIPIO) {
                $where['mu.ID_MUNICIPIO = ?'] = $MUNICIPIO;
            }

            if ($OPERADORA > 0) {
                $where['b.id_Operadora = ?'] = $OPERADORA;
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

            if (is_array($DTINICIO)) {
                if (!checkdate($DTINICIO[1], $DTINICIO[0], $DTINICIO[2])) {
                    parent::message('Data de Cadastro (Mínima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }

            if (is_array($DTFIM)) {
                if (!checkdate($DTFIM[1], $DTFIM[0], $DTFIM[2])) {
                    parent::message('Data de Cadastro (Máxima) inválida.', '/minc/relatorio/relatorio-beneficiarias/', 'error');
                }
            }
        }

        $order = array("pj.NM_FANTASIA", "mu.NM_MUNICIPIO", "uf.SG_UF", "p.DT_REGISTRO", "pj.CD_NATUREZA_JURIDICA", "pjO.NM_FANTASIA");
        $beneficiarias = $modelBeneficiaria->buscarDados($where, $order);

        // Definimos o nome do arquivo que será exportado
        $arquivo = 'empresas_beneficiarias.xls';

        $html = '<table border="1">';
        $html .= '<tr>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Nome da empresa</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Cnpj</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Município</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>UF</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Data de cadastro</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Status</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Natureza Jurídica</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>&nbsp;&nbsp;&nbsp;&nbsp;QTD Funcionários</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Operadora</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';
        foreach ($beneficiarias as $b):
            $html .= '<tr>';
            $html .= '<td>' . $b->nmRazaoSocial . '</td>';
            $html .= '<td>' . $this->view->mascara($b->nrCnpj, 'cnpj') . '</td>';
            $html .= '<td>' . $b->nmMunicipio . '</td>';
            $html .= '<td>' . $b->sgUF . '</td>';
            $html .= '<td>' . $b->dtInscricao . '</td>';
            $html .= '<td>' . $this->view->verificarSituacao($b->situacao, 'st', 'B') . '</td>';
            $html .= '<td>' . $b->dsNaturezaJuridica . '</td>';
            $qtdFuncionarios = $b->qtdFuncionarios > 0 ? $b->qtdFuncionarios : 0;
            $html .= '<td>' . number_format($qtdFuncionarios, 0, ',', '.') . '</td>';
            $html .= '<td>' . $b->operadora . '</td>';
            $html .= '</tr>';
        endforeach;

        $i = 0;
        foreach ($beneficiarias as $p) {
            $i = $i + $p->qtdFuncionarios;
        }
        $this->view->qtdFuncionarios = $i;

        $html .= '<tr>';
        $html .= '<td colspan="8" style="background: #fff;">&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="8" style="background: #f3f3f3; text-align: center; height: 30px; line-height: 30px;"><br /><b>Total de ' . number_format($i, 0, ',', '.') . ' Funcionários</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="8" style="background: #fff;">&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="8" style="background: #f3f3f3; text-align: center; height: 30px;"><br /><b>Total de ' . count($beneficiarias) . ' Empresas Beneficiárias</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';

        $html .= '</table>';

        // Configurações header para forçar o download
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/x-msexcel");
        header("Content-Disposition: attachment; filename=\"{$arquivo}\"");
        header("Content-Description: PHP Generated Data");
        // Envia o conteúdo do arquivo
        echo $html;
        die();
    }

    public function detalhamentoDeBeneficiariasAction()
    {
        $this->relatorioBeneficiariasAction();
        $this->view->detalhamentoDeBeneficiariasExelUrl = $this->view->url(
                array('module' => 'minc', 'controller' => 'relatorio', 'action' => 'detalhamento-de-beneficiarias-exportar-exel'),
                null,
                true
                );
    }

    public function detalhamentoDeBeneficiariasExportarExelAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        if ($this->getRequest()->isPost()) {
            $cnpj = $this->getRequest()->getParam('CNPJ');
            $situacao = $this->getRequest()->getParam('SITUACAO');
            $regiao = $this->getRequest()->getParam('REGIAO');
            $uf = $this->getRequest()->getParam('UF');
            $municipio = $this->getRequest()->getParam('MUNICIPIO');
            $operadora = $this->getRequest()->getParam('OPERADORA');
            $dataInicil = $this->getRequest()->getParam('DTINICIO');
            $dataFim = $this->getRequest()->getParam('DTFIM');
            $relatorio = new Minc_Model_Relatorio();

            try {
                $dataMensagem = 'Mínima';
                $dataInicilFormatada = $relatorio->formatarData($dataInicil);
                $dataMensagem = 'Máxima';
                $dataFimFormatada = $relatorio->formatarData($dataFim, true);
            } catch (InvalidArgumentException $ex) {
                parent::message(
                        "Data de Cadastro ({$dataMensagem}) inválida.",
                        "/{$this->getRequest()->getModuleName()}/{$this->getRequest()->getControllerName()}/detalhamentoDeBeneficiarias/",
                        'error'
                        );
            }

            // Configura o header da requisição e o nome do arquivo que será feito download
            $relatorio->configuraHeader('detalhamento_de_beneficiarias.xls');

            echo $relatorio->detalhamentoDeBeneficiariasExportarExel(
                    $cnpj, $situacao, $regiao, $uf, $municipio, $operadora, $dataInicilFormatada, $dataFimFormatada
            );
            return;
        }
    }

    public function detalhamentoDeOperadorasAction()
    {
        $this->relatorioOperadorasAction();
        $this->view->detalhamentoDeOperadorasExcelUrl = $this->view->url(
                array('module' => 'minc', 'controller' => 'relatorio', 'action' => 'detalhamento-de-operadoras-exportar-excel'),
                null,
                true
                );
    }

    public function detalhamentoDeOperadorasExportarExcelAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        if ($this->getRequest()->isPost()) {
            $cnpj = $this->getRequest()->getParam('CNPJ');
            $situacao = $this->getRequest()->getParam('SITUACAO');
            $regiao = $this->getRequest()->getParam('REGIAO');
            $uf = $this->getRequest()->getParam('UF');
            $municipio = $this->getRequest()->getParam('MUNICIPIO');
            $dataInicil = $this->getRequest()->getParam('DTINICIO');
            $dataFim = $this->getRequest()->getParam('DTFIM');
            $relatorio = new Minc_Model_Relatorio();

            try {
                $dataMensagem = 'Mínima';
                $dataInicilFormatada = $relatorio->formatarData($dataInicil);
                $dataMensagem = 'Máxima';
                $dataFimFormatada = $relatorio->formatarData($dataFim, true);
            } catch (InvalidArgumentException $ex) {
                parent::message(
                        "Data de Cadastro ({$dataMensagem}) inválida.",
                        "/{$this->getRequest()->getModuleName()}/{$this->getRequest()->getControllerName()}/detalhamentoDeOperadoras/",
                        'error'
                        );
            }

            // Configura o header da requisição e o nome do arquivo que será feito download
            $relatorio->configuraHeader('detalhamento_de_operadoras.xls');

            echo $relatorio->detalhamentoDeOperadorasExportarExcel(
                    $cnpj, $situacao, $regiao, $uf, $municipio, $dataInicilFormatada, $dataFimFormatada
            );
            return;
        }
    }

}
