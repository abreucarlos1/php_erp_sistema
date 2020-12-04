<?php
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '2014M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".apontamento_horas ";
//$sql .= "WHERE id_funcionario IN ('6') ";
$sql .= "WHERE data BETWEEN '2016-01-01' AND '2017-10-26' ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);		
}

$array_apont = $db->array_select;

foreach($array_apont as $regs)
{
	$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_manutencoes ";
	$sql .= "WHERE 	id_ti_analista = '".$regs["id_funcionario"]."' ";
	$sql .= "AND ti_data_manutencao = '".$regs["data"]."' ";
	
	$db->select($sql,'MYSQL');
	
	if($db->numero_registros==0)
	{
		
		$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_analistas, ".DATABASE.".ti_rotinas ";
		$sql .= "WHERE ti_rotinas_analistas.ti_delete = 0 ";
		$sql .= "AND ti_rotinas.ti_delete = 0 ";
		$sql .= "AND ti_rotinas_analistas.id_ti_analista = '".$regs["id_funcionario"]."' ";
		$sql .= "AND ti_rotinas.id_ti_rotina = ti_rotinas_analistas.id_ti_rotina ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_analistas = $db->array_select;
		
		foreach($array_analistas as $regs1)
		{		
			$isql = "INSERT INTO ".DATABASE.".ti_rotinas_manutencoes ";
			$isql .= "(id_ti_rotina, ti_data_manutencao, ti_data_inclusao, ti_data_previsao, ti_manutencao_observacao, id_ti_analista) ";
			$isql .= "VALUES ('" . $regs1["id_ti_rotina"] . "', ";
			$isql .= "'" . $regs["data"] . "', ";
			$isql .= "'" . php_mysql(calcula_data(mysql_php($regs["data"]), "sum", "day", 1)) . "', ";
			$isql .= "'" . php_mysql(calcula_data(mysql_php($regs["data"]), "sum", "day", 1)) . "', ";
			$isql .= "'ROTINA REALIZADA', ";
			$isql .= "'" . $regs["id_funcionario"] . "') ";
	
			//FAZ O INSERT
			$db->insert($isql,'MYSQL');
		}
	}
}

?>
