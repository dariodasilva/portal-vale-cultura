<?php

class Application_Model_DbTable_VwTrabalhadorLocalizacao extends Zend_Db_Table_Abstract
{
    protected $_schema = "BI_VALE_CULTURA";
    protected $_name = 'VW_TRABALHADOR_LOCALIZACAO';
    protected $_primary = 'TRA_LOC_REGIAO';
}