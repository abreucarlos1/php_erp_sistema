<?php
/*
		Formulário de Formulario Medição
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/formulario_medicao.php
	
		Versão 0 --> VERSÃO INICIAL : 04/04/2016 - Carlos Abreu
		Versão 1 --> atualização layout - Carlos Abreu - 23/03/2017
		Versão 2 --> Alteração das somatorias por disciplina e na EDT - Chamado - #1997 - Carlos Abreu - 07/08/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(555))
{
	nao_permitido();
}


function calc_acumulado($projeto,$edt)
{
	if($projeto!='' && $edt!='')
	{
		$db = new banco_dados;
		
		$prc_acumulado = 0;
		
		$vlr_acumulado = 0;
		
		//recalcula os percentuais e valores
		$sql = "SELECT * FROM ".DATABASE.".medicoes ";
		$sql .= "WHERE reg_del = 0 ";
		$sql .= "AND medicoes.projeto = '".$projeto."' ";
		$sql .= "AND medicoes.edt = '".$edt."' ";
		$sql .= "ORDER BY medicoes.periodo ASC ";
		
		$db->select($sql,'MYSQL',true);
		
		foreach($db->array_select as $regs)
		{
			$prc_acumulado += $regs["perc_medido"];
			
			$vlr_acumulado += $regs["valor_medido"];
			
			$usql = "UPDATE ".DATABASE.".medicoes SET ";
			$usql .= "perc_acumulado = '".floatval(str_replace(",",".",$prc_acumulado))."', ";
			$usql .= "valor_acumulado = '".floatval(str_replace(",",".",$vlr_acumulado))."' ";
			$usql .= "WHERE id_medicao = '".$regs["id_medicao"]."' ";
			$usql .= "AND reg_del = 0 ";				
			
			$db->update($usql,'MYSQL');						
		}
	}
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btn_inserir","value","Inserir");
	
	$resposta->addScript("seleciona_combo('" . date("m"). "','mes');");
	
	$resposta->addAssign('ano','value',date("Y"));
	
	$resposta->addAssign('id_medicao','value','');
	
	$resposta->addAssign('percentual','value','0');
	
	$resposta->addAssign('valor_med','value','0');
	
	$resposta->addEvent("btn_inserir","onclick","xajax_inserir(xajax.getFormValues('frm'));");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm');");	
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$array_data = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');	

	/*
	//sumariza o projeto
	$sql = "SELECT AF5_ORCAME, AF5_DESCRI, AF5_TOTAL FROM AF5010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND AF5_ORCAME = '".$dados_form["escolhaos"]."' ";
	$sql .= "AND AF5_NIVEL = '001' ";
	
	$db->select($sql,'MSSQL',true);
	*/
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{				
		$regs0 = $db->array_select[0];
		
		//$total_projeto = $regs0["AF5_TOTAL"];
		
		//sumariza o ajuste
		$sql = "SELECT * FROM ".DATABASE.".medicoes ";
		$sql .= "WHERE reg_del = 0 ";
		$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
		$sql .= "GROUP BY edt ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			foreach($db->array_select as $regs1)
			{
				$soma_ajuste += $regs1["valor_ajuste"];	
			}
		}
		
		$total = $total_projeto + $soma_ajuste;
		
		$resposta->addAssign('total_os','value',number_format($total,2,',',''));		
		
		$sql = "SELECT SUM(valor_medido) as medicao FROM ".DATABASE.".medicoes ";
		$sql .= "WHERE reg_del = 0 ";
		$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
	
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$regs1 = $db->array_select[0];
			
			$total_os = $total;
			
			$percentual = ($regs1["medicao"]*100)/$total_os;
			
			$valor = $total_os*($percentual/100);
			
			$diferenca = $total_os-$valor;
			
			$xml->startElement('row');
				$xml->writeAttribute('id','id_0');
				//$xml->writeElement('cell', trim($regs0["AF5_ORCAME"]));
				//$xml->writeElement('cell', trim($regs0["AF5_DESCRI"]));
				$xml->writeElement('cell', '');
				$xml->writeElement('cell', number_format($percentual,2,',',''));
				$xml->writeElement('cell', number_format($valor,2,',',''));
				$xml->writeElement('cell', '');
				$xml->writeElement('cell', '');
				$xml->writeElement('cell', number_format($diferenca,2,',',''));
				
			$xml->endElement();
			
		}		
	}		
	
	//SELECIONA AS DISCIPLINAS
	/*
	$sql = "SELECT * FROM AE5010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	
	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{				
		foreach($db->array_select as $regs)
		{
			$array_disciplina[trim($regs["AE5_GRPCOM"])] = trim($regs["AE5_DESCRI"]);
		}
	}
	*/	
	
	$sql = "SELECT * FROM ".DATABASE.".medicoes ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
	$sql .= "AND medicoes.edt = '".$dados_form["escolhaedt"]."' ";
	$sql .= "ORDER BY medicoes.periodo DESC ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{			
			$diferenca = $regs["valor_total"]-$regs["valor_acumulado"];
			
			$mes = $array_data[date("m",strtotime($regs["periodo"]))-1];
			
			$xml->startElement('row');
				$xml->writeAttribute('id','id_'.$regs["id_medicao"]);
				$xml->startElement('cell');
					$xml->text($regs["projeto"]);
				$xml->endElement();
				
				$xml->startElement('cell');
					$xml->text($array_disciplina[$regs["edt"]]);
				$xml->endElement();
				
				$xml->startElement('cell');
					$xml->text($mes.'/'.date("Y",strtotime($regs["periodo"])));
				$xml->endElement();
				
				$xml->startElement('cell');
					$xml->text(number_format($regs["perc_medido"],2,',',''));
				$xml->endElement();
				
				$xml->startElement('cell');
					$xml->text(number_format($regs["valor_medido"],2,',',''));
				$xml->endElement();
				
				$xml->startElement('cell');
					$xml->text(number_format($regs["perc_acumulado"],2,',',''));
				$xml->endElement();
				
				$xml->startElement('cell');
					$xml->text(number_format($regs["valor_acumulado"],2,',',''));
				$xml->endElement();
				
				$xml->startElement('cell');
					$xml->text(number_format($diferenca,2,',',''));
				$xml->endElement();
				
				$xml->startElement('cell');
					
					$img = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma&nbsp;a&nbsp;exclusão&nbsp;da&nbsp;medição?")){xajax_excluir("'.$regs["id_medicao"].'");}>';
					
					$xml->text($img);
				$xml->endElement();
				
			$xml->endElement();
		}
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('medicoes',true,'420','".$conteudo."');");
	
	return $resposta;		
}

