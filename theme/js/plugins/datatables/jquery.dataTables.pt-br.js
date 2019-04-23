$.extend($.fn.dataTable.defaults, {
    "language": {
        "sEmptyTable": "Nenhum registro encontrado",
        "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
        "sInfoFiltered": "(Filtrados de _MAX_ registros)",
        "sInfoPostFix": "",
        "sInfoThousands": ".",
        "sLengthMenu": "_MENU_ Resultados por página",
        "sLoadingRecords": "Carregando...",
        "sProcessing": "Processando...",
        "sZeroRecords": "Nenhum registro encontrado",
        "sSearch": "Pesquisar",
        "oPaginate": {
            "sNext": "Próximo",
            "sPrevious": "Anterior",
            "sFirst": "Primeiro",
            "sLast": "Último"
        },
        "oAria": {
            "sSortAscending": ": Ordenar colunas de forma ascendente",
            "sSortDescending": ": Ordenar colunas de forma descendente"
        }
    },
    "aLengthMenu": [
        [10, 25, 50, 100, 200, -1],
        [10, 25, 50, 100, 200, "Todos"]
    ]
});

$.fn.dataTableExt.oApi.fnLengthChange = function (oSettings, iDisplay)
{
    oSettings._iDisplayLength = iDisplay;
    oSettings.oApi._fnCalculateEnd(oSettings);

    /* If we have space to show extra rows (backing up from the end point - then do so */
    if (oSettings._iDisplayEnd == oSettings.aiDisplay.length)
    {
        oSettings._iDisplayStart = oSettings._iDisplayEnd - oSettings._iDisplayLength;
        if (oSettings._iDisplayStart < 0)
        {
            oSettings._iDisplayStart = 0;
        }
    }

    if (oSettings._iDisplayLength == -1)
    {
        oSettings._iDisplayStart = 0;
    }

    oSettings.oApi._fnDraw(oSettings);

    if (oSettings.aanFeatures.l)
    {
        $('select', oSettings.aanFeatures.l).val(iDisplay);
    }
};

$.fn.dataTableExt.aTypes.unshift(
    function ( sData ){
        var exp=/data-order="*(-?[0-9\.]+)/;
        if (sData !== null && sData.toString().match(exp)){
            return 'data-order';            
        }
        return null;
    }
);

$.extend( $.fn.dataTableExt.oSort, {
    "data-order-pre": function ( a ) {
        var x = a.toString().match(/data-order="*(-?[0-9\.]+)/);
        if(x != null){
            return parseFloat( x[1] );
        }else{
            return 0;
        }
    },
 
    "data-order-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "data-order-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
} );