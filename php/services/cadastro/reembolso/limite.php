<?php

class limite {

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

    function getLimiteTodos($s_usuario) {
        $retorno = array();
        $query = "SELECT n.Codigo AS codigo
                         ,n.Descricao AS natureza
                         ,li.limite AS limite
                  FROM reembolso_limites AS li
                  LEFT JOIN corporativo_vwNaturezas AS n ON n.Codigo = li.id_natureza";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }
    function getLimite($s_usuario,$id_natureza) {
        $retorno = array();
        $query = "SELECT li.limite AS limite
                  FROM reembolso_limites AS li
                  WHERE li.id_natureza=?";
        $params = array($id_natureza);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setLimite($s_usuario, $id_natureza, $limite) {
        $query = "UPDATE reembolso_limites
                    SET 
                    limite = ?
                    WHERE id_natureza = ?";
        $params = array($limite,$id_natureza);
        $resul = sqlsrv_query($this->link, $query, $params);
        if(($errors=sqlsrv_errors())!= null){
            print_r($errors);
        }
        $obj = new limite();
        return $obj->getLimiteTodos($s_usuario);
    }

}

?>