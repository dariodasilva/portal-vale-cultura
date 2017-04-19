<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function _initRouteRest()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $restRoute = new Zend_Rest_Route(
            $frontController,
            array('module' => 'api'),
            array(
                'api' => array(
                    'trabalhadores-acumulados',
                    'trabalhadores-acumulados-por-ano',
                    'trabalhadores-acumulados-por-localizacao',
                    'trabalhadores-ativos',
                    'trabalhadores-inativos',
                )));
        $frontController->getRouter()->addRoute('rest', $restRoute);
    }
}
