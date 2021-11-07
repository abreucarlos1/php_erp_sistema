<?php
/*
		Formulário de Funções 	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/cargos.php
		
		Versão 0 --> VERSÃO INICIAL - 28/01/2008
		Versão 1 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 04/04/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(222))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_cargos')");
	
	$resposta->addScriptCall("moveAllOptions(document.getElementById('obrigatorios'),document.getElementById('conhecimentos'))");

	$resposta->addScriptCall("moveAllOptions(document.getElementById('desejaveis'),document.getElementById('conhecimentos'))");

	$resposta->addScriptCall("moveAllOptions(document.getElementById('habilidades1'),document.getElementById('habil'))");

	$resposta->addScriptCall("moveAllOptions(document.getElementById('valores'),document.getElementById('habil'))");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_cargos'));");
	
	$resposta->addScript("desseleciona_combo('setores');");

	$resposta->addEvent("btnvoltar", "onclick", "javascript:history.back();");

	return $resposta;
}

function atualizatabela($filtro, $combo='')
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

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
		
		$sql_filtro = " AND (rh_funcoes.descricao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rh_funcoes.formacao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rh_funcoes.experiencia LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rh_escolaridade.escolaridade LIKE '".$sql_texto."') ";
	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_cargos_x_conhecimento ON(rh_funcoes.id_funcao = rh_cargos_x_conhecimento.id_rh_cargo AND rh_cargos_x_conhecimento.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_escolaridade ON(rh_funcoes.id_rh_escolaridade = rh_escolaridade.id_rh_escolaridade AND rh_escolaridade.reg_del = 0) ";
	$sql .= "WHERE rh_funcoes.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "GROUP BY rh_funcoes.id_funcao ";
	$sql .= "ORDER BY rh_funcoes.descricao ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($sql . $db->erro);
	}

	$conteudo = "";
	
	$array_funcoes = $db->array_select;
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($array_funcoes as $cont_desp)
	{
		$sql = "SELECT id_funcionario FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_funcao = '".$cont_desp["id_funcao"]."' ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($sql . $db->erro);
		}
		
		if($db->numero_registros==0)
		{
			$img = '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja excluir este registro?")){xajax_excluir("'.$cont_desp["id_funcao"].'");}></span>';
		}
		else
		{
			$img = ' ';
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id',$cont_desp["id_funcao"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["descricao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["cbo_2002"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["escolaridade"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["formacao"]);
			$xml->endElement();
	
			$xml->startElement('cell');
				$xml->text($cont_desp["experiencia"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($img);
			$xml->endElement();
			
			$img = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./relatorios/descricao_cargo_pdf.php?cod_cargo='.$cont_desp['id_funcao'].'","_blank");></span>'; 
			$xml->startElement('cell');
				$xml->text($img);
			$xml->endElement();
			
		$xml->endElement();
		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('cargos',true,'300','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["funcao"]!='' || $dados_form["escolaridade"]!='' || $dados_form["formacao"]!='' || $dados_form["experiencia"]!='' || $dados_form["cargo"]!='')
	{
		$sql = "SELECT id_funcao FROM ".DATABASE.".rh_funcoes ";
		$sql .= "WHERE rh_funcoes.descricao = '".maiusculas($dados_form["funcao"])."' ";
		$sql .= "AND rh_funcoes.reg_del = 0 ";
		$sql .= "AND rh_funcoes.id_rh_escolaridade = '".$dados_form["escolaridade"]."' ";
		$sql .= "AND rh_funcoes.id_cargo_grupo = '".$dados_form["cargo"]."' ";
		$sql .= "AND rh_funcoes.formacao = '".maiusculas($dados_form["formacao"])."' ";
		$sql .= "AND rh_funcoes.experiencia = '".maiusculas($dados_form["experiencia"])."' ";
		$sql .= "AND rh_funcoes.cbo_2002 = '".maiusculas($dados_form["cbo"])."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($sql . $db->erro);
		}

		if($db->numero_registros==0)
		{
			$isql = "INSERT INTO ".DATABASE.".rh_funcoes ";
			$isql .= "(descricao, id_rh_escolaridade, id_cargo_grupo, cbo_2002, formacao, experiencia, principais_atividades, missao, diretoria, competencias_tecnicas, competencias_individuais) ";
			$isql .= "VALUES ('" . maiusculas($dados_form["funcao"]) . "', ";
			$isql .= "'" . $dados_form["escolaridade"] . "', ";
			$isql .= "'" . $dados_form["cargo"] . "', ";
			$isql .= "'" . maiusculas($dados_form["cbo"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["formacao"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["experiencia"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["atividades"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["missao"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["diretoria"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["competencias_tecnicas"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["competencias_individuais"]) . "') ";

			$db->insert($isql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($isql . $db->erro);
			}
			
			$id_cargo = $db->insert_id;
			
			$desejaveis_str = "";
			
			$obrigatorios_str = "";
			
			$habilidades_str = "";
			
			$valores_str = "";
			
			if(count($dados_form["desejaveis"])>0)
			{
				$virgula = '';
				$desejaveis_str = '';
				foreach($dados_form["desejaveis"] as $x => $valor)
				{
					$desejaveis_str .= $virgula."('" . $valor . "','" . $id_cargo . "','0') ";
					$virgula = ',';
				}				
				
				$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
				$isql .= $desejaveis_str;
				
				$db->insert($isql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($isql . $db->erro);
				}				
			}
			
			
			if(count($dados_form["obrigatorios"])>0)
			{
				$virgula = '';
				$obrigatorios_str = '';
				foreach($dados_form["obrigatorios"] as $x => $valor)
				{
					$obrigatorios_str .= $virgula."('" . $valor . "','" . $id_cargo . "','1') ";
					$virgula = ',';
				}				
				
				$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
				$isql .= $obrigatorios_str;
				
				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($isql . $db->erro);
				}
			}
			
			if(count($dados_form["habilidades1"])>0)
			{
				$virgula = '';
				$habilidades_str = '';
				foreach($dados_form["habilidades1"] as $x => $valor)
				{
					$habilidades_str .= $virgula."('" . $valor . "','" . $id_cargo . "','1') ";
					$virgula = ',';
				}				
				
				$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
				$isql .= $habilidades_str;
				
				$db->insert($isql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($isql . $db->erro);
				}
			}
			
			if(count($dados_form["valores"])>0)
			{
				$virgula = '';
				$valores_str = '';
				foreach($dados_form["valores"] as $x => $valor)
				{
					$valores_str .= $virgula."('" . $valor . "','" . $id_cargo . "','0') ";
					$virgula = ',';				
				}				
				
				$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
				$isql .= $valores_str;
				
				$db->insert($isql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($isql . $db->erro);
				}				
			}
			
			if(count($dados_form["setores"])>0)
			{
				$virgula = '';
				$valores_str = '';
				foreach($dados_form["setores"] as $x => $valor)
				{
					$valores_str .= $virgula."('" . $valor . "','" . $id_cargo . "') ";
					$virgula = ',';				
				}				
				
				$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_setor (id_rh_setor, id_rh_cargo) VALUES";
				$isql .= $valores_str;
				
				$db->insert($isql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($isql . $db->erro);
				}				
			}
			
			//Insere na tabela função (RH)
			$texto = explode(" ",$dados_form["funcao"]);
			
			$exp = "";
			
			for($j=0;$j<count($texto);$j++)
			{
				if(strlen($dados_form["funcao"])>20)
				{
				
					$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,5);
				}
				else
				{
					$exp .= maiusculas(tiraacentos($texto[$j]));
				}
				
				$exp .= " ";
			}			
			
			//Função
			$sql = "SELECT R_E_C_N_O_ FROM SRJ010 WITH(NOLOCK) ";
			$sql .= "WHERE D_E_L_E_T_ = '' ";
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";
			
			$db->select($sql,'MSSQL', true);
			
			$reg3 = $db->array_select[0];
		
			$recno3 = $reg3["R_E_C_N_O_"] + 1;	
			
			$isql = "INSERT INTO SRJ010 ";
			$isql .= "(RJ_FUNCAO, RJ_DESC, RJ_CARGO, RJ_CODCBO, RJ_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
			$isql .= "VALUES (";
			$isql .= "'".sprintf("%05d",$id_cargo)."', ";
			$isql .= "'".trim($exp)."', ";
			$isql .= "'".sprintf("%05d",$dados_form["cargo"])."', ";
			$isql .= "'".sprintf("%06d",$dados_form["cbo"])."', ";
			$isql .= "'".$id_cargo."', ";
			$isql .= "'".$recno3."', ";
			$isql .= "'0') ";
			
			$db->insert($isql,'MSSQL');			
			
		}
		else
		{						
			$resposta->addAlert("Registro já existente no banco de dados.");
			
			return $resposta;
		}
		
		$resposta->addAlert("Cargo cadastrado com sucesso.");	
		$resposta->addScript("window.location='./cargos.php';");		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	$resposta->addScript('xajax_voltar();');
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$resposta->addScriptCall("moveAllOptions(document.getElementById('obrigatorios'),document.getElementById('conhecimentos'))");

	$resposta->addScriptCall("moveAllOptions(document.getElementById('desejaveis'),document.getElementById('conhecimentos'))");

	$resposta->addScriptCall("moveAllOptions(document.getElementById('habilidades1'),document.getElementById('habil'))");

	$resposta->addScriptCall("moveAllOptions(document.getElementById('valores'),document.getElementById('habil'))");
	
	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
	$sql .= "WHERE rh_funcoes.id_funcao = '".$id."' ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($sql . $db->erro);
	}

	$regs_cargo = $db->array_select[0];
		
	$sql = "SELECT * FROM ".DATABASE.".rh_conhecimentos, ".DATABASE.".rh_cargos_x_conhecimento ";
	$sql .= "WHERE rh_cargos_x_conhecimento.id_rh_cargo = '".$id."' ";
	$sql .= "AND rh_conhecimentos.reg_del = 0 ";
	$sql .= "AND rh_cargos_x_conhecimento.reg_del = 0 ";
	$sql .= "AND rh_cargos_x_conhecimento.id_rh_conhecimento = rh_conhecimentos.id_rh_conhecimento ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($sql . $db->erro);
	}

	foreach($db->array_select as $regs)
	{
		if($regs["rh_cargos_x_conhecimento_status"] == 1)
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
	$sql .= "AND rh_habilidades.reg_del = 0 ";
	$sql .= "AND rh_cargos_x_habilidade.reg_del = 0 ";
	$sql .= "AND rh_cargos_x_habilidade.id_rh_habilidade = rh_habilidades.id_rh_habilidade ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($sql . $db->erro);
	}

	foreach($db->array_select as $regs)
	{			
		if($regs["rh_cargos_x_habilidade_status"])
		{
			$resposta->addScript("selectMatchingOptions(document.getElementById('habil'),'".$regs["habilidade"]."');");	
			$resposta->addScript("moveSelectedOptions(document.getElementById('habil'),document.getElementById('habilidades1'));");
		}
		else
		{
			$resposta->addScript("selectMatchingOptions(document.getElementById('habil'),'".$regs["habilidade"]."');");	
			$resposta->addScript("moveSelectedOptions(document.getElementById('habil'),document.getElementById('valores'));");
		}	
	}
	
	$sql = "SELECT id_rh_setor FROM ".DATABASE.".rh_cargos_x_setor ";
	$sql .= "WHERE id_rh_cargo = ".$id." ";
	$sql .= "AND rh_cargos_x_setor.reg_del = 0 ";
	
	$resposta->addScript("desseleciona_combo('setores');");
	
	$db->select($sql, 'MYSQL', true);
	
	$reg = $db->array_select[0];

	$resposta->addScript("seleciona_combo(".$reg['id_rh_setor'].",'setores');");
	
	$resposta->addAssign("id_cargo", "value",$id);
	
	$resposta->addScript("seleciona_combo(".$regs_cargo["id_rh_escolaridade"].",'escolaridade');");
	
	$resposta->addScript("seleciona_combo(".$regs_cargo["id_cargo_grupo"].",'cargo');");
	
	$resposta->addAssign("funcao", "value",$regs_cargo["descricao"]);
	
	$resposta->addAssign("missao", "value",$regs_cargo["missao"]);
	
	$resposta->addAssign("diretoria", "value",$regs_cargo["diretoria"]);
	
	$resposta->addAssign("formacao", "value",$regs_cargo["formacao"]);
	
	$resposta->addAssign("cbo", "value",$regs_cargo["cbo_2002"]);
	
	$resposta->addAssign("experiencia", "value",$regs_cargo["experiencia"]);
	
	$resposta->addAssign("competencias_tecnicas", "value",$regs_cargo["competencias_tecnicas"]);
	
	$resposta->addAssign("competencias_individuais", "value",$regs_cargo["competencias_individuais"]);
	
	$resposta->addAssign("atividades", "value",$regs_cargo["principais_atividades"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");

	$resposta->addEvent("btninserir", "onclick", "selectAllOptions(document.getElementById('obrigatorios'));selectAllOptions(document.getElementById('desejaveis'));selectAllOptions(document.getElementById('habilidades1'));selectAllOptions(document.getElementById('valores'));xajax_atualizar(xajax.getFormValues('frm_cargos'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if($dados_form["funcao"]!='' || $dados_form["escolaridade"]!='' || $dados_form["formacao"]!='' || $dados_form["experiencia"]!='' || $dados_form["cargo"]!='')
	{
		$usql = "UPDATE ".DATABASE.".rh_funcoes SET ";
		$usql .= "descricao = '" . maiusculas(addslashes($dados_form["funcao"])) . "', ";
		$usql .= "id_rh_escolaridade = '" . $dados_form["escolaridade"] . "', ";
		$usql .= "experiencia = '" . maiusculas(addslashes($dados_form["experiencia"])) . "', ";
		$usql .= "formacao = '" . maiusculas(addslashes($dados_form["formacao"])) . "', ";
		$usql .= "cbo_2002 = '" . $dados_form["cbo"] . "', ";
		$usql .= "principais_atividades = '" . maiusculas(addslashes($dados_form["atividades"])) . "', ";
		$usql .= "missao = '".maiusculas(addslashes($dados_form['missao']))."', ";
		$usql .= "diretoria = '".maiusculas(addslashes($dados_form['diretoria']))."', ";
		$usql .= "competencias_tecnicas = '".maiusculas(addslashes($dados_form['competencias_tecnicas']))."', ";
		$usql .= "competencias_individuais = '".maiusculas(addslashes($dados_form['competencias_individuais']))."' ";		
		$usql .= "WHERE rh_funcoes.id_funcao = '".$dados_form["id_cargo"]."' ";
		$usql .= "AND rh_funcoes.reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($usql . $db->erro);
		}
		
		$usql = "UPDATE ".DATABASE.".rh_cargos_x_conhecimento SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE rh_cargos_x_conhecimento.id_rh_cargo = '".$dados_form["id_cargo"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($dsql . $db->erro);
		}
		
		$usql = "UPDATE ".DATABASE.".rh_cargos_x_habilidade SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE rh_cargos_x_habilidade.id_rh_cargo = '".$dados_form["id_cargo"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($dsql . $db->erro);
		}

		$usql = "UPDATE ".DATABASE.".rh_cargos_x_setor SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_rh_cargo = '".$dados_form["id_cargo"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($dsql . $db->erro);
		}
		
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
			
			$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
			$isql .= $desejaveis_str;
			
			$db->insert($isql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($isql . $db->erro);
			}			
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
			
			$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_conhecimento (id_rh_conhecimento, id_rh_cargo, rh_cargos_x_conhecimento_status ) VALUES";
			$isql .= $obrigatorios_str;
			
			$db->insert($isql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($isql . $db->erro);
			}			
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
			
			$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
			$isql .= $habilidades_str;
			
			$db->insert($isql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($isql . $db->erro);
			}
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
			
			$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_habilidade (id_rh_habilidade, id_rh_cargo, rh_cargos_x_habilidade_status ) VALUES";
			$isql .= $valores_str;
			
			$db->insert($isql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($isql . $db->erro);
			}
		}
		
		if(count($dados_form["setores"])>0)
		{
			$virgula = '';
			$valores_str = '';
			foreach($dados_form["setores"] as $x => $valor)
			{
				$valores_str .= $virgula."('" . $valor . "','" . $dados_form["id_cargo"] . "') ";
				$virgula = ',';				
			}				
			
			$isql = "INSERT INTO ".DATABASE.".rh_cargos_x_setor (id_rh_setor, id_rh_cargo) VALUES";
			$isql .= $valores_str;
			
			$db->insert($isql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($isql . $db->erro);
			}				
		}
		
		$texto = explode(" ",$dados_form["funcao"]);		
		
		switch ($texto[0]) 
		{
			case 'ENGENHEIRO':
				$cust_fix = '50.00';
			break;
			
			case 'SUPERVISOR':
				$cust_fix = '40.00';
			break;
			
			case 'COORDENADOR':
				$cust_fix = '30.00';
			break;
			
			case 'PROJETISTA':
				$cust_fix = '20.00';
			break;
			
			case 'DESENHISTA':
				$cust_fix = '10.00';
			break;
			
			default: $cust_fix = 0;	
		}
				
		$texto = explode(" ",$dados_form["funcao"]);
		
		$exp = "";
		
		for($j=0;$j<count($texto);$j++)
		{
			if(strlen($dados_form["funcao"])>20)
			{
			
				$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,5);
			}
			else
			{
				$exp .= maiusculas(tiraacentos($texto[$j]));
			}
			
			$exp .= " ";
		}
		
		//Altera a função no banco microsiga(RH)
		$usql = "UPDATE SRJ010 SET ";
		$usql .= "RJ_DESC = '".trim($exp)."', ";					//DESCRICAO
		$usql .= "RJ_CODCBO = '".trim($dados_form["cbo"])."', ";
		$usql .= "RJ_CARGO = '".sprintf("%05d",$dados_form["id_cargo"])."' ";	
		$usql .= "WHERE RJ_ID_DVM = '".$dados_form["id_cargo"]."' ";														//ID CARGO													
						
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($usql . $db->erro);
		}
			
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addScript("xajax_voltar();");
	
		$resposta->addAlert("cargo atualizado com sucesso.");			

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	
	}

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".rh_funcoes SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_funcoes.id_funcao = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($dsql . $db->erro);
	}
	
	$usql = "UPDATE ".DATABASE.".rh_cargos_x_conhecimento SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_cargos_x_conhecimento.id_rh_cargo = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($dsql . $db->erro);
	}
	
	$usql = "UPDATE ".DATABASE.".rh_cargos_x_habilidade SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_cargos_x_habilidade.id_rh_cargo = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($dsql . $db->erro);
	}
	
	//Deleta a função no banco microsiga
	$usql = "UPDATE SRJ010 SET ";
	$usql .= "D_E_L_E_T_ = '*' ";					
	$usql .= "WHERE RJ_ID_DVM = '".$id."' "; //ID CARGO													
					
	$db->update($usql,'MSSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($usql . $db->erro);
	}

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta->addAlert("Registro excluído corretamente!");	
	
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

$smarty->assign("body_onload","tab();xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>selectbox.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function tab()
{
	myTabbar = new dhtmlXTabBar("a_tabbar");
	
	myTabbar.addTab("a10_", "Função/cargo", null, null, true);
	myTabbar.addTab("a20_", "Conhecimentos e Habilidades");
	myTabbar.addTab("a40_", "Competências");
	
	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a20_").attachObject("a20");
	myTabbar.tabs("a40_").attachObject("a40");
	
	myTabbar.enableAutoReSize(true);
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=4)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Cargo,CBO2002,Escolaridade,Formação,Experiência,D,P",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("*,100,180,220,80,30,30");
	mygrid.setColAlign("left,left,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function move_itens(id_combo,from)
{	
	var idDestino 	= document.getElementById(id_combo).value;
	var to 			= document.getElementById(idDestino);
	
	moveSelectedOptions(document.getElementById(from),to);
}

</script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_cargo_values = NULL;
$array_cargo_output = NULL;

$array_escolaridade_values = NULL;
$array_escolaridade_output = NULL;

$array_conhecimentos_values = NULL;
$array_conhecimentos_output = NULL;

$array_habilidades_values = NULL;
$array_habilidades_output = NULL;


$array_escolaridade_values[] = "";
$array_escolaridade_output[] = "SELECIONE";

$array_cargo_values[] = "";
$array_cargo_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
$sql .= "WHERE obsoleto = 0 ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY grupo ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($sql . $db->erro);
}

foreach($db->array_select as $cont)
{
	$array_cargo_values[] = $cont["id_cargo_grupo"];
	$array_cargo_output[] = $cont["grupo"];
}

$sql = "SELECT * FROM ".DATABASE.".rh_escolaridade ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY rh_escolaridade.escolaridade ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($sql . $db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_escolaridade_values[] = $cont["id_rh_escolaridade"];
	$array_escolaridade_output[] = $cont["escolaridade"];
}

$sql = "SELECT * FROM ".DATABASE.".rh_conhecimentos  ";
$sql .= "WHERE reg_del = 0 ";	
$sql .= "ORDER BY rh_conhecimentos.conhecimento ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($sql . $db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_conhecimentos_values[] = $cont["id_rh_conhecimento"];
	$array_conhecimentos_output[] = $cont["conhecimento"];

}

$sql = "SELECT * FROM ".DATABASE.".rh_habilidades ";
$sql .= "WHERE reg_del = 0 ";	
$sql .= "ORDER BY rh_habilidades.habilidade ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($sql . $db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_habilidades_values[] = $cont["id_rh_habilidade"];
	$array_habilidades_output[] = $cont["habilidade"];
}									

$array_setor_values = NULL;
$array_setor_output = NULL;

$array_setor_values[] = "";
$array_setor_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regset)
{
	$array_setor_values[] = $regset["id_setor"];
	$array_setor_output[] = $regset["setor"];
}

$smarty->assign("option_setores_values",$array_setor_values);
$smarty->assign("option_setores_output",$array_setor_output);

$smarty->assign("option_cargo_values",$array_cargo_values);
$smarty->assign("option_cargo_output",$array_cargo_output);

$smarty->assign("option_escolaridade_values",$array_escolaridade_values);
$smarty->assign("option_escolaridade_output",$array_escolaridade_output);

$smarty->assign("option_conhecimentos_values",$array_conhecimentos_values);
$smarty->assign("option_conhecimentos_output",$array_conhecimentos_output);

$smarty->assign("option_habilidades_values",$array_habilidades_values);
$smarty->assign("option_habilidades_output",$array_habilidades_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('cargos_funcoes'));
$smarty->assign("nome_formulario","CARGOS/FUNÇÕES");

$smarty->assign("classe",CSS_FILE);

$smarty->display('cargos.tpl');

?>