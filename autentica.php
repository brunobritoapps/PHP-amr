<?php

session_start();

function autentica() {
    if (@$_SESSION['autent'] !== '1E07C2A0-597F-4603-8177-2FED2F624E54' ||
            ($_SERVER['REMOTE_ADDR'] !== @$_SESSION['PREV_REMOTEADDR'] ||
//            $_SERVER['HTTP_USER_AGENT'] !== @$_SESSION['PREV_USERAGENT'] ||
            @$_SESSION['ULT_REC'] < time() - (60 * 180))) {
        @$_SESSION['autent'] = "";
        if (!isset($_SESSION)) {
            session_start();
        }
        session_destroy();

        return false;
    } else {
        $_SESSION['PREV_USERAGENT'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['PREV_REMOTEADDR'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['ULT_REC'] = time();
        return true;
    }
}

//--------------------------VARIAVEIS DE CONFIG -------------------------------//

$GUID_SECRET = "31dc6589-dc01-4c6f-b9c9-093740caa0f5";
//empresas que nao entram no reteio
$arrNaoRateia = '8,9,16,18,19';
$hsrTech = 19;

?>