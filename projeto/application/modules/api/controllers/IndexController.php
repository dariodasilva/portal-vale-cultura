<?php

include_once 'GenericController.php';

class Api_IndexController extends ValeCultura_Controller_Rest_Abstract {

    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        parent::init();
    }

    public function indexAction() {
        $this->getResponse()->setBody(Zend_Json::encode(
            array(
                'disponiveis' => array(
                    'trabalhadores-acumulados',
                    'trabalhadores-acumulados-por-ano',
                    'trabalhadores-acumulados-por-localizacao',
                    'trabalhadores-ativos',
                    'trabalhadores-inativos',
                    'beneficiarias-acumuladas',
                    'beneficiarias-acumuladas-por-ano',
                    'beneficiarias-acumuladas-por-localizacao',
                    'beneficiarias-ativas',
                    'beneficiarias-inativas',
                    'consumo-por-periodo',
                    'consumo-por-localizacao',
                )
            )
        ));
    }

    /**
     * The get action handles GET requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function getAction(){}

    /**
     * The head action handles HEAD requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function headAction(){}

    /**
     * The post action handles POST requests; it should accept and digest a
     * POSTed resource representation and persist the resource state.
     */
    public function postAction(){}

    /**
     * The put action handles PUT requests and receives an 'id' parameter; it
     * should update the server resource state of the resource identified by
     * the 'id' value.
     */
    public function putAction(){}

    /**
     * The delete action handles DELETE requests and receives an 'id'
     * parameter; it should update the server resource state of the resource
     * identified by the 'id' value.
     */
    public function deleteAction(){}
}

