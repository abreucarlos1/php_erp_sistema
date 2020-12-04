<?php
// Dados da conex�o com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db(); 

/*
//CARGOS
$sql = "SELECT * FROM ".DATABASE.".cargos_grupos, ".DATABASE.".Cargos, ".DATABASE.".Funcionarios ";
$sql .= "WHERE Cargos.id_cargo_grupo = cargos_grupos.id_cargo_grupo ";
$sql .= "AND Funcionarios.id_funcao = Cargos.id_funcao ";
//$sql .= "GROUP BY cargos_grupos.id_cargo_grupo ";

$reg = mysql_query($sql,$db->conexao);

while($cont = mysql_fetch_array($reg))
{
	$sql = "SELECT * FROM AE8010 ";
	$sql .= "WHERE AE8_ID = '".$cont["id_funcionario"]."' ";
	$sql .= "AND AE8_RECURS LIKE 'FUN_%' ";
	$sql .= "AND D_E_L_E_T_ = '' ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	if(mssql_num_rows($con)>0)
	{
		
		$reg3 = mssql_fetch_array($con);
		//Altera a fun��o no banco microsiga(RH)
		$sql = "UPDATE AE8010 SET ";
		$sql .= "AE8_FUNCAO = '".sprintf("%05d",$cont["id_cargo_grupo"])."' ";	
		$sql .= "WHERE R_E_C_N_O_ = '".trim($reg3["R_E_C_N_O_"])."' ";														//ID CARGO													
		
		//echo $cont["descricao"]." -- ".$cont["grupo"]."<br>";
		
		echo $reg3["AE8_RECURS"]." -- ".$reg3["AE8_ID"]." - ".$cont["id_cargo_grupo"]."<br>";
						
		$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
		
	}
	
}
*/

$sql = "SELECT * FROM AE8010 ";
$sql .= "WHERE D_E_L_E_T_ = '' ";

$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
while($reg3 = mssql_fetch_array($con))
{
	$sql = "UPDATE AE8010 SET ";
	$sql .= "AE8_FUNCAO = '".sprintf("%09d",$reg3["AE8_FUNCAO"])."' ";	
	$sql .= "WHERE R_E_C_N_O_ = '".trim($reg3["R_E_C_N_O_"])."' ";														//ID CARGO													
	
	//echo $cont["descricao"]." -- ".$cont["grupo"]."<br>";
	
	//echo $reg3["AE8_RECURS"]." -- ".$reg3["AE8_ID"]." - ".$cont["id_cargo_grupo"]."<br>";
					
	$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
	
}


/*
//CARGOS
$sql = "SELECT * FROM ".DATABASE.".cargos_grupos ";

$reg = mysql_query($sql,$db->conexao);

$i = 1;

while($cont = mysql_fetch_array($reg))
{
	
	$sql = "INSERT INTO AN1010 ";
	$sql .= "(AN1_CODIGO, AN1_DESCRI, R_E_C_N_O_) ";
	$sql .= "VALUES (";
	$sql .= "'".sprintf("%09d",$cont["id_cargo_grupo"])."', ";
	$sql .= "'".maiusculas(tiraacentos($cont["grupo"]))."', ";
	$sql .= "'".$i."') ";
	
	$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);		

	$i++;
}
*/

$db->fecha_db();

$db->fecha_ms_db();

?>
