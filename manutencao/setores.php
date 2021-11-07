<?php
/*
		Formulário de setores	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../manutencao/setores.php
		
		Versão 0 --> VERSÃO INICIAL : 20/03/2007
		Versão 1 --> Atualização Lay-out / Smarty : 25/06/2008
		Versão 2 --> Alteração classe banco - 06/07/2012 - Carlos Abreu
		Versão 3 --> atualização layout - Carlos Abreu - 30/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
if(!verifica_sub_modulo(44))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm_setores').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_setores')); ");
	
	$resposta->addScript("xajax_atualizatabela(''); ");	
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
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
		
		$sql_filtro = " AND setores.setor LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR setores.abreviacao LIKE '".$sql_texto."' ";

	}
	
	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE setores.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY setor ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_dep['id_setor']);
			$xml->writeElement('cell', $cont_desp["abreviacao"]);
			$xml->writeElement('cell', $cont_desp["setor"]);
			$xml->writeElement('cell', $cont_desp["sigla"]);
			
			$img = "<img src=\'".DIR_IMAGENS."apagar.png\' style=\'cursor:pointer;\' onclick=if(confirm(\'Confirma a exclusão do setor selecionado?\')){xajax_excluir(\'".$cont_desp["id_setor"]."\');}>";
			$xml->writeElement('cell', $img);
		$xml->endElement();
	}
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('setores',true,'400','".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["setor"]!='' || $dados_form["abreviacao"]!='')
	{
		$isql = "INSERT INTO ".DATABASE.".setores ";
		$isql .= "(setor, abreviacao,sigla) ";
		$isql .= "VALUES ('" . maiusculas($dados_form["setor"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["abreviacao"]) . "', ";
		$isql .= "'" . trim($dados_form["sigla"]) . "') ";

		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$id_insert = $db->insert_id;

		/*
		
		$sql = "SELECT R_E_C_N_O_ FROM AE5010 WITH(NOLOCK) ";
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$reg1 = $db->array_select[0];
	
		$recno5 = $reg1["R_E_C_N_O_"] + 1;
		
		//INSERE NO PROTHEUS - TABELA GRUPO COMPOSIÇÃO
		$isql = "INSERT INTO AE5010 ";
		$isql .= "(AE5_GRPCOM, AE5_DESCRI, R_E_C_N_O_, AE5_ID_DVM) ";
		$isql .= "VALUES ('" . maiusculas($dados_form["abreviacao"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["setor"]) . "', ";
		$isql .= "'" . $recno5 . "', ";
		$isql .= "'" . $id_insert . "') ";

		$db->insert($isql,'MSSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$sql = "SELECT R_E_C_N_O_ FROM AED010 WITH(NOLOCK) ";
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$reg2 = $db->array_select[0];
	
		$recnoE = $reg2["R_E_C_N_O_"] + 1;
		
		//INSERE NO PROTHEUS - TABELA EQUIPES PROJETO
		$isql = "INSERT INTO AED010 ";
		$isql .= "(AED_EQUIP, AED_DESCRI, R_E_C_N_O_, AED_ID_DVM) ";
		$isql .= "VALUES ('" . sprintf("%010d",$id_insert) . "', ";
		$isql .= "'" . maiusculas($dados_form["setor"]) . "', ";
		$isql .= "'" . $recnoE . "', ";
		$isql .= "'" . $id_insert . "') ";

		$db->insert($isql,'MSSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		*/
			
		$resposta->addScript("xajax_voltar();");		

		$resposta->addScript("xajax_atualizatabela('');");

		$resposta->addAlert("setor cadastrado com sucesso.");	

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".setores  ";
	$sql .= "WHERE setores.id_setor = '".$id."' ";
	$sql .= "AND setores.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs = $db->array_select[0];
	
	$resposta -> addAssign("setor", "value",$regs["setor"]);
	
	$resposta -> addAssign("abreviacao", "value",$regs["abreviacao"]);
	
	$resposta -> addAssign("sigla", "value",$regs["sigla"]);
	
	$resposta -> addAssign("id_setor", "value",$regs["id_setor"]);
	
	$resposta -> addAssign("btninserir", "value", "Atualizar");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_setores'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["setor"]!='' || $dados_form["abreviacao"]!='')
	{	

		$usql = "UPDATE ".DATABASE.".setores SET ";
		$usql .= "setor = '" . maiusculas($dados_form["setor"]) . "', ";
		$usql .= "sigla = '" . trim($dados_form["sigla"]) . "', ";
		$usql .= "abreviacao = '" . maiusculas($dados_form["abreviacao"]) . "' ";
		$usql .= "WHERE id_setor = '".$dados_form["id_setor"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		/*
		$usql = "UPDATE AE5010 SET ";
		$usql .= "AE5_DESCRI = '" . maiusculas($dados_form["setor"]) . "', ";
		$usql .= "AE5_GRPCOM = '" . maiusculas($dados_form["abreviacao"]) . "' ";
		$usql .= "WHERE AE5_ID_DVM = '".$dados_form["id_setor"]."' ";

		$db->update($usql,'MSSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$usql = "UPDATE AED010 SET ";
		$usql .= "AED_DESCRI = '" . maiusculas($dados_form["setor"]) . "' ";
		$usql .= "WHERE AED_ID_DVM = '".$dados_form["id_setor"]."' ";

		$db->update($usql,'MSSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		*/
		
		$resposta->addScript("xajax_voltar();");
		
		$resposta->addScript("xajax_atualizatabela('');");
	
		$resposta->addAlert("setor atualizado com sucesso.");	

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE id_setor = '".$id."' ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	if($db->numero_registros>0)
	{
		$resposta->addAlert("Setor atrelado ao Apontamento de horas, não pode ser excluida!");
	}
	else
	{

		$usql = "UPDATE ".DATABASE.".setores SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE setores.id_setor = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addAlert("Setor excluído com sucesso!");
		
		$resposta->addScript("xajax_atualizatabela(''); ");
	}

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

$conf = new configs();

$smarty->assign("nome_formulario","SETORES");

$smarty->assign("revisao_documento","V4");

$smarty->assign('campo', $conf->campos('setores'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('setores.tpl');

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>


function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	function doOnRowSelected(row,col)
	{
		if(col<=4)
		{						
			xajax_editar(row);

			return true;
		}
	}

	mygrid.setHeader("Abreviação, Setor, Sigla, D");
	mygrid.setInitWidths("100,*,100,50");
	mygrid.setColAlign("left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");

	mygrid.attachEvent('onRowSelect', doOnRowSelected);

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>