<?php

class aprovacao {

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

    function getReembolsoAprovador($s_usuario) {
        $retorno_final = array();
        // SELECIONA O GRUPO DO APROVADOR
        $query = "SELECT u.id_grupo
                      FROM usuario AS u
                      WHERE u.id = ?";
        $params = array($s_usuario);
        $resul_1 = sqlsrv_query($this->link, $query, $params);
        $linha_1 = sqlsrv_fetch_object($resul_1);
        $id_aprovador = $s_usuario;
        $id_grupo_aprovador = $linha_1->id_grupo;


        // SELECIONA AS SOLICITACOES EM STATUS DE >-1 {0-N} <100
        $query = "SELECT rs.id
                        ,rs.id_usuario
                        ,rs.id_status
                    FROM reembolso_solicitacao AS rs
                    WHERE rs.id_status>-1 AND rs.id_status<100";
        $params = array();
        $resul_2 = sqlsrv_query($this->link, $query, $params);
        while ($linha_2 = sqlsrv_fetch_object($resul_2)) {
            //SELCIONA ID_REEEMBOLSO + ID_USUARIO + ID_STATUS_REEMBOLSO
            $id_reembolso = $linha_2->id;
            $id_usuario = $linha_2->id_usuario;
            $status_reembolso = $linha_2->id_status;
            $query="
                      DECLARE @ID_USUARIO int
                      DECLARE @ID_APROVADOR  int
                      DECLARE @ID_GRUPO_APROVADOR int
                      DECLARE @ID_REEMBOLSO int  
                      DECLARE @STATUS_REEMBOLSO int
                      
                      SET @ID_USUARIO =?
                      SET @ID_APROVADOR =?
                      SET @ID_GRUPO_APROVADOR =?
                      SET @ID_REEMBOLSO=?
                      SET @STATUS_REEMBOLSO=?
                      
                      DECLARE @S int
                      DECLARE @N int                    
                      SET @S=1
                      SET @N=0
                      
                      DECLARE @N_ORDEM int
                
                      DECLARE @ID_GRUPO_USUARIO int
                      SELECT @ID_GRUPO_USUARIO = u.id_grupo
                          FROM usuario AS u
                      WHERE u.id = @ID_USUARIO;
                        
                      IF
                      (SELECT COUNT (ga.ordem)
                          FROM reembolso_aprovador_usuario AS ga
                          LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                      WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO)=1
                
                      BEGIN
                          SELECT @N_ORDEM = ga.ordem
                              FROM reembolso_aprovador_usuario AS ga
                              LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                          WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO
                
                          DECLARE @ORDEM_APROVADOR int
                          SET @ORDEM_APROVADOR = @N_ORDEM-1
                
                          IF(@STATUS_REEMBOLSO = @ORDEM_APROVADOR )
                          BEGIN
                              SELECT DISTINCT rs.id
                                    ,rs.id_format AS cod
                                    ,rs.data_base AS mes
                                    ,rs.titulo_evento
                                    ,d.descricao AS despesa
                                    ,g.nome AS grupo
                                    ,(SELECT SUM(CAST(ri.valor AS DECIMAL (18,2))) FROM reembolso_itens AS ri WHERE ri.id_reembolso_solicitacao=rs.id)AS total
                                    ,u.nome
                                    ,u.id AS usuario
                                    ,REPLACE (LTRIM(e.Nome_Empresa),'AMERICA ','') AS empresa
                                    ,rs.data_inclusao
                                    ,rs.data_envio
                                    ,(SELECT COUNT(id) AS n_item FROM reembolso_itens WHERE id_reembolso_solicitacao=rs.id)AS itens
                                    ,CASE WHEN  rs.id_status<100 AND rs.id_status>0 THEN CAST(rs.id_status AS VARCHAR)+'-'+CAST((SELECT fim_aprov FROM reembolso_guia_aprovador AS gap WHERE gap.id_reeembolso=rs.id_format)AS VARCHAR ) ELSE '' END AS progresso
                                    ,CASE id_status WHEN -1 THEN 'EDICAO' WHEN 0 THEN 'ENVIADO' WHEN 100 THEN 'APROVADO'  WHEN 110 THEN 'EM ANALISE' WHEN 150 THEN 'INTEGRADO' WHEN 200 THEN 'REPROVADO' WHEN 210 THEN 'PARA REVISAO'  ELSE 'EM ANALISE' END AS status
                            FROM reembolso_solicitacao AS rs
                            LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                            LEFT JOIN vwEmpresas AS e ON e.Cod_Empresa = rs.id_empresa
                            LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                            LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                            WHERE rs.id = @ID_REEMBOLSO
                            ORDER by rs.id
                          END
                          
                      END";
            $params = array($id_usuario,$id_aprovador,$id_grupo_aprovador,$id_reembolso,$status_reembolso);
            $resul_3 = sqlsrv_query($this->link, $query, $params);
            while ($linha_3 = sqlsrv_fetch_object($resul_3)) {
                unset($linha_3->id);
                unset($linha_3->nome);
                unset($linha_3->grupo);
                $retorno_final[]=$linha_3;
            }
        }
        return $retorno_final;
    }

