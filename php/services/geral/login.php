<?php

class login {

    var $link;

    function login() {
        //error_reporting(E_ALL);
        if (file_exists('parametros.php')) {
            require_once('parametros.php');
        } else {
            require_once('..\parametros.php');
        }
        $this->link = conexao("base");

        require_once 'services/email/email.php';

    }

    function logar($s_usuario, $usuario, $senha) {
//      die("Sistema em manuten??o");
        session_start();
        session_destroy();
        session_start();
        $query = "SELECT u.id
                ,u.usuario
                ,u.nome
                ,u.sobrenome
                ,u.cpf
                ,c.nome AS tipo_conta
                ,u.conta
                ,(SELECT TOP 1 Nome_Empresa
                FROM dbo.usuario_ccusto AS ucc
                LEFT JOIN dbo.vwEmpresas ON Cod_Empresa = SUBSTRING (ucc.id_ccusto,1,1)
                WHERE ucc.id_usuario=u.id)AS empresa
                
                ,(SELECT TOP 1 ucc.id_ccusto
                FROM dbo.usuario_ccusto AS ucc
                WHERE ucc.id_usuario=u.id)AS id_ccusto
                
                ,(SELECT TOP 1 cc.Descricao 
                FROM dbo.usuario_ccusto AS ucc
                LEFT JOIN corporativo_vwCcustos AS cc ON  cc.Codigo = ucc.id_ccusto
                WHERE ucc.id_usuario=u.id)AS ccusto
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=1)AS dir_prt1
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=2)AS dir_prt2
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=3)AS dir_prt3
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=4)AS dir_emp1
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=5)AS dir_emp2
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=6)AS dir_emp3
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=7)AS dir_emp4
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=8)AS dir_emp5
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=9)AS dir_emp6
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=10)AS dir_emp7
                
                ,(SELECT cd.diretorio 
                FROM dbo.configuracao_geral_diretorio AS cd
                WHERE cd.id=11)AS dir_emp8
                
                 ,(SELECT cr.dataLimite 
                FROM dbo.configuracao_reembolso AS cr
                WHERE cr.id=1)AS dt_lim

            FROM usuario AS u
                LEFT JOIN dbo.contas AS c ON c.id = u.conta
            WHERE u.usuario=? AND u.senha=? AND u.ativo = 1
            ";

        $params = array($usuario, sha1($senha));
        $resul = sqlsrv_query($this->link, $query, $params);
//        print_r($params);
//        die($query);
        if ($linha = sqlsrv_fetch_object($resul)) {

            $_SESSION['autent'] = '1E07C2A0-597F-4603-8177-2FED2F624E54';
            $_SESSION['PREV_USERAGENT'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['PREV_REMOTEADDR'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['ULT_REC'] = time();
            $_SESSION['s_usuario'] = $linha->id;
            $_SESSION['s_login'] = $linha->usuario;
            $_SESSION['s_nome'] = $linha->nome;
            $_SESSION['s_sobrenome'] = $linha->sobrenome;
            $_SESSION['s_cpf'] = $linha->cpf;
            $_SESSION['s_conta'] = $linha->conta;
            $_SESSION['s_tipoConta'] = $linha->tipo_conta;
            $_SESSION['s_empresa'] = $linha->empresa;
            $_SESSION['s_id_ccusto'] = $linha->id_ccusto;
            $_SESSION['s_ccusto'] = $linha->ccusto;

            $_SESSION['s_dir_prt1'] = $linha->dir_prt1;
            $_SESSION['s_dir_prt1'] = $linha->dir_prt1;
            $_SESSION['s_dir_prt1'] = $linha->dir_prt1;

            $_SESSION['s_dir_emp1'] = $linha->dir_emp1;
            $_SESSION['s_dir_emp2'] = $linha->dir_emp2;
            $_SESSION['s_dir_emp3'] = $linha->dir_emp3;
            $_SESSION['s_dir_emp4'] = $linha->dir_emp4;
            $_SESSION['s_dir_emp5'] = $linha->dir_emp5;
            $_SESSION['s_dir_emp6'] = $linha->dir_emp6;
            $_SESSION['s_dir_emp7'] = $linha->dir_emp7;
            $_SESSION['s_dir_emp8'] = $linha->dir_emp8;

            $_SESSION['s_dt_lim'] = $linha->dt_lim;


//            //Fechamento 2015
//            $arrContasLiberadas = [1,7,12];
//            if(date("Y-m-d") >= '2015-12-10' && date("Y-m-d") < '2016-01-04' && !in_array($_SESSION["s_conta"], $arrContasLiberadas)){
//                session_destroy();
//                die("Sistema fechado. Reabertura dia 04/01/2016");
//            }            

            return 1;
        } else {
            return 0;
        }


    }

    function setUsuarioRegistro($s_usuario, $usuario, $nome, $sobrenome,$cpf, $senha) {
//        print_r(get_defined_vars());
//        die();
        $retorno = array();
        $query = "SELECT id FROM usuario AS u WHERE cpf= ?";
        $params = array($cpf);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        if ($linha->id > 0) {
            return 0;
            //Erro: Usuário já registrado");
        }else{

            $query = "INSERT INTO usuario(usuario, nome, sobrenome,cpf,senha,ativo,data_registro)
                    OUTPUT inserted.id
                    VALUES(?, ?, ?, ?, ?, ?, GETDATE())";
            $params = array($usuario, $nome, $sobrenome,$cpf,sha1($senha),2);
            $resul = sqlsrv_query($this->link, $query, $params);
            $vallid_sql=false;
            if($linha = sqlsrv_fetch_object($resul)){
                $vallid_sql = true;
            }

            //ENVIO DE EMAIL
            $obj1 = new email();
            $valid_email=$obj1->e1cUsuarioRegistro($s_usuario,$nome, $sobrenome,$usuario,$cpf);

            if($vallid_sql==1 && $valid_email==1){
                return 1;
            }else{
                return 0;
            }
        }
    }

    function validaCpf($s_usuario,$cpf) {
        $query = "SELECT f.A2_CGC AS cpf, f.A2_EMAIL AS email
                    FROM corporativo_vwFuncionarios AS f WHERE f.A2_CGC=?";
        $params = array($cpf);
        $resul = sqlsrv_query($this->link, $query, $params);
        if ($linha = sqlsrv_fetch_object($resul)) {
            return 1;
        } else {
            return 0;
        }
    }
}

?>