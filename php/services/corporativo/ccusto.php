<?php

class ccusto {

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

    function getCcusto($s_usuario,$empresa,$unidade) {
        $retorno = array();
        $query = "SELECT c.Codigo AS id,c.Descricao AS descricao
                    FROM corporativo_vwCcustos AS c
                    WHERE c.Codigo LIKE '".$empresa.$unidade."%'" ;
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $linha->id=trim($linha->id);
            $linha->descricao=trim($linha->descricao);
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getCcustoUsuario($s_usuario,$id) {
        $retorno = array();
        $query = "SELECT uc.id_usuario, uc.id_ccusto, cc.Descricao AS ccusto
                    FROM usuario_ccusto AS uc
                    LEFT JOIN corporativo_vwCcustos AS cc ON cc.Codigo = uc.id_ccusto
                    WHERE uc.id_usuario = ?";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $linha->id_ccusto=trim($linha->id_ccusto);
            $linha->ccusto=trim($linha->ccusto);
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getCcustoUsuarioReembolso($s_usuario,$empresa) {
        $retorno = array();
        $query = "SELECT uc.id_ccusto
                  FROM usuario_ccusto AS uc
                  WHERE uc.id_usuario=? AND SUBSTRING (uc.id_ccusto,1,1)=?";
        $params = array($s_usuario, $empresa);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[]=$linha;
        }
        return $retorno;
    }

    function getCcustoAll($s_usuario) {
        $retorno = array();
        $query = "SELECT c.Codigo AS id,c.Descricao AS descricao
                    FROM corporativo_vwCcustos AS c" ;
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $linha->id=trim($linha->id);
            $linha->descricao=trim($linha->descricao);
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDepartamento($s_usuario) {
        $retorno = array();
        $query = "SELECT d.id, d.descricao
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