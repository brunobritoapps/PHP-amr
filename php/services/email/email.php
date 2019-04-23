<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 06-Jan-19
 * Time: 02:33 AM
 */

class email
{

    var $link;

    function __construct()
    {
        //error_reporting(E_ALL );
        if (file_exists('parametros.php')) {
            require_once('parametros.php');
        } else {
            require_once('..\parametros.php');
        }
        $this->link = conexao("base");
        require_once 'PHPMailer/class.phpmailer.php';
        require_once 'PHPMailer/class.smtp.php';
        require_once 'PHPMailer/PHPMailerAutoload.php';
        //
        require_once 'services/log/log.php';
        //
    }

    function enviaEmail($titulo, $corpo, $nomePara, $emailPara)
    {
        $emailExp = explode(';',$emailPara);
        //Enviando o e-mail utilizando a classe PHPMailer
        $Mailer = new PHPMailer(true);
        $Mailer->CharSet = "utf8";
        //$Mailer->SMTPDebug =3;
        $Mailer->isSMTP();
        $Mailer->Host = "smtp.americaenergia.com.br";
        $Mailer->SMTPAuth = true;
        $Mailer->Username = "noreply@americaenergia.com.br";
        $Mailer->Password = "america@123";
        $Mailer->SMTPAutoTLS = false;
        $Mailer->SMTPSecure = "";
        $Mailer->Port = "587";
        $Mailer->FromName = $nomePara;
        $Mailer->setFrom("noreply@americaenergia.com.br");
        foreach ($emailExp as $emailIterado){
            $Mailer->addAddress($emailIterado);
        }
        $Mailer->isHTML(true);
        $Mailer->Subject = $titulo;
        $Mailer->Body = $corpo;
        $Mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if (!$Mailer->send()) {
            //echo 'Email não pode ser enviado. Erro: ', $Mailer->ErrorInfo;
            return $Mailer->ErrorInfo;
        } else {
            //echo 'Message foi enviada com sucesso';
            return 1;
        }
    }

    function enviaEmailCopia($titulo, $corpo, $nomePara, $emailPara,$nomeDe, $emailDe)
    {
        //Enviando o e-mail utilizando a classe PHPMailer
        $Mailer = new PHPMailer(true);
        $Mailer->CharSet = "utf8";
        //$Mailer->SMTPDebug =3;
        $Mailer->isSMTP();
        $Mailer->Host = "smtp.americaenergia.com.br";
        $Mailer->SMTPAuth = true;
        $Mailer->Username = "noreply@americaenergia.com.br";
        $Mailer->Password = "america@123";
        $Mailer->SMTPAutoTLS = false;
        $Mailer->SMTPSecure = "";
        $Mailer->Port = "587";
        $Mailer->FromName = $nomePara;
        $Mailer->setFrom("noreply@americaenergia.com.br");
        $Mailer->addAddress($emailPara);
        $Mailer->AddCC($emailDe,$nomeDe);
        $Mailer->isHTML(true);
        $Mailer->Subject = $titulo;
        $Mailer->Body = $corpo;
        $Mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if (!$Mailer->send()) {
            //echo 'Email não pode ser enviado. Erro: ', $Mailer->ErrorInfo;
            return $Mailer->ErrorInfo;
        } else {
            //echo 'Message foi enviada com sucesso';
            return 1;
        }
    }

