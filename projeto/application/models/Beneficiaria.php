<?php

class Application_Model_Beneficiaria
{

    private $table = null;

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Beneficiaria();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null)
    {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function buscarDados($where = array(), $order = array(), $limit = null)
    {

        $selectSituacao = $this->getTable()->select();
        $selectSituacao->setIntegrityCheck(false);
        $selectSituacao->from(array('si' => 'VALE_CULTURA.S_SITUACAO'),
            array('idTipoSituacao' => 'si.ID_TIPO_SITUACAO')
        );

        $selectSituacao->where('si.ID_PESSOA = ?', new Zend_Db_Expr('b.ID_BENEFICIARIA'));
        $selectSituacao->where('si.TP_ENTIDADE_VALE_CULTURA = ?', 'B');
        $selectSituacao->order(array('si.DT_SITUACAO desc'));
        $selectSituacao->limit(1);

        $selectQtdFuncionarios = $this->getTable()->select();
        $selectQtdFuncionarios->setIntegrityCheck(false);
        $selectQtdFuncionarios->from(array('fx' => 'VALE_CULTURA.S_FAIXA_SALARIAL_BENEFICIARIA'),
            array('qtdFuncionarios' => new Zend_Db_Expr('isnull(sum(QT_TRABALHADOR_FAIXA_SALARIAL), 0)'))
        );

        $selectQtdFuncionarios->where('fx.ID_BENEFICIARIA = ?', new Zend_Db_Expr('b.ID_BENEFICIARIA'));
        $selectQtdFuncionarios->limit(1);

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('b' => 'VALE_CULTURA.S_BENEFICIARIA'),
            array('idBeneficiaria' => 'b.ID_BENEFICIARIA',
                'idOperadora' => 'b.ID_OPERADORA',
                'dtInscricao' => 'CONVERT(VARCHAR(10),b.DT_INSCRICAO,103)',
                'nrComprovanteInscricao' => 'b.NR_COMPROVANTE_INSCRICAO',
                'nrCetificado' => 'b.NR_CERTIFICADO',
                'stDivulgarDados' => 'b.ST_DIVULGAR_DADOS',
                'stAutorizaMinc' => 'b.ST_AUTORIZA_MINC',
                'idOperadoraAutorizada' => "b.ID_OPERADORA_AUTORIZADA",
                'stAutorizaValeFunc' => "b.ST_AUTORIZA_VALE_FUNC",
                'qtdFuncionarios' => new Zend_Db_Expr('(' . $selectQtdFuncionarios . ')'),
                'situacao' => new Zend_Db_Expr('(' . $selectSituacao . ')'))
        );

        $select->joinInner(array('p' => 'CORPORATIVO.S_PESSOA'), 'b.ID_BENEFICIARIA = p.ID_PESSOA',
            array('idPessoa' => 'p.ID_PESSOA',
                'dtRegistro' => 'CONVERT(VARCHAR(10),p.DT_REGISTRO ,103)')
        );

        $select->joinInner(array('pj' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'p.ID_PESSOA = pj.ID_PESSOA_JURIDICA',
            array('nrCnpj' => 'pj.NR_CNPJ',
                'idPessoaJuridica' => 'pj.ID_PESSOA_JURIDICA',
                'nrInscricaoEstadual' => 'pj.NR_INSCRICAO_ESTADUAL',
                'nmRazaoSocial' => 'pj.NM_RAZAO_SOCIAL',
                'nmFantasia' => 'pj.NM_FANTASIA',
                'nrCei' => 'pj.NR_CEI',
                'cdNaturezaJuridica' => 'pj.CD_NATUREZA_JURIDICA')
        );

        $select->joinInner(array('pjO' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'b.ID_OPERADORA = pjO.ID_PESSOA_JURIDICA',
            array('operadora' => 'pjO.NM_FANTASIA', 'idOperadora' => 'pjO.ID_PESSOA_JURIDICA')
        );

        $select->joinLeft(array('tl' => 'CORPORATIVO.S_PESSOA_JURIDICA_LUCRO'), 'b.ID_BENEFICIARIA = tl.ID_PESSOA_JURIDICA',
            array('idTipoLucro' => 'tl.ID_TIPO_LUCRO')
        );

        $select->joinLeft(array('lu' => 'CORPORATIVO.S_TIPO_LUCRO'), 'tl.ID_TIPO_LUCRO = lu.ID_TIPO_LUCRO',
            array('dsTipoLucro' => 'lu.DS_TIPO_LUCRO')
        );

        $select->joinLeft(array('na' => 'CORPORATIVO.S_NATUREZA_JURIDICA'), 'pj.CD_NATUREZA_JURIDICA = na.CD_NATUREZA_JURIDICA',
            array('dsNaturezaJuridica' => 'na.DS_NATUREZA_JURIDICA',
                'cdNaturezaJuridica' => 'na.CD_NATUREZA_JURIDICA')
        );

        $select->joinInner(array('en' => 'CORPORATIVO.S_ENDERECO'), 'p.ID_PESSOA = en.ID_PESSOA AND en.CD_TIPO_ENDERECO = 01 AND en.ID_SERVICO = 1',
            array('dsComplementoEndereco' => 'en.DS_COMPLEMENTO_ENDERECO',
                'nrComplemento' => 'en.NR_COMPLEMENTO',
                'dsLograEndereco' => 'en.DS_LOGRA_ENDERECO',
                'nmBairro' => 'en.DS_BAIRRO_ENDERECO')
        );

        $select->joinInner(array('lo' => 'CORPORATIVO.S_LOGRADOURO'), 'en.ID_LOGRADOURO = lo.ID_LOGRADOURO',
            array('nmLogradouro' => 'lo.NM_LOGRADOURO',
                'nrCep' => 'lo.NR_CEP',
                'dsTipoLogradouro' => 'lo.DS_TIPO_LOGRADOURO')
        );

        $select->joinLeft(array('ba' => 'CORPORATIVO.S_BAIRRO'), 'en.ID_BAIRRO = ba.ID_BAIRRO',
            array('idBairro' => 'ba.ID_BAIRRO',
                'nmBairro' => 'ba.NM_BAIRRO')
        );

        $select->joinLeft(array('mu' => 'CORPORATIVO.S_MUNICIPIO'), 'lo.ID_MUNICIPIO = mu.ID_MUNICIPIO',
            array('nmMunicipio' => 'mu.NM_MUNICIPIO',
                'idMunicipio' => 'mu.ID_MUNICIPIO')
        );

        $select->joinLeft(array('uf' => 'CORPORATIVO.S_UF'), 'mu.SG_UF = uf.SG_UF',
            array('nmUF' => 'uf.NM_UF',
                'sgUF' => 'uf.SG_UF')
        );

        $select->joinInner(array('reg' => 'CORPORATIVO.S_REGIAO'), 'reg.SG_REGIAO = uf.SG_REGIAO',
            array('sgRegiao' => 'reg.SG_REGIAO',
                'nmRegiao' => 'reg.NM_REGIAO')
        );

        $select->joinLeft(array('pais' => 'CORPORATIVO.S_PAIS'), 'mu.SG_PAIS = pais.SG_PAIS',
            array('nmPais' => 'pais.NM_PAIS')
        );

        if ($where) {
            foreach ($where as $coluna => $valor) :
                if ($coluna == 'ID_SITUACAO = ?') {
                    $select->where(new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')') . ' = ?', $valor);
                } else {
                    $select->where($coluna, $valor);
                }
            endforeach;
        }

        if ($order) {
            if (is_array($order)) {
                foreach ($order as $valor):
                    $select->order($valor);
                endforeach;
            } else {
                $select->order($order);
            }
        }

        $select->limit($limit);

//       xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }

    public function buscarBeneficiariasDoResponsavel($where = array(), $order = array(), $limit = null)
    {

        $selectSituacao = $this->getTable()->select();
        $selectSituacao->setIntegrityCheck(false);
        $selectSituacao->from(array('si' => 'VALE_CULTURA.S_SITUACAO'),
            array('idTipoSituacao' => 'si.ID_TIPO_SITUACAO')
        );

        $selectSituacao->where('si.ID_PESSOA = ?', new Zend_Db_Expr('b.ID_BENEFICIARIA'));
        $selectSituacao->where('si.TP_ENTIDADE_VALE_CULTURA = ?', 'B');
        $selectSituacao->order(array('si.ID_SITUACAO desc'));
        $selectSituacao->limit(1);

        $selectQtdFuncionarios = $this->getTable()->select();
        $selectQtdFuncionarios->setIntegrityCheck(false);
        $selectQtdFuncionarios->from(array('fx' => 'VALE_CULTURA.S_FAIXA_SALARIAL_BENEFICIARIA'),
            array('qtdFuncionarios' => new Zend_Db_Expr('SUM(QT_TRABALHADOR_FAIXA_SALARIAL )'))
        );

        $selectQtdFuncionarios->where('fx.ID_BENEFICIARIA = ?', new Zend_Db_Expr('b.ID_BENEFICIARIA'));
        $selectQtdFuncionarios->limit(1);

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('pv' => 'CORPORATIVO.S_PESSOA_VINCULADA'));

        $select->joinInner(array('b' => 'VALE_CULTURA.S_BENEFICIARIA'), 'b.ID_BENEFICIARIA = pv.ID_PESSOA',
            array('idBeneficiaria' => 'b.ID_BENEFICIARIA',
                'idOperadora' => 'b.ID_OPERADORA',
                'dtInscricao' => 'CONVERT(VARCHAR(10),b.DT_INSCRICAO,103)',
                'nrComprovanteInscricao' => 'b.NR_COMPROVANTE_INSCRICAO',
                'nrCetificado' => 'b.NR_CERTIFICADO',
                'stDivulgarDados' => 'b.ST_DIVULGAR_DADOS',
                'stAutorizaMinc' => 'b.ST_AUTORIZA_MINC',
                'idOperadoraAutorizada' => "b.ID_OPERADORA_AUTORIZADA",
                'stAutorizaValeFunc' => "b.ST_AUTORIZA_VALE_FUNC",
                'qtdFuncionarios' => new Zend_Db_Expr('(' . $selectQtdFuncionarios . ')'),
                'situacao' => new Zend_Db_Expr('(' . $selectSituacao . ')'))
        );

        $select->joinInner(array('p' => 'CORPORATIVO.S_PESSOA'), 'b.ID_BENEFICIARIA = p.ID_PESSOA',
            array('idPessoa' => 'p.ID_PESSOA',
                'dtRegistro' => 'CONVERT(VARCHAR(10),p.DT_REGISTRO ,103)')
        );

        $select->joinInner(array('pj' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'p.ID_PESSOA = pj.ID_PESSOA_JURIDICA',
            array('nrCnpj' => 'pj.NR_CNPJ',
                'idPessoaJuridica' => 'pj.ID_PESSOA_JURIDICA',
                'nrInscricaoEstadual' => 'pj.NR_INSCRICAO_ESTADUAL',
                'nmRazaoSocial' => 'pj.NM_RAZAO_SOCIAL',
                'nmFantasia' => 'pj.NM_FANTASIA',
                'nrCei' => 'pj.NR_CEI',
                'cdNaturezaJuridica' => 'pj.CD_NATUREZA_JURIDICA',
                'idTipoLucro' => 'pj.ID_TIPO_LUCRO')
        );

        $select->joinInner(array('pjO' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'b.ID_OPERADORA = pjO.ID_PESSOA_JURIDICA',
            array('operadora' => 'pjO.NM_FANTASIA', 'idOperadora' => 'pjO.ID_PESSOA_JURIDICA')
        );

        $select->joinLeft(array('na' => 'CORPORATIVO.S_NATUREZA_JURIDICA'), 'pj.CD_NATUREZA_JURIDICA = na.CD_NATUREZA_JURIDICA',
            array('dsNaturezaJuridica' => 'na.DS_NATUREZA_JURIDICA',
                'cdNaturezaJuridica' => 'na.CD_NATUREZA_JURIDICA')
        );

        $select->joinLeft(array('lu' => 'CORPORATIVO.S_TIPO_LUCRO'), 'pj.ID_TIPO_LUCRO = lu.ID_TIPO_LUCRO',
            array('dsTipoLucro' => 'lu.DS_TIPO_LUCRO',
                'idTipoLucro' => 'lu.ID_TIPO_LUCRO')
        );

        $select->joinLeft(array('en' => 'CORPORATIVO.S_ENDERECO'), 'p.ID_PESSOA = en.ID_PESSOA AND en.CD_TIPO_ENDERECO = 01 AND en.ID_SERVICO = 1',
            array('dsComplementoEndereco' => 'en.DS_COMPLEMENTO_ENDERECO',
                'nrComplemento' => 'en.NR_COMPLEMENTO',
                'dsLograEndereco' => 'en.DS_LOGRA_ENDERECO',
                'nmBairro' => 'en.DS_BAIRRO_ENDERECO')
        );

        $select->joinLeft(array('lo' => 'CORPORATIVO.S_LOGRADOURO'), 'en.ID_LOGRADOURO = lo.ID_LOGRADOURO',
            array('nmLogradouro' => 'lo.NM_LOGRADOURO',
                'nrCep' => 'lo.NR_CEP',
                'dsTipoLogradouro' => 'lo.DS_TIPO_LOGRADOURO')
        );

        $select->joinLeft(array('ba' => 'CORPORATIVO.S_BAIRRO'), 'en.ID_BAIRRO = ba.ID_BAIRRO',
            array('idBairro' => 'ba.ID_BAIRRO',
                'nmBairro' => 'ba.NM_BAIRRO')
        );

        $select->joinLeft(array('mu' => 'CORPORATIVO.S_MUNICIPIO'), 'lo.ID_MUNICIPIO = mu.ID_MUNICIPIO',
            array('nmMunicipio' => 'mu.NM_MUNICIPIO',
                'idMunicipio' => 'mu.ID_MUNICIPIO')
        );

        $select->joinLeft(array('uf' => 'CORPORATIVO.S_UF'), 'mu.SG_UF = uf.SG_UF',
            array('nmUF' => 'uf.NM_UF',
                'sgUF' => 'uf.SG_UF')
        );

        $select->joinLeft(array('reg' => 'CORPORATIVO.S_REGIAO'), 'reg.SG_REGIAO = uf.SG_REGIAO',
            array('sgRegiao' => 'reg.SG_REGIAO',
                'nmRegiao' => 'reg.NM_REGIAO')
        );

        $select->joinLeft(array('pais' => 'CORPORATIVO.S_PAIS'), 'mu.SG_PAIS = pais.SG_PAIS',
            array('nmPais' => 'pais.NM_PAIS')
        );

        if ($where) {
            foreach ($where as $coluna => $valor) :
                if ($coluna == 'ID_SITUACAO = ?') {
                    $select->where(new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')') . ' = ?', $valor);
                } else {
                    $select->where($coluna, $valor);
                }
            endforeach;
        }

        if ($order) {
            if (is_array($order)) {
                foreach ($order as $valor) :
                    $select->order($valor);
                endforeach;
            } else {
                $select->order($order);
            }
        }

        $select->limit($limit);

//       xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }

    public function find($id)
    {
        return $this->getTable()->find($id)->current();
    }

    public function insert(array $request)
    {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $id)
    {
        $where["ID_BENEFICIARIA = ?"] = $id;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id)
    {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function criaNrCertificado()
    {
        $sql = "SELECT NEXT VALUE FOR VALE_CULTURA.sqCertificado as NR_CERTIFICADO";
        $statement = $this->getTable()->getAdapter()->query($sql);
        return $statement->fetch();
    }

    public function ultimaSituacaoCadastral($idBeneficiaria)
    {

        $historicoSituacaoArray = array();

        $modelHistoricoSituacaoCadastralPJ = new Application_Model_HistoricoSituacaoCadastralPJ();
        $consultaHistorico = $modelHistoricoSituacaoCadastralPJ->ultimaSituacaoCadastral($idBeneficiaria);

        if (count($consultaHistorico) > 0) {
            $historicoSituacaoArray = $consultaHistorico[0];
        }

        return $historicoSituacaoArray;
    }
}