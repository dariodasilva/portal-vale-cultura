<?php

/**
 * Helper para verificar o status da operadora
 *
 * @author tarcisio
 */
class Zend_View_Helper_VerificarSituacao
{

    /**
     * MÃ©todo para verificar o status da operadora
     * @access public
     * @param integer $idOperadora
     * @return string
     */
    public function verificarSituacao($idSituacao = null, $info = null, $tipo = 'O')
    {

        $dadosSituacao = array();
        $dadosSituacao['idTipoSituacao'] = 0;
        $dadosSituacao['dsSituacao'] = 'Situação não encontrada!';
        $dadosSituacao['corSituacao'] = '#E00000';

        if (!empty($idSituacao)) {
            $dadosSituacao['idTipoSituacao'] = $idSituacao;

            switch ($idSituacao) {
                case ID_SITUACAO_AGUARDANDO_ANALISE:
                    $dadosSituacao['corSituacao'] = '#FF7B00';
                    $dadosSituacao['dsSituacao'] = 'Aguardando analise';
                    break;

                case ID_SITUACAO_AUTORIZADO:
                    $dadosSituacao['corSituacao'] = '#66B20A';
                    $dadosSituacao['dsSituacao'] = 'Autorizado';
                    break;

                case ID_SITUACAO_NAO_AUTORIZADO:
                    $dadosSituacao['corSituacao'] = '#E00000';
                    $dadosSituacao['dsSituacao'] = 'Não autorizado';
                    break;

                case ID_SITUACAO_INATIVO:
                    $dadosSituacao['corSituacao'] = '#FF7B00';
                    $dadosSituacao['dsSituacao'] = 'Inativo';
                    break;

                default:
                    $dadosSituacao['corSituacao'] = '#E00000';
                    $dadosSituacao['dsSituacao'] = 'Situação não encontrada!';
                    break;
            }

        }

        if (!empty($info)) {
            if ($info == 'id') {
                return $dadosSituacao['idTipoSituacao'];
            } else if ($info == 'st') {
                return $dadosSituacao['dsSituacao'];
            } else if ($info == 'cor') {
                return $dadosSituacao['corSituacao'];
            }
        } else {
            return $dadosSituacao['dsSituacao'];
        }
    }

}

?>