    function e00admUsuarioRegistro($s_usuario,$nome_usuario,$sobrenome_usuario,$email_usuario,$cpf_usuario,$arrAdm){
        $titulo = 'Notificação ao Administrador - O usuário ['.$email_usuario.'] foi registrado no portal';
        $cont=0;

        foreach ($arrAdm as $adm) {
            $corpo = file_get_contents("../pages/email/e00adm_usr_reg.html", true);
            $corpo = str_replace("[[NOME_PARA]]", $adm->nome_para, $corpo);
            $corpo = str_replace("[[NOME_USUARIO]]", $nome_usuario, $corpo);
            $corpo = str_replace("[[SOBRENOME_USUARIO]]", $sobrenome_usuario, $corpo);
            $corpo = str_replace("[[EMAIL_USUARIO]]", $email_usuario, $corpo);
            $corpo = str_replace("[[CPF]]", $cpf_usuario, $corpo);

            //ENVIANDO EMAIL
            $objEmail = new email();
            $valid_email = $objEmail->enviaEmail($titulo, $corpo, $adm->nome_para, $adm->email_para);

            //GRAVANDO LOG
            $objLog = new log();
            $valid_log=$objLog->logEmailRegistroAdm($s_usuario,$adm->id_para);

            if ($valid_email == 1 && $valid_log==1) {
                $cont++;
            }
        }
        if($cont==count($arrAdm)){
            return 1;
        }else{
            return 0;
        }
    }

    function e1cUsuarioRegistro($s_usuario, $nome_usuario, $sobrenome_usuario,$email_usuario, $cpf_usuario){
        $titulo='Notificação ao colaborador - Registro no Portal América' ;

        //1-ADD DADOS AO CORPO DO EMAIL
        $corpo = file_get_contents("../pages/email/e01c_usr_reg.html", true);
        $corpo = str_replace("[[NOME_PARA]]",$nome_usuario,$corpo);

        //2-ENVIANDO EMAIL PARA USUARIO
        $objEmail = new email();
        $valid_email_usuario = $objEmail->enviaEmail($titulo,$corpo,$nome_usuario,$email_usuario);

        //-ENVIANDO EMAIL PARA ADM
        $arrAdm = array();
        $query = "SELECT conf.id_usuario AS id_para
                        ,conf.nome AS nome_para
                        ,conf.email AS email_para
                      FROM configuracao_email_disparo AS conf
                      WHERE conf.tipo_disparo=500 AND id_permissao=1";
        $params = array();
        $resul = sqlsrv_query($this->link, $query, $params);
        while ($linha = sqlsrv_fetch_object($resul)) {
            $arrAdm[] = $linha;
        }
        $valid_email_adm=$objEmail->e00admUsuarioRegistro($s_usuario,$nome_usuario,$sobrenome_usuario,$email_usuario,$cpf_usuario,$arrAdm);


        if($valid_email_usuario==1 && $valid_email_adm==1 ){
            $valid_Sql = false;
            //1-SELECT USUARIO
            $query = "SELECT u.id
                      FROM usuario AS u
                      WHERE u.usuario=? AND u.ativo=2";
            $params = array($email_usuario);
            $resul = sqlsrv_query($this->link, $query, $params);
            if($linha1 = sqlsrv_fetch_object($resul)){
                $id_usuario = $linha1->id;
                $valid_Sql = true;
            }

            $objLog = new log();
            $valid_Log=$objLog->logEmailRegistro($s_usuario,$id_usuario);

            if($valid_Sql && $valid_Log){
                return 1;
            }else{
                return 0;
            }
        }

    }

