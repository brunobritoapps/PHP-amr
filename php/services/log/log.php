<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 06-Jan-19
 * Time: 02:33 AM
 */

class log
{
    var $link;

    function __construct() {
        //error_reporting(E_ALL );
        if (file_exists('parametros.php')) {
            require_once('parametros.php');
        } else {
            require_once('..\parametros.php');
        }
        $this->link = conexao("base");
    }

    function logEmailRegistro($s_usuario,$id_usuario){
        $validLog = false;
        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,acao,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data_envio)
                      OUTPUT inserted.status
                      VALUES(0,'E1CRGT','N/A',0,?,1,500,GETDATE())";
        $params = array($id_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha2 = sqlsrv_fetch_object($resul)){
            $validLog = true;
        }
        if($validLog){
            return 1;
        }else{
            return 0;
        }
    }

    function logEmailRegistroAdm($s_usuario,$id_usuario){
        $validLog = false;
        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,acao,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data_envio)
                      OUTPUT inserted.status
                      VALUES(0,'E0ADRG','N/A',0,?,1,501,GETDATE())";
        $params = array($id_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha2 = sqlsrv_fetch_object($resul)){
            $validLog = true;
        }
        if($validLog){
            return 1;
        }else{
            return 0;
        }
    }

    function logEmailAtivacao($s_usuario,$id_usuario){
        $validLog = false;
        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,acao,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data_envio)
                  OUTPUT inserted.status
                  VALUES(0,'E2CATV','N/A',0,?,1,502,GETDATE())";
        $params = array($id_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha2 = sqlsrv_fetch_object($resul)){
            $validLog = true;
        }
        if($validLog){
            return 1;
        }else{
            return 0;
        }
    }

    function logEmailGrupo($s_usuario,$id_para){
        $validLog = false;
        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,acao,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data_envio)
                  OUTPUT inserted.status
                  VALUES(0,'E3CGRP','N/A',0,?,1,503,GETDATE())";
        $params = array($id_para);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha2 = sqlsrv_fetch_object($resul)){
            $validLog = true;
        }
        if($validLog){
            return 1;
        }else{
            return 0;
        }
    }

    function logEmailDocumentoCompartilhado($s_usuario,$id_para){
        $validLog = false;
        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,acao,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data_envio)
                  OUTPUT inserted.status
                  VALUES(0,'E10DCP','N/A',0,?,1,300,GETDATE())";
        $params = array($id_para);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha2 = sqlsrv_fetch_object($resul)){
            $validLog = true;
        }
        if($validLog){
            return 1;
        }else{
            return 0;
        }
    }

    function logEmailAprovadoFinanceiro($s_usuario,$id_de,$id_para){
        $validLog = false;
        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,acao,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data_envio)
                  OUTPUT inserted.status
                  VALUES(0,'E11FAP','N/A',?,?,1,400,GETDATE())";
        $params = array($id_de,$id_para);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha2 = sqlsrv_fetch_object($resul)){
            $validLog = true;
        }
        if($validLog){
            return 1;
        }else{
            return 0;
        }
    }

    function logEmailMaisDetalhesFinanceiro($s_usuario,$id_de,$id_para,$id_msg){
        $validLog = false;
        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,acao,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data_envio)
                  OUTPUT inserted.status
                  VALUES(?,'E12FDT','N/A',?,?,1,410,GETDATE())";
        $params = array($id_msg,$id_de,$id_para);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha2 = sqlsrv_fetch_object($resul)){
            $validLog = true;
        }
        if($validLog){
            return 1;
        }else{
            return 0;
        }
    }


}