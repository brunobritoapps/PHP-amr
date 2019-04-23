<?php

class reembolso {

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

    function getReembolso($s_usuario) {
        $retorno = array();
        $query = "SELECT DISTINCT 
                       rs.id
                      ,rs.id_format AS cod
                      ,rs.data_base AS mes
                      ,rs.titulo_evento AS evento
                      ,d.descricao AS despesa
                      ,(SELECT SUM(CAST(ri.total AS DECIMAL (18,2))) FROM reembolso_itens AS ri WHERE ri.id_reembolso_solicitacao=rs.id)AS total
                      ,e.Nome_Empresa AS empresa
                      ,(SELECT COUNT(id) AS n_item FROM reembolso_itens WHERE id_reembolso_solicitacao=rs.id)AS itens
                      ,rs.data_inclusao AS inclusao
                      ,rs.data_envio AS envio
                      ,CASE WHEN  rs.id_status<=100 AND rs.id_status>0 THEN CAST(rs.id_status AS VARCHAR)+'-'+CAST((SELECT fim_aprov FROM reembolso_guia_aprovador AS gap WHERE gap.id_reeembolso=rs.id_format)AS VARCHAR ) ELSE '' END AS progresso
                      ,CASE id_status WHEN -1 THEN 'EDICAO' WHEN 0 THEN 'ENVIADO' WHEN 100 THEN 'APROVADO'  WHEN 110 THEN 'EM ANALISE' WHEN 150 THEN 'APROVADO-I' WHEN 200 THEN 'REPROVADO' WHEN 210 THEN 'PARA REVISAO'  ELSE 'EM ANALISE' END AS status
                        FROM reembolso_solicitacao AS rs
                        LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                        LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                        LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        WHERE rs.id_usuario = ? 
                        AND id_status<>100 
                        AND id_status<>150 
                        AND id_status<>200
                        ORDER BY rs.id";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            unset($linha->id);
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getReembolsoHistorico($s_usuario) {
        $retorno = array();
        $query = "SELECT DISTINCT 
                       rs.id
                      ,rs.id_format AS cod
                      ,rs.data_base AS mes
                      ,rs.titulo_evento AS evento
                      ,d.descricao AS despesa
                      ,(SELECT SUM(CAST(ri.total AS DECIMAL (18,2))) FROM reembolso_itens AS ri WHERE ri.id_reembolso_solicitacao=rs.id)AS total
                      ,e.Nome_Empresa AS empresa
                      ,(SELECT COUNT(id) AS n_item FROM reembolso_itens WHERE id_reembolso_solicitacao=rs.id)AS itens
                      ,rs.data_inclusao AS inclusao
                      ,rs.data_envio AS envio
                      ,CASE id_status WHEN 100 THEN (SELECT TOP 1 LEFT(CONVERT(VARCHAR, log.data, 105), 10) AS dt FROM log_reembolso_acao AS log WHERE log.id_reembolso=rs.id_format AND log.status_para=100 ORDER BY log.data DESC) 
                                      WHEN 150 THEN (SELECT TOP 1 LEFT(CONVERT(VARCHAR, log.data, 105), 10) AS dt FROM log_reembolso_acao AS log WHERE log.id_reembolso=rs.id_format AND log.status_para=100 ORDER BY log.data DESC) 
                                      WHEN 200 THEN (SELECT TOP 1 LEFT(CONVERT(VARCHAR, log.data, 105), 10) AS dt FROM log_reembolso_acao AS log WHERE log.id_reembolso=rs.id_format AND log.status_para=200 ORDER BY log.data DESC) 
                                      ELSE ''
                                      END AS avaliado
                      ,CASE id_status WHEN -1 THEN 'EDICAO' WHEN 0 THEN 'ENVIADO' WHEN 100 THEN 'APROVADO'  WHEN 110 THEN 'EM ANALISE' WHEN 150 THEN 'APROVADO-I' WHEN 200 THEN 'REPROVADO' WHEN 210 THEN 'PARA REVISAO'  ELSE 'EM ANALISE' END AS status
                        FROM reembolso_solicitacao AS rs
                        LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                        LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                        LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        WHERE rs.id_usuario =?
                        AND id_status BETWEEN 100 AND 200
                        AND id_status<>110
                        ORDER by rs.id";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            unset($linha->id);
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getReembolsoResumo($s_usuario,$id) {
        $retorno = array();
        $query = "SELECT DISTINCT rs.id
                                ,rs.id_tipo_despesa AS despesa 
                                ,rs.id_empresa AS empresa_id
                                ,e.Nome_Empresa AS empresa
                                ,rs.titulo_evento AS titulo
                                ,rs.data_inclusao AS data
                                ,rs.data_base AS data_base
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                    LEFT JOIN corporativo_vwEmpresas AS e ON e.Cod_Empresa = rs.id_empresa
                    WHERE rs.id_format =? ";
        $params = array($id);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getReembolsoItens($s_usuario,$id_format) {
        $retorno = array();
        $query = "SELECT item.data_item AS data
                        ,dbo.fnGetDiaSemana(item.data_item) AS semana
                        ,cli.Descricao AS cliente
                        ,ntz.Descricao AS natureza
                        ,cct.Descricao AS ccusto
                        ,item.valor
                        ,item.desconto
                        ,item.total
                        ,item.observacao
                        ,item.documento
                    FROM reembolso_itens AS item
                    LEFT JOIN corporativo_vwClientes AS cli ON cli.Codigo = item.id_cliente
                    LEFT JOIN corporativo_vwNaturezas AS ntz ON ntz.Codigo = item.id_natureza
                    LEFT JOIN corporativo_vwCcustos AS cct ON cct.Codigo = item.id_ccusto
                    WHERE item.id_reembolso=?
                    ORDER by item.id";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getReembolsoItensReembolso($s_usuario,$id_format) {
        $retorno = array();
        $query = "SELECT item.data_item AS data, cli.Descricao AS cliente,cli.Codigo AS cliente_id, ntz.Codigo AS natureza_id, ntz.Descricao AS natureza,cct.Codigo AS ccusto_id, cct.Descricao AS ccusto, item.valor, item.desconto, item.total, item.observacao, item.documento
                    FROM reembolso_itens AS item
                    LEFT JOIN corporativo_vwClientes AS cli ON cli.Codigo = item.id_cliente
                    LEFT JOIN corporativo_vwNaturezas AS ntz ON ntz.Codigo = item.id_natureza
                    LEFT JOIN corporativo_vwCcustos AS cct ON cct.Codigo = item.id_ccusto
                    WHERE item.id_reembolso = ?
                    ORDER by item.id";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDespesa($s_usuario) {
        $retorno = array();
        $query = "SELECT  td.id AS id, td.descricao AS despesa
                    FROM reembolso_tipo_despesa AS td";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getNatureza($s_usuario) {
        $retorno = array();
        $query = "SELECT  n.Codigo AS id, n.Descricao AS natureza
                    FROM corporativo_vwNaturezas AS n
                    ORDER BY n.Descricao";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getCliente($s_usuario) {
        $retorno = array();
        $query = "SELECT  c.Codigo AS id, c.Descricao AS nome
                    FROM corporativo_vwClientes AS c
                    ORDER BY c.Descricao";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setReembolso($s_usuario, $id, $data, $despesa, $empresa, $titulo, $dataBase, $arrItens)
    {   //1-INSERT REEMBOLSO OU UPDATE
        $valida=0;
        $id_status_old = 0;
        $retorno = array();
        if ($id == "") {//inserir
            $valida = 1;
            //1.1 INSERT REEMBOLSO
            $query = "INSERT INTO reembolso_solicitacao(id_usuario, data_inclusao,data_envio, id_tipo_despesa, id_empresa, titulo_evento, data_base, id_status)
                        OUTPUT inserted.id, inserted.id_status
                        VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array($s_usuario, $data,'', $despesa, $empresa, $titulo,$dataBase,-1);
        } else {//editar
            $valida =2;
            //1.1 SELECT  STATUS ANTERIOR
            $query_s = "SELECT rs.id_status
                    FROM reembolso_solicitacao AS rs
                    WHERE id_format = ?";
            $params_s = array($id);
            $resul_s = sqlsrv_query($this->link, $query_s, $params_s);
            $linha_s = sqlsrv_fetch_object($resul_s);
            $id_status_old = $linha_s->id_status;

            //1.2 UPDATE
            $query = "UPDATE reembolso_solicitacao
                        SET 
                        id_usuario = ?,
                        id_tipo_despesa = ?, 
                        id_empresa = ?,
                        titulo_evento =?,
                        data_envio = '',
                        data_base = ?,
                        id_status = ?
                        OUTPUT inserted.id,inserted.id_status
                        WHERE id_format = ?";
            $params = array($s_usuario, $despesa, $empresa, $titulo,$dataBase, -1, $id);
        }
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_reembolso = $linha->id;
        $id_status_new = $linha->id_status;

        //2-GERA ID COM PREFIXO FORMATADO
        $query = "DECLARE @PREFIXO VARCHAR(2)
                SET @PREFIXO = 'RD'
                DECLARE @NEWID VARCHAR (11)
                DECLARE @LASTVAL INT
                SET @LASTVAL = ?
                IF @LASTVAL is null SET @LASTVAL = 0
                SET @NEWID = @PREFIXO+REPLICATE ( '0' ,4 - len(@LASTVAL) )+cast (@LASTVAL AS VARCHAR )
                SELECT @NEWID AS newid";
        $params = array($id_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $new_id_format = $linha->newid;

        //3-UPDATE COM PREFIXO FORMATADO
        $query = "UPDATE reembolso_solicitacao
                        SET 
                        id_format= ?
                        WHERE id = ?";
        $params = array($new_id_format,$id_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //4-DELETE ITENS DO REEEMBOLSO PARA NOVO INSERT
        $query = "DELETE FROM reembolso_itens
                    WHERE id_reembolso_solicitacao = ?";
        $params = array($id_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //5-INSERT ITENS
        $id_item=1;
        foreach ($arrItens as $item) {
            $query = "INSERT INTO reembolso_itens(id, id_reembolso_solicitacao,data_item,id_cliente,id_natureza,id_ccusto,valor, desconto, total, observacao,documento,id_reembolso)
                      OUTPUT inserted.id
                      VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
            $params = array($id_item, $id_reembolso,$item{'data'},$item{'clienteId'},$item{'natureza'},$item{'ccusto'},$item{'valor'}, $item{'desconto'}, $item{'total'}, $item{'observacao'},$item{'documento'},$new_id_format);
            $resul = sqlsrv_query($this->link, $query, $params);
            $linha = sqlsrv_fetch_object($resul);
            $id_item++;
        }
        if($valida==1){
            //6-GRAVANDO LOG DE STATUS AO GERAR REEMBOLSO
            $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,-2,?,1,GETDATE())";
            $params = array($new_id_format,$s_usuario,$id_status_new);
            $resul = sqlsrv_query($this->link, $query, $params);
        }else if ($valida==2){
            //6-GRAVANDO LOG DE STATUS EDICAO
            $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,?,?,1,GETDATE())";
            $params = array($id,$s_usuario,$id_status_old,$id_status_new);
            $resul = sqlsrv_query($this->link, $query, $params);
        }

//        if(($errors=sqlsrv_errors())!= null){
//            print_r($errors);
//        }
            $obj = new reembolso();
            return $obj->getReembolso($s_usuario);
    }

    function setReembolsoGuiaAprovador($s_usuario, $id_reembolso, $dataBase)
    {
        $retorno = array();
        // 1-SELECIONA USUARIO DO REEMBOLSO
        // 1-SELECIONA GRUPO DO USUARIO
        $query = "SELECT rs.id_usuario, u.id_grupo
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    WHERE id_format = ?";
        $params = array($id_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_usuario = $linha->id_usuario;
        $id_grupo_usuario = $linha->id_grupo;


        // 2- DELETE GUIA DO REEEMBOLSO SE EXISTE
        $query = "DELETE FROM reembolso_guia_aprovador
                    WHERE id_reeembolso = ?";
        $params = array($id_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);



        // 3-SELECIONA TOTAL-MES DE SOLICITACOES DO USUARIO
        $query = "DECLARE @ID_USUARIO INT
                  SET @ID_USUARIO = ?
                  DECLARE @DT_BASE VARCHAR (7)
                  SET @DT_BASE =?
                  SELECT SUM(CAST(ri.total AS DECIMAL (18,2)))AS total_mes
                  FROM reembolso_itens AS ri
                  JOIN reembolso_solicitacao AS rs ON rs.id_format = ri.id_reembolso
                  WHERE rs.id_usuario = @ID_USUARIO AND rs.data_base = @DT_BASE
                  AND rs.id_status BETWEEN 0 AND 100";
        $params = array($id_usuario,$dataBase);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $total_mes_usuario = $linha->total_mes;



        // 4-SELECIONA ORDEM DO ULTIMO APROVADOR DE ACORDO COM A ALCADA DE VALORES
        $query = "DECLARE @TOTAL_MES DECIMAL (18,2)
                  SET @TOTAL_MES = ?
                  SELECT ru.ordem AS ordem
                  FROM reembolso_aprovador_usuario AS ru
                  WHERE ru.id_grupo=? AND @TOTAL_MES>=ru.alcada_inicio AND @TOTAL_MES<=ru.alcada_fim";
        $params = array($total_mes_usuario,$id_grupo_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $ordem_aprovador = $linha->ordem;


        // INSERE DADOS NA TABELA GUIA DO REEMBOLSO
        $query = "INSERT INTO reembolso_guia_aprovador(id_reeembolso, data_base, total_mes, inicio_aprov, fim_aprov, data_envio )
                  VALUES(?,?,?,1,?,GETDATE())";
        $params = array($id_reembolso, $dataBase,$total_mes_usuario,$ordem_aprovador);
        $resul = sqlsrv_query($this->link, $query, $params);

//        if(($errors=sqlsrv_errors())!= null){
//            print_r($errors);
//        }

    }

    function setReembolsoStatusEnviado($s_usuario,$id_format){
        //1-SELECT  STATUS ANTERIOR
        $query = "SELECT rs.id_status
                    FROM reembolso_solicitacao AS rs
                    WHERE id_format = ?";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_status_old = $linha->id_status;

        //2-SETA STATUS ENVIADO
        $retorno = array();
        $query = "UPDATE reembolso_solicitacao
                    SET 
                    id_status = 0,
                    data_envio= CONVERT(VARCHAR(10), GETDATE(), 105)
                    OUTPUT inserted.id
                    WHERE id_format = ?";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_reembolso = $linha->id;

        //3-GRAVANDO LOG DE ACAO STATUS AO ENVIAR REEMBOLSO
        $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                ,id_usuario
                                                ,status_de
                                                ,status_para
                                                ,tipo
                                                ,data)
                          VALUES(?,?,?,0,1,GETDATE())";
        $params = array($id_format,$s_usuario,$id_status_old);
        $resul = sqlsrv_query($this->link, $query, $params);


        // 4-GET DADOS PARA ENVIO DO EMAIL
        $objReembolso = new reembolso();
        $dadosEmail =$objReembolso->getDadosEmailReembolsoEnvio($s_usuario,$id_format);

        // 5-GERA GUIA
        $objReembolso->setReembolsoGuiaAprovador($s_usuario,$id_format,$dadosEmail[0]->mes);

        //6-ENVIO DO EMAIL COM DADOS
        $objEmail = new email();
        $valid_email = $objEmail->e4aEnvioReembolso($s_usuario,$dadosEmail[0]->nome_para,$dadosEmail[0]->email_para,$dadosEmail[0]->nome_usuario,$id_format,$dadosEmail[0]->empresa,$dadosEmail[0]->mes,$dadosEmail[0]->despesa,$dadosEmail[0]->evento,$dadosEmail[0]->total,$dadosEmail[0]->itens,$dadosEmail[0]->envio);

//        if(($errors=sqlsrv_errors())!= null){
//            print_r($errors);
//        }

        //RETORNA CONSULTA DE REEMBOLSO
        if($valid_email==1){
            $obj = new reembolso();
            return $obj->getReembolso($s_usuario);
        }
    }

    function getDadosEmailReembolsoEnvio($s_usuario,$id_formtat) {
        $retorno = array();
        $query = "SELECT DISTINCT 
                       rs.id_format AS id_format
                      ,rs.data_base AS mes
                      ,rs.titulo_evento AS evento
                      ,d.descricao AS despesa
                      ,(SELECT SUM(CAST(ri.total AS DECIMAL (18,2))) FROM reembolso_itens AS ri WHERE ri.id_reembolso_solicitacao=rs.id)AS total
                      ,e.Nome_Empresa AS empresa
                      ,(SELECT COUNT(id) AS n_item FROM reembolso_itens WHERE id_reembolso_solicitacao=rs.id)AS itens
                      ,rs.data_envio AS envio
                      ,u.nome AS nome_usuario
                      ,(SELECT usr.nome 
                        FROM reembolso_aprovador_usuario AS grp 
                        LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario 
                        WHERE grp.id_grupo = u.id_grupo AND grp.ordem =1) AS nome_para
                      ,(SELECT usr.usuario 
                        FROM reembolso_aprovador_usuario AS grp 
                        LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario 
                        WHERE grp.id_grupo = u.id_grupo AND grp.ordem =1) AS email_para  
                        FROM reembolso_solicitacao AS rs
                        LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                        LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                        LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        WHERE rs.id_format = ?";
        $params = array($id_formtat);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setReembolsoStatus($s_usuario,$id_format,$status){
        $retorno = array();
        $query = "UPDATE reembolso_solicitacao
                        SET 
                        id_status = ?
                        OUTPUT inserted.id
                        WHERE id_format = ?";
             if($status=='E'){
                 $query = "UPDATE reembolso_solicitacao
                        SET 
                        id_status = ?,
                        data_envio= CONVERT(VARCHAR(10), GETDATE(), 105)
                        OUTPUT inserted.id
                        WHERE id_format = ?";
                 $params = array(0,$id_format);
             }else if ($status =='R'){
                 $params = array(100,$id_format);
             }else if ($status =='A'){
                 $params = array(200,$id_format);
             }
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_reembolso = $linha->id;

        if(($errors=sqlsrv_errors())!= null){
            print_r($errors);
        }
        $obj = new reembolso();
        return $obj->getReembolso($s_usuario);
    }

    function remReembolso($s_usuario,$id_format) {
        //1-SELECT  STATUS OLD
        $query_s = "SELECT rs.id_status
                    FROM reembolso_solicitacao AS rs
                    WHERE id_format = ?";
        $params_s = array($id_format);
        $resul_s = sqlsrv_query($this->link, $query_s, $params_s);
        $linha_s = sqlsrv_fetch_object($resul_s);
        $id_status_old = $linha_s->id_status;

        //2-DELETA REEMBOLSO
        $retorno = array();
        $query = "DELETE FROM reembolso_solicitacao
                    WHERE id_format = ?";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //3-DELETA ITENS
        $query = "DELETE FROM reembolso_itens
                    WHERE id_reembolso = ?";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //4-GRAVANDO LOG DE STATUS AO DELETAR REEMBOLSO
        $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,?,-3,1,GETDATE())";
        $params = array($id_format,$s_usuario,$id_status_old);
        $resul = sqlsrv_query($this->link, $query, $params);

        // RETORNO
        $obj = new reembolso();
        return $obj->getReembolso($s_usuario);
    }

    function uploadDocumento($s_usuario, $arquivo,$id) {
        // CONSULTA CPF DO USUARIO
        $query = " SELECT u.id,u.cpf AS cpf
                      FROM usuario AS u
                      WHERE u.id=?";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $cpf = $linha->cpf;
        //
        $extensao = strtolower(substr($arquivo,-4));
        $novo_cripto = md5(time().$cpf);
//        $novo_nome = $novo_cripto.'_'.$cpf.'_COMPROVANTE_'.$extensao;
        $novo_nome = 'CP'.$id.'_'.$novo_cripto.$extensao;
        copy("../" . $arquivo, "../files/comp/{$novo_nome}");
        unlink("../" . $arquivo);
        //
        return $novo_nome;
    }

    function getData($s_usuario){
        $retorno = array();
        $query = "SELECT LEFT(CONVERT(VARCHAR, GETDATE(), 105), 10) AS data";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $retorno = $linha->data;
        return $retorno;
    }

    function getDataSemana($s_usuario, $data)
    {
        $retorno = array();
        // CONSULTA CPF DO USUARIO
        $query = " DECLARE @DATE VARCHAR (10)
                    SET @DATE = ?
                    DECLARE @D_DATE VARCHAR (2)
                    DECLARE @M_DATE VARCHAR (2)
                    DECLARE @Y_DATE VARCHAR (4)
                    SET @D_DATE= SUBSTRING (@DATE ,1,2)
                    SET @M_DATE= SUBSTRING (@DATE ,4,2)
                    SET @Y_DATE= SUBSTRING (@DATE ,7,4)
                    DECLARE @DATE_ITEM DATE
                    SET @DATE_ITEM = CONCAT(@Y_DATE,'-',@M_DATE,'-',@D_DATE)
                    DECLARE @WEEK INT
                    DECLARE @DAY VARCHAR (14)
                    SET @WEEK=DATEPART(dw,@DATE_ITEM)
                    SET @DAY=
                      CASE
                        WHEN @WEEK=1 THEN 'domingo'
                        WHEN @WEEK=2 THEN 'segunda-feira'
                        WHEN @WEEK=3 THEN 'terca-feira'
                        WHEN @WEEK=4 THEN 'quarta-feira'
                        WHEN @WEEK=5 THEN 'quinta-feira'
                        WHEN @WEEK=6 THEN 'sexta-feira'
                        WHEN @WEEK=7 THEN 'sabado'
                      END
                    SELECT @DAY AS semana, @WEEK as dia";
        $params = array($data);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getValidaDataLimite($s_usuario,$diaLimite, $dataBase)
    {
        $retorno = array();

        $query = "  DECLARE @DATA_ENVIO VARCHAR(10)
                    SET @DATA_ENVIO = LEFT(CONVERT(VARCHAR, GETDATE(), 105), 10)
                    
                    DECLARE @D_LIM VARCHAR (2)
                    SET @D_LIM = ?
                    
                    DECLARE @DATA_REF VARCHAR(7)
                    SET @DATA_REF = ?
                    
                    DECLARE @D2 VARCHAR (2)
                    DECLARE @M2 VARCHAR (2)
                    DECLARE @Y2 VARCHAR (4)
                    SET @D2= SUBSTRING (@DATA_ENVIO ,1,2)
                    SET @M2= SUBSTRING (@DATA_ENVIO ,4,2)
                    SET @Y2= SUBSTRING (@DATA_ENVIO ,7,4)
                    DECLARE @DT_ENVIO DATE
                    SET @DT_ENVIO = CONCAT(@M2,'-',@D2,'-',@Y2)
                    
                    DECLARE @M VARCHAR(2)
                    DECLARE @Y VARCHAR(4)
                    SET @M =SUBSTRING (@DATA_REF ,1,2)
                    SET @Y =SUBSTRING (@DATA_REF ,4,7)
                    DECLARE @DATA DATETIME
                    DECLARE @DATA_MAX DATETIME
                    SET @DATA = CONCAT(@M,'-01-',@Y)
                    SET @DATA_MAX = CONCAT(@M,'-',@D_LIM,'-',@Y)
                    DECLARE @DT_MIN DATETIME
                    DECLARE @DT_MAX DATETIME
                    SET @DT_MIN= (@DATA - DAY(@DATA) + 1)
                    SET @DT_MAX= DATEADD(MONTH , +1, @DATA_MAX)
                    
                    
                    DECLARE @RETURN INT
                    SET @RETURN = 0
                    IF(@DT_ENVIO <= @DT_MAX AND @DT_ENVIO >= @DT_MIN)
                      BEGIN
                      SET @RETURN = 1
                        SELECT @RETURN AS valida
                      END
                    ELSE
                      BEGIN
                        SELECT @RETURN AS valida
                      END";
        $params = array($diaLimite, $dataBase);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getValidaPeriodoDataItem($s_usuario, $dataItem, $dataBase)
    {
        $retorno = array();

        $query = "  DECLARE @DATA_ITEM VARCHAR(10)
                    SET @DATA_ITEM = ?
                    
                    DECLARE @DATA_BASE VARCHAR(7)
                    SET @DATA_BASE = ?
                    
                    DECLARE @D2 VARCHAR (2)
                    DECLARE @M2 VARCHAR (2)
                    DECLARE @Y2 VARCHAR (4)
                    SET @D2= SUBSTRING (@DATA_ITEM ,1,2)
                    SET @M2= SUBSTRING (@DATA_ITEM ,4,2)
                    SET @Y2= SUBSTRING (@DATA_ITEM ,7,4)
                    DECLARE @DT_ITEM DATE
                    SET @DT_ITEM = CONCAT(@Y2,'-',@M2,'-',@D2)
                    
                    DECLARE @M VARCHAR(2)
                    DECLARE @Y VARCHAR(4)
                    SET @M =SUBSTRING (@DATA_BASE ,1,2)
                    SET @Y =SUBSTRING (@DATA_BASE ,4,7)
                    DECLARE @DATA DATETIME
                    SET @DATA = CONCAT(@Y,'-',@M,'-01')
                    DECLARE @DT_MIN DATETIME
                    DECLARE @DT_MAX DATETIME
                    SET @DT_MIN= (@DATA - DAY(@DATA) + 1)
                    SET @DT_MAX= EOMONTH (@DATA)
                    
                    DECLARE @RETURNO INT
                    
                    IF(@DT_ITEM <= @DT_MAX AND @DT_ITEM >= @DT_MIN)
                    BEGIN
                    SET @RETURNO = 1
                    SELECT @RETURNO AS valida
                    END
                    ELSE
                    BEGIN
                    SET @RETURNO = 0
                    SELECT @RETURNO AS valida
                    END";
        $params = array($dataItem, $dataBase);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDataBase($s_usuario,$dataBase)
    {
        $retorno = array();

        $query = "  DECLARE @D_LIM VARCHAR (2)
                    SET @D_LIM = ?
                    DECLARE @DT_CORRENTE DATETIME
                    SET @DT_CORRENTE = LEFT(CONVERT(VARCHAR, GETDATE(), 120), 10)
                    DECLARE @DT_MIN DATETIME
                    DECLARE @DT_MAX DATETIME
                    SET @DT_MIN= (@DT_CORRENTE - DAY(@DT_CORRENTE) + 1)
                    SET @DT_MAX= EOMONTH (@DT_CORRENTE)
                    --
                    DECLARE @DT_LIM DATETIME
                    SET @DT_LIM = CONCAT(YEAR (@DT_CORRENTE),'-',MONTH(@DT_CORRENTE),'-',@D_LIM)
                    DECLARE @RETORNO INT
                    
                    
                    IF(@DT_CORRENTE >= @DT_MIN AND @DT_CORRENTE <= @DT_LIM)
                    BEGIN
                      SET @RETORNO =2
                      SELECT @RETORNO AS valida,
                             RIGHT(CONVERT(VARCHAR (10),@DT_CORRENTE,105),7)AS dataMesCorrente,
                             RIGHT(CONVERT(VARCHAR (10),DATEADD(MONTH , -1, @DT_CORRENTE),105),7)dataMesAnterior
                    END
                    ELSE
                    BEGIN
                    
                      IF (@DT_CORRENTE >@DT_LIM AND @DT_CORRENTE <= @DT_MAX)
                      BEGIN
                          SET @RETORNO =1
                          SELECT @RETORNO AS valida,
                                 RIGHT(CONVERT(VARCHAR (10),@DT_CORRENTE,105),7)AS dataMesCorrente
                      END
                    END
                    ";
        $params = array($dataBase);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getReembolsoMensagemRevisao($s_usuario,$id_format){
        $retorno = array();
        $query = "SELECT TOP 1 
                         u.id
                        ,u.nome
                        ,u.sobrenome
                        ,u.usuario
                        ,msg.mensagem
                        ,LEFT(CONVERT(VARCHAR, msg.data, 105), 10) AS data
                    FROM reembolso_mensagem AS msg
                    LEFT JOIN usuario AS u ON u.id = msg.id_usuario
                    WHERE id_reembolso=? AND tipo=1 AND status=210
                    ORDER BY msg.data DESC";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getReembolsoMensagemReprovado($s_usuario,$id_format){
        $retorno = array();
        $query = "SELECT TOP 1
                         u.id 
                        ,u.nome
                        ,u.sobrenome
                        ,u.usuario
                        ,msg.mensagem
                        ,LEFT(CONVERT(VARCHAR, msg.data, 105), 10) AS data
                    FROM reembolso_mensagem AS msg
                    LEFT JOIN usuario AS u ON u.id = msg.id_usuario
                    WHERE id_reembolso=? AND tipo=1 AND status=200
                    ORDER BY msg.data DESC";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getValorNaturezakm($s_usuario){
        $retorno = array();
        $query = "SELECT crc.id_contexto AS id_natureza ,crc.cota AS valor
                    FROM configuracao_reembolso_cotas AS crc
                    WHERE crc.id_contexto ='20002' ";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

}

?>