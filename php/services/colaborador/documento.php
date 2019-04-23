<?php

class documento {

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

    function getDocumentoUsuario($s_usuario){
        $retorno = array();
        $query = "SELECT doc.data_inclusao,doc.tipo,  doc.documento AS arquivo
                        FROM colaborador_documento AS doc
                        WHERE doc.id_usuario =?
                        ORDER BY doc.data_inclusao DESC
                        ";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDocumentoDownload($s_usuario,$FILENAME, $FILEDIR){
        header("Content-Disposition: attachment; filename={$FILENAME}");
        $FILEPATH = $FILEDIR.$FILENAME;
        readfile($FILEPATH);
    }
}

?>