<?php

class Application_Model_Operadora {

    private $table = null;

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Operadora();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null) {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function buscarDados($where = array(), $order = null, $limit = null) {

        $selectSituacao = $this->getTable()->select();
        $selectSituacao->setIntegrityCheck(false);
        $selectSituacao->from(array('si' => 'vale_cultura.S_Situacao'),
                                array('si.id_Tipo_Situacao')
        );

        $selectSituacao->where('si.id_Pessoa = ?', new Zend_Db_Expr('o.id_Operadora'));
        $selectSituacao->where('si.TP_ENTIDADE_VALE_CULTURA = ?', 'O');
        $selectSituacao->order(array('si.id_Situacao desc'));
        $selectSituacao->limit(1);

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('o' => 'vale_cultura.S_OPERADORA'),
                        array('idOperadora' => 'o.id_Operadora',
                              'CONVERT(VARCHAR(10),o.dt_Inscricao ,103) as dtInscricao',
                              'nrComprovanteInscricao' => 'o.nr_Comprovante_Inscricao',
                              'nrCertificado' => 'o.nr_Certificado',
                              'CONVERT(VARCHAR(10),o.dt_inicio_comercializacao,103) as dtInicioComercializacao',
                              'situacao' =>  new Zend_Db_Expr('(' . $selectSituacao . ')'))
        );

        $select->joinInner(array('p' => 'corporativo.S_PESSOA'), 'o.id_Operadora = p.id_pessoa',
                            array('idPessoa' => 'p.id_pessoa',
                                  'dtRegistro' => 'CONVERT(VARCHAR(10),p.dt_registro ,103)')
        );

        $select->joinInner(array('pj' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'p.ID_PESSOA = pj.ID_PESSOA_JURIDICA',
                            array('nrCnpj' => 'pj.NR_CNPJ',
                                  'nrInscricaoEstadual' => 'pj.NR_INSCRICAO_ESTADUAL',
                                  'nmRazaoSocial' => 'pj.NM_RAZAO_SOCIAL',
                                  'nmFantasia' => 'pj.NM_FANTASIA',
                                  'nrCei' => 'pj.NR_CEI',
                                  'cdNaturezaJuridica' => 'pj.CD_NATUREZA_JURIDICA')
        );

        $select->joinLeft(array('na' => 'CORPORATIVO.S_NATUREZA_JURIDICA'), 'pj.CD_NATUREZA_JURIDICA = na.CD_NATUREZA_JURIDICA',
                            array('dsNaturezaJuridica' => 'na.DS_NATUREZA_JURIDICA',
                                  'cdNaturezaJuridica' => 'na.CD_NATUREZA_JURIDICA')
        );

        $select->joinLeft(array('en' => 'CORPORATIVO.S_ENDERECO'), 'p.ID_PESSOA = en.ID_PESSOA AND en.CD_TIPO_ENDERECO = 01 AND en.ID_SERVICO = 1',
                            array('dsComplementoEndereco' => 'en.DS_COMPLEMENTO_ENDERECO',
                                  'nrComplemento' => 'en.NR_COMPLEMENTO',
                                  'dsLograEndereco' => 'en.DS_LOGRA_ENDERECO',
                                  'idBairro' => 'en.ID_BAIRRO',
                                  'nmBairro' => 'en.DS_BAIRRO_ENDERECO',
                                  'dsBairroEndereco' => 'en.DS_BAIRRO_ENDERECO')
        );

        $select->joinLeft(array('lo' => 'CORPORATIVO.S_LOGRADOURO'), 'en.ID_LOGRADOURO = lo.ID_LOGRADOURO',
                            array('nmLogradouro' => 'lo.NM_LOGRADOURO',
                                  'dsTipoLogradouro' => 'lo.DS_TIPO_LOGRADOURO',
                                  'nrCep' => 'lo.NR_CEP')
        );

        $select->joinLeft(array('ba' => 'CORPORATIVO.S_BAIRRO'), 'en.ID_BAIRRO = ba.ID_BAIRRO',
                            array('ba.NM_BAIRRO')
        );

        $select->joinLeft(array('mu' => 'CORPORATIVO.S_MUNICIPIO'), 'lo.ID_MUNICIPIO = mu.ID_MUNICIPIO',
                            array('nmMunicipio' => 'mu.NM_MUNICIPIO','idMunicipio' => 'mu.ID_MUNICIPIO')
        );

        $select->joinLeft(array('st' => 'CORPORATIVO.S_SITE'), 'p.ID_PESSOA = ST.ID_PESSOA',
                            array('dsSite' => 'st.DS_SITE')
        );

        $select->joinLeft(array('uf' => 'CORPORATIVO.S_UF'), 'mu.SG_UF = uf.SG_UF',
                            array('nmUF' => 'uf.NM_UF', 'sgUF' => 'uf.SG_UF')
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

        $select->order($order);
        $select->limit($limit);

//        xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }

    public function buscarOperadorasDoResponsavel($where = array(), $order = array(), $limit = null) {

        $selectSituacao = $this->getTable()->select();
        $selectSituacao->setIntegrityCheck(false);
        $selectSituacao->from(array('si' => 'vale_cultura.S_Situacao'),
                                array('si.id_Tipo_Situacao')
        );

        $selectSituacao->where('si.id_Pessoa = ?', new Zend_Db_Expr('o.id_Operadora'));
        $selectSituacao->where('si.TP_ENTIDADE_VALE_CULTURA = ?', 'O');
        $selectSituacao->order(array('si.id_Situacao desc'));
        $selectSituacao->limit(1);

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('pv' => 'CORPORATIVO.S_PESSOA_VINCULADA'));

        $select->joinInner(array('o' => 'VALE_CULTURA.S_OPERADORA'),'o.ID_OPERADORA = pv.ID_PESSOA',
                        array('idOperadora' => 'o.id_Operadora',
                              'CONVERT(VARCHAR(10),o.dt_Inscricao ,103) as dtInscricao',
                              'nrComprovanteInscricao' => 'o.nr_Comprovante_Inscricao',
                              'nrCertificado' => 'o.nr_Certificado',
                              'situacao' =>  new Zend_Db_Expr('(' . $selectSituacao . ')'))
        );

        $select->joinInner(array('p' => 'corporativo.S_PESSOA'), 'o.id_Operadora = p.id_pessoa',
                            array('idPessoa' => 'p.id_pessoa',
                                  'dtRegistro' => 'CONVERT(VARCHAR(10),p.dt_registro ,103)')
        );

        $select->joinInner(array('pj' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'p.ID_PESSOA = pj.ID_PESSOA_JURIDICA',
                            array('nrCnpj' => 'pj.NR_CNPJ',
                                  'nrInscricaoEstadual' => 'pj.NR_INSCRICAO_ESTADUAL',
                                  'nmRazaoSocial' => 'pj.NM_RAZAO_SOCIAL',
                                  'nmFantasia' => 'pj.NM_FANTASIA',
                                  'nrCei' => 'pj.NR_CEI',
                                  'cdNaturezaJuridica' => 'pj.CD_NATUREZA_JURIDICA')
        );

        $select->joinLeft(array('na' => 'CORPORATIVO.S_NATUREZA_JURIDICA'), 'pj.CD_NATUREZA_JURIDICA = na.CD_NATUREZA_JURIDICA',
                            array('dsNaturezaJuridica' => 'na.DS_NATUREZA_JURIDICA',
                                  'cdNaturezaJuridica' => 'na.CD_NATUREZA_JURIDICA')
        );

        $select->joinLeft(array('en' => 'CORPORATIVO.S_ENDERECO'), 'p.ID_PESSOA = en.ID_PESSOA AND en.CD_TIPO_ENDERECO = 01 AND en.ID_SERVICO = 1',
                            array('dsComplementoEndereco' => 'en.DS_COMPLEMENTO_ENDERECO',
                                  'nrComplemento' => 'en.NR_COMPLEMENTO',
                                  'dsLograEndereco' => 'en.DS_LOGRA_ENDERECO',
                                  'nmBairro' => 'en.DS_BAIRRO_ENDERECO',
                                  'dsBairroEndereco' => 'en.DS_BAIRRO_ENDERECO')
        );

        $select->joinLeft(array('lo' => 'CORPORATIVO.S_LOGRADOURO'), 'en.ID_LOGRADOURO = lo.ID_LOGRADOURO',
                            array('nmLogradouro' => 'lo.NM_LOGRADOURO',
                                  'dsTipoLogradouro' => 'lo.DS_TIPO_LOGRADOURO',
                                  'nrCep' => 'lo.NR_CEP')
        );

        $select->joinLeft(array('ba' => 'CORPORATIVO.S_BAIRRO'), 'en.ID_BAIRRO = ba.ID_BAIRRO',
                            array('ba.NM_BAIRRO')
        );

        $select->joinLeft(array('mu' => 'CORPORATIVO.S_MUNICIPIO'), 'lo.ID_MUNICIPIO = mu.ID_MUNICIPIO',
                            array('nmMunicipio' => 'mu.NM_MUNICIPIO','idMunicipio' => 'mu.ID_MUNICIPIO')
        );

        $select->joinLeft(array('uf' => 'CORPORATIVO.S_UF'), 'mu.SG_UF = uf.SG_UF',
                            array('nmUF' => 'uf.NM_UF', 'sgUF' => 'uf.SG_UF')
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
            }else {
                $select->order($order);
            }
        }

        $select->limit($limit);

//       xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }


    public function find($id) {
        return $this->getTable()->find($id)->current();
    }

    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $id) {
        $where["ID_OPERADORA = ?"] = $id;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function criaNrCertificado() {
        $sql = "SELECT NEXT VALUE FOR VALE_CULTURA.SQ_CERTIFICADO as nrCertificado";
        $statement = $this->getTable()->getAdapter()->query($sql);
        return $statement->fetch();
    }

}

?>