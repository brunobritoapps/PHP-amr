
--cabecAprovacao= ["Codigo","Tipo", "Valor R$", "Beneficiário","Pagadora","Vencimento","Observação"];
--cabecItens = ["Item","Codigo","Natureza","Valor R$"];

--cabecHistorico = ["Data","Tipo","Beneficiario","Valor"];

USE[P12_PRODUCAO]

--LIBERACAO DE TITULOS
UPDATE SE2010
SET E2_XPRTLIB = '1'
WHERE E2_NUM = '000000301'
--000000301
--000007948
--RD1012

--TITULOS A APROVAR
--cabecAprovacao= ["Codigo","Tipo", "Valor R$", "Beneficiário","Pagadora","Vencimento","Observação"];
SELECT
	 E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA codigo
	,'FI ' AS tipo 
	,FORMAT (SE2.E2_VALOR, 'c', 'pt-br')  AS valor
	,SA2.A2_NOME AS beneficiario
	,REPLACE(vw_E.Nome_Empresa COLLATE Latin1_General_BIN, 'AMERICA', '' ) AS pagadora
	,LEFT(CONVERT(VARCHAR,CONVERT(DATE,SE2.E2_EMIS1), 105), 10) AS emissao
	,LEFT(CONVERT(VARCHAR,CONVERT(DATE,SE2.E2_VENCREA), 105), 10) AS vencimento
	,SE2.E2_HIST AS observacao
	,(CASE  SE2.E2_XPRTLIB WHEN '1' THEN 'PARA APROVAÇÃO' WHEN '2' THEN 'AGUARDANDO MAIS DETALHES' END) AS status
	FROM P12_PRODUCAO..SE2010 AS SE2
	JOIN P12_PRODUCAO..SA2010 SA2 ON A2_FILIAL=' ' AND A2_COD=E2_FORNECE AND A2_LOJA=E2_LOJA AND SA2.D_E_L_E_T_=' '
	JOIN AMR_P00_PRODUCAO.dbo.corporativo_vwEmpresas AS vw_E ON vw_E.Cod_Empresa+Cod_Filial='01'+E2_FILIAL
	WHERE 
	SE2.E2_SALDO>0
	AND SE2.D_E_L_E_T_=' '
	AND E2_DATALIB=''
	AND E2_XPRTLIB IN ('1','2')
	--AND E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = '0000309972TX UNIAO 00'
	ORDER BY SE2.E2_VENCREA DESC


--APROVACAO DE TITULOS
UPDATE SE2010
SET E2_XPRTLIB = '3'
   ,E2_DATALIB = CONVERT(varchar, GETDATE() ,112)
WHERE E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = '014  000201062 NF 00062501'
--01   000446492 NF 00114802
--01   0000309972TX UNIAO 00
--01   012019    FOL00067501
--014  000201062 NF 00062501

--TITULOS APROVADOS
SELECT
	 E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA codigo
	,'FI ' AS tipo 
	,FORMAT (SE2.E2_VALOR, 'c', 'pt-br')  AS valor
	,SA2.A2_NOME AS beneficiario
	,REPLACE(vw_E.Nome_Empresa COLLATE Latin1_General_BIN, 'AMERICA', '' ) AS pagadora
	,LEFT(CONVERT(VARCHAR,CONVERT(DATE,SE2.E2_EMIS1), 105), 10) AS emissao
	,LEFT(CONVERT(VARCHAR,CONVERT(DATE,SE2.E2_VENCREA), 105), 10) AS vencimento
	,SE2.E2_HIST AS observacao
	FROM SE2010 AS SE2
	JOIN P12_PRODUCAO..SA2010 SA2 ON A2_FILIAL=' ' AND A2_COD=E2_FORNECE AND A2_LOJA=E2_LOJA AND SA2.D_E_L_E_T_=' '
	JOIN AMR_P00_PRODUCAO.dbo.corporativo_vwEmpresas AS vw_E ON vw_E.Cod_Empresa+Cod_Filial='01'+E2_FILIAL
	WHERE 
	SE2.D_E_L_E_T_=' '
	AND E2_DATALIB<>''
	AND E2_XPRTLIB='3'
	--AND E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = '01   000446492 NF 00114802'
	ORDER BY SE2.E2_VENCREA DESC





