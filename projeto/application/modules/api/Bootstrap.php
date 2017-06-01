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

    /**
     * Implementa a rota para o servico rest apenas para o modulo api.
     *
     * @name _initRouteRest
     *
     * @author Ruy Junior Ferreira Silva <ruyjfs@gmail.com>
     * @since  26/05/2017
     *
     * @todo verificar futuramente uma forma melhor de implementar a rota só no modulo api, por enquanto foi implementado a logica na mao.
     */
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

        $frontController = Zend_Controller_Front::getInstance();

        $arRedirectUrl = explode('/', $_SERVER['REDIRECT_URL']);
        
        if (isset($_SERVER['REDIRECT_URL']) && is_int(strpos($_SERVER['REDIRECT_URL'], '/')) && $arRedirectUrl[1] == 'api') {
            $router = $frontController->getRouter();
            $router->addRoute('rest', new Zend_Rest_Route($frontController));
        }
    }
}

