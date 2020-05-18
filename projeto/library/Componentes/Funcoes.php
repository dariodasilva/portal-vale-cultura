<?php

/* FUNÇÃO ÚTIL PARA DEBUG */
function xd($obj)
{
    if (getenv('APPLICATION_ENV') == 'desenvolvimento') {
        echo "<div style='background-color:#DFDFDF; border:1px #666666 solid; text-align:left;'>";
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
        echo "</div>";
        die();
    }
}

/* FUNÇÃO ÚTIL PARA DEBUG SEM  DIE */
function x($obj)
{
    if (getenv('APPLICATION_ENV') == 'desenvolvimento') {
        echo "<div style='background-color:#DFDFDF; border:1px #666666 solid; text-align:left;'>";
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
        echo "</div>";
    }
}

function gerarSenha()
{

    $senha = '12345678';

    if (APPLICATION_ENV == 'producao') {

        $lmin = 'abcdefghijklmnopqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';
        $caracteres = $lmin . $lmai . $num;

        for ($n = 0; $n < 8; $n++) {
            $rand = mt_rand(1, strlen($caracteres));
            $senha .= $caracteres[$rand - 1];
        }
    }

    return $senha;
}

function gerarCodigo()
{

    $lmin = 'abcdefghijklmnopqrstuvwxyz';
    $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $num = '1234567890';
    $caracteres = $lmin . $lmai . $num;

    $senha = '';

    for ($n = 0; $n < 15; $n++) {
        $rand = mt_rand(1, strlen($caracteres));
        $senha .= $caracteres[$rand - 1];
    }

    return $senha;
}

function removeAcentos($string, $slug = false)
{
    $string = strtolower($string);

    // CÃ³digo ASCII das vogais
    $ascii['a'] = range(224, 230);
    $ascii['e'] = range(232, 235);
    $ascii['i'] = range(236, 239);
    $ascii['o'] = array_merge(range(242, 246), array(240, 248));
    $ascii['u'] = range(249, 252);

    // CÃ³digo ASCII dos outros caracteres
    $ascii['b'] = array(223);
    $ascii['c'] = array(231);
    $ascii['d'] = array(208);
    $ascii['n'] = array(241);
    $ascii['y'] = array(253, 255);

    foreach ($ascii as $key => $item) {
        $acentos = '';
        foreach ($item AS $codigo)
            $acentos .= chr($codigo);
        $troca[$key] = '/[' . $acentos . ']/i';
    }

    $string = preg_replace(array_values($troca), array_keys($troca), $string);

    // Slug?
    if ($slug) {
        // Troca tudo que nÃ£o for letra ou número por um caractere ($slug)
        $string = preg_replace('/[^a-z0-9]/i', '-', $string);
        // Tira os caracteres ($slug) repetidos
        $string = preg_replace('/' . $slug . '{2,}/i', $slug, $string);
        $string = trim($string, $slug);
    }

    return $string;
}

function addMascara($str = '', $tipo = 'cnpj')
{

    switch ($tipo) {
        case 'cnpj':
            $str = substr($str, 0, 2) . '.' . substr($str, 2, 3) . '.' . substr($str, 5, 3) . '/' . substr($str, 8, 4) . '-' . substr($str, 12);

            break;

        case 'cpf':
            $str = substr($str, 0, 3) . '.' . substr($str, 3, 3) . '.' . substr($str, 6, 3) . '-' . substr($str, 9);

            break;

        case 'cep':
            $str = substr($str, 0, 5) . '-' . substr($str, 5);

            break;

        case 'telefone':
            if (strlen($str) > 10) {
                $str = '(' . substr($str, 0, 2) . ') ' . substr($str, 2, 5) . '-' . substr($str, 7);
            } else {
                $str = '(' . substr($str, 0, 2) . ') ' . substr($str, 2, 4) . '-' . substr($str, 6);
            }
            break;

        default:
            $str = $str;
            break;
    }
    return $str;
}

function retornaDigitos($str = '')
{
    $caracters = array("/", "\\", "_", ".", ",", ":", ";", "-", "[", "]", "{", "}", "(", ")", " ");

    foreach ($caracters as $caracter) {
        $str = str_replace($caracter, "", trim($str));
    }

    return $str;
}

