<?php

include_once 'GenericController.php';

class Api_ConsumosController extends ValeCultura_Controller_Rest_Abstract
{
    public function indexAction() {
        $ano = $this->getRequest()->getParam('ano');
        $mes = $this->getRequest()->getParam('mes');
        $regiao = $this->getRequest()->getParam('regiao');
        $uf = $this->getRequest()->getParam('uf');
        
        $mdl = new Application_Model_AcumuladoConsumo();    

        if (isset($regiao) || isset($uf)) {
            $this->getResponse()->setBody(Zend_Json::encode($mdl->getPorLocalizacao($regiao, $uf)));
        } else if (isset($ano) || isset($mes)) {
            $this->getResponse()->setBody(Zend_Json::encode($mdl->getPorData($ano, $mes)));
        } else {
            $this->getResponse()->setBody(Zend_Json::encode($mdl->getTotal()));
        }
        $this->getResponse()->setHeader('Content-Type', 'application/json; charset=utf-8');
    }
    
    /**
     * The get action handles GET requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function getAction() {}

    /**
     * The head action handles HEAD requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function headAction(){

    }

    /**
     * The post action handles POST requests; it should accept and digest a
     * POSTed resource representation and persist the resource state.
     */
    public function postAction(){

    }

    /**
     * The put action handles PUT requests and receives an 'id' parameter; it
     * should update the server resource state of the resource identified by
     * the 'id' value.
     */
    public function putAction(){

    }

    /**
     * The delete action handles DELETE requests and receives an 'id'
     * parameter; it should update the server resource state of the resource
     * identified by the 'id' value.
     */
    public function deleteAction(){

    }
}

