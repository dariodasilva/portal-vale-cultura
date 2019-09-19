<?php

include_once 'GenericController.php';

class Minc_ErrorController extends GenericController
{

    /**
     * Trata as excecoes para os usuarios
     * @access public
     * @param void
     * @return void
     */
    public function errorAction()
    {
        // limpa o conteudo gerado antes do erro
        $this->getResponse()->clearBody();

        // pega a excecao e manda para o template
        $this->_helper->viewRenderer->setViewSuffix('phtml');
        $error = $this->_getParam('error_handler');
        $this->view->ambiente = APPLICATION_ENV;
        $this->view->exception = $error->exception;
        $this->view->request = $error->request;

        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Página não encontrada!';
                break;

            default:
                $this->view->message = 'Ops, ocorreu um erro no sistema!';
                break;
        }
    }

// fecha errorAction()
}
