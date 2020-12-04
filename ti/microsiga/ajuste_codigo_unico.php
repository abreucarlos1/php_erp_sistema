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

//ACERTA O ORCAMENTO
$sql = "SELECT * FROM AF1010 ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
//$sql .= "AND AF1_FASE = '04' ";

//$sql .= "AND AF1_ORCAME = '0000006607' ";
 
$sql .= "ORDER BY AF1_ORCAME "; 

$cont1 = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);

while($regs1 = mssql_fetch_array($cont1))
{
	
	$sql = "SELECT * FROM AF2010 ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND AF2_ORCAME = '".$regs1["AF1_ORCAME"]."' ";
	$sql .= "ORDER BY AF2_TAREFA ";
	
	$cont2 = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
	
	$cod_unico = 1;
	
	while($regs2 = mssql_fetch_array($cont2))
	{
		$usql = "UPDATE AF2010 SET ";
		$usql .= "AF2_CODIGO = '".sprintf("%06d",$cod_unico)."' ";
		$usql .= "WHERE R_E_C_N_O_ = '".$regs2["R_E_C_N_O_"]."' ";
		
		mssql_query($usql,$db->conexao_ms) or die(mssql_get_last_message().$usql);
		
		$sql = "SELECT * FROM AF9010 ";
		$sql .= "WHERE D_E_L_E_T_ = '' ";
		$sql .= "AND AF9_PROJET = '".$regs2["AF2_ORCAME"]."' ";
		$sql .= "AND AF9_TAREFA = '".$regs2["AF2_TAREFA"]."' ";
		$sql .= "ORDER BY AF9_REVISA ";
		
		$cont3 = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);

		while($regs3 = mssql_fetch_array($cont3))
		{
			echo $regs2["AF2_ORCAME"]." - ".$regs2["AF2_TAREFA"]." == ".$regs3["AF9_TAREFA"]." = ".sprintf("%06d",$cod_unico)."<br>";
			
			$usql = "UPDATE AF9010 SET ";
			$usql .= "AF9_CODIGO = '".sprintf("%06d",$cod_unico)."' ";
			$usql .= "WHERE R_E_C_N_O_ = '".$regs3["R_E_C_N_O_"]."' ";
			
			$contU1 = mssql_query($usql,$db->conexao_ms) or die(mssql_get_last_message().$usql);
		}
		
		$cod_unico++;		
	}
	
}

echo "fim";

$db->fecha_ms_db();


?>
