<?php
//Este arquivo deve ser executado via console utilizando-se do CRON
require_once("config.inc.php");
//require_once(INCLUDE_DIR."include_email.inc.php");
require_once(INCLUDE_DIR."encryption.php");

/*

ALTERAÇÕES:

	03/07/2012 - CLASSE BANCO DE DADOS - CARLOS ABREU
	22/06/2016 - ALTEREI TODAS AS ROTINAS PARA ENVIAR EMAIL COM A NOVA CLASSE EMAILS E BUSCAR DESTINATÁRIOS DO BANCO DE DADOS
	10/05/2017 - Unificação das tabelas numero_cliente e numeros_interno - Carlos Abreu
	13/11/2017 - Inclusão dos campos reg_del nas consultas - Carlos Abreu
	11/01/2018 - Inclusão da função de encerramento dos chamados - Carlos Eduardo
	24/01/2018 - Inclusão da aprovação automatica de solicitação alteração escopo - Carlos Abreu
	26/02/2018 - Inclusão da função de aviso pedidos sem anexo - Carlos Eduardo
*/

function vencimento_senha()
{
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','SUSPENSO','CANCELADO') ";
	$sql .= "AND usuarios.data_troca <> '0000-00-00' ";
	$sql .= "AND usuarios.status <> '2' ";//bloqueado

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}

	foreach($db->array_select as $reg)
	{
		$data_referencia = date("d/m/Y");
		
		$data_troca = mysql_php($reg["data_troca"]);
		
		$dias_restantes = abs(dif_datas($data_referencia,$data_troca)-(DIAS_LIMITE));
		
		//maior que 60 dias, bloqueia
		if(dif_datas($data_referencia,$data_troca) > DIAS_LIMITE)
		{
			if(!empty($reg["email"]) && !empty($reg["funcionario"]))
			{
				$array_bloq[$reg["email"]] = $reg["funcionario"];
			}
			
			$usql = "UPDATE ".DATABASE.".usuarios SET ";
			$usql .= "status = '2' ";
			$usql .= "WHERE id_usuario = '".$reg["id_usuario"]."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				die($db->erro);		
			}
		}
		else
		{
			//igual a 60, envia o ultimo aviso
			if($dias_restantes == 0)
			{
				if(!empty($reg["email"]) && !empty($reg["funcionario"]))
				{
					$array_last_warning[$reg["email"]] = $reg["funcionario"];
				}
			}
			else
			{
				//se faltar 10 dias para o vencimento, envia o e-mail
				if($dias_restantes<=10)
				{					
					$Body = "<B><FONT FACE=ARIAL COLOR=RED>ATENÇÃO - SENHA DVMSYS</FONT></B><BR>";
					$Body .= "Sua senha expira em ".$dias_restantes." dia(s), favor trocar sua senha.<br>";
					$Body .= "Ao fazer o login no sistema, escolha Trocar Senha no menu inicial.";
					$Body .= "<br><br><br>";
					
					$params = array();
					
					$params['from']	= "tecnologia@dominio.com.br";
					
					$params['from_name'] = "Sistema ERP";
					
					$params['subject'] 	= "SENHA EXPIRANDO EM ".$dias_restantes." dia(s)";
					
					if(!empty($reg["email"]) && !empty($reg["funcionario"]))
					{
						$params['emails']['to'][] = array('email' => $reg["email"], 'nome' => $reg["funcionario"]);
					}
					
					$mail = new email($params);
					
					$mail->montaCorpoEmail($Body);
										
					if(!$mail->Send())
					{
						echo $mail->ErrorInfo;
					}
					
					$mail->ClearAllRecipients();				
				}
			}
		}
	}	
	
	if(count($array_bloq)>0)
	{
		//ENVIA E-MAIL EXPIRADO	
		$Body = "<B><FONT FACE=ARIAL COLOR=RED>ATENÇÃO - SENHA DVMSYS</FONT></B><BR>";
		$Body .= "Sua senha do ERP expirou, o sistema solicitará a troca no próximo login.<br>";
		$Body .= "<br><br><br>";
		
		$params = array();
		$params['from']	= "tecnologia@dominio.com.br";
		$params['from_name'] = "Sistema ERP";
		$params['subject'] = "SENHA ERP EXPIRADA, ACESSO AO SISTEMA BLOQUEADO";
		
		//Limpamos os emails anteriores
		$params['emails']['to'] = array();
		
		foreach($array_bloq as $email=>$funcionario)
		{
			if(!empty($email) && !empty($funcionario))
			{
				$params['emails']['to'][] = array('email' => $email, 'nome' => $funcionario);
			}
		}
		
		$mail = new email($params);
		
		$mail->montaCorpoEmail($Body);
		
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
		
		$mail->ClearAllRecipients();
	}
	
	if(count($array_last_warning)>0)
	{
		//ENVIA ULTIMO E-MAIL
		$Body = "<B><FONT FACE=ARIAL COLOR=RED>ATENÇÃO - SENHA ERP</FONT></B><BR>";
		$Body .= "Sua senha do ERP expira hoje, favor trocar sua senha.<br>";
		$Body .= "Ao fazer o login no sistema, escolha Trocar Senha no menu inicial.";
		$Body .= "<br><br><br>";
		
		$params = array();
		$params['from']	= "tecnologia@dominio.com.br";
		$params['from_name'] = "Sistema ERP";
		$params['subject'] = "SENHA ERP EXPIRANDO, ÚLTIMO AVISO";
		
		//Limpamos os emails anteriores
		$params['emails']['to'] = array();
		
		foreach($array_last_warning as $email=>$funcionario)
		{
			$params['emails']['to'][] = array('email' => $email, 'nome' => $funcionario);
		}
		
		$mail = new email($params);
		
		$mail->montaCorpoEmail($Body);
		
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
		
		$mail->ClearAllRecipients();
	}			
}

function rotinas_manutencao()
{
	$db = new banco_dados;

	$params = array();
	
	$params['from']	= "suporte@dominio.com.br";
	
	$params['from_name'] = "ROTINAS DE MANUTENÇÕES";
	
	$params['subject'] = "ROTINAS AGENDADAS DE MANUTENÇÕES";
	
	//1 - segunda	
	if(date('w') == 1)
	{
		$data_filtro = php_mysql(calcula_data(date('d/m/Y'),'sub','day',2));
	}
	else
	{
		$data_filtro = date('Y-m-d');
	}
	
	$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_manutencoes, ".DATABASE.".ti_rotinas, ".DATABASE.".ti_rotinas_frequencias, ".DATABASE.".ti_frequencias, ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE ti_rotinas_manutencoes.reg_del = 0 ";
	$sql .= "AND ti_rotinas.reg_del = 0 ";
	$sql .= "AND ti_rotinas_frequencias.reg_del = 0 ";
	$sql .= "AND ti_frequencias.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND ti_rotinas_manutencoes.id_ti_rotina = ti_rotinas.id_ti_rotina ";
	$sql .= "AND ti_rotinas_manutencoes.id_ti_analista = funcionarios.id_funcionario ";
	$sql .= "AND ti_rotinas_manutencoes.ti_data_previsao = '".$data_filtro."' ";
	$sql .= "AND ti_rotinas.id_ti_rotina = ti_rotinas_frequencias.id_ti_rotina ";
	$sql .= "AND ti_rotinas_frequencias.id_ti_frequencia = ti_frequencias.id_ti_frequencia ";
	$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "ORDER BY funcionario, ti_frequencia, ti_rotina ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		echo ($db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		$array_funcionario[$reg["funcionario"]] = $reg["email"];
		
		$array_rotinas[$reg["funcionario"]][$reg["ti_frequencia"]][$reg["ti_rotina"]] = date('d/m/Y');	
	}
	
	foreach ($array_rotinas as $funcionario=>$frequencia)
	{
		$texto = "";
		
		foreach ($frequencia as $freq=>$rotina)
		{
			$texto .= "<strong>".$freq . "</strong><br>";
			
			foreach($rotina as $rot=>$data)
			{
				$texto .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;".$data . " - " . $rot."</p>";
			}
			
		}
		
		if(!empty($array_funcionario[$funcionario]) && !empty($funcionario))
		{
			$params['emails']['to'][] = array('email' => $array_funcionario[$funcionario], 'nome' => $funcionario);
		}
		
		$mail = new email($params);
		
		$mail->montaCorpoEmail($texto);		
		
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
		
		$mail->ClearAddresses();
	}	
}

