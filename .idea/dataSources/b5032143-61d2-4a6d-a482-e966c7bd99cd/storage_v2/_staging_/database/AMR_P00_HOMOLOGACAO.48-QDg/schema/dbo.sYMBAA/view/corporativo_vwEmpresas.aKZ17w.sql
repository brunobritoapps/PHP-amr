CREATE VIEW [dbo].[vwEmpresas] AS
SELECT  dbo.fRemoveZeros(M0_CODIGO,0) AS Cod_Empresa,
        M0_NOME AS Nome_Empresa,
        M0_CODFIL AS Cod_Filial,
        M0_FILIAL AS Nome_Filial
FROM P12_PRODUCAO..SM0010
go

