<?php
// Dados da conex�o com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

//error_reporting(E_ERROR);

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db();


$sql = "SELECT * FROM ".DATABASE.".contatos ";
$sql .= "WHERE contatos.situacao = '1' ";
$sql .= "AND contatos.id_empresa_erp IN ('48','49','147','166','1044','169','167','74','1226','1241','1244','1262','1243','1246','1291','46','1289','1311','344','342','1046','108','109','1290','1339','246','1023','1102','412') ";

$cont = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel selecionar os dados.".$sql);

$i = 1;

while($reg = mysql_fetch_array($cont))
{
	
	
	$sql = "SELECT R_E_C_N_O_ FROM SU5010 ";
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);

	$recno = $regs["R_E_C_N_O_"] + 1;
	
	$sql = "SELECT U5_CODCONT FROM SU5010 ";
	$sql .= "ORDER BY U5_CODCONT DESC ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);

	$id_cont = $regs["U5_CODCONT"] + 1;
	
	$tel = explode(" ",$reg["telefone"]);
	
	$cel = explode(" ",$reg["celular"]);
	
	$array_tel = array('(',')');
	
	$ddd = str_replace($array_tel,"",$tel[0]);		 
	
	//Insere o CONTATO no banco microsiga
	$sql = "INSERT INTO SU5010 ";
	$sql .= "(U5_CODCONT, U5_CONTAT, U5_EMAIL, U5_ATIVO, U5_DDD, U5_CELULAR, U5_FCOM1, ";
	$sql .= "R_E_C_N_O_, R_E_C_D_E_L_, U5_ID_DVM) ";
	$sql .= "VALUES ( ";
	$sql .= "'".sprintf("%06d",$id_cont)."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["nome_contato"]))."', ";
	$sql .= "'".tiraacentos($reg["email"])."', ";
	$sql .= "'1', ";
	$sql .= "'".$ddd."', ";
	$sql .= "'".str_replace("-","",$cel[1])."', ";
	$sql .= "'".str_replace("-","",$tel[1])."', ";
	$sql .= "'".$recno."', ";
	$sql .= "'0', ";
	$sql .= "'".$reg["id_contato"]."') ";
	
	echo sprintf("%06d",$id_cont) . " - ".maiusculas(tiraacentos($reg["nome_contato"]))." - ".tiraacentos($reg["email"])." - ".$ddd."-".$cel[1]."-".$tel[1]."<br>";
	
	//$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());

	$i++;
}

echo $i;

$db->fecha_db();

$db->fecha_ms_db();


?>
