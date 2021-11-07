<?php
/*

		Formulário de Ponto Eletronico 	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/ponto_eletronico.php
		
		Versão 0 --> VERSÃO INICIAL - 23/07/2009
		
*/

session_start();

function checaSessao()
{
	$resposta = new xajaxResponse();
	session_start();

	if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
	{

		$resposta->addAlert("A sessão expirou. É necessário efetuar o login novamente. ");
		// Usuário não logado! Redireciona para a página de login
		$resposta->addRedirect("../index.php?pagina=" . $_SERVER['PHP_SELF']);

	}

	return $resposta;
}	

require_once ("../includes/conectdb.inc.php");
//require_once ("../includes/logs.inc.php");
include_once ("../includes/tools.inc.php");
require("../includes/smarty/libs/Smarty.class.php");
include_once("../xajax/xajax.inc.php");


$smarty = new Smarty;
$smarty->compile_check = true;
$smarty->force_compile = true;

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta -> addScriptCall("reset_campos('frm_cargos')");
	
	$resposta -> addScriptCall("moveAllOptions(document.getElementById('obrigatorios'),document.getElementById('conhecimentos'))");

	$resposta -> addScriptCall("moveAllOptions(document.getElementById('desejaveis'),document.getElementById('conhecimentos'))");

	$resposta -> addScriptCall("moveAllOptions(document.getElementById('habilidades1'),document.getElementById('habil'))");

	$resposta -> addScriptCall("moveAllOptions(document.getElementById('valores'),document.getElementById('habil'))");
	
	$resposta -> addAssign("btninserir", "value", "Inserir");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_cargos'));");

	$resposta -> addEvent("btnvoltar", "onclick", "javascript:history.back();");

	return $resposta;
}	

function atualizatabela($filtro, $combo='')
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();	

	//Rotina para atualizar a tabela via AJAX
	$sql_filtro = "";
	
	$sql_texto = "";	
	
	if($filtro!="")
	{
		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " WHERE (Cargos.descricao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR Cargos.formacao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR Cargos.experiencia LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR Cargos.categoria LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rh_escolaridade.escolaridade LIKE '".$sql_texto."') ";
	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".Cargos ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_cargos_x_conhecimento ON(Cargos.id_funcao = rh_cargos_x_conhecimento.id_rh_cargo) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_escolaridade ON(Cargos.id_rh_escolaridade = rh_escolaridade.id_rh_escolaridade) ";
	$sql .= $sql_filtro;
	$sql .= "GROUP BY Cargos.id_funcao ";
	$sql .= "ORDER BY Cargos.descricao ";
	
	$reg = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

	$conteudo = "";
	
	$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<td type=\"ro\">Cargo</td>";
	$header .= "<td type=\"ro\">CBO2002</td>";
	$header .= "<td width=\"80\" type=\"ro\">Categoria</td>";
	$header .= "<td type=\"ro\">Escolaridade</td>";
	$header .= "<td type=\"ro\">Formação</td>";
	$header .= "<td type=\"ro\">Experiência</td>";
	$header .= "<td width=\"40\" type=\"img\">D</td>";
	$header .= "</tr>";
	
	$footer = "</table>";
	
	$chars = array("'","\"",")","(","\\","/");
	
	while($cont_desp = mysql_fetch_array($reg))
	{
		$sql = "SELECT id_funcionario FROM ".DATABASE.".Funcionarios ";
		$sql .= "WHERE Funcionarios.id_funcao = '".$cont_desp["id_funcao"]."' ";
		$reg_func = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

		$sql = "SELECT id_proposta FROM ".DATABASE.".Propostas ";
		$sql .= "WHERE Propostas.id_categoria = '".$cont_desp["id_funcao"]."' ";
		$reg_preco = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

		$sql = "SELECT id_mo_preco FROM ".DATABASE.".mo_precos ";
		$sql .= "WHERE mo_precos.id_categoria = '".$cont_desp["id_funcao"]."' ";
		$reg_mo = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

		
		$conteudo .= "<tr>";
		$conteudo .= "<td onclick=\"xajax_editar('". $cont_desp["id_funcao"]."')\">".$cont_desp["descricao"]."</td>";
		$conteudo .= "<td onclick=\"xajax_editar('". $cont_desp["id_funcao"]."')\">".$cont_desp["cbo_2002"]."</td>";
		$conteudo .= "<td onclick=\"xajax_editar('". $cont_desp["id_funcao"]."')\">".$cont_desp["categoria"]."</td>";
		$conteudo .= "<td onclick=\"xajax_editar('". $cont_desp["id_funcao"]."')\">".$cont_desp["escolaridade"]."</td>";
		$conteudo .= "<td onclick=\"xajax_editar('". $cont_desp["id_funcao"]."')\">".$cont_desp["formacao"]."</td>";
		$conteudo .= "<td onclick=\"xajax_editar('". $cont_desp["id_funcao"]."')\">".$cont_desp["experiencia"]."</td>";
		
		if((mysql_num_rows($reg_func)==0) && (mysql_num_rows($reg_preco)==0) && (mysql_num_rows($reg_mo)==0))
		{
			$conteudo .= "<td style=\"cursor:pointer;\" title=\"Apagar\" onclick=\"javascript:if(apagar('". trim(str_replace($chars,"",$cont_desp["descricao"]))."')){xajax_excluir('".$cont_desp["id_funcao"]."','". trim(str_replace($chars,"",$cont_desp["descricao"]))."');}\"><img src=\"../images/buttons_action/apagar.gif\"></td>";
		}
		else
		{
			$conteudo .= "<td> </td>";
		
		}
		$conteudo .= "</tr>";
	}
	
	$resposta->addAssign("cargos","innerHTML", $header.$conteudo.$footer);
	
	$resposta->addScript("grid('');");
	
	$db->fecha_db();
	
	return $resposta;

}

