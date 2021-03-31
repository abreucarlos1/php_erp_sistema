<?php
// Dados da conexão com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db(); 

//setores
$sql = "SELECT * FROM ".DATABASE.".setores ";

$reg = mysql_query($sql,$db->conexao);

$i = 1;

while($cont = mysql_fetch_array($reg))
{
	$texto = explode(" ",$cont["setor"]);
	
	$exp = "";
	
	for($j=0;$j<count($texto);$j++)
	{
		if(strlen($cont["setor"])>30)
		{
		
			$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,5);
		}
		else
		{
			$exp .= maiusculas(tiraacentos($texto[$j]));
		}
		
		$exp .= " ";
	}	
	/*
	$sql = "INSERT INTO AE5010 ";
	$sql .= "(AE5_FILIAL, AE5_GRPCOM, AE5_DESCRI, R_E_C_N_O_, R_E_C_D_E_L_) ";
	$sql .= "VALUES (";
	$sql .= "'01', ";
	$sql .= "'".maiusculas(tiraacentos($cont["abreviacao"]))."', ";
	$sql .= "'".trim($exp)."', ";
	//$sql .= "'".$cont["id_setor"]."', ";
	$sql .= "'".$i."', ";
	$sql .= "'0') ";
	*/
	
	$sql = "INSERT INTO AED010 ";
	$sql .= "(AED_FILIAL, AED_EQUIP, AED_DESCRI, AED_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
	$sql .= "VALUES (";
	$sql .= "'01', ";
	$sql .= "'".sprintf("%010d",$cont["id_setor"])."', ";
	$sql .= "'".trim($exp)."', ";
	$sql .= "'".$cont["id_setor"]."', ";
	$sql .= "'".$i."', ";
	$sql .= "'0') ";
		
	echo $cont["abreviacao"]." - ".trim($exp)."<br>";
	
	$i++;
		
	$cont_requisicao = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());
	
}

$db->fecha_db();

$db->fecha_ms_db();

?>
