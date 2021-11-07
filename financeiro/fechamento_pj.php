<?php
/*
		Formulário de FECHAMENTO PJ	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/fechamento_pj.php
	
		Versão 0 --> VERSÃO INICIAL : 10/03/2006 - Otavio Pamplona / Carlos Abreu
		Versão 1 --> Alterações no agrupamento por período - 03/04/2006
		Versão 2 --> Pequenas alterações na funcionalidade - 05/07/2006
		Versão 3 --> Atualização Carlos - 18/01/2008
			#Alteração 2: 
			Adicionado campo "periodo" na tabela "fechamento_folha"
			Na ação "editar", adicionado linha do campo "periodo" na string de "INSERT"
			Tabela HTML inferior passou a filtrar pelo campo "período"
			
			#Alteração 3:
			Alterada a ordem do "dropdown box" de Período.
			Mudanças no checkbox de liberação.
		Versão 4 --> Mudanças no layout / banco de dados - Carlos Abreu - 27/06/2013
		Versão 5 --> Acrescentado calculo de HA para mensalistas - 28/03/2016
		Versão 6 --> Atualização imagens - Carlos Abreu - 12/07/2016
		Versão 7 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 8 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
		Versão 9 --> Alteração para o calculo de mensalistas, levando em conta o fator 30 dias/mes e calculando dia a dia - 04/04/2018 - Carlos Abreu
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(308))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta->addScriptCall("reset_campos('frm')");
		
	$resposta->addAssign("btninserir", "value", $botao[1]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$filtro = "";
	$filtro1 = "";
	
	if($dados_form["periodo"])
	{
		$filtro = "AND fechamento_folha.periodo = '" . $dados_form["periodo"] . "' ";
		$filtro1 = "WHERE fechamento_folha.periodo = '" . $dados_form["periodo"] . "'";
	}
	else
	{
		$filtro = "AND SUBSTRING(fechamento_folha.periodo,9,7) = '" . date("Y-m") . "' ";
		$filtro1 = "WHERE SUBSTRING(fechamento_folha.periodo,9,7) = '" . date("Y-m") . "'";
	}
	
	$resposta->addScript("combo_destino = document.getElementById('funcionario');");
	
	$resposta->addScriptCall("limpa_combo('funcionario')");					
	
	$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE situacao NOT IN ('DESLIGADO','CANCELADO') ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario NOT IN (SELECT id_funcionario FROM ".DATABASE.".fechamento_folha ".$filtro1." AND fechamento_folha.reg_del = 0) ";
	$sql .= "AND nivel_atuacao NOT IN ('P','D') "; //Pacote
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$cont = $db->array_select;
	
	foreach ($cont as $regs)
	{
		$sql = "SELECT  tipo_contrato FROM ".DATABASE.".salarios ";
		$sql .= "WHERE id_funcionario = '".$regs["id_funcionario"]."' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY data DESC, id_salario DESC LIMIT 1 ";	
		
		$db->select($sql,'MYSQL',true);
		
		$regs1 = $db->array_select[0];

		if($regs1[" tipo_contrato"]!='SOCIO' && $regs1[" tipo_contrato"]!='CLT' && $regs1[" tipo_contrato"]!='EST')
		{	
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["funcionario"]."', '".$regs["id_funcionario"]."');");
		}			
	}
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
	$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= $filtro;
	$sql .= "ORDER BY funcionarios.funcionario ";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$res = $db->array_select;	

	$chars = array("'","\"",")","(","\\","/");
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($res as $cont_desp)
	{
		$tipoEmpresa = $cont_desp['tipo_empresa'];
		
		$str_datainifim = mysql_php($cont_desp["data_ini"]) . " - " . mysql_php($cont_desp["data_fim"]);		
		
		if($cont_desp["excessao"]=='1') 
		{ 
			$check = 'checked'; 
		}
		else
		{
			$check = '';
		}

		$xml->startElement('row');
			$xml->writeElement('cell', $cont_desp["funcionario"]);
			$xml->writeElement('cell', $str_datainifim);

			$xml->writeElement('cell', formatavalor($cont_desp["valor_total"]));
			$xml->writeElement('cell', formatavalor($cont_desp["valor_imposto"]));
			$xml->writeElement('cell', formatavalor($cont_desp["valor_pcc"]));
			$xml->writeElement('cell', formatavalor($cont_desp["valor_pagamento"]));			
			$xml->writeElement('cell', '<input name="chkf_'.$cont_desp['id_fechamento'].'" '.$check.' id="chkf_'.$cont_desp['id_fechamento'].'" value="1" type="checkbox" onclick=xajax_liberar_fechamento(xajax.getFormValues("frm"),"'.$cont_desp['id_fechamento'].'"); >');
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'bt_relogio.png" style="cursor:pointer;" onclick=abrejanela("HA","fechamentofolha_horaextra.php?id_funcionario='.$cont_desp['id_funcionario'].'&data_ini='.$cont_desp['data_ini'].'&datafim='.$cont_desp['data_fim'].'&modo=tabela",800,300); />');
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'detalhes.png" style="cursor:pointer;" onclick=abrejanela("relatorio","fechamentofolha_detalhes.php?id_fechamento='.$cont_desp['id_fechamento'].'",800,350); />');
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'nf.png" style="cursor:pointer;" onclick=abrejanela("NotasFiscais","nfsfunc.php?id_fechamento='.$cont_desp['id_fechamento'].'&periodo='.substr($cont_desp['data_ini'], 0, 7).','.substr($cont_desp['data_fim'], 0, 7).'",1024,600); />');
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'add.png" style="cursor:pointer;" onclick=abrejanela("anexar","cadastra_docs_forn.php?id_fechamento='.$cont_desp['id_fechamento'].'",1024,600); />');
		
		//verifica os documentos anexados
		$sql = 
		"SELECT id_fechamento, competencia, conferido FROM
		(
			SELECT *, ".$cont_desp["id_fechamento"]." as fechamentoPadrao
			FROM
			".DATABASE.".fechamento_tipos_tributos a
			LEFT JOIN (
			  SELECT id_fechamento_docs, competencia, id_fechamento_tipos_tributos as tipos, conferido, documento, excessao, id_fechamento, reg_del as deletado
			  FROM ".DATABASE.".fechamento_documentos
			  JOIN (
			  	SELECT id_fechamento as fechamento, excessao FROM ".DATABASE.".fechamento_folha
			  	WHERE fechamento_folha.id_fechamento = ".$cont_desp["id_fechamento"]."
			  	AND fechamento_folha.reg_del = 0
			  ) fechamento
			  ON fechamento = id_fechamento
			  WHERE id_fechamento = ".$cont_desp["id_fechamento"]."
			) docs
			ON docs.tipos = id_fechamento_tipos_tributos AND docs.deletado = 0
			WHERE id_fechamento_tipos_tributos not in(11)
			AND reg_del = 0 
			AND tipo_empresa IN(".$tipoEmpresa.", 0)
			AND calcular = 1
		) consulta
		GROUP BY fechamentoPadrao, conferido, competencia
		ORDER BY ordem";

		$db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		//se tiver documentos anexados
		if($db->numero_registros>0)
		{					
			$faltaAprovar = 0;
			$reprovados = 0;
			$aprovados = 0;
			$naoLancados = 0;
			
			$res2 = $db->array_select;
			
			foreach($res2 as $registro)
			{
				//Se houver algum registro ainda não conferido
				if (is_numeric($registro['conferido']) && intval($registro['conferido']) === 0)
				{
					$faltaAprovar ++;
				}
				else if (intval($registro['conferido']) === 2)
				{
					$reprovados ++;
				}
				else if (intval($registro['conferido']) === 1)
				{
					$aprovados ++;
				}
				else if (empty($registro['conferido']))
				{
					$naoLancados ++;
				}
			}
			
			$img = '';
			
			if ($aprovados == $db->numero_registros)
			{
				$img = DIR_IMAGENS.'led_vd.png';
			}
			if ($naoLancados == $db->numero_registros)
			{
				$img = DIR_IMAGENS.'led_az.png';
			}
			else if($faltaAprovar > 0 && $naoLancados == 0)
			{
				$img = DIR_IMAGENS.'file_pdf.png';
			}
			else if ($reprovados > 0)
			{
				$img = DIR_IMAGENS.'led_vm.png';
			}
			else if ($naoLancados > 0)
			{
				$img = DIR_IMAGENS.'led_am.png';
			}
			
			$xml->writeElement('cell', '<img src="'.$img.'" style="cursor:pointer;" onclick=openpage("anexar","confere_documentos.php?id_fechamento='.$cont_desp['id_fechamento'].'",1024,700); />');
		}
		else
		{
			$xml->writeElement('cell', ' ');
		}
		
		//verifica se enviou e-mail
		$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos ";
		$sql .= "WHERE fechamento_documentos.id_fechamento = '" . $cont_desp["id_fechamento"] . "' ";
		$sql .= "AND fechamento_documentos.reg_del = 0 ";
		$sql .= "AND envio_email = '1' ";

		$db->select($sql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		//já enviado
		if($db->numero_registros>0)
		{
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'aprovado.png" style="cursor:pointer;" onclick=telaEmail('.$cont_desp['id_fechamento'].');>');
		}
		else
		{
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'mail.png" style="cursor:pointer;" onclick=telaEmail('.$cont_desp['id_fechamento'].');>');
		}
		
		$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja excluir?")){xajax_excluir('.$cont_desp['id_fechamento'].');} >');
		
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid', true, '460', '".$conteudo."');");
	
	$resposta->addScript("frm.funcionario.focus();");
	
	$resposta->addScript("hideLoader();");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);	
	
	$medicao = 0;
	$valor_total = 0;
	$clt = 0;
	$mens = 0;
	$adicionais_dom = 0;
	$adicionais_sem = 0;
	$adicionais_sab = 0;
	$adicionais_not = 0;
	$adicionais_fer = 0;
	$contrato = "";
	$tarifa_mens = 0;
	$tarifa_sal = 0;
	$valor_mensal = 0;
	
	$data_ini = str_replace("-","",php_mysql($dados_form["data_ini"]));
	$datafim = str_replace("-","",php_mysql($dados_form["data_fim"]));

	if($datafim<$data_ini)
	{
		$resposta->addAlert('As datas estão inválidas');
	}	
	else
	{
		if($conf->checa_permissao(8,$resposta))
		{
			$db = new banco_dados;
			
			if($dados_form["funcionario"]!='' && $dados_form["data_ini"]!='' && $dados_form["data_fim"]!='')
			{			
				//Formata o valores para FLOAT
				$valor_outros_descontos = str_replace(",",".",str_replace(".","",$dados_form["outros_descontos"]));
				$valor_outros_acrescimos = str_replace(",",".",str_replace(".","",$dados_form["outros_acrescimos"]));
				$valor_diferencaclt_ferias = str_replace(",",".",str_replace(".","",$dados_form["diferenca_clt_ferias"]));
				$valor_diferencaclt_rescisao = str_replace(",",".",str_replace(".","",$dados_form["diferenca_clt_rescisao"]));
				$valor_ferias = str_replace(",",".",str_replace(".","",$dados_form["ferias"]));
				$valor_fgts = str_replace(",",".",str_replace(".","",$dados_form["fgts"])); 
				$valor_decimoterceiro = str_replace(",",".",str_replace(".","",$dados_form["decimoterceiro"]));
				$valor_rescisao = str_replace(",",".",str_replace(".","",$dados_form["rescisao"]));
				$salario_proporcional = str_replace(",",".",str_replace(".","",$dados_form["salarioproporcional"]));
				
				if($dados_form["proporcional"])
				{
					//calcula o numero de dias do periodo para mensalistas
					//incluido em 04/04/2018 - Carlos Abreu
					$data1 = new DateTime(php_mysql($dados_form["data_ini"]));
					
					$data2 = new DateTime(php_mysql($dados_form["data_fim"]));
					
					$intervalo = $data1->diff($data2);
					
					$num_dias = $intervalo->format('%a');				
					
					$data_inicio = php_mysql(php_mysql($dados_form["data_ini"]));
					
					for($i=1;$i<=$num_dias;$i++)
					{					
						//Obtem o valor do salario na data
						$sql = "SELECT * FROM ".DATABASE.".salarios ";
						$sql .= "WHERE salarios.id_funcionario = '" . $dados_form["funcionario"] . "' ";
						$sql .= "AND DATE_FORMAT(data , '%Y%m%d') <= '".str_replace("-","",$data_inicio)."' ";
						$sql .= "AND salarios.reg_del = 0 ";
						$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";
		
						$db->select($sql,'MYSQL',true);
		
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
								
						$cont1 = $db->array_select[0];
						
						$contrato = $cont1[" tipo_contrato"];
						
						if(in_array($contrato,array('SC+MENS','SC+CLT+MENS')))
						{
							$valor_mensal += $cont1["salario_mensalista"]/30;							
						}					
						
						$data_n = new DateTime($data_inicio);
													
						$interval = new DateInterval('P1D');
						
						$data_n->add($interval);
						
						$data_inicio = $data_n->format('Y-m-d');				
					}
				}
	
								
				//ADICIONADO POR CARLOS ABREU - 18/01/2008				
				//obtem as horas do funcionario dentro de um periodo
				$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS HN, SUM(TIME_TO_SEC(hora_adicional)) AS HA, SUM(TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
				$sql .= "FROM ".DATABASE.".apontamento_horas ";
				$sql .= "WHERE apontamento_horas.id_funcionario = '" . $dados_form["funcionario"] . "' ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND apontamento_horas.data BETWEEN '" . php_mysql($dados_form["data_ini"]) . "' AND '" . php_mysql($dados_form["data_fim"]) . "' ";
				$sql .= "GROUP BY apontamento_horas.id_funcionario ";
	
				$db->select($sql,'MYSQL',true);
	
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
					
				$conth = $db->array_select[0];
				
				$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_extra ";
				$sql .= "WHERE fechamento_folha_extra.id_funcionario = '" . $dados_form["funcionario"] . "' ";
				$sql .= "AND fechamento_folha_extra.reg_del = 0 ";
				$sql .= "AND fechamento_folha_extra.data_ini = '".php_mysql($dados_form["data_ini"])."' ";
				$sql .= "ORDER BY id_fechamento_horaextra DESC LIMIT 1 ";
	
				$db->select($sql,'MYSQL',true);
	
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
						
				$cont2 = $db->array_select[0];	
				
				//obtem as horas do funcionario dentro de um periodo
				$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal))/3600 AS HN, SUM(TIME_TO_SEC(hora_adicional))/3600 AS HA, SUM(TIME_TO_SEC(hora_adicional_noturna))/3600 AS HAN ";
				$sql .= "FROM ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
				$sql .= "WHERE apontamento_horas.id_funcionario = '" . $dados_form["funcionario"] . "' ";
				$sql .= "AND OS.reg_del = 0 ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND apontamento_horas.data BETWEEN '" . php_mysql($dados_form["data_ini"]) . "' AND '" . php_mysql($dados_form["data_fim"]) . "' ";
				$sql .= "AND OS.id_os = apontamento_horas.id_os ";
				$sql .= "GROUP BY OS.id_os, apontamento_horas.data ";
				$sql .= "ORDER BY apontamento_horas.data ";
	
				$db->select($sql,'MYSQL',true);
	
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$regs = $db->array_select;
				
				foreach ($regs as $cont)
				{		
					//Obtem o valor do salario na data
					$sql = "SELECT * FROM ".DATABASE.".salarios ";
					$sql .= "WHERE salarios.id_funcionario = '" . $cont["id_funcionario"] . "' ";
					$sql .= "AND DATE_FORMAT(data , '%Y%m%d') <= '".str_replace("-","",$cont["data"])."' ";
					$sql .= "AND salarios.reg_del = 0 ";
					$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";
	
					$db->select($sql,'MYSQL',true);
	
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
							
					$cont1 = $db->array_select[0];
					
					$contrato = $cont1[" tipo_contrato"];
			
					$tarifa_sal = $cont1["salario_clt"];
					
					switch ($contrato)
					{
						case 'SC':
						case 'SC+CLT':
						
							//Horas Extras NãO
							if($dados_form["horasextras"]=="0")
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
							
						break;
						
						case 'CLT':
						case 'EST':
						
							if($dados_form["salarioproporcional"]!="")
							{
								$valor_total = $salario_proporcional;
								$medicao = $salario_proporcional;	
							}
							else
							{
								$valor_total = $cont1["salario_clt"];
								$medicao = $cont1["salario_clt"];	
							}
							
						break;
						
						case 'SC+MENS':
						case 'SC+CLT+MENS':
						
							$tarifa_mens = $cont1["salario_mensalista"];
							
							$tarifa_prop = $cont1["salario_mensalista"]/176;
							
							//acrescentado em 28/03/2016 - Carlos Abreu
							
							//Horas Extras SIM
							if($dados_form["horasextras"]=="1")
							{
								//Horas Extras SIM				
								//ADICIONADO POR CARLOS 12/02/2008
								//Verifica se a OS incide Horas Extras				
								if($cont["hora_extra"])
								{					
									if(empty($cont2["ad_feriado_porc"]) && empty($cont2["domingo_porc"]) && empty($cont2["sabado_porc"]) && empty($cont2["semana_porc"]) && empty($cont2["ad_noturno_porc"]))
									{
										$valor_total = ($cont["HA"] * $tarifa_prop);
									}
									
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
											//Se a data do banco de extra estiver entre as datas de feriado
											if(in_array($cont["data"],$array_ad_data_fer1))
											{
												$adicionais_fer += ((($tarifa_prop * $cont2["ad_feriado_porc"])/100) + $tarifa_prop) * ($cont2["ad_feriado_horas"]/count($array_ad_data_fer1));
											}
											else
											{
												$adicionais_dom += ((($tarifa_prop * $cont2["domingo_porc"])/100) + $tarifa_prop) * ($cont["HA"]+$cont["HAN"]);
											}
											break;
										//Adicionais Sabado
										case 6:
											//Cria um array com as datas de feriado
											$array_ad_data_fer1 = explode(";",$cont2["ad_data_fer1"]);
																	
											// Se a data do feriado coincidir com a data do banco de extra
											//Se a data do banco de extra estiver entre as datas de feriado
											if(in_array($cont["data"],$array_ad_data_fer1))
											{
												$adicionais_fer += ((($tarifa_prop * $cont2["ad_feriado_porc"])/100) + $tarifa_prop) * ($cont2["ad_feriado_horas"]/count($array_ad_data_fer1));
											}
											else
											{
												$adicionais_sab += ((($tarifa_prop * $cont2["sabado_porc"])/100) + $tarifa_prop) * ($cont["HA"]+$cont["HAN"]);
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
											//Se a data do banco de extra estiver entre as datas de feriado
											if(in_array($cont["data"],$array_ad_data_fer1))
											{
												$adicionais_fer += ((($tarifa_prop * $cont2["ad_feriado_porc"])/100) + $tarifa_prop) * ($cont2["ad_feriado_horas"]/count($array_ad_data_fer1));
											}
											else
											{
												$adicionais_sem += ((($tarifa_prop * $cont2["semana_porc"])/100) + $tarifa_prop) * ($cont["HA"]+$cont["HAN"]);
											}
											break;
										
									}
									
									//Adicional Noturno
									$adicionais_not = $cont2["ad_noturno_horas"] * $tarifa_prop * ($cont2["ad_noturno_porc"]/100);
								
								}							
							}
						
						break;						
					}		
				}
				
				//Soma os adicionais ao valor total
				$valor_total += ($adicionais_dom+$adicionais_sab+$adicionais_sem+$adicionais_not+$adicionais_fer);
				
				$medicao = $valor_total;
				
				switch ($contrato)
				{
					case 'SC+MENS':
						
						if($dados_form["proporcional"])
						{
							$valor_total += $valor_mensal;
						
							$medicao += $valor_mensal;							
						}
						else
						{
							$valor_total += $tarifa_mens;
						
							$medicao += $tarifa_mens;							
						}
					
					break;
					
					case 'SC+CLT+MENS':
					
						if($dados_form["proporcional"])
						{
							$valor_total += $valor_mensal;
						
							$medicao += $valor_mensal;							
						}
						else
						{
							$valor_total += $tarifa_mens;
						
							$medicao += $tarifa_mens;							
						}
						
						if($dados_form["salarioproporcional"]!="")
						{
							$clt = $salario_proporcional;
						}
						else
						{
							$clt = $tarifa_sal;
						}	
				
					break;	
				}
				
				//Calcula descontos/acrescimos conforme contrato (válido contrato com CLT+...)
				if($dados_form["ferias"]!="")
				{
					if($contrato=='SC+CLT' || $contrato=='SC+CLT+MENS')
					{
						$valor_total -= ($valor_diferencaclt_ferias + $valor_ferias + $valor_fgts + $valor_decimoterceiro + $salario_proporcional);
					}
				}
				elseif($dados_form["rescisao"]!="")
				{
					if($contrato=='SC+CLT' || $contrato=='SC+CLT+MENS')
					{
						$valor_total -= ($valor_diferencaclt_rescisao + $valor_rescisao + $valor_fgts + $valor_decimoterceiro + $salario_proporcional);
					}
				}			
				else
				{		
					if($contrato=='SC+CLT')
					{
						if($dados_form["salarioproporcional"]!="")
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
							//$valor_total = $tarifa_mens - $clt - $valor_fgts - $valor_decimoterceiro;
							//$valor_total = $valor_mensal - $clt - $valor_fgts - $valor_decimoterceiro;
							if($dados_form["proporcional"])
							{
								$valor_total = $valor_mensal - $clt - $valor_fgts - $valor_decimoterceiro;						
							}
							else
							{
								$valor_total = $tarifa_mens - $clt - $valor_fgts - $valor_decimoterceiro;						
							}							
						}					
					}
				}
				
				//Reseta a variável - Será calculada na ação = "impostos"
				$svalor_pagamento = 0;
				
				//MODO MANUAL DE CÁLCULO
				if($dados_form["manual"]!="")
				{
					$medicao = str_replace(",",".",str_replace(".","",$dados_form["manual"]));
					$valor_total = str_replace(",",".",str_replace(".","",$dados_form["manual"]));
					$svalor_pagamento = str_replace(",",".",str_replace(".","",$dados_form["manual"]));
					$descricao_manual = $dados_form["descricao_manual"];
				}
			
				//Desconta o "OUTROS DESCONTOS"
				$valor_total -= $valor_outros_descontos;
				
				//Acrescenta o "OUTROS ACRÉSCIMOS"
				$valor_total += $valor_outros_acrescimos;
				
				//SQL para inserir os valores na tabela.
				$isql = "INSERT INTO ".DATABASE.".fechamento_folha ";
				$isql .= "(id_funcionario, id_salario, data_ini, data_fim, periodo, valor_ferias, valor_rescisao, valor_fgts, valor_decimo_terceiro, total_horas_normais, total_horas_adicionais, valor_medicao, valor_total, valor_imposto, valor_pagamento, valor_pcc, inclui_hora_extra, valor_proporcional, valor_diferenca_ferias, valor_diferenca_rescisao, valor_descontos, valor_acrescimos, observacao) ";
				$isql .= "VALUES ('". $dados_form["funcionario"] ."', ";
				$isql .= "'" . $cont1["id_salario"]. "', ";
				$isql .= "'" . php_mysql($dados_form["data_ini"]) ."', ";
				$isql .= "'" . php_mysql($dados_form["data_fim"]) ."', ";
				$isql .= "'" . substr(php_mysql($dados_form["data_ini"]),0,7) . "," . substr(php_mysql($dados_form["data_fim"]),0,7) . "', ";
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
				$isql .= "'" . $dados_form["horasextras"] . "', ";
				$isql .= "'" . $salario_proporcional . "', ";
				$isql .= "'" . $valor_diferencaclt_ferias . "', ";
				$isql .= "'" . $valor_diferencaclt_rescisao . "', ";
				$isql .= "'" . $valor_outros_descontos . "', ";
				$isql .= "'" . $valor_outros_acrescimos . "', ";
				$isql .= "'" . $descricao_manual . "') ";
	
				$db->insert($isql,'MYSQL');
	
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}	
										
				$cod_fechamento = $db->insert_id;
				
				//Insere Nota Fiscal
				$isql = "INSERT INTO ".DATABASE.".nf_funcionarios ";
				$isql .= "(id_fechamento, nf_valor, nf_ajuda_custo) VALUES (";
				$isql .= "'" . $cod_fechamento . "', ";
				$isql .= "'" . $valor_total . "', ";
				$isql .= "'0') ";
	
				$db->insert($isql,'MYSQL');
	
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}				
						
				$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
				$resposta->addAssign("outros_descontos", "value", "");
				
				$resposta->addAssign("outros_acrescimos", "value", "");
				
				$resposta->addAssign("manual", "value", "");
						
				$resposta->addAlert($msg[1]);
		
			}
			else
			{
				$resposta->addAlert($msg[4]);
			}			
		}
	}

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
		$usql .= "fechamento_folha.reg_del = 1, ";
		$usql .= "fechamento_folha.data_del = '".date('Y-m-d')."', ";
		$usql .= "fechamento_folha.reg_who = '".$_SESSION['id_funcionario']."' ";
		$usql .= "WHERE fechamento_folha.id_fechamento = '".$id."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$usql = "UPDATE ".DATABASE.".nf_funcionarios SET ";
		$usql .= "nf_funcionarios.reg_del = 1, ";
		$usql .= "nf_funcionarios.data_del = '".date('Y-m-d')."', ";
		$usql .= "nf_funcionarios.reg_who = '".$_SESSION['id_funcionario']."' ";
		$usql .= "WHERE nf_funcionarios.id_fechamento = '".$id."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		
		$resposta->addAlert($msg[3]);
	}

	return $resposta;
}

function impostos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta))
	{
		$db = new banco_dados;
		
		//Traz o periodo atual e estabelece o filtro.
		if($dados_form["periodo"])
		{
			$filtro_datas = "AND fechamento_folha.periodo = '" . $dados_form["periodo"] . "' ";
		}
		else
		{
			
			$filtro_datas = "AND SUBSTRING(fechamento_folha.periodo,9,7) = '" . date("Y-m") . "' ";
		
		}		
		
		//CALCULA OS IMPOSTOS DOS FECHAMENTOS DE CADA FUNCIONÁRIO, NO PERÍODO ATUAL
		$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
		$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
		$sql .= "AND fechamento_folha.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= $filtro_datas;
		$sql .= "ORDER BY fechamento_folha.id_fechamento ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$reg1 = $db->array_select;
				
		foreach($reg1 as $cont_fechamento)
		{
		
			$svalor_total_empresa = 0;
			
			//Seleciona todos os funcionários que estão na mesma empresa do funcionário do fechamento.
			$sql = "SELECT * FROM ".DATABASE.".empresa_funcionarios, ".DATABASE.".funcionarios ";
			$sql .= "WHERE funcionarios.id_empfunc = empresa_funcionarios.id_empfunc ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND empresa_funcionarios.reg_del = 0 ";
			$sql .= "AND empresa_funcionarios.id_empfunc = '" . $cont_fechamento["id_empfunc"] . "' ";
		
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$reg2 = $db->array_select;
					
			foreach($reg2 as $cont_func)
			{
				
				//1) Pega o valor total do pagamento (Nota Fiscal normal) de cada Funcionário.
				//2) Checa se existem Notas de Ajuda de Custo, caso positivo soma seu valor ao valor total da empresa.
				$sql = "SELECT * FROM ".DATABASE.".fechamento_folha ";
				$sql .= "LEFT JOIN ".DATABASE.".nf_funcionarios ON (fechamento_folha.id_fechamento = nf_funcionarios.id_fechamento AND nf_funcionarios.nf_ajuda_custo IN (1,2) AND nf_funcionarios.reg_del = 0) ";
				$sql .= "WHERE fechamento_folha.id_funcionario = '" . $cont_func["id_funcionario"] . "' ";
				$sql .= "AND fechamento_folha.reg_del = 0 ";
				$sql .= $filtro_datas;
				
				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
							
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
			$sql .= "AND empresa_funcionarios.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$contempresafunc = $db->array_select[0];		
		
			//Atribui o valor atual a variável de valor do pagamento atual.
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
				//PIS, COFINS, CSL (PCC)
				if (($svalor_total_empresa) >= 215.05)
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
					//Não há imposto a deduzir.
					$imposto_ir_pessoa=0;
				}			
		
			}			
	
			//Atualiza o registro com as informações.
			$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
			$usql .= "valor_imposto = '" . $imposto_ir_pessoa . "', ";
			$usql .= "valor_pcc = '" . $svalor_pis_cofins_csl . "', ";
			$usql .= "valor_pagamento = '" . $svalor_pagamento_atual . "' ";
			$usql .= "WHERE fechamento_folha.id_fechamento = '" . $cont_fechamento["id_fechamento"] . "' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");			
	}

	return $resposta;
}

function liberar_fechamento($dados_form, $id_fechamento)
{	
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	if (empty($id_fechamento))
	{
		$resposta->addAlert('Houve uma falha ao tentar Liberar/Travar o fechamento');
	}
	else
	{
		$db = new banco_dados;
		
		$mensagens = array('Fechamento liberado para o colaborador!', 'Fechamento Não liberado para o colaborador!');
		
		$valorLiberacao = intval($dados_form["chkf_".$id_fechamento]);
				
		$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
		$usql .= "excessao = '" . $valorLiberacao . "' ";
		$usql .= "WHERE id_fechamento = '" . $id_fechamento ."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
			$resposta->addAlert($mensagens[$valorLiberacao]);
	}
	
	return $resposta;
}

function envia_email($dados_form)
{
	$resposta = new xajaxResponse();
	
	$id_fechamento = $dados_form['id_fechamento'];
	
	$envio = false;
	
	$txt_apr = "";
	$txt_rep = "";
	
	$aprovados = "Os seguintes documentos anexados ao sistema foram aprovados:<br>";
	$n_aprovados = "Os seguintes documentos anexados ao sistema não foram aprovados:<br>";		
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE fechamento_folha.id_fechamento = '".$id_fechamento."' ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
	
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
			
	$cont_fun = $db->array_select[0];
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos, ".DATABASE.".fechamento_tipos_tributos ";
	$sql .= "WHERE fechamento_documentos.id_fechamento = '".$id_fechamento."' ";
	$sql .= "AND fechamento_documentos.reg_del = 0 ";
	$sql .= "AND fechamento_tipos_tributos.reg_del = 0 ";
	$sql .= "AND fechamento_documentos.id_fechamento_tipos_tributos = fechamento_tipos_tributos.id_fechamento_tipos_tributos ";
	$sql .= "AND fechamento_documentos.envio_email = 0 ";
	$sql .= "AND fechamento_documentos.conferido IN (1,2) ";
		
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	//se possuir registros, existe documentos anexados
	if($db->numero_registros>0)
	{
		$reg2 = $db->array_select;
		
		foreach($reg2 as $cont)
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
			$usql .= "envio_email = 1 ";
			$usql .= "WHERE fechamento_documentos.id_fechamento_docs = '".$cont["id_fechamento_docs"]."' ";
			$usql .= "AND fechamento_documentos.reg_del = 0 ";
			
			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
		}		
		
		//Concatena mensagem de urgência
		$texto = '<B><FONT FACE=ARIAL COLOR=RED>OBRIGAÇÕES ACESSÓRIAS</FONT></B><BR><br><br>';
		
		$texto .= 'Caro colaborador '.$cont_fun["funcionario"].',<br><br>';
		
		if($txt_apr!='')
		{
			$texto .= $aprovados;
			$texto .= $txt_apr.'<br><br>';
		}
		
		if($txt_rep!='')
		{
			$texto .= $n_aprovados;
			$texto .= $txt_rep.'<br><br>';
		}
		
		$texto .= 'Competência: '.substr($competencia,0,2).'/'.substr($competencia,2,4).'<br><br><br>';
		$texto .= 'Em caso de dúvida, procurar o setor Financeiro.<br><br><br>';
	
		$envio = true;
	}
	else
	{
		$sql = "SELECT id_fechamento FROM ".DATABASE.".fechamento_documentos, ".DATABASE.".fechamento_tipos_tributos ";
		$sql .= "WHERE fechamento_documentos.id_fechamento = '".$id_fechamento."' ";
		$sql .= "AND fechamento_documentos.reg_del = 0 ";
		$sql .= "AND fechamento_tipos_tributos.reg_del = 0 ";
		$sql .= "AND fechamento_documentos.id_fechamento_tipos_tributos = fechamento_tipos_tributos.id_fechamento_tipos_tributos ";
		$sql .= "AND fechamento_documentos.envio_email = 0 ";
			
		$db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
				
		//se não possui registros, não há documentos anexados
		if($db->numero_registros<=0)
		{
			$texto = '<B><FONT FACE=ARIAL COLOR=RED>OBRIGAÇÕES ACESSÓRIAS</FONT></B><BR><br><br>';
			$texto .= 'Caro colaborador '.$cont_fun["funcionario"].',<br><br>';
			$texto .= 'Favor anexar os documentos no sistema.<br><br>';
			$texto .= 'Em caso de dúvida, procurar o setor Financeiro.<br><br><br>';	
		}
		
		$envio = true;
	}
	
	if($envio)
	{
		if(ENVIA_EMAIL)
		{

			$params = array();
			
			$params['subject'] = 'OBRIGAÇÕES ACESSÓRIAS';
			
			$params['emails']['to'][] = array('email' => $cont_fun["email"], 'nome' => $cont_fun["funcionario"]);

			$mail = new email($params);
			
			$mail->montaCorpoEmail(trim($dados_form['conteudo']));//Removi o trecho á direita pois, agora o colaborador digita-ra o que quiser.'<hr />'.$texto);
			
			if(!$mail->Send())
			{
				$resposta->addAlert($mail->ErrorInfo);
			}
			else
			{				
				$resposta->addAlert('E-mail enviado com sucesso.');					
			}
			
			$mail->ClearAddresses();
		}
		else 
		{
			$resposta->addScriptCall('modal', trim($dados_form['conteudo']), '300_650', 'Conteúdo email', 3);
		}

		$resposta->addScript('divPopupInst.destroi();');

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");

	}
	
	return $resposta;
}

function relatorios($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("openpage('Relatorios','relatorios.php?periodo=".$dados_form["periodo"]."',800,600);");
	
	return $resposta;
}


function visualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	if($dados_form["periodo"] && $dados_form["funcionario"])
	{	
		$resposta->addScript("abrejanela('fechamento','fechamento_forn.php?periodo=".$dados_form["periodo"]."&id_funcionario=".$dados_form["funcionario"]."','750','380');");
	}
	else
	{
		$resposta->addAlert('É necessário selecionar um Funcionário e um período.');	
	}
	
	return $resposta;
}

function tipo_contrato($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;

	$sql = "SELECT id_salario FROM ".DATABASE.".salarios ";
	$sql .= "WHERE id_funcionario = '".$dados_form["funcionario"]."' ";
	$sql .= "AND  tipo_contrato LIKE '%CLT%' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY data DESC, id_salario DESC ";

	$db->select($sql,'MYSQL');

	if($db->erro!='')
	{
		die($db->erro);
	}	
	
	if($db->numero_registros>0)
	{	
		$resposta->addScript("campos_clt.style.display = 'inline';");
		$resposta->addScript("campos_clt.style.visibility = 'visible';");
	}
	else
	{
		$resposta->addScript("campos_clt.style.display = 'none';");
		$resposta->addScript("campos_clt.style.visibility = 'collapse';");		
	}

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("impostos");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("liberar_fechamento");
$xajax->registerFunction("envia_email");
$xajax->registerFunction("relatorios");
$xajax->registerFunction("visualizar");
$xajax->registerFunction("tipo_contrato");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

$conf = new configs();

$array_funcionario_values[] = "";
$array_funcionario_output[] = "SELECIONE";

$array_periodo_values[] = "";
$array_periodo_output[] = "ATUAL";

$sql = "SELECT periodo FROM ".DATABASE.".fechamento_folha ";
$sql .= "WHERE fechamento_folha.reg_del = 0 ";
$sql .= "GROUP BY fechamento_folha.periodo ";
$sql .= "ORDER BY fechamento_folha.periodo DESC ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$array_periodo = explode(",",$regs["periodo"]);
	$per_dataini = substr($array_periodo[0],-2,2) . "/" . substr($array_periodo[0],0,4);
	$per_datafin = substr($array_periodo[1],-2,2) . "/" . substr($array_periodo[1],0,4);
	
	$array_periodo_values[] = $regs["periodo"];
	$array_periodo_output[] = $per_dataini . " - " . $per_datafin;
}

$mes_per = date('m');

if ($mes_per==1)
{
	$mes = 12;
	$ano = date('Y')-1;
	$data_ini = "26/" . $mes . "/" . $ano;
	$datafim = "25/01/" . date('Y');
}
else
{ 
	$mesant = $mes_per - 1;
	$ano = date('Y'); //retirado "-1" 07/02/2008 
	$data_ini = "26/" . sprintf("%02d",$mesant) . "/" . $ano;
	$datafim = "25/" . $mes_per . "/" . $ano;
}

$smarty->assign("revisao_documento","V9");

$smarty->assign("campo",$conf->campos('fechamento_pj'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("lupa",DIR_IMAGENS.'procurar.png');

$smarty->assign("data_inicial",$data_ini);

$smarty->assign("data_final",$datafim);

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);

$smarty->assign("option_periodo_values",$array_periodo_values);
$smarty->assign("option_periodo_output",$array_periodo_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('fechamento_pj.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

<!--
//captura a tecla enter
document.onkeypress = keyhandler;

function keyhandler(e) 
{
	if (document.layers)
		Key = e.which;
	else
		Key = window.event.keyCode;

	if (Key != 0)
		if (Key == 13)

			xajax_insere(xajax.getFormValues('frm'));
}
//-->

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader('Funcionário,Período,Total,1.5%,4.65%,Pgto,NL,HA,DT,NF,A,DC,EM,D');
	mygrid.setInitWidths("*,150,80,50,50,80,40,40,40,40,40,40,40,40");
	mygrid.setColAlign("left,center,right,right,right,right,right,center,center,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function abreHE(id_funcionario, data_ini, data_fim)
{
	//Javascript
	if(id_funcionario && data_ini && data_fim)
	{
		params = "width=800,height=300,resizable=0,status=1,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0, top="+((screen.height/2)-125)+", left="+((screen.width/2)-300)+" ";
		wnd_horasextras = window.open('fechamentofolha_horaextra.php?id_funcionario='+id_funcionario+'&data_ini='+data_ini+'&data_fim='+data_fim+'','wnd_horasextras', params);
	}
	else
	{
		alert('É necessário selecionar um Funcionário e digitar as datas do período!');
		frm.horasextras0.checked = true;
	}
}

function fn_manual(chkbox)
{

	if(chkbox.checked==true && confirm('Essa ação fará com que o cálculo automático não seja utilizado. Deseja continuar?'))
	{
		frm.manual.disabled=false;
		document.getElementById('label_manual').style.display = '';
		document.getElementById('label_descricao').style.display = '';
		document.getElementById('text_descricao').style.display = '';
		frm.manual.focus();		
		document.getElementById('campos_clt').style.visibility = 'visible';
		document.getElementById('campos_clt').style.display = 'inline';
	}
	else
	{
		chkbox.checked=false;
		frm.manual.value="";
		document.getElementById('descricao_manual').innerHTML = "";
		document.getElementById('label_manual').style.display = 'none';
		document.getElementById('label_descricao').style.display = 'none';
		document.getElementById('text_descricao').style.display = 'none';
		frm.manual.disabled=true;		
		document.getElementById('campos_clt').style.visibility = 'collapse';
		document.getElementById('campos_clt').style.display = 'none';		
	}
}

function abrejanela(nome,caminho,largura,altura)
{

  params = "width="+largura+",height="+altura+",resizable=0,status=0,scrollbars=0,toolbar=0,location=0,directories=0,menubar=0, top="+(screen.height/2-altura/2)+", left="+(screen.width/2-largura/2)+" ";
  windows = window.open( caminho, nome , params);
  if(window.focus) 
  {
	setTimeout("windows.focus()",100);
  }
}

var editor;

function telaEmail(id_fechamento)
{
	var html = '<form name="frm_email" id="frm_email" method="post">';
			html +=	'<label class="labels">Digite o conteúdo do E-mail:</label>';
			html += '<div id="editor" style="width:100%; height:240px; border:#909090 1px solid;"></div>';
			html += '<textarea id="conteudo" name="conteudo" class="caixa" style="display:none;"></textarea>';
			html += '<input type="hidden" name="id_fechamento" id="id_fechamento" value="'+id_fechamento+'" />';
			html +=	'<input type="button" class="class_botao" style="float:right;margin-top:5px" value="Enviar E-mail" onclick="document.getElementById(\'conteudo\').value = editor.getContent();xajax_envia_email(xajax.getFormValues(\'frm_email\'))" />';
		html += '</form>';
			
	modal(html, 'p');

	editor = new dhtmlXEditor("editor");
}
</script>