//RH
function vencimento_exame()
{
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_aso ";
	$sql .= "WHERE funcionarios.id_funcionario = rh_aso.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_aso.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
	$sql .= "AND rh_aso.vencimento = '0' ";
	$sql .= "AND rh_aso.data_vencimento <= '".php_mysql(calcula_data(date('d/m/y'), "sum", "day", "20"))."' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}

	$texto = "";
	
	foreach($db->array_select as $reg)
	{		
		//Concatena mensagem
		$texto = "<B><FONT FACE=ARIAL COLOR=RED>ATENCAO</FONT></B><BR>";
		$texto .= "O funcionario abaixo esta com o exame periodico com data de vencimento em: ".mysql_php($reg["data_vencimento"])."<br>";
		$texto .= "favor providenciar um novo exame.<br><br>";
		$texto .= "<B>Exame número:</b> ".$reg["id_rh_aso"]."<br>";
		$texto .= "<b>Funcionário:</b> ".$reg["funcionario"]."<br><br><br>";

		$params = array();
		
		$params['from']	= "empresa@dominio.com.br";
		
		$params['from_name'] = "Recursos Humanos - Sistema de PCMSO";
		
		$params['subject'] = 'VENCIMENTO DE EXAME PERIODICO EM 20 DIAS';
		
		$mail = new email($params, 'vencimento_exame_periodico');
		
		$mail->montaCorpoEmail($texto);
		
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
		
		$mail->ClearAddresses();
		
		$usql = "UPDATE ".DATABASE.".rh_aso SET ";
		$usql .= "rh_aso.vencimento = '1' ";
		$usql .= "WHERE rh_aso.id_rh_aso = '".$reg["id_rh_aso"]."' ";
		$usql .= "AND rh_aso.reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			die($db->erro);		
		}
	}
}

function vencimento_integracao()
{
	//$dias: Quantidade de dias anteriores a data atual, utilizado na checagem do atraso.
	//$encerra: Se true, insere um registro no banco para que não seja feita a checagem novamente no dia. Valores: (true/*false*)
	
	//Função que verifica o preenchimento do Controle de Horas e dispara e-mails
	$db = new banco_dados;	
	
	//Alteração: 04/01/2017
	//Eduardo: Agora o aviso de integração ocorrerá com 30 dias ou 15 dias
	$sql = "SELECT descricao, data_vencimento, id_rh_integracao, funcionario FROM ".DATABASE.".funcionarios, ".DATABASE.".local, ".DATABASE.".rh_integracao ";
	$sql .= "WHERE funcionarios.id_funcionario = rh_integracao.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND local.reg_del = 0 ";
	$sql .= "AND rh_integracao.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
	$sql .= "AND rh_integracao.id_local_trabalho = local.id_local ";
	$sql .= "AND (
					(rh_integracao.vencimento = '0' AND rh_integracao.data_vencimento = '".php_mysql(calcula_data(date('d/m/y'), "sum", "day", "30"))."')
					OR
					(rh_integracao.vencimento = '1' AND rh_integracao.data_vencimento = '".php_mysql(calcula_data(date('d/m/y'), "sum", "day", "15"))."')
				) ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}

	$texto = "";
	
	foreach($db->array_select as $reg)
	{
		//Concatena mensagem
		$texto = "<B><FONT FACE=ARIAL COLOR=RED>ATENCAO</FONT></B><BR>";
		$texto .= "O funcionario abaixo esta com a integracao na ".$reg["descricao"]." com data de vencimento em: ".mysql_php($reg["data_vencimento"])."<br>";
		$texto .= "favor providenciar um novo exame.<br><br>";
		$texto .= "<B>Integracao numero:</b> ".$reg["id_rh_integracao"]."<br>";
		$texto .= "<b>funcionario:</b> ".$reg["funcionario"]."<br><br><br>";
		
		$params = array();
		
		$params['from']	= "empresa@dominio.com.br";
		
		$params['from_name'] = "Recursos Humanos - Sistema de PCMSO";
		
		$params['subject'] = 'VENCIMENTO DE INTEGRACAO ('.$reg['funcionario'].') EM '.mysql_php($reg['data_vencimento']);
		
		$mail = new email($params, 'vencimento_integracao');
		
		$mail->montaCorpoEmail($texto);
		
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
		
		$mail->ClearAddresses();
		
		$usql = "UPDATE ".DATABASE.".rh_integracao SET ";
		$usql .= "rh_integracao.vencimento = '1' ";
		$usql .= "WHERE rh_integracao.id_rh_integracao = '".$reg["id_rh_integracao"]."' ";
		$usql .= "AND rh_integracao.reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			die($db->erro);		
		}
	}
}

function vencimento_treinamento()
{
    $db = new banco_dados();
	
    $sql = "SELECT rti_id, rtc_data_vencimento, DATEDIFF(SUBSTRING(NOW(),1,10), rtc_data_vencimento) dif, funcionario, email, treinamento, rti_renovar, rti_situacao
				FROM ".DATABASE.".rh_treinamentos_cabecalho
				JOIN (SELECT rti_id, rti_id_funcionario, rti_rtc_id, rti_renovar, rti_situacao FROM ".DATABASE.".rh_treinamentos_itens WHERE rh_treinamentos_itens.reg_del = 0) itens ON rtc_id = rti_rtc_id
				JOIN (SELECT id_rh_treinamento, treinamento FROM ".DATABASE.".rh_treinamentos WHERE rh_treinamentos.reg_del = 0) treinamentos ON id_rh_treinamento = rtc_id_treinamento
				JOIN (SELECT id_funcionario, funcionario, email FROM ".DATABASE.".funcionarios 
				JOIN (SELECT id_funcionario id_funcionario, email FROM ".DATABASE.".usuarios WHERE usuarios.reg_del = 0) usuarios ON id_funcionario = id_funcionario
				WHERE situacao = 'ATIVO' AND funcionarios.reg_del = 0) funcs ON id_funcionario = rti_id_funcionario
				WHERE rtc_data_vencimento >= '2017-01-01'
				AND rh_treinamentos_cabecalho.reg_del = 0
				AND rti_renovar = 1
				AND rtc_situacao IN(1,2)
				AND DATEDIFF(SUBSTRING(NOW(),1,10), rtc_data_vencimento) IN(-45,-30) ORDER BY rtc_data_treinamento DESC";
    
    $params = array();
    
    $texto = '';
    
    $db->select($sql, 'MYSQL',true);
    
    if($db->erro!='')
    {
        die($db->erro);
    }
    
    foreach($db->array_select as $reg)
    {
        $dias = $reg['dif'];
        
        //faltando 30 e 45 dias deve ser enviado um email para o rh
        if (in_array($dias, array(-30,-45)))
        {
            $texto = "<B><FONT FACE=ARIAL COLOR=RED>ATENÇÃO</FONT></B><BR>";
            $texto .= "O funcionário abaixo está com o treinamento ".$reg["treinamento"]." com data de vencimento em: ".mysql_php($reg["rtc_data_vencimento"])."<br>";
            $texto .= "favor providenciar um novo treinamento.<br><br>";
            $texto .= "<B>Treinamento número:</b> ".$reg["rti_id"]."<br>";
            $texto .= "<b>Funcionário:</b> ".$reg["funcionario"]."<br><br><br>";
            
            $params['from']	= "empresa@dominio.com.br";
            
            $params['from_name'] = "Recursos Humanos - Treinamentos";
            
            $mail = new email($params, 'vencimento_treinamento');
            
            $mail->montaCorpoEmail($corpoEmail);
            
            if(!$mail->Send())
            {
                echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
            }
        }
        
        //caso o treinamento esteja vencido, marcar como vencido (SITUAÇÃO = 4)
        if ($dias > 0 && in_array($reg['rti_situacao'],array(1,2)))
        {
            $usql = "UPDATE ".DATABASE.".rh_treinamentos_itens SET ";
			$usql .= "rti_situacao = 4 ";
			$usql .= "WHERE rti_id = '".$reg['rti_id']."' ";
			$usql .= "AND rh_treinamentos_itens.reg_del = 0 ";
            
			$db->update($usql, 'MYSQL');
        }
    }
}

