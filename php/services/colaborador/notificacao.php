<?php

class notificacao {

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

    function getCountNotificacao($_usuario) {
        $retorno = '';
        $retorno->total=0;
        $query = "SELECT COUNT(c.id)AS contador
                    FROM colaborador_notificacao AS c
                    WHERE c.id_usuario = ? AND id_status_count=0";
        $params = array($_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno->total = $linha->contador;
        }
        return $retorno;
    }

    function setStatus($_usuario,$idReembolso=null){
        $obj = new notificacao();
        $retorno = $obj->getCountNotificacao($_usuario);
        $retorno->total = 0;
        return $retorno;
    }



}

?>