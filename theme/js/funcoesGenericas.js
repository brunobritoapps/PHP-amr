$.ajaxSetup({
    contentType: 'application/x-www-form-urlencoded; charset=ISO-8859-1',
    beforeSend: function (jqXHR) {
        jqXHR.overrideMimeType('application/x-www-form-urlencoded; charset=ISO-8859-1');
    }
});

//scroll
$.fn.extend({
    scrollToMe: function () {
        var x = $(this).offset().top - 100;
        $('body').animate({scrollTop: x}, 400);
    }});

//exportacao de arquivo
jQuery.download = function (url, data, method) {
    //url and data options required
    if (url && data) {
        var inputs = '';
        for (var name in data) {
            inputs += "<input type='hidden' name='" + name + "' value='" + encodeURI(data[name].split("'").join(" ")) + "' />";
        }
        //send request
        jQuery('<form action="' + url + '" method="' + (method || 'post') + '">' + inputs + '<input type="submit" value="submit"/></form>')
                .appendTo('body').submit().remove();        
    }
    ;
};

function rgbToHex(colorval) {
    var parts = colorval.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    delete(parts[0]);
    for (var i = 1; i <= 3; ++i) {
        parts[i] = parseInt(parts[i]).toString(16);
        if (parts[i].length == 1)
            parts[i] = '0' + parts[i];
    }
    return '#' + parts.join('');
}

function rgb2hex(rgb) {
    if (rgb.search("rgb") == -1) {
        return rgb;
    } else {
        rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
        function hex(x) {
            return ("0" + parseInt(x).toString(16)).slice(-2);
        }
        return  hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
    }
}

function textPlain(texto) {
//    return texto;
    return $($("<p>" + texto + "</p>")).text();
}

//limpeza e convecao de table para xml    
function tableToXML(table, title) {
    title = (title == null) ? ($(table).attr("title") != undefined ? $(table).attr("title") : "Tabela") : (title);
    var tableLimpa = '<table title="' + title + '"><thead>';
    $("thead tr", table).each(function () {
        tableLimpa += "<tr>";
        $("th", $(this)).each(function () {
            var $this = $(this);
            var bg = rgb2hex($this.css('backgroundColor'));
            var cor = rgb2hex($this.css('color'));
            var largura = $this.css('width').slice(0, -2) / 10;
            var bold = $this.css("font-weight");

            bg = bg == "000000" || bg == 'transparent' || bg == "" ? "" : 'backgroud="' + bg + '"';
            bold = bold == "bold" ? 'bold="true"' : "";
            var cols = $this.attr("colspan") != null ? $(this).attr("colspan") : "";
            if (isNaN($this.text().replace(/\./g, '').replace(/\,/g, '')) === false) {
                tableLimpa += '<th colspan="' + cols + '" ' + bg + ' cor="' + cor + '" width="' + largura + '" ' + bold + '><![CDATA[' + $this.text().replace(/\./g, '').replace(",", ".") + ']]></th>';
            } else {
                tableLimpa += '<th colspan="' + cols + '" ' + bg + ' cor="' + cor + '" width="' + largura + '" ' + bold + '><![CDATA[' + $this.text() + ']]></th>';
            }
        });
        tableLimpa += "</tr>";
    });
    tableLimpa += "</thead><tbody>";
    $("tbody tr", table).each(function () {
        tableLimpa += "<tr>";
        $("td", $(this)).each(function () {
            var $this = $(this);
            var bg = rgb2hex($this.css('backgroundColor'));
            var cor = rgb2hex($this.css('color'));
            var largura = $this.css('width').slice(0, -2) / 10;
            var bold = $this.css("font-weight");

            bg = bg == "000000" || bg == 'transparent' || bg == "" ? "" : 'backgroud="' + bg + '"';
            bold = bold == "bold" ? 'bold="true"' : "";
            var cols = $this.attr("colspan") != null ? $this.attr("colspan") : "";
            if (isNaN($this.text().replace(/\./g, '').replace(/\,/g, '')) === false) {
                tableLimpa += '<td colspan="' + cols + '" ' + bg + ' cor="' + cor + '" width="' + largura + '" ' + bold + '><![CDATA[' + $this.text().replace(/\./g, '').replace(",", ".") + ']]></td>';
            } else {
                tableLimpa += '<td colspan="' + cols + '" ' + bg + ' cor="' + cor + '" width="' + largura + '" ' + bold + '><![CDATA[' + $this.text() + ']]></td>';
            }
        });
        tableLimpa += "</tr>";
    });
    tableLimpa += "</tbody></table>";
    return tableLimpa;
}

