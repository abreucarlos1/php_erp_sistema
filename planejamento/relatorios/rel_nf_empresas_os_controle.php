<?php
/*
		Relat�rio NF x empresas
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_nf_empresas_os_controle.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

$db = new banco_dados;

$data = "";

$data = "\n".";".";Per�odo: ".$_POST["data_ini"]." � ".$_POST["datafim"]."\n\n";

$periodo_ini = explode("/",$_POST["data_ini"]);
$periodo_fim = explode("/",$_POST["datafim"]);

$periodo_fechamento = $periodo_ini[2]."-" .$periodo_ini[1]. "," . $periodo_fim[2]."-" .$periodo_fim[1];

//Seleciona as OS's da Klabin / do Rubens e armazena em um array
$sql = "SELECT * FROM ".DATABASE.".OS ";
$sql .= "WHERE OS.id_empresa_erp = 253 "; //253=KLABIN TEL�MACO
$sql .= "AND OS.id_cod_coord = 206 "; //206=RUBEN OSVALDO ROJAS
$sql .= "AND OS.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_klabin)
{
	$os_klabin[$reg_klabin["os"]] = 1;	
}

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
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND OS.id_os = apontamento_horas.id_os ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["data_ini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$cont_soma = $db->array_select[0];		

	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND unidades.reg_del = 0 ";
	$sql .= "AND OS.id_os = apontamento_horas.id_os ";
	$sql .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["data_ini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario, OS.id_os ";
	$sql .= "ORDER BY os.os ";
	
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
			
			if($os<100)
			{
				if($reg_empresas["id_funcionario"]!=$os_old || $reg_empresas["nf_numero"]!=$nfsnum_old)
				{
					$data .= $reg_empresas["nf_numero"] . ";" . $reg_empresas["empresa_func"] . ";" . $reg_empresas["funcionario"] .";". number_format($reg_empresas["NfsFunc_valor"],2,",",".") .";". $site[$os] . ";". $cliente[$os].";0". $os . ";" . "-" . ";" . number_format($valor,2,",",".") . "\n";
					$celd[$i] = "D".$celulas;
					$cele[$i] = "E".$celulas;
				}
				else
				{
					$data .= " ". ";" . " " . ";" . " " . ";" . " " . ";" .$site[$os] . ";". $cliente[$os].";0". $os . ";" . "-" . ";" . number_format($valor,2,",",".") . "\n";						

				}
				
				$os_old = $reg_empresas["id_funcionario"];
				
				$nfsnum_old = $reg_empresas["nf_numero"];					
			}
			else
			{

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
			}
			
			$celulas++;
			
			$i++;
		}
		
	}

}

$data .= "\n\n".";"." ".";TOTAL;=".implode("+",$celd).";"." ".";"." ".";"." ".";"." ".";=".implode("+",$celj).";\n\n"; 

$data .= " ".";"."PRESTA��O DE SERVI�OS(2)\nNF;empresa Func.;funcionario;valor NF;Site;Cliente;C.C.;OS;valor (R$)\n"; 	

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
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND OS.id_os = apontamento_horas.id_os ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["data_ini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$cont_soma = $db->array_select[0];		

	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND unidades.reg_del = 0 ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND OS.id_os = apontamento_horas.id_os ";
	$sql .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND funcionarios.id_empfunc = '" . $reg_empresas["id_empfunc"] . "' ";
	$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["data_ini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$sql .= "GROUP BY apontamento_horas.id_funcionario, OS.id_os ";
	$sql .= "ORDER BY os.os ";
	
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
			
			if($os<100)
			{
				if($reg_empresas["id_funcionario"]!=$os_old)
				{
					$data .= $reg_empresas["nf_numero"] . ";" . $reg_empresas["empresa_func"] . ";" . $reg_empresas["funcionario"] .";". number_format($reg_empresas["NfsFunc_valor"],2,",",".") . ";". $site[$os] . ";". $cliente[$os].";0". $os . ";" . "-" . ";" . number_format($valor,2,",",".") . "\n";
					$celd[$i] = "D".$celulas;
					$cele[$i] = "E".$celulas;
				}
				else
				{
					$data .= " ". ";" . " " . ";" . " " . ";" . " " . ";" .$site[$os] . ";". $cliente[$os].";0". $os . ";" . "-" . ";" . number_format($valor,2,",",".") . "\n";
				}
				
				$os_old = $reg_empresas["id_funcionario"];
			}
			else
			{

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
			}
			
			$celulas++;
			
			$i++;
		}
	}

}

$data .= "\n\n".";"." ".";TOTAL;=".implode("+",$celd).";"." ".";"." ".";"." ".";"." ".";=".implode("+",$celj).";"; 

$cabecalho = "".";"."PRESTA��O DE SERVI�OS(1)\nNF;empresa Func.;funcionario;valor NF;Site;Cliente;C.C.;OS;valor (R$)"; 

$filename = "nf_empresas" . date("dMY");

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$cabecalho\n$data";
exit();

?>