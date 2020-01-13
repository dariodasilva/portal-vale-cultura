<?php

class Application_Model_DbTable_VwConsumoLocalizacao extends Zend_Db_Table_Abstract
{
    protected $_schema = "BI_VALE_CULTURA";
    protected $_name = 'VW_CONSUMO_LOCALIZACAO';
    protected $_primary = array('CONS_REGIAO', 'CON_ESTADO');
}