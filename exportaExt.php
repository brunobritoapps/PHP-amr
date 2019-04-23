<?php

if (isset($_REQUEST["dados"]) && $_REQUEST["dados"] != "") {
    $dados = utf8_decode(urldecode($_REQUEST["dados"]));
    $nome = isset($_POST["nome"]) ? $_REQUEST["nome"] : "Dados";
    $ext = isset($_POST["ext"]) ? $_REQUEST["ext"] : "txt";
    header('Content-Type: application/' . $ext);
    header('Content-Disposition: attachment;filename="' . $nome . "." . $ext . '"');
    header('Cache-Control: max-age=0');
    echo $dados;
    exit;
} else {
    if (isset($_REQUEST["caminho"]) && $_REQUEST["caminho"] != "" && isset($_REQUEST["nome"]) && $_REQUEST["nome"] != "" && isset($_REQUEST["ext"]) && $_REQUEST["ext"] != "") {
        $caminho = $_REQUEST["caminho"];
        $nome = utf8_decode(urldecode($_REQUEST["nome"]));
        $ext = $_REQUEST["ext"];
        $chave = $_REQUEST["chave"];
        $apagar = $_REQUEST["apagar"];
//        $chaveArq = sha1_file($caminho . $nome . "." . $ext);
//        print_r(get_defined_vars());
//        die("bbbbb");
        if (!file_exists($caminho . $nome . "." . $ext) || sha1_file($caminho . $nome . "." . $ext) != $chave) {
            header("status: 204");
            header("HTTP/1.0 204 No Response");
            die("Erro.");
        }
        header('Content-Type: application/' . $ext);
        if ($_REQUEST["nomeNovo"] != "") {
            header('Content-Disposition: attachment;filename="' . urldecode($_REQUEST["nomeNovo"]) . '"');
        } else {
            header('Content-Disposition: attachment;filename="' . $nome . "." . $ext . '"');
        }

        header('Content-Length: ' . filesize($caminho . $nome . "." . $ext));
        header('Cache-Control: max-age=0');
        readfile($caminho . $nome . "." . $ext);
        if($apagar == "1"){
            unlink($caminho . $nome . "." . $ext);
        }
    } else {
        header("status: 204");
        header("HTTP/1.0 204 No Response");
        die("Erro.");
    }
}
?>