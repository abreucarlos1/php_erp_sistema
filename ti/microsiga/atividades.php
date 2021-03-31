<?php
// Dados da conexão com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db(); 

//Atividades
$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades ";
$sql .= "WHERE setores.id_setor = atividades.cod ";
$sql .= "ORDER BY codigo";

$reg = mysql_query($sql,$db->conexao);

$i = 1;

while($cont = mysql_fetch_array($reg))
{
	$texto = explode(" ",$cont["descricao"]);
	
	$exp = "";
	
	for($j=0;$j<count($texto);$j++)
	{
		if(strlen($cont["setor"])>90)
		{
		
			$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,30);
		}
		else
		{
			$exp .= maiusculas(tiraacentos($texto[$j]));
		}
		
		$exp .= " ";
	}
	
	switch($cont["id_formato"])
	{
		case 0 : $formato = "HR";
		break;
		
		case 1 : $formato = "A0";
		break;
		
		case 2 : $formato = "A1";
		break;
		
		case 3 : $formato = "A2";
		break;
		
		case 4 : $formato = "A3";
		break;
		
		case 5 : $formato = "A4";
		break;
		
		case 6 : $formato = "HR";
		break;
		
		case 8 : $formato = "UN";
		break;
		
		case 9 : $formato = "HR";
		break;
	}	
	
	$sql = "INSERT INTO AE1010 ";
	$sql .= "(AE1_COMPOS, AE1_DESCRI, AE1_GRPCOM, AE1_UM, AE1_USO, AE1_ULTATU, AE1_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
	$sql .= "VALUES (";
	$sql .= "'".maiusculas(tiraacentos($cont["codigo"]))."', ";
	$sql .= "'".trim($exp)."', ";
	$sql .= "'".maiusculas(tiraacentos($cont["abreviacao"]))."', ";
	$sql .= "'".$formato."', ";
	$sql .= "'1', ";
	$sql .= "'".date("Ymd")."', ";
	$sql .= "'".$cont["id_atividade"]."', ";
	$sql .= "'".$i."', ";
	$sql .= "'0') ";
	
	echo maiusculas(tiraacentos($cont["codigo"]))." - ".$cont["abreviacao"]." - ".trim($exp)." - ".$i."<br>";
	
	$i++;
		
	$cont_requisicao = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());
	
}

echo $i;

$db->fecha_db();

$db->fecha_ms_db();

?>
