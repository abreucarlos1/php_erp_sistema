<?php
/*
		Relat�rio Apontamentos por periodo
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_funcionarios_os_excel.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

$db = new banco_dados;

$data = "\n"."Per�odo: ".$_POST["data_ini"]." � ".$_POST["datafim"]."\n\n";

//Seta no formato do per�odo do fechamento_folha	
$periodo_ini = explode("/",$_POST["data_ini"]);
$periodo_fim = explode("/",$_POST["datafim"]);

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADODVM') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

$array_func = $db->array_select;

$celulas = 5;

$i = 0;

foreach($array_func as $reg_empresas)
{

	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".str_replace("-","",php_mysql($_POST["datafim"]))."' ";
	$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);
	
	$reg_salarios = $db->array_select[0];		
	
	$tipocontrato_array = explode("+",$reg_salarios[" tipo_contrato"]);
	
	if(in_array($_POST["tipocontrato"],$tipocontrato_array))
	{		
		$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)+TIME_TO_SEC(hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND OS.reg_del = 0 ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND OS.id_os = apontamento_horas.id_os ";
		$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["data_ini"])."' AND '".php_mysql($_POST["datafim"])."' ";
		$sql .= "GROUP BY apontamento_horas.id_funcionario ";
		
		$db->select($sql,'MYSQL',true);
		
		$cont_soma = $db->array_select[0];
		
		$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)+TIME_TO_SEC(hora_adicional_noturna)) AS HT FROM ".DATABASE.".funcionarios, ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".OS, ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND unidades.reg_del = 0 ";
		$sql .= "AND OS.reg_del = 0 ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND OS.id_os = apontamento_horas.id_os ";
		$sql .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
		$sql .= "AND apontamento_horas.id_funcionario = '" . $reg_empresas["id_funcionario"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '".php_mysql($_POST["data_ini"])."' AND '".php_mysql($_POST["datafim"])."' ";
		$sql .= "GROUP BY apontamento_horas.id_funcionario, OS.id_os ";
		$sql .= "ORDER BY os.os ";
		
		$db->select($sql,'MYSQL',true);
		
		$totsal = NULL;
				
		foreach($db->array_select as $cont_os)
		{
			//aqui salarios
			$cliente[$cont_os["os"]] = $cont_os["id_empresa_erp"];
			
			if($_POST["tipocontrato"]=='SOCIO')
			{
				$totsal[$cont_os["os"]] += ($reg_salarios["salario_clt"]/(($cont_soma["HT"])/3600))*($cont_os["HT"]/3600);
				$valsal[$cont_os["os"]] = $reg_salarios["salario_clt"];
			}
			else
			{
				$totsal[$cont_os["os"]] += (100/(($cont_soma["HT"])/3600))*($cont_os["HT"]/3600);
			}
		
		}
		
		if($db->numero_registros>0)
		{
			
			foreach($totsal as $os => $valor)
			{
				
				if($os<100)
				{
					if($reg_empresas["id_funcionario"]!=$os_old)
					{
						$data .= $reg_empresas["funcionario"] . ";". $valsal[$os]. ";". $site[$os] . ";". $cliente[$os].";0". $os . ";" . "-" . ";" . number_format($valor,2,",",".") . "\n";
					}
					else
					{

						$data .= " ". ";" ." ". ";" .$site[$os] . ";". $cliente[$os].";0". $os . ";" . "-" . ";" . number_format($valor,2,",",".") . "\n";
					}
					$os_old = $reg_empresas["id_funcionario"];
				}
				else
				{

					$os_re = sprintf("%05d",$os);
					
					if($reg_empresas["id_funcionario"]!=$os_old)
					{
						$data .= $reg_empresas["funcionario"] . ";". $valsal[$os] . ";" .$site[$os] . ";". $cliente[$os].";". "99" . ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
					}
					else
					{
						$data .= " ". ";" . " " . ";" .$site[$os] . ";". $cliente[$os].";". "99". ";" . $os_re . ";" . number_format($valor,2,",",".") . "\n";
					}
					$os_old = $reg_empresas["id_funcionario"];
				}
				$celulas++;
				$i++;
			}
		}
			
	}


}

//echo $corpo;
if($_POST["tipocontrato"]=='SOCIO')
{
	$cabecalho = "Funcion�rio (".$_POST["tipocontrato"].");Sal�rio;Site;Cliente;C.C.;OS;R$";
}
else
{
	$cabecalho = "Funcion�rio (".$_POST["tipocontrato"].");;Site;Cliente;C.C.;OS;%";
}

$filename = "funcionarios_".$_POST["tipocontrato"]."_" . date("dMY");

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$cabecalho\n$data";
exit();

?>