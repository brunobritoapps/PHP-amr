<?php

class empresa {

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

    function getEmpresa($s_usuario) {
        $retorno = array();
        $query = "SELECT  e.Cod_Empresa AS id, e.Nome_Empresa AS empresa , e.Cod_Filial, dbo.fString(e.Nome_Filial)AS filial
                    FROM corporativo_vwEmpresas AS e";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getEmpresaUsuario($s_usuario) {
        $retorno = array();
        $query = "DECLARE @ID_USUARIO int
                        SET @ID_USUARIO=?
                        SELECT DISTINCT e.Cod_Empresa AS id ,e.Nome_Empresa AS empresa
                        FROM vwEmpresas AS e
                        WHERE e.Cod_Empresa IN (SELECT SUBSTRING (id_ccusto,1,1)FROM usuario_ccusto AS uc WHERE uc.id_usuario =@ID_USUARIO)";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

}

?>