//gera objeto datatable
function colunasDefaultDataTable(tabela, dados, callbackClick) {
    var objRetorno = "";
    var arrCol = new Array();
    var cSort = $("thead tr th[aria-sort]", tabela).index();
    var cOrd = $("thead tr th[aria-sort]", tabela).attr("aria-sort");
    cSort = cSort == -1 ? 0 : cSort;
    cOrd = cOrd == undefined ? "asc" : cOrd;

    if ($("thead tr:last th", tabela).size() > 0) {
        $("thead tr:last th", tabela).each(function () {
            arrCol[arrCol.length] = {"mData": $(this).attr("campo")};
        });
    } else {
        if (dados.length > 0) {
            for (var propertyName in dados[0]) {
                arrCol[arrCol.length] = {"mData": propertyName, "sTitle": propertyName.charAt(0).toUpperCase() + propertyName.slice(1).split("_").join(" ")};
            }
        }
    }
    if (arrCol.length == 0) {
        arrCol[arrCol.length] = {"mData": null, "sTitle": "Informacao"};
    }

    tabela.on('click', 'tbody tr', function () {
        $(this).closest('table').find('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if (callbackClick) {
            callbackClick($(this));
        }
    });

    objRetorno = {
        "aaData": dados,
        "aoColumns": arrCol,
        "aaSorting": [[cSort, cOrd]],
        "bDestroy": true
    };
    return objRetorno;
}

//gera objeto datatable
function colunasDefaultDataTableP(tabela, dados) {
    var objRetorno = "";
    var arrCol = new Array();
    var cSort = $("thead tr th[aria-sort]", tabela).index();
    var cOrd = $("thead tr th[aria-sort]", tabela).attr("aria-sort");
    cSort = cSort == -1 ? 0 : cSort;
    cOrd = cOrd == undefined ? "asc" : cOrd;

//    var arrOrdenacao = new Array();
//    $("thead tr th[aria-sort]", tabela).each(function(){
//        var arrTemp = new Array();
//        arrTemp[0] = $(this).index();
//        arrTemp[1] = $(this).attr("aria-sort");
//    });

    if ($("thead tr:last th", tabela).size() > 0) {
        $("thead tr:last th", tabela).each(function () {
            arrCol[arrCol.length] = {"mData": $(this).attr("campo")};
        });
    } else {
        if (dados.length > 0) {
            for (var propertyName in dados[0]) {
                arrCol[arrCol.length] = {"mData": propertyName, "sTitle": propertyName};
            }
        }
    }
    if (arrCol.length == 0) {
        arrCol[arrCol.length] = {"mData": null, "sTitle": "Informacao"};
    }
    objRetorno = {
        "aaData": dados,
        "aoColumns": arrCol,
        "aaSorting": [[cSort, cOrd]],
        "bDestroy": true
    };
    return objRetorno;
}