function validaCPF($cpf)
{ // Verifiva se o número digitado contém todos os digitos
    $cpf = str_pad(preg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);

    if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
        return false;
    } else {   // Calcula os numeros para verificar se o CPF é verdadeiro
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf{$c} != $d) {
                return false;
            }
        }

        return true;
    }
}

function validaCNPJ($cnpj)
{
    //Etapa 1: Cria um array com apenas os digitos numÃ©ricos, isso permite receber o cnpj em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
    $num = array();
    $j = 0;
    for ($i = 0; $i < (strlen($cnpj)); $i++) {
        if (is_numeric($cnpj[$i])) {
            $num[$j] = $cnpj [$i];
            $j++;
        }
    }

    //Etapa 2: Conta os dÃ­gitos, um Cnpj vÃ¡lido possui 14 dÃ­gitos numÃ©ricos.
    if (count($num) != 14) {
        return false;
    }
    //Etapa 3: O número 00000000000 embora nÃ£o seja um cnpj real resultaria um cnpj vÃ¡lido apÃ³s o calculo dos dÃ­gitos verificares e por isso precisa ser filtradas nesta etapa.
    if ($num[0] == 0 && $num[1] == 0 && $num[2] == 0 && $num[3] == 0 && $num[4] == 0 && $num[5] == 0 && $num[6] == 0 && $num[7] == 0 && $num[8] == 0 && $num[9] == 0 && $num[10] == 0 && $num[11] == 0) {
        return false;
    } //Etapa 4: Calcula e compara o primeiro dÃ­gito verificador.
    else {
        $j = 5;
        for ($i = 0; $i < 4; $i++) {
            $multiplica[$i] = $num[$i] * $j;
            $j--;
        }
        $soma = array_sum($multiplica);
        $j = 9;
        for ($i = 4; $i < 12; $i++) {
            $multiplica[$i] = $num[$i] * $j;
            $j--;
        }
        $soma = array_sum($multiplica);
        $resto = $soma % 11;
        if ($resto < 2) {
            $dg = 0;
        } else {
            $dg = 11 - $resto;
        }
        if ($dg != $num[12]) {
            return false;
        }
    }
    //Etapa 5: Calcula e compara o segundo dÃ­gito verificador.
    if (!isset($isCnpjValid)) {
        $j = 6;
        for ($i = 0; $i < 5; $i++) {
            $multiplica[$i] = $num[$i] * $j;
            $j--;
        }
        $soma = array_sum($multiplica);
        $j = 9;
        for ($i = 5; $i < 13; $i++) {
            $multiplica[$i] = $num[$i] * $j;
            $j--;
        }
        $soma = array_sum($multiplica);
        $resto = $soma % 11;
        if ($resto < 2) {
            $dg = 0;
        } else {
            $dg = 11 - $resto;
        }
        if ($dg != $num[13]) {
            return false;
        } else {
            return true;
        }
    }
}

function validaEmail($email)
{
    $conta = "^[a-zA-Z0-9\._-]+@";
    $domino = "[a-zA-Z0-9\._-]+.";
    $extensao = "([a-zA-Z]{2,4})$";

    $pattern = $conta . $domino . $extensao;

    if (@ereg($pattern, $email))
        return true;
    else
        return false;
}

function convertArrayKeysToUtf8(array $array)
{
    $convertedArray = array();
    foreach ($array as $key => $value) {
        if (!mb_check_encoding($key, 'UTF-8'))
            $key = utf8_encode($key);
        if (is_array($value)) {
            $value = convertArrayKeysToUtf8($value);
        } else {
            if (!mb_check_encoding($value, 'UTF-8'))
                $value = utf8_encode($value);
        }

        $convertedArray[$key] = $value;
    }
    return $convertedArray;
}

function uc_latin1($str)
{
    $str = strtoupper(strtr($str, LATIN1_LC_CHARS, LATIN1_UC_CHARS));
    return strtr($str, array("ß" => "SS"));
}

function strtolower_iso8859_1($s)
{
    $i = strlen($s);
    while ($i > 0) {
        --$i;
        $c = ord($s[$i]);
        if (($c & 0xC0) == 0xC0) {
            // two most significante bits on
            if (($c != 215) and ($c != 223)) { // two chars OK as is
                // to get lowercase set 3. most significante bit if needed:
                $s[$i] = chr($c | 0x20);
            }
        }
    }
    return strtolower($s);
}

