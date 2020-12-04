<?php
// Dados da conexão com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

//error_reporting(E_ERROR);

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db();


$sql = "SELECT AE8_EQUIP, AE8_RECURS FROM AE8010 WHERE D_E_L_E_T_ = '' ";

$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);

while ($regs = mssql_fetch_array($con))
{
	$usql = "UPDATE AFU010 SET ";
	$usql .= "AFU_EQUIP = '".$regs["AE8_EQUIP"]."' ";
	$usql .= "WHERE AFU_RECURS = '".$regs["AE8_RECURS"]."' ";
	
   $cont_ms = mssql_query($usql,$db->conexao_ms) or die(mssql_get_last_message());
	
	//echo $usql . "<br>";	
	
}

$db->fecha_db();

$db->fecha_ms_db();




?>
