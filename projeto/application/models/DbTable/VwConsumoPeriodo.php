<?php

class Application_Model_DbTable_VwConsumoPeriodo extends Zend_Db_Table_Abstract
{
    protected $_schema = "BI_VALE_CULTURA";
    protected $_name = 'VW_CONSUMO_PERIODO';
    protected $_primary = array('CON_ANO', 'CON_MES');
}