    function getReembolsoRevisao($s_usuario) {
        $retorno_final = array();
        // SELECIONA O GRUPO DO APROVADOR
        $query = "SELECT u.id_grupo
                      FROM usuario AS u
                      WHERE u.id = ?";
        $params = array($s_usuario);
        $resul_1 = sqlsrv_query($this->link, $query, $params);
        $linha_1 = sqlsrv_fetch_object($resul_1);
        $id_aprovador = $s_usuario;
        $id_grupo_aprovador = $linha_1->id_grupo;


        // SELECIONA AS SOLICITACOES EM STATUS DE >-1 {0-N} <100
        $query = "SELECT rs.id
                        ,rs.id_usuario
                        ,rs.id_status
                    FROM reembolso_solicitacao AS rs
                    WHERE rs.id_status=110";
        $params = array();
        $resul_2 = sqlsrv_query($this->link, $query, $params);
        while ($linha_2 = sqlsrv_fetch_object($resul_2)) {
            //SELCIONA ID_REEEMBOLSO + ID_USUARIO + ID_STATUS_REEMBOLSO
            $id_reembolso = $linha_2->id;
            $id_usuario = $linha_2->id_usuario;
            $status_reembolso = $linha_2->id_status;
            $query="
                      DECLARE @ID_USUARIO int
                      DECLARE @ID_APROVADOR  int
                      DECLARE @ID_GRUPO_APROVADOR int
                      DECLARE @ID_REEMBOLSO int  
                      DECLARE @STATUS_REEMBOLSO int
                      
                      SET @ID_USUARIO =?
                      SET @ID_APROVADOR =?
                      SET @ID_GRUPO_APROVADOR =?
                      SET @ID_REEMBOLSO=?
                      SET @STATUS_REEMBOLSO=?
                      
                      DECLARE @S int
                      DECLARE @N int                    
                      SET @S=1
                      SET @N=0
                      
                      DECLARE @N_ORDEM int
                
                      DECLARE @ID_GRUPO_USUARIO int
                      SELECT @ID_GRUPO_USUARIO = u.id_grupo
                          FROM usuario AS u
                      WHERE u.id = @ID_USUARIO;
                        
                      IF
                      (SELECT COUNT (ga.ordem)
                          FROM reembolso_aprovador_usuario AS ga
                          LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                      WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO AND ga.ordem=1 )=1
                
                      BEGIN
                          SELECT @N_ORDEM = ga.ordem
                              FROM reembolso_aprovador_usuario AS ga
                              LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                          WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO
                
                          DECLARE @ORDEM_APROVADOR int
                          SET @ORDEM_APROVADOR = @N_ORDEM-1
                
                              SELECT DISTINCT rs.id
                                    ,rs.id_format AS cod
                                    ,rs.data_base AS mes
                                    ,rs.titulo_evento
                                    ,d.descricao AS despesa
                                    ,(SELECT SUM(CAST(ri.total AS DECIMAL (18,2))) FROM reembolso_itens AS ri WHERE ri.id_reembolso_solicitacao=rs.id)AS total
                                    ,u.nome
                                    ,u.id AS usuario
                                    ,REPLACE (LTRIM(e.Nome_Empresa),'AMERICA ','') AS empresa
                                    ,(SELECT COUNT(id) AS n_item FROM reembolso_itens WHERE id_reembolso_solicitacao=rs.id)AS itens
                                    ,rs.data_inclusao
                                    ,rs.data_envio
                                    ,CASE WHEN  rs.id_status<100 AND rs.id_status>0 THEN CAST(rs.id_status AS VARCHAR)+'-'+CAST((SELECT fim_aprov FROM reembolso_guia_aprovador AS gap WHERE gap.id_reeembolso=rs.id_format)AS VARCHAR ) ELSE '' END AS progresso
                                    ,CASE id_status WHEN -1 THEN 'EDICAO' WHEN 0 THEN 'ENVIADO' WHEN 100 THEN 'APROVADO'  WHEN 110 THEN 'NEGADO' WHEN 150 THEN 'INTEGRADO' WHEN 200 THEN 'REPROVADO' WHEN 210 THEN 'PARA REVISAO'  ELSE 'EM ANALISE' END AS status
                            FROM reembolso_solicitacao AS rs
                            LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                            LEFT JOIN vwEmpresas AS e ON e.Cod_Empresa = rs.id_empresa
                            LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                            LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                            WHERE rs.id = @ID_REEMBOLSO AND rs.id_status=110
                            ORDER by rs.id
                        
                      END";
            $params = array($id_usuario,$id_aprovador,$id_grupo_aprovador,$id_reembolso,$status_reembolso);
            $resul_3 = sqlsrv_query($this->link, $query, $params);
            while ($linha_3 = sqlsrv_fetch_object($resul_3)) {
                unset($linha_3->id);
                $retorno_final[]=$linha_3;
            }
        }
        return $retorno_final;
    }

    function getReembolsoHistorico($s_usuario) {
        $retorno_final = array();

        // SELECIONA AS SOLICITACOES EM STATUS DE >-1 {0-N} <100
        $query = "SELECT rs.id
                        ,rs.id_format
                        ,rs.id_usuario
                    FROM reembolso_solicitacao AS rs
                    WHERE rs.id_status IN (100,150,200)";
        $params = array();
        $resul_1 = sqlsrv_query($this->link, $query, $params);
        //SELCIONA ID_REEEMBOLSO + ID_USUARIO + ID_STATUS_REEMBOLSO

        while ($linha_1 = sqlsrv_fetch_object($resul_1)) {
                $id_reembolso = $linha_1->id;
                $id_format_reembolso = $linha_1->id_format;
                $id_usuario = $linha_1->id_usuario;
                $query="
                      DECLARE @ID_USUARIO int
                      DECLARE @ID_APROVADOR  int
                      DECLARE @ID_REEMBOLSO varchar(6)
                    
                      SET @ID_USUARIO =?
                      SET @ID_APROVADOR =?
                      SET @ID_REEMBOLSO=?
                    
                      DECLARE @ID_GRUPO_USUARIO int
                      SELECT @ID_GRUPO_USUARIO = u.id_grupo
                         FROM usuario AS u
                      WHERE u.id = @ID_USUARIO
                    
                      IF
                      (SELECT COUNT (ga.ordem)
                           FROM reembolso_aprovador_usuario AS ga
                           LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                      WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO )=1
                      BEGIN
                            DECLARE @ORDEM_APROVADOR int
                            SELECT @ORDEM_APROVADOR = ga.ordem
                            FROM reembolso_aprovador_usuario AS ga
                            LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                            WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO
                          
                            DECLARE @ORDEM_FIM_GUIA int
                            SELECT @ORDEM_FIM_GUIA=g.fim_aprov
                            FROM reembolso_guia_aprovador AS g
                            WHERE g.id_reeembolso = @ID_REEMBOLSO   
                            
                            IF(@ORDEM_APROVADOR<=@ORDEM_FIM_GUIA)
                            BEGIN         
                                   SELECT DISTINCT rs.id
                                        ,rs.id_format AS cod
                                        ,rs.data_base
                                        ,rs.titulo_evento
                                        ,d.descricao AS despesa
                                        ,g.nome AS grupo
                                        ,(SELECT SUM(CAST(ri.total AS DECIMAL (18,2))) FROM reembolso_itens AS ri WHERE ri.id_reembolso_solicitacao=rs.id)AS total
                                        ,u.nome
                                        ,u.id AS usuario
                                        ,REPLACE (LTRIM(e.Nome_Empresa),'AMERICA ','') AS empresa
                                        ,rs.data_inclusao AS inclusao
                                        ,rs.data_envio AS envio
                                        ,CASE id_status WHEN 100 THEN (SELECT TOP 1 LEFT(CONVERT(VARCHAR, log.data, 105), 10) AS dt FROM log_reembolso_acao AS log WHERE log.id_reembolso=rs.id_format AND log.status_para=100 ORDER BY log.data DESC)
                                                        WHEN 150 THEN (SELECT TOP 1 LEFT(CONVERT(VARCHAR, log.data, 105), 10) AS dt FROM log_reembolso_acao AS log WHERE log.id_reembolso=rs.id_format AND log.status_para=100 ORDER BY log.data DESC)
                                                        WHEN 200 THEN (SELECT TOP 1 LEFT(CONVERT(VARCHAR, log.data, 105), 10) AS dt FROM log_reembolso_acao AS log WHERE log.id_reembolso=rs.id_format AND log.status_para=200 ORDER BY log.data DESC)
                                                        ELSE ''
                                                        END AS avaliado
                                        ,(SELECT COUNT(id) AS n_item FROM reembolso_itens WHERE id_reembolso_solicitacao=rs.id)AS itens
                                        ,CASE id_status WHEN -1 THEN 'EDICAO' WHEN 0 THEN 'ENVIADO' WHEN 100 THEN 'APROVADO'  WHEN 110 THEN 'EM ANALISE' WHEN 150 THEN 'APROVADO-ERP' WHEN 200 THEN 'REPROVADO' WHEN 210 THEN 'PARA REVISAO'  ELSE 'EM ANALISE' END AS status
                                   FROM reembolso_solicitacao AS rs
                                   LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                                   LEFT JOIN vwEmpresas AS e ON e.Cod_Empresa = rs.id_empresa
                                   LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                                   LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                                   WHERE rs.id_format = @ID_REEMBOLSO
                                   AND rs.id_status IN (100,150,200)
                                   ORDER by rs.id
                            END
                       END";
            $params = array($id_usuario,$s_usuario,$id_format_reembolso);
            $resul_2 = sqlsrv_query($this->link, $query, $params);
            while ($linha_2 = sqlsrv_fetch_object($resul_2)) {
                unset($linha_2->id);
                $retorno_final[]=$linha_2;
            }
        }
        return $retorno_final;
    }

