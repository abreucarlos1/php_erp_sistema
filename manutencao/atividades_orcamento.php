<?php
/*
		Formulário de ATIVIDADES / ORÇAMENTO	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../manutencao/atividades_orcamento.php
		
		Versão 0 --> VERSÃO INICIAL : 28/09/2006
		Versão 1 --> Atualização Lay-Out 24/06/2008	
		Versão 2 --> atualização classe de dados - 27/01/2015 - Carlos Abreu	
		Versão 3 --> atualização layout - Carlos Abreu - 30/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");	

function orcamento($cod_atividade, $id_setor = 0)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;

	if($id_setor!=0)
	{
		$sql = "SELECT * FROM ".DATABASE.".setores ";
		$sql .= "WHERE id_setor = '".$id_setor."' ";
		$sql .= "AND setores.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}		

		$reg1 = $db->array_select[0];		
	}	
	
	//Faz a seleÇÃo de cargos
	//Alterado em 03/05/2011
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
	$sql .= "LEFT JOIN ".DATABASE.".atividades_orcamento ON (rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo AND atividades_orcamento.id_atividade = '" . $cod_atividade . "' AND atividades_orcamento.reg_del = 0) ";
	$sql .= "WHERE rh_cargos.reg_del = 0 ";
	
	if($id_setor!=0)
	{
		$sql .= "AND rh_cargos.grupo LIKE '%".$reg1["setor"]."%' ";
	}
	
	$sql .= "ORDER BY rh_cargos.ordem, grupo ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $cont)
	{	
		if($cont["id_cargo"]) 
		{ 
			$checado = "checked"; 
		}
		else
		{
			$checado = "";
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id',$cont["id_cargo_grupo"]);
			
			$xml->startElement('cell');
				$xml->text('<input type="checkbox" id="chk_'.$cont["id_cargo_grupo"].'" name="chk_'.$cont["id_cargo_grupo"].'" value="checkbox" '.$checado.'  onclick=document.getElementById("txt_'.$cont["id_cargo_grupo"].'").focus();\>');
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["grupo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<input type="text" class="caixa" size="3" maxlength="3" id="txt_'.$cont["id_cargo_grupo"].'" name="txt_'.$cont["id_cargo_grupo"].'" value="'.$cont["porcentagem"].'" onkeypress=num_only();> %');
			$xml->endElement();
			
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('porcentagem',true,'420','".$conteudo."');");	

	return $resposta;
}

function atualizatabela($cod_atividade)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	//Alterado em 03/05/2011
	//Para adequação dos cargos - Flyspray #140
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
	$sql .= "WHERE atividades_orcamento.id_cargo = rh_cargos.id_cargo_grupo ";
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND atividades_orcamento.id_atividade = '" . $cod_atividade . "' ";
	$sql .= "ORDER BY rh_cargos.ordem, rh_cargos.grupo ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$conteudo = "";
	
	$porcent = 0;
		
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $cont)
	{		
		$porcent += $cont["porcentagem"];
		
		$xml->startElement('row');
		    $xml->writeAttribute('id',$cont["id_cargo_grupo"]);
			
			$xml->startElement('cell');
				$xml->text($cont["grupo"]);
			$xml->endElement();

			$xml->startElement('cell');
				$xml->text($cont["porcentagem"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma a exclusão do orçamento selecionado?")){xajax_excluir("' . $cont["atividades_orcamento"] . '");}\>');
			$xml->endElement();
			
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('orcamento',true,'300','".$conteudo."');");	

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	//Alterado em 03/05/2011
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
	$sql .= "WHERE rh_cargos.reg_del = 0 ";
	$sql .= "ORDER BY rh_cargos.ordem, grupo  ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$array_cargos = $db->array_select;
	
	//Alterado em 26/01/2010 por Carlos Abreu
	//Atendendo ao Protheus (Composição -> AE2)
	foreach($db->array_select as $reg_cargos)
	{	
		if($dados_form["chk_".$reg_cargos["id_cargo_grupo"]]) //se Categoria estiver setado
		{
			$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento ";
			$sql .= "WHERE atividades_orcamento.reg_del = 0 ";
			$sql .= "AND atividades_orcamento.id_atividade = '" . $dados_form["id_atividade"] . "' ";
			$sql .= "AND atividades_orcamento.id_cargo = '" . $reg_cargos["id_cargo_grupo"] . "' ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$regs1 = $db->array_select[0];
			
			if($db->numero_registros <= 0) //não existe no banco, insere
			{
				$isql = "INSERT INTO ".DATABASE.".atividades_orcamento (id_atividade, id_cargo, porcentagem) VALUES(";
				$isql .= "'" . $dados_form["id_atividade"] . "', ";
				$isql .= "'" . $reg_cargos["id_cargo_grupo"] . "', ";
				$isql .= "'" . $dados_form["txt_".$reg_cargos["id_cargo_grupo"]] . "') ";
	
				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}

				$id_orcamento = $db->insert_id;
				
				$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".atividades ";
				$sql .= "WHERE atividades_orcamento.id_atividade = atividades.id_atividade ";
				$sql .= "AND atividades_orcamento.reg_del = 0 ";
				$sql .= "AND atividades.reg_del = 0 ";
				$sql .= "AND atividades.id_atividade = '".$dados_form["id_atividade"]."' ";
				$sql .= "AND atividades_orcamento.id_cargo = '".$reg_cargos["id_cargo_grupo"]."' ";
				
				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}

				$regs = $db->array_select[0];

				/*
				$sql = "SELECT R_E_C_N_O_ FROM AE2010 WITH(NOLOCK) ";
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";
				
				$db->select($sql,'MSSQL', true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$reg1 = $db->array_select[0];
			
				$recno = $reg1["R_E_C_N_O_"] + 1;
				
				$sql = "SELECT AE2_ITEM FROM AE2010 WITH(NOLOCK) ";
				$sql .= "WHERE AE2_COMPOS = '".$regs["codigo"]."' ";
				$sql .= "ORDER BY AE2_ITEM DESC ";
				
				$db->select($sql,'MSSQL', true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$reg2 = $db->array_select[0];
				
				$item = $reg2["AE2_ITEM"] + 1;
				
				$isql = "INSERT INTO AE2010 ";
				$isql .= "(AE2_COMPOS, AE2_ITEM, AE2_QUANT, AE2_QTSEGU, AE2_CSTITM, AE2_RECURS, AE2_ID_DVM, AE2_FATOR, R_E_C_N_O_, R_E_C_D_E_L_) ";
				$isql .= "VALUES (";
				$isql .= "'".maiusculas(tiraacentos($regs["codigo"]))."', ";
				$isql .= "'".sprintf("%02d",$item)."', ";
				$isql .= "'".($regs["porcentagem"]/100)*$regs["horasestimadas"]."', ";
				$isql .= "'0',";
				$isql .= "'0',";
				$isql .= "'ORC_".sprintf("%011d",$reg_cargos["id_cargo_grupo"])."', ";
				$isql .= "'".$id_orcamento."', ";
				$isql .= "'0',";
				$isql .= "'".$recno."', ";
				$isql .= "'0') ";

				$db->insert($isql,'MSSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$usql = "UPDATE AE1010 SET "; 
				$usql .= "AE1_ULTATU = '".date('Ymd')."' ";
				$usql .= "WHERE AE1_COMPOS = '".maiusculas(tiraacentos($regs["codigo"]))."' ";
				
				$db->update($usql,'MSSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				*/
							
			}
			else
			{
				$usql = "UPDATE ".DATABASE.".atividades_orcamento SET ";
				$usql .= "porcentagem = '" . $dados_form["txt_".$reg_cargos["id_cargo_grupo"]] . "' ";
				$usql .= "WHERE atividades_orcamento = '".$regs1["atividades_orcamento"]."' ";
				$usql .= "AND reg_del = 0 ";
				
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".atividades ";
				$sql .= "WHERE atividades_orcamento.id_atividade = atividades.id_atividade ";
				$sql .= "AND atividades_orcamento.reg_del = 0 ";
				$sql .= "AND atividades.reg_del = 0 ";
				$sql .= "AND atividades.id_atividade = '".$dados_form["id_atividade"]."' ";
				$sql .= "AND atividades_orcamento.id_cargo = '".$reg_cargos["id_cargo_grupo"]."' ";
				
				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$regs = $db->array_select[0];
				
				/*
				$sql = "SELECT * FROM AE2010 WITH(NOLOCK) ";
				$sql .= "WHERE AE2010.D_E_L_E_T_ = '' ";
				$sql .= "AND AE2_ID_DVM = '".$regs["atividades_orcamento"]."' ";
				
				$db->select($sql,'MSSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				if($db->numero_registros_ms>0) //existe no banco, atualiza
				{
					$usql = "UPDATE AE2010 SET "; 
					$usql .= "AE2_QUANT = '".($regs["porcentagem"]/100)*$regs["horasestimadas"]."', ";
					$usql .= "AE2_RECURS = 'ORC_".sprintf("%011d",$reg_cargos["id_cargo_grupo"])."' ";
					$usql .= "WHERE AE2_ID_DVM = '".$regs["atividades_orcamento"]."' ";
					
					$db->update($usql,'MSSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					
					$usql = "UPDATE AE1010 SET "; 
					$usql .= "AE1_ULTATU = '".date('Ymd')."' ";
					$usql .= "WHERE AE1_COMPOS = '".maiusculas(tiraacentos($regs["codigo"]))."' ";
					
					$db->update($usql,'MSSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					
				}
				else
				{
					$sql = "SELECT R_E_C_N_O_ FROM AE2010 WITH(NOLOCK) ";
					$sql .= "ORDER BY R_E_C_N_O_ DESC ";
					
					$db->select($sql,'MSSQL', true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					
					$reg1 = $db->array_select[0];
				
					$recno = $reg1["R_E_C_N_O_"] + 1;
					
					$sql = "SELECT AE2_ITEM FROM AE2010 WITH(NOLOCK) ";
					$sql .= "WHERE AE2_COMPOS = '".$regs["codigo"]."' ";
					$sql .= "ORDER BY AE2_ITEM DESC ";
					
					$db->select($sql,'MSSQL', true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					
					$reg2 = $db->array_select[0];
					
					$item = $reg2["AE2_ITEM"] + 1;
					
					$isql = "INSERT INTO AE2010 ";
					$isql .= "(AE2_COMPOS, AE2_ITEM, AE2_QUANT, AE2_QTSEGU, AE2_CSTITM, AE2_RECURS, AE2_ID_DVM, AE2_FATOR, R_E_C_N_O_, R_E_C_D_E_L_) ";
					$isql .= "VALUES (";
					$isql .= "'".maiusculas(tiraacentos($regs["codigo"]))."', ";
					$isql .= "'".sprintf("%02d",$item)."', ";
					$isql .= "'".($regs["porcentagem"]/100)*$regs["horasestimadas"]."', ";
					$isql .= "'0',";
					$isql .= "'0',";
					$isql .= "'ORC_".sprintf("%011d",$reg_cargos["id_cargo_grupo"])."', ";
					$isql .= "'".$regs1["atividades_orcamento"]."', ";
					$isql .= "'0',";
					$isql .= "'".$recno."', ";
					$isql .= "'0') ";

					$db->insert($isql,'MSSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					
					$usql = "UPDATE AE1010 SET "; 
					$usql .= "AE1_ULTATU = '".date('Ymd')."' ";
					$usql .= "WHERE AE1_COMPOS = '".maiusculas(tiraacentos($regs["codigo"]))."' ";
					
					$db->update($usql,'MSSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
										
				}
				*/
			}
		}
		else
		{
			$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento ";
			$sql .= "WHERE atividades_orcamento.reg_del = 0 ";
			$sql .= "AND atividades_orcamento.id_atividade = '" . $dados_form["id_atividade"] . "' ";
			$sql .= "AND atividades_orcamento.id_cargo = '" . $reg_cargos["id_cargo_grupo"] . "' ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$regs1 = $db->array_select[0];
			
			$id_compos = $regs1["atividades_orcamento"];

			$usql = "UPDATE ".DATABASE.".atividades_orcamento SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE atividades_orcamento.id_atividade = '" . $dados_form["id_atividade"] . "' ";
			$usql .= "AND atividades_orcamento.id_cargo = '" . $reg_cargos["id_cargo_grupo"] . "' ";
			$usql .= "AND atividades_orcamento.reg_del = 0 ";

			$db->update($usql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			/*
			$usql = "UPDATE AE2010 SET "; 
			$usql .= "D_E_L_E_T_ = '*' ";
			$usql .= "WHERE AE2_ID_DVM = '".$id_compos."' ";

			$db->update($usql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$usql = "UPDATE AE1010 SET "; 
			$usql .= "AE1_ULTATU = '".date('Ymd')."' ";
			$usql .= "WHERE AE1_COMPOS = '".maiusculas(tiraacentos($regs["codigo"]))."' ";
			
			$db->update($usql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			*/
		}	
	}
	
	$resposta->addScript("xajax_orcamento(".$dados_form["id_atividade"].");");
	
	$resposta->addScript("xajax_atualizatabela(".$dados_form["id_atividade"].");");		

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento ";
	$sql .= "WHERE atividades_orcamento.reg_del = 0 ";
	$sql .= "AND atividades_orcamento.atividades_orcamento = '" . $id . "' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
			
	$reg = $db->array_select[0];
	
	$usql = "UPDATE ".DATABASE.".atividades_orcamento SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE atividades_orcamento = '" . $id . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$resposta->addAlert("Porcentagem excluída com sucesso!");
	
	$resposta->addScript("xajax_orcamento('".$reg["id_atividade"]."'); ");
	
	$resposta->addScript("xajax_atualizatabela('".$reg["id_atividade"]."'); ");

	/*
	$usql = "UPDATE AE2010 SET ";
	$usql .= "D_E_L_E_T_ = '*' ";					
	$usql .= "WHERE AE2_ID_DVM = '".$id."' ";//ID CARGO													
					
	$db->update($usql,'MSSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	*/

	return $resposta;
}