function vencimento_controles_sgi()
{
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".sgi_controle  ";
	$sql .= "WHERE sgi_controle.vencimento < 3 ";
	$sql .= "AND sgi_controle.reg_del = 0 ";
	$sql .= "ORDER BY data_vencimento ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}

	$texto = "";
	
	$array_controle = $db->array_select;
	
	foreach($array_controle as $reg)
	{
		switch ($reg["vencimento"])
		{
			case 0:	//60 dias			
				$filtro = "AND sgi_controle.vencimento = 0 ";
				$filtro .= "AND sgi_controle.data_vencimento <= '".php_mysql(calcula_data(date('d/m/y'), "sum", "day", "60"))."' ";
				$subject = "VENCIMENTO DE REQUISITO SGI EM 60 DIAS";
				$update = "sgi_controle.vencimento = 1 ";
			break;
			
			case 1:	//45 dias			
				$filtro = "AND sgi_controle.vencimento = 1 ";
				$filtro .= "AND sgi_controle.data_vencimento <= '".php_mysql(calcula_data(date('d/m/y'), "sum", "day", "45"))."' ";
				$subject = "VENCIMENTO DE REQUISITO SGI EM 45 DIAS";
				$update = "sgi_controle.vencimento = 2 ";
			break;
			
			case 2:	//30 dias			
				$filtro = "AND sgi_controle.vencimento = 2 ";
				$filtro .= "AND sgi_controle.data_vencimento <= '".php_mysql(calcula_data(date('d/m/y'), "sum", "day", "30"))."' ";
				$subject = "VENCIMENTO DE REQUISITO SGI EM 30 DIAS";
				$update = "sgi_controle.vencimento = 3 ";
			break;
		}
		
		
		$sql = "SELECT * FROM ".DATABASE.".sgi_item, ".DATABASE.".sgi_requisito, ".DATABASE.".sgi_controle  ";
		$sql .= "WHERE sgi_controle.id_sgi_item = sgi_item.id_sgi_item ";
		$sql .= "AND sgi_item.reg_del = 0 ";
		$sql .= "AND sgi_requisito.reg_del = 0 ";
		$sql .= "AND sgi_controle.reg_del = 0 ";
		$sql .= "AND sgi_controle.id_sgi_requisito = sgi_requisito.id_sgi_requisito ";
		$sql .= $filtro;

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			die($db->erro);		
		}
	
		$texto = "";
		
		foreach($db->array_select as $reg1)
		{
			//Concatena mensagem
			$texto = "<B><FONT FACE=ARIAL COLOR=RED>ATENÇÃO</FONT></B><BR>";
			$texto .= "O Requisito SGI ".$reg1["sgi_requisito"]." com data de vencimento em: ".mysql_php($reg1["data_vencimento"])."<br>";
			
			$params = array();
			
			$params['from']	= "empresa@dominio.com.br";
			
			$params['from_name'] = "Recursos Humanos - Sistema de Treinamentos";
			
			$params['subject'] = $subject;
			
			$mail = new email($params, 'vencimento_controles_sgi');
			
			$mail->montaCorpoEmail($texto);	
			
			if(!$mail->Send())
			{
				echo $mail->ErrorInfo;
			}
			
			$mail->ClearAddresses();
			
			$usql = "UPDATE ".DATABASE.".sgi_controle SET ";
			$usql .= $update;
			$usql .= "WHERE sgi_controle.id_sgi_controle = '".$reg1["id_sgi_controle"]."' ";
			$usql .= "AND sgi_controle.reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				die($db->erro);		
			}		
		}
	}
}

function vencimento_habilitacao()
{
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_habilitacao ";
	$sql .= "WHERE funcionarios.id_funcionario = rh_habilitacao.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_habilitacao.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
	$sql .= "AND rh_habilitacao.vencimento = 0 ";
	$sql .= "AND rh_habilitacao.data_vencimento <= '".php_mysql(calcula_data(date('d/m/y'), "sum", "day", "45"))."' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}

	$texto = "";
	
	foreach($db->array_select as $reg)
	{
		//Concatena mensagem
		$texto = "<B><FONT FACE=ARIAL COLOR=RED>ATENÇÃO</FONT></B><BR>";
		$texto .= "O funcionário abaixo está com a CNH Nº: ".$reg["numero_habilitacao"].", categoria: ".$reg["categoria"]." com data de vencimento em: ".mysql_php($reg["data_vencimento"])."<br>";
		$texto .= "favor alertar o funcionário para que renove a CNH.<br><br>";
		$texto .= "<B>Número:</b> ".$reg["id_habilitacao"]."<br>";
		$texto .= "<b>Funcionário:</b> ".$reg["funcionario"]."<br>";
		$texto .= "<B>CNH nº:</b> ".$reg["numero_habilitacao"]."<br>";
		$texto .= "<B>Categoria:</b> ".$reg["categoria"]."<br>";
		$texto .= "<B>Emissão:</b> ".mysql_php($reg["data_emissao"])."<br>";
		$texto .= "<B>Vencimento:</b> ".mysql_php($reg["data_vencimento"])."<br><br><br>";
		
		$params = array();
		
		$params['from']	= "empresa@dominio.com.br";
		
		$params['from_name'] = "Recursos Humanos - Sistema de Habilitações";
		
		$params['subject'] = 'VENCIMENTO DE CNH EM 45 DIAS';
		
		$mail = new email($params, 'vencimento_cnh');
		
		$mail->montaCorpoEmail($texto);
				
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
		
		$mail->ClearAddresses();
		
		$usql = "UPDATE ".DATABASE.".rh_habilitacao SET ";
		$usql .= "rh_habilitacao.vencimento = 1 ";
		$usql .= "WHERE rh_habilitacao.id_habilitacao = '".$reg["id_habilitacao"]."' ";
		$usql .= "AND rh_habilitacao.reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			die($db->erro);		
		}		
	}
}

/**
 * Objetivo: 
 *      Verificar se existem treinamentos a serem avaliados quanto à sua eficácia
 * Regra:
 *      Passados 30 dias do treinamento realizado, enviar um email toda segunda feira para o RH e o gestor do colaborador
 *      até que seja realizada a avaliação da eficácia
 */
function vencimento_avaliacao_eficacia()
{
    $db = new banco_dados();
    
    $sql = "SELECT
				c.funcionario as gestor, d.funcionario as colaborador, e.treinamento as treinamento, a.rtc_data_treinamento data, DATEDIFF(NOW(), a.rtc_data_treinamento) as dias,
				email
			FROM
				".DATABASE.".rh_treinamentos_cabecalho a
				JOIN ".DATABASE.".rh_treinamentos_itens b ON b.rti_rtc_id = a.rtc_id AND b.reg_del = 0 AND b.rti_eficacia IS NULL
				JOIN ".DATABASE.".funcionarios c ON c.id_funcionario = a.rtc_responsavel_eficacia AND c.reg_del = 0
				JOIN ".DATABASE.".funcionarios d ON d.id_funcionario = b.rti_id_funcionario AND d.reg_del = 0
				JOIN ".DATABASE.".rh_treinamentos e ON e.id_rh_treinamento = a.rtc_id_treinamento AND e.reg_del = 0
				JOIN ".DATABASE.".usuarios f ON f.id_funcionario = c.id_funcionario AND f.reg_del = 0
			WHERE
				a.reg_del = 0
				AND a.rtc_responsavel_eficacia > 0
				AND DATEDIFF(NOW(), a.rtc_data_treinamento) >= 30
			ORDER BY
				gestor, colaborador, treinamento";
    
		/*
		$arrDados = array();
		
		$db->select($sql, 'MYSQL', function($reg, $i) use(&$arrDados){
			$arrDados[$reg['gestor']][] = array(
				'colaborador' => $reg['colaborador'],
				'treinamento' => $reg['treinamento'],
				'data_realizacao' => $reg['data'],
				'dias_passados' => $reg['dias'],
				'email' => $reg['email']
			);
		});
		*/
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}
    
    $titulo  = '<p style="font-family: Verdana, Arial; font-size: 12px; font-weight:bold;">TREINAMENTOS COM PEND&Ecirc;NCIA DE AVALIA&Ccedil;&Atilde;O DE EFIC&Aacute;CIA<b></br>';
	
	foreach($db->array_select as $regs)
    {
        $corpo = '<p><table cellpadding="2" cellspacing="0" style="font-family: Verdana, Arial; font-size: 12px;" width="100%" border="1">';
        $corpo .= '<tr><th width="30%">Colaborador</th><th width="50%">Treinamento</th><th width="10%">data Realiza&ccedil;&atilde;o</th><th width="10%">Dias Passados</th></tr>';
        $corpo .= '<caption><b>Gestor: '.$regs["gestor"].'</b></caption>';
        
        //$email = $funcRow[0]['email'];
        
        //foreach($funcRow as $row)
        //{
            $corpo .= '<tr><td>'.$regs['colaborador'].'</td><td>'.$regs['treinamento'].'</td><td>'.mysql_php($regs['data_realizacao']).'</td><td>'.$regs['dias_passados'].'</td></tr>';
       // }
        
        $corpo .= '</table></p><br />';
        
        //Montando o corpo do email para o RH
        $corpoRH .= $corpo;
        
        //Envio de email para o gestor
        $params = array();
        $params['from'] = "empresa@dominio.com.br";
        $params['from_name'] = "Recursos Humanos";
        $params['subject'] = 'TREINAMENTOS COM PENDENCIA DE AVALIACAO DE EFICACIA;';
        $params['emails']['to'][] = array('email' => $regs["email"], 'nome' => $regs["gestor"]);
        
        //Enviando o email ao gestor
        //Esta variável é criada dentro do loop para garantir que será sempre zerada antes do envio
        $mail = new email($params);
        $mail->montaCorpoEmail($titulo.$corpo);
        $mail->send();
    }
    
    //Envio para o RH
    $params = array();
    $params['from'] = "empresa@dominio.com.br";
    $params['from_name'] = "Recursos Humanos";
    $params['subject'] = 'TREINAMENTOS COM PENDENCIA DE AVALIACAO DE EFICACIA;';
        
    $mail = new email($params, 'vencimento_treinamento');
    $mail->montaCorpoEmail($titulo.$corpoRH);
    $mail->send();
}

