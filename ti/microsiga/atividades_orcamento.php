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

$sql = "SELECT * FROM ".DATABASE.".atividades ";

$reg = mysql_query($sql,$db->conexao);

$i = 1;

while($cont = mysql_fetch_array($reg))
{
	
	$item = 1;
	
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".Cargos, ".DATABASE.".cargos_grupos ";
	$sql .= "WHERE atividades_orcamento.id_atividade = '".$cont["id_atividade"]."' ";
	$sql .= "AND atividades_orcamento.id_funcao = Cargos.id_funcao ";
	$sql .= "AND Cargos.id_cargo_grupo = cargos_grupos.id_cargo_grupo ";
	
	$reg1 = mysql_query($sql,$db->conexao);
	
	while($cont1 = mysql_fetch_array($reg1))
	{
	
		$sql = "INSERT INTO AE2010 ";
		$sql .= "(AE2_COMPOS, AE2_ITEM, AE2_QUANT, AE2_RECURS, AE2_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
		$sql .= "VALUES (";
		$sql .= "'".maiusculas(tiraacentos($cont["codigo"]))."', ";
		$sql .= "'".$item."', ";
		$sql .= "'".($cont1["porcentagem"]/100)*$cont["horasestimadas"]."', ";
		$sql .= "'ORC_".sprintf("%011d",$cont1["id_cargo_grupo"])."', ";
		$sql .= "'".$cont1["atividades_orcamento"]."', ";
		$sql .= "'".$i."', ";
		$sql .= "'0') ";
		
		$i++;
			
		//$cont_requisicao = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());

		echo maiusculas(tiraacentos($cont["codigo"]))." - ORC_".sprintf("%011d",$cont1["id_cargo_grupo"])." - ".($cont1["porcentagem"]/100)*$cont["horasestimadas"]." - ".$item."<br>";
		
		//$item++;
	}
		
}
*/ 
/*
//Atividades
$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades ";
$sql .= "WHERE setores.id_setor = atividades.cod ";

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
	$sql .= "(AE1_FILIAL, AE1_COMPOS, AE1_DESCRI, AE1_GRPCOM, AE1_UM, AE1_USO, AE1_ULTATU,AE1_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
	$sql .= "VALUES (";
	$sql .= "'01', ";
	$sql .= "'".maiusculas(tiraacentos($cont["codigo"]))."', ";
	$sql .= "'".trim($exp)."', ";
	$sql .= "'".maiusculas(tiraacentos($cont["abreviacao"]))."', ";
	$sql .= "'".$formato."', ";
	$sql .= "'1', ";
	$sql .= "'".date("Ymd")."', ";
	$sql .= "'".$cont["id_atividade"]."', ";
	$sql .= "'".$i."', ";
	$sql .= "'0') ";
	
	//echo maiusculas(tiraacentos($cont["codigo"]))." - ".$cont["abreviacao"]." - ".trim($exp)." - ".$i."<br>";
	
	$i++;
		
	//$cont_requisicao = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());
	
}

echo $i;
*/

$sql = "SELECT AE2_ITEM, R_E_C_N_O_  FROM AE2010 ";

$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);

while ($regs = mssql_fetch_array($con))
{
	$sql = "UPDATE AE2010 SET "; 
	$sql .= "AE2_ITEM = '".sprintf("%02d",$regs["AE2_ITEM"])."' ";
	$sql .= "WHERE R_E_C_N_O_ = '".$regs["R_E_C_N_O_"]."' ";
	
	$c = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);

}



$db->fecha_db();

$db->fecha_ms_db();

?>
