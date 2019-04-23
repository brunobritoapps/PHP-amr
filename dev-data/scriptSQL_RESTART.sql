USE[AMR_P00_PRODUCAO]

--COM IDENTITY
DELETE FROM usuario
DBCC CHECKIDENT('[usuario]', RESEED, 0)

DELETE FROM reembolso_solicitacao
DBCC CHECKIDENT('[reembolso_solicitacao]', RESEED, 0)

DELETE FROM log_reembolso_acao
DBCC CHECKIDENT('[log_reembolso_acao]', RESEED, 0)

DELETE FROM log_financeiro_acao
DBCC CHECKIDENT('[log_financeiro_acao]', RESEED, 0)

DELETE FROM log_servicos
DBCC CHECKIDENT('[log_servicos]', RESEED, 0)

DELETE FROM financeiro_mensagem
DBCC CHECKIDENT('[financeiro_mensagem]', RESEED, 0)

DELETE FROM log_email
DBCC CHECKIDENT('[log_email]', RESEED, 0)

DELETE FROM reembolso_aprovador_grupo
DBCC CHECKIDENT('[reembolso_aprovador_grupo]', RESEED, 0)

DELETE FROM colaborador_documento
DBCC CHECKIDENT('[colaborador_documento]', RESEED, 0)

--SEM IDENTITY
DELETE FROM reembolso_aprovador_usuario
DELETE FROM reembolso_itens

INSERT INTO usuario (usuario,nome, conta, senha, ativo, sobrenome, cpf, id_grupo, data_ativacao,data_registro)
VALUES ('loopconsultoria.brito@gmail.com','Master',1,'40bd001563085fc35165329ea1ff5c5ecbdbbeef',1,'Admin','00000000000',1,LEFT(CONVERT(VARCHAR, GETDATE(), 105), 10),LEFT(CONVERT(VARCHAR, GETDATE(), 105), 10))

SELECT * FROM usuario


SELECT u.id
      ,u.usuario
      ,u.nome
      ,u.sobrenome
      ,u.cpf
      ,c.nome AS tipo_conta
      ,u.conta
       ,(SELECT TOP 1 Nome_Empresa
       FROM dbo.usuario_ccusto AS ucc
       LEFT JOIN dbo.vwEmpresas ON Cod_Empresa = SUBSTRING (ucc.id_ccusto,1,1)
       WHERE ucc.id_usuario=u.id)AS empresa

       ,(SELECT TOP 1 ucc.id_ccusto
       FROM dbo.usuario_ccusto AS ucc
       WHERE ucc.id_usuario=u.id)AS id_ccusto

       ,(SELECT TOP 1 cc.Descricao
       FROM dbo.usuario_ccusto AS ucc
       LEFT JOIN corporativo_vwCcustos AS cc ON  cc.Codigo = ucc.id_ccusto
       WHERE ucc.id_usuario=u.id)AS ccusto

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=1)AS dir_prt1

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=2)AS dir_prt2

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=3)AS dir_prt3

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=4)AS dir_emp1

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=5)AS dir_emp2

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=6)AS dir_emp3

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=7)AS dir_emp4

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=8)AS dir_emp5

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=9)AS dir_emp6

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=10)AS dir_emp7

       ,(SELECT cd.diretorio
       FROM dbo.configuracao_geral_diretorio AS cd
       WHERE cd.id=11)AS dir_emp8

        ,(SELECT cr.dataLimite
       FROM dbo.configuracao_reembolso AS cr
       WHERE cr.id=1)AS dt_lim

      FROM usuario AS u
      LEFT JOIN dbo.contas AS c ON c.id = u.conta
WHERE u.usuario='loopconsultoria.brito@gmail.com' AND u.senha='40bd001563085fc35165329ea1ff5c5ecbdbbeef' AND u.ativo = 1