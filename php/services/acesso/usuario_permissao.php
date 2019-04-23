<?php

class usuario_permissao {

    var $link;
    var $link_erp;

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

    function getUsuario($s_usuario) {
        $retorno = array();
        $query = " SELECT u.id
                        ,u.usuario
                        ,u.nome
                        ,u.sobrenome
                        ,u.cpf AS cpf
                        ,g.nome AS grupo
                        ,CONCAT(u.ativo,s.status) AS status
                        FROM usuario AS u
                        LEFT JOIN contas AS c ON c.id = u.conta
                        LEFT JOIN usuario_status AS s ON s.id = u.ativo
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        ORDER BY u.ativo";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getResumoUsuario($s_usuario) {
        $retorno = array();
        $query = "SELECT u.id
                        ,u.usuario
                        ,u.nome
                        ,u.sobrenome
                        ,u.cpf
                        ,e.Nome_Empresa AS empresa
                        ,(SELECT TOP 1 d.descricao FROM usuario_ccusto AS ucc
                         LEFT JOIN corporativo_departamento AS d ON d.codigo = SUBSTRING (ucc.id_ccusto,4,3)
                         WHERE ucc.id_usuario = u.id ) AS departamento
                        FROM usuario AS u
                        LEFT JOIN corporativo_vwCcustos AS cc ON cc.Codigo = (SELECT TOP 1 cc1.id_ccusto FROM usuario_ccusto AS cc1 WHERE cc1.id_usuario = u.id)
                        LEFT JOIN corporativo_vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0) = SUBSTRING (cc.Codigo,1,1)
                        WHERE u.ativo=1";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }

        return $retorno;
    }

    function getResumoUsuarioById($s_usuario,$id) {
        $retorno = array();
        $query = "SELECT u.id
                        ,u.usuario
                        ,u.nome
                        ,u.sobrenome
                        ,u.cpf 
                        ,e.Nome_Empresa AS empresa
                        ,(SELECT TOP 1 d.descricao FROM usuario_ccusto AS ucc  
                         LEFT JOIN corporativo_departamento AS d ON d.codigo = SUBSTRING (ucc.id_ccusto,4,3)
                         WHERE ucc.id_usuario = u.id ) AS departamento
                        FROM usuario AS u
                        LEFT JOIN corporativo_vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0) = SUBSTRING (u.id_ccusto,1,1)
                        WHERE u.id=? AND u.ativo=1";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getInfoUsuario($s_usuario, $id_usuario) {
        $retorno = array();
        $query_1 = " SELECT u.id
                  ,u.usuario
                  ,u.nome
                  ,u.sobrenome
                  ,u.cpf AS cpf
                  ,u.conta AS id_conta
                  ,u.ativo AS id_status
                  ,s.status
                  ,u.ativo AS id_status
                  ,c.nome AS conta
                  ,cc.Descricao AS ccusto
                  ,cc.Codigo AS id_ccusto
                  ,g.id AS id_grupo
                  ,g.nome AS grupo
                  ,(SELECT TOP 1 Nome_Empresa FROM usuario_ccusto AS cc1 LEFT JOIN vwEmpresas ON Cod_Empresa = SUBSTRING (cc1.id_ccusto,1,1) WHERE cc1.id_usuario=u.id) AS empresa
                  FROM usuario AS u
                  LEFT JOIN contas AS c ON c.id = u.conta
                  LEFT JOIN usuario_status AS s ON s.id = u.ativo
                  LEFT JOIN corporativo_vwCcustos AS cc ON cc.Codigo = (SELECT TOP 1 cc2.id_ccusto FROM usuario_ccusto AS cc2 WHERE cc2.id_usuario = ?)
                  LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                  WHERE u.id = ?";
        $params = array($id_usuario,$id_usuario);
        $resul_1 = sqlsrv_query($this->link, $query_1, $params);
        while ($linha_1 = sqlsrv_fetch_object($resul_1)) {
             $retorno[] = $linha_1;
        }
        return $retorno;
    }

    function getPerfil($s_usuario) {
        $retorno = array();
            $query = "SELECT u.id
                      ,u.usuario
                      ,u.nome
                      ,u.sobrenome
                      ,u.cpf AS cpf
                      ,cct.Codigo AS id_ccusto
                      ,cct.Descricao AS descricao
                      ,c.nome AS conta
                      ,u.conta AS id_conta
                      ,e.Nome_Empresa AS empresa
                      ,e.Cod_Empresa AS id_empresa
                      ,e.Nome_Filial AS filial
                      ,(SELECT TOP 1 UPPER(d.descricao)
                      FROM usuario_ccusto AS cc1
                      LEFT JOIN corporativo_departamento AS d ON d.codigo = SUBSTRING (cc1.id_ccusto,4,3)
                      WHERE cc1.id_usuario = u.id ) AS departamento
                      ,(SELECT TOP 1 SUBSTRING (cc2.id_ccusto,2,2)
                      FROM usuario_ccusto AS cc2
                      WHERE cc2.id_usuario = u.id ) AS unidade
                      ,(SELECT TOP 1 SUBSTRING (cc3.id_ccusto,4,3)
                      FROM usuario_ccusto AS cc3
                      WHERE cc3.id_usuario = u.id ) AS id_departamento
                      FROM usuario AS u
                      INNER JOIN contas AS c ON c.id = u.conta
                      INNER JOIN usuario_status AS s ON s.id = u.ativo
                      INNER JOIN corporativo_vwCcustos AS cct ON cct.Codigo = (SELECT TOP 1 cc4.id_ccusto FROM usuario_ccusto AS cc4 WHERE cc4.id_usuario = u.id)
                      INNER JOIN corporativo_vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0) = SUBSTRING (cct.Codigo,1,1)
                      WHERE u.id = ?";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno = $linha;
        }
        return $retorno;
    }

    function getContas($s_usuario) {
        $retorno = array();
        $query = "SELECT ct.id
                        ,ct.nome
                    FROM contas AS ct";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getPermissoesConta($s_usuario, $conta) {
        $retorno = array();
        $retorno[0] = array();
        $retorno[1] = array();
        $query = "SELECT id_permissao
                    FROM permissoes_contaf
                    WHERE id_conta = ?";
        $params = array($conta);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[0][] = "s-" . $linha->id_permissao;
        }

        $query = "SELECT id_permissao
                    FROM permissoes_contap
                    WHERE id_conta = ?";
        $params = array($conta);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[1][] = $linha->id_permissao;
        }
        return $retorno;
    }

    function setUsuario($s_usuario, $id, $usuario, $nome, $sobrenome, $cpf, $senha, $grupoAprovadores, $conta, $status, $arrCcusto) {
        $retorno = array();
        if ($id == "") {//inserir
            $query = "SELECT id FROM usuario WHERE usuario = ? AND senha = ?";
            $params = array($usuario, sha1($senha));
            $resul = sqlsrv_query($this->link, $query, $params);
            $linha = sqlsrv_fetch_object($resul);
            if ($linha->id > 0) {
                die("Erro: Usuário já cadastrado");
            }
            $query = "INSERT INTO usuario(usuario, nome, sobrenome,cpf, senha, id_grupo, conta, ativo)
                        OUTPUT inserted.id
                        VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array($usuario, $nome, $sobrenome,$cpf, sha1($senha),$grupoAprovadores, $conta, $status);
        } else {//editar
            if ($senha == "") {
                $query = "UPDATE usuario
                            SET 
                            usuario = ?,
                            nome = ?, 
                            sobrenome = ?,
                            cpf=?,
                            id_grupo=?,
                            conta = ?, 
                            ativo = ?
                            OUTPUT inserted.id
                            WHERE id = ?";
                $params = array($usuario, $nome, $sobrenome,$cpf,$grupoAprovadores, $conta, $status, $id);
            } else {
                $query = "UPDATE usuario
                        SET 
                        usuario = ?,
                        nome = ?, 
                        sobrenome = ?,
                        cpf=?,
                        senha=?,
                        id_grupo=?,
                        conta = ?, 
                        ativo = ?
                        OUTPUT inserted.id
                        WHERE id = ?";
                $params = array($usuario, $nome, $sobrenome,$cpf,sha1($senha),$grupoAprovadores, $conta, $status, $id);
            }
        }
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $idUsuario = $linha->id;


        $query_1 = "DELETE FROM usuario_ccusto
                    WHERE id_usuario = ?";
        $params_1 = array($id);
        $resul_1 = sqlsrv_query($this->link, $query_1, $params_1);
        //$linha_1 = sqlsrv_fetch_object($resul_1);

        foreach ($arrCcusto as $ccusto) {
            $query_2 = "INSERT INTO usuario_ccusto(id_usuario, id_ccusto)
                        VALUES(?,?)";
            $params_2 = array($id,$ccusto{'id_ccusto'});
            $resul_2 = sqlsrv_query($this->link, $query_2, $params_2);
            $linha_2 = sqlsrv_fetch_object($resul_2);
        }
//        if(($errors=sqlsrv_errors())!= null){
//            print_r($errors);
//        }
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
        $obj = new usuario_permissao();
        return $obj->getUsuario($s_usuario);
    }

    function setUsuarioAtivacao($s_usuario, $id_usuario, $nome_para, $email_para, $id_grupo, $id_conta, $arrCcusto) {
        //1-UPDATE USUARIO
        $query = "UPDATE usuario
                        SET usuario.ativo =1,
                            usuario.id_grupo=?,
                            usuario.conta=?
                        OUTPUT inserted.id
                        WHERE id = ?";
        $params = array($id_grupo,$id_conta,$id_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //2-DELETE CENTROS DE CUSTOS
        $query_1 = "DELETE FROM usuario_ccusto
                    WHERE id_usuario = ?";
        $params_1 = array($id_usuario);
        $resul_1 = sqlsrv_query($this->link, $query_1, $params_1);
        $linha_1 = sqlsrv_fetch_object($resul_1);

        //3-INSERT CENTROS DE CUSTO
        foreach ($arrCcusto as $ccusto) {
            $query_2 = "INSERT INTO usuario_ccusto(id_usuario, id_ccusto)
                        VALUES(?,?)";
            $params_2 = array($id_usuario,$ccusto{'id_ccusto'});
            $resul_2 = sqlsrv_query($this->link, $query_2, $params_2);
        }

        //4ENVIA EMAIL
        $objEmail = new email();
        $valid_email = $objEmail->e2cUsuarioAtivacao($s_usuario,$nome_para,$email_para,$id_usuario);

        if( $valid_email==1){
            $obj = new usuario_permissao();
            return $obj->getUsuario($s_usuario);
        }else{
            return 0;
        }
     }

    function setPerfil($s_usuario, $id,$nome,$sobrenome, $senha) {
        $retorno = array();
        if ($senha == "") {
            $query = "UPDATE usuario
                        SET nome = ?, sobrenome = ? 
                        OUTPUT inserted.id
                        WHERE id = ?";
            $params = array($nome, $sobrenome,$id);
        } else {
            $query = "UPDATE usuario
                        SET nome = ?, sobrenome = ?, senha = ? 
                        OUTPUT inserted.id
                        WHERE id = ?";
            $params = array($nome, $sobrenome, sha1($senha), $id);
        }
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        $obj = new usuario_permissao();
        return $obj->getPerfil($s_usuario);
    }

    function setNovaConta($s_usuario, $nome) {
        $retorno = array();
        $query = "INSERT INTO contas(nome) 
                        OUTPUT inserted.id, inserted.nome
                        VALUES(?)";
        $params = array($nome);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        return $linha;
    }

    function setPermissoes($s_usuario, $conta, $arrPagina) {
    if ($conta == "1") {
        die("Não foi possível alterar as permissões desta conta!");
    }

    $query = "DELETE FROM permissoes_contaf
                WHERE id_conta = ?";
    $params = array($conta);
    $resul = sqlsrv_query($this->link, $query, $params);
    $linha = sqlsrv_fetch_object($resul);


    $query = "DELETE FROM permissoes_contap
                WHERE id_conta = ?";
    $params = array($conta);
    $resul = sqlsrv_query($this->link, $query, $params);
    $linha = sqlsrv_fetch_object($resul);

//        for ($i = 0; $i < count($arrPermissao); $i++) {
//            $expIterado = explode("-", $arrPermissao[$i]);
//
//            if ($expIterado[0] == "s") {
//                $query = "INSERT INTO permissoes_contaf(id_conta, id_permissao)
//                        VALUES(?, ?)";
//            }
//            $params = array($conta, $expIterado[1]);
//            $resul = sqlsrv_query($this->link, $query, $params);
//            $linha = sqlsrv_fetch_object($resul);
//        }
    for ($i = 0; $i < count($arrPagina); $i++) {

        $query = "INSERT INTO permissoes_contap(id_conta, id_permissao) 
                    VALUES(?, ?)";
        $params = array($conta, $arrPagina[$i]);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
    }

    $obj = new usuario_permissao();
    return $obj->getPermissoesConta($s_usuario, $conta);
}

    function remUsuario($s_usuario, $id) {
        $retorno = array();
        $query = "DELETE FROM usuario
                    WHERE id = ?";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        $obj = new usuario_permissao();
        return $obj->getUsuario($s_usuario);
    }

    function uploadFoto($s_usuario, $arquivo, $id) {
        copy("../" . $arquivo, "../img/user/{$id}.jpg");
        unlink("../" . $arquivo);
        return $id;
    }

}

?>