//arquivo técnico
function verifica_pacotes()
{
	/** EMISSÃO DE DOCUMENTOS - GRD - ARQUIVO TÉCNICO
	 *  Envia um alerta ao solicitante de um pacote, após 2 dias na espera de emissão
	 **/
	$db = new banco_dados();
	
	$array_solicitantes = NULL;
	
	$sql = "SELECT *, ged_pacotes.numero_pacote, ordem_servico.os, ged_pacotes.id_autor, ged_pacotes.data FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE os.id_os = numeros_interno.id_os ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "AND ged_pacotes.data < '" . php_mysql(calcula_data(date("d/m/Y"),"sub","day","2")) . "' ";
	$sql .= "AND ged_pacotes.id_ged_pacote NOT IN (SELECT id_ged_pacote FROM ".DATABASE.".grd WHERE grd.reg_del = 0) ";
	$sql .= "AND ordem_servico.id_os_status NOT IN (3,4,8,9,12,17,18,19) ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}

	foreach($db->array_select as $reg_verifica)
	{
		$array_solicitantes[$reg_verifica["id_autor"]][$reg_verifica["id_ged_pacote"]] = true;
	
		$array_numpacote[$reg_verifica["id_ged_pacote"]] = $reg_verifica["numero_pacote"];
		
		$array_os[$reg_verifica["id_ged_pacote"]] = $reg_verifica["os"];
		
		$array_data[$reg_verifica["id_ged_pacote"]] = $reg_verifica["data"];
	}	

	$sql = "SELECT funcionarios.id_funcionario, funcionarios.funcionario, usuarios.email FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}

	$params = array();
	
	foreach($db->array_select as $reg_usuario)
	{
		$array_loginusr[$reg_usuario["id_funcionario"]] = $reg_usuario["funcionario"];
		$array_emailusr[$reg_usuario["id_funcionario"]] = $reg_usuario["email"];
	}

	//Passa no array
	foreach($array_solicitantes as $chave_solicitante=>$valor_solicitante)
	{
		$texto = "Os seguintes pacotes estão pendentes para emissão no sistema há mais de 2 dias: <BR><BR><BR>";
		
		$params = array();
		
		$params['from']	= "arqivotecnico@dominio.com.br";
		
		$params['from_name'] = "GED";
		
		$params['subject'] = 'GED - Solicitação pendente';
		
		$texto .= "<table border=1>";
		$texto .= "<tr>";		
		$texto .= "<td># Pacote</td>";		
		$texto .= "<td>OS</td>";		
		$texto .= "<td>data da solicitação</td>";					
		$texto .= "<td>Solicitante</td>";					
		$texto .= "</tr>";		
		
		foreach($valor_solicitante as $chave_pacotes=>$valor_pacotes)
		{
			$texto .= "<tr>";		
			$texto .= "<td>" . sprintf("%03d",$array_numpacote[$chave_pacotes]) . "</td>";		
			$texto .= "<td>" . sprintf("%05d",$array_os[$chave_pacotes]) . "</td>";		
			$texto .= "<td>" . mysql_php($array_data[$chave_pacotes]) . "</td>";		
			$texto .= "<td>" . $array_loginusr[$chave_solicitante] . "</td>";		
			$texto .= "</tr>";				
		}
		
		$texto .= "</table>";	
		
		if(!empty($array_emailusr[$chave_solicitante]) && !empty($array_loginusr[$chave_solicitante]))
		{		
			$params['emails']['to'][] = array('email' => $array_emailusr[$chave_solicitante], 'nome' => $array_loginusr[$chave_solicitante]);		
		}
		
		$mail = new email($params);
		
		$mail->montaCorpoEmail($texto);

		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}

		$mail->ClearAddresses();
	}
}

function verifica_devolucao()
{
	/** DEVOLUCAO/COMENTARIOS DE DOCUMENTOS - GRD - ARQUIVO TÉCNICO
	 *  Envia um alerta ao solicitante de um pacote, após 10 dias na espera de emissão
	 **/
	$db = new banco_dados();
	
	//Seleciona o Coordenador principal da OS
	$sql = "SELECT funcionarios.id_funcionario, funcionario, email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$reg_verifica["id_cod_coord"]."' ";
	$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
  
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}
  
	foreach($db->array_select as $regs)
	{
		$array_func[$regs["id_funcionario"]] = $regs["funcionario"];
		$array_email[$regs["id_funcionario"]] = $regs["email"];
	}
	
	/*
	//seleciona os documentos com status de devolução aprovado com comentarios
	*/
	$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".grd ";
	$sql .= "WHERE numeros_interno.id_os = os.id_os ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND grd.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os_status NOT IN (3,4,8,9,12,17,18,19) ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";	
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
	$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "AND ged_versoes.data_devolucao > '2015-01-01' ";
	$sql .= "AND ged_versoes.data_devolucao < '" . php_mysql(calcula_data(date("d/m/Y"),"sub","day","10")) . "' ";
	$sql .= "AND ged_versoes.status_devolucao = 'AC' ";
	$sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
	$sql .= "AND grd.id_grd = numeros_interno.id_grd_emitido ";	
	$sql .= "GROUP BY ged_arquivos.id_ged_arquivo ";	
	$sql .= "ORDER BY ordem_servico.os, ged_pacotes.numero_pacote, ged_versoes.data_devolucao DESC, setores.setor ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}
	
	$array_docs = $db->array_select;

	foreach($array_docs as $reg_verifica)
	{		
		//seleciona o pacote do arquivo
		$sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE ged_pacotes.id_ged_pacote = '".$reg_verifica["id_ged_pacote"]."' ";
		$sql .= "AND ged_pacotes.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			die($db->erro);		
		}
	 
		$reg_grd = $db->array_select[0];		
		
		$params = array();
		
		//Forma o e-mail
		$params['from'] = "arquivotecnico@dominio.com.br";
		
		$params['from_name'] = "GED";
		
		$params['subject'] = "Comentário(s) devolvido pelo cliente a mais de 10 dias - OS: ".sprintf("%05d",$reg_verifica["os"]). " - DISCIPLINA - ".$reg_verifica["setor"];
		
		//principal
		if(!empty($array_email[$reg_verifica["id_cod_coord"]]))
		{
			$params['emails']['to'][] = array('email' => $array_email[$reg_verifica["id_cod_coord"]], 'nome' => $array_func[$reg_verifica["id_cod_coord"]]);
		}
		
		if(!empty($array_email[$reg_verifica["id_coord_aux"]]))
		{
			$params['emails']['to'][] = array('email' => $array_email[$reg_verifica["id_coord_aux"]], 'nome' => $array_func[$reg_verifica["id_coord_aux"]]);
		}
	
		//Adiciona a mensagem do corpo do e-mail
		$Body = "<html>";
		$Body .= "<body style='font: 11pt Arial'><p>O seguinte arquivo do pacote teve comentários em ".mysql_php($reg_verifica["data_devolucao"]) ." e está atrasado a mais de 10 dias";
		$Body .= "<div id='pacote'>Pacote: ".sprintf("%03d",$reg_verifica["numero_pacote"]) . "-" . $reg_verifica["os"] . "</div>";
		$Body .= "<div id='div_numint'>Número Interno: ". PREFIXO_DOC_GED . sprintf("%05d",$reg_verifica["os"]) . "-" . $reg_verifica["sequencia"] . "</div>";
		$Body .= "<div id='div_numcli'>Número Cliente: " . $reg_verifica["numero_cliente"] . "</div>";
		$Body .= "<div id='div_disc'>Disciplina: " . $reg_verifica["setor"] . "</div>";
		$Body .= "<div id='div_revisao'>Revisão / Versão: " . $reg_verifica["revisao_documento"] . "." . $reg_verifica["versao_documento"] . "</div>";
		$Body .= "<div id='div_data_sol'>data da solicitação do Pacote: " . mysql_php($reg_verifica["data"]) . "</div>";
		$Body .= "<div id='div_obs'>Favor encaminhar este e-mail aos responsáveis.</div></body></html>";
		
		$params['emails']['to'][] = array('email' => 'arquivotecnico@dominio.com.br', 'nome' => 'Arquivo Técnico');		
		
		$mail = new email($params);
		
		$mail->montaCorpoEmail($Body);
		
		//Envia o e-mail
		if(!$mail->Send())
		{
			echo ('Erro ao enviar e-mail! '.$mail->ErrorInfo);
		}
	
		$mail->ClearAddresses();		
	}
}