    function getReembolsoAcompanhamento($s_usuario) {
        $retorno_final = array();
        // SELECIONA O GRUPO DO APROVADOR
        $query = "SELECT u.id_grupo
                      FROM usuario AS u
                      WHERE u.id = ?";
        $params = array($s_usuario);
        $resul_1 = sqlsrv_query($this->link, $query, $params);
        $linha_1 = sqlsrv_fetch_object($resul_1);
        $id_aprovador = $s_usuario;
        $id_grupo_aprovador = $linha_1->id_grupo;


        // SELECIONA AS SOLICITACOES EM STATUS DE >-1 {0-N} <100
        $query = "SELECT rs.id
                        ,rs.id_usuario
                        ,rs.id_status
                    FROM reembolso_solicitacao AS rs
                    WHERE rs.id_status IN(1,2,3,4,5,6,7,8,9,10,110,210) ";
        $params = array();
        $resul_2 = sqlsrv_query($this->link, $query, $params);
        while ($linha_2 = sqlsrv_fetch_object($resul_2)) {
            //SELCIONA ID_REEEMBOLSO + ID_USUARIO + ID_STATUS_REEMBOLSO
            $id_reembolso = $linha_2->id;
            $id_usuario = $linha_2->id_usuario;
            $status_reembolso = $linha_2->id_status;
            $query="
                      DECLARE @ID_USUARIO int
                      DECLARE @ID_APROVADOR  int
                      DECLARE @ID_GRUPO_APROVADOR int
                      DECLARE @ID_REEMBOLSO int  
                      DECLARE @STATUS_REEMBOLSO int
                      
                      SET @ID_USUARIO =?
                      SET @ID_APROVADOR =?
                      SET @ID_GRUPO_APROVADOR =?
                      SET @ID_REEMBOLSO=?
                      SET @STATUS_REEMBOLSO=?
                      
                      DECLARE @S int
                      DECLARE @N int                    
                      SET @S=1
                      SET @N=0
                      
                      DECLARE @N_ORDEM int
                
                      DECLARE @ID_GRUPO_USUARIO int
                      SELECT @ID_GRUPO_USUARIO = u.id_grupo
                          FROM usuario AS u
                      WHERE u.id = @ID_USUARIO;
                        
                      IF
                      (SELECT COUNT (ga.ordem)
                          FROM reembolso_aprovador_usuario AS ga
                          LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                      WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO)=1
                
                      BEGIN
                          SELECT @N_ORDEM = ga.ordem
                              FROM reembolso_aprovador_usuario AS ga
                              LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                          WHERE ga.id_usuario = @ID_APROVADOR AND gp.id=@ID_GRUPO_USUARIO
                
                          DECLARE @ORDEM_APROVADOR int
                          SET @ORDEM_APROVADOR = @N_ORDEM-1
                           
                              SELECT DISTINCT rs.id
                                        ,rs.id_format AS cod
                                        ,rs.titulo_evento
                                        ,d.descricao AS despesa
                                        ,g.nome AS grupo
                                        ,(SELECT SUM(CAST(ri.total AS DECIMAL (18,2))) FROM reembolso_itens AS ri WHERE ri.id_reembolso_solicitacao=rs.id)AS total
                                        ,u.nome
                                        ,u.id AS usuario
                                        ,REPLACE (LTRIM(e.Nome_Empresa),'AMERICA ','') AS empresa
                                        ,rs.data_inclusao
                                        ,rs.data_envio
                                        ,(SELECT COUNT(id) AS n_item FROM reembolso_itens WHERE id_reembolso_solicitacao=rs.id)AS itens
                                        ,CASE WHEN  rs.id_status<100 AND rs.id_status>0 THEN CAST(rs.id_status AS VARCHAR)+'-'+CAST((SELECT fim_aprov FROM reembolso_guia_aprovador AS gap WHERE gap.id_reeembolso=rs.id_format)AS VARCHAR ) ELSE '' END AS progresso
                                        ,CASE id_status WHEN -1 THEN 'EDICAO' WHEN 0 THEN 'ENVIADO' WHEN 100 THEN 'APROVADO'  WHEN 110 THEN 'NEGADO' WHEN 150 THEN 'INTEGRADO' WHEN 200 THEN 'REPROVADO' WHEN 210 THEN 'PARA REVISAO'  ELSE 'EM ANALISE' END AS status
                            FROM reembolso_solicitacao AS rs
                            LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                            LEFT JOIN vwEmpresas AS e ON e.Cod_Empresa = rs.id_empresa
                            LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                            LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                            WHERE rs.id = @ID_REEMBOLSO 
                            AND rs.id_status  IN(@ORDEM_APROVADOR+1,@ORDEM_APROVADOR+2,@ORDEM_APROVADOR+3,@ORDEM_APROVADOR+4,@ORDEM_APROVADOR+5,110,210)
                            ORDER by rs.id
                      END";
            $params = array($id_usuario,$id_aprovador,$id_grupo_aprovador,$id_reembolso,$status_reembolso);
            $resul_3 = sqlsrv_query($this->link, $query, $params);
            while ($linha_3 = sqlsrv_fetch_object($resul_3)) {
                unset($linha_3->id);
                $retorno_final[]=$linha_3;
            }
        }
        return $retorno_final;
    }