function insere($dados_form)
{
	session_start();
	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();
	
	if($dados_form["funcao"]!='' || $dados_form["escolaridade"]!='' || $dados_form["formacao"]!='' || $dados_form["experiencia"]!='')
	{
		$sql = "SELECT id_funcao FROM ".DATABASE.".Cargos ";
		$sql .= "WHERE Cargos.descricao = '".maiusculas($dados_form["funcao"])."' ";
		$sql .= "AND Cargos.id_rh_escolaridade = '".$dados_form["escolaridade"]."' ";
		$sql .= "AND Cargos.formacao = '".maiusculas($dados_form["formacao"])."' ";
		$sql .= "AND Cargos.experiencia = '".maiusculas($dados_form["experiencia"])."' ";
		$sql .= "AND Cargos.categoria = '".maiusculas($dados_form["categoria"])."' ";
		$sql .= "AND Cargos.cbo_2002 = '".maiusculas($dados_form["cbo"])."' ";
		
		$reg = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

		if(mysql_num_rows($reg)==0)
		{
		
			$isql = "INSERT INTO ".DATABASE.".Cargos ";
			$isql .= "(descricao, id_rh_escolaridade, cbo_2002, formacao, experiencia, categoria, principais_atividades) ";
			$isql .= "VALUES ('" . maiusculas($dados_form["funcao"]) . "', ";
			$isql .= "'" . $dados_form["escolaridade"] . "', ";
			$isql .= "'" . maiusculas($dados_form["cbo"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["formacao"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["experiencia"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["categoria"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["atividades"]) . "') ";
	
			//Carrega os registros
			$registros = mysql_query($isql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$isql);
			
			$id_cargo = mysql_insert_id($db->conexao);
			
			$desejaveis_str = "";
			
			$obrigatorios_str = "";
			
			$habilidades_str = "";
			
			$valores_str = "";
			
			if(count($dados_form["desejaveis"])>0)
			{
				for($x=0;$x<count($dados_form["desejaveis"]);$x++)
				{
					$desejaveis_str .= "('" . $dados_form["desejaveis"][$x] . "','" . $id_cargo . "','0') ";
					
					if($x<count($dados_form["desejaveis"])-1)
					{
						$desejaveis_str .= ", ";
					}
				
				}				
				
				$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
				$sql .= $desejaveis_str;
				
				mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir os conhecimentos!");
			}
			
			
			if(count($dados_form["obrigatorios"])>0)
			{
				for($x=0;$x<count($dados_form["obrigatorios"]);$x++)
				{
					$obrigatorios_str .= "('" . $dados_form["obrigatorios"][$x] . "','" . $id_cargo . "','1') ";
					
					if($x<count($dados_form["obrigatorios"])-1)
					{
						$obrigatorios_str .= ", ";
					}
				
				}				
				
				$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
				$sql .= $obrigatorios_str;
				
				mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir os conhecimentos!");
			}			

			
			if(count($dados_form["habilidades1"])>0)
			{
				for($x=0;$x<count($dados_form["habilidades1"]);$x++)
				{
					$habilidades_str .= "('" . $dados_form["habilidades1"][$x] . "','" . $id_cargo . "','1') ";
					
					if($x<count($dados_form["habilidades1"])-1)
					{
						$habilidades_str .= ", ";
					}
				
				}				
				
				$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
				$sql .= $habilidades_str;
				
				mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir as habilidades!");
			}
			
			
			if(count($dados_form["valores"])>0)
			{
				for($x=0;$x<count($dados_form["valores"]);$x++)
				{
					$valores_str .= "('" . $dados_form["valores"][$x] . "','" . $id_cargo . "','0') ";
					
					if($x<count($dados_form["valores"])-1)
					{
						$valores_str .= ", ";
					}
				
				}				
				
				$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
				$sql .= $valores_str;
				
				mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir os locais!");
			}
			
	
		}
		else
		{
			$regs = mysql_fetch_array($reg);
			$resposta->addAlert("Registro já existente no banco de dados.");
			
			return $resposta;
		}
		
		$resposta -> addScript("xajax_atualizatabela('');");
		
		$resposta -> addScript("xajax_voltar();");
	
		$resposta -> addAlert("cargo cadastrado com sucesso.");	
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	$resposta->addScript('xajax_voltar();');
	
	$db->fecha_db();
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();	
	
	$resposta -> addScriptCall("moveAllOptions(document.getElementById('obrigatorios'),document.getElementById('conhecimentos'))");

	$resposta -> addScriptCall("moveAllOptions(document.getElementById('desejaveis'),document.getElementById('conhecimentos'))");

	$resposta -> addScriptCall("moveAllOptions(document.getElementById('habilidades1'),document.getElementById('habil'))");

	$resposta -> addScriptCall("moveAllOptions(document.getElementById('valores'),document.getElementById('habil'))");
	
	$sql = "SELECT * FROM ".DATABASE.".Cargos ";
	$sql .= "WHERE Cargos.id_funcao = '".$id."' ";
	
	$registro = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível fazer a seleção." . $sql);

	$regs_cargo = mysql_fetch_array($registro);
		
	$sql = "SELECT * FROM ".DATABASE.".rh_conhecimentos, ".DATABASE.".rh_cargos_x_conhecimento ";
	$sql .= "WHERE rh_cargos_x_conhecimento.id_rh_cargo = '".$id."' ";
	$sql .= "AND rh_cargos_x_conhecimento.id_rh_conhecimento = rh_conhecimentos.id_rh_conhecimento ";
	
	$registro = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível fazer a seleção." . $sql);

	while($regs = mysql_fetch_array($registro))
	{
			
		if($regs["rh_cargos_x_conhecimento_status"])
		{
			$resposta->addScript("selectMatchingOptions(document.getElementById('conhecimentos'),'".$regs["conhecimento"]."');");	
			$resposta->addScript("moveSelectedOptions(document.getElementById('conhecimentos'),document.getElementById('obrigatorios'));");
		}
		else
		{
			$resposta->addScript("selectMatchingOptions(document.getElementById('conhecimentos'),'".$regs["conhecimento"]."');");	
			$resposta->addScript("moveSelectedOptions(document.getElementById('conhecimentos'),document.getElementById('desejaveis'));");
		}
	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_habilidades, ".DATABASE.".rh_cargos_x_habilidade ";
	$sql .= "WHERE rh_cargos_x_habilidade.id_rh_cargo = '".$id."' ";
	$sql .= "AND rh_cargos_x_habilidade.id_rh_habilidade = rh_habilidades.id_rh_habilidade ";
	
	$registro = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível fazer a seleção." . $sql);

	while($regs = mysql_fetch_array($registro))
	{
			
		if($regs["rh_cargos_x_habilidade_status"])
		{
			//selectMatchingOptions(obj,regex)
			//moveSelectedOptions(from,to)
			$resposta->addScript("selectMatchingOptions(document.getElementById('habil'),'".$regs["habilidade"]."');");	
			$resposta->addScript("moveSelectedOptions(document.getElementById('habil'),document.getElementById('habilidades1'));");
		}
		else
		{
			$resposta->addScript("selectMatchingOptions(document.getElementById('habil'),'".$regs["habilidade"]."');");	
			$resposta->addScript("moveSelectedOptions(document.getElementById('habil'),document.getElementById('valores'));");
		}
	
	}
	
	$resposta -> addAssign("id_cargo", "value",$id);
	
	$resposta -> addScript("seleciona_combo(".$regs_cargo["id_rh_escolaridade"].",'escolaridade');");
	
	$resposta -> addAssign("funcao", "value",$regs_cargo["descricao"]);
	
	$resposta -> addAssign("formacao", "value",$regs_cargo["formacao"]);
	
	$resposta -> addAssign("cbo", "value",$regs_cargo["cbo_2002"]);
	
	$resposta -> addAssign("experiencia", "value",$regs_cargo["experiencia"]);
	
	$resposta -> addAssign("atividades", "value",$regs_cargo["principais_atividades"]);
	
	$resposta -> addAssign("categoria", "value",$regs_cargo["categoria"]);
	
	$resposta -> addAssign("btninserir", "value", "Atualizar");

	$resposta -> addEvent("btninserir", "onclick", "selectAllOptions(document.getElementById('obrigatorios'));selectAllOptions(document.getElementById('desejaveis'));selectAllOptions(document.getElementById('habilidades1'));selectAllOptions(document.getElementById('valores'));xajax_atualizar(xajax.getFormValues('frm_cargos'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "xajax_voltar();");

	$db->fecha_db();
	
	return $resposta;	

}

function atualizar($dados_form)
{
	session_start();
		
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();
	
	if($dados_form["funcao"]!='' || $dados_form["escolaridade"]!='' || $dados_form["formacao"]!='' || $dados_form["experiencia"]!='')
	{
		$isql = "UPDATE ".DATABASE.".Cargos SET ";
		$isql .= "Cargos.descricao = '" . maiusculas($dados_form["funcao"]) . "', ";
		$isql .= "Cargos.id_rh_escolaridade = '" . $dados_form["escolaridade"] . "', ";
		$isql .= "Cargos.experiencia = '" . maiusculas($dados_form["experiencia"]) . "', ";
		$isql .= "Cargos.formacao = '" . maiusculas($dados_form["formacao"]) . "', ";
		$isql .= "Cargos.cbo_2002 = '" . maiusculas($dados_form["cbo"]) . "', ";
		$isql .= "Cargos.categoria = '" . maiusculas($dados_form["categoria"]) . "', ";
		$isql .= "Cargos.principais_atividades = '" . maiusculas($dados_form["atividades"]) . "' ";
		$isql .= "WHERE Cargos.id_funcao = '".$dados_form["id_cargo"]."' ";

		//Carrega os registros
		$registros = mysql_query($isql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$isql);
		
		$sql = "DELETE FROM ".DATABASE.".rh_cargos_x_conhecimento ";
		$sql .= "WHERE rh_cargos_x_conhecimento.id_rh_cargo = '".$dados_form["id_cargo"]."' ";
						
		mysql_query($sql,$db->conexao) or $resposta->addAlert('Erro ao excluir o registro.'.$sql);

		$sql = "DELETE FROM ".DATABASE.".rh_cargos_x_habilidade ";
		$sql .= "WHERE rh_cargos_x_habilidade.id_rh_cargo = '".$dados_form["id_cargo"]."' ";
						
		mysql_query($sql,$db->conexao) or $resposta->addAlert('Erro ao excluir o registro.'.$sql);
		
		$desejaveis_str = "";
		
		$obrigatorios_str = "";
		
		$habilidades_str = "";
		
		$valores_str = "";
		
		if(count($dados_form["desejaveis"])>0)
		{
	
			for($x=0;$x<count($dados_form["desejaveis"]);$x++)
			{
				$desejaveis_str .= "('" . $dados_form["desejaveis"][$x] . "','" . $dados_form["id_cargo"] . "','0') ";
				
				if($x<count($dados_form["desejaveis"])-1)
				{
					$desejaveis_str .= ", ";
				}
			
			}				
			
			$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
			$sql .= $desejaveis_str;
			
			mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir os locais!");
		}
		
		
		if(count($dados_form["obrigatorios"])>0)
		{
			for($x=0;$x<count($dados_form["obrigatorios"]);$x++)
			{
				$obrigatorios_str .= "('" . $dados_form["obrigatorios"][$x] . "','" . $dados_form["id_cargo"] . "','1') ";
				
				if($x<count($dados_form["obrigatorios"])-1)
				{
					$obrigatorios_str .= ", ";
				}
			
			}				
			
			$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
			$sql .= $obrigatorios_str;
			
			mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir os locais!");
		}		

		
		if(count($dados_form["habilidades1"])>0)
		{

			for($x=0;$x<count($dados_form["habilidades1"]);$x++)
			{
				$habilidades_str .= "('" . $dados_form["habilidades1"][$x] . "','" . $dados_form["id_cargo"] . "','1') ";
				
				if($x<count($dados_form["habilidades1"])-1)
				{
					$habilidades_str .= ", ";
				}
			
			}				
			
			$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
			$sql .= $habilidades_str;
			
			mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir os locais!");
		}
		
		
		if(count($dados_form["valores"])>0)
		{
			for($x=0;$x<count($dados_form["valores"]);$x++)
			{
				$valores_str .= "('" . $dados_form["valores"][$x] . "','" . $dados_form["id_cargo"] . "','0') ";
				
				if($x<count($dados_form["valores"])-1)
				{
					$valores_str .= ", ";
				}
			
			}				
			
			$sql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
			$sql .= $valores_str;
			
			mysql_query($sql,$db->conexao) or $resposta->addAlert("Erro ao tentar inserir os locais!");
		}
		
		$resposta -> addScript("xajax_atualizatabela('');");
		
		$resposta -> addScript("xajax_voltar();");
	
		$resposta -> addAlert("cargo atualizado com sucesso.");			

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	
	}
		
	$db->fecha_db();
	
	return $resposta;
}

function excluir($id, $what)
{
	session_start();
	
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();			
	
	$sql = "DELETE FROM ".DATABASE.".Cargos ";
	$sql .= "WHERE Cargos.id_funcao = '".$id."' ";
	
	mysql_query($sql,$db->conexao) or $resposta->addAlert('Erro ao excluir o registro.'.$sql);

		
	$sql = "DELETE FROM ".DATABASE.".rh_cargos_x_conhecimento ";
	$sql .= "WHERE rh_cargos_x_conhecimento.id_rh_cargo = '".$id."' ";
	
	mysql_query($sql,$db->conexao) or $resposta->addAlert('Erro ao excluir o registro.'.$sql);

	$sql = "DELETE FROM ".DATABASE.".rh_cargos_x_habilidade ";
	$sql .= "WHERE rh_cargos_x_habilidade.id_rh_cargo = '".$id."' ";
	
	mysql_query($sql,$db->conexao) or $resposta->addAlert('Erro ao excluir o registro.'.$sql);

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta -> addAlert($what . " excluido com sucesso.");	

	$db->fecha_db();
	
	return $resposta;
}

$xajax = new xajax();

$xajax->setCharEncoding("utf-8");

$xajax->decodeUTF8InputOn();

$xajax->registerPreFunction("checaSessao");
$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../xajax'));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<script type="text/javascript" src="../includes/selectbox.js"></script>

<!-- GRID -->
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>		
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>

<script>

xajax.loadingFunction = function() {xajax.$('aguarde').style.display = 'block';}
xajax.doneLoadingFunction = function() {xajax.$('aguarde').style.display='none';}


function grid()
{
	
	var mygrid = new dhtmlXGridFromTable('tbl1');
	mygrid.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
	mygrid.enableAutoHeight(true,300);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");
	
}

function move_itens(id_combo,from,to1,to2)
{	
	combo = document.getElementById(id_combo);
	
	if(combo.options.value==0)
	{
		moveSelectedOptions(from,to1);
	}
	else
	{
		moveSelectedOptions(from,to2);
	}
	
}

</script>

<?php

$array_escolaridade_values = NULL;
$array_escolaridade_output = NULL;

$array_conhecimentos_values = NULL;
$array_conhecimentos_output = NULL;

$array_habilidades_values = NULL;
$array_habilidades_output = NULL;


$array_escolaridade_values[] = "";
$array_escolaridade_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".rh_escolaridade  ";
$sql .= "ORDER BY rh_escolaridade.escolaridade ";

$reg = mysql_query($sql,$db->conexao) or die($sql);

while ($cont = mysql_fetch_array($reg))
{
	$array_escolaridade_values[] = $cont["id_rh_escolaridade"];
	$array_escolaridade_output[] = $cont["escolaridade"];
}


$sql = "SELECT * FROM ".DATABASE.".rh_conhecimentos  ";	
$sql .= "ORDER BY rh_conhecimentos.conhecimento ";

$reg = mysql_query($sql,$db->conexao) or die($sql);

while ($cont = mysql_fetch_array($reg))
{
	$array_conhecimentos_values[] = $cont["id_rh_conhecimento"];
	$array_conhecimentos_output[] = $cont["conhecimento"];

}


$sql = "SELECT * FROM ".DATABASE.".rh_habilidades ";	
$sql .= "ORDER BY rh_habilidades.habilidade ";

$reg = mysql_query($sql,$db->conexao) or die($sql);

while ($cont = mysql_fetch_array($reg))
{
	$array_habilidades_values[] = $cont["id_rh_habilidade"];
	$array_habilidades_output[] = $cont["habilidade"];

}									


$smarty->assign("option_escolaridade_values",$array_escolaridade_values);
$smarty->assign("option_escolaridade_output",$array_escolaridade_output);

$smarty->assign("option_conhecimentos_values",$array_conhecimentos_values);
$smarty->assign("option_conhecimentos_output",$array_conhecimentos_output);

$smarty->assign("option_habilidades_values",$array_habilidades_values);
$smarty->assign("option_habilidades_output",$array_habilidades_output);

$smarty->assign("nome_formulario","CARGOS");

$smarty->assign("classe",CSS_FILE);

$db->fecha_db();

$smarty->display('ponto_eletronico.tpl');
								
?>