function verifica_retorno()
{
	/** RETORNO DE ARQUIVOS DO CLIENTE - ARQUIVO TÉCNICO
	 *  Envia um alerta ao solicitante de um pacote, após 5 dias na espera de retorno
	 *  a partir da data de estimada de retorno, sendo reenviada conforme o campo flag_numero_avisos de numeros_interno
	 **/

	//$enc = new Crypter('DEVEMADAENGENHARIA');

	$db = new banco_dados();
	
	//Seleciona o Coordenador principal da OS
	$sql = "SELECT funcionarios.id_funcionario, funcionario, email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$reg_verifica["id_cod_coord"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
  
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}
  
	foreach($db->array_select as $regs)
	{
		$array_func[$regs["id_funcionario"]] = $regs["funcionario"];
		$array_email[$regs["id_funcionario"]] = $regs["email"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".codigos_emissao, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".numeros_interno  ";
	$sql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND codigos_emissao.reg_del = 0 ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os_status NOT IN (3,4,8,9,12,17,18,19) ";
	$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
	$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "AND ged_versoes.id_fin_emissao = codigos_emissao.id_codigo_emissao ";
	$sql .= "AND ged_arquivos.situacao = 1 "; //situação: no cliente	
	$sql .= "AND codigos_emissao.codigos_emissao IN ('PA','CO') "; //PARA APROVAÇÃO / PARA COMENTÁRIOS
	$sql .= "ORDER BY ordem_servico.os, numeros_interno.data_retorno_arquivo, setores.setor ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}
	
	$array_docs = $db->array_select;

	foreach($array_docs as $reg_verifica)
	{
		$avisos = $reg_verifica["flag_numero_avisos"]*7;
		
		if($avisos>0)
		{
			$data_retorno = checaDiasUteis(date("d/m/Y"),$avisos,$ret,"sub");
		}
		else
		{
			$data_retorno = date("d/m/Y");
		}
	
		$sql = "SELECT * FROM ".DATABASE.".numeros_interno ";
		$sql .= "WHERE numeros_interno.id_numero_interno = '".$reg_verifica["id_numero_interno"]."' ";
		$sql .= "AND numeros_interno.reg_del = 0 ";
		$sql .= "AND numeros_interno.data_retorno_arquivo = '" . php_mysql($data_retorno) . "' ";
		$sql .= "AND numeros_interno.flag_numero_avisos < 2 ";		

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			die($db->erro);		
		}
		
		if($db->numero_registros>0)
		{
			$inc_flag = $reg_verifica["flag_numero_avisos"]+1;
		
			$usql = "UPDATE ".DATABASE.".numeros_interno SET ";			
			$usql .= "flag_numero_avisos = '".$inc_flag."' ";			
			$usql .= "WHERE id_numero_interno = '".$reg_verifica["id_numero_interno"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);		
			}				
		
			//seleciona o pacote do arquivo
			$sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE ged_pacotes.id_ged_pacote = '".$reg_verifica["id_ged_pacote"]."' ";
			$sql .= "AND ged_pacotes.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);		
			}
		 
			$reg_grd = $db->array_select[0];
						
			//Forma o e-mail
			$params = array();
			
			$params['from']	= "arquivotecnico@dominio.com.br";
			
			$params['from_name'] = "GED";
			
			$params['subject'] = "Documento ". PREFIXO_DOC_GED . sprintf("%05d",$reg_verifica["os"]) . "-" . $reg_verifica["sequencia"]." pendente no cliente a mais de ".$dias." dias";
			
			if($reg_verifica["flag_numero_avisos"]==0)
			{
				$dias = 5;
			}
			else
			{
				$dias = $reg_verifica["flag_numero_avisos"]*7;
			}
			
			//principal
			if(!empty($array_email[$reg_verifica["id_cod_coord"]]))
			{
				$params['emails']['to'][] = array('email' => $array_email[$reg_verifica["id_cod_coord"]], 'nome' => $array_func[$reg_verifica["id_cod_coord"]]);
			}
			
			if(!empty($array_email[$reg_verifica["id_coord_aux"]]))
			{
				$params['emails']['to'][] = array('email' => $array_email[$reg_verifica["id_coord_aux"]], 'nome' => $array_func[$reg_verifica["id_coord_aux"]]);
			}
		
			//Adiciona a mensagem do corpo do e-mail
			$Body = "<html>";
			$Body .= "<body style='font: 11pt Arial'><p>O seguinte arquivo do pacote teve emissão em ".mysql_php($reg_verifica["data_emissao_arquivo"]) ." e está pendente a mais de ".$dias." dias";
			$Body .= "<div id='pacote'>Pacote: ".sprintf("%03d",$reg_verifica["numero_pacote"]) . "-" . $reg_verifica["os"] . "</div>";
			$Body .= "<div id='div_numint'>Número Interno: ". PREFIXO_DOC_GED . sprintf("%05d",$reg_verifica["os"]) . "-" . $reg_verifica["sequencia"] . "</div>";
			$Body .= "<div id='div_numcli'>Número Cliente: " . $reg_verifica["numero_cliente"] . "</div>";
			$Body .= "<div id='div_disc'>Disciplina: " . $reg_verifica["setor"] . "</div>";
			$Body .= "<div id='div_revisao'>Revisão / Versão: " . $reg_verifica["revisao_documento"] . "." . $reg_verifica["versao_documento"] . "</div>";
			$Body .= "<div id='div_status'>status da Emissão: " . $reg_verifica["emissao"]. "</div>";
			$Body .= "<div id='div_data_sol'>data da solicitação do Pacote: " . mysql_php($reg_verifica["data"]) . "</div>";
			$Body .= "<div id='div_data_ret'>data programada de retorno: " . $data_retorno . "</div>";
			$Body .= "<div id='div_obs'>data prevista para o próximo aviso: ".checaDiasUteis(date("d/m/Y"),$avisos+7,$ret,"sum")."</div>";
			
			$Body .= "</body></html>";
			
			$mail = new email($params);
			
			$mail->montaCorpoEmail($Body);
			
			//Envia o e-mail
			if(!$mail->Send())
			{
				echo ('Erro ao enviar e-mail! '.$mail->ErrorInfo);
			}
		
			$mail->ClearAddresses();			
		}	
	}
}

//FINANCEIRO
function verifica_docs_fin()
{
	//Função que verifica se o colaborador anexou os 
	//documentos da competencia anterior ao mes corrente

	$db = new banco_dados();

	$data_per = calcula_data(date('d/m/Y'), "sub", "month", 1);
	
	$data_period = explode("/",$data_per);	
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios, ".DATABASE.".fechamento_folha ";
	$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND fechamento_folha.periodo LIKE '".$data_period[2]."-".$data_period[1]."%' ";
	$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	$sql .= "ORDER BY funcionarios.funcionario ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}
	
	$array_fech = $db->array_select;

	if($db->numero_registros>0)
	{
		foreach($array_fech as $regs)
		{
			$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos ";
			$sql .= "WHERE fechamento_documentos.id_fechamento = '".$regs["id_fechamento"]."' ";
			$sql .= "AND fechamento_documentos.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);		
			}

			if($db->numero_registros==0)
			{
				$txt = "<B><FONT FACE=ARIAL COLOR=BLUE>ANEXOS DE COMPROVANTES</FONT></B><BR>";
				$txt .= "Caro colaborador ".$regs["funcionario"]."<br>";
				$txt .= "Foi detectado a ausência dos comprovantes de impostos referentes a competência: ".$data_period[1]."/".$data_period[2]."<br><br>";
				$txt .= "Favor providenciar os comprovantes e anexá-los ao sistema. <br><br><br>";
				$txt .= "ERP - Fechamento<br><br>";
				$txt .= "Desconsiderar caso já tenha anexado os documentos necessários<br><br><br>";
				$txt .= "E-mail automático, favor não responder este e-mail.";
			
				$params = array();
		
				$params['from']		= "sistema@dominio.com.br";
				
				$params['from_name']= "SISTEMA DE COMPROVANTES DE IMPOSTOS";
				
				$params['subject'] 	= 'AUSÊNCIA DOS COMPROVANTES DE IMPOSTOS';
				
				if(!empty($regs["email"]))
				{
					$params['emails']['to'][] = array('email' => $regs["email"], 'nome' => $regs["funcionario"]);
				}
				
				$mail = new email($params);
				
				$mail->montaCorpoEmail($txt);
				
				if(!$mail->Send())
				{
					echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
				}
				
				$mail->ClearAddresses();
			}
		}
	}
}

//Função para enviar aviso ao RH quando os colaboradores estiverem para completar 45 e 90 dias
//De acordo com o pedido do RH
function verifica_funcionario_44_89_dias()
{
	$db = new banco_dados();
	
	$sql = "SELECT funcionario, data_inicio, DATEDIFF(SUBSTRING(NOW(),1,10), data_inicio) dias FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
  	$sql .= "AND DATEDIFF(SUBSTRING(NOW(),1,10), data_inicio) IN('44','89') ";
	$sql .= "ORDER BY dias ";
	
	$params = array();
	
	$corpoEmail = '';
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);		
	}
	
	foreach($db->array_select as $reg)
	{
		$dias = $reg['dias'];
		
		$corpoEmail = "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">AVISO ".$dias." DIAS</span><br><br><br>";
		$corpoEmail .= "Nome:&nbsp;<strong>".$reg["funcionario"]."</strong><br>";
		$corpoEmail .= "data inicio:&nbsp;<strong>".mysql_php($reg['data_inicio'])."</strong><br>";					
		
		$params['from']	= "empresa@dominio.com.br";
		
		$params['from_name'] = "Recursos Humanos - Avaliação Experiência";
		
		$params['subject'] = "AVISO ".$dias." - ".trim($reg['funcionario']);
		
		$mail = new email($params, 'aviso_44_89');
		
		$mail->montaCorpoEmail($corpoEmail);
	
		if(!$mail->Send())
		{
			echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
		}
		
		$mail->ClearAddresses();		
	}	
}

