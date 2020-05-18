<?php

class Application_Model_Situacao {

    private $table = null;

    public function getTable() {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Situacao();
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

    public function find($id) {
        return $this->getTable()->find($id)->current();
    }

    public function insert(array $request) {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $id) {
        $where["id = ?"] = $id;
        return $this->getTable()->update($request, $where);
    }

    public function delete($id) {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function selecionaOperadorasAtivas() {

        // Situação da Operadora
        $selectSituacao = $this->getTable()->select();
        $selectSituacao->setIntegrityCheck(false);
        $selectSituacao->from(array('si' => 'VALE_CULTURA.S_SITUACAO'),
                                array('si.ID_TIPO_SITUACAO')
        );

        $selectSituacao->where('si.ID_PESSOA = ?', new Zend_Db_Expr('op.ID_OPERADORA'));
        $selectSituacao->where('si.TP_ENTIDADE_VALE_CULTURA = ?', 'O');
        $selectSituacao->order(array('si.ID_SITUACAO desc'));
        $selectSituacao->limit(1);

        // Lista de Operadoras
        $selectOperadora = $this->getTable()->select();
        $selectOperadora->setIntegrityCheck(false);
        $selectOperadora->from(array('op' => 'VALE_CULTURA.S_OPERADORA'),
                                array('idOperadora' => 'ID_OPERADORA',
                                      'idSituacaoXX' => new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')'))
        );

        $selectOperadora->joinInner(array('pj' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'pj.ID_PESSOA_JURIDICA = ID_OPERADORA',
                                    array('nmFantasia' => 'NM_FANTASIA',
                                          'nmRazaoSocial' => 'NM_RAZAO_SOCIAL',
                                          'nrCNPJ' => 'NR_CNPJ')
        );

        $selectOperadora->joinLeft(array('st' => 'CORPORATIVO.S_SITE'), 'st.ID_PESSOA = ID_OPERADORA',
                                    array('dsSite' => 'DS_SITE')
        );

        $selectOperadora->where(new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')') . ' = ?', '2');
        $selectOperadora->where('CONVERT(datetime, op.DT_INICIO_COMERCIALIZACAO) <= GETDATE()');

        $selectOperadora->order('NM_RAZAO_SOCIAL');
        
//        xd($selectOperadora->assemble());
        return $this->getTable()->fetchAll($selectOperadora)->toArray();

    }


    public function selecionaOperadorasAtivasInativas() {

        $selectSituacao = $this->
                        getTable()->
                        select()->
                        from(
                                array(
                            'si' => 'VALE_CULTURA.S_SITUACAO'), array('idTipoSituacao' => 'si.ID_TIPO_SITUACAO')
                        )->setIntegrityCheck(false);

        $selectSituacao->where('si.ID_PESSOA = ?', new Zend_Db_Expr('op.ID_OPERADORA'));
        $selectSituacao->where('si.TP_ENTIDADE_VALE_CULTURA = ?', 'O');
        $selectSituacao->order(array('si.ID_SITUACAO desc'));
        $selectSituacao->limit(1);

        $selectOperadora = $this->
                        getTable()->
                        select()->
                        from(
                                array(
                            'op' => 'VALE_CULTURA.S_OPERADORA'), array(
                            'idOperadora' => 'op.ID_OPERADORA',
                            'idSituacaoXX' => new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')')
                                )
                        )->setIntegrityCheck(false);


        $selectOperadora->joinInner(
            array('pj' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'pj.ID_PESSOA_JURIDICA = ID_OPERADORA', array('nmFantasia' => 'NM_FANTASIA', 'NR_CNPJ', 'nmRazaoSocial' => 'NM_RAZAO_SOCIAL')
        );

        $selectOperadora->joinLeft(array('st' => 'CORPORATIVO.S_SITE'), 'st.ID_PESSOA = ID_OPERADORA',
                                    array('dsSite' => 'DS_SITE')
        );

        $selectOperadora->where(new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')') . ' IN (?)', array(2,4));
        $selectOperadora->order('NM_RAZAO_SOCIAL');
        
        //xd($selectOperadora->assemble());
        return $this->getTable()->fetchAll($selectOperadora)->toArray();
    }



    public function selecionaBeneficiariasAtivas($where = array()) {

        $selectSituacao = $this->getTable()->select();
        $selectSituacao->setIntegrityCheck(false);
        $selectSituacao->from(array('si' => 'VALE_CULTURA.S_SITUACAO'),
                                array('si.ID_TIPO_SITUACAO')
        );

        $selectSituacao->where('si.ID_PESSOA = ?', new Zend_Db_Expr('op.ID_BENEFICIARIA'));
        $selectSituacao->where('si.TP_ENTIDADE_VALE_CULTURA = ?', 'B');
        $selectSituacao->order(array('si.DT_SITUACAO desc'));
        $selectSituacao->limit(1);

        $selectOperadora = $this->getTable()->select();
        $selectOperadora->setIntegrityCheck(false);
        $selectOperadora->from(array('op' => 'VALE_CULTURA.S_BENEFICIARIA'),
                                array('idBeneficiaria' => 'op.ID_BENEFICIARIA',
                                      'idOperadora' => 'op.ID_OPERADORA',
                                      'idSituacaoXX' => new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')'))
        );


