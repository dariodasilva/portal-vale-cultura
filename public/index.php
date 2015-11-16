<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath('../projeto/application'));


// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'homologacao'));

//DEFINE CONSTANTES DO SISTEMA VALE CULTURA
        define('ID_SITUACAO_AGUARDANDO_ANALISE', 1);
        define('ID_SITUACAO_AUTORIZADO', 2);
        define('ID_SITUACAO_NAO_AUTORIZADO', 3);
        define('ID_SITUACAO_INATIVO', 4);
        define('NAO_AUTORIZADO_DIVULGAR_INFORMACAO', 0);
        define('AUTORIZADO_OPERADORA_SELECIONADA', 1);
        define('AUTORIZADO_TODAS_OPERADORAS', 2);

defined('UPLOAD_DIR')
    || define('UPLOAD_DIR', APPLICATION_ENV == 'desenvolvimento' ? 'arquivos/' : '/var/arquivos/arquivos-valecultura/');

define("LATIN1_UC_CHARS", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖØÙÚÛÜİ");
define("LATIN1_LC_CHARS", "àáâãäåæçèéêëìíîïğñòóôõöøùúûüı");

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

/** Funcoes */
require_once 'Componentes/Funcoes.php';
require_once 'Componentes/Servicos.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

if (!defined('RUN_CLI_MODE') || RUN_CLI_MODE === false) {
    $application->bootstrap()->run();
}
