<?php

class Application_Model_DbTable_AcumuladoConsumo extends Zend_Db_Table_Abstract
{
    protected $_schema = "BI_VALE_CULTURA";
    protected $_name = 'ACUMULADO_CONSUMO';
    protected $_primary = 'VALOR';
}