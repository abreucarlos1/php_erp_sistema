<?
require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db();


//CARGOS
$sql = "SELECT * FROM ".DATABASE.".cargos_grupos, ".DATABASE.".Cargos, ".DATABASE.".Funcionarios ";
$sql .= "WHERE cargos_grupos.id_cargo_grupo = Cargos.id_cargo_grupo ";
$sql .= "AND Funcionarios.id_funcao = Cargos.id_funcao ";
$sql .= "AND Funcionarios.situacao = 'ATIVO' ";
$sql .= "GROUP BY cargos_grupos.id_cargo_grupo ";

$reg = mysql_query($sql,$db->conexao);

$i = 1;

while($cont = mysql_fetch_array($reg))
{
	$texto = explode(" ",$cont["grupo"]);
	
	switch ($texto[0]) 
	{
		case 'ENGENHEIRO':
			$valor = '50.00';
		break;
		
		case 'SUPERVISOR':
			$valor = '40.00';
		break;
		
		case 'COORDENADOR':
			$valor = '30.00';
		break;
		
		case 'PROJETISTA':
			$valor = '20.00';
		break;
		
		case 'DESENHISTA':
			$valor = '10.00';
		break;
		
		default: $valor = 0;		
	}
	
	$exp = "";
	
	for($j=0;$j<count($texto);$j++)
	{
		if(strlen($cont["grupo"])>30)
		{
		
			$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,10);
		}
		else
		{
			$exp .= maiusculas(tiraacentos($texto[$j]));
		}
		
		$exp .= " ";
	}	
	
	
	$sql = "SELECT R_E_C_N_O_ FROM AE8010 ";
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);

	$recno = $regs["R_E_C_N_O_"] + 1;
	
	
	//Insere o funcionario no banco microsiga
	$sql = "INSERT INTO AE8010 ";
	$sql .= "(AE8_RECURS, AE8_DESCRI, AE8_TIPO, AE8_UMAX, AE8_PRODUT, AE8_CALEND, AE8_TPREAL, AE8_EMAIL, ";
	$sql .= "AE8_CUSFIX, AE8_VALOR, AE8_PRDREA, AE8_ATIVO, AE8_CODFUN, AE8_EQUIP, R_E_C_N_O_, R_E_C_D_E_L_, AE8_ID, AE8_ID_CAR) ";
	//$sql .= "A2_MUN, A2_TIPO, RA_MAT, RA_NOME, RA_NATURAL, RA_NACIONA, RA_SEXO, RA_ESTCIVI, RA_NASC, ";
	//$sql .= "RA_CC, RA_ADMISSA, RA_OPCAO, RA_BCDPFGT, RA_CTDPFGT, RA_HRSMES, RA_HRSEMAN, RA_CODFUNC,  ";
	//$sql .= "RA_CATFUNC, RA_TIPOPGT, RA_TIPOADM, RA_VIEMRAI, RA_GRINRAI, RA_NUMCP, RA_SERCP, RA_ADTPOSE, RA_TNOTRAB, ID) ";
	$sql .= "VALUES ( ";
	$sql .= "'ORC_".sprintf("%011d",$cont["id_cargo_grupo"])."', ";
	$sql .= "'".$exp."', ";
	$sql .= "'2', ";
	$sql .= "'100', ";
	$sql .= "'', ";
	$sql .= "'001', ";
	$sql .= "'4', ";
	$sql .= "'', ";
	$sql .= "'".$valor."', ";
	$sql .= "'".$valor."', ";
	$sql .= "'', ";
	$sql .= "'1', ";
	$sql .= "'', ";
	$sql .= "'', ";
	$sql .= "'".$recno."', ";
	$sql .= "'0', ";
	$sql .= "'0', ";
	$sql .= "'".$cont["id_cargo_grupo"]."') ";
	
	$i++;
	
	echo $exp . "<br>";
		
	$cont_requisicao = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
	
}



/*
//CARGOS
$sql = "SELECT * FROM ".DATABASE.".cargos_grupos ";

$reg = mysql_query($sql,$db->conexao);

$i = 1;

while($cont = mysql_fetch_array($reg))
{
	$texto = explode(" ",$cont["grupo"]);
	
	$exp = "";
	
	for($j=0;$j<count($texto);$j++)
	{
		if(strlen($cont["grupo"])>30)
		{
		
			$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,5);
		}
		else
		{
			$exp .= maiusculas(tiraacentos($texto[$j]));
		}
		
		$exp .= " ";
	}	
	
	
	$sql = "SELECT R_E_C_N_O_ FROM SQ3010 ";
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);

	$recno = $regs["R_E_C_N_O_"] + 1;	
	
	$sql = "INSERT INTO SQ3010 ";
	$sql .= "(Q3_CARGO, Q3_DESCSUM, Q3_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
	$sql .= "VALUES (";
	$sql .= "'".sprintf("%05d",$cont["id_cargo_grupo"])."', ";
	$sql .= "'".trim($exp)."', ";
	$sql .= "'".$cont["id_cargo_grupo"]."', ";
	$sql .= "'".$recno."', ";
	$sql .= "'0') ";
	
	$i++;
	
	echo $exp . "<br>";
		
	$cont_requisicao = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);
	
}
*/

$db->fecha_db();

$db->fecha_ms_db();

?>
