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


/*
//RECURSOS
$sql = "SELECT * FROM AF8010, AF9010 ";
$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";

//$sql .= "AND AF8010.AF8_FASE = '01' ";
$sql .= "AND AF9010.AF9_REVISA = '0002' ";
//$sql .= "AND AF8010.AF8_ENCPRJ = '1' ";
$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
//$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
$sql .= "AND AF9010.AF9_HESF = 0  ";
//$sql .= "AND AF9010.R_E_C_N_O_ between 0 and 3000   ";
$sql .= "ORDER BY AF8_PROJET, AF9_TAREFA ";

$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);


while($cont = mssql_fetch_array($cont_ms))
{
	
	$sql = "SELECT SUM(AFA_QUANT) AS QTD FROM AFA010 ";
	$sql .= "WHERE AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.AFA_PROJET = '".$cont["AF9_PROJET"]."' ";
	$sql .= "AND AFA010.AFA_REVISA = '".$cont["AF9_REVISA"]."' ";
	$sql .= "AND AFA010.AFA_TAREFA = '".$cont["AF9_TAREFA"]."' ";
		
	$cont_ms1 = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
	
	$cont1 = mssql_fetch_array($cont_ms1);
	
	//echo $cont["AF9_PROJET"]." %% ".$cont["AF9_TAREFA"]." --- ".$cont1["QTD"]." # ".$cont["R_E_C_N_O_"]."<br>";
	
	
	if($cont1["QTD"]>=0)
	{
		$usql = "UPDATE AF9010 SET ";
		$usql .= "AF9010.AF9_HESF = '".$cont1["QTD"]."' ";
		$usql .= "WHERE AF9010.R_E_C_N_O_ = '".$cont["AF9010.R_E_C_N_O_"]."' ";
		
		//$contU = mssql_query($usql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
		
		echo $cont["AF9_PROJET"]." - ".$cont["AF9_TAREFA"]." = ".$cont1["QTD"]." --# ".$cont["R_E_C_N_O_"]."<br>";
	
	}
	
	
}
*/



$sql = "SELECT * FROM AFC010 
 
WHERE AFC010.D_E_L_E_T_ = '' 
AND AFC010.AFC_REVISA = '0002' 

AND AFC010.R_E_C_N_O_ between 40000 and 80000

ORDER BY AFC_PROJET "; 

$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);


while($cont = mssql_fetch_array($cont_ms))
{
	
	$sql = "SELECT SUM(AFC_HESF) AS QTD FROM AFC010 ";
	$sql .= "WHERE AFC010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFC010.AFC_PROJET = '".$cont["AFC_PROJET"]."' ";
	$sql .= "AND AFC010.AFC_REVISA = '".$cont["AFC_REVISA"]."' ";
	$sql .= "AND AFC010.AFC_NIVEL = 002 ";
		
	$cont_ms1 = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
	
	$cont1 = mssql_fetch_array($cont_ms1);
	
	//echo $cont["AF9_PROJET"]." %% ".$cont["AF9_TAREFA"]." --- ".$cont1["QTD"]." # ".$cont["R_E_C_N_O_"]."<br>";
	
	
	if($cont1["QTD"]>=0)
	{
		$usql = "UPDATE AFC010 SET ";
		$usql .= "AFC010.AFC_HESF = '".$cont1["QTD"]."' ";
		//$usql .= "WHERE AF9010.R_E_C_N_O_ = '".$cont["AF9010.R_E_C_N_O_"]."' ";
		$usql .= "WHERE AFC010.AFC_PROJET = '".$cont["AFC_PROJET"]."' ";
		$usql .= "AND AFC010.AFC_REVISA = '".$cont["AFC_REVISA"]."' ";
		$usql .= "AND AFC010.AFC_NIVEL = '001' ";
		
		$contU = mssql_query($usql,$db->conexao_ms) or die(mssql_get_last_message().$usql);
		
		echo $cont["AFC_PROJET"]." - ".$cont["AFC_EDT"]." = ".$cont1["QTD"]." --# ".$cont["R_E_C_N_O_"]."<br>";
	
	}
	
	
}



echo "fim";

$db->fecha_ms_db();


?>