    function getInfoUsuario($s_usuario, $id_format_reembolso) {
        // 1-SELECIONA USUARIO DO REEMBOLSO
        // 1-SELECIONA GRUPO DO USUARIO
        $query = "SELECT rs.id_usuario
                        ,rs.id_status
                        ,u.id_grupo
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_usuario =$linha->id_usuario;
        $id_status= $linha->id_status;
        $id_grupo_usuario = $linha->id_grupo;

        // 2-SELECIONA INFO USUARIO
        $retorno = array();
        $query_1 = " SELECT u.id
                        ,u.usuario
                        ,u.nome
                        ,u.sobrenome
                        ,u.cpf AS cpf
                        ,u.conta AS id_conta
                        ,u.ativo AS id_status
                        ,s.status
                        ,cc.Codigo AS id_ccusto
                        ,cc.Descricao AS ccusto
                        ,c.nome AS conta
                        ,g.id AS id_grupo
                        ,g.nome AS grupo
                        ,(SELECT TOP 1 Nome_Empresa FROM usuario_ccusto AS cc LEFT JOIN vwEmpresas ON Cod_Empresa = SUBSTRING (cc.id_ccusto,1,1) WHERE cc.id_usuario=u.id) AS empresa
                        ,(SELECT  ga.data_base FROM reembolso_guia_aprovador ga WHERE id_reeembolso = ?) AS data_base
                        ,(SELECT  ga.total_mes FROM reembolso_guia_aprovador ga WHERE id_reeembolso = ?) AS total_mes
                        ,(SELECT  ga.inicio_aprov FROM reembolso_guia_aprovador ga WHERE id_reeembolso = ?) AS inicio_aprov
                        ,(SELECT  ga.fim_aprov FROM reembolso_guia_aprovador ga WHERE id_reeembolso = ?) AS fim_aprov
                        ,(SELECT  ga.data_envio FROM reembolso_guia_aprovador ga WHERE id_reeembolso = ?) AS data_envio
                        FROM usuario AS u
                        LEFT JOIN contas AS c ON c.id = u.conta
                        LEFT JOIN usuario_status AS s ON s.id = u.ativo
                        LEFT JOIN corporativo_vwCcustos AS cc ON cc.Codigo = (SELECT TOP 1 cc1.id_ccusto FROM usuario_ccusto AS cc1 WHERE cc1.id_usuario = u.id)
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        WHERE u.id = ?";
        $params = array($id_format_reembolso,$id_format_reembolso,$id_format_reembolso,$id_format_reembolso,$id_format_reembolso,$id_usuario);
        $resul_1 = sqlsrv_query($this->link, $query_1, $params);
        while ($linha_1 = sqlsrv_fetch_object($resul_1)) {
            $retorno[] = $linha_1;
        }
        return $retorno;
    }

