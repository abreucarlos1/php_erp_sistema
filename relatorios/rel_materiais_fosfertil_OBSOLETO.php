<?php

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

$db = new banco_dados;

$rev_doc = '0';

$sql = "SELECT OS FROM ".DATABASE.".OS ";
$sql .= "WHERE OS.id_os = '" . $_GET["id_os"] . "' ";

$db->select($sql,'MYSQL',true);

$reg_os = $db->array_select[0];

//Armazena a string de arquivos
$sel_mat = $_COOKIE["Materiais_Fosfertil"];

$array_sel_mat = explode(";",$sel_mat);

$sql = "SELECT * FROM fosfertil.SAP ";

if($_COOKIE["Materiais_Fosfertil"])
{
	$sql .= "WHERE id_SAP IN (" . implode(",",$array_sel_mat) . ") ";
}

$sql .= "ORDER BY num_estoque ASC ";
	
$db->select($sql,'MYSQL',true);

$y = 0;
$data = "";
$separador = ";";

foreach($db->array_select as $reg_mat)
{
	$y++;

	$data .= '"' . $y . '"' . $separador . '"' . trim($reg_mat["descricao_completa"]) . '"' . $separador . '""' . $separador . '"' . $reg_mat["num_estoque"] . '"' . $separador . '"' . $reg_mat["local"] . '"' . $separador . '""' . $separador . '"' . $reg_mat["unidade"] . '"' . "\r\n";
}

$filename = "LM_" . $reg_os["os"] . "_" . date("Ymd");

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$filename.csv");
header("Pragma: no-cache");
header("Expires: 0");
print $header.$data;
exit();

?> 
