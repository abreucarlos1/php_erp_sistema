<?php
/*
		Relatório Sócios x empresas x OS 
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:		
		../planejamento/rel_socios_empresas_os_controle.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2014
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

$db = new banco_dados;

$data = "\n"."Período: ".$_POST["dataini"]." á ".$_POST["datafim"]."\n\n";

$periodo_ini = explode("/",$_POST["dataini"]);
$periodo_fim = explode("/",$_POST["datafim"]);

$sql = "SELECT * FROM ".DATABASE.".salarios, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_salario = salarios.id_salario ";
$sql .= "AND salarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios. tipo_contrato LIKE '%SOCIO%' ";	
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

$array_func = $db->array_select;

$celulas = 5;

$i = 0;

foreach($array_func as $reg_empresas)
{		
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
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
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario, ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);
	
	$num_regs = $db->numero_registros;
	
	$totsal = NULL;
			
	foreach($db->array_select as $cont_os)
	{
		$cliente[$cont_os["os"]] = $cont_os["id_empresa_erp"];
		$totsal[$cont_os["os"]] += ($reg_empresas["salario_clt"]/(($cont_soma["HT"])/3600))*($cont_os["HT"]/3600);
	}
	
	if($num_regs>0)
	{
		
		foreach($totsal as $os => $valor)
		{
			
			$celg[$i] = "G".$celulas;			

			$os_re = sprintf("%05d",$os);
			
			if($reg_empresas["id_funcionario"]!=$os_old)
			{
				$data .= $reg_empresas["funcionario"] . ";" . number_format($reg_empresas["salario_clt"],2,",",".") . ";". $site[$os] . ";". $cliente[$os].";". "99" . ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
				$celb[$i] = "B".$celulas;
			}
			else
			{
				$data .= " ". ";" . " " . ";" .$site[$os] . ";". $cliente[$os].";". "99". ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
			}
			$os_old = $reg_empresas["id_funcionario"];
			
			$celulas++;

			$i++;
		}
	}	
}

$data .= "\n \n"."TOTAL;=".implode("+",$celb).";"." ".";"." ".";"." ".";"." ".";=".implode("+",$celg).";"; 

//echo $corpo;
$cabecalho = "Funcionário (SÓCIOS);Salário;Site;Cliente;C.C.;OS;Valor (R$)"; 

$filename = "funcionarios_socios" . date("dMY");

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$cabecalho\n$data";
exit();
	
?>