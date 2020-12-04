<?php
// Dados da conex�o com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db(); 

//FUN��ES
$sql = "SELECT * FROM ".DATABASE.".Cargos ";

$reg = mysql_query($sql,$db->conexao);

$i = 1;

while($cont = mysql_fetch_array($reg))
{
	/*
	$texto = explode(" ",$cont["descricao"]);
	
	$exp = "";
	
	for($j=0;$j<count($texto);$j++)
	{
		if(strlen($cont["descricao"])>20)
		{
		
			$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,5);
		}
		else
		{
			$exp .= maiusculas(tiraacentos($texto[$j]));
		}
		
		$exp .= " ";
	}	
	
	$sql = "INSERT INTO SRJ010 ";
	$sql .= "(RJ_FILIAL, RJ_FUNCAO, RJ_DESC, RJ_CARGO, RJ_CODCBO, RJ_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
	$sql .= "VALUES (";
	$sql .= "'01', ";
	$sql .= "'".sprintf("%05d",$cont["id_funcao"])."', ";
	$sql .= "'".trim($exp)."', ";
	$sql .= "'".sprintf("%05d",$cont["id_cargo_grupo"])."', ";
	$sql .= "'".sprintf("%06d",$cont["cbo_2002"])."', ";
	$sql .= "'".$cont["id_funcao"]."', ";
	$sql .= "'".$i."', ";
	$sql .= "'0') ";
	*/
	
	$sql = "INSERT INTO AN1010 ";
	$sql .= "(AN1_CODIGO, AN1_DESCRI, R_E_C_N_O_) ";
	$sql .= "VALUES (";
	$sql .= "'".sprintf("%09d",$cont["id_funcao"])."', ";
	$sql .= "'".trim($cont["descricao"])."', ";
	$sql .= "'".$i."') ";
	
	echo sprintf("%09d",$cont["id_funcao"])." - ".trim($cont["descricao"])."<br>";
	
	$i++;
		
	$cont_requisicao = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());
	
}

$db->fecha_db();

$db->fecha_ms_db();

?>
