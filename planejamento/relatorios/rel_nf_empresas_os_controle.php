<?php
/*
		Relatório NF x empresas
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_nf_empresas_os_controle.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

$db = new banco_dados;

$data = "";

$data = "\n".";".";Período: ".$_POST["dataini"]." á ".$_POST["datafim"]."\n\n";

$periodo_ini = explode("/",$_POST["dataini"]);
$periodo_fim = explode("/",$_POST["datafim"]);

$periodo_fechamento = $periodo_ini[2]."-" .$periodo_ini[1]. "," . $periodo_fim[2]."-" .$periodo_fim[1];

$sql = "SELECT *,SUM(NfsFunc_valor) AS NfsFunc_valor FROM ".DATABASE.".empresa_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".fechamento_folha, ".DATABASE.".nf_funcionarios ";
$sql .= "WHERE empresa_funcionarios.id_empfunc = funcionarios.id_empfunc ";
$sql .= "AND funcionarios.id_funcionario = fechamento_folha.id_funcionario ";	
$sql .= "AND fechamento_folha.id_fechamento = nf_funcionarios.id_fechamento "; 
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND empresa_funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND nf_funcionarios.reg_del = 0 ";
$sql .= "AND fechamento_folha.periodo = '" . $periodo_fechamento . "' ";
$sql .= "AND nf_funcionarios.nf_ajuda_custo <> 1 "; 
$sql .= "GROUP BY funcionarios.id_funcionario, nf_funcionarios.nf_numero ";
$sql .= "ORDER BY empresa_funcionarios.empresa_func ";

$db->select($sql,'MYSQL',true);

$array_nfs = $db->array_select;

$celulas = 6;

$i = 0;	

foreach($array_nfs as $reg_empresas)
{	
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$cont_soma = $db->array_select[0];		

	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".ordem_servico, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND unidades.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
	$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario, ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);
	
	$totsal = NULL;
	$cliente = NULL;
	$site = NULL;
			
	foreach($db->array_select as $cont_os)
	{
		$cliente[$cont_os["os"]] = $cont_os["id_empresa_erp"];
		$totsal[$cont_os["os"]] += ($reg_empresas["NfsFunc_valor"]/(($cont_soma["HT"])/3600))*($cont_os["HT"]/3600);
	}
	
	if($db->numero_registros>0)
	{
		foreach($totsal as $os => $valor)
		{				
			$celj[$i] = "I".$celulas;
			

			$os_re = sprintf("%05d",$os);
			
			if($reg_empresas["id_funcionario"]!=$os_old || $reg_empresas["nf_numero"]!=$nfsnum_old)
			{
				$data .= $reg_empresas["nf_numero"] . ";" . $reg_empresas["empresa_func"] . ";". $reg_empresas["funcionario"].";". number_format($reg_empresas["NfsFunc_valor"],2,',','.') . ";". $site[$os] . ";". $cliente[$os].";". "99" . ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
				$celd[$i] = "D".$celulas;
				$cele[$i] = "E".$celulas;
			}
			else
			{
				$data .= " " . ";" . " " . ";" . " " . ";" . " " . ";" . $site[$os] . ";". $cliente[$os].";". "99" . ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
			}
			
			$os_old = $reg_empresas["id_funcionario"];
			
			$nfsnum_old = $reg_empresas["nf_numero"];
			
			
			$celulas++;
			
			$i++;
		}
		
	}

}

$data .= "\n\n".";"." ".";TOTAL;=".implode("+",$celd).";"." ".";"." ".";"." ".";"." ".";=".implode("+",$celj).";\n\n"; 

$data .= " ".";"."PRESTAÇÃO DE SERVIÇOS(2)\nNF;Empresa Func.;Funcionário;Valor NF;Site;Cliente;C.C.;OS;Valor (R$)\n"; 	

$sql = "SELECT *,SUM(NfsFunc_valor) AS NfsFunc_valor FROM ".DATABASE.".empresa_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".fechamento_folha, ".DATABASE.".nf_funcionarios ";
$sql .= "WHERE empresa_funcionarios.id_empfunc = funcionarios.id_empfunc ";
$sql .= "AND funcionarios.id_funcionario = fechamento_folha.id_funcionario ";	
$sql .= "AND fechamento_folha.id_fechamento = nf_funcionarios.id_fechamento "; 
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND empresa_funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND nf_funcionarios.reg_del = 0 ";
$sql .= "AND fechamento_folha.periodo = '" . $periodo_fechamento . "' ";
$sql .= "AND nf_funcionarios.nf_ajuda_custo = 1 "; 
$sql .= "GROUP BY funcionarios.id_funcionario, nf_funcionarios.nf_numero ";
$sql .= "ORDER BY empresa_funcionarios.empresa_func ";

$db->select($sql,'MYSQL',true);

$array_nfs = $db->array_select;

$celulas += 6;

$i = 0;

$celd = NULL;

$cele = NULL;

$celj = NULL;

$site = NULL;

$cliente = NULL;
	
foreach($array_nfs as $reg_empresas)
{	
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$cont_soma = $db->array_select[0];		

	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".ordem_servico, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND unidades.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
	$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario, ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);
	
	$totsal = NULL;
			
	foreach($db->array_select as $cont_os)
	{
		$cliente[$cont_os["os"]] = $cont_os["id_empresa_erp"];
		$totsal[$cont_os["os"]] += ($reg_empresas["NfsFunc_valor"]/(($cont_soma["HT"])/3600))*($cont_os["HT"]/3600);
	}
	
	if($db->numero_registros>0)
	{			
		foreach($totsal as $os => $valor)
		{				
			$celj[$i] = "I".$celulas;
			
			$os_re = sprintf("%05d",$os);
			
			if($reg_empresas["id_funcionario"]!=$os_old)
			{
				$data .= $reg_empresas["nf_numero"] . ";" . $reg_empresas["empresa_func"] . ";". $reg_empresas["funcionario"].";". number_format($reg_empresas["NfsFunc_valor"],2,',','.') . ";". $site[$os] . ";". $cliente[$os].";". "99" . ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
				$celd[$i] = "D".$celulas;
				$cele[$i] = "E".$celulas;
			}
			else
			{
				$data .= " " . ";" . " " . ";" . " " . ";" . " " . ";" . $site[$os] . ";". $cliente[$os].";". "99" . ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
			}
			
			$os_old = $reg_empresas["id_funcionario"];			
			
			$celulas++;
			
			$i++;
		}
	}

}

$data .= "\n\n".";"." ".";TOTAL;=".implode("+",$celd).";"." ".";"." ".";"." ".";"." ".";=".implode("+",$celj).";"; 

$cabecalho = "".";"."PRESTAÇÃO DE SERVIÇOS(1)\nNF;Empresa Func.;Funcionário;Valor NF;Site;Cliente;C.C.;OS;Valor (R$)"; 

$filename = "nf_empresas" . date("dMY");

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$cabecalho\n$data";
exit();

?>