    function e2cUsuarioAtivacao($s_usuario, $nome_para, $email_para,$id_usuario){
        $titulo = 'Notificação ao colaborador - Ativação de usuário no Portal América';

        //1-ADD DADOS AO CORPO DO EMAIL
        $corpo = file_get_contents("../pages/email/e02c_usr_atv.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);

        //2-ENVIANDO EMAIL
        $objEmail = new email();
        $valid_email = $objEmail->enviaEmail($titulo, $corpo, $nome_para, $email_para);

        //3-GRAVANDO LOG
        $objLog = new log();
        $valid_Log=$objLog->logEmailAtivacao($s_usuario,$id_usuario);


        if ($valid_email == 1 && $valid_Log==1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e3cAdicionadoGrupoAprovacao($s_usuario, $grupo, $id_departamento, $arrUsuario)
    {
        $titulo = 'Notificação ao colaborador - Participação de grupo de aprovação de reembolsos';
        //
        $aprovadores = '';
        foreach ($arrUsuario as $usuario) {
            $aprovadores .= '<tr>';
            $aprovadores .= '<td align="left">' . $usuario{'ordem'} . '</td>';
            $aprovadores .= '<td align="left">' . $usuario{'nomeSobrenome'} . '</td>';
            $aprovadores .= '<td align="left">' . $usuario{'alcada_de'} . '</td>';
            $aprovadores .= '<td align="left">' . $usuario{'alcada_ate'} . '</td>';
            $aprovadores .= '</tr>';
        }
        //SELECT  DEPARTAMENTO
        $query = "SELECT d.descricao
                    FROM corporativo_departamento AS d
                    WHERE id = ?";
        $params = array($id_departamento);
        $resul= sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $departamento = $linha->descricao;
        //
        //Add dados no corpo do email
        //
        $cont=0;
        foreach ($arrUsuario as $usuario) {
            //SELECT  DADOS DO USUARIO
            $query = "SELECT u.id,u.nome,u.sobrenome,u.usuario
                    FROM usuario AS u
                    WHERE id = ?";
            $params = array($usuario{'id'});
            $resul= sqlsrv_query($this->link, $query, $params);
            $linha = sqlsrv_fetch_object($resul);
            $id_para = $linha->id;
            $email_para = $linha->usuario;
            $nome_para = $linha->nome;
            $sobrenome_para = $linha->sobrenome;
            //
            $corpo = file_get_contents("../pages/email/e03c_cad_grp.html", true);
            $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
            $corpo = str_replace("[[SOBRENOME_PARA]]", $sobrenome_para, $corpo);
            $corpo = str_replace("[[GRUPO]]", $grupo, $corpo);
            $corpo = str_replace("[[DEPARTAMENTO]]", $departamento, $corpo);
            $corpo = str_replace("[[APROVADORES]]", $aprovadores, $corpo);

            //enviando email
            $objEmail = new email();
            $val1id_email = $objEmail->enviaEmail($titulo, $corpo, $nome_para, $email_para);

            //3-GRAVANDO LOG
            $objLog = new log();
            $valid_Log=$objLog->logEmailGrupo($s_usuario,$id_para);

            if ($val1id_email == 1 && $valid_Log==1) {
                $cont++;
            }
        }

        if ($cont == count($arrUsuario)) {
            return 1;
        } else {
            return 0;
        }
    }

    function e4aEnvioReembolso($s_usuario, $nome_para,$email_para,$nome_usuario,$id_format,$empresa,$mes,$despesa,$evento,$total,$itens,$envio)
    {
        $titulo = 'Notificação ao aprovador - Reembolso [' . $id_format . '] disponibilizado para sua avaliação';

            //Add dados no corpo do email
            $corpo = file_get_contents("../pages/email/e04a_rdv_anl.html", true);
            $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
            $corpo = str_replace("[[ID_REEMBOLSO]]", $id_format, $corpo);
            $corpo = str_replace("[[EMPRESA]]", $empresa, $corpo);
            $corpo = str_replace("[[NOME_USUARIO]]", $nome_usuario, $corpo);
            $corpo = str_replace("[[MES]]", $mes, $corpo);
            $corpo = str_replace("[[DESPESA]]", $despesa, $corpo);
            $corpo = str_replace("[[EVENTO]]", $evento, $corpo);
            $corpo = str_replace("[[TOTAL]]", $total, $corpo);
            $corpo = str_replace("[[ITENS]]", $itens, $corpo);
            $corpo = str_replace("[[ENVIO]]", $envio, $corpo);
            //
            //ENVIANDO EMAIL
            $objEmail = new email();
            $valid_email =$objEmail->enviaEmail($titulo, $corpo, $nome_para, $email_para);



            // GRAVANDO LOG DE EMAIL

        if ($valid_email==1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e5cAprovadoReembolso($s_usuario, $nome_para,$email_para,$id_format,$empresa,$mes,$despesa,$evento,$total,$itens,$envio,$aprovacao,$pagamento)
    {
        $titulo = 'Notificação ao colaborador - Reembolso [' . $id_format . '] aprovado';
        //
        //Add dados no corpo do email
        $corpo = file_get_contents("../pages/email/e05c_rdv_apv.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
        $corpo = str_replace("[[ID_REEMBOLSO]]", $id_format, $corpo);
        $corpo = str_replace("[[EMPRESA]]", $empresa, $corpo);
        $corpo = str_replace("[[NOME_USUARIO]]", $nome_para, $corpo);
        $corpo = str_replace("[[MES]]", $mes, $corpo);
        $corpo = str_replace("[[DESPESA]]", $despesa, $corpo);
        $corpo = str_replace("[[EVENTO]]", $evento, $corpo);
        $corpo = str_replace("[[TOTAL]]", $total, $corpo);
        $corpo = str_replace("[[ITENS]]", $itens, $corpo);
        $corpo = str_replace("[[ENVIO]]", $envio, $corpo);
        $corpo = str_replace("[[APROVACAO]]", $aprovacao, $corpo);
        $corpo = str_replace("[[DIA_PAGAMENTO]]", $pagamento, $corpo);
        //
        //enviando email
        $obj = new email();
        $valida = $obj->enviaEmail($titulo, $corpo, $nome_para, $email_para);
        if ($valida == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e6aNegadoReembolso($s_usuario, $nome_para,$email_para,$nome_de,$email_de,$nome_usuario,$id_format,$empresa,$mes,$despesa,$evento,$total,$itens,$envio,$mensagem)
    {
        $titulo = 'Notificação ao colaborador - Reembolso [' . $id_format . '] negado';
        //
        //
        //Add dados no corpo do email
        $corpo = file_get_contents("../pages/email/e06a_rdv_neg.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
        $corpo = str_replace("[[NOME_DE]]", $nome_de, $corpo);
        $corpo = str_replace("[[EMAIL_DE]]", $email_de, $corpo);
        $corpo = str_replace("[[ID_REEMBOLSO]]", $id_format, $corpo);
        $corpo = str_replace("[[EMRPESA]]", $empresa, $corpo);
        $corpo = str_replace("[[NOME_USUARIO]]", $nome_usuario, $corpo);
        $corpo = str_replace("[[MES]]", $mes, $corpo);
        $corpo = str_replace("[[DESPESA]]", $despesa, $corpo);
        $corpo = str_replace("[[EVENTO]]", $evento, $corpo);
        $corpo = str_replace("[[TOTAL]]", $total, $corpo);
        $corpo = str_replace("[[ITENS]]", $itens, $corpo);
        $corpo = str_replace("[[ENVIO]]", $envio, $corpo);
        $corpo = str_replace("[[MENSAGEM]]", $mensagem, $corpo);
        //
        //enviando email
        $obj = new email();
        $valida = $obj->enviaEmailCopia($titulo, $corpo, $nome_para, $email_para, $nome_de, $email_de);
        if ($valida == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e7cRevisarReembolso($s_usuario, $nome_para,$email_para,$nome_de,$email_de,$id_format,$empresa,$mes,$despesa,$evento,$total,$itens,$envio,$mensagem)
    {

        $titulo = 'Notificação ao colaborador - Reembolso [' . $id_format . '] para revisão';
        //
        //Add dados no corpo do email
        $corpo = file_get_contents("../pages/email/e07c_rdv_rev.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
        $corpo = str_replace("[[NOME_DE]]", $nome_de, $corpo);
        $corpo = str_replace("[[EMAIL_DE]]", $email_de, $corpo);
        $corpo = str_replace("[[ID_REEMBOLSO]]", $id_format, $corpo);
        $corpo = str_replace("[[EMPRESA]]", $empresa, $corpo);
        $corpo = str_replace("[[MES]]", $mes, $corpo);
        $corpo = str_replace("[[DESPESA]]", $despesa, $corpo);
        $corpo = str_replace("[[EVENTO]]", $evento, $corpo);
        $corpo = str_replace("[[TOTAL]]", $total, $corpo);
        $corpo = str_replace("[[ITENS]]", $itens, $corpo);
        $corpo = str_replace("[[ENVIO]]", $envio, $corpo);
        $corpo = str_replace("[[MENSAGEM]]", $mensagem, $corpo);
        //
        //enviando email
        $obj = new email();
        $valida = $obj->enviaEmail($titulo, $corpo, $nome_para, $email_para);
        if ($valida == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e8cReprovadoReemmbolso($s_usuario, $nome_para,$email_para,$nome_de,$email_de,$id_format,$empresa,$mes,$despesa,$evento,$total,$itens,$envio,$mensagem)
    {
        $titulo = 'Notificação ao colaborador - Reembolso [' . $id_format . '] reprovado';
        //
        //Add dados no corpo do email
        $corpo = file_get_contents("../pages/email/e08c_rdv_rep.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
        $corpo = str_replace("[[NOME_DE]]", $nome_de, $corpo);
        $corpo = str_replace("[[EMAIL_DE]]", $email_de, $corpo);
        $corpo = str_replace("[[ID_REEMBOLSO]]", $id_format, $corpo);
        $corpo = str_replace("[[EMPRESA]]", $empresa, $corpo);
        $corpo = str_replace("[[MES]]", $mes, $corpo);
        $corpo = str_replace("[[DESPESA]]", $despesa, $corpo);
        $corpo = str_replace("[[EVENTO]]", $evento, $corpo);
        $corpo = str_replace("[[TOTAL]]", $total, $corpo);
        $corpo = str_replace("[[ITENS]]", $itens, $corpo);
        $corpo = str_replace("[[ENVIO]]", $envio, $corpo);
        $corpo = str_replace("[[MENSAGEM]]", $mensagem, $corpo);
        //
        //enviando email
        $obj = new email();
        $valida = $obj->enviaEmail($titulo, $corpo, $nome_para, $email_para);
        if ($valida == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e9aRetomadoReembolso($s_usuario, $nome_para,$email_para,$nome_de,$email_de,$nome_usuario,$id_format,$empresa,$mes,$despesa,$evento,$total,$itens,$envio,$negado,$mensagem)
    {
        $titulo = 'Notificação ao aprovador - Reembolso [' . $id_format . '] retomada de aprovação';
        //
        //Add dados no corpo do email
        $corpo = file_get_contents("../pages/email/e09a_rdv_rtm.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
        $corpo = str_replace("[[NOME_DE]]", $nome_de, $corpo);
        $corpo = str_replace("[[EMAIL_DE]]", $email_de, $corpo);
        $corpo = str_replace("[[ID_REEMBOLSO]]", $id_format, $corpo);
        $corpo = str_replace("[[NOME_USUARIO]]", $nome_usuario, $corpo);
        $corpo = str_replace("[[EMPRESA]]", $empresa, $corpo);
        $corpo = str_replace("[[MES]]", $mes, $corpo);
        $corpo = str_replace("[[DESPESA]]", $despesa, $corpo);
        $corpo = str_replace("[[EVENTO]]", $evento, $corpo);
        $corpo = str_replace("[[TOTAL]]", $total, $corpo);
        $corpo = str_replace("[[ITENS]]", $itens, $corpo);
        $corpo = str_replace("[[ENVIO]]", $envio, $corpo);
        $corpo = str_replace("[[NEGADO]]", $negado, $corpo);
        $corpo = str_replace("[[MENSAGEM]]", $mensagem, $corpo);
        //
        //enviando email
        $obj = new email();
        $valida = $obj->enviaEmail($titulo, $corpo, $nome_para, $email_para);
        if ($valida == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e10cDocumentoCompartilhado($s_usuario, $id_usuario, $arquivo)
    {
        //SELECT INFORMACOES DO DOCUMENTO
        $query = "SELECT d.id AS id_documento
                        ,d.tipo
                        ,d.documento AS nome_documento
                        ,d.data_inclusao AS inclusao
                        ,u.nome AS nome_para
                        ,u.usuario AS email_para
                    FROM colaborador_documento AS d
                    LEFT JOIN usuario AS u ON u.id = d.id_usuario
                    WHERE d.documento = ? AND d.id_usuario = ?";
        $params = array($arquivo,$id_usuario);
        $resul= sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_documento = $linha->id_documento;
        $tipo = $linha->tipo;
        $nome_documento = $linha->nome_documento;
        $dt_inclusao = $linha->inclusao;
        $nome_para = $linha->nome_para;
        $email_para = $linha->email_para;

        $titulo = 'Notificação ao colaborador - Novo documento [DOC-' . $id_documento . '/TIPO-'.$tipo.'] compartilhado';


        //
        //Add dados no corpo do email
        $corpo = file_get_contents("../pages/email/e10c_doc_cmp.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para, $corpo);
        $corpo = str_replace("[[ID_DOCUMENTO]]", $id_documento, $corpo);
        $corpo = str_replace("[[TIPO]]", $tipo, $corpo);
        $corpo = str_replace("[[NOME_DOCUMENTO]]", $nome_documento, $corpo);
        $corpo = str_replace("[[INCLUSAO]]", $dt_inclusao, $corpo);
        //
        //enviando email
        $obj = new email();
        $valida_email = $obj->enviaEmail($titulo, $corpo, $nome_para, $email_para);

        $objLog = new log();
        $valid_Log=$objLog->logEmailDocumentoCompartilhado($s_usuario,$id_usuario);


        if ($valida_email == 1 && $valid_Log==1) {
            return 1;
        } else {
            return 0;
        }
    }

    function e11cAprovadoFinanceiro($s_usuario, $arrTitulos){
        //1-GET DADOS DOS USUARIOS PARA ENVIO DE EMAIL
        $query = "SELECT TOP 1
                         e.id_usuario AS id_para
                        ,e.nome AS nome_para
                        ,e.sobrenome  AS sobrenome_para
                        ,e.email AS email_para
                        ,u.id AS id_de
                        ,u.nome AS nome_de
                        ,u.sobrenome AS sobrenome_de
                        ,u.usuario AS email_de
                        ,CONVERT(varchar,GETDATE(),105) AS dataAprov
                    FROM configuracao_email_disparo AS e
                    LEFT JOIN usuario AS u ON u.id = ?
                    WHERE tipo_disparo=400
                    ORDER BY e.id  ";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_de = $linha->id_de;
        $nome_de = $linha->nome_de;
        $sobrenome_de = $linha->sobrenome_de;
        $email_de = $linha->email_de;
        $id_para = $linha->id_para;
        $nome_para = $linha->nome_para;
        $sobrenome_para = $linha->sobrenome_para;
        $email_para = $linha->email_para;
        $dt_aprov = $linha->dataAprov;
        //
        $tituloEmail = 'Notificação financeira - Aprovação de ['.count($arrTitulos).']título(s) na data de ['.$dt_aprov.']';
        //
        $titulos = '';
        foreach ($arrTitulos as $titulo) {
            $titulos .= '<tr>';
            $titulos .= '<td align="left">' . $titulo{'pagadora'} . '</td>';
            $titulos .= '<td align="left">' . $titulo{'beneficiario'} . '</td>';
            $titulos .= '<td align="left">' . $titulo{'valor'} . '</td>';
            $titulos .= '<td align="left">' . $titulo{'vencimento'} . '</td>';
            $titulos .= '<td align="left">' . $titulo{'codigo'} . '</td>';
            $titulos .= '</tr>';
        }

        $corpo = file_get_contents("../pages/email/e11c_fin_apv.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para.' '.$sobrenome_para, $corpo);
        $corpo = str_replace("[[NOME_DE]]", $nome_de.' '.$sobrenome_de, $corpo);
        $corpo = str_replace("[[EMAIL_DE]]", $email_de, $corpo);
        $corpo = str_replace("[[NUM_TITULOS]]", count($arrTitulos), $corpo);
        $corpo = str_replace("[[TITULOS]]", $titulos, $corpo);

        //enviando email
        $objEmail = new email();
        $val1id_email = $objEmail->enviaEmailCopia($tituloEmail, $corpo, $nome_para, $email_para,$nome_de, $email_de);

        //3-GRAVANDO LOG
        $objLog = new log();
        $valid_Log=$objLog->logEmailAprovadoFinanceiro($s_usuario,$id_de,$id_para);

        if ($val1id_email == 1 && $valid_Log==1) {
            return 1;
        }else{
            return 0;
        }

    }

    function e12cMaisDetalhesFinanceiro($s_usuario, $codigo,$pagadora,$beneficiario,$valor,$emissao,$vencimento,$mensagem,$id_msg){
        //1-GET DADOS DOS USUARIOS PARA ENVIO DE EMAIL
        $query = "SELECT TOP 1
                         e.id_usuario AS id_para
                        ,e.nome AS nome_para
                        ,e.sobrenome  AS sobrenome_para
                        ,e.email AS email_para
                        ,u.id AS id_de
                        ,u.nome AS nome_de
                        ,u.sobrenome AS sobrenome_de
                        ,u.usuario AS email_de
                        ,CONVERT(varchar,GETDATE(),105) AS dataAprov
                    FROM configuracao_email_disparo AS e
                    LEFT JOIN usuario AS u ON u.id = ?
                    WHERE tipo_disparo=410
                    ORDER BY e.id  ";
        $params = array($s_usuario);
        $resul = sqlsrv_query($this->link, $query, $params);
        $linha = sqlsrv_fetch_object($resul);
        $id_de = $linha->id_de;
        $nome_de = $linha->nome_de;
        $sobrenome_de = $linha->sobrenome_de;
        $email_de = $linha->email_de;
        $id_para = $linha->id_para;
        $nome_para = $linha->nome_para;
        $sobrenome_para = $linha->sobrenome_para;
        $email_para = $linha->email_para;
        $dt_aprov = $linha->dataAprov;
        //
        $tituloEmail = 'Notificação financeira - Solicitação de maiores detalhes do título ['.$codigo.']';
        //

        $corpo = file_get_contents("../pages/email/e12c_fin_det.html", true);
        $corpo = str_replace("[[NOME_PARA]]", $nome_para.' '.$sobrenome_para, $corpo);
        $corpo = str_replace("[[NOME_DE]]", $nome_de.' '.$sobrenome_de, $corpo);
        $corpo = str_replace("[[EMAIL_DE]]", $email_de, $corpo);
        $corpo = str_replace("[[PAGADORA]]", $pagadora, $corpo);
        $corpo = str_replace("[[BENEFICIARIO]]", $beneficiario, $corpo);
        $corpo = str_replace("[[VALOR]]", $valor, $corpo);
        $corpo = str_replace("[[EMISSAO]]", $emissao, $corpo);
        $corpo = str_replace("[[VENCIMENTO]]", $vencimento, $corpo);
        $corpo = str_replace("[[CODIGO]]", $codigo, $corpo);
        $corpo = str_replace("[[MENSAGEM]]", $mensagem, $corpo);

        //enviando email
        $objEmail = new email();
        $val1id_email = $objEmail->enviaEmailCopia($tituloEmail, $corpo, $nome_para, $email_para,$nome_de, $email_de);

        //3-GRAVANDO LOG
        $objLog = new log();
        $valid_Log=$objLog->logEmailMaisDetalhesFinanceiro($s_usuario,$id_de,$id_para,$id_msg);

        if ($val1id_email == 1 && $valid_Log==1) {
            return 1;
        }else{
            return 0;
        }

    }

}

