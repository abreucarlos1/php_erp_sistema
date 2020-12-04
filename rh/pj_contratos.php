<?php
/*
    Formulário de Contratos PJ - RH	
    
    Criado por Carlos Abreu  
    
    local/Nome do arquivo:
    ../rh/pj_contratos.php
    
    Versão 0 --> VERSÃO INICIAL : 10/05/2013 - Carlos Abreu
    Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
	Versão 3 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(303))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('pj_contratos',$resposta);
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
	}
	
	$sql = "SELECT * FROM ".DATABASE.".pj_contratos, ".DATABASE.".pj_tipo_contratacao ";
	$sql .= "WHERE pj_contratos.id_tipo_contratacao = pj_tipo_contratacao.id_tipo_contratacao ";
	$sql .= "AND pj_contratos.reg_del = 0 ";
	$sql .= "AND pj_tipo_contratacao.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY data_inicio ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$array_contratos = $db->array_select;

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($array_contratos as $cont_desp)
	{
		if($cont_resp["id_tipo_contratacao"]!=4)
		{
			$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
			$sql .= "WHERE funcionarios.id_funcionario = '".$cont_desp["id_funcionario"]."' ";
			$sql .= "AND funcionarios.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$cont0 = $db->array_select[0];
			
			$funcionario = $cont0["funcionario"];
			
		}
		else
		{
			$funcionario = $cont_resp["nome_subcontratado"];
		}
		
		$sql = "SELECT * FROM SA2010 WITH(NOLOCK) ";
		$sql .= "WHERE SA2010.A2_COD = '".$cont_desp["id_empresa"]."' ";
		$sql .= "AND D_E_L_E_T_ = ''";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$cont1 = $db->array_select[0];
		
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont_desp["id_contrato"]);
			
			$xml->startElement('cell');
				$xml->text(sprintf("%04d",$cont_desp["id_contrato"])."/".substr($cont_desp["data_inicio"],0,4));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($funcionario);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont1["A2_NOME"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["tipo_contratacao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'impressora.png" style="cursor:pointer;" onclick=imprimir_contrato("'.$cont_desp["id_contrato"].'");\>');
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'editar.png" style="cursor:pointer;" onclick=openpage("Adendos","pj_contratos_adendos.php?id_contrato='.$cont_desp["id_contrato"].'",1100,800);\>');
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim($cont_desp["clausula"]).'")){xajax_excluir("'.$cont_desp["id_contrato"].'","'. $cont_desp["clausula"].'");}>');
			$xml->endElement();
			
		$xml->endElement();	
		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid',true,'350','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta))
	{
		$db = new banco_dados;
		
		if($dados_form["opcao_contratacao"]!='' && $dados_form["funcionario"]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".pj_contratos ";
			$sql .= "WHERE id_tipo_contratacao = '".$dados_form["opcao_contratacao"]."' ";
			$sql .= "AND id_funcionario = '".$dados_form["funcionario"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}			
			
			if($db->numero_registros<=0)
			{	
			
				if($dados_form["opcao_contratacao"]!=4)
				{
					$funcionario = $dados_form["funcionario"];
					
					$colaborador = "";
				}
				else
				{
					$colaborador = maiusculas($dados_form["funcionario"]);
					
					$funcionario = "";	
				}
				
				$isql = "INSERT INTO ".DATABASE.".pj_contratos ";
				$isql .= "(id_tipo_contratacao, id_funcionario, nome_subcontratado, id_empresa, id_clausula_reajuste, id_clausula_refeicao, ";
				$isql .= "id_clausula_transporte, id_clausula_hospedagem, id_clausula_refeicao_mob, id_clausula_transporte_mob, id_clausula_hospedagem_mob, id_clausula_tipo_contrato, ";
				$isql .= "valor_contrato, id_disciplina, id_local_trabalho, data_inicio, data_fim, vigencia) ";
				$isql .= "VALUES ('" . $dados_form["opcao_contratacao"] . "', ";
				$isql .= "'" . $funcionario . "', ";
				$isql .= "'" . $colaborador . "', ";
				$isql .= "'" . $dados_form["fornecedor"] . "', ";
				$isql .= "'" . $dados_form["reajuste"] . "', ";
				$isql .= "'" . $dados_form["refeicao"] . "', ";
				$isql .= "'" . $dados_form["transporte"] . "', ";
				$isql .= "'" . $dados_form["hospedagem"] . "', ";
				$isql .= "'" . $dados_form["refeicao_mob"] . "', ";
				$isql .= "'" . $dados_form["transporte_mob"] . "', ";
				$isql .= "'" . $dados_form["hospedagem_mob"] . "', ";
				$isql .= "'" . $dados_form["tipo_contrato"] . "', ";
				$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor_contrato"])) . "', ";
				$isql .= "'" . $dados_form["disciplina"] . "', ";
				$isql .= "'" . $dados_form["local_trabalho"] . "', ";
				$isql .= "'" . php_mysql($dados_form["data_inicio"]) . "', ";
				$isql .= "'" . php_mysql($dados_form["data_fim"]) . "', ";
				$isql .= "'" . $dados_form["vigencia"] . "') ";
		
				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}				
					
				$resposta->addScript("xajax_atualizatabela('');");
				
				$resposta->addScript('xajax_voltar();');
			
				$resposta->addAlert($msg[1]);
			}
			else
			{
				$resposta->addAlert($msg[5]);
			}
	
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
			
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes();

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".pj_contratos ";
	$sql .= "WHERE id_contrato = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_contrato", "value", $id);
	
	$resposta->addScript("seleciona_combo(".$regs["id_tipo_contratacao"].",'opcao_contratacao');");

	if($regs["id_tipo_contratacao"]!=4)
	{
		$resposta->AddScript("xajax_colaborador(".$regs["id_tipo_contratacao"].",".$regs["id_funcionario"].");");	
	}
	else
	{
		$resposta->AddScript("xajax_colaborador(".$regs["id_tipo_contratacao"].",".$regs["nome_subcontratado"].");");
	}
	
	$resposta->addScript("seleciona_combo('".$regs["id_empresa"]."','fornecedor');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_disciplina"].",'disciplina');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_local_trabalho"].",'local_trabalho');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_tipo_contrato"].",'tipo_contrato');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_reajuste"].",'reajuste');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_refeicao"].",'refeicao');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_transporte"].",'transporte');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_hospedagem"].",'hospedagem');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_refeicao_mob"].",'refeicao_mob');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_transporte_mob"].",'transporte_mob');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_clausula_hospedagem_mob"].",'hospedagem_mob');");
	
	$resposta->addAssign("data_inicio", "value", mysql_php($regs["data_inicio"]));
	
	$resposta->addAssign("data_fim", "value", mysql_php($regs["data_fim"]));
	
	$resposta->addAssign("vigencia", "value", $regs["vigencia"]);
	
	$resposta->addAssign("valor_contrato", "value", formatavalor($regs["valor_contrato"]));
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta))
	{
		$db = new banco_dados;
		
		if($dados_form["opcao_contratacao"]!='' && $dados_form["funcionario"]!='')
		{
			
			  if($dados_form["opcao_contratacao"]!=4)
			  {
				  $funcionario = $dados_form["funcionario"];
				  
				  $colaborador = "";
			  }
			  else
			  {
				  $colaborador = maiusculas($dados_form["funcionario"]);
				  
				  $funcionario = "";	
			  }
		
			$sql = "SELECT * FROM ".DATABASE.".pj_contratos ";
			$sql .= "WHERE id_tipo_contratacao = '".$dados_form["opcao_contratacao"]."' ";
			$sql .= "AND id_funcionario = '".$funcionario."' ";
			$sql .= "AND id_contrato <> '".$dados_form["id_contrato"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".pj_contratos SET ";
				$usql .= "id_tipo_contratacao = '" . $dados_form["opcao_contratacao"] . "', ";
				$usql .= "id_funcionario = '" . $funcionario . "', ";
				$usql .= "nome_subcontratado = '" . $colaborador . "', ";
				$usql .= "id_empresa = '" . $dados_form["fornecedor"] . "', ";
				$usql .= "id_clausula_reajuste = '" . $dados_form["reajuste"] . "', ";
				$usql .= "id_clausula_refeicao = '" . $dados_form["refeicao"] . "', ";
				$usql .= "id_clausula_transporte = '" . $dados_form["transporte"] . "', ";
				$usql .= "id_clausula_hospedagem = '" . $dados_form["hospedagem"] . "', ";
				$usql .= "id_clausula_refeicao_mob = '" . $dados_form["refeicao_mob"] . "', ";
				$usql .= "id_clausula_transporte_mob = '" . $dados_form["transporte_mob"] . "', ";
				$usql .= "id_clausula_hospedagem_mob = '" . $dados_form["hospedagem_mob"] . "', ";
				$usql .= "id_clausula_tipo_contrato = '" . $dados_form["tipo_contrato"] . "', ";
				$usql .= "id_local_trabalho = '" . $dados_form["local_trabalho"] . "', ";
				$usql .= "valor_contrato = '" .str_replace(",",".",str_replace(".","",$dados_form["valor_contrato"]))."', ";
				$usql .= "id_disciplina = '" . $dados_form["disciplina"] . "' ";
				$usql .= "WHERE id_contrato = '".$dados_form["id_contrato"]."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$resposta->addAlert($msg[2]);
				
				$resposta->addScript("xajax_voltar();");
		
				$resposta->addScript("xajax_atualizatabela('');");
			}
			else
			{
				$resposta->addAlert($msg[5]);
			}
			
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
	}

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{		
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".pj_contratos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_contrato = '".$id."' ";
	
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addAlert($what . $msg[3]);		
	}

	return $resposta;
}

//SELECIONA O COLABORADOR CONFORME OPÇÃO DE CONTRATAÇÃO
function colaborador($opcao_contratacao, $selecionado=0)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$resposta->addScript("combo_destino = document.getElementById('funcionario');");
	
	$resposta->addScriptCall("limpa_combo('funcionario')");
	
	//não é pacote
	if($opcao_contratacao!=4)
	{
			
		$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".salarios ";
		$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND funcionarios.id_salario = salarios.id_salario ";
		$sql .= "AND salarios. tipo_contrato NOT IN ('CLT','EST','SOCIO') ";
		$sql .= "ORDER BY funcionario ";		

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$i = 0;
				
		foreach ($db->array_select as $regs)
		{
			if($regs["id_funcionario"]==$selecionado)
			{
				$sel = 'true';
			}
			else
			{
				$sel = 'false';
			}
			
			if($i==0)
			{
				$def = 'true';
			}
			else
			{
				$def = 'false';
			}
			
			$i = 1;
		
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["funcionario"]."', '".$regs["id_funcionario"]."',".$def.",".$sel.");");
		}
	
	}
	else
	{
		$sql = "SELECT * FROM SA2010 WITH(NOLOCK) ";
		$sql .= "WHERE SA2010.A2_MSBLQL <> '1' ";
		$sql .= "AND SA2010.D_E_L_E_T_ = '' ";
		$sql .= "AND SA2010.A2_CONTATO <> '' ";
		$sql .= "ORDER BY SA2010.A2_CONTATO ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$i = 0;
		
		foreach($db->array_select as $regs)
		{
			if($regs["A2_CONTATO"]==$selecionado)
			{
				$sel = 'true';
			}
			else
			{
				$sel = 'false';
			}
			
			if($i==0)
			{
				$def = 'true';
			}
			else
			{
				$def = 'false';
			}
			
			$i = 1;
		
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["A2_CONTATO"]."', '".$regs["A2_CONTATO"]."',".$def.",".$sel.");");
		}
	}

	return $resposta;
}

//SELECIONA O FORNECEDOR CONFORME OPÇÃO DE CONTRATAÇÃO
function empresa($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$resposta->addScript("seleciona_combo('0', 'fornecedor'); ");
	
	//não é pacote
	if($dados_form["opcao_contratacao"]!=4)
	{
			
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_funcionario = '".$dados_form["funcionario"]."' ";
		$sql .= "AND funcionarios.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs = $db->array_select[0];
		
		$resposta->addScript("seleciona_combo('" . $regs["id_setor"] . "', 'disciplina'); ");
		
		$resposta->addScript("seleciona_combo('" . $regs["id_cod_fornec"] . "', 'fornecedor'); ");		
	
		$resposta->addScript("seleciona_combo('" . $regs["id_local"] . "', 'local_trabalho'); ");	
	}
	else
	{
		
		$sql = "SELECT * FROM SA2010 WITH(NOLOCK) ";
		$sql .= "WHERE SA2010.A2_MSBLQL <> '1' ";
		$sql .= "AND SA2010.D_E_L_E_T_ = '' ";
		$sql .= "AND SA2010.A2_CONTATO = '".$dados_form["funcionario"]."' ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs = $db->array_select[0];
		
		$resposta->addScript("seleciona_combo('" . $regs["A2_COD"] . "', 'fornecedor'); ");		

	}

	return $resposta;
}

//SELECIONA O FORNECEDOR CONFORME OPÇÃO DE CONTRATACAO
function val_contrato($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;

	$valor = 0;
			
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '".$dados_form["funcionario"]."' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY id_salario ASC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs1 = $db->array_select[0];	
	
	switch ($dados_form["tipo_contrato"])
	{
		case "3": //horista
			$valor = $regs1["salario_hora"];
		break;
		
		case "4": //mensalista
			$valor = $regs1["salario_mensalista"];
		break;		
	}	
	
	$resposta->addAssign("valor_contrato", "value", formatavalor($valor));		

	return $resposta;
}

function calcula_vencimento($data,$vigencia=12)
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("data_fim","value",calcula_data($data, "sum", "month", $vigencia));

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("colaborador");
$xajax->registerFunction("empresa");
$xajax->registerFunction("calcula_vencimento");
$xajax->registerFunction("val_contrato");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=3)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Nº&nbsp;Contrato,Colaborador,Empresa,Opção,I,A,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("*,*,*,*,30,30,30");
	mygrid.setColAlign("left,left,left,left,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

function imprimir_contrato(id_contrato)
{
	window.open('relatorios/pj_imprimir_contrato.php?id_contrato='+id_contrato+'', '_blank');
}

</script>

<?php
$array_contratacao_values[] = '';
$array_contratacao_output[] = 'SELECIONE';

$array_empresa_values[] = '0';
$array_empresa_output[] = 'SELECIONE';

$array_setor_values[] = '';
$array_setor_output[] = 'SELECIONE';

$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".pj_tipo_contratacao ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY tipo_contratacao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$array_contratacao_values[] = $regs["id_tipo_contratacao"];
	$array_contratacao_output[] = $regs["tipo_contratacao"];
}

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$array_local_values[] = $regs["id_local"];
	$array_local_output[] = $regs["descricao"];

}

$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY clausula ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$array_clausula_values[$regs["id_tipo_clausula"]][] = $regs["id_clausula"];
	$array_clausula_output[$regs["id_tipo_clausula"]][] = $regs["clausula"];
}

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$array_setor_values[] = $regs["id_setor"];
	$array_setor_output[] = $regs["setor"];
}

$sql = "SELECT * FROM SA2010 WITH(NOLOCK) ";
$sql .= "WHERE SA2010.A2_FABRICA = 2 "; //FORNECEDOR
$sql .= "AND SA2010.D_E_L_E_T_ = '' ";
$sql .= "ORDER BY SA2010.A2_NOME ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach($db->array_select as $regs)
{
	$array_empresa_values[] = trim($regs["A2_COD"]);
	$array_empresa_output[] = trim($regs["A2_NOME"]);
}


$sql = "SELECT * FROM ".DATABASE.".pj_tipo_clausulas ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY tipo_clausula ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$smarty->assign("option_tipo_clausula_".$regs["id_tipo_clausula"]."_values",$array_clausula_values[$regs["id_tipo_clausula"]]);
	$smarty->assign("option_tipo_clausula_".$regs["id_tipo_clausula"]."_output",$array_clausula_output[$regs["id_tipo_clausula"]]);
}

$smarty->assign("option_contratacao_values",$array_contratacao_values);
$smarty->assign("option_contratacao_output",$array_contratacao_output);

$smarty->assign("option_empresa_values",$array_empresa_values);
$smarty->assign("option_empresa_output",$array_empresa_output);

$smarty->assign("option_setor_values",$array_setor_values);
$smarty->assign("option_setor_output",$array_setor_output);

$smarty->assign("option_local_values",$array_local_values);
$smarty->assign("option_local_output",$array_local_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("data_inicio",date("d/m/Y"));

$smarty->assign("campo",$conf->campos('pj_contratos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('pj_contratos.tpl');
?>