    function getInfoAprovador($s_usuario,$id_format_reembolso) {
        $retorno = array();
        // 1-SELECIONA USUARIO DO REEMBOLSO
        // 1-SELECIONA GRUPO DO USUARIO
        $query = "SELECT rs.id_usuario
                        ,rs.id_status
                        ,u.id_grupo
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_usuario =$linha->id_usuario;
        $id_status= $linha->id_status;
        $id_grupo_usuario = $linha->id_grupo;


        // 2-VERIFICA SE APROVADOR E USUARIO POSSUEM VINCULO E RETORNA 1 OU 0
        $query = "SELECT COUNT (ga.ordem) AS valida
                    FROM reembolso_aprovador_usuario AS ga
                    LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                    WHERE ga.id_usuario = ? AND gp.id=?";
        $params = array($s_usuario,$id_grupo_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $verificador_vinculo = $linha->valida;


        // 3-VERIFICA VINCULO ENTRE USUARIO E APROVADOR
        if($verificador_vinculo ==1){
            //4-SELECIONA DADOS DA GUIA DO REEMBOLSO
            //4-SELECIONA DADOS DO GRUPO DO USUARIO=APROVADOR
            $query = "SELECT  ga.data_base
                             ,ga.total_mes
                             ,ga.inicio_aprov
                             ,ga.fim_aprov
                             ,ga.data_envio
                             ,ra.id_usuario
                             ,rg.nome AS grupo
                             ,rg.id AS id_grupo
                        FROM reembolso_guia_aprovador ga
                        LEFT JOIN reembolso_aprovador_usuario AS ra ON ra.id_grupo=?
                        LEFT JOIN reembolso_aprovador_grupo AS rg ON rg.id=ra.id_grupo
                        WHERE id_reeembolso = ? AND ra.ordem=ga.fim_aprov";
            $params = array($id_grupo_usuario,$id_format_reembolso);
            $resul = sqlsrv_query($this->link, $query, $params);

//            if(($errors=sqlsrv_errors())!= null){
//                print_r($errors);
//            }
            while ($linha = sqlsrv_fetch_object($resul)) {
                $retorno[] = $linha;
            }
            return $retorno;

        }

    }

    function getReembolsoResumo($s_usuario,$id) {
        $retorno = array();
        $query = "SELECT DISTINCT rs.id
                            ,rs.id_tipo_despesa AS despesa
                            ,rs.id_empresa AS empresa_id
                            ,e.Nome_Empresa AS empresa
                            ,rs.titulo_evento AS titulo
                            ,rs.data_inclusao AS data
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
        $query = "SELECT item.data_item AS data
                        ,cli.Descricao AS cliente
                        ,cli.Codigo AS cliente_id
                        ,ntz.Codigo AS natureza_id
                        ,ntz.Descricao AS natureza
                        ,cct.Codigo AS ccusto_id
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
                    WHERE item.id_reembolso = ?
                    ORDER by item.id";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function setReembolsoStatusAprovadorAprovado($s_usuario, $id_format) {

        // 1-SELECIONA USUARIO DO REEMBOLSO
        // 1-SELECIONA GRUPO DO USUARIO
        $query = "SELECT rs.id_usuario
                        ,rs.id_status
                        ,u.id_grupo
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    WHERE id_format = ?";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_usuario = $linha->id_usuario;
        $id_grupo_usuario = $linha->id_grupo;
        $id_status = $linha->id_status;

        // 2-SELECIONA GRUPO DO APROVADOR
        $query = "SELECT u.id_grupo
                    FROM usuario AS u
                    WHERE id = ?";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_grupo_aprovador = $linha->id_grupo;

        // 3-VERIFICA SE APROVADOR E USUARIO POSSUEM VINCULO E RETORNA 1 OU 0
        $query = "SELECT COUNT (ga.ordem) AS valida
                    FROM reembolso_aprovador_usuario AS ga
                    LEFT JOIN reembolso_aprovador_grupo AS gp ON gp.id = ga.id_grupo
                    WHERE ga.id_usuario = ? AND gp.id=?";
        $params = array($s_usuario,$id_grupo_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $verificador_vinculo = $linha->valida;

        if($verificador_vinculo ==1) {
            //4-SELECIONA ULTIMA ORDEM DE APROVACAO SEGUNDO A GUIA DE APROVACAO
            //4-SELECIONA ID_ULTIMO_APROVADOR
            $query = "SELECT ga.fim_aprov AS ultimo_aprovador
                             ,ra.id_usuario 
                        FROM reembolso_guia_aprovador ga
                        LEFT JOIN reembolso_aprovador_usuario AS ra ON ra.id_grupo=? 
                        WHERE id_reeembolso = ? AND ra.ordem=ga.fim_aprov";
            $params = array($id_grupo_usuario, $id_format);
            $resul = sqlsrv_query($this->link, $query, $params);
            $linha = sqlsrv_fetch_object($resul);
            $ordem_ultimo_aprovador = $linha->ultimo_aprovador;
            $id_ultimo_aprovador = $linha->id_usuario;

            if ($id_ultimo_aprovador == $s_usuario) {
                // 5-SETA STATUS - APROVADO
                $query = "UPDATE reembolso_solicitacao
                             SET id_status = 100
                             WHERE id_format = ?";
                $params = array($id_format);
                $resul = sqlsrv_query($this->link, $query, $params);

                //6-GRAVANDO LOG DE APROVACAO
                $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,?,100,2, GETDATE())";
                $params = array($id_format, $s_usuario, $id_status);
                $resul = sqlsrv_query($this->link, $query, $params);
            } else {
                //5-SETA STATUS - PROXIMA APROVACAO
                $query = "UPDATE reembolso_solicitacao
                             SET id_status = ?
                             WHERE id_format = ?";
                $params = array($id_status + 1, $id_format);
                $resul = sqlsrv_query($this->link, $query, $params);

                //6-GRAVANDO LOG DE STATUS - APROVACAO
                $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,?,?,2,GETDATE())";
                $params = array($id_format, $s_usuario, $id_status, $id_status + 1);
                $resul = sqlsrv_query($this->link, $query, $params);
            }


            $arrGetDados = array();
            //7-GET DADOS PARA AVALIAR O TIPO DE EMAIL A ENVIAR
            $objGetDados = new aprovacao();
            $arrDados=$objGetDados->getDadosEmailReembolsoAvaliaAprovacao($s_usuario, $id_format);
            $status=0;
            $ordem_fim=0;
            $status = intval($arrDados[0]->id_status);
            $ordem_fim = intval($arrDados[0]->fim_aprov);

            if ($status< $ordem_fim) {
                //8-GET DADOS PARA ENVIO DE EMAIL
                $objDadosProximo = new aprovacao();
                $dadosProximo = $objDadosProximo->getDadosEmailReembolsoProximoAprovador($s_usuario, $id_format);
                //9-ENVIAR EMAIL
                $objEmailProximo = new email();
                $valid_email_proximo = $objEmailProximo->e4aEnvioReembolso($s_usuario,$dadosProximo[0]->nome_para, $dadosProximo[0]->email_para, $dadosProximo[0]->nome_usuario, $dadosProximo[0]->id_format, $dadosProximo[0]->empresa, $dadosProximo[0]->mes, $dadosProximo[0]->despesa, $dadosProximo[0]->evento, $dadosProximo[0]->total, $dadosProximo[0]->itens, $dadosProximo[0]->envio);

                if ($valid_email_proximo == 1) {
                    return 2;
                } else {
                    return 0;
                }

            } else if ($status == 100) {
                //8-GET DADOS PARA ENVIO DE EMAIL
                $objDadosAprovado = new aprovacao();
                $dadosAprovado = $objDadosAprovado->getDadosEmailReembolsoAprovado($s_usuario, $id_format);

                //9-ENVIAR EMAIL
                $objEmailAprovado = new email();
                $valid_email_aprovado = $objEmailAprovado->e5cAprovadoReembolso($s_usuario,$dadosAprovado[0]->nome_para, $dadosAprovado[0]->email_para, $dadosAprovado[0]->id_format, $dadosAprovado[0]->empresa, $dadosAprovado[0]->mes, $dadosAprovado[0]->despesa, $dadosAprovado[0]->evento, $dadosAprovado[0]->total, $dadosAprovado[0]->itens, $dadosAprovado[0]->envio, $dadosAprovado[0]->aprovacao, $dadosAprovado[0]->pagamento);

                if ($valid_email_aprovado == 1) {
                    return 1;
                } else {
                    return 0;
                }

            }
        }
    }

