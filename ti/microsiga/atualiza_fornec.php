<?php
// Dados da conexão com banco de dados

//Atualiza o banco de funcionarios PJ com o codigo
//de fornecedor do microsiga protheus

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db();


$sql = "SELECT * FROM ".DATABASE.".Funcionarios, ".DATABASE.".empresa_funcionarios ";
$sql .= "WHERE Funcionarios.id_empfunc = empresa_funcionarios.id_empfunc ";
$sql .= "AND Funcionarios.situacao = 'ATIVO' ";
$sql .= "GROUP BY Funcionarios.id_funcionario ";
$sql .= "ORDER BY Funcionarios.funcionario ";

$cont = mysql_query($sql,$db->conexao) or die("Não foi possível selecionar os dados.".$sql);

$chars = array('-','.','/');

while($reg = mysql_fetch_array($cont))
{

	$cnpj = str_replace($chars,"",$reg["empresa_cnpj"]);
	
	$sql = "SELECT * FROM SA2010 ";
	$sql .= "WHERE SA2010.A2_CGC = '".$cnpj."' "; 
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);
	
	$usql = "UPDATE ".DATABASE.".Funcionarios SET ";
	$usql .= "id_cod_fornec = '".$regs["A2_COD"]."' ";
	$usql .= "WHERE id_funcionario = '".$reg["id_funcionario"]."' ";

	$cont1 = mysql_query($usql,$db->conexao) or die("Não foi possível atualizar os dados.".$usql);
	
	/*	
	//Insere o funcionario no banco microsiga
	$sql = "INSERT INTO DVM001 ";
	$sql .= "(AE8_RECURS, AE8_DESCRI, AE8_TIPO, AE8_UMAX, AE8_PRODUT, AE8_CALEND, AE8_TPREAL, AE8_EMAIL, ";
	$sql .= "AE8_ESPEC, AE8_CUSFIX, AE8_VALOR, AE8_PRDREA, AE8_ATIVO1, AE8_CODFUN, AE8_EQUIP, A2_NOME, A2_NREDUZ, A2_END, ";
	$sql .= "A2_MUN, A2_TIPO, RA_MAT, RA_NOME, RA_NATURAL, RA_NACIONA, RA_SEXO, RA_ESTCIVI, RA_NASC, ";
	$sql .= "RA_CC, RA_ADMISSA, RA_OPCAO, RA_BCDPFGT, RA_CTDPFGT, RA_HRSMES, RA_HRSEMAN, RA_CODFUNC,  ";
	$sql .= "RA_CATFUNC, RA_TIPOPGT, RA_TIPOADM, RA_VIEMRAI, RA_GRINRAI, RA_NUMCP, RA_SERCP, RA_ADTPOSE, RA_TNOTRAB, ID) ";
	$sql .= "VALUES ( ";
	$sql .= "'FUN_".sprintf("%011d",$reg["id_funcionario"])."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["funcionario"]))."', ";
	$sql .= "'2', ";
	$sql .= "'100', ";
	$sql .= "'FAT0001', ";
	$sql .= "'001', ";
	$sql .= "'4', ";
	$sql .= "'".$reg["email"]."', ";
	$sql .= "'".$espec."', ";
	$sql .= "'', ";
	$sql .= "'".$valor."', ";
	$sql .= "'FAT0001', ";
	$sql .= "'".$status."', ";
	$sql .= "'', ";
	$sql .= "'".sprintf("%010d",$reg["id_setor"])."', ";
	$sql .= "'".$a2_nome."', ";
	$sql .= "'', ";
	$sql .= "'".$a2_end."', ";
	$sql .= "'".$a2_mun."', ";
	$sql .= "'".$a2_tipo."', ";
	$sql .= "'', ";
	$sql .= "'".maiusculas(tiraacentos($reg["funcionario"]))."', ";
	$sql .= "'".$reg["estado_nascimento"]."', ";
	$sql .= "'".$reg["id_nacionalidade"]."', ";
	$sql .= "'".$reg["sexo"]."', ";
	$sql .= "'".$reg["id_estado_civil"]."', ";
	$sql .= "'".str_replace("-","",$reg["data_nascimento"])."', ";
	$sql .= "'', ";
	$sql .= "'".str_replace("-","",$reg["clt_admissao"])."', ";
	$sql .= "'".$ra_opcao."', ";
	$sql .= "'".$ra_bcdpfgt."', ";
	$sql .= "'', ";
	$sql .= "'".$ra_hrsmes."', ";
	$sql .= "'".$ra_hrsseman."', ";
	$sql .= "'".$ra_codfunc."', ";
	$sql .= "'".$ra_catfunc."', ";
	$sql .= "'M', ";
	$sql .= "'".$ra_tipoadm."', ";
	$sql .= "'".$ra_viemrai."', ";
	$sql .= "'".$reg["id_escolaridade"]."', ";
	$sql .= "'".$ra_numcp."', ";
	$sql .= "'".$ra_sercp."', ";
	$sql .= "'N', ";
	$sql .= "'".$ra_tnotrab."', ";
	$sql .= "'".$reg["id_funcionario"]."') ";
	
	echo maiusculas(tiraacentos($reg["funcionario"])) . " - ".$a2_nome."<br>";
	
	//$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());

	$i++;
	*/
	
	echo $reg["id_funcionario"]." - ".$reg["funcionario"]." - ". $cnpj . " - ".$regs["A2_COD"]. "<br>";
	
}



/*
//Insere o funcionario
$sql = "INSERT INTO DVM001 ";
$sql .= "(AE8_RECURS,R_E_C_N_O_) ";
$sql .= "VALUES (";
$sql .= "'TESTE',2) ";

$cont_requisicao = mssql_query($sql,$s) or die(mssql_get_last_message());

*/

?>
