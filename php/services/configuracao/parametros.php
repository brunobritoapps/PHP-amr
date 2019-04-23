<?php

class parametros {

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
    }

    function getDiretorioReembolso($s_usuario) {
        $retorno = array();
        $query = " SELECT d.diretorio AS dir
                      FROM configuracao_geral_diretorio AS d
                      WHERE d.id= 2";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDiretorioFinanceiro($s_usuario, $id_titulo) {
        $retorno = array();
        $query = " SELECT d.diretorio
                      FROM processo_vwAprovFinTitulosParaAprovar  AS t
                      LEFT JOIN configuracao_geral_diretorio AS d ON d.cod = t.empresa
                      WHERE t.codigo=?";
        $params = array($id_titulo);
        $resul = sqlsrv_query($this->link, $query, $params);
        while($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDiretorioCompartilhamentoDocumento($s_usuario) {
        $retorno = array();
        $query = " SELECT d.diretorio AS dir
                      FROM configuracao_geral_diretorio AS d
                      WHERE d.id= 3";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDiretorioTodos_reembolso($s_usuario) {
        $retorno = array();
        $query = " SELECT d.id, d.diretorio AS dir
                  FROM configuracao_geral_diretorio AS d 
                  ORDER BY d.id";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDiretorioTodos_erp($s_usuario) {
        $retorno = array();
        $query = " SELECT d.id, d.diretorio AS dir
                  FROM configuracao_geral_diretorio AS d 
                  WHERE id>3
                  ORDER BY d.id";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDataLimite_reembolso($s_usuario) {
        $retorno = array();
        $query = " SELECT conf.id 
                         ,conf.dataLimite
                  FROM configuracao_reembolso AS conf
                  ORDER BY id";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setDiretorio_reembolso($s_usuario, $arrDir) {
        foreach ($arrDir as $dir) {
            $query = "UPDATE configuracao_geral_diretorio
                        SET 
                        diretorio=?,
                        data= GETDATE()
                        OUTPUT inserted.id
                        WHERE id = ?";
            $params = array($dir{'diretorio'},$dir{'id'});
            if(($errors=sqlsrv_errors())!= null){
                print_r($errors);
            }
            sqlsrv_query($this->link, $query, $params);
        }
        $obj = new parametros();
        return $obj->getDiretorioTodos_reembolso($s_usuario);
    }

    function getCotaKm_reembolso($s_usuario) {
        $retorno = array();
        $query = " SELECT conf.cota AS valor
                  FROM configuracao_reembolso_cotas AS conf
                  WHERE id_contexto = '20002'";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setCotaKm_reembolso($s_usuario,$valor) {
        $query = "UPDATE configuracao_reembolso_cotas
                        SET 
                        cota=?,
                        data = GETDATE()
                        WHERE id_contexto ='20002'";
        $params = array($valor);
        sqlsrv_query($this->link, $query, $params);

        if(($errors=sqlsrv_errors())!= null){
            print_r($errors);
        }

        $obj = new parametros();
        return $obj->getCotaKm_reembolso($s_usuario);
    }

    function setDataLimite_reembolso($s_usuario,$id, $data) {
            $query = "UPDATE configuracao_reembolso
                        SET 
                        dataLimite=?,
                        data= GETDATE()
                        WHERE id = ?";
            $params = array($data,$id);
            if(($errors=sqlsrv_errors())!= null){
                print_r($errors);
            }
            $resul = sqlsrv_query($this->link, $query, $params);

        $obj = new parametros();
        return $obj->getDataLimite_reembolso($s_usuario);
    }

    function getEmails_reembolso($s_usuario) {
        $retorno = array();
        $query = " SELECT conf.id_usuario
                         ,conf.email AS email_usuario
                         ,conf.nome AS nome_usuario
                         ,conf.tipo_disparo AS id_tipo
                         ,tipo.tipo
                        FROM configuracao_email_disparo AS conf
                  LEFT JOIN configuracao_reeembolso_tipo_email AS tipo ON tipo.id=conf.tipo_disparo";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setEmails_reembolso($s_usuario,$arrEmail){
        //1DELETE EMAILS
        $query = "DELETE FROM configuracao_email_disparo";
        $params = array();
        sqlsrv_query($this->link, $query, $params);

        //2-INSERT EMAILS
        $id_email=1;
        foreach ($arrEmail as $usuario) {
            $query = "INSERT INTO configuracao_email_disparo(id,id_usuario,nome,email,tipo_disparo,id_permissao,data)
                      VALUES(?,?,?,?,?,1,GETDATE())";
            $params = array($id_email,$usuario{'id_usuario'},$usuario{'nome_usuario'},$usuario{'email_usuario'},$usuario{'id_tipo'});
            $resul = sqlsrv_query($this->link, $query, $params);
            sqlsrv_fetch_object($resul);
            $id_email++;
        }

        $obj = new parametros();
        return $obj->getEmails_reembolso($s_usuario);
    }

    function getTipoEmails_reembolso($s_usuario) {
        $retorno = array();
        $query = " SELECT tipo.id , tipo.tipo
                        FROM configuracao_reeembolso_tipo_email AS tipo ";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }


}

?>