<?php
/*
		Formulário de Fechamento da Folha 	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/fechamentofolha.php
		
		Versão 0 --> VERSÃO INICIAL - 10/03/2006
		Versão 1 --> Alterações no agrupamento por período
		Versão 2 --> Pequenas alterações na funcionalidade
		Versão 3 --> Atualização Carlos
		
		1ª atualização: 03/04/2006

		2ª atualização: 05/07/2006
		
		3ª atualização: 18/01/2008
		
		Atualização: 2007
		
		#Alteração 2: 
		Adicionado campo "periodo" na tabela "fechamento_folha"
		Na ação "editar", adicionado linha do campo "periodo" na string de "INSERT"
		Tabela HTML inferior passou a filtrar pelo campo "periodo"
		
		#Alteração 3:
		Alterada a ordem do "dropdown box" de Período.
		Mudanças no checkbox de liberação.
		
		última atualização: 18/01/2008
		
		Versão 4 - atualização classe banco - 20/01/2015 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(36))
{
	nao_permitido();
}
	
$db = new banco_dados;
	
function insere($dados_form, $data_ini, $datafim)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
	$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND fechamento_folha.data_ini = '".$data_ini."' ";
	$sql .= "AND fechamento_folha.data_fim = '".$datafim."' ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		if($dados_form["chkf_".$reg["id_fechamento"]]==1)
		{
			$excessao = '1';							
		}
		else
		{
			$excessao = '0';
		}
		
		$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
		$usql .= "excessao = '" . $excessao . "' ";
		$usql .= "WHERE id_fechamento = '" . $reg["id_fechamento"] ."' ";
		
		$db->update($usql,'MYSQL');
		
	}
	
	return $resposta;
}

function envia_email($id_fechamento)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$envio = FALSE;
	
	$txt_apr = "";
	
	$txt_rep = "";
	
	$aprovados = "Os seguintes documentos anexados ao sistema foram aprovados:<br>";
	
	$n_aprovados = "Os seguintes documentos anexados ao sistema não foram aprovados:<br>";
	
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE fechamento_folha.id_fechamento = '".$id_fechamento."' ";
	$sql .= "AND fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$cont_fun = $db->array_select[0];
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos, ".DATABASE.".fechamento_tipos_tributos ";
	$sql .= "WHERE fechamento_documentos.id_fechamento = '".$id_fechamento."' ";
	$sql .= "AND fechamento_documentos.id_fechamento_tipos_tributos = fechamento_tipos_tributos.id_fechamento_tipos_tributos ";
	$sql .= "AND fechamento_documentos.envio_email = '0' ";
	$sql .= "AND fechamento_documentos.conferido IN ('1','2') ";
		
	$db->select($sql,'MYSQL',true);
	
	//se possuir registros, existe documentos anexados
	if($db->numero_registros>0)
	{
		foreach($db->array_select as $cont)
		{
			$competencia = $cont["competencia"];
			
			switch($cont["conferido"])
			{
				//aprovado
				case '1':
					$txt_apr .= $cont["fechamento_tipos_tributos"]."<br>";
				break;
				//rejeitado
				case '2':
					$txt_rep .= $cont["fechamento_tipos_tributos"]."<br>";
				break;
			}
			
			$usql = "UPDATE ".DATABASE.".fechamento_documentos SET ";
			$usql .= "envio_email = '1' ";
			$usql .= "WHERE fechamento_documentos.id_fechamento_docs = '".$cont["id_fechamento_docs"]."' ";
			
			$db->update($usql,'MYSQL');

		}	
		
		//Concatena mensagem de urgência
		$texto = "<B><FONT FACE=ARIAL COLOR=RED>OBRIGAÇÕES ACESSÓRIAS</FONT></B><BR><br><br>";
		
		$texto .= "Caro colaborador ".$cont_fun["funcionario"].",<br><br>";
		
		if($txt_apr!='')
		{
			$texto .= $aprovados;
			$texto .= $txt_apr."<br><br>";
		}
		
		if($txt_rep!='')
		{
			$texto .= $n_aprovados;
			$texto .= $txt_rep."<br><br>";
		}
		
		$texto .= "Competência: ".substr($competencia,0,2)."/".substr($competencia,2,4)."<br><br><br>";
		$texto .= "Em caso de dúvida, procurar o setor Financeiro.<br><br><br>";
	
		$envio = TRUE;
	}
	else
	{
		
		$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos, ".DATABASE.".fechamento_tipos_tributos ";
		$sql .= "WHERE fechamento_documentos.id_fechamento = '".$id_fechamento."' ";
		$sql .= "AND fechamento_documentos.id_fechamento_tipos_tributos = fechamento_tipos_tributos.id_fechamento_tipos_tributos ";
		$sql .= "AND fechamento_documentos.envio_email = '0' ";
			
		$db->select($sql,'MYSQL',true);
		
		//se não possui registros, não há documentos anexados
		if($db->numero_registros <= 0)
		{		
			$texto = "<B><FONT FACE=ARIAL COLOR=RED>OBRIGAÇÕES ACESSÓRIAS</FONT></B><BR><br><br>";
			
			$texto .= "Caro colaborador ".$cont_fun["funcionario"].",<br><br>";
			
			$texto .= "Favor anexar os documentos no sistema.<br><br>";
			
			$texto .= "Em caso de dúvida, procurar o setor ".DATABASE.".<br><br><br>";
			
			$envio = TRUE;
		}		
	}
	
	if($envio)
	{

		if(ENVIA_EMAIL)
		{

			$params 			= array();
			$params['from']		= "empresa@".DOMINIO;
			$params['from_name']= "Sistema ERP - OBRIGAÇÕES ACESSÓRIAS";
			$params['subject'] 	= "OBRIGAÇÕES ACESSÓRIAS";
			
			$params['emails']['to'][] = array('email' => $cont_fun["email"], 'nome' => $cont_fun["funcionario"]);
			
			$mail = new email($params, 'admissao_funcionario');
			
			$mail->montaCorpoEmail($texto);
			
			if(!$mail->Send())
			{
				$resposta->addAlert($mail->ErrorInfo);
			}
			else
			{
				$resposta->addAlert('E-mail enviado com sucesso.');	
			}
		}
		else 
		{
			$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 1);
		}
	}
	
	return $resposta;
}

if ($_POST["acao"]=="impostos")
{
	//Traz o periodo atual e estabelece o filtro.
	if($_POST["periodo"])
	{
		$filtro_datas = "AND fechamento_folha.periodo = '" . $_POST["periodo"] . "' ";
	}
	else
	{		
		$filtro_datas = "AND SUBSTRING(fechamento_folha.periodo,9,7) = '" . date("Y-m") . "' ";	
	}	
	
	//CALCULA OS IMPOSTOS DOS FECHAMENTOS DE CADA FUNCIONÁRIO, NO PERÍODO ATUAL
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
	$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= $filtro_datas;	
	$sql .= "ORDER BY fechamento_folha.id_fechamento ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_fechamento = $db->array_select;
	
	foreach($array_fechamento as $cont_fechamento)
	{	
		$svalor_total_empresa = 0;
		
		//Seleciona todos os funcionários que estão na mesma empresa do funcionário do fechamento.
		$sql = "SELECT * FROM ".DATABASE.".empresa_funcionarios, ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_empfunc = empresa_funcionarios.id_empfunc ";
		$sql .= "AND empresa_funcionarios.id_empfunc = '" . $cont_fechamento["id_empfunc"] . "' ";
	
		$db->select($sql,'MYSQL',true);
		
		$array_empresa = $db->array_select;
		
		foreach($array_empresa as $cont_func)
		{			
			//1) Pega o valor total do pagamento (Nota Fiscal normal) de cada Funcionário.
			//2) Checa se existem Notas de Ajuda de Custo, caso positivo soma seu valor ao valor total da empresa.
			$sql = "SELECT * FROM ".DATABASE.".fechamento_folha LEFT JOIN ".DATABASE.".nf_funcionarios ON ";
			$sql .= "(fechamento_folha.id_fechamento = nf_funcionarios.id_fechamento AND (nf_funcionarios.nf_ajuda_custo = 1 OR nf_funcionarios.nf_ajuda_custo = 2)) ";
			$sql .= "WHERE fechamento_folha.id_funcionario = '" . $cont_func["id_funcionario"] . "' ";
			$sql .= "AND fechamento_folha.reg_del = 0 ";
			$sql .= $filtro_datas;
			
			$db->select($sql,'MYSQL',true);
			
			$cont_fechamento_func = $db->array_select[0];
			
			if($cont_fechamento_func["valor_total"]<0)
			{
				$total_bruto = 0;
			}
			else
			{
				$total_bruto = $cont_fechamento_func["valor_total"];
			}
			
			$svalor_total_empresa = $svalor_total_empresa + $total_bruto + $cont_fechamento_func["nf_valor"];						
		}
				
		//Busca se a empresa do funcionário incide imposto ou não.
		$sql = "SELECT empresa_imposto FROM ".DATABASE.".empresa_funcionarios ";
		$sql .= "WHERE empresa_funcionarios.id_empfunc = '" . $cont_fechamento["id_empfunc"] . "' ";
		
		$db->select($sql,'MYSQL',true);
		
		$contempresafunc = $db->array_select[0];
		
		if($cont_fechamento["valor_total"]<0)
		{
			$svalor_pagamento_atual = 0;
		}
		else
		{
			$svalor_pagamento_atual = $cont_fechamento["valor_total"];
		}		
		
		//Reseta as variáveis
		$svalor_pis_cofins_csl = 0;
		$imposto_ir_empresa = 0;
		$imposto_ir_pessoa = 0;
		
		//Checa o valor total das notas da empresa.
		//Deduz IMPOSTOS, caso aplicável.
		if($contempresafunc["empresa_imposto"]=="1")
		{
			//PIS, COFINS, CSL
			if (($svalor_total_empresa) >= 5000)
			{				
				//Calcula o valor total dos tributos.
				$svalor_pis_cofins_csl = ($cont_fechamento["valor_total"] * 4.65)/100;
				//Deduz do pagamento.
				$svalor_pagamento_atual = $svalor_pagamento_atual - $svalor_pis_cofins_csl;			
			}
	
			//Calcula imposto IR - 1,5% do valor total.
			$imposto_ir_empresa = round((($svalor_total_empresa)*1.5)/100,2);	
			$imposto_ir_pessoa = round(($cont_fechamento["valor_total"]*1.5)/100,2);
			
			//IR: Se o da EMPRESA for maior que 10,00.
			if($imposto_ir_empresa>10)
			{
				//Deduz.
				$svalor_pagamento_atual = $svalor_pagamento_atual - $imposto_ir_pessoa;
			}
			else
			{
				//Náo há imposto a deduzir.
				$imposto_ir_pessoa=0;
			}	
		}			

		//Atualiza o registro com as informações.
		$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
		$usql .= "valor_imposto = '" . $imposto_ir_pessoa . "', ";
		$usql .= "valor_pcc = '" . $svalor_pis_cofins_csl . "', ";
		$usql .= "valor_pagamento = '" . $svalor_pagamento_atual . "' ";
		$usql .= "WHERE fechamento_folha.id_fechamento = '" . $cont_fechamento["id_fechamento"] . "' ";
		
		$db->update($usql,'MYSQL');	
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$medicao = 0;
	$valor_total = 0;
	$clt = 0;
	$mens = 0;
	$adicionais_dom = 0;
	$adicionais_sem = 0;
	$adicionais_sab = 0;
	$adicionais_not = 0;
	$adicionais_fer = 0;

	//Formata o valores para FLOAT
	$valor_outros_descontos = str_replace(",",".",str_replace(".","",$_POST["outros_descontos"]));
	$valor_outros_acrescimos = str_replace(",",".",str_replace(".","",$_POST["outros_acrescimos"]));
	$valor_diferencaclt_ferias = str_replace(",",".",str_replace(".","",$_POST["diferenca_clt_ferias"]));
	$valor_diferencaclt_rescisao = str_replace(",",".",str_replace(".","",$_POST["diferenca_clt_rescisao"]));
	$valor_ferias = str_replace(",",".",str_replace(".","",$_POST["ferias"]));
	$valor_fgts = str_replace(",",".",str_replace(".","",$_POST["fgts"])); 
	$valor_decimoterceiro = str_replace(",",".",str_replace(".","",$_POST["decimoterceiro"]));
	$valor_rescisao = str_replace(",",".",str_replace(".","",$_POST["rescisao"]));
	$salario_proporcional = str_replace(",",".",str_replace(".","",$_POST["salarioproporcional"]));
	
	//ADICIONADO POR CARLOS ABREU - 18/01/2008
	
	//obtem as horas do funcionario dentro de um periodo
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS HN, SUM(TIME_TO_SEC(hora_adicional)) AS HA, SUM(TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
	$sql .= "FROM ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE apontamento_horas.id_funcionario = '" . $_POST["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["data_fim"]) . "' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario ";
	
	$db->select($sql,'MYSQL',true);
		
	$conth = $db->array_select[0];
	
	$contrato = "";
	$tarifa_mens = 0;
	$tarifa_sal = 0;
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_extra ";
	$sql .= "WHERE fechamento_folha_extra.id_funcionario = '" . $_POST["id_funcionario"] . "' ";
	$sql .= "AND fechamento_folha_extra.data_ini = '".php_mysql($_POST["dataini"])."' ";
	$sql .= "ORDER BY id_fechamento_horaextra DESC LIMIT 1 ";
	
	$db->select($sql,'MYSQL',true);

	$cont2 = $db->array_select[0];	
	
	//obtem as horas do funcionario dentro de um periodo
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal))/3600 AS HN, SUM(TIME_TO_SEC(hora_adicional))/3600 AS HA, SUM(TIME_TO_SEC(hora_adicional_noturna))/3600 AS HAN ";
	$sql .= "FROM ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE apontamento_horas.id_funcionario = '" . $_POST["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["data_fim"]) . "' ";
	$sql .= "AND OS.id_os = apontamento_horas.id_os ";
	$sql .= "GROUP BY OS.id_os, apontamento_horas.data ";
	$sql .= "ORDER BY apontamento_horas.data ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;
	
	foreach ($array_horas as $cont)
	{		
		//Obtem o valor do salario na data
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $cont["id_funcionario"] . "' ";
		$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".str_replace("-","",$cont["data"])."' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";
		
		$db->select($sql,'MYSQL',true);
		
		$cont1 = $db->array_select[0];

		$tarifa_sal = $cont1["salario_clt"];
	
		//Modificado 29/02/2008 
		if($cont1[" tipo_contrato"]=='SC' || $cont1[" tipo_contrato"]=='SC+CLT')
		{			
			//Horas Extras NÃO
			if($_POST["horasextras"]=="0")
			{
				$valor_total += (($cont["HN"]+$cont["HA"]+$cont["HAN"]) * $cont1["salario_hora"]);
			}
			else
			{
				//Horas Extras SIM				
				//ADICIONADO POR CARLOS 12/02/2008
				//Verifica se a OS incide Horas Extras
				
				if($cont["hora_extra"])
				{					
					$valor_total += ($cont["HN"] * $cont1["salario_hora"]);
					
					//Explode a data no formato que vem do MySQL.
					$temp = explode("-",$cont["data"]);		
		
					$data_registro = getdate(mktime(0,0,0,$temp[1],$temp[2],$temp[0]));
					//Pega o dia da semana: retorna 0-6 (de Domingo=0 a Sábado=6)
											
					switch ($data_registro["wday"])
					{
						//Adicionais Domingo
						case 0:
							//Cria um array com as datas de feriado
							$array_ad_data_fer1 = explode(";",$cont2["ad_data_fer1"]);
													
							// Se a data do feriado coincidir com a data do banco de extra
							//if($cont2["ad_data_fer1"]==$cont["data"])  30/05/2008
							//Se a data do banco de extra estiver entre as datas de feriado
							if(in_array($cont["data"],$array_ad_data_fer1))
							{
								$adicionais_fer += ((($cont1["salario_hora"] * $cont2["ad_feriado_porc"])/100) + $cont1["salario_hora"]) * ($cont2["ad_feriado_horas"]/count($array_ad_data_fer1));
							}
							else
							{
								$adicionais_dom += ((($cont1["salario_hora"] * $cont2["domingo_porc"])/100) + $cont1["salario_hora"]) * ($cont["HA"]+$cont["HAN"]);
							}
							break;
						//Adicionais Sabado
						case 6:
							//Cria um array com as datas de feriado
							$array_ad_data_fer1 = explode(";",$cont2["ad_data_fer1"]);
													
							// Se a data do feriado coincidir com a data do banco de extra
							//if($cont2["ad_data_fer1"]==$cont["data"]) 30/05/2008
							//Se a data do banco de extra estiver entre as datas de feriado
							if(in_array($cont["data"],$array_ad_data_fer1))
							{
								$adicionais_fer += ((($cont1["salario_hora"] * $cont2["ad_feriado_porc"])/100) + $cont1["salario_hora"]) * ($cont2["ad_feriado_horas"]/count($array_ad_data_fer1));
							}
							else
							{
								$adicionais_sab += ((($cont1["salario_hora"] * $cont2["sabado_porc"])/100) + $cont1["salario_hora"]) * ($cont["HA"]+$cont["HAN"]);
							}
							break;
						//Adicionais Semana	
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						
							//Cria um array com as datas de feriado
							$array_ad_data_fer1 = explode(";",$cont2["ad_data_fer1"]);
													
							// Se a data do feriado coincidir com a data do banco de extra
							//if($cont2["ad_data_fer1"]==$cont["data"])  30/05/2008
							//Se a data do banco de extra estiver entre as datas de feriado
							if(in_array($cont["data"],$array_ad_data_fer1))
							{
								$adicionais_fer += ((($cont1["salario_hora"] * $cont2["ad_feriado_porc"])/100) + $cont1["salario_hora"]) * ($cont2["ad_feriado_horas"]/count($array_ad_data_fer1));
							}
							else
							{
								$adicionais_sem += ((($cont1["salario_hora"] * $cont2["semana_porc"])/100) + $cont1["salario_hora"]) * ($cont["HA"]+$cont["HAN"]);
							}
							break;
						
					}
					
					//Adicional Noturno
					
					$adicionais_not = $cont2["ad_noturno_horas"] * $cont1["salario_hora"] * ($cont2["ad_noturno_porc"]/100);																
		
				}
				else
				{
					$valor_total += (($cont["HN"]+$cont["HA"]+$cont["HAN"]) * $cont1["salario_hora"]);
				}
			}
		}		
		
		if($cont1[" tipo_contrato"]=='CLT' || $cont1[" tipo_contrato"]=='EST')
		{		
			if($_POST["salarioproporcional"]!="")
			{
				$valor_total = $salario_proporcional;
				$medicao = $salario_proporcional;	
			}
			else
			{
				$valor_total = $cont1["salario_clt"];
				$medicao = $cont1["salario_clt"];	
			}		
		}		
		
		$contrato = $cont1[" tipo_contrato"];
		
		if($cont1[" tipo_contrato"]=='SC+MENS' || $cont1[" tipo_contrato"]=='SC+CLT+MENS')
		{			
			$tarifa_mens = $cont1["salario_mensalista"];			
		}				
		
	}
	
	//Soma os adicionais ao valor total		
	$valor_total += ($adicionais_dom+$adicionais_sab+$adicionais_sem+$adicionais_not+$adicionais_fer);

	$medicao = $valor_total;
	
	if($contrato=='SC+MENS')
	{
		$valor_total = $tarifa_mens;
		$medicao = $tarifa_mens;			
	}
	else
	{
		if($contrato=="SC+CLT+MENS")
		{
			$valor_total = $tarifa_mens;
			$medicao = $tarifa_mens;
			if($_POST["salarioproporcional"]!="")
			{
				$clt = $salario_proporcional;
			}
			else
			{
				$clt = $tarifa_sal;
			}
		}
	}
	
	//Calcula descontos/acrescimos conforme contrato (válido contrato com CLT+...)
	if($_POST["ferias"]!="")
	{
		if($contrato=='SC+CLT')
		{
			$valor_total -= ($valor_diferencaclt_ferias + $valor_ferias + $valor_fgts + $valor_decimoterceiro);
		}
		else
		{
			if($contrato=='SC+CLT+MENS')
			{
				$valor_total -= ($valor_diferencaclt_ferias + $valor_ferias + $valor_fgts + $valor_decimoterceiro);
			}		
		}
	}
	elseif($_POST["rescisao"]!="")
	{
		if($contrato=='SC+CLT')
		{
			$valor_total -= ($valor_diferencaclt_rescisao + $valor_rescisao + $valor_fgts + $valor_decimoterceiro);
		}
		else
		{
			if($contrato=='SC+CLT+MENS')
			{
				$valor_total -= ($valor_diferencaclt_rescisao + $valor_rescisao + $valor_fgts + $valor_decimoterceiro);
			}		
		}
	}			
	else
	{		
		if($contrato=='SC+CLT')
		{
			if($_POST["salarioproporcional"]!="")
			{
				$salreg = $salario_proporcional;
			}
			else
			{
				$salreg = $tarifa_sal;
			}
		
			$valor_total -= ($salreg + $valor_decimoterceiro + $valor_fgts);
		}
		else
		{
			if($contrato=='SC+CLT+MENS')
			{
				$valor_total = $tarifa_mens - $clt - $valor_fgts - $valor_decimoterceiro;
			}
		
		}
	}	
	
	//Reseta a variável - Será calculada na ação = "impostos"
	$svalor_pagamento = 0;
	
	//MODO MANUAL DE CÁLCULO
	if($_POST["manual"]!="")
	{
		$medicao = str_replace(",",".",str_replace(".","",$_POST["manual"]));
		$valor_total = str_replace(",",".",str_replace(".","",$_POST["manual"]));
		$svalor_pagamento = str_replace(",",".",str_replace(".","",$_POST["manual"]));
		$descricao_manual = $_POST["descricao_manual"];
	}

	//Desconta o "OUTROS DESCONTOS"
	$valor_total -= $valor_outros_descontos;
	
	//Acrescenta o "OUTROS ACRÉSCIMOS"
	$valor_total += $valor_outros_acrescimos;
	
	//SQL para inserir os valores na tabela.
	$isql = "INSERT INTO ".DATABASE.".fechamento_folha ";
	$isql .= "(id_funcionario, id_salario, data_ini, data_fim, periodo, valor_ferias, valor_rescisao, valor_fgts, valor_decimo_terceiro, total_horas_normais, total_horas_adicionais, valor_medicao, valor_total, valor_imposto, valor_pagamento, valor_pcc, inclui_hora_extra, valor_proporcional, valor_diferenca_ferias, valor_diferenca_rescisao, valor_descontos, valor_acrescimos, observacao) ";
	$isql .= "VALUES ('". $_POST["id_funcionario"] ."', ";
	$isql .= "'" . $cont1["id_salario"]. "', ";
	$isql .= "'" . php_mysql($_POST["dataini"]) ."', ";
	$isql .= "'" . php_mysql($_POST["data_fim"]) ."', ";
	$isql .= "'" . substr(php_mysql($_POST["dataini"]),0,7) . "," . substr(php_mysql($_POST["data_fim"]),0,7) . "', ";
	$isql .= "'" . $valor_ferias ."', ";
	$isql .= "'" . $valor_rescisao ."', ";
	$isql .= "'" . $valor_fgts ."', ";
	$isql .= "'" . $valor_decimoterceiro ."', ";
	$isql .= "'" . sec_to_time($conth["HN"]) . "', ";
	$isql .= "'" . sec_to_time($conth["HA"]+$conth["HAN"]) . "', ";		
	$isql .= "'" . $medicao . "', ";
	$isql .= "'" . $valor_total . "', ";
	$isql .= "'" . $imposto_ir . "', ";
	$isql .= "'" . $svalor_pagamento . "', ";
	$isql .= "'" . $svalor_pis_cofins_csl . "', ";
	$isql .= "'" . $_POST["horasextras"] . "', ";
	$isql .= "'" . $sal_proporcional . "', ";
	$isql .= "'" . $valor_diferencaclt_ferias . "', ";
	$isql .= "'" . $valor_diferencaclt_rescisao . "', ";
	$isql .= "'" . $valor_outros_descontos . "', ";
	$isql .= "'" . $valor_outros_acrescimos . "', ";
	$isql .= "'" . $descricao_manual . "') ";
	
	$db->insert($isql,'MYSQL');
			
	$cod_fechamento = $db->insert_id;
	
	//Insere Nota Fiscal
	$isql = "INSERT INTO ".DATABASE.".nf_funcionarios ";
	$isql .= "(id_fechamento, nf_valor, nf_ajuda_custo) VALUES (";
	$isql .= "'" . $cod_fechamento . "', ";
	$isql .= "'" . $valor_total . "', ";
	$isql .= "'0') ";
	
	$db->insert($isql,'MYSQL');
	
	?>
	<script>
		alert('Fechamento de folha de funcionário inserido com sucesso.');
		location.href='<?= $_SERVER["PHP_SELF"] ?>?data_ini=<?= $_POST["dataini"] ?>&data_fin=<?= $_POST["data_fim"] ?>';
	</script>
	<?php
		
}

if ($_GET["acao"]=="excluir")
{
	//$dsql = "DELETE FROM ".DATABASE.".fechamento_folha WHERE fechamento_folha.id_fechamento = '" . $_GET["id_fechamento"] . "' ";
	
	//$db->delete($dsql,'MYSQL');	
}

$xajax->registerFunction("insere");
$xajax->registerFunction("envia_email");

$xajax->processRequests();

?>

<html>
<head>
<title>: : . FECHAMENTO DE FOLHA - V4. : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<?php $xajax->printJavascript(XAJAX_DIR); ?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script>
function abreHE(id_funcionario, data_ini, data_fim)
{
	//Javascript
	if(document.forms[0].id_funcionario.value && document.forms[0].data_ini.value && document.forms[0].data_fim.value)
	{
		params = "width=600,height=250,resizable=0,status=1,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0, top="+((screen.height/2)-125)+", left="+((screen.width/2)-300)+" ";
		wnd_horasextras = window.open('fechamentofolha_horaextra.php?id_funcionario='+document.forms[0].id_funcionario.value+'&data_ini='+document.forms[0].data_ini.value+'&data_fim='+document.forms[0].data_fim.value+'','wnd_horasextras', params);
	}
	else
	{
		alert('É necessário selecionar um Funcionário e digitar as datas do período!');
		document.forms[0].horasextras0.checked = true;
	}	
}

function abreHE_tbl(id_funcionario, data_ini, data_fim)
{
	params = "width=600,height=250,resizable=0,status=1,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0, top="+((screen.height/2)-125)+", left="+((screen.width/2)-300)+" ";
	wnd_horasextras_tbl = window.open('fechamentofolha_horaextra.php?id_funcionario='+id_funcionario+'&data_ini='+data_ini+'&data_fim='+data_fim+'&modo=tabela','wnd_horasextras', params);
}

function atualiza_periodo(combo)
{

	id_funcionario = document.getElementById('id_funcionario').options[document.getElementById('id_funcionario').selectedIndex].value;
	periodo = combo.value;
	location.href='<?php $_SERVER["PHP_SELF"] ?>?periodo='+periodo+'&id_funcionario='+id_funcionario+'';
	
}

function abrejanela(nome,caminho,largura,altura)
{
  params = "width="+largura+",height="+altura+",resizable=0,status=0,scrollbars=2,toolbar=0,location=0,directories=0,menubar=0, top="+(screen.height/2-altura/2)+", left="+(screen.width/2-largura/2)+" ";
  windows = window.open( caminho, nome , params);
  if(window.focus) 
  {
	setTimeout("windows.focus()",100);
  }  
}

function gera_impostos()
{

	document.forms["nfsfunc"].acao.value='impostos';
	document.forms["nfsfunc"].submit();

}

function excluir(id_fechamento, funcionario)
{
	if(confirm('Tem certeza que deseja excluir o fechamento de '+funcionario+' ?'))
	{
		location.href = '<?= $_SERVER["PHP_SELF"] ?>?acao=excluir&id_fechamento='+id_fechamento+'';
	}
}

function editar(id_fechamento)
{
	location.href = '<?= $_SERVER["PHP_SELF"] ?>?acao=editar&id_fechamento='+id_fechamento+'';
}

function ordenar(campo,ordem, periodo)
{
	location.href = '<?= $_SERVER["PHP_SELF"] ?>?campo='+campo+'&ordem='+ordem+'&periodo='+periodo+'';
}

function fn_manual(chkbox)
{

	if(chkbox.checked==true && confirm('Essa ação fará com que o cálculo automático não seja utilizado. Deseja continuar?'))
	{
		document.forms[0].manual.disabled=false;
		document.getElementById('label_descricao').style.display = 'inline';
		document.getElementById('text_descricao').style.display = 'inline';
		document.forms[0].manual.focus();		
	}
	else
	{
		chkbox.checked=false;
		document.getElementById('label_descricao').style.display = 'none';
		document.getElementById('text_descricao').style.display = 'none';
		document.forms[0].manual.disabled=true;	
		document.getElementById('text_descricao').value = "";
		document.forms[0].manual.value="";				
	}

}

</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.style2 {font-size: 12px}
.style4 {font-family: Arial, Helvetica, sans-serif}
-->
</style>
</head>
<body  class="body">
<center>
<form name="nfsfunc" method="post" action="<?= $_SERVER["PHP_SELF"] ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"> <td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
	  <tr>
        <td>
		
		 	<!-- SALVAR -->
				
			  <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"><table width="100%" border="0">
                    <tr>
                      <td width="14%"><span class="label1">FUNCIONÁRIO</span></td>
                      <td width="1%"> </td>
                      <td width="11%" class="label1">DATA INICIAL </td>
                      <td width="1%" class="label1"> </td>
                      <td width="11%" class="label1">DATA FINAL </td>
                      <td width="1%"> </td>
                      <td width="40%" class="label1">HORAS ADICIONAIS </td>
                      <td width="1%"> </td>
                      <td width="20%"><span class="label1">
                        <input name="chkmanual" type="checkbox" class="menu" id="chkmanual" value="1" onclick="fn_manual(this)">
                        CÁLCULO MANUAL </span></td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_funcionario" class="txt_box" id="id_funcionario" onChange="">
						<option value="">SELECIONE</option>
						
						<?php
						
						$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
						$sql .= "WHERE situacao NOT IN('DESLIGADO','CANCELADO') ";
						$sql .= "AND nivel_atuacao <> 'P' "; //Pacote
						$sql .= "ORDER BY funcionarios.funcionario ";
						
						$db->select($sql,'MYSQL',true);
						
						$array_func = $db->array_select;
						
						foreach ($array_func as $cont)
						{
							//incluido por Carlos Abreu
							//28/02/2011
							//pedido da   - filtrar os funcionarios que contenham SC no tipo contrato
							$sql = "SELECT * FROM ".DATABASE.".salarios ";
							$sql .= "WHERE id_funcionario = '".$cont["id_funcionario"]."' ";
							$sql .= "AND  tipo_contrato LIKE '%SC%' ";
							$sql .= "AND salarios.reg_del = 0 ";
							$sql .= "ORDER BY data DESC, id_salario DESC ";
							
							$db->select($sql,'MYSQL',true);

							if($db->numero_registros>0)
							{
								?>
								<option value="<?= $cont["id_funcionario"] ?>" <?php if($cont["id_funcionario"]==$_GET["id_funcionario"]) { echo "selected"; } ?>><?= $cont["funcionario"] ?></option>
								<?php
							}
						}									
									
						?>
                        </select>
                      </font></font></td>
                      <td> </td>
                      <td><input name="data_ini" type="text" class="txt_box" id="data_ini" size="20" maxlength="10" onKeyPress="return txtBoxFormat(document.nfsfunc, 'data_ini', '99/99/9999', event);" onKeyUp="return autoTab(this, 10, event);" value="<?= $_GET["data_ini"] ?>"></td>
                      <td> </td>
                      <td><input name="data_fim" type="text" class="txt_box" id="data_fim" size="20" maxlength="10" onKeyPress="return txtBoxFormat(document.nfsfunc, 'data_fim', '99/99/9999', event);" onKeyUp="return autoTab(this, 10, event);" value="<?= $_GET["data_fin"] ?>"></td>
                      <td> </td>
                      <td><input name="horasextras" type="radio" value="1" id="horasextras1" onclick="javascript:abreHE(document.forms[0].id_funcionario.value,document.forms[0].data_ini,document.forms[0].data_fim);">
                        <span class="label1 style2">Sim</span>
                          <input name="horasextras" type="radio" value="0"  id="horasextras0" checked>
                          <span class="label1">NÃO</span></td>
                      <td> </td>
                      <td><input name="manual" type="text" class="txt_box" id="manual" size="20" maxlength="9" onKeyDown="FormataValor(document.forms[0].manual, 9, event)" disabled></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="9%" class="label1">FÉRIAS (R$) </td>
                      <td width="1%"> </td>
                      <td width="12%" class="label1">RESCISÃO (r$) </td>
                      <td width="1%"> </td>
                      <td width="12%" class="label1">valor_fgts (r$) </td>
                      <td width="1%"> </td>
                      <td width="17%"><span class="label1">DÉCIMO TERCEIRO (R$) </span></td>
                      <td width="1%"> </td>
                      <td width="26%"><span class="label1">SALÁRIO PROPORCIONAL CLT (R$) </span></td>
                      <td width="1%"> </td>
                      <td width="19%" class="label1"><span id="label_descricao" style="display:none;">DESCRIÇÃO</span></td>
                    </tr>
                    <tr>
                      <td><input name="ferias" type="text" class="txt_box" id="ferias" size="20" maxlength="9" onKeyDown="FormataValor(document.forms[0].ferias, 9, event)"></td>
                      <td> </td>
                      <td><input name="rescisao" type="text" class="txt_box" id="rescisao" size="20" maxlength="9" onKeyDown="FormataValor(document.forms[0].rescisao, 9, event)"></td>
                      <td> </td>
                      <td><input name="fgts" type="text" class="txt_box" id="fgts" size="20" maxlength="9" onKeyDown="FormataValor(document.forms[0].fgts, 9, event)"></td>
                      <td> </td>
                      <td><input name="decimoterceiro" type="text" class="txt_box" id="decimoterceiro" size="20" maxlength="9" onKeyDown="FormataValor(document.forms[0].decimoterceiro, 9, event)"></td>
                      <td> </td>
                      <td><input name="salarioproporcional" type="text" class="txt_box" id="salarioproporcional" size="20" maxlength="9" onKeyDown="FormataValor(document.forms[0].decimoterceiro, 9, event)"></td>
                      <td> </td>
                      <td><span id="text_descricao" style="display:none; position:absolute;"><textarea name="descricao_manual" class="txt_box" cols="40" rows="4"></textarea></span></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                      <tr>
                        <td width="22%" class="label1">DIF. CLT FÉRIAS </td>
                        <td width="1%"> </td>
                        <td width="12%"><span class="label1">DIF. CLT RESC. </span></td>
                        <td width="1%"> </td>
                        <td width="17%" class="label1">OUTROS DESC. </td>
                        <td width="1%"> </td>
                        <td width="33%" class="label1">OUTROS ACRÉSCIMOS </td>
                        <td width="6%"> </td>
                        <td width="7%"> </td>
                      </tr>
                      <tr>
                        <td valign="top" class="kks_nivel3"><input name="diferenca_clt_ferias" type="text" class="txt_box" id="diferenca_clt_ferias" size="20" maxlength="20" onKeyDown="FormataValor(document.forms[0].diferenca_clt_ferias, 9, event)">
                        <a href="javascript:openpage('detalhes', 'fechamentofolha_outros.php?tipo=diferenca_clt_ferias&id_funcionario='+document.forms[0].id_funcionario.value+'&data_ini='+document.forms[0].data_ini.value+'&data_fim='+document.forms[0].data_fim.value+'',600,300)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
                        <td> </td>
                        <td><input name="diferenca_clt_rescisao" type="text" class="txt_box" id="diferenca_clt_rescisao" size="20" maxlength="20" onKeyDown="FormataValor(document.forms[0].diferenca_clt_rescisao, 9, event)">
                        <a href="javascript:openpage('detalhes', 'fechamentofolha_outros.php?tipo=diferenca_clt_rescisao&id_funcionario='+document.forms[0].id_funcionario.value+'&data_ini='+document.forms[0].data_ini.value+'&data_fim='+document.forms[0].data_fim.value+'',600,300)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
                        <td> </td>
                        <td><input name="outros_descontos" type="text" class="txt_box" id="outros_descontos" size="20" maxlength="20" onKeyDown="FormataValor(document.forms[0].outros_descontos, 9, event)">
                        <a href="javascript:openpage('detalhes', 'fechamentofolha_outros.php?tipo=outros_descontos&id_funcionario='+document.forms[0].id_funcionario.value+'&data_ini='+document.forms[0].data_ini.value+'&data_fim='+document.forms[0].data_fim.value+'',600,300)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
                        <td> </td>
                        <td><input name="outros_acrescimos" type="text" class="txt_box" id="outros_acrescimos" size="20" maxlength="20" onKeyDown="FormataValor(document.forms[0].outros_acrescimos, 9, event)">
                        <a href="javascript:openpage('detalhes', 'fechamentofolha_outros.php?tipo=outros_acrescimos&id_funcionario='+document.forms[0].id_funcionario.value+'&data_ini='+document.forms[0].data_ini.value+'&data_fim='+document.forms[0].data_fim.value+'',600,300)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
                        <td> </td>
                        <td> </td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"> </td>
                </tr>
                
                <tr>
                  <td> </td>
                  <td><span class="label1">
				  <input name="id_porcadicionais" type="hidden" id="id_porcadicionais"" value="0" id+"porc_adicionais>
                    <input name="acao" type="hidden" id="acao" value="salvar">
                    <?php
					// Verifica as permissões para incluir
					if($_SESSION["FINANCEIRO"]{1} || $_SESSION["FINANCEIRO"]{2} || $_SESSION["FINANCEIRO"]{3})
					{
						?>
						<input name="Incluir" type="submit" class="btn" id="Incluir" value="Incluir">
						<input name="Calcular impostos" type="button" class="btn" id="Calcular impostos" value="Calcular impostos" onCLick="javascript:if(confirm('Deseja calcular os impostos?')) { gera_impostos(); }">

						<?php
					}
					else
					{
						?>
						<input name="Incluir" type="button" class="btn" id="Incluir2" value="Incluir" onclick="javascript:alert('Você não possue permissão para executar esta ação.')">
						<input name="Calcular impostos" type="button" class="btn" id="Calcular impostos" value="Calcular impostos" onCLick="javascript:alert('Você não possue permissão para executar esta ação.')">

						<?php				
					}

					// Verifica as permissões para incluir
					if($_SESSION["FINANCEIRO"]{1} || $_SESSION["FINANCEIRO"]{2} || $_SESSION["FINANCEIRO"]{3})
					{
						?>
						<input name="Relatorios" type="button" class="btn" id="Relatorios" value="Relatórios" onclick="javascript:location.href='relatorios.php?periodo=<?= $_GET["periodo"] ?>';">
						<?php
					}
					else
					{
						?>
						<input name="Relatorios" type="button" class="btn" id="Relatorios" value="Relatórios" onclick="javascript:alert('Você não possue permissão para executar esta ação.')">
						<?php
					}
					// Verifica as permissôes para incluir
					if($_SESSION["FINANCEIRO"]{1} || $_SESSION["FINANCEIRO"]{2} || $_SESSION["FINANCEIRO"]{3})
					{
						?>
						<input name="Liberar" type="button" class="btn" id="Liberar" value="Liberar fechamento" onclick="javascript:abrejanela('listafechamentos','listafechamentos.php',600,300);">
						<?php
					}
					else
					{
						?>
						<input name="Liberar" type="button" class="btn" id="Liberar" value="Liberar fechamento" onclick="javascript:alert('Você não possue permissão para executar esta ação.')">
						<?php
					}

					// Verifica as permissões para incluir
					if($_SESSION["FINANCEIRO"]{1} || $_SESSION["FINANCEIRO"]{2} || $_SESSION["FINANCEIRO"]{3})
					{
						?>
						<input name="permite_anexo" type="button" class="btn" id="permite_anexo" value="Liberar anexos" onclick="javascript:abrejanela('permite_anexos','libera_anexos.php',600,300);">
						<?php
					}
					else
					{
						?>
						<input name="permite_anexo" type="button" class="btn" id="permite_anexo" value="Liberar anexos" onclick="javascript:alert('Você não possue permissão para executar esta ação.')">
						<?php
					}
					?>
                   
                    <input name="Visualizar" type="button" class="btn" id="Visualizar" value="Visualizar" onclick="if(document.getElementById('id_funcionario').selectedIndex==0 || document.getElementById('periodo').selectedIndex==0){alert('É necessário selecionar um Funcionário e um período.');}else{abrejanela('fechamento','fechamento_forn.php?periodo='+document.forms[0].periodo.value+'&id_funcionario='+document.forms[0].id_funcionario.value+'','750','380');}">
                    <input name="Alterar2" type="button" class="btn" id="Alterar2" value="Voltar" onclick="javascript:history.back();">
                  </span></td>
                </tr>
                <tr>
                  <td> </td>
                  <td> </td>
                </tr>
              </table>
			  
			<!-- /SALVAR -->
			  </div>
		</td>
      </tr>
	  <tr>
	    <td>
		
		<span class="menu_inicio style4">Visualizar período:</span>		
		<select name="periodo" class="txt_box" onChange="atualiza_periodo(this)">
		<option value="">PERÍODO ATUAL</option>
		
		<?php
		
		$sql = "SELECT periodo FROM ".DATABASE.".fechamento_folha WHERE fechamento_folha.reg_del = 0 ";
		$sql .= "GROUP BY fechamento_folha.periodo ";
		$sql .= "ORDER BY fechamento_folha.periodo DESC ";
		
		$db->select($sql,'MYSQL',true);

		foreach($db->array_select as $cont_periodo)
		{
			?>
			
			<option value="<?= $cont_periodo["periodo"] ?>" <?php if($_GET["periodo"]==$cont_periodo["periodo"]) { echo "selected"; } ?>>
			<?php 

				$array_periodo = explode(",",$cont_periodo["periodo"]);
				$per_dataini = substr($array_periodo[0],-2,2) . "/" . substr($array_periodo[0],0,4);
				$per_datafin = substr($array_periodo[1],-2,2) . "/" . substr($array_periodo[1],0,4);
				echo $per_dataini . " - " . $per_datafin;
			?>
			</option>
			
			<?php
		}
		
		?>						
	    </select>	    </td>
	    </tr>
      <tr>
        <td>
			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "funcionario";
					}
					if($_GET["ordem"]=='' || $_GET["ordem"]=='DESC')
					{
						$ordem="ASC";
					}
					else
					{
						$ordem="DESC";
					}
					//Controle de ordenação
				  ?>
				  <td width="23%"><a href="#" class="cabecalho_tabela" onclick="ordenar('funcionario','<?= $ordem ?>','<?= $_GET["periodo"] ?>')">FUNCIONÁRIO</a></td>
				  <td width="16%"><a href="#" class="cabecalho_tabela" onclick="ordenar('data_ini','<?= $ordem ?>','<?= $_GET["periodo"] ?>')">PERÍODO</a></td>
				  <td width="13%"><a href="#" class="cabecalho_tabela" onclick="ordenar('valor_total','<?= $ordem ?>','<?= $_GET["periodo"] ?>')">TOTAL VALOR</a></td>
				  <td width="9%"><a href="#" class="cabecalho_tabela" onclick="ordenar('valor_imposto','<?= $ordem ?>','<?= $_GET["periodo"] ?>')">1,5%</a></td>
				  <td width="9%"><a href="#" class="cabecalho_tabela" onclick="ordenar('valor_pcc','<?= $ordem ?>','<?= $_GET["periodo"] ?>')">4,65%</a></td>
				  <td width="12%"><a href="#" class="cabecalho_tabela" onclick="ordenar('valor_pagamento','<?= $ordem ?>','<?= $_GET["periodo"] ?>')">PAGTO</a></td>
				  <td width="3%" title="Não liberar">NL</td>
				  <td width="4%" class="cabecalho_tabela" title="Horas Adicionais">HA</td>
				  <td width="4%" class="cabecalho_tabela" title="Detalhes">DT</td>
				  <td width="3%" class="cabecalho_tabela" title="Notas Fiscais">NF</td>
                  <td width="3%" class="cabecalho_tabela" title="Anexar">A</td>
                  <td width="3%" class="cabecalho_tabela" title="Documento">DC</td>
                  <td width="3%" class="cabecalho_tabela" title="E-mail">EM</td>
				  <td width="2%"  class="cabecalho_tabela" title="Deletar">D</td>
				  <td width="2%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:520px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela_menor">
			<?php
				
					$filtro = "";
					
					if($_GET["periodo"])
					{
						$filtro = "AND fechamento_folha.periodo = '" . $_GET["periodo"] . "' ";
					}
					else
					{
					
						$filtro = "AND SUBSTRING(fechamento_folha.periodo,9,7) = '" . Date("Y-m") . "' ";
					
					}					
					
					// Mostra os clientes
					$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
					$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario " . $filtro;
					$sql .= "AND fechamento_folha.reg_del = 0 ";
					$sql .= "ORDER BY funcionario ";
					
					$db->select($sql,'MYSQL',true);
					
					$array_funci = $db->array_select;
					
					$i = 0;
					
					foreach ($array_funci as $fechamento_folha)
					{
					
						if($i%2)
						{
							// escuro
							$cor = "#CCD8E1";
						
						}
						else
						{
							//claro
	
							$cor = "#FFFFFF";
						}
						
						$i++;							

						$str_datainifim = mysql_php($fechamento_folha["data_ini"]) . " - " . mysql_php($fechamento_folha["data_fim"]);
					
						?>
						<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">
						  <td width="23%"><div style="font-size:10px" align="center"><?= $fechamento_folha["funcionario"] ?></div></td>
						  <td width="17%"><div style="font-size:10px" align="center"><?= $str_datainifim ?></div></td>
                          
                          
						  <td width="13%"><div style="font-size:10px" align="center">R$ <?= formatavalor($fechamento_folha["valor_total"]) ?></div></td>
						  <td width="9%"><div style="font-size:10px" align="center">R$ <?= formatavalor($fechamento_folha["valor_imposto"]) ?>
						  </div></td>
						  <td width="10%"><div style="font-size:10px" align="center">R$ <?= formatavalor($fechamento_folha["valor_pcc"]) ?>
                          </div></td>
						  <td width="13%"><div style="font-size:10px" align="center">R$ <?= formatavalor($fechamento_folha["valor_pagamento"]) ?>
						  </div></td>
						  <td width="3%"><div align="center"><span class="box">
						    <input name="chkf_<?= $fechamento_folha["id_fechamento"] ?>" type="checkbox" id="chkf_<?= $fechamento_folha["id_fechamento"] ?>" onclick="xajax_insere(xajax.getFormValues('nfsfunc'),'<?= $fechamento_folha["data_ini"] ?>','<?= $fechamento_folha["data_fim"] ?>');" value="1" <?php if($fechamento_folha["excessao"]=='1') { echo "checked"; } ?> title="Selecione para não liberar o Fechamento para esse funcionário">
					      </span></div></td>
						  <td width="4%"><div align="center"><a href="javascript:abreHE_tbl('<?= $fechamento_folha["id_funcionario"] ?>','<?= $fechamento_folha["data_ini"] ?>','<?= $fechamento_folha["data_fim"] ?>');"><img src="../images/buttons_action/bt_relogio.gif" alt="Horas Adicionais" width="16" height="16" border="0"></a></div></td>
						  <td width="2%"><div align="center"><a href="javascript:abrejanela('relatorio', 'fechamentofolha_detalhes.php?funcionario=<?= $fechamento_folha["funcionario"] ?>&id_fechamento=<?= $fechamento_folha["id_fechamento"] ?>',800,300);"><img src="../images/buttons_action/detalhes.gif" alt="Visualizar detalhes" width="16" height="16" border="0"></a></div></td>
						  <td width="4%"><div align="center"><a href="nfsfunc.php?id_fechamento=<?= $fechamento_folha["id_fechamento"] ?>&periodo=<?= substr($fechamento_folha["data_ini"],0,7) ?>,<?= substr($fechamento_folha["data_fim"],0,7) ?>"><img src="../images/buttons_action/nf.gif" alt="Notas fiscais" width="16" height="16" border="0"></a></div></td>
						<td width="2%"><div align="center"><a href="javascript:abrejanela('anexar', 'cadastra_docs_forn.php?id_fechamento=<?= $fechamento_folha["id_fechamento"] ?>',800,300);"><img src="../images/silk/add.gif" alt="Anexar Documentos" width="16" height="16" border="0"></a></div></td>
                        
						<?php 
						 
						$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos ";
						$sql .= "WHERE fechamento_documentos.id_fechamento = '" . $fechamento_folha["id_fechamento"] . "' ";

						$db->select($sql,'MYSQL',true);
						
						$regs = $db->array_select[0];
						
						if($db->numero_registros>0)
						{
							//procura os documentos aprovados, se todos muda icone
							$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos ";
							$sql .= "WHERE fechamento_documentos.id_fechamento = '" . $fechamento_folha["id_fechamento"] . "' ";
							$sql .= "AND conferido IN ('0','2') ";
	
							$db->select($sql,'MYSQL',true);
							
							if($db->numero_registros>0)
							{													
								?>
								<td width="4%"><div align="center"><a href="confere_documentos.php?id_fechamento=<?= $fechamento_folha["id_fechamento"] ?>"><img src="../images/buttons_action/file_pdf.gif" alt="Documentos" width="16" height="16" border="0"></a></div></td>
								<?php
							}
							else
							{
								?>
								<td width="4%"><div align="center"><a href="confere_documentos.php?id_fechamento=<?= $fechamento_folha["id_fechamento"] ?>"><img src="../images/buttons/aprovado.gif" alt="Documentos Aprovados" width="16" height="16" border="0"></a></div></td>
							  <?php
							}
						}
						else
						{
							?>
                            <td width="4%"><div align="center"> </div></td>
                            <?php
						}
						
						//procura os documentos aprovados, se todos muda icone
						$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos ";
						$sql .= "WHERE fechamento_documentos.id_fechamento = '" . $fechamento_folha["id_fechamento"] . "' ";
						$sql .= "AND envio_email = '1' ";

						$db->select($sql,'MYSQL',true);
						
						if($db->numero_registros>0)
						{
							?>	
                          <td width="2%"><div align="center"><img src="../imagens/aprovado.png" style="cursor:pointer" width="10" height="10" border="0" onclick="xajax_envia_email('<?= $fechamento_folha["id_fechamento"] ?>')">
						  </div></td>
                          <?php	
						}
						else
						{
							?>	
                          <td width="2%"><div align="center"><img src="../imagens/web.png" alt="E-mail" style="cursor:pointer" width="10" height="10" border="0" onclick="xajax_envia_email('<?= $fechamento_folha["id_fechamento"] ?>')">
						  </div></td>
                          <?php	
						}						
						
						?>                      
                          
                          <td width="2%">
                          	<?php
                               if($_SESSION["id_funcionario"]!=858)
							   {
							
							?>
                            <div align="center"><a href="javascript:excluir('<?= $fechamento_folha["id_fechamento"] ?>','<?= $fechamento_folha["funcionario"] ?>')"><img src="../imagens/apagar.png" alt="Deletar" width="16" height="16" border="0"></a></div>
                            <?php
							   }
							   else
							   {
								   ?>
									<div align="center"> </div>
                                   <?php   
							   }
							?>
                          
                          </td>
						</tr>
						<?php
					}
				?>
				<tr><td><input type="text" id="fim_tabela" style="border:none #FFFFFF 0px; background:none;"> </td></tr>
			  </table>
			</div></td>
      </tr>
    </table>
	</td>
  </tr>
</table>
</form>

<script>
{

	//Desce o scroll até o último item
	document.getElementById('fim_tabela').focus();
	document.getElementById('fim_tabela').style.display='none';

}
</script>

</center>
</body>
</html>