function tabelaPadrao(dados, cabecalho, conteudo) {
    var retorno = '';
    if (conteudo != 1) {
        retorno = '<table id="t_dados" class="table table-striped table-bordered table-condensed"><thead><tr>';
    } else {
        retorno = '<thead><tr>';
    }

    var obj = new Array();
    if (Object.prototype.toString.call(dados) === '[object Array]') {
        obj = dados;
    } else {
        obj.push(dados);
    }
    if (cabecalho != null) {
        var tempCab = new Array();
        for (var i = 0; i < cabecalho.length; i++) {
            retorno += '<th data-coluna="' + cabecalho[i].nome + '">' + cabecalho[i].label + '</th>';
        }
    } else {
        cabecalho = new Array();
        for (var propertyName in obj[0]) {
            if (propertyName.toUpperCase() === "CLASS") {
                continue;
            }
            var nomeCampo = propertyName.charAt(0).toUpperCase() + propertyName.slice(1).split("_").join(" ");
            retorno += '<th>' + nomeCampo + '</th>';
            cabecalho.push({"nome": propertyName, "label": nomeCampo});
        }
    }
    retorno += '</tr></thead>';
    retorno += '<tbody>';
    for (var i = 0; i < obj.length; i++) {
        var linha = "";
        for (var j = 0; j < cabecalho.length; j++) {
            linha += '<td>' + obj[i][cabecalho[j].nome] + '</td>';
        }
        retorno += '<tr class="' + (obj[i].hasOwnProperty("class") ? obj[i].class : "") + '">';
        retorno += linha;
        retorno += '</tr>';
    }
    retorno += '</tbody>';
    if (conteudo != 1) {
        retorno += '</table>';
    }
    return retorno;
}

//exporta xlsx
function exportaXLSX(tabela, nome) {    
    var data = {
        nome: nome || "Dados.xlsx",
        dados: tableToXML(tabela)
    };    
    $.download('exportaTabelaExcel.php', data, 'POST');
}

//exporta tabela xlsx
function exportaTabelaXLSX(tabela, nome) {
    var data = {
        nome: nome,
        dados: tabela
    };
    $.download('exportaTabelaExcel.php', data, 'POST');
}

function exportaExt(dados, nome, extensao, caminho, nomeNovo, chave, apagar) {
    var data = {
        nome: nome,
        dados: dados,
        ext: extensao,
        caminho: caminho,
        nomeNovo: nomeNovo,
        chave: chave,
        apagar: apagar == null ? "" : apagar
    };
    $.download('exportaExt.php', data, 'POST');
}

//gera infromacoes do obj default
function infoDefault(dados) {
    var retorno = "";

    var obj = new Array();
    if (Object.prototype.toString.call(dados) === '[object Array]') {
        obj = dados;
    } else {
        obj.push(dados);
    }
    for (var propertyName in obj[0]) {
        var nomeCampo = propertyName.charAt(0).toUpperCase() + propertyName.slice(1).split("_").join(" ");
        retorno += '<div class="row"><label class="control-label">' + nomeCampo + ':</label> <span class="form-control-static">' + (obj[0][propertyName] != null ? obj[0][propertyName] : '') + '</span></div>';
    }

    return retorno;
}

function erroPersonalizado(dados) {
    noty({
        text: "<br/>" + dados + "<br/><br/>",
        type: 'error',
        layout: 'bottomRight',
        timeout: 6000
    });
}

function carregaFrame(objAcao, pagina) {
    addLoad(objAcao);
    objAcao.load(pagina, function () {
        removeLoad(objAcao);
    });
}

$.fn.scrollTo = function (target, options, callback) {
    if (typeof options == 'function' && arguments.length == 2) {
        callback = options;
        options = target;
    }
    var settings = $.extend({
        scrollTarget: target,
        offsetTop: 50,
        duration: 500,
        easing: 'swing'
    }, options);
    return this.each(function () {
        var scrollPane = $(this);
        var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : $(settings.scrollTarget);
        var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top + scrollPane.scrollTop() - parseInt(settings.offsetTop);
        scrollPane.animate({scrollTop: scrollY}, parseInt(settings.duration), settings.easing, function () {
            if (typeof callback == 'function') {
                callback.call(this);
            }
        });
    });
}