    function setReembolsoStatusAprovadorReprovado($s_usuario,$id_format_reembolso) {
        // 1-SELECIONA USUARIO DO REEMBOLSO
        // 1-SELECIONA GRUPO DO USUARIO
        $query = "SELECT rs.id_usuario
                        ,rs.id_status
                        ,u.id_grupo
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_usuario = $linha->id_usuario;
        $id_grupo_usuario = $linha->id_grupo;
        $id_status = $linha->id_status;

        // 2-SETA STATUS PARA REPROVADO
        $retorno = array();
        $query = "UPDATE reembolso_solicitacao
                        SET id_status = 200
                        WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //3-GRAVANDO LOG REEMBOLSO ACAO - REPROVADO
        $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,?,200,2,GETDATE())";
        $params = array($id_format_reembolso,$s_usuario,$id_status);
        $resul = sqlsrv_query($this->link, $query, $params);

//        if(($errors=sqlsrv_errors())!= null){
//            print_r($errors);
//        }
        //$obj = new aprovacao();
        //return $obj->getReembolsoAprovador($s_usuario);
    }

    function setReembolsoStatusAprovadorRevisao($s_usuario,$id_format_reembolso) {
        // 1-SELECIONA USUARIO DO REEMBOLSO
        // 1-SELECIONA GRUPO DO USUARIO
        $query = "SELECT rs.id_usuario
                        ,rs.id_status
                        ,u.id_grupo
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_usuario = $linha->id_usuario;
        $id_grupo_usuario = $linha->id_grupo;
        $id_status = $linha->id_status;

        // 2-SETA STATUS PARA REPROVADO
        $retorno = array();
        $query = "UPDATE reembolso_solicitacao
                        SET id_status = 210
                        WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //3-GRAVANDO LOG DE STATUS - REPROVADO
        $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,?,210,2,GETDATE())";
        $params = array($id_format_reembolso,$s_usuario,$id_status);
        $resul = sqlsrv_query($this->link, $query, $params);


//        if(($errors=sqlsrv_errors())!= null){
//            print_r($errors);
//        }
        //$obj = new aprovacao();
        //return $obj->getReembolsoAprovador($s_usuario);
    }

    function setReembolsoStatusAprovadorNegado($s_usuario,$id_format_reembolso) {
        //1-SELECIONA STATUS ANTERIOR
        $query = "SELECT rs.id_status
                    FROM reembolso_solicitacao AS rs
                    WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_status_old = $linha->id_status;

        //2-SETA STATUS PARA ANALISE DE REVISAO
        $retorno = array();
        $query = "UPDATE reembolso_solicitacao
                        SET id_status = 110
                        WHERE id_format = ?";
        $params = array($id_format_reembolso);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);

        //3-GRAVANDO LOG DE STATUS - ANALISE DE REVISAO
        $query = "INSERT INTO log_reembolso_acao(id_reembolso
                                                        ,id_usuario
                                                        ,status_de
                                                        ,status_para
                                                        ,tipo
                                                        ,data)
                          VALUES(?,?,?,110,2,GETDATE())";
        $params = array($id_format_reembolso,$s_usuario,$id_status_old);
        $resul = sqlsrv_query($this->link, $query, $params);

