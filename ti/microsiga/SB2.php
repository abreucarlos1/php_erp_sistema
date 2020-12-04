<?php
// Dados da conexão com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

//error_reporting(E_ERROR);

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSTST';
$db->conexao_ms_db();


$sql = "SELECT * FROM SB2010 WHERE B2_FILIAL = '01' ";

$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);

while ($regs = mssql_fetch_array($con))
{

	$sql = "SELECT * FROM SB2010 WHERE B2_FILIAL = '02' ";
	$sql .= "AND B2_COD = '".$regs["B2_COD"]."' ";
	
	$con1 = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	while ($regs1 = mssql_fetch_array($con1))
	{

		$usql = "UPDATE SB2010 SET ";
		$usql .= "D_E_L_E_T_ = '*', ";
		$usql .= "R_E_C_D_E_L_ = '".$regs1["R_E_C_N_O_"]."' ";
		$usql .= "WHERE R_E_C_N_O_ = '".$regs1["R_E_C_N_O_"]."' ";
		
	   $cont_ms = mssql_query($usql,$db->conexao_ms) or die(mssql_get_last_message());
		
		//echo $usql . "<br>";
	}	
	
}

$db->fecha_db();

$db->fecha_ms_db();




?>