function preencheos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	/*
	$sql = "SELECT AF1_ORCAME, AF1_DESCRI FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";	
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "AND AF8_ENCPRJ <> '1' ";
	
	if($dados_form["escolhacoord"]>0)
	{
		$sql .= "AND (AF1010.AF1_COORD1 = '". $dados_form["escolhacoord"] ."' OR AF1010.AF1_COORD2 = '". $dados_form["escolhacoord"] ."') " ;
	}
	
	$sql .= "GROUP BY AF1_ORCAME, AF1_DESCRI ";
	$sql .= "ORDER BY AF1_ORCAME ";
	
	$db->select($sql,'MSSQL', true);

	

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$limp = "xajax.$('escolhaos').length = null";
		
		$resposta->addScript($limp);
		
		$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('SELECIONE','0');";
	
		foreach($db->array_select as $regs)
		{
			$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". trim($regs["AF1_ORCAME"])." - ".trim(addslashes($regs["AF1_DESCRI"]))."','". $regs["AF1_ORCAME"] ."');";
		}
	
		$resposta->addScript($comb);
	}

	*/

	return $resposta;
}

function preencheestrut($projeto)
{
	$resposta = new xajaxResponse();	
	
	$db = new banco_dados;
	
	/*
	$sql = "SELECT AF2_GRPCOM, AE5_DESCRI FROM AF5010 WITH(NOLOCK), AE5010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE AE5010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF5010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF5_ORCAME = AF2_ORCAME ";
	$sql .= "AND AF5_EDT = AF2_EDTPAI ";
	$sql .= "AND AF2_GRPCOM = AE5_GRPCOM ";
	$sql .= "AND AF5_ORCAME = '".$projeto."' ";
	$sql .= "AND AF5_EDTPAI <> '' ";
	$sql .= "GROUP BY AF2_GRPCOM, AE5_DESCRI ";
	
	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$array_edt = $db->array_select;
		
		$limp = "xajax.$('escolhaedt').length = null";
		
		$resposta->addScript($limp);
		
		$comb = "xajax.$('escolhaedt').options[xajax.$('escolhaedt').length] = new Option('SELECIONE','0');";
		
		foreach($array_edt as $regs)
		{	
			$comb .= "xajax.$('escolhaedt').options[xajax.$('escolhaedt').length] = new Option('". $regs["AE5_DESCRI"]."','". $regs["AF2_GRPCOM"] ."');";
		}
		
		$resposta->addScript($comb);		
	}
	
	*/

	$resposta->addScript('xajax_atualizatabela(xajax.getFormValues("frm"));');
	
	return $resposta;
}

