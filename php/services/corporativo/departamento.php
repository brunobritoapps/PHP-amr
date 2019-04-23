<?php

class departamento {

    var $link;

    function __construct() {
        if (file_exists('parametros.php')) {
            require_once('parametros.php');
        } else {
            require_once('..\parametros.php');
        }
        $this->link = conexao("base");
        //error_reporting(E_ALL );
    }

    function getDepartamento($s_usuario) {
        $retorno = array();
        $query = "SELECT d.id
                        ,d.codigo
                        ,d.descricao
                    FROM corporativo_departamento AS d";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }
}

?>