        $selectOperadora->joinInner(array('pj' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'pj.ID_PESSOA_JURIDICA = op.ID_BENEFICIARIA',
                                        array('nmFantasia' => 'NM_FANTASIA',
                                              'nrCNPJ' => 'NR_CNPJ',
                                              'nmRazaoSocial' => 'NM_RAZAO_SOCIAL')
        );

        $selectOperadora->joinInner(array('pjO' => 'CORPORATIVO.S_PESSOA_JURIDICA'), 'pjO.ID_PESSOA_JURIDICA = op.ID_OPERADORA',
                                    array('operadora' => 'NM_FANTASIA')
        );

        $selectOperadora->joinInner(array('en' => 'CORPORATIVO.S_ENDERECO'), 'op.ID_BENEFICIARIA = en.ID_PESSOA AND en.CD_TIPO_ENDERECO = 01',
                            array('dsComplementoEndereco'     => 'en.DS_COMPLEMENTO_ENDERECO',
                                  'nrComplemento'             => 'en.NR_COMPLEMENTO',
                                  'dsLograEndereco'           => 'en.DS_LOGRA_ENDERECO',
                                  'nmBairro'                  => 'en.DS_BAIRRO_ENDERECO')
        );

        $selectOperadora->joinInner(array('lo' => 'CORPORATIVO.S_LOGRADOURO'), 'en.ID_LOGRADOURO = lo.ID_LOGRADOURO',
                            array('nmLogradouro'      => 'lo.NM_LOGRADOURO',
                                  'nrCep'             => 'lo.NR_CEP',
                                  'dsTipoLogradouro'  => 'lo.DS_TIPO_LOGRADOURO')
        );

        $selectOperadora->joinLeft(array('ba' => 'CORPORATIVO.S_BAIRRO'), 'en.ID_BAIRRO = ba.ID_BAIRRO',
                            array('idBairro' => 'ba.ID_BAIRRO',
                                  'nmBairro' => 'ba.NM_BAIRRO')
        );

        $selectOperadora->joinLeft(array('mu' => 'CORPORATIVO.S_MUNICIPIO'), 'lo.ID_MUNICIPIO = mu.ID_MUNICIPIO',
                            array('nmMunicipio' => 'mu.NM_MUNICIPIO',
                                  'idMunicipio' => 'mu.ID_MUNICIPIO')
        );

        $selectOperadora->joinLeft(array('uf' => 'CORPORATIVO.S_UF'), 'mu.SG_UF = uf.SG_UF',
                            array('nmUF' => 'uf.NM_UF',
                                  'sgUF' => 'uf.SG_UF')
        );

        $selectOperadora->joinInner(array('reg' => 'CORPORATIVO.S_REGIAO'), 'reg.SG_REGIAO = uf.SG_REGIAO',
                            array('sgRegiao' => 'reg.SG_REGIAO',
                                  'nmRegiao' => 'reg.NM_REGIAO')
        );

        $selectOperadora->joinLeft(array('pais' => 'CORPORATIVO.S_PAIS'), 'mu.SG_PAIS = pais.SG_PAIS',
                            array('nmPais' => 'pais.NM_PAIS')
        );

        $selectOperadora->where(new Zend_Db_Expr('(' . $selectSituacao->assemble() . ')') . ' = ?', '2');
        $selectOperadora->order('nmRazaoSocial');

        foreach ($where as $coluna => $valor) :
            $selectOperadora->where($coluna, $valor);
        endforeach;

//        xd($selectOperadora->assemble());
        return $this->getTable()->fetchAll($selectOperadora)->toArray();
    }

    public function buscarSituacao($where = array(), $dbg = null) {

        $select = $this->getTable()->select()->from(
        array('si' => 'VALE_CULTURA.S_SITUACAO'),
        array(
                'idSituacao'    => 'si.ID_SITUACAO',
                'dtSituacao'    => 'CONVERT(VARCHAR(10),si.DT_SITUACAO,103)'))
                ->setIntegrityCheck(false);

        $select->joinInner(array('ts' => 'VALE_CULTURA.S_TIPO_SITUACAO'), 'ts.ID_TIPO_SITUACAO = si.ID_TIPO_SITUACAO',
        array('idTipoSituacao' => 'ts.ID_TIPO_SITUACAO',
            'dsTipoSituacao' => 'ts.DS_TIPO_SITUACAO',
            'stTipoSituacao' => 'ts.ST_TIPO_SITUACAO')
        );

        $select->order(array('si.ID_SITUACAO desc'));
        $select->limit(1);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        if($dbg){
            xd($select->assemble());
        }
        return $this->getTable()->fetchAll($select)->toArray();
    }

        public function listarSituacoes($where = array()) {

        /*
         * SituaÃ§Ãµes
         * 1 - Aguardando analise
         * 2 - Autorizado
         * 3 - NÃ£o Autorizado
         * 4 - Inativo
         */

        $select = $this->getTable()->select()->from(
                        array('si' => 'VALE_CULTURA.S_SITUACAO'),
                            array('si.ID_SITUACAO',
                                  'dsJustificativa' => 'si.DS_JUSTIFICATIVA',
                                  'CONVERT(VARCHAR(10),si.DT_SITUACAO,103) as dtSituacao'))
                                    ->setIntegrityCheck(false);

          $select->joinInner(array('ts' => 'VALE_CULTURA.S_TIPO_SITUACAO'), 'ts.ID_TIPO_SITUACAO = si.ID_TIPO_SITUACAO',
                                array('idTipoSituacao' => 'ts.ID_TIPO_SITUACAO',
                                      'dsTipoSituacao' => 'ts.DS_TIPO_SITUACAO',
                                      'stTipoSituacao' => 'ts.ST_TIPO_SITUACAO')
        );

        $select->order(array('si.ID_SITUACAO'));
        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;
//        xd($select->assemble());
        return $this->getTable()->fetchAll($select)->toArray();
    }

}

?>