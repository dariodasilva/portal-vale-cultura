<?php

include_once 'GenericController.php';

class Api_ConsumosController extends ValeCultura_Controller_Rest_Abstract
{
    public function indexAction()
    {
        echo Zend_Json::encode([
            'disponiveis' => [
                'get',
                'post',
                'put',
                'delete',
            ]
        ]);
    }
    /**
     * The get action handles GET requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function getAction()
    {
        $arrResult = [];
        $arrResult[] = ['ano' => 2014, 'mes' => 'Janeiro', 'regiao' => 'Brasilia', 'estado' => 'DF', 'qtd_trabalhadores'=> 3054];
        $arrResult[] = ['ano' => 2014, 'mes' => 'Janeiro', 'regiao' => 'Brasilia', 'estado' => 'SP', 'qtd_trabalhadores'=> 3054];
        $arrResult[] = ['ano' => 2014, 'mes' => 'Janeiro', 'regiao' => 'Brasilia', 'estado' => 'MG', 'qtd_trabalhadores'=> 3054];
        $arrResult[] = ['ano' => 2014, 'mes' => 'Janeiro', 'regiao' => 'Brasilia', 'estado' => 'GO', 'qtd_trabalhadores'=> 3054];
        $arrResult[] = ['ano' => 2014, 'mes' => 'Janeiro', 'regiao' => 'Brasilia', 'estado' => 'MT', 'qtd_trabalhadores'=> 3054];
//
//        echo Zend_Json::encode($arrResult);
        $this->getResponse()->setBody(Zend_Json::encode($arrResult));
        $this->getResponse()->setHttpResponseCode(200);
    }

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

