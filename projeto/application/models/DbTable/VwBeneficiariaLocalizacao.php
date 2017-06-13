<?php

class Application_Model_DbTable_VwBeneficiariaLocalizacao extends Zend_Db_Table_Abstract
{
    protected $_schema = "BI_VALE_CULTURA";
    protected $_name = 'VW_BENEFICIARIA_LOCALIZACAO';
    protected $_primary = 'BEN_LOC_REGIAO';
}