/**
 * Função que verifica os funcionários que receberam treinamentos há 30 dias para
 * que seja feita a avaliação do mesmo
 */
function avaliacao_treinamento_30_dias()
{
    $db = new banco_dados();
    
    $sql = "SELECT rtc_data_treinamento, DATEDIFF(SUBSTRING(NOW(),1,10), rtc_data_treinamento) dif, funcionario, email, treinamento, rtc_situacao, rti_renovar
				FROM ".DATABASE.".rh_treinamentos_cabecalho
				JOIN (SELECT rti_id_funcionario, rti_rtc_id, rti_renovar FROM ".DATABASE.".rh_treinamentos_itens WHERE rh_treinamentos_itens.reg_del = 0) itens ON rtc_id = rti_rtc_id
				JOIN (SELECT id_rh_treinamento, treinamento FROM ".DATABASE.".rh_treinamentos WHERE rh_treinamentos.reg_del = 0) treinamentos ON id_rh_treinamento = rtc_id_treinamento
				JOIN (SELECT id_funcionario, funcionario, email FROM ".DATABASE.".funcionarios
				JOIN (SELECT id_funcionario id_funcionario, email FROM ".DATABASE.".usuarios WHERE usuarios.reg_del = 0) usuarios ON id_funcionario = id_funcionario
				WHERE situacao = 'ATIVO' AND funcionarios.reg_del = 0) funcs ON id_funcionario = rti_id_funcionario
				WHERE rtc_data_vencimento >= '2017-01-01'
				AND rh_treinamentos_cabecalho.reg_del = 0
				AND rti_renovar = 1
				AND rtc_situacao IN(1,2,4)
				AND DATEDIFF(SUBSTRING(NOW(),1,10), rtc_data_treinamento) = 30 ORDER BY rtc_data_treinamento DESC";
    
    $params = array();
    
    $corpoEmail = '';
    
    $db->select($sql, 'MYSQL',true);
    
    if($db->erro!='')
    {
        die($db->erro);
    }
    
    foreach($db->array_select as $reg)
    {
        $dias = $reg['dif'];
        
        $corpoEmail = "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">AVALIAÇÃO DE TREINAMENTO 30 DIAS</span><br><br><br>";
        $corpoEmail .= "Nome:&nbsp;<strong>".$reg['funcionario']."</strong><br>";
        $corpoEmail .= "Treinamento:&nbsp;<strong>".$reg['treinamento']."</strong><br>";
        $corpoEmail .= "data Treinamento:&nbsp;<strong>".mysql_php($reg['rtc_data_treinamento'])."</strong><br>";
        
        $params['from']	= "empresa@dominio.com.br";
        
        $params['from_name'] = "Recursos Humanos - Treinamentos";
        
        $mail = new email($params, 'aviso_treinamento_30_dias');
        
        $mail->montaCorpoEmail($corpoEmail);
        
        if(!$mail->Send())
        {
            echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
        }
    }	
}

/**
 * Função que verifica os estagiários que receberam treinamentos há 179 dias para
 * que seja feita a avaliação do mesmo
 */
function avaliacao_estagiario_179_dias()
{
	$db = new banco_dados();
	
	$sql = "SELECT DISTINCT DATEDIFF(SUBSTRING(NOW(),1,10), data_inicio) dif, funcionario, email,  tipo_contrato, id_funcionario, funcionario, email,  tipo_contrato
			FROM ".DATABASE.".funcionarios
				JOIN (
					SELECT id_funcionario id_funcionario, email FROM ".DATABASE.".usuarios WHERE usuarios.reg_del = 0 
				) usuarios ON id_funcionario = id_funcionario
				JOIN(
					SELECT id_funcionario salarioFuncionario,  tipo_contrato FROM ".DATABASE.".salarios WHERE salarios.reg_del = 0 
				) salarios
				ON salarioFuncionario = id_funcionario
				WHERE funcionarios.situacao = 'ATIVO'
				AND salarios. tipo_contrato = 'EST'
				AND funcionarios.reg_del = 0 
			AND DATEDIFF(SUBSTRING(NOW(),1,10), data_inicio) = 740
			ORDER BY data_inicio DESC ";
	
	$params = array();
	
	$corpoEmail = '';
	
	$db->select($sql, 'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);		
	}
	
	foreach($db->array_select as $reg)
	{
		$dias = $reg['dif'];
		
		$corpoEmail = "Favor, realizar a <span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">AVALIAÇÃO DO(A) ESTAGIÁRIO(A) ".$reg['funcionario']."</span><br />";
		
		$params['from']	= "empresa@dominio.com.br";
		$params['from_name'] = "Recursos Humanos - Avaliação Estágio";
		$params['subject'] = "AVALIAÇÃO DO<sup>a</sup> ESTAGIÁRIO<sup>a</sup> ".$reg['funcionario'];
		
		$mail = new email($params, 'aviso_estagio_180_dias');
		
		$mail->montaCorpoEmail($corpoEmail);
	
		if(!$mail->Send())
		{
			echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
		}	
	}	
}

function fim_contrato_proximo()
{
	$db = new banco_dados();
	
    $sql = "SELECT flt_id_funcionario, flt_id_local, flt_inicio, flt_fim, flt_atual, funcionario, descricao, tipo_empresa
			FROM 
				".DATABASE.".funcionario_x_local_trabalho
				JOIN ".DATABASE.".funcionarios ON id_funcionario = flt_id_funcionario
				JOIN ".DATABASE.".local ON id_local = flt_id_local
			WHERE
				funcionario_x_local_trabalho.reg_del = 0
				AND funcionarios.reg_del = 0 
				AND local.reg_del = 0
				AND funcionario_x_local_trabalho.flt_atual = 1
				AND (
						(
							tipo_empresa > 0 AND 
							DATEDIFF(SUBSTRING(NOW(),1,10), flt_fim) BETWEEN -15 AND 0
						)
						OR
						(
							tipo_empresa = 0 AND 
							DATEDIFF(SUBSTRING(NOW(),1,10), flt_fim) BETWEEN -40 AND 0
						)
					)";
    
    $db->select($sql, 'MYSQL',true);
    
    if($db->erro!='')
    {
        die($db->erro);
    }
    
    foreach($db->array_select as $reg)
    {
        $dias = $reg['dif'];
        
        $corpoEmail = "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">Final contrato colaborador</span><br><br><br>";
        $corpoEmail .= "Nome:&nbsp;<strong>".$reg['funcionario']."</strong><br>";
        $corpoEmail .= "data Fim:&nbsp;<strong>".mysql_php($reg['flt_fim'])."</strong>";
        
        $params['from']	= "empresa@dominio.com.br";
        
        $params['from_name'] = "Recursos Humanos";
        
        $params['subject'] = "Final contrato colaborador";
        
        $mail = new email($params, 'fim_permanencia_colaborador_cliente');
        
        $mail->montaCorpoEmail($corpoEmail);
        
        if(!$mail->Send())
        {
            echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
        }
    }
}

/**
 * Função que verifica se a os foi finalizada:
 * Todas as faturas recebidas
 */
