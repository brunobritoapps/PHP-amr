<?php

class documento {

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

    function getDocumento($s_usuario){
        $retorno = array();
        $query = "SELECT doc.id, doc.tipo, doc.data_inclusao, doc.id_usuario AS cod, u.nome AS usuario, u.sobrenome,  u.cpf, doc.documento AS arquivo
                    FROM colaborador_documento AS doc
                    LEFT JOIN usuario AS u ON u.id = doc.id_usuario
                    WHERE doc.id_remetente =?
                    ";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function uploadDocumento($s_usuario,$id, $arquivo , $tipo) {
        // CONSULTA CPF DO USUARIO DE ENVIO
        $query = " SELECT u.id,u.cpf AS cpf
                      FROM usuario AS u
                      WHERE u.id=?";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $cpf = $linha->cpf;
        //
        $extensao = strtolower(substr($arquivo,-4));
        $novo_cripto = md5(time().$cpf);
        $novo_nome = 'DOC_'.$novo_cripto.'_'.$tipo.$extensao;
        copy("../" . $arquivo, "../files/doc/{$novo_nome}");
        unlink("../" . $arquivo);
        //
        return $novo_nome;
    }

    function setDocumento($s_usuario,$id_usuario, $arquivo , $tipo){
        $query = "INSERT INTO colaborador_documento (data_inclusao, tipo, id_usuario, documento, id_remetente)
                        OUTPUT inserted.id
                        VALUES(CONVERT(VARCHAR(10), GETDATE(), 105), ?, ?, ?,?)";
        $params = array($tipo, $id_usuario, $arquivo,$s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        $objEmail = new email();
        $valid_email = $objEmail->e10cDocumentoCompartilhado($s_usuario,$id_usuario,$arquivo);

        if ($valid_email==1 && $linha->id>0){
            $obj = new documento();
            return $obj->getDocumento($s_usuario);
        }else{
            return 0;
        }


    }

    function remDocumento($s_usuario, $id_envio_documento){
        $retorno = array();
        $query = "DELETE FROM colaborador_documento
                    WHERE id = ?";
        $params = array($id_envio_documento);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        $obj = new documento();
        return $obj->getDocumento($s_usuario);
    }

}

?>