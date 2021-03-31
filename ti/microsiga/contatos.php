<?php
// Dados da conexão com banco de dados

require("../../includes/conectdb.inc.php");
require("../../includes/tools.inc.php");

//error_reporting(E_ERROR);


$db = new banco_dados;

$sql = "SELECT * FROM test.contatos_protheus ";

$cont = $db->select($sql,'MYSQL');

$i = 1;

while($reg = mysql_fetch_array($cont))
{	
	$sql = "SELECT R_E_C_N_O_ FROM SU5010 ";
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$con = $db->select($sql,'MSSQL');
	
	$regs = mssql_fetch_array($con);

	$recno = $regs["R_E_C_N_O_"] + 1;
	
	/*
	$sql = "SELECT U5_CODCONT FROM SU5010 ";
	$sql .= "ORDER BY U5_CODCONT DESC ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);

	$id_cont = $regs["U5_CODCONT"] + 1;
	
	
	$tel = explode(" ",$reg["telefone"]);
	
	$cel = explode(" ",$reg["celular"]);
	
	$array_tel = array('(',')');
	
	$ddd = str_replace($array_tel,"",$tel[0]);
	*/		 
	
	//Insere o CONTATO no banco microsiga
	$sql = "INSERT INTO SU5010 ";
	$sql .= "(U5_CODCONT, U5_CONTAT, U5_END, U5_BAIRRO, U5_MUN, U5_EST, U5_CEP, U5_CODPAIS, U5_CELULAR, U5_FCOM1, U5_EMAIL, U5_DPTODVM, U5_DDDFCO1, U5_SEGMEN, U5_CLASS, U5_ATIVO, ";
	$sql .= "R_E_C_N_O_, R_E_C_D_E_L_) ";
	$sql .= "VALUES ( ";
	$sql .= "'".sprintf("%06d",$reg["U5_CODCONT"])."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_CONTAT"]))."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_END"]))."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_BAIRRO"]))."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_MUN"]))."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_EST"]))."', ";
	$sql .= "'".$reg["U5_CEP"]."', ";
	$sql .= "'".$reg["U5_CODPAIS"]."', ";
	$sql .= "'".$reg["U5_CELULAR"]."', ";
	$sql .= "'".$reg["U5_FCOM1"]."', ";
	$sql .= "'".minusculas(tiraacentos($reg["U5_EMAIL"]))."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_DPTODVM"]))."', ";
	$sql .= "'".$reg["U5_DDDFCO1"]."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_SEGMEN"]))."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["U5_CLASS"]))."', ";
	$sql .= "'1', ";
	$sql .= "'".$recno."', ";
	$sql .= "'0') ";

	$db->insert($sql,'MSSQL');

	$i++;
}

echo $i;

?>
