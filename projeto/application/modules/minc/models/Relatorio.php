<?php

/**
 * Description of Relatorio
 *
 * @author Mikhail Cavalcanti mikhail.leite@xti.com.br
 */
class Minc_Model_Relatorio
{

    private $quantidadeFuncionarios = 0;

    public function getQuantidadeFuncionarios()
    {
        return $this->quantidadeFuncionarios;
    }

    public function formatarData($data, $datafim = false)
    {
        if (strlen($data) == 10) {
            $data = explode('/', $data);
            if (checkdate($data[1], $data[0], $data[2])) {
                $date = new DateTime("{$data[2]}-{$data[1]}-{$data[0]}");
                if ($datafim) {
                    $date->add(new DateInterval('P1D'));
                }
                return $date->format('Y-m-d');
            }
            throw new InvalidArgumentException();
        }
    }

    public function configuraHeader($nomeDoArquivo)
    {
        // Configurações header para forçar o download
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/x-msexcel");
        header("Content-Disposition: attachment; filename=\"{$nomeDoArquivo}\"");
        header("Content-Description: PHP Generated Data");
    }

    /**
     * Monta e retorna o html do relatório de beneficiaria
     * @return string
     */
    public function detalhamentoDeBeneficiariasExportarExel(
        $cnpj, $situacao, $regiao, $uf, $municipio, $operadora, $dataInicil, $dataFim
    )
    {
        set_time_limit(0);

        $modelBeneficiaria = new Application_Model_Beneficiaria();
        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $where = array(
            'ID_SITUACAO = ?' => $situacao,
            'reg.SG_REGIAO = ?' => $regiao,
            'uf.sg_Uf = ?' => $uf,
            'mu.ID_MUNICIPIO = ?' => $municipio,
            'b.id_Operadora = ?' => $operadora,
            'dt_Inscricao >= ?' => $dataInicil,
            'dt_Inscricao < ?' => $dataFim,
        );
        if (!empty($cnpj)) {
            $where['pj.NR_CNPJ = ?'] = str_replace('/', '', str_replace('-', '', str_replace('.', '', $cnpj)));
        }
        $whereFiltrado = array_filter($where);
        $order = array("pj.NM_FANTASIA", "mu.NM_MUNICIPIO", "uf.SG_UF", "p.DT_REGISTRO", "pj.CD_NATUREZA_JURIDICA", "pjO.NM_FANTASIA");
        $beneficiarias = $modelBeneficiaria->buscarDados($whereFiltrado, $order);

        $html = '<table border="1">';
        $html .= '<tr>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>CNPJ</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Razão Social</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Nome Fantasia</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Tipo de Tributação</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>CNAE Primário</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>CEP</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Região</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Município/UF</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Bairro</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Complemento</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Número</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Natureza Jurídica</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>&nbsp;&nbsp;&nbsp;&nbsp;QTD Trabalhadores</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Operadora</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Responsáveis ativos</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Status</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';
        foreach ($beneficiarias as $beneficiaria) {
            $html .= '<tr>';
            $html .= '<td>' . addMascara($beneficiaria->nrCnpj, 'cnpj') . '</td>';
            $html .= '<td>' . $beneficiaria->nmRazaoSocial . '</td>';
            $html .= '<td>' . $beneficiaria->nmFantasia . '</td>';
            $html .= '<td>' . $beneficiaria->dsTipoLucro . '</td>';

            // CNAE Principal
            $modelCNAE = new Application_Model_PessoaJuridicaCNAE();
            $whereP = array('p.ID_PESSOA_JURIDICA = ?' => $beneficiaria->idPessoa, 'p.ST_CNAE = ?' => 'P');
            $cnaePrincipal = $modelCNAE->listarCnae($whereP);

            $cnae = 'Não encontrada';
            if (count($cnaePrincipal) > 0) {
                $cnae = $cnaePrincipal[0]['dsCNAE'] == '' ? 'Não encontrada' : $cnaePrincipal[0]['dsCNAE'];
            }

            $html .= '<td>' . $cnae . '</td>'; // CNAE

            $html .= '<td>' . $beneficiaria->nrCep . '</td>';
            $html .= '<td>' . $beneficiaria->nmRegiao . '</td>';
            $html .= '<td>' . $beneficiaria->nmMunicipio . '/' . $beneficiaria->sgUF . '</td>';
            $html .= '<td>' . $beneficiaria->nmBairro . '</td>';
            $html .= '<td>' . $beneficiaria->dsComplementoEndereco . '</td>';
            $html .= '<td>' . $beneficiaria->nrComplemento . '</td>';
            $html .= '<td>' . $beneficiaria->dsNaturezaJuridica . '</td>';
            $qtdFuncionarios = $beneficiaria->qtdFuncionarios > 0 ? $beneficiaria->qtdFuncionarios : 0;
            $html .= '<td>' . number_format($qtdFuncionarios, 0, ',', '.') . '</td>';
            $html .= '<td>' . $beneficiaria->operadora . '</td>';

            // Dados do responsável da operadora
            $where = array(
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16,
                'pv.id_Pessoa = ?' => $beneficiaria->idBeneficiaria,
                'up.id_Perfil = ?' => 2,
                'up.st_Usuario_Perfil = ?' => 'A'
            );

            $responsaveis = $modelPessoaVinculada->buscarDadosResponsavel($where);

            $listaResponsaveis = '';
            foreach ($responsaveis as $responsavel) {

                $listaResponsaveis .= 'CPF: ' . addMascara($responsavel->nrCpf, 'cpf') . '<br>';
                $listaResponsaveis .= $responsavel->nmPessoaFisica . '<br>';

                // Email do responsável da operadora
                $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $responsavel->idPessoaVinculada));
                foreach ($emails as $email) {
                    $listaResponsaveis .= $email->dsEmail . '<br>';
                }

                // Telefones do responsável da operadora
                $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $responsavel->idPessoaVinculada));
                foreach ($telefones as $telefones) {
                    $listaResponsaveis .= addMascara($telefones->cdDDD . $telefones->nrTelefone, 'telefone') . '<br>';
                }

                $listaResponsaveis .= '<p>- - - - - - - - - - </p>';
            }

            $html .= '<td>' . $listaResponsaveis . '</td>';

            $html .= '<td>' . Zend_Layout::getMvcInstance()->getView()->verificarSituacao($beneficiaria->situacao, 'st', 'B') . '</td>';
            $html .= '</tr>';
            $this->quantidadeFuncionarios += $beneficiaria->qtdFuncionarios;
        }
        $colspanCount = 16;
        $html .= '<tr>';
        $html .= '<td colspan=' . $colspanCount . '" style="background: #fff;">&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="' . $colspanCount . '" style="background: #f3f3f3; text-align: center; height: 30px; line-height: 30px;"><br /><b>Total de ' . number_format($this->getQuantidadeFuncionarios(), 0, ',', '.') . ' Funcionários</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="' . $colspanCount . '" style="background: #fff;">&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="' . $colspanCount . '" style="background: #f3f3f3; text-align: center; height: 30px;"><br /><b>Total de ' . count($beneficiarias) . ' Empresas Beneficiárias</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';

        $html .= '</table>';

        // Envia o conteúdo do arquivo
        return $html;
    }

    /**
     * Monta e retorna o html do relatório de beneficiaria
     * @return string
     */
    public function consultarBeneficiariaExportarExel($beneficiarias)
    {
        set_time_limit(0);

        $modelPessoaVinculada = new Application_Model_PessoaVinculada();
        $modelEmail = new Application_Model_Email();
        $modelTelefone = new Application_Model_Telefone();

        $html = '<table border="1">';
        $html .= '<tr>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>CNPJ</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Nome fantasia</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Razão social</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>&nbsp;&nbsp;&nbsp;&nbsp;QTD Trabalhadores</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Operadora</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Responsáveis ativos</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Status</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '<td style="background: #f3f3f3;  height: 30px; line-height: 30px;"><br /><b>Data cadastro</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';
        foreach ($beneficiarias as $beneficiaria) {
            $beneficiaria = (object) $beneficiaria;
            $html .= '<tr>';
            $html .= '<td>' . addMascara($beneficiaria->nrCnpj, 'cnpj') . '</td>';
            $html .= '<td>' . $beneficiaria->nmFantasia . '</td>';
            $html .= '<td>' . $beneficiaria->nmRazaoSocial . '</td>';
            $qtdFuncionarios = $beneficiaria->qtdFuncionarios > 0 ? $beneficiaria->qtdFuncionarios : 0;
            $html .= '<td>' . number_format($qtdFuncionarios, 0, ',', '.') . '</td>';
            $html .= '<td>' . $beneficiaria->nmOperadora . '</td>';

            // Dados do responsável da operadora
            $where = array(
                'pv.ID_TIPO_VINCULO_PESSOA = ?' => 16,
                'pv.id_Pessoa = ?' => $beneficiaria->idBeneficiaria,
                'up.id_Perfil = ?' => 2,
                'up.st_Usuario_Perfil = ?' => 'A'
            );

            $responsavel = $modelPessoaVinculada->buscarDadosResponsavel($where);

            $listaResponsaveis = '';
            foreach ($responsavel as $re) {
                $listaResponsaveis .= $re->nmPessoaFisica . '<br>';

                // Email do responsável da operadora
                $emails = $modelEmail->buscarEmails(array('ID_PESSOA = ?' => $re->idPessoaVinculada));
                foreach ($emails as $e) {
                    $listaResponsaveis .= $e->dsEmail . '<br>';
                }

                // Telefones do responsável da operadora
                $telefones = $modelTelefone->buscarTelefones(array('ID_PESSOA = ?' => $re->idPessoaVinculada));
                foreach ($telefones as $t) {
                    $listaResponsaveis .= addMascara($t->cdDDD . $t->nrTelefone, 'telefone') . '<br>';
                }

                $listaResponsaveis .= '<p>- - - - - - - - - - </p>';
            }

            $html .= '<td>' . $listaResponsaveis . '</td>';

            $html .= '<td>' . Zend_Layout::getMvcInstance()->getView()->verificarSituacao($beneficiaria->situacao, 'st', 'B') . '</td>';
            $html .= '<td>' . $beneficiaria->dtRegistro . '</td>';
            $html .= '</tr>';
            $this->quantidadeFuncionarios += $beneficiaria->qtdFuncionarios;
        }

        $html .= '<tr>';
        $html .= '<td colspan="8" style="background: #fff;">&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="8" style="background: #f3f3f3; text-align: center; height: 30px; line-height: 30px;"><br /><b>Total de ' . number_format($this->getQuantidadeFuncionarios(), 0, ',', '.') . ' Funcionários</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="8" style="background: #fff;">&nbsp;</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $totalBeneficiarias = count($beneficiaria);
        if ($beneficiarias instanceof Zend_Paginator) {
            $totalBeneficiarias = $beneficiarias->getTotalItemCount();
        }
        $html .= '<td colspan="8" style="background: #f3f3f3; text-align: center; height: 30px;"><br /><b>Total de ' . $totalBeneficiarias . ' Empresas Beneficiárias</b><br />&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $html .= '</tr>';

        $html .= '</table>';

        return $html;
    }

}
