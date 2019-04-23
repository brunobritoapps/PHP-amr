<?php

class cargo {

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

    function getCargo($s_usuario) {
        $retorno = array();
        $query = "SELECT c.id, c.codigo
                    FROM corporativo_departamento AS c";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }

        return $retorno;
    }

    function setCargo($s_usuario, $id, $cargo) {
        $retorno = array();
        if ($id == "") {//inserir
            $query = "SELECT id FROM corporativo_departamento AS c WHERE c.codigo = ?";
            $params = array($cargo);
            $resul = sqlsrv_query($this->link, $query, $params);
            $linha = sqlsrv_fetch_object($resul);
            if ($linha->id > 0) {
                die("Erro: Cargo já cadastrado");
            }
            $query = "INSERT INTO corporativo_departamento(codigo)
                        OUTPUT inserted.id
                        VALUES(?)";
            $params = array($cargo);
        } else {//editar
            $query = "UPDATE corporativo_departamento 
                    SET codigo = ?
                    WHERE id = ?";
            $params = array($cargo, $id);
        }
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $idUsuario = $linha->id;

        /*
        $query = "DELETE FROM usuario_empresa WHERE id_usuario = ?";
        $params = array($idUsuario);
        $resul = sqlsrv_query($this->link, $query, $params);

        for ($i = 0; $i < count($arrEmpresa); $i++) {
            $query = "INSERT INTO usuario_empresa(id_usuario, id_empresa) VALUES(?, ?)";
            $params = array($idUsuario, $arrEmpresa[$i]);
            $resul = sqlsrv_query($this->link, $query, $params);
        }
        */
        $obj = new cargo();
        return $obj->getCargo($s_usuario);
    }

    function remCargo($s_usuario, $id) {
        $retorno = array();
        $query = "DELETE FROM corporativo_departamento
                    WHERE id = ?";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        $obj = new cargo();
        return $obj->getCargo($s_usuario);
    }

}

?>