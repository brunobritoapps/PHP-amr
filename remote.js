//Construtor
function Remote(gateway, path, classe) {
    this.gateway = gateway;
    this.path = path;
    this.classe = classe;
}

//adiciona loading
function addLoad(obj) {
//    $(obj).append('<div id="loading_auto" class="center" style="position: absolute; z-index:1; top: 0px; left: 0px; right: 0px; bottom: 0px; background-color:#F4F4F4; opacity:0.65; -moz-opacity: 0.65; filter: alpha(opacity=65); "><img style="padding: 10px" src="theme/img/loaders/default.gif"/></div>');
//    $('*').css("cursor", "progress");
    panel_refresh(obj);
}

//remove loading
function removeLoad(obj) {
//    $("#loading_auto", obj).remove();
//    $('*').css("cursor", "");
    panel_refresh(obj);
}

//Metodos da Classe
function executa(action, par, callB, objAcao, callErro) {
    addLoad(objAcao);
    $.ajax({
        type: "POST",
        url: this.gateway,
        cache: false,
        async: true,
        data: {
            path: this.path,
            classe: this.classe,
            action: action,
            parametros: par
        },
        success: function(dados) {
            removeLoad(objAcao);
            $(window).resize();
            var retorno = "";
            try {
                retorno = $.parseJSON(dados);
            } catch (e) {
                erro(callErro, dados, e);
            }

            switch (retorno.status) {
                case 1:
                    callB(retorno.resultado);
                    break;
                case 2:
                    erro(callErro, dados);
                    break;
                case 3:
                    erroPersonalizado(retorno.resultado);
                    break;
                case 99:
                    window.location.assign("index.php?ses=1");
                    break;
            }

        },
        error: function(dados) {
            removeLoad(objAcao);
            erro(callErro, dados);
        }
    });
}

//Prototipo dos metodos
Remote.prototype.executa = executa;

//Erro padrao
function erro(func, dados, erro) {
    if (func != null) {
        func(dados, erro);
    } else {
        erroPersonalizado("<br/>Erro ao executar a??o.<br/><br/>");
        console.log("ERRO:");
        console.log(dados);
        console.log("----");
        console.log(erro);
        console.log("----");
    }
    return;
}