<?php
/*
		Relat�rio empresa funcionarios	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../financeiro/rel_empresafunc.php
		
		Vers�o 0 --> VERS�O INICIAL - 20/03/2007
		Vers�o 1 --> Atualiza��o Lay-Out - 09/11/2007
		Vers�o 2 --> atualiza��o layout - Carlos Abreu - 28/03/2017
		Vers�o 3 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
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
	
	//CABE�ALHO
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"3\"><b>EMPRESAS DE FUNCION�RIOS<b></td>";
	$conteudo .= "</tr>";

	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"3\">Emiss�o: " . date("d/m/Y") . "</td>";
	$conteudo .= "</tr>";

	//CABE�ALHO
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"left\"><b>Respons�vel</b></td>";
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