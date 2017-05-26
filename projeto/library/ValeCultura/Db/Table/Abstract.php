<?php
require_once 'Zend/Db/Table/Abstract.php';

abstract class ValeCultura_Db_Table_Abstract extends Zend_Db_Table_Abstract
{

    private $_config;
    protected $_rowClass = "ValeCultura_Db_Table_Row";
    protected $debugMode = false;

    public function init()
    {
        $this->setName($this->getName($this->_name));
        $this->setSchema($this->getSchema($this->_schema));
    }

    public function setSchema($strSchema) {
        $this->_schema = $strSchema;
    }
    public function setName($name) {
        $this->_name = $name;
    }
    public function setDatabase($banco) {
        $this->_banco = $banco;
    }

    public function getName($strName = '', $strSchema = '')
    {
        $strName = strtolower($strName);
        return $strName;
    }
    
    public function getSchema($strSchema = null, $isReturnDb = true, $strNameDb = null)
    {

            $db = Zend_Db_Table::getDefaultAdapter();

            if ($db instanceof Zend_Db_Adapter_Pdo_Mssql) {
                if (is_null($strNameDb)) {
                    $strNameDb = 'corporativo';
                }

                if ($isReturnDb && strpos($strSchema, '.') === false) {
                    if ($strSchema) {
                        $strSchema = $strSchema . "." . $strNameDb;
                    } else {
                        $strSchema = $strNameDb;
                    }
                } elseif (strpos($strSchema, '.') === false) {
                    $strSchema = $strNameDb;
                }
            } else if (!$strSchema) {
                $strSchema = $this->_schema;
            }

            return $strSchema;
    }


    public function setDebugMode($boolean) {
        $this->debugMode = $boolean;
    }

    public function getPrimary()
    {
        return (isset($this->_primary))? $this->_primary : '';
    }

    public function getSequence()
    {
        return (isset($this->_sequence))? $this->_sequence : true;
    }

    public function __destruct()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->closeConnection();
    }

    public function getTotal()
    {
        $select = $this->getTable()->select();
        $intTotal = $select->from($this->getTable(), 'count(*) as total')->query()->fetchColumn();
        return $intTotal;
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
}