--SET STATUS DE TITULO - SOLICITAR MAIS DETALHES = EM ANALISE
UPDATE SE2010
SET E2_XPRTLIB = '2'
WHERE E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA IN( '011  000000332 NF 00020801','01   000000060 NF 00041101','01   000003671 NF 00006001','011  000000045 NF 00125301')
--AND E2_XPRTLIB='1'
--011  000000332 NF 00020801
--01   000000060 NF 00041101
--01   000003671 NF 00006001
--011  000000045 NF 00125301

--TITULOS APROVADOS
SELECT
	 E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA codigo
	,'FI ' AS tipo 
	,FORMAT (SE2.E2_VALOR, 'c', 'pt-br')  AS valor
	,SA2.A2_NOME AS beneficiario
	,REPLACE(vw_E.Nome_Empresa COLLATE Latin1_General_BIN, 'AMERICA', '' ) AS pagadora
	,LEFT(CONVERT(VARCHAR,CONVERT(DATE,SE2.E2_EMIS1), 105), 10) AS emissao
	,LEFT(CONVERT(VARCHAR,CONVERT(DATE,SE2.E2_VENCREA), 105), 10) AS vencimento
	,SE2.E2_HIST AS observacao
	FROM SE2010 AS SE2
	JOIN P12_PRODUCAO..SA2010 SA2 ON A2_FILIAL=' ' AND A2_COD=E2_FORNECE AND A2_LOJA=E2_LOJA AND SA2.D_E_L_E_T_=' '
	JOIN AMR_P00_PRODUCAO.dbo.corporativo_vwEmpresas AS vw_E ON vw_E.Cod_Empresa+Cod_Filial='01'+E2_FILIAL
	WHERE 
	SE2.D_E_L_E_T_=' '
	AND E2_DATALIB<>''
	AND E2_XPRTLIB='3'
	--AND E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA = '01   000446492 NF 00114802'
	ORDER BY SE2.E2_VENCREA DESC


--ITENS DO TITULO 

--cabecItens = ["Item","Codigo","Produto","Valor R$"];
SELECT
	 SD1.D1_ITEM AS item
	,E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA AS codigo
	,SB1.B1_DESC AS produto
	,FORMAT (D1_TOTAL - D1_VALDESC, 'c', 'pt-br')  AS valor
FROM P12_PRODUCAO..SE2010 AS SE2
JOIN P12_PRODUCAO..SA2010 SA2 ON A2_FILIAL=' ' AND A2_COD=E2_FORNECE AND A2_LOJA=E2_LOJA AND SA2.D_E_L_E_T_=' '
LEFT JOIN P12_PRODUCAO..SF1010 AS SF1 ON F1_FILIAL=E2_FILIAL AND F1_DOC=E2_NUM AND F1_SERIE=E2_PREFIXO AND F1_FORNECE=E2_FORNECE AND F1_LOJA=E2_LOJA AND SF1.D_E_L_E_T_=' '
LEFT JOIN P12_PRODUCAO..SD1010 AS SD1 ON D1_FILIAL=F1_FILIAL AND D1_DOC=F1_DOC AND D1_SERIE=D1_SERIE AND D1_FORNECE=F1_FORNECE AND D1_LOJA=F1_LOJA AND SD1.D_E_L_E_T_=' '
LEFT JOIN P12_PRODUCAO..SB1010 AS SB1 ON B1_FILIAL=' ' AND B1_COD=D1_COD AND SB1.D_E_L_E_T_=' '
WHERE E2_FILIAL BETWEEN '01' AND '02'
AND E2_SALDO > 0
AND SE2.D_E_L_E_T_=' '
AND E2_DATALIB=' '
--AND E2_FILIAL+E2_PREFIXO+E2_NUM+E2_PARCELA+E2_TIPO+E2_FORNECE+E2_LOJA='01   000097335 NF 00034701'
ORDER BY SD1.D1_ITEM


--DOCUMENTOS
--cabecDocumentos = ["Item","Codigo","Documento","Download","View"];




