function preenchevalor($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$resposta->addAssign('percentual','value',0);
	
	$resposta->addAssign('valor_med','value',0);
	
	$resposta->addAssign('id_medicao','value','');
	
	$resposta->addAssign('btn_inserir','value','Inserir');
	
	$resposta->addEvent("btn_inserir","onclick","xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");	
	
	/*
	$sql = "SELECT SUM(AF2_TOTAL) AS TOTAL FROM AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND AF2_ORCAME = '".$dados_form["escolhaos"]."' ";
	$sql .= "AND AF2_GRPCOM = '".$dados_form["escolhaedt"]."' ";
	
	$db->select($sql,'MSSQL',true);

	*/
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$regs = $db->array_select[0];
		
		$sql = "SELECT * FROM ".DATABASE.".medicoes ";
		$sql .= "WHERE reg_del = 0 ";
		$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
		$sql .= "AND medicoes.edt = '".$dados_form["escolhaedt"]."' ";
		$sql .= "LIMIT 1 ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$regs1 = $db->array_select[0];	
		}
		
		$valor_ajuste = $regs["TOTAL"]+$regs1["valor_ajuste"];
		
		$resposta->addAssign('ajuste','value',number_format($regs1["valor_ajuste"],2,',',''));
		
		$resposta->addAssign('valor','value',number_format($regs["TOTAL"],2,',',''));
				
		$resposta->addAssign('valor_ajuste','value',number_format($valor_ajuste,2,',',''));		
		
		$resposta->addAssign('percentual','value','0');
		
		$resposta->addAssign('valor_med','value','0');
	}

	return $resposta;
}

