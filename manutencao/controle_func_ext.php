<?php
/*
		Formulário de Alteração de Apontamento Horas - externo	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../manutencao/controle_func_ext.php
		
		Versão 0 --> VERSÃO INICIAL : 26/08/2005
		Versão 1 --> ATUALIZAÇÃO LAYOUT : 03/04/2006
		Versão 2 --> Atualização Lay-Out : 23/06/2008
		Versão 3 --> Inclusão de filtro por atuacao: 20/07/2012
		Versão 4 --> Atualização de layout: 09/12/2014
		Versão 5 --> atualização layout - Carlos Abreu - 30/03/2017
		Versão 6 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu			
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(45) && !verifica_sub_modulo(46) && !verifica_sub_modulo(255) && !verifica_sub_modulo(264))
{
	nao_permitido();
}

$conf = new configs();

function func_equipe($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("combo_destino = document.getElementById('id_funcionario');");
	$resposta->addScriptCall("limpa_combo('id_funcionario')");
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
	$sql .= "WHERE setores.id_setor = funcionarios.id_setor ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao IN ('ATIVO','FECHAMENTO FOLHA') ";
	$sql .= "GROUP BY setor ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		exit("Erro ao tentar selecionar os dados. ".$db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		if($dados_form["chk_".$reg["id_setor"]]==1)
		{
			$array_filtro[] = $reg["id_setor"];	
		}
	}
	
	$filtro = implode(",",$array_filtro);
	
	if(count($filtro)>0)
	{
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.situacao IN ('ATIVO','FECHAMENTO FOLHA') ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND funcionarios.id_setor IN (".$filtro.") ";
		
		if($dados_form["atuacao"]!="")
		{
			$sql .= "AND funcionarios.nivel_atuacao = '".$dados_form["atuacao"]."' ";
		}
		
		$sql .= "ORDER BY funcionario ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			exit("Erro ao tentar selecionar os dados. ".$db->erro);
		}
		
		foreach($db->array_select as $reg)
		{
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["funcionario"]."', '".$reg["id_funcionario"]."');");
		}	
	}

	return $resposta;
}

$xajax->registerFunction("func_equipe");
$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$check = "<br>";

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE setores.id_setor = funcionarios.id_setor ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.situacao IN ('ATIVO','FECHAMENTO FOLHA') ";
$sql .= "GROUP BY setor ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Erro ao tentar selecionar os dados. ".$db->erro);
}

$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_TODOS\" id=\"chk_TODOS\" value=\"-1\" onclick=\"if(this.checked){setcheckbox('frm','check');}else{setcheckbox('frm','');};xajax_func_equipe(xajax.getFormValues('frm'));\">TODOS</label><br>";

foreach($db->array_select as $reg)
{
	$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_".$reg["id_setor"]."\" id=\"chk_".$reg["id_setor"]."\" value=\"1\" onclick=\"xajax_func_equipe(xajax.getFormValues('frm'));\" />".$reg["setor"]."</label><br>";
}

$smarty->assign("check_equipe",$check);

$combo_atuacao = "<label class=\"labels\">Nível Atuação</label><BR>";
$combo_atuacao .= "<select name=\"atuacao\" class=\"caixa\" id=\"atuacao\" onchange=\"xajax_func_equipe(xajax.getFormValues('frm'));\"  >";
$combo_atuacao .= "<option value=\"\">TODOS</option>";
$combo_atuacao .=  "<option value=\"A\">ADMINISTRAÇÃO</option>";
$combo_atuacao .=  "<option value=\"D\">DIREÇÃO</option>";
$combo_atuacao .=  "<option value=\"C\">COORDENAÇÃO</option>";
$combo_atuacao .=  "<option value=\"S\">SUPERVISÃO</option>";
$combo_atuacao .=  "<option value=\"G\">GERÊNCIA</option>";
$combo_atuacao .=  "<option value=\"E\">EXECUTANTE</option>";
$combo_atuacao .=  "<option value=\"P\">PACOTE</option>";
$combo_atuacao .= "</select>";

$smarty->assign("combo_atuacao",$combo_atuacao);

$array_func_values = NULL;
$array_func_output = NULL;

$array_func_values[] = "0";
$array_func_output[] = "SELECIONE";

$smarty->assign("option_values",$array_func_values);
$smarty->assign("option_output",$array_func_output);

$smarty->assign("externo","1");

$smarty->assign("revisao_documento","V6");

$smarty->assign("campo",$conf->campos('manutencao_horas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("redirect","../apontamentos/apontamentos.php");

$smarty->assign("nome_formulario","APONTAMENTO DE HORAS - MANUTENÇÃO");

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_func_ext.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>