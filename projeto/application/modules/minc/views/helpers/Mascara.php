<?php
/**
 * Description of Mascara
 *
 * @author tarcisio
 */
class Zend_View_Helper_Mascara {

    /**
     * Método para adicionar máscaras nos campos
     * @access public
     * @param integer $valor
     * @param integer $mascara
     * @return string
     */
    public function mascara($valor = null, $mascara = null){
        
        $valorSaida = $valor;
    
        if (isset($valor) && !empty($valor) && isset($mascara) && !empty($mascara)) {
            $valorSaida = addMascara($valor, $mascara);
        }
        
        return $valorSaida;
        
    }
    
    
}

?>