function oss_finalizadas()
{
    $db = new banco_dados();
    
    $sql = "SELECT a.id_bms_pedido, b.os, m.data as dataMedicao, m.numero_nf, d.ordem_servico_status
			FROM
				".DATABASE.".bms_pedidos_finalizados a
				JOIN ".DATABASE.".bms_pedido b ON b.id_bms_pedido = a.id_bms_pedido AND b.reg_del = 0
				JOIN ".DATABASE.".bms_medicao m ON m.id_bms_pedido = a.id_bms_pedido AND m.reg_del = 0
				JOIN ".DATABASE.".ordem_servico c ON c.id_os = a.id_os AND c.reg_del = 0
				JOIN ".DATABASE.".ordem_servico_status d ON d.id_os_status = c.id_os_status AND d.fase_protheus IN('15','09') AND d.reg_del = 0
			WHERE 
				a.reg_del = 0
			ORDER BY
				b.os";
	    
    $arrOs = array();
    $arrInvalidos = array();
    $cont = 0;
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$arrOs, &$arrInvalidos, &$cont){
        if (!key_exists($reg['os'], $arrInvalidos))
        {
            $notas = explode(' ', $reg['numero_nf']);
            
            //array de controle de os's que deverao ser ignoradas
            $arrOs[$reg['os']]['datas'][$reg['dataMedicao']] = implode(',', array_filter($notas, 
                                                                    function($val){
                                                                        return is_numeric($val);
                                                                    }));
            
            //array criado para controle e retorno na parte do mssql
            $arrOs[$reg['os']]['notas'][] = $notas;
            $arrOs[$reg['os']]['status'] = $reg['ordem_servico_status'];
            
            if (empty($arrOs[$reg['os']]['datas'][$reg['dataMedicao']]))
                $arrInvalidos[$reg['os']] = true;
            
            $cont++;
        }
    });
        
    //neste trecho fazemos uma consulta por projeto, trazendo todas as notas e comparando com as notas já existentes no bms
    //assim, sabemos que se o indice estiver vazio, o projeto ainda nao foi recebido 100%
    foreach($arrOs as $os => $datas)
    {
        if (key_exists($os, $arrInvalidos))
        {
            unset($arrOs[$os]);
            continue;
        }
        
        $notas = implode(',', $datas['datas']);
        $arrOs[$os]['notas'] = array_flip($datas['notas']);
		
		/*
        $sql = "SELECT CONVERT(INT, C6_NOTA) C6_NOTA, E1_BAIXA, CONVERT(INT, C6_PROJPMS) C6_PROJPMS FROM SC6010 
                JOIN SE1010 ON SE1010.D_E_L_E_T_ = '' AND E1_TIPO = 'NF' AND E1_BAIXA != '' AND E1_NUM = C6_NOTA
                WHERE SC6010.D_E_L_E_T_ = '' AND C6_NOTA IN(".$notas.") AND C6_PROJPMS = '".sprintf('%010d', $os)."';";
        
        $db->select($sql, 'MSSQL', function($reg, $i) use(&$arrOs){
            $arrOs[$reg['C6_PROJPMS']]['notas'][$reg['C6_NOTA']] = 'OK';
        });
        
        if (!$db->numero_registros_ms)
        {
            unset($arrOs[$os]);
		}
		
		*/
    }
    
    $params = array();
    
    $corpoEmail =   "<p><b>Lista de OS's finalizadas (Todas as notas recebidas no Protheus)</b></p>";
    
    foreach($arrOs as $os => $reg)
    {
        $corpoEmail .= "<p>".sprintf('%05d', $os)." (".$reg['status'].")</p>";
    }
    
    $params['from']	= "tecnologia@dominio.com.br";
    $params['from_name'] = "BMS - OS's finalizadas";
    $params['subject'] = "Lista de OS's finalizadas";
    
    if (HOST != 'localhost')
    {
        $mail = new email($params, 'oss_finalizadas');
        
        $mail->montaCorpoEmail($corpoEmail);
        
        if(!$mail->Send())
        {
            echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
        }
    }
    else
    {
        print_r($corpoEmail);
    }
}

function encerrar_chamados()
{
    $db = new banco_dados();
    
    //ATENÇÃO, RETIRAR O STATUS 3 APÓS OS TESTES E VOLTAR >= 4 NO where
    $sql = "SELECT 
				MAX(i_data_hora) iDataHora, i_status status, c_area area, c_telefone, c_cod_funcionario, c_descricao, c_id,
				datediff(now(), i_data_hora), MAX(c_data_abertura) cDataHora
			FROM 
				".DATABASE.".interacoes
				JOIN ".DATABASE.".chamados ON c_id = i_chamado AND chamados.reg_del = 0
			WHERE
				interacoes.reg_del = 0 AND i_status IN(4,10) AND i_ultima = 1 AND datediff(now(), i_data_hora) >= 2
			GROUP BY
				i_status, c_area, c_telefone, c_cod_funcionario, c_descricao, c_id";
    
    $db->select($sql, 'MYSQL', true);
	
	$chamadosVencidos = $db->array_select;
        
    if($db->erro!='')
    {
        die($db->erro);
    }
    
    foreach($chamadosVencidos as $reg)
    {
        $dataInteracao = date('Y-m-d H:i:s');
        
        $usql = "UPDATE ".DATABASE.".interacoes SET i_ultima = 0 WHERE i_chamado = ".$reg['c_id'];
		
		$db->update($usql, 'MYSQL');
        
        $isql = "INSERT INTO ".DATABASE.".interacoes (i_chamado, i_cod_funcionario, i_status, i_descricao, i_ultima, i_data_hora) VALUES ";
        $isql .= "('".$reg['c_id']."', '".$reg['c_cod_funcionario']."', 7, 'ENCERRADO POR FALTA DE INTERACAO DO USUARIO', '1', '".$dataInteracao."')";
		
		$db->insert($isql, 'MYSQL');
        
        $usql = "UPDATE ".DATABASE.".chamados SET c_status = 7 WHERE c_id = ".$reg['c_id'];
		
		$db->update($usql, 'MYSQL');
        
        //Buscando dados do chamado, solicitante, interações e atendente
        $sql = "SELECT
                        f.funcionario, email, i_descricao, i_data_hora, f2.funcionario nomeInteracao, s.descricao, s2.descricao statusChamado, c_descricao, u.login
                    FROM
						".DATABASE.".chamados c
                        JOIN ".DATABASE.".interacoes i ON i_chamado = c_id AND i.reg_del = 0
                    	JOIN ".DATABASE.".funcionarios f ON f.reg_del = 0 AND f.id_funcionario = c.c_cod_funcionario
                        JOIN ".DATABASE.".usuarios u ON u.reg_del = 0 AND u.id_funcionario = f.id_funcionario
                        JOIN ".DATABASE.".funcionarios f2 ON f2.reg_del = 0 AND f2.id_funcionario = i_cod_funcionario
                        JOIN ".DATABASE.".status s ON s.id_status = i_status AND s.reg_del = 0
                        JOIN ".DATABASE.".status s2 ON s2.id_status = c_status AND s2.reg_del = 0
                    WHERE
                        c.reg_del = 0
                        AND c.c_id = ".$reg['c_id']."
                    ORDER BY
	                    i_id DESC";
        
        $db->select($sql, 'MYSQL', true);
     
        $nomeSolicitante = $db->array_select[0]['login'];
        $emailSolicitante = $db->array_select[0]['email'];
        
        $subject = 'Nova interacao para o chamado'.$reg['c_id'];
        
        $body  = '<b>Numero Chamado: </b>'.$reg['c_id'].'<br /><hr />';
        $body .= '<br />';
        
        foreach($db->array_select as $interacao)
        {
            $body .= '<b>data: </b>'.mysql_php(substr($reg['cDataHora'],0,10)).'<br />';
            $body .= '<b>Autor da Interacao: </b>'.$interacao['nomeInteracao'].'<br />';
            $body .= '<b>descricao da interacao: </b>'.$interacao['i_descricao'].'<br />';
            $body .= '<b>status: </b>'.$interacao['descricao'].'<br /><hr />';
        }
        
        if (!empty($anexo)) {
            $body .= '<br /><b>Obs.: Interacao com Anexo</b>';
        }
        
        $body .= '<b>status do Chamado: </b>'.$db->array_select[0]['statusChamado'].'<br />';
        $body .= '<b>Solicitante: </b>'.$nomeSolicitante.'<br />';
        $body .= '<b>descricao: </b>'.$db->array_select[0]['c_descricao'].'<br />';
        
        $params = array();
        $params['emails']['to'][] = array('email' => $emailSolicitante, 'nome' => $nomeSolicitante);
        $params['emails']['to'][] = array('email' => 'suporte@dominio.com.br', 'nome' => 'Suporte Técnico');
        $params['subject'] = $subject;
        
        $mail = new email($params);
        $mail->montaCorpoEmail($body);
        
        if (!$mail->send())
            echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
        
        $mail->clearAddresses();
    }
}

