<?php
/*
		Relatorio Empresa Funcionarios
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/relatorios/rel_empresafunc.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2006
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/

	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

	require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM CC3010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND CC3_MSBLQL = 'N' ";
	
	$db->select($sql,'MSSQL', true);
	
	foreach($db->array_select as $regs)
	{
		$array_cnae[trim($regs["CC3_COD"])] = trim($regs["CC3_DESC"]);
	}
		
	$filtro = '';
	
	//CABEÇALHO
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"7\"><b>EMPRESAS DE FUNCIONÁRIOS<b></td>";
	$conteudo .= "</tr>";

	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"7\">Emissão: " . date("d/m/Y") . "</td>";
	$conteudo .= "</tr>";

	//CABEÇALHO
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"left\"><b>Responsável</b></td>";
	$conteudo .= "<td align=\"left\"><b>Função</b></td>";
	$conteudo .= "<td align=\"left\"><b>Empresa</b></td>";
	$conteudo .= "<td align=\"left\"><b>CNPJ</b></td>";
	$conteudo .= "<td colspan=\"2\" align=\"left\"><b>CNAE</b></td>";
	$conteudo .= "<td align=\"left\"><b>Incide imposto</b></td>";
	$conteudo .= "</tr>";
	
	$sql = "SELECT *, rh_funcoes.descricao AS descricao FROM ".DATABASE.".empresa_funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (empresa_funcionarios.empresa_socio = funcionarios.id_funcionario AND funcionarios.reg_del = 0) "; 
	$sql .= "LEFT JOIN ".DATABASE.".rh_funcoes ON (funcionarios.id_funcao = rh_funcoes.id_funcao AND rh_funcoes.reg_del = 0) ";
	$sql .= "WHERE empresa_funcionarios.empresa_situacao = 1 ";
	$sql .= "AND empresa_funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionarios.funcionario, empresa_funcionarios.empresa_func ";
	
	$db->select($sql,'MYSQL',true);

	foreach($db->array_select as $reg_empfunc)
	{		
		$imp = "";
		
		if($reg_empfunc["empresa_imposto"])
		{
			$imp = "SIM";	
		}
		else
		{
			$imp = "NÃO";		
		}
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\">" . $reg_empfunc["funcionario"] . "</td>";
		$conteudo .= "<td align=\"left\">" . $reg_empfunc["descricao"] . "</td>";
		$conteudo .= "<td align=\"left\">" . $reg_empfunc["empresa_func"] . "</td>";
		$conteudo .= "<td align=\"left\">" . $reg_empfunc["empresa_cnpj"] . "</td>";
		$conteudo .= "<td align=\"left\">'" . $reg_empfunc["empresa_cnae"] . "'</td>";
		$conteudo .= "<td align=\"left\">" . $array_cnae[$reg_empfunc["empresa_cnae"]] . "</td>";
		$conteudo .= "<td align=\"left\">" . $imp . "</td>";
		$conteudo .= "</tr>";

	}	
	
	echo $conteudo;
?>