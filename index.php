<?php
//header("Content-Type: text/html; charset=ISO-8859-1",true);
setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
date_default_timezone_set("America/Sao_Paulo");
error_reporting(0);

//autenticacao
require_once("autentica.php");

//definindo paginas constantes
define("PG_ERRO", "pages/sistema/erro.html");
define("PG_HOME", "pages/home.html");
define("PG_TOPO", "pages/sistema/topo.html");
define("PG_LOGIN", "pages/sistema/login.html");
define("PG_HEAD", "pages/sistema/head.html");
define("PG_FOOT", "pages/sistema/foot.html");
define("PG_MENU", "pages/sistema/menu.html");
define("PG_RODAPE", "pages/sistema/rodape.html");

//verifica se esta saindo
if (isset($_GET["sair"])) {
    @session_start();
    $_SESSION['autent'] = "";
    session_destroy();
}

//print_r(get_defined_vars());
//die("...");

//verifica se esta logado
if (!autentica() /*|| ($_SESSION["s_conta"] != 1 && $_SESSION["s_conta"] != 12 && $_SESSION["s_conta"] != 7)*/) {
    //inclui o cabecalho
    require_once(PG_HEAD);

    echo '<script type="text/javascript">';
    echo "var url = {};";
    foreach ($_GET as $chave => $valor) {
        echo "url.{$chave} = '{$valor}';";
    }    
    echo '</script>';

    //inclui o foot
    require_once(PG_LOGIN);

    //inclui o foot
    require_once(PG_FOOT);

    //fecha o body e html
    echo "\n</body>\n</html>";
    exit();
}

//menu

require_once("menu.php");
@$arrStatusMenu = statusMenu($_GET["page"]);
//recebe a pagina solicitada
@$page = $_GET["page"];

//inclui o cabecalho
if (!isset($_REQUEST["frame"])) {
    require_once(PG_HEAD);
}

//verifica se a pagina solicitada existe e define a pagina de erro   
$localPagina = isset($_GET["page"]) ? PG_ERRO : PG_HOME;
$objMenu = verificaCondicoes($page);

//verifica a permissao da pagina
require_once ("php/services/geral/paginas.php");
$obj = new paginas();
@$perm = $obj->validaPagina($_SESSION["s_usuario"], $_SESSION["s_conta"], $objMenu->permissao);

//pega as permissoes do usuario
$permUsu = $obj->getArrPerm($_SESSION["s_usuario"], $_SESSION["s_conta"]);
$_SESSION["permissao"] = $permUsu;

//pega todas as variaveis GET e algumas session e coloca na variavel javascript
echo '<script type="text/javascript">';
if (!isset($_REQUEST["frame"])) {
    echo "var url = {};";
    echo "var s = {};";
    echo "var menu = {};";
    echo "var permissao = {};";
}
foreach ($_GET as $chave => $valor) {
    if ($chave != "page") {
        echo "url.{$chave} = '{$valor}';";
    }
}
if (!isset($_REQUEST["frame"])) {
    //
    echo 's.usuario = "' . $_SESSION["s_usuario"] . '";';
    echo 's.conta = "' . $_SESSION['s_conta'] . '";';
    echo 's.tipo_conta = "' . $_SESSION['s_tipoConta'] . '";';
    echo 's.login = "' . $_SESSION["s_login"] . '";';
    echo 's.nome = "' . $_SESSION["s_nome"] . '";';
    echo 's.sobrenome = "' . $_SESSION["s_sobrenome"] . '";';
    echo 's.cpf = "' . $_SESSION["s_cpf"] . '";';
    echo 's.empresa = "' . $_SESSION['s_empresa'] . '";';
    echo 's.id_ccusto = "' . $_SESSION['s_id_ccusto'] . '";';
    echo 's.ccusto = "' . $_SESSION['s_ccusto'] . '";';
    //
    echo 's.dir_prt1 = "' . $_SESSION['s_dir_prt1'] . '";';
    echo 's.dir_prt2 = "' . $_SESSION['s_dir_prt2'] . '";';
    echo 's.dir_prt3 = "' . $_SESSION['s_dir_prt3'] . '";';
    //
    echo 's.dir_emp1 = "' . $_SESSION['s_dir_emp1'] . '";';
    echo 's.dir_emp2 = "' . $_SESSION['s_dir_emp2'] . '";';
    echo 's.dir_emp3 = "' . $_SESSION['s_dir_emp3'] . '";';
    echo 's.dir_emp4 = "' . $_SESSION['s_dir_emp4'] . '";';
    echo 's.dir_emp5 = "' . $_SESSION['s_dir_emp5'] . '";';
    echo 's.dir_emp6 = "' . $_SESSION['s_dir_emp6'] . '";';
    echo 's.dir_emp7 = "' . $_SESSION['s_dir_emp7'] . '";';
    echo 's.dir_emp8 = "' . $_SESSION['s_dir_emp8'] . '";';

    echo 's.dt_lim= "' . $_SESSION['s_dt_lim'] . '";';
    //
    echo 'menu.estruturaMenu = ' . json_encode(imprimeMenu($arrStatusMenu)) . ';';
    echo 'menu.statusMenu = ' . json_encode($arrStatusMenu) . ';';
    echo 'permissao = ' . json_encode($permUsu) . ';';
}
echo '</script>';

if ($objMenu != "" && file_exists($objMenu->local) && $perm == true) {
    $localPagina = $objMenu->local;
}

if (!isset($_REQUEST["frame"])) {
    $localPagina != PG_ERRO ? require_once(PG_MENU) : "";
    $localPagina != PG_ERRO ? require_once(PG_TOPO) : "";
}


//inclui a pagina solicitada
//require_once($localPagina);
$conteudoPagina = file_get_contents($localPagina);
echo str_replace(array("\r"), "\r\n", $conteudoPagina);

if (!isset($_REQUEST["frame"])) {
    $localPagina != PG_ERRO ? require_once(PG_RODAPE) : "";
    //inclui o foot
    require_once(PG_FOOT);

    //fecha o body e html
    echo "\n</body>\n</html>";
}
?>