$xajax->registerFunction("orcamento");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('".$_GET["cod_atividade"]."');xajax_orcamento('".$_GET["cod_atividade"]."','".$_GET["setor"]."');");


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	switch (tabela)
	{
		case 'porcentagem':		
			mygrid.setHeader(" ,Cargo,%",
				null,
				["text-align:center","text-align:left","text-align:center"]);
			mygrid.setInitWidths("30,*,50");
			mygrid.setColAlign("center,left,center");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");
		break;
		
		case 'orcamento':		
			mygrid.setHeader("Cargo,%,D",
				null,
				["text-align:left","text-align:left","text-align:center"]);
			mygrid.setInitWidths("*,50,30");
			mygrid.setColAlign("left,center,center");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");
		break;
	}
	
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

$smarty->assign("id_atividade",$_GET["cod_atividade"]);

$smarty->assign("id_setor",$_GET["setor"]);

$sql = "SELECT * FROM ".DATABASE.".atividades ";
$sql .= "WHERE atividades.id_atividade = '" . $_GET["cod_atividade"] . "' ";
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND atividades.cod = '".$_GET["setor"]."' ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);	
}

$cont = $db->array_select[0];

$smarty->assign("atividade",$cont["codigo"]." - ".$cont["descricao"]);

$smarty->assign("revisao_documento","V4");

$smarty->assign('ocultarCabecalhoRodape','style="display:none;"');

$smarty->assign("nome_formulario","% MÃO-DE-OBRA");

$smarty->assign("classe",CSS_FILE);

$smarty->display('atividades_orcamento.tpl');

?>