function carregaHTMLCertificado()
{

    $url = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'index.php'));
    $img = $url . 'img/Coat_of_arms_of_Brazil.gif';
    $html = '
            <center>
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center" colspan="3">
                    <img src="' . $img . '"><br><br>
                    MINIST&Eacute;RIO DA CIDADANIA<br>
                    SECRETARIA DE FOMENTO E INCENTIVO &Agrave; CULTURA - SEFIC
                </td>
            </tr>
            <tr>
                <td align="center" colspan="3" style="padding: 10px;">
                    CERTIFICADO DE INSCRI&Ccedil;&Atilde;O NO PROGRAMA DE CULTURA DO TRABALHADOR
                </td>
            </tr>
            <tr>
                <td align="center" colspan="3" style="padding: 10px 10px 20px 10px;">
                    EMPRESA OPERADORA
                </td>
            </tr>
            <tr>
                <td align="left">
                    #N_CERTIFICADO#/#ANO_CERTIFICADO#
                </td>
                <td>
                </td>
                <td align="right">
                    #DATA#
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    <table style="width: 100%">
                        <tr>
                            <td align="center" width="400px">
                                CNPJ<br>
                                #CNPJ#
                            </td>
                            <td align="center" width="400px">
                                Raz&atilde;o Social<br>
                                #RAZAO#
                            </td>
                            <td align="center" width="400px">
                                Nome Fantasia<br>
                                #FANTASIA#
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="10">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    <table style="width: 100%">
                        <tr>
                            <td align="center" width="400px">
                                Endere&ccedil;o<br>
                                #ENDERECO#
                            </td>
                            <td align="center" width="400px">
                                Bairro<br>
                                #BAIRRO#
                            </td>
                            <td align="center" width="400px">
                                CEP<br>
                                #CEP#
                            </td>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="10">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    <table style="width: 100%">
                        <tr>
                            <td align="center" width="400px">
                                Pa&iacute;s<br>
                                #PAIS#
                            </td>
                            <td align="center" width="400px">
                                Estado<br>
                                #ESTADO#
                            </td>
                            <td align="center" width="400px">
                                Munic&iacute;pio<br>
                                #MUNICIPIO#
                            </td>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="10">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    #RESPONSAVEIS#
                </td>
            </tr>
        </table>
    </center>';

    return $html;
}

function carregaHTMLCertificadoBeneficiaria()
{

    $url = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'index.php'));
    $img = $url . 'img/Coat_of_arms_of_Brazil.gif';
    $html = '
            <center>
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center" colspan="3">
                    <img src="' . $img . '"><br><br>
                    MINIST&Eacute;RIO DA CIDADANIA<br>
                    SECRETARIA DE FOMENTO E INCENTIVO &Agrave; CULTURA - SEFIC
                </td>
            </tr>
            <tr>
                <td align="center" colspan="3" style="padding: 10px;">
                    CERTIFICADO DE INSCRIÇ&Atilde;O NO PROGRAMA DE CULTURA DO TRABALHADOR
                </td>
            </tr>
            <tr>
                <td align="center" colspan="3" style="padding: 10px 10px 20px 10px;">
                    EMPRESA BENEFICI&Aacute;RIA
                </td>
            </tr>
            <tr>
                <td align="left">
                    #N_CERTIFICADO#/#ANO_CERTIFICADO#
                </td>
                <td>
                </td>
                <td align="right">
                    #DATA#
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    <table style="width: 100%">
                        <tr>
                            <td align="center" width="400px">
                                CNPJ<br>
                                #CNPJ#
                            </td>
                            <td align="center" width="400px">
                                Raz&atilde;o Social<br>
                                #RAZAO#
                            </td>
                            <td align="center" width="400px">
                                Nome Fantasia<br>
                                #FANTASIA#
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="10">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    <table style="width: 100%">
                        <tr>
                            <td align="center" width="400px">
                                Endere&ccedil;o<br>
                                #ENDERECO#
                            </td>
                            <td align="center" width="400px">
                                Bairro<br>
                                #BAIRRO#
                            </td>
                            <td align="center" width="400px">
                                CEP<br>
                                #CEP#
                            </td>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="10">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    <table style="width: 100%">
                        <tr>
                            <td align="center" width="400px">
                                Pa&iacute;s<br>
                                #PAIS#
                            </td>
                            <td align="center" width="400px">
                                Estado<br>
                                #ESTADO#
                            </td>
                            <td align="center" width="400px">
                                Munic&iacute;pio<br>
                                #MUNICIPIO#
                            </td>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="10">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    #RESPONSAVEIS#
                </td>
            </tr>
            <tr>
                <td colspan="3" height="10">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 0px 5px 0px; margin-top: 15px" colspan="3">
                    <table style="width: 100%">
                        <tr>
                            <td align="center" width="400px">
                                #CNAE_PRINCIPAL#<br>
                                (CNAE)
                            </td>
                            <td align="center" width="400px">
                                #CNAE_SECUNDARIOS#<br>
                                Secund&aacute;rias (CNAE)
                            </td>
                            <td align="center" width="400px">
                                #NATJUR#<br>
                                Natureza Jurídica
                            </td>
                    </table>
                </td>
            </tr>
        </table>
    </center>';

    return $html;
}

function emailSenhaHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        
                        <br>
                        Olá! Muito obrigado por aderir ao Vale-Cultura. O cadastro de sua empresa foi realizado com sucesso!
                        <br>
                        Em breve você receberá uma nova mensagem sobre a avaliação do seu cadastro.
                        <br>
                        Para acessar o sistema utilize os dados:
                        <br><br>
                        <b>URL:</b> <a href="#URL#" target="_blank" style="text-decoration: underline;">#URL#</a>
                        <br>
                        <b>Senha:</b> #Senha#
                        <br><br>
                        Em caso de dúvidas, sugestões, reclamações ou denúncias, envie e-mail para <a href="mailto:#EMAIL#" style="text-decoration: underline;">#EMAIL#</a>.
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}

function emailNoSenhaHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        <br>
                        Olá! Muito obrigado por aderir ao Vale-Cultura. O cadastro de sua empresa foi realizado com sucesso!
                        <br>
                        Em breve você receberá uma nova mensagem sobre a avaliação do seu cadastro.
                        <br>
                        Para acessar o sistema utilize os dados:
                        <br><br>
                        <b>URL:</b> <a href="#URL#" target="_blank" style="text-decoration: underline;">#URL#</a>
                        <br>
                        A sua senha já foi enviada anteriormente, para alterá-la acesse <a href="#URL#" target="_blank" style="text-decoration: underline;">#URL#</a> e clique em "Esqueceu a senha?"
                        <br><br>
                        Em caso de dúvidas, sugestões, reclamações ou denúncias, envie e-mail para <a href="mailto:#EMAIL#" style="text-decoration: underline;">#EMAIL#</a>.
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}

function emailContatoHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Contato - Vale-Cultura</h3>
                        <br><br>
                        Contato realizado via sistema Vale Cultura<br><br>
                        <table border="0">
                            <tr>
                                <td>
                                    Nome:
                                </td>
                                <td>
                                    #NOME#
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    E-mail:
                                </td>
                                <td>
                                    #EMAIL#
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Assunto:
                                </td>
                                <td>
                                    #ASSUNTO#
                                </td>
                            </tr>
                            <tr>
                                <td valign="top">
                                    Mensagem:
                                </td>
                                <td>
                                    #MENSAGEM#
                                </td>
                            </tr>
                        </table>
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}

function emailNovaSenhaHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        <br><br>
                        Oi #NOME_USUARIO#, uma redefinição de senha foi solicitada para o seu acesso ao sistema do Vale-Cultura.
                        <br>
                        Para confirmar este pedido e definir uma nova senha para o seu acesso, por favor, clique no link abaixo:
                        <br>
                        <a href="#URL#" target="_blank" style="text-decoration: underline;">#URL#</a>
                        <br><br>
                        Se esta redefinição de senha não foi solicitada por você, nenhuma ação é necessária. 
                        <br>
                        Se precisar de ajuda, envie e-mail para <a href="mailto:#EMAIL#" style="text-decoration: underline;">#EMAIL#</a>.
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}

function emailAprovacaoHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        <br><br>
                        Olá <b>#NOMERESPONSAVEL#</b>,
                        <br><br>
                        O cadastro da #PERFIL# #NOMEEMPRESA# no Vale-Cultura foi aprovado.
                        <br>
                        Para consultar o certificado de inscrição, acesse <a href="#URL#" target="_blank" style="text-decoration: underline;">#URL#</a>.
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}

function emailAprovacaoOperadoraHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        <br><br>
                        Olá <b>#NOMERESPONSAVEL#</b>,
                        <br><br>
                        O cadastro da operadora #NOMEEMPRESA# no Vale-Cultura foi aprovado.
                        <br>
                        Agora sua empresa pode habilitar os estabelecimentos comerciais que vendem os produtos culturais previstos no anexo I da Instrução Normativa n°2/2013.
                        <br>
                        Além disso, sua empresa também está autorizada a produzir e comercializar o cartão Vale-Cultura para as empresas Beneficiárias cadastradas no programa.
                        <br>
                        Para consultar o certificado de inscrição, acesse <a href="#URL#" target="_blank" style="text-decoration: underline;">#URL#</a> e faça o seu login.
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}


function emailAprovacaoBeneficiariaHTML()
{
    $links = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('link');

    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        <br><br>
                        Parabéns! O seu cadastro foi aprovado!
                        <br><br>
                        A sua participação estimula o desenvolvimento cultural e social dos trabalhadores de sua empresa e do País.
                        <br><br>
                        A sua decisão de conceder o Vale-Cultura no valor fixo mensal de R$50,00 aos seus empregados vai permitir que eles comprem instrumentos
                        musicais, CDs, DVDs, livros, revistas e jornais , ingressos para teatro, cinema, museus,
                        e também paguem mensalidades de cursos de artes, audiovisual, dança, circo, fotografia, música, literatura ou teatro.
                        <br><br>
                        Esqueceu sua senha? <a href="#URL#">Clique aqui</a>
                        <br>
                        O próximo passo é entrar em contato com a operadora de sua preferência, escolhida no ato do cadastro,
                        para que ela possa emitir os cartões Vale-Cultura que serão utilizados pelos empregados da sua empresa.
                        <br>
                        No seu cadastro, você já escolheu a operadora #NOMEOPERADORA# e o telefone de contato dela é #SAC#.
                        <br>
                        Caso não esteja satisfeito com a operadora escolhida, você pode optar por outra.
                        Há uma lista das credenciadas pelo MinC na página inicial do sistema Vale-Cultura: <a href="#URL#">#URL#</a>
                        <br>
                        Em caso de dúvidas, sugestões, reclamações ou denúncias, envie e-mail para <a href="mailto:' . $links['email-vale-cultura'] . '">' . $links['email-vale-cultura'] . '</a>.
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}

function emailReprovacaoBeneficiariaHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        <br><br>
                        Olá <b>#NOMERESPONSAVEL#</b>,
                        <br><br>
                        Informamos que o seu cadastro no Programa de Cultura do Trabalhador não foi aprovado.
                        <br>
                        Portanto, pedimos que acesse o sistema para verificar o motivo da reprovação.
                        <br>
                        Entre no site: <a href="#URL#" target="_blank" style="text-decoration: underline;">#URL#</a> e faça o seu login para consultar.
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}

function emailAprovacaoBeneficiariaParaOperadoraHTML()
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Vale-Cultura</title>
                    </head>
                    <body>
                        <h3>Vale-Cultura</h3>
                        <br><br>
                        A empresa beneficiária do Vale Cultura: #NOMEBENEFICIARIA# - #CNPJBENEFICIARIA# acabou de ser habilitada no programa.
                        <br>
                        Esta empresa escolheu a Operadora: #NOMEOPERADORA# como preferencial fornecedora de cartão.
                        <br>
                        Dados para contato da empresa: <br>
                        #NOMEBENEFICIARIA# - #CNPJBENEFICIARIA# 
                        <br><br>
                        #RESPONSAVEIS# 
                        <br><br>
                        Atenciosamente,
                        <br><br>
                        <b>Secretaria de Fomento e Incentivo à Cultura</b>
                        <br>
                        <b>Ministério da Cidadania</b>
                    </body>
                </html>';
    return utf8_decode($html);
}