//        if(($errors=sqlsrv_errors())!= null){
//            print_r($errors);
//        }
        //$obj = new aprovacao();
        //return $obj->getReembolsoAprovador($s_usuario);
    }

    function setReembolsoStatusAprovadorRetomado($s_usuario,$id_grupo,$id_usuario, $id_format){
        //1-SELECIONA STATUS ANTERIOR
        $query = "SELECT rau.ordem
                    FROM reembolso_aprovador_usuario AS rau
                    WHERE rau.id_grupo =? AND rau.id_usuario=?";
        $params = array($id_grupo,$id_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $ordem = $linha->ordem;
        $retomada = intval($ordem-1);

        $query = "UPDATE reembolso_solicitacao
                        SET id_status = ?
                        WHERE id_format = ?";
        $params = array($retomada,$id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        if($linha = sqlsrv_fetch_object($resul)){
            return 1;
        }else{
            return 0;
        }
    }

    function getData($s_usuario){
        $retorno = array();
        $query = "DECLARE @D_DATE INT
                    DECLARE @M_DATE INT
                    DECLARE @Y_DATE INT
                    SET @D_DATE = DAY (GETDATE())
                    SET @M_DATE = MONTH (GETDATE())
                    SET @Y_DATE = YEAR (GETDATE())
                    SELECT CONCAT(@D_DATE,'-',@M_DATE,'-',@Y_DATE) AS data";
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

    //GET INFORMACOES PARA PAINEL DE INFORMACOES DE GRUPO DO APROVADOR
    function getReembolsoAprovadorInfoGrupos($s_usuario,$arrUsuarios)
    {
        $retorno = array();
        $queryP1 = "DELETE FROM temp_usuario; \n";
        $params = array();
        $resul1 = sqlsrv_query($this->link, $queryP1, $params);
        $queryP2='';
        foreach ($arrUsuarios as $user) {
            $queryP2=$queryP2."INSERT INTO temp_usuario (id_temp) VALUES (".$user."); \n";
        }
        $resul2 = sqlsrv_query($this->link, $queryP2, $params);
        $queryP3="SELECT COUNT(gr.id) AS num
                          ,gr.nome AS grupo
                    FROM usuario AS us
                    LEFT JOIN temp_usuario AS tmp ON tmp.id_temp = us.id
                    LEFT JOIN reembolso_aprovador_grupo AS gr ON gr.id = us.id_grupo
                    WHERE us.id IN ( SELECT tmp.id_temp FROM temp_usuario AS tmp )
                    GROUP BY gr.descricao;";
        $params = array();
        $resul3 = sqlsrv_query($this->link, $queryP3, $params);
        while ($linha = sqlsrv_fetch_object($resul3)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    //PERMISSAO REVISAO DE APROVACAO
    function getPermissaoRevisao($s_usuario){
        $retorno = array();
        $query = "SELECT rau.id_usuario
                    FROM reembolso_aprovador_usuario rau
                    WHERE rau.id_usuario=? AND rau.ordem=1 ";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        if ($linha = sqlsrv_fetch_object($resul)) {
            return 1;
        } else {
            return 0;
        }
    }

    //SET MENSAGEM REEMBOLSO - NEGADO
    function setMensagemNegado($s_usuario,$id_format,$mensagem,$usuario_de,$usuario_para) {
        //1- INSERT MENSAGEM DO REEMBOLSO
        $query = "INSERT INTO reembolso_mensagem(id_reembolso,mensagem,tipo,id_usuario,status,data)
                  OUTPUT inserted.id, inserted.data
                  VALUES(?,?,2,?,110,GETDATE())";
        $params = array($id_format,$mensagem,$s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_msg = $linha->id;
        $dt_msg = $linha->data;

        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data)
                          VALUES(?,?,?,?,2,110,?)";
        $params = array($id_msg,$id_format,$usuario_de,$usuario_para, $dt_msg);
        $resul = sqlsrv_query($this->link, $query, $params);




//        if ($linha = sqlsrv_fetch_object($resul)) {
//            return 1;
//        }else{
//            return 0;
//        }
    }

    //SET MENSAGEM REEMBOLSO - RETOMADO
    function setMensagemRetomado($s_usuario,$id_format,$mensagem,$usuario_de,$usuario_para) {
        //1- INSERT MENSAGEM DO REEMBOLSO
        $query = "INSERT INTO reembolso_mensagem(id_reembolso,mensagem,tipo,id_usuario,status,data)
                  OUTPUT inserted.id, inserted.data
                  VALUES(?,?,2,?,105,GETDATE())";
        $params = array($id_format,$mensagem,$s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_msg = $linha->id;
        $dt_msg = $linha->data;

        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data)
                          VALUES(?,?,?,?,2,105,?)";
        $params = array($id_msg,$id_format,$usuario_de,$usuario_para, $dt_msg);
        $resul = sqlsrv_query($this->link, $query, $params);




//        if ($linha = sqlsrv_fetch_object($resul)) {
//            return 1;
//        }else{
//            return 0;
//        }
    }

    //SET MENSAGEM REEMBOLSO - REVISAO
    function setMensagemRevisao($s_usuario,$id_format,$mensagem,$usuario_de,$usuario_para) {
        //1- INSERT MENSAGEM DO REEMBOLSO
        $query = "INSERT INTO reembolso_mensagem(id_reembolso,mensagem,tipo,id_usuario,status,data)
                  OUTPUT inserted.id, inserted.data                       
                  VALUES(?,?,1,?,210,GETDATE())";
        $params = array($id_format,$mensagem, $s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_msg = $linha->id;
        $dt_msg = $linha->data;

        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data)
                          VALUES(?,?,?,?,1,210,?)";
        $params = array($id_msg,$id_format,$usuario_de,$usuario_para,$dt_msg);
        $resul = sqlsrv_query($this->link, $query, $params);

//        if ($linha = sqlsrv_fetch_object($resul)) {
//            return 1;
//        }else{
//            return 0;
//        }
    }

    //SET MENSAGEM REEMBOLSO - REPROVADO
    function setMensagemReprovado($s_usuario,$id_format,$mensagem,$usuario_de,$usuario_para) {
        $query = "INSERT INTO reembolso_mensagem(id_reembolso,mensagem,tipo,id_usuario,status,data)
                   OUTPUT inserted.id, inserted.data                     
                  VALUES(?,?,1,?,200,GETDATE())";
        $params = array($id_format,$mensagem, $s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_msg = $linha->id;
        $dt_msg = $linha->data;

        //2-GRAVANDO LOG DE EMAIL
        $query = "INSERT INTO log_email(id_msg,id_contexto,id_usuario_de,id_usuario_para,tipo,status,data)
                          VALUES(?,?,?,?,1,200,?)";
        $params = array($id_msg,$id_format,$usuario_de,$usuario_para,$dt_msg);
        $resul = sqlsrv_query($this->link, $query, $params);
//        if ($linha = sqlsrv_fetch_object($resul)) {
//            return 1;
//        }else{
//            return 0;
//        }
    }

    //GET MENSAGEM REEMBOLSO - NEGADO
    function getReembolsoMensagemNegado($s_usuario,$id_format){
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
                    WHERE id_reembolso=? AND tipo=2 AND status=110
                    ORDER BY msg.data DESC";
        $params = array($id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    //GET MENSAGEM REEMBOLSO - REPROVADO
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

    // GET INFORMACOES DO USUARIO E APROVADOR PARA ENVIO DE EMAIL - STATUS REVISAO 210
    function getReembolsoInfoParaMensagemRevisao($s_usuario,$id_format){
        $retorno = array();
        $query = "SELECT u.id AS id_para
                        ,u.nome AS nome_para
                        ,u.sobrenome AS sobrenome_para
                        ,u.usuario  AS email_para
                        ,(SELECT u1.id
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS id_de
                        ,(SELECT u1.nome
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS nome_de
                         ,(SELECT u1.sobrenome
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS sobrenome_de 
                        ,(SELECT u1.usuario
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS email_de                
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id= rs.id_usuario
                    LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                    WHERE id_format = ?";
        $params = array($s_usuario,$s_usuario,$s_usuario,$s_usuario,$id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    // GET INFORMACOES DO USUARIO E APROVADOR PARA ENVIO DE EMAIL - STATUS NEGADO 110
    function getReembolsoInfoParaMensagemNegado($s_usuario,$id_format){
        $retorno = array();
        $query = "SELECT 
                        (SELECT u1.id
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS id_de
                        ,(SELECT u2.nome
                          FROM usuario AS u2
                          WHERE u2.id = ? )AS nome_de
                        ,(SELECT u3.sobrenome
                          FROM usuario AS u3
                          WHERE u3.id = ? )AS sobrenome_de
                        ,(SELECT u4.usuario
                          FROM usuario AS u4
                          WHERE u4.id = ? )AS email_de 
                        ,(SELECT au1.id_usuario FROM reembolso_aprovador_usuario AS au1
                          WHERE au1.id_grupo = u.id_grupo
                          AND au1.ordem=1)AS id_para
                        ,(SELECT usr.nome FROM reembolso_aprovador_usuario AS grp
                          LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario
                          WHERE grp.id_grupo = u.id_grupo AND grp.ordem =1) AS nome_para
                          ,(SELECT usr.sobrenome FROM reembolso_aprovador_usuario AS grp
                          LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario
                          WHERE grp.id_grupo = u.id_grupo AND grp.ordem =1) AS sobrenome_para
                        ,(SELECT usr.usuario FROM reembolso_aprovador_usuario AS grp
                          LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario
                        WHERE grp.id_grupo = u.id_grupo AND grp.ordem =1) AS email_para            
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id= rs.id_usuario
                    LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                    WHERE id_format = ?";
        $params = array($s_usuario,$s_usuario,$s_usuario,$s_usuario,$id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    // GET INFORMACOES DO USUARIO E APROVADOR PARA ENVIO DE EMAIL - STATUS REPROVADO 200
    function getReembolsoInfoParaMensagemReprovado($s_usuario,$id_format){
        $retorno = array();
        $query = "SELECT u.id AS id_para
                        ,u.nome AS nome_para
                        ,u.sobrenome AS sobrenome_para
                        ,u.usuario  AS email_para
                        ,(SELECT u1.id
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS id_de
                        ,(SELECT u1.nome
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS nome_de
                         ,(SELECT u1.sobrenome
                          FROM usuario AS u1
                          WHERE u1.id = ? )AS sobrenome_de
                        ,(SELECT u1.usuario FROM usuario AS u1
                         WHERE u1.id = ?) AS email_de             
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id= rs.id_usuario
                    LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                    WHERE id_format = ?";
        $params = array($s_usuario,$s_usuario,$s_usuario,$s_usuario,$id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    // GET INFORMACOES DO USUARIO E APROVADOR PARA ENVIO DE EMAIL - STATUS REPROVADO 200
    function getReembolsoInfoParaMensagemRetomado($s_usuario,$id_format){
        $retorno = array();
        $query = "SELECT
                         TOP 1
                         u2.id AS id_para
                        ,u2.nome AS nome_para
                        ,u2.sobrenome AS sobrenome_para
                        ,u2.usuario AS email_para
                        ,u1.id AS id_de
                        ,u1.nome AS nome_de
                        ,u1.sobrenome AS sobrenome_de
                        ,u1.usuario AS email_de
                FROM usuario AS u2
                LEFT JOIN usuario AS u1 ON u1.id = ?
                LEFT JOIN reembolso_mensagem AS m ON m.id_usuario = u2.id
                WHERE m.id_reembolso = ?
                AND tipo=2 AND status=110
                ORDER BY m.data DESC";
        $params = array($s_usuario,$id_format);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }


    //GET DADOS PARA EMAILS
    function getDadosEmailReembolsoAvaliaAprovacao($s_usuario,$id_formtat) {
        $query = "SELECT guia.fim_aprov
                         ,rs.id_status
                FROM reembolso_guia_aprovador AS guia
                LEFT JOIN reembolso_solicitacao AS rs ON rs.id_format = guia.id_reeembolso
                WHERE id_reeembolso = ? ";
        $params = array($id_formtat);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDadosEmailReembolsoProximoAprovador($s_usuario,$id_formtat) {
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
                  WHERE grp.id_grupo = u.id_grupo AND grp.ordem =rs.id_status+1) AS nome_para
                  ,(SELECT usr.usuario 
                  FROM reembolso_aprovador_usuario AS grp 
                  LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario 
                  WHERE grp.id_grupo = u.id_grupo AND grp.ordem =rs.id_status+1) AS email_para
                  
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

    function getDadosEmailReembolsoAprovado($s_usuario,$id_formtat) {
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
                      ,u.nome AS nome_para
                      ,u.usuario AS email_para
                      ,(SELECT TOP 1 LEFT(CONVERT(VARCHAR, log.data, 105), 10) AS dt FROM log_reembolso_acao AS log WHERE log.id_reembolso=rs.id_format AND log.status_para=100 ORDER BY log.data DESC) AS aprovacao
                      ,(SELECT cfg.dataLimite FROM configuracao_reembolso AS cfg WHERE cfg.id=5)AS pagamento
                      FROM reembolso_solicitacao AS rs
                        LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                        LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                        LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        WHERE rs.id_format = ?";
        $params = array($id_formtat);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDadosEmailReembolsoNegado($s_usuario,$id_formtat) {
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
                    ,(SELECT usr.nome FROM usuario AS usr WHERE usr.id = ?) AS nome_de
                    ,(SELECT usr.usuario FROM  usuario AS usr WHERE usr.id = ?) AS email_de
                    ,(SELECT usr.nome FROM reembolso_aprovador_usuario AS grp
                    LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario
                    WHERE grp.id_grupo = u.id_grupo AND grp.ordem =1) AS nome_para
                    ,(SELECT usr.usuario FROM reembolso_aprovador_usuario AS grp
                    LEFT JOIN usuario AS usr ON usr.id= grp.id_usuario
                    WHERE grp.id_grupo = u.id_grupo AND grp.ordem =1) AS email_para
                    
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                    LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                    LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                    WHERE rs.id_format = ?";
        $params = array($s_usuario,$s_usuario,$id_formtat);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDadosEmailReembolsoRetomado($s_usuario, $id_grupo, $id_para, $id_formtat) {
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
                    ,(SELECT usr.nome FROM usuario AS usr WHERE usr.id = ?) AS nome_de
                    ,(SELECT usr.usuario FROM  usuario AS usr WHERE usr.id = ?) AS email_de
                    ,(SELECT u2.nome FROM reembolso_aprovador_usuario AS rau LEFT JOIN usuario AS u2 ON u2.id=rau.id_usuario WHERE  rau.id_usuario =? AND rau.id_grupo =?) AS nome_para
                    ,(SELECT u2.usuario FROM reembolso_aprovador_usuario AS rau LEFT JOIN usuario AS u2 ON u2.id=rau.id_usuario WHERE  rau.id_usuario =? AND rau.id_grupo =?) AS email_para
                    ,(SELECT TOP 1 LEFT(CONVERT(VARCHAR, msg.data, 105), 10) FROM reembolso_mensagem AS msg  WHERE id_reembolso=? AND msg.tipo=2 AND msg.status=110 ORDER BY msg.data DESC) AS negado
                    FROM reembolso_solicitacao AS rs
                    LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                    LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                    LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                    LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                    WHERE rs.id_format = ?";
        $params = array($s_usuario,$s_usuario,$id_para,$id_grupo,$id_para,$id_grupo,$id_formtat,$id_formtat);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDadosEmailReembolsoReprovado($s_usuario,$id_formtat) {
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
                      ,u.nome AS nome_para
                      ,u.usuario AS email_para
                      ,(SELECT usr.nome FROM usuario AS usr WHERE usr.id = ?) AS nome_de
                      ,(SELECT usr.usuario FROM  usuario AS usr WHERE usr.id = ?) AS email_de
                        FROM reembolso_solicitacao AS rs
                        LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                        LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                        LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        WHERE rs.id_format = ?";
        $params = array($s_usuario,$s_usuario,$id_formtat);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

    function getDadosEmailReembolsoRevisao($s_usuario,$id_formtat) {
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
                      ,u.nome AS nome_para
                      ,u.usuario AS email_para
                      ,(SELECT usr.nome FROM usuario AS usr WHERE usr.id = ?) AS nome_de
                      ,(SELECT usr.usuario FROM  usuario AS usr WHERE usr.id = ?) AS email_de
                        FROM reembolso_solicitacao AS rs
                        LEFT JOIN usuario AS u ON u.id = rs.id_usuario
                        LEFT JOIN vwEmpresas AS e ON dbo.fRemoveZeros(e.Cod_Empresa,0)= rs.id_empresa
                        LEFT JOIN reembolso_tipo_despesa AS d ON d.id = rs.id_tipo_despesa
                        LEFT JOIN reembolso_aprovador_grupo AS g ON g.id = u.id_grupo
                        WHERE rs.id_format = ?";
        $params = array($s_usuario,$s_usuario,$id_formtat);
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $retorno[] = $linha;
        }
        return $retorno;
    }

}

?>