//APROVA AUTOMATICAMENTE AS SOLICITAÇÕES DE ALTERAÇÃO DE ESCOPO APROVADAS PELA SUPERVISÃO
function aprova_solicitacoes_escopo()
{
    $db = new banco_dados();
	
	//Cria o timestamp. Formato: 0,0,0,mês,dia,ano
	$data_stamp = mktime(0,0,0,date('m'),date('d'),date('Y'));

	//Referencia a data com o timestamp	
	$data_registro = getdate($data_stamp);
	
	//Pega o dia da semana: retorna 0-6 (de Domingo=0 a Sábado=6)	
	$dia_semana = $data_registro["wday"];
	
	//se durante a semana, roda a rotina
	if($dia_semana>0 && $dia_semana<6)    
	{
		//FUNCIONARIOS
		$sql = "SELECT funcionarios.id_funcionario, funcionario, nivel_atuacao, setores.abreviacao, email FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".usuarios ";
		$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND funcionarios.id_setor = setores.id_setor ";
		$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			echo $db->erro;
		}
	
		foreach($db->array_select as $regs)
		{
			$array_func[$regs["id_funcionario"]] = $regs["funcionario"];
			$array_email[$regs["id_funcionario"]] = $regs["email"];
			$array_status[$regs["id_funcionario"]][$regs["abreviacao"]] = $regs["nivel_atuacao"];
		}
	
		//seleciona formato
		$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
		$sql .= "WHERE formatos.reg_del = 0 ";
	
		$db->select($sql,'MYSQL',true);
			
		if($db->erro!='')
		{
			echo $db->erro;
		}
		
		foreach($db->array_select as $regs)
		{
			$array_formato[$regs["id_formato"]] = $regs["formato"];
		}
		
		$sql = "SELECT id_solicitacao_motivo, motivo_solicitacao FROM ".DATABASE.".solicitacao_hora_motivos ";
		$sql .= "WHERE solicitacao_hora_motivos.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			echo $db->erro;
		}
		
		foreach ($db->array_select as $cont1)
		{
			$array_motivo[$cont1["id_solicitacao_motivo"]] = $cont1["motivo_solicitacao"];
		}			
	
		$sql = "SELECT *, setores.abreviacao AS CODSETOR FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios, ".DATABASE.".solicitacao_hora, ".DATABASE.".atividades, ".DATABASE.".setores, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE solicitacao_hora.id_solicitante = funcionarios.id_funcionario ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND solicitacao_hora.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND atividades.cod = setores.id_setor ";
		$sql .= "AND solicitacao_hora.id_os = ordem_servico.id_os ";
		$sql .= "AND solicitacao_hora.id_atividade = atividades.id_atividade ";
		$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
		$sql .= "AND solicitacao_hora.id_aprovacao = 3 "; //APROVADO SUPERVISÃO
		$sql .= "AND solicitacao_hora.data_aprovacao_supervisao <= '".php_mysql(checaDiasUteis(date("d/m/Y"),2,$ret,"sub"))."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			echo $db->erro;
		}
		
		$array_solicitacao = $db->array_select;
		
		foreach($array_solicitacao as $cont)
		{
			//Atividade
			$sql = "SELECT * FROM ".DATABASE.".atividades ";
			$sql .= "WHERE atividades.id_atividade = '".$cont["id_atividade"]."' ";
			$sql .= "AND atividades.reg_del = 0 ";
		
			$db->select($sql,'MYSQL',true);
		
			if($db->erro!='')
			{
				echo $db->erro;
			}	
			
			$reg_atividade = $db->array_select[0];			
			
			/*
			//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
			$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont["os"])."' ";
			$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
			$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
			$sql .= "AND AF9010.AF9_COMPOS = '".$cont["codigo"]."' ";
			
			if(!empty($cont["tarefa"]))
			{
				$sql .= "AND AF9010.AF9_TAREFA = '".$cont["tarefa"]."' ";
			}
						
			$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
			$sql .= "ORDER BY AF9010.AF9_TAREFA ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				echo $db->erro;
			}
			
			$reg_atv = $db->array_select[0];
			
			*/
			
			$params = array();
			
			//Concatena mensagem 
			$texto = "<B><FONT FACE=ARIAL COLOR=RED>APROVA&Ccedil;&Atilde;O DE ALTERA&Ccedil;&Atilde;O DE ESCOPO - N&deg;: ".$cont["id_solicitacao_hora"]."</FONT></B><BR><br>";
			$texto .= "<FONT FACE=ARIAL COLOR=RED>Motivo solicita&ccedil;&atilde;o: ".$array_motivo[$cont["id_motivo_solicitacao"]]."</FONT><br><br>";
			$texto .= "O colaborador ".$cont["funcionario"]." solicitou altera&ccedil;&atilde;o de escopo.<br><br>";
			$texto .= "Aprovada por SISTEMA em ".date('d/m/Y')."<br><br>";
			$texto .= "Solicitada em: ".mysql_php($cont["data_solicitacao"])."<br>";
			$texto .= "Para o projeto: ".sprintf("%010d",$cont["os"])."<br>";
			
			//horas insuficientes		
			//if($cont["id_motivo_solicitacao"]==2)
			//{
			//	$texto .= "na tarefa: ".$reg_atv["AF9_TAREFA"]." - ".$reg_atv["AF9_COMPOS"]." - ".$reg_atv["AF9_DESCRI"]."<br><br>";
			//}
			//else
			//{
				$texto .= "na tarefa: ".$reg_atividade["codigo"]." - ".$reg_atividade["descricao"]."<br><br>";						
			//}
			
			$texto .= "Motivo aprova&ccedil;&atilde;o: APROVA&Ccedil;&Atilde;O AUTOM&Aacute;TICA<br><br>";
			
			$texto .= "Total de horas: ".number_format($cont["total_horas"],2,",","")."<br><br>";
			
			$params['emails']['to'][] = array('email' => 'planejamento@dominio.com.br', 'nome' => 'Grupo Planejamento');			
			
			$texto .= "Observacao: ".maiusculas(addslashes($cont["observacao"]))."<br><br><br>";
			
			$params['fromNameCompl'] = ' - Solicita&ccedil;&atilde;o de altera&ccedil;&atilde;o de escopo - APROVADO';
			$params['subject'] = 'APROVAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: '.$cont["id_solicitacao_hora"];
			
			$mail = new email($params);
			
			$mail->montaCorpoEmail($texto);
			
			if(!$mail->Send())
			{
				echo "Horas aprovadas, porém, houve uma falha ao tentar enviar o e-mail ao Planejamento!";
			}		
						
			$usql = "UPDATE ".DATABASE.".solicitacao_hora SET ";
			$usql .= "id_aprovador_coord = '" . $cont["id_cod_coord"] ."', ";
			$usql .= "data_aprovacao_coord = '". date('Y-m-d') ."', ";
			$usql .= "motivo_coord = 'APROVAÇÃO AUTOMÁTICA')', ";				
			$usql .= "tipo_aprovacao = 2, "; //se aprovado pelo sistema
			$usql .= "id_aprovacao = 1 ";			
			$usql .= "WHERE id_solicitacao_hora = '". $cont["id_solicitacao_hora"] ."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
			
			if ($db->erro != '')
			{
				echo $db->erro;
			}
		}
	}
}

//VERIFICAÇÃO DE PEDIDO SEM ANEXO NO SISTEMA BMS A PARTIR DE 20/02/2018
function pedidos_sem_anexo()
{
    $db = new banco_dados();
    
    $body  = '<b>solicitacao_documentos sem anexo: </b><hr />';
    $body .= '<br />';
    $body .= '<table><tr><th>Pedido</th><th>data</th></tr>';
    
    $sql = "SELECT 
				bms_pedido.os, bms_pedido.data_pedido
			FROM
				".DATABASE.".ordem_servico 
				JOIN ".DATABASE.".ordem_servico_status ON ordem_servico_status.id_os_status = ordem_servico.id_os_status AND ordem_servico_status.reg_del = 0 
				JOIN ".DATABASE.".empresas ON empresas.id_empresa_erp = ordem_servico.id_empresa_erp AND empresas.reg_del = 0 
				JOIN ".DATABASE.".bms_pedido ON bms_pedido.os = os.os AND bms_pedido.reg_del = 0
				LEFT JOIN(
					SELECT
						id_bms_pedido idPedido, SUM(progresso_medido) totalMedido, MAX(numero_nf) nf, SUM(dif_faturado) difFaturado
					FROM
						".DATABASE.".bms_medicao
					WHERE
						bms_medicao.reg_del = 0
					GROUP BY
						bms_medicao.id_bms_pedido
	            ) medicao
	            ON idPedido = id_bms_pedido
			WHERE 
				(data_pedido >= '2018-02-20') AND ordem_servico.reg_del = 0
                AND arquivo_pedido IS NULL
                AND totalMedido < 50
			GROUP BY 
				os.os, descricao, bms_pedido.id_bms_pedido, data_pedido, os_status
			ORDER BY os.os";
    
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$body){
        $body .= '<tr><td><b>'.sprintf('%05d', $reg['os']).'</b></td><td>'.mysql_php($reg['data_pedido']).'</td></tr>';
    });    
    
    $body .= '</table>';
    
    $params['from']	= "tecnologia@dominio.com.br";
    $params['from_name'] = "BMS - solicitacao_documentos sem anexo";
    $params['subject'] = "BMS - Lista de pedidos sem anexo";
    
    if ($db->numero_registros > 0)
    {
        if (HOST != 'localhost')
        {
            $mail = new email($params, 'bms_pedidos_sem_anexo');
            
            $mail->montaCorpoEmail($body);
            
            if(!$mail->Send())
            {
                echo('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
            }
        }
        else
        {
            print_r($body);
        }
    }
}

vencimento_avaliacao_eficacia();

//PLANEJAMENTO
//dispara toda segunda-feira
if (date('w') == 1)
{
     oss_finalizadas();
}

//TI
vencimento_senha();

//encerra os chamados que estão pendentes de retorno de usuário a mais de 4 dias
encerrar_chamados();

//rotinas_manutencao();

//RH
vencimento_exame();
vencimento_integracao();
vencimento_treinamento();
vencimento_habilitacao();
vencimento_controles_sgi();
verifica_funcionario_44_89_dias();
avaliacao_treinamento_30_dias();
avaliacao_estagiario_179_dias();
fim_contrato_proximo();

//ARQUIVO TECNICO
verifica_pacotes();
//verifica_devolucao();
verifica_retorno();

//FINANCEIRO
//dispara somente no ultimo dia util do mes
if(ult_dia_mes(date('d/m/Y'))==date('d/m/Y'))
{
    verifica_docs_fin();
}


aprova_solicitacoes_escopo();

pedidos_sem_anexo();

?>