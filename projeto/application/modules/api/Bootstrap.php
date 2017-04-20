<?php
class Api_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initApi()
    {

//        $controller = Zend_Controller_Front::getInstance();
//        $response = $controller->getResponse();
//echo '<pre>';
//var_dump($response->setHeader('Content-Type', 'text/html; charset=utf-8'));
//exit;
//        /**
//         * When no Content-Type has been set, set the default text/html; charset=utf-8
//         */
//        $response->setHeader('Content-Type', 'text/html; charset=utf-8');
    }

    public function _initRouteRest()
    {
//        $frontController = Zend_Controller_Front::getInstance();
//        $restRoute = new Zend_Rest_Route(
//            $frontController,
//            array('module' => 'api'),
//            array(
//                'api' => array(
//                    'trabalhadores-acumulados',
//                    'trabalhadores-acumulados-por-ano',
//                    'trabalhadores-acumulados-por-localizacao',
//                    'trabalhadores-ativos',
//                    'trabalhadores-inativos',
//                )));
//        $frontController->getRouter()->addRoute('rest', $restRoute);
    }
}