function calcula($dados_form, $forma)
{
	$resposta = new xajaxResponse();
	
	if($forma=='valor')
	{
		$med = round((str_replace(",",'.',$dados_form["valor_ajuste"])*str_replace(",",".",$dados_form["percentual"]))/100,2);
		
		$resposta->addAssign('valor_med','value',number_format($med,2,',',''));
	}
	
	if($forma=='percent')
	{
		$med = round((str_replace(",",".",$dados_form["valor_med"])/str_replace(",",".",$dados_form["valor_ajuste"]))*100,2);
		
		$resposta->addAssign('percentual','value',number_format($med,2,',',''));		
	}
	
	if($forma=='ajuste')
	{
		$med = round(str_replace(",",'.',$dados_form["valor"])+str_replace(",",'.',$dados_form["ajuste"]),2);
		
		$resposta->addAssign('valor_ajuste','value',number_format($med,2,",",''));
	}

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".medicoes ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "AND medicoes.periodo = '".date('Y-m-d',mktime(0,0,0,$dados_form["mes"],1,$dados_form["ano"]))."' ";
	$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
	$sql .= "AND medicoes.edt = '".$dados_form["escolhaedt"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		//não existe o periodo cadastrado
		if($db->numero_registros<=0)
		{			
			$sql = "SELECT SUM(perc_medido) as prc_acumulado, SUM(valor_medido) as vlr_acumulado FROM ".DATABASE.".medicoes ";
			$sql .= "WHERE reg_del = 0 ";
			$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
			$sql .= "AND medicoes.edt = '".$dados_form["escolhaedt"]."' ";
		
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{	
				$regs2 = $db->array_select[0];
				
				$prc_acumulado = $regs2["prc_acumulado"]+floatval(str_replace(",",".",$dados_form["percentual"]));
			}
			
			if($prc_acumulado<=100)
			{			
				$isql = "INSERT INTO ".DATABASE.".medicoes (projeto, edt, periodo, valor_total, valor_ajuste, perc_medido, valor_medido) VALUES ( ";
				$isql .= "'".$dados_form["escolhaos"]."',";
				$isql .= "'".$dados_form["escolhaedt"]."',";
				$isql .= "'".date('Y-m-d',mktime(0,0,0,$dados_form["mes"],1,$dados_form["ano"])) ."',";
				$isql .= "'".floatval(str_replace(",",".",$dados_form["valor_ajuste"]))."',";
				$isql .= "'".floatval(str_replace(",",".",$dados_form["ajuste"]))."',";
				$isql .= "'".floatval(str_replace(",",".",$dados_form["percentual"]))."',";
				$isql .= "'".floatval(str_replace(",",".",$dados_form["valor_med"]))."')";
				
				$db->insert($isql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$resposta->addAssign('percentual','value','0');
				
				$resposta->addAssign('valor_med','value','0');
				
				calc_acumulado($dados_form["escolhaos"],$dados_form["escolhaedt"]);
				
				$resposta->addScript('xajax_atualizatabela(xajax.getFormValues("frm"));');
			}
			else
			{
				$resposta->addAlert('O percentual acumulado não pode ser maior que 100%.');	
			}
		}
		else
		{
			$resposta->addAlert('Já existe o periodo cadastrado nesta medição.');	
		}
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".medicoes ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "AND medicoes.id_medicao = '".$id."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		//não existe o periodo cadastrado
		if($db->numero_registros>=1)
		{			
			$regs = $db->array_select[0];
			
			$resposta->addScript("seleciona_combo('" . date("m",strtotime($regs["periodo"])). "','mes');");
			
			$resposta->addAssign('ano','value',date("Y",strtotime($regs["periodo"])));
			
			$resposta->addAssign('percentual','value',number_format($regs["perc_medido"],2,',',''));
			
			$resposta->addAssign('valor_med','value',number_format($regs["valor_medido"],2,',',''));
			
			$resposta->addAssign('id_medicao','value',$id);
			
			$resposta->addAssign('btn_inserir','value','Atualizar');
			
			$resposta->addEvent("btn_inserir","onclick","xajax_atualizar(xajax.getFormValues('frm'));");
			
			$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");			
		}
		else
		{
			$resposta->addAlert('Erro.');	
		}
	}

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$array_index = NULL;

	$sql = "SELECT * FROM ".DATABASE.".medicoes ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "AND medicoes.periodo = '".date('Y-m-d',mktime(0,0,0,$dados_form["mes"],1,$dados_form["ano"]))."' ";
	$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
	$sql .= "AND medicoes.edt = '".$dados_form["escolhaedt"]."' ";
	$sql .= "AND medicoes.id_medicao <> '".$dados_form["id_medicao"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		//não existe o periodo cadastrado
		if($db->numero_registros<=0)
		{
			
			$sql = "SELECT SUM(perc_medido) as prc_acumulado, SUM(valor_medido) as vlr_acumulado FROM ".DATABASE.".medicoes ";
			$sql .= "WHERE reg_del = 0 ";
			$sql .= "AND medicoes.projeto = '".$dados_form["escolhaos"]."' ";
			$sql .= "AND medicoes.edt = '".$dados_form["escolhaedt"]."' ";
			$sql .= "AND medicoes.id_medicao <> '".$dados_form["id_medicao"]."' ";
		
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{	
				$regs2 = $db->array_select[0];
				
				$prc_acumulado = $regs2["prc_acumulado"]+floatval(str_replace(",",".",$dados_form["percentual"]));
			}
			
			if($prc_acumulado<=100)
			{			
				$usql = "UPDATE ".DATABASE.".medicoes SET ";
				$usql .= "periodo = '".date('Y-m-d',mktime(0,0,0,$dados_form["mes"],1,$dados_form["ano"]))."', ";
				$usql .= "valor_total = '" . floatval(str_replace(",",".",$dados_form["valor_ajuste"])) . "', ";
				$usql .= "valor_ajuste = '" . floatval(str_replace(",",".",$dados_form["ajuste"])) . "', ";
				$usql .= "perc_medido = '".floatval(str_replace(",",".",$dados_form["percentual"]))."', ";
				$usql .= "valor_medido = '".floatval(str_replace(",",".",$dados_form["valor_med"]))."' ";
				$usql .= "WHERE id_medicao = '".$dados_form["id_medicao"]."' ";
				$usql .= "AND reg_del = 0 ";				
				
				$db->update($usql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$resposta->addAssign('percentual','value','0');
				
				$resposta->addAssign('valor_med','value','0');
				
				calc_acumulado($dados_form["escolhaos"],$dados_form["escolhaedt"]);
				
				$resposta->addScript('xajax_atualizatabela(xajax.getFormValues("frm"));');
			}
			else
			{
				$resposta->addAlert('O percentual acumulado não pode ser maior que 100%.');	
			}
		}
		else
		{
			$resposta->addAlert('Já existe o periodo cadastrado nesta medição.');	
		}
	}
	
	$resposta->addAssign('id_medicao','value','');
	
	$resposta->addAssign('btn_inserir','value','Inserir');
	
	$resposta->addEvent("btn_inserir","onclick","xajax_insere(xajax.getFormValues('frm'));");

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".medicoes ";
	$sql .= "WHERE medicoes.id_medicao = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$regs = $db->array_select[0];
		
		$projeto = $regs["projeto"];
		
		$edt = $regs["edt"];	
	}
	
	$usql = "UPDATE ".DATABASE.".medicoes SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE medicoes.id_medicao = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	calc_acumulado($projeto,$edt);
	
	$resposta->addScript('xajax_voltar();');
	
	$resposta->addScript('xajax_atualizatabela(xajax.getFormValues("frm"));');
	
	return $resposta;	
}	

$xajax->registerFunction("preencheestrut");

$xajax->registerFunction("atualizatabela");

$xajax->registerFunction("preencheos");

$xajax->registerFunction("preenchevalor");

$xajax->registerFunction("calcula");

$xajax->registerFunction("insere");

$xajax->registerFunction("editar");

$xajax->registerFunction("atualizar");

$xajax->registerFunction("excluir");

$xajax->registerFunction("voltar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_preencheos(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);

	mygrid.enableRowsHover(true,'cor_mouseover');
	
	function doOnRowSelected(row,col)
	{
		if(col<=7 && row!='id_0')
		{
			var id = row.split('_');
								
			xajax_editar(id[1]);

			return true;
		}
	}
	
	mygrid.attachEvent("onRowSelect",doOnRowSelected);	

	mygrid.setHeader("Projeto,Disc.,Período,Percentual,Valor,%&nbsp;acumulado, Valor&nbsp;acumulado, Diferença/Saldo,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("100,*,100,75,100,150,150,150,25");
	mygrid.setColAlign("center,left,left,left,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php

$conf = new configs();

$db = new banco_dados;

$array = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

for($i=1;$i<=12;$i++)
{
	$array_per_values[] = sprintf("%02d",$i);
	
	$array_per_output[] = $array[$i-1];
	
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

/*
$sql = "SELECT PA7_ID, PA7_NOME FROM PA7010 WITH(NOLOCK), AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
$sql .= "AND AF8_ENCPRJ <> '1' ";
$sql .= "AND AF2_CODIGO <> '' ";
$sql .= "AND (PA7010.PA7_ID = AF1010.AF1_COORD1 ";
$sql .= "OR PA7010.PA7_ID = AF1010.AF1_COORD2) ";
$sql .= "GROUP BY PA7_ID, PA7_NOME ";
$sql .= "ORDER BY PA7010.PA7_NOME ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($sql);
}
else
{
	$array_coord_val[] = '-1';
	$array_coord_out[] = 'TODOS';
	
	foreach($db->array_select as $regs)
	{
		$array_coord_val[] = $regs["PA7_ID"];
		
		$array_coord_out[] = trim($regs["PA7_NOME"]);
	}
}
*/

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('formulario_medicao'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_per_values",$array_per_values);
$smarty->assign("option_per_id",$index);
$smarty->assign("option_per_output",$array_per_output);

$smarty->assign("option_coord_values",$array_coord_val);
$smarty->assign("option_coord_output",$array_coord_out);

$smarty->assign("ano",date('Y'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('formulario_medicao.tpl');
?>