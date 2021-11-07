<?php
/*
		Relatório empresa funcionarios	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../financeiro/rel_empresafunc.php
		
		Versão 0 --> VERSÃO INICIAL - 20/03/2007
		Versão 1 --> Atualização Lay-Out - 09/11/2007
		Versão 2 --> Atualização layout - Carlos Abreu - 28/03/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
	session_cache_limiter("private");
	
	require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

	$db = new banco_dados;
	
	$sql = "SELECT funcionarios.funcionario, empresa_funcionarios.empresa_func, empresa_funcionarios.empresa_cnpj FROM ".DATABASE.".empresa_funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (empresa_funcionarios.empresa_socio = funcionarios.id_funcionario AND funcionarios.reg_del = 0) "; 
	$sql .= "WHERE empresa_funcionarios.empresa_situacao = 1 ";
	$sql .= "AND empresa_funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionarios.funcionario, empresa_funcionarios.empresa_func ";
	
	$db->select($sql,'MYSQL',true);
	
	$filtro = '';
	
	//CABEÇALHO
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"3\"><b>EMPRESAS DE FUNCIONÁRIOS<b></td>";
	$conteudo .= "</tr>";

	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"3\">Emissão: " . date("d/m/Y") . "</td>";
	$conteudo .= "</tr>";

	//CABEÇALHO
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"left\"><b>Responsável</b></td>";
	$conteudo .= "<td align=\"left\"><b>empresa</b></td>";
	$conteudo .= "<td align=\"left\"><b>CNPJ</b></td>";
	$conteudo .= "</tr>";



	foreach($db->array_select as $reg_empfunc)
	{
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\">" . $reg_empfunc["funcionario"] . "</td>";
		$conteudo .= "<td align=\"left\">" . $reg_empfunc["empresa_func"] . "</td>";
		$conteudo .= "<td align=\"left\">" . $reg_empfunc["empresa_cnpj"] . "</td>";
		$conteudo .= "</tr>";

	}	
	
	echo $conteudo;
?>