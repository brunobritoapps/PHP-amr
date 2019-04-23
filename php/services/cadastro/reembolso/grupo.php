<?php

class grupo {

    var $link;

    function __construct() {
        //error_reporting(E_ALL );
        if (file_exists('parametros.php')) {
            require_once('parametros.php');
        } else {
            require_once('..\parametros.php');
        }
        $this->link = conexao("base");
        require_once 'services/email/email.php';
    }

    function getGrupo($s_usuario) {
        $retorno = array();
        $query = "SELECT g.id
                        ,g.nome
                        ,d.id AS id_departamento
                        ,d.descricao AS departamento
                        ,g.descricao
                        ,(SELECT COUNT(id_grupo) AS n FROM reembolso_aprovador_usuario WHERE id_grupo=g.id)AS numero_aprovadores
                    FROM reembolso_aprovador_grupo AS g
                    LEFT JOIN corporativo_departamento AS d ON d.id = g.id_departamento              
                    ORDER BY g.nome";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getGrupoAprovador($s_usuario) {
        $retorno = array();
        $query = "SELECT DISTINCT
                     g.id
                    ,g.nome
                    ,d.descricao AS departamento
                    ,g.descricao
                    ,(SELECT COUNT(id_grupo) AS n FROM reembolso_aprovador_usuario WHERE id_grupo=g.id)AS numero_aprovadores
                FROM reembolso_aprovador_grupo AS g
                LEFT JOIN corporativo_departamento AS d ON d.id = g.id_departamento
                LEFT JOIN reembolso_aprovador_usuario AS rau ON rau.id_grupo = g.id
                WHERE rau.id_usuario =?
                ORDER BY g.nome";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getInfoAprovadores($s_usuario,$id_grupo) {
        $retorno = array();
        $query = "SELECT u.id
                      ,u.usuario
                      ,u.nome
                      ,u.sobrenome
                      ,e.Nome_Empresa AS empresa
                       ,(SELECT TOP 1 d.descricao FROM usuario_ccusto AS cc2
                        LEFT JOIN corporativo_departamento AS d ON d.codigo = SUBSTRING (cc2.id_ccusto,4,3)
                        WHERE cc2.id_usuario = u.id ) AS departamento
                      ,cc.Descricao AS ccusto
                      ,cc.Codigo AS id_ccusto
                      ,apu.ordem
                      ,apu.alcada_inicio
                      ,apu.alcada_fim
                      ,g.nome AS grupo
                      ,g.descricao AS descricao_grupo
                  FROM reembolso_aprovador_usuario AS apu
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = apu.id_grupo
                        LEFT JOIN usuario AS u ON u.id = apu.id_usuario
                        LEFT JOIN corporativo_vwCcustos AS cc ON cc.Codigo = (SELECT TOP 1 cc1.id_ccusto FROM usuario_ccusto AS cc1 WHERE cc1.id_usuario = u.id)
                        LEFT JOIN corporativo_vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0) = SUBSTRING (cc.Codigo,1,1)
                  WHERE apu.id_grupo=?
                  ORDER BY apu.ordem
                  ";
        $params = array($id_grupo);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getTipoAprovador($s_usuario) {
        $retorno = array();
        $query = "SELECT n.id , n.descricao AS tipo
                    FROM reembolso_aprovador_nivel AS n
                    ORDER BY n.id";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setGrupo($s_usuario, $id, $nome, $descricao, $id_departamento, $arrUsuarios) {
        $retorno = array();
        if ($id == "") {//inserir
            $query = "INSERT INTO reembolso_aprovador_grupo(nome, descricao,id_departamento)
                        OUTPUT inserted.id
                        VALUES(?, ?, ?)";
            $params = array($nome, $descricao,$id_departamento);
        } else {//editar
                $query = "UPDATE reembolso_aprovador_grupo
                        SET 
                        nome = ?,
                        descricao = ?, 
                        id_departamento = ?
                        OUTPUT inserted.id
                        WHERE id = ?";
                $params = array($nome,$descricao,$id_departamento,$id);
        }
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $idGrupo = $linha->id;
        //
        $query = "DELETE FROM reembolso_aprovador_usuario
                    WHERE id_grupo = ?";
        $params = array($idGrupo);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        //
        foreach ($arrUsuarios as $usuario) {
            $query = "INSERT INTO reembolso_aprovador_usuario(id_grupo, id_usuario,ordem,alcada_inicio, alcada_fim)
                      VALUES(?,?,?,CAST(? AS DECIMAL(18, 2)),CAST(? AS DECIMAL(18, 2)))";
            $params = array($idGrupo,$usuario{'id'},$usuario{'ordem'},$usuario{'alcada_de'},$usuario{'alcada_ate'});
            $resul = sqlsrv_query($this->link, $query, $params);
        }

        $objEmail = new email();
        $valid_email = $objEmail->e3cAdicionadoGrupoAprovacao($s_usuario,$nome,$id_departamento,$arrUsuarios);
        if($valid_email==1){
            $obj = new grupo();
            return $obj->getGrupo($s_usuario);
        }else{
            return 0;
        }
    }

    function remGrupo($s_usuario,$id) {
        $retorno = array();
        $query = "DELETE FROM reembolso_aprovador_grupo
                    WHERE id = ?";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        //
        $query = "DELETE FROM reembolso_aprovador_usuario
                    WHERE id_grupo = ?";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        //
        $obj = new grupo();
        return $obj->getGrupo($s_usuario);
    }
}

?>