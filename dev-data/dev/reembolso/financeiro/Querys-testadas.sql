/****** Script for SelectTopNRows command from SSMS  ******/
USE [AMR_P00_PRODUCAO]

--LIBERACAO DE TITULOS
UPDATE P12_PRODUCAO.dbo.SE2010
SET E2_XPRTLIB = '1'
WHERE E2_NUM = '000000301'

--RD1012
--SELECT TITULOS PARA APROVAR
SELECT *
  FROM dbo.processo_vwAprovFinTitulosParaAprovar AS vw_T
  ORDER BY vw_T.status,vw_T.vencimento DESC

--SELECT FILTRO TITULO PARA APROVAR
SELECT *
  FROM dbo.processo_vwAprovFinTitulosParaAprovar AS vw_T
  WHERE vw_T.codigo='01   000000060 NF 00041101'
  ORDER BY vw_T.status,vw_T.vencimento DESC

--SELECT TITULOS PARA APROVADOS
SELECT *
  FROM dbo.processo_vwAprovFinTitulosAprovados AS vw_T
  ORDER BY vw_T.vencimento DESC

--SELECT ITENS DO TITULO
SELECT *
  FROM dbo.processo_vwAprovFinTitulosItens AS vw_I
  WHERE vw_I.codigo='01   000000060 NF 00041101'
  ORDER BY vw_I.item DESC


--SELECT DOCUMENTO DO TITULO  teste-1
SELECT *
  FROM dbo.processo_vwAprovFinTitulosDocumentos AS vw_D
  WHERE vw_D.E2_NUM='000000060'


--SELECT DOCUMENTO DO TITULO
SELECT *
	 FROM dbo.processo_vwAprovFinTitulosParaAprovar AS vw_T
	 LEFT JOIN dbo.processo_vwAprovFinTituloDocumentos AS vw_D ON vw_D.numero=vw_T.E2_NUM

--SELECT DOCUMENTO DO TITULO  teste-2
SELECT DISTINCT *
  FROM dbo.processo_vwAprovFinTituloDocumentos AS vw_D
  WHERE vw_D.numero='000000060'



--SET STATUS DE TITULO - SOLICITAR MAIS DETALHES = PARA ANALISE
UPDATE P12_PRODUCAO.dbo.SE2010
SET E2_XPRTLIB = '2'
   ,E2_DATALIB = ''
WHERE E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = '011  000000045 NF 00125301'
AND E2_XPRTLIB='1'


--SET STATUS DE TITULO - APROVAR = APROVADOS
UPDATE [P12_PRODUCAO].dbo.SE2010
SET E2_XPRTLIB = '3'
   ,E2_DATALIB = CONVERT(varchar, GETDATE() ,112)
WHERE E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = '011  000000045 NF 00125301'



-- SELECT HISTORICO
Select DISTINCT Top 1000 
		--SUM(Total) Valor,
	    --Count(Item) QtdItens,
		
		LEFT(CONVERT(VARCHAR,CONVERT(DATE,vw_H.Emissao), 105), 10) AS Emissao
	    ,FORMAT (vw_H.Total, 'c', 'pt-br')  AS valor
		--, Numero
		,*
	    From dbo.processo_vwAprovHistorico AS vw_H
	    --Where Codigo + Loja = '000411'+'01'
	    --Group By Emissao, Numero
		WHERE CONVERT(DATE,vw_H.Emissao) < GETDATE()
		AND Codigo + Loja = '001227'+'01'
	    Order by vw_H.Emissao Desc  
 

DECLARE @VALOR decimal(18,2)
    SET @VALOR = (SELECT CAST(CAST(SUM(af.valor) AS DECIMAL(18,2)) AS valor FROM processo_vwAprovFinTitulosParaAprovar af)
    SELECT
       COUNT(af.codigo)AS num
       ,CONVERT(VARCHAR(10), MAX(af.vencimento), 105) AS dataAte
       ,CONVERT(VARCHAR(10), MIN(af.vencimento), 105) AS dataDe
       --FORMAT (@VALOR, 'c', 'pt-br') AS total
       FROM dbo.processo_vwAprovFinTitulosParaAprovar af


SELECT
    af.valor  AS valor
FROM processo_vwAprovFinTitulosParaAprovar af




SELECT codigo
      ,FORMAT (vw_T.valor, 'c', 'pt-br')  AS valor
      ,*
FROM dbo.processo_vwAprovFinTitulosParaAprovar AS vw_T
ORDER BY vw_T.status,vw_T.vencimento DESC



SELECT codigo
      ,tipo
      ,FORMAT (vw_T.valor, 'c', 'pt-br')  AS valor
      ,beneficiario
      ,pagadora
      ,emissao
      ,vencimento
      ,observacao
      ,status
      ,selecao
FROM dbo.processo_vwAprovFinTitulosParaAprovar AS vw_T
ORDER BY vw_T.status,vw_T.vencimento DESC


DECLARE @VALOR decimal(18,2)
SET @VALOR = (SELECT CAST(CAST(SUM(af.valor) AS DECIMAL(18,2)) AS VARCHAR(30))AS valor FROM dbo.processo_vwAprovFinTitulosParaAprovar af)
SELECT
    COUNT(af.codigo)AS num
   ,CONVERT(VARCHAR(10), MAX(af.vencimento), 105) AS dataAte
   ,CONVERT(VARCHAR(10), MIN(af.vencimento), 105) AS dataDe
   ,FORMAT (SUM(CAST(af.valor AS DECIMAL(18,2))), 'c', 'pt-br') AS valTotal
   ,FORMAT (MAX(CAST(af.valor AS DECIMAL(10, 4))), 'c', 'pt-br') AS valMax
   ,FORMAT (MIN(CAST(af.valor AS DECIMAL(10, 4))), 'c', 'pt-br') AS valMin
   FROM dbo.processo_vwAprovFinTitulosParaAprovar af


SELECT
       FORMAT (MAX(CAST(af.valor AS DECIMAL(10, 4))), 'c', 'pt-br') AS valMax
       ,FORMAT (MAX(CAST(af.valor AS DECIMAL(10, 4))), 'c', 'pt-br') AS valMin
       FROM dbo.processo_vwAprovFinTitulosParaAprovar af

DECLARE @VALOR decimal(18,2)

SELECT COUNT(af.codigo) AS numTitulos
          ,af.pagadora
          ,FORMAT (SUM(CAST(af.valor AS DECIMAL(18,2))), 'c', 'pt-br') AS totalPagadora
          FROM dbo.processo_vwAprovFinTitulosParaAprovar af
          GROUP BY af.pagadora

SELECT COUNT(af.codigo) AS numTitulos
          ,af.tipo
          ,FORMAT (SUM(CAST(af.valor AS DECIMAL(18,2))), 'c', 'pt-br') AS totalTipo
          FROM dbo.processo_vwAprovFinTitulosParaAprovar af
          GROUP BY af.tipo



