<?php

class aprovacao
{

    var $link;
    var $link2;

    function __construct()
    {
        //error_reporting(E_ALL );
        if (file_exists('parametros.php')) {
            require_once('parametros.php');
        } else {
            require_once('..\parametros.php');
        }
        require_once 'services/email/email.php';
        $this->link = conexao("base");
        $this->link2 = conexao("erp");
    }

    function getTitulosParaAprovar($s_usuario) {
        $retorno = array();
        $query = "SELECT
                        vw_T.pagadora 
                        ,vw_T.beneficiario
                        ,vw_T.tipo
                        ,FORMAT (vw_T.valor, 'c', 'pt-br')  AS valor
                        ,vw_T.emissao
                        ,vw_T.vencimento
                        ,vw_T.codigo
                        ,vw_T.observacao
                        ,vw_T.status
                        ,vw_T.selecao
                  FROM dbo.processo_vwAprovFinTitulosParaAprovar AS vw_T
                  ORDER BY vw_T.status DESC";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getTituloItens($s_usuario, $idTitulo) {
//        cabecItens = ["Item", "Data", "Natureza","Valor","Observação"];
        $retorno = array();
        $query = "SELECT *
                  FROM dbo.processo_vwAprovFinTitulosItens AS vw_I
                  WHERE vw_I.codigo=?
                  ORDER BY vw_I.item DESC";
        $params = array($idTitulo);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getTitulosInfo($s_usuario) {
        $retorno = array();
        $query = "SELECT
                    COUNT(af.codigo)AS num
                   ,CONVERT(VARCHAR(10), MAX(af.vencimento), 105) AS dataAte
                   ,CONVERT(VARCHAR(10), MIN(af.vencimento), 105) AS dataDe
                   ,FORMAT (SUM(CAST(af.valor AS DECIMAL(18,2))), 'c', 'pt-br') AS valTotal
                   ,FORMAT (MAX(CAST(af.valor AS DECIMAL(10, 4))), 'c', 'pt-br') AS valMax
                   ,FORMAT (MIN(CAST(af.valor AS DECIMAL(10, 4))), 'c', 'pt-br') AS valMin
                   FROM dbo.processo_vwAprovFinTitulosParaAprovar af
                  ";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getTitulosPagadora($s_usuario){
        $retorno = array();
        $query = "SELECT COUNT(af.codigo) AS numTitulos
                  ,af.pagadora
                  ,FORMAT (SUM(CAST(af.valor AS DECIMAL(18,2))), 'c', 'pt-br') AS totalPagadora
                  FROM dbo.processo_vwAprovFinTitulosParaAprovar af
                  GROUP BY af.pagadora";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getTitulosTipos($s_usuario){
        $retorno = array();
        $query = "SELECT COUNT(af.codigo) AS numTitulos
                  ,af.tipo
                  ,FORMAT (SUM(CAST(af.valor AS DECIMAL(18,2))), 'c', 'pt-br') AS totalTipo
                  FROM dbo.processo_vwAprovFinTitulosParaAprovar af
                  GROUP BY af.tipo";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getTitulosAprovados($s_usuario) {
        $retorno = array();
        $query = "SELECT *
                      FROM dbo.processo_vwAprovFinTitulosAprovados AS vw_T
                      ORDER BY vw_T.aprovacao DESC";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getHistoricoBeneficiario($s_usuario,$idTitulo){
        $retorno = array();
        $query = "SELECT conf.numHistorico
                  FROM configuracao_financeiro AS conf
                  WHERE conf.id=1";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $topNum = $linha->numHistorico;

        $retorno = array();
        $query = "SELECT t.A2_COD
                  FROM processo_vwAprovFinTitulosParaAprovar AS t
                  WHERE t.codigo=?";
        $params = array($idTitulo);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_fornecedor = $linha->A2_COD;

        $query = "SELECT TOP ".$topNum." *
                  FROM processo_vwAprovFinHistoricoPagamentos AS h
                  WHERE h.A2_COD=?
                  ORDER BY CONVERT(DATE,h.data,105) DESC";
        $params = array($id_fornecedor);
        $resul = sqlsrv_query($this->link, $query, $params);
        while($linha= sqlsrv_fetch_object($resul)){
            $retorno[]=$linha;
        }
        return $retorno;
    }

    function getTituloDocumentos($s_usuario,$idTitulo){
        $query = "SELECT vw_D.numero, vw_D.tipo, vw_D.arquivo
                  FROM processo_vwAprovFinTituloDocumentos AS vw_D
                  WHERE vw_D.numero= ? ";
        $params = array($idTitulo);
        $resul = sqlsrv_query($this->link, $query, $params);
        while($linha= sqlsrv_fetch_object($resul)){
            $retorno[]=$linha;
        }
        return $retorno;
    }

    function getFinanceiroInfoParaMensagemDetalhes($s_usuario){
        $retorno = array();
        $query = "SELECT 
                         u.id AS id_de
                        ,u.nome AS nome_de
                        ,u.sobrenome AS sobrenome_de
                        ,u.usuario AS email_de
                        ,(SELECT e.id_usuario FROM configuracao_email_disparo AS e  WHERE tipo_disparo=400)AS id_para
                        ,(SELECT e.nome FROM configuracao_email_disparo AS e  WHERE tipo_disparo=400)AS nome_para
                        ,(SELECT e.sobrenome  FROM configuracao_email_disparo AS e  WHERE tipo_disparo=400)AS sobrenome_para
                        ,(SELECT e.email FROM configuracao_email_disparo AS e  WHERE tipo_disparo=400)AS email_para
                    FROM usuario AS u
                    WHERE u.id = ?";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getMensagemMaisDetalhes($s_usuario,$id_titulo){
        $retorno = array();
        $query = "SELECT TOP 1 
                         u.id
                        ,u.nome
                        ,u.sobrenome
                        ,u.usuario
                        ,msg.mensagem
                        ,LEFT(CONVERT(VARCHAR, msg.data, 105), 10) AS data
                    FROM financeiro_mensagem AS msg
                    LEFT JOIN usuario AS u ON u.id = msg.id_usuario
                    WHERE msg.id_titulo=? AND tipo=1 AND status=410
                    ORDER BY msg.data DESC";
        $params = array($id_titulo);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setMensagemMaisDetalhes($s_usuario,$codigo, $mensagem) {
        //1- INSERT MENSAGEM DO REEMBOLSO
        $query = "INSERT INTO financeiro_mensagem(id_titulo,mensagem,tipo,id_usuario,status,data)
                  OUTPUT inserted.id
                  VALUES(?,?,1,?,410,GETDATE())";
        $params = array($codigo,$mensagem,$s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha = sqlsrv_fetch_object($resul)){
            return $linha->id;
        }else{
            return 0;
        }
    }

    function setFinanceiroStatusAprovadorAprovado($s_usuario, $arrTitulos){
        $cont = 0;
        foreach ($arrTitulos as $titulo) {
            $query = "UPDATE [P12_PRODUCAO].dbo.SE2010
                        SET E2_XPRTLIB = '3'
                           ,E2_DATALIB = CONVERT(varchar, GETDATE() ,112)
                        OUTPUT inserted.E2_DATALIB
                        WHERE E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = ?
                        AND E2_XPRTLIB = '1'
                        OR E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = ?
                        AND E2_XPRTLIB = '2' ";
            $params = array($titulo{"codigo"},$titulo{"codigo"});
            $resul = sqlsrv_query($this->link2, $query, $params);
            if ($linha = sqlsrv_fetch_object($resul)) {
                $cont++;
            }
        }

        $objEmail = new email();
        $valid_email = $objEmail->e11cAprovadoFinanceiro($s_usuario,$arrTitulos);
        if($cont == count($arrTitulos) && $valid_email==1){
            return 1;
        }else{
            return 0;
        }

    }

    function setFinanceiroStatusAprovadorMaisDetalhes($s_usuario, $codigo,$pagadora,$beneficiario,$valor,$emissao,$vencimento,$mensagem){
        $cont = 0;
        $query = "UPDATE [P12_PRODUCAO].dbo.SE2010
                    SET E2_XPRTLIB = '2'
                       ,E2_DATALIB = ''
                    OUTPUT inserted.E2_DATALIB
                    WHERE E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = ?
                    AND E2_XPRTLIB = '1'";
        $params = array($codigo);
        $resul = sqlsrv_query($this->link2, $query, $params);
        if ($linha = sqlsrv_fetch_object($resul)) {

            $objAprovacao =new aprovacao();
            $id_msg = $objAprovacao->setMensagemMaisDetalhes($s_usuario,$codigo,$mensagem);

            $objEmail = new email();
            $valid_email = $objEmail->e12cMaisDetalhesFinanceiro($s_usuario,$codigo,$pagadora,$beneficiario,$valor,$emissao,$vencimento,$mensagem,$id_msg);

            if($id_msg>0 && $valid_email==1){
                return 1;
            }else{
                return 0;
            }
        }


    }

}
?>