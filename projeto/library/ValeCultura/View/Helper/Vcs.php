<?php

require_once 'Zend/View/Helper/Abstract.php';

class ValeCultura_View_Helper_Vcs extends Zend_View_Helper_Abstract
{

    public function vcs()
    {
        $vcs = $this->getVcs();
        if ($vcs) {
            $functionName = "get" . ucfirst($vcs) . "FullVersion";
            if (is_callable(array($this, $functionName))) {
                return $this->$functionName();
            }
        }
    }

    private function getVcs()
    {
        $folders = array('.git', '.svn');
        foreach ($folders as $vcs) {
            if (is_dir(realpath(constant('APPLICATION_PATH') . "/../../{$vcs}"))) {
                return substr($vcs, 1);
            }
        }
    }

    /**
     * Retorna a versão do projeto, seja branch ou tag
     */
    private function getGitFullVersion()
    {
        exec("git rev-list HEAD | wc -l", $revisionNumber);
        exec("git rev-parse --abbrev-ref HEAD", $branchName);

        return "Branch|Tag: " . array_pop($branchName) . " Revisão: " . array_pop($revisionNumber);
    }

    /**
     * Retorna a versão do projeto, seja branch ou tag
     */
    private function getSvnFullVersion()
    {
        exec("svn info | grep \"Revision\" | awk '{print $2}'", $revisionNumber);
        exec("svn info | grep '^URL:' | egrep -o '(tags|branches)/[^/]+|trunk' | egrep -o '[^/]+$'", $branchName);

        return "Branch|Tag: " . array_pop($branchName) . " Revisão: " . array_pop($revisionNumber);
    }

}
