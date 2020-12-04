<?php
// Dados da conex�o com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db();

$sql = "SELECT * FROM ".DATABASE.".Bancos ";

$cont = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel selecionar os dados.".$sql);

while($reg = mysql_fetch_array($cont))
{
	$bancos[$reg["id_banco"]] = $reg["dv"];
} 

$sql = "SELECT *, Cargos.descricao AS descricao, Funcionarios.id_funcionario AS id_funcionario FROM ".DATABASE.".Cargos, ".DATABASE.".setores, ".DATABASE.".Funcionarios ";
$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (Funcionarios.id_empfunc = empresa_funcionarios.id_empfunc) ";
$sql .= "LEFT JOIN ".DATABASE.".salarios ON (Funcionarios.id_salario = salarios.id_salario) ";
$sql .= "LEFT JOIN ".DATABASE.".local ON (Funcionarios.id_local = local.id_local) ";
$sql .= "LEFT JOIN ".DATABASE.".usuarios ON (Funcionarios.id_funcionario = usuarios.id_funcionario) ";
$sql .= "WHERE Funcionarios.id_setor = setores.id_setor ";
$sql .= "AND Funcionarios.id_funcao = Cargos.id_funcao ";
$sql .= "AND Funcionarios.situacao = 'ATIVO' ";
$sql .= "GROUP BY Funcionarios.id_funcionario ";
$sql .= "ORDER BY Funcionarios.funcionario ";

$cont = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel selecionar os dados.".$sql);

$i = 1;

while($reg = mysql_fetch_array($cont))
{
	if($reg["situacao"]=="DESLIGADO")
	{
		$status = 2;
	}
	else
	{
		$status = 1;
	}

	/*
	if($reg[" tipo_contrato"]=="EST" || $reg[" tipo_contrato"]=="CLT" || $reg[" tipo_contrato"]=="SOCIO" || $reg[" tipo_contrato"]=="SC+CLT" || $reg[" tipo_contrato"]=="SC+CLT+MENS")
	{
		//DADOS CLT;
		$ra_opcao = str_replace("-","",$reg["fgts_data"]);
		$ra_bcdpfgt = $banco[$reg["fgts_banco"]].$reg["fgts_agencia"];
		$ra_hrsmes = 200;
		$ra_hrsseman = 40;
		$ra_codfunc = sprintf("%05d",$reg["id_funcao"]);
		$ra_catfunc = $reg["id_categoria_funcional"];
		$ra_tipoadm = $reg["id_tipo_admissao"];
		$ra_viemrai = $reg["id_vinculo_empregaticio"];
		$ra_numcp = $reg["ctps_num"];
		$ra_sercp = $reg["ctps_serie"];
		$ra_tnotrab = $reg["id_turno_trabalho"];
		
		$a2_nome = "";
		$a2_end = "";
		$a2_mun	= "";
		$a2_tipo = "";
	}
	else
	{
		if($reg[" tipo_contrato"]=="SC" || $reg[" tipo_contrato"]=="SC+CLT" || $reg[" tipo_contrato"]=="SC+MENS" || $reg[" tipo_contrato"]=="SC+CLT+MENS")
		{
			//DADOS PJ
			$ra_opcao = "";
			$ra_bcdpfgt = "";
			$ra_hrsmes = "";
			$ra_hrsseman = "";
			$ra_codfunc = "";
			$ra_catfunc = "";
			$ra_tipoadm = "";
			$ra_viemrai = "";
			$ra_numcp = "";
			$ra_sercp = "";
			$ra_tnotrab = "";
			
			$a2_nome = maiusculas(tiraacentos($reg["empresa_func"]));
			$a2_end = maiusculas(tiraacentos($reg["empresa_end"]));
			$a2_mun	= maiusculas(tiraacentos($reg["empresa_cidade"]));
			$a2_tipo = "J";
		}
	
	}
	*/
	
	//$funcao = explode(" ",$reg[""]);
	
	
	$sql = "SELECT R_E_C_N_O_ FROM AE8010 ";
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);

	$recno = $regs["R_E_C_N_O_"] + 1;	
	
	//Insere o funcionario no banco microsiga
	$sql = "INSERT INTO AE8010 ";
	$sql .= "(AE8_FILIAL, AE8_RECURS, AE8_DESCRI, AE8_TIPO, AE8_UMAX, AE8_PRODUT, AE8_CALEND, AE8_TPREAL, AE8_EMAIL, ";
	$sql .= "AE8_CUSFIX, AE8_VALOR, AE8_PRDREA, AE8_ATIVO, AE8_CODFUN, AE8_EQUIP, R_E_C_N_O_, R_E_C_D_E_L_, AE8_ID, AE8_ID_CAR) ";
	//$sql .= "A2_MUN, A2_TIPO, RA_MAT, RA_NOME, RA_NATURAL, RA_NACIONA, RA_SEXO, RA_ESTCIVI, RA_NASC, ";
	//$sql .= "RA_CC, RA_ADMISSA, RA_OPCAO, RA_BCDPFGT, RA_CTDPFGT, RA_HRSMES, RA_HRSEMAN, RA_CODFUNC,  ";
	//$sql .= "RA_CATFUNC, RA_TIPOPGT, RA_TIPOADM, RA_VIEMRAI, RA_GRINRAI, RA_NUMCP, RA_SERCP, RA_ADTPOSE, RA_TNOTRAB, ID) ";
	$sql .= "VALUES ( ";
	$sql .= "'01', ";
	$sql .= "'FUN_".sprintf("%011d",$reg["id_funcionario"])."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["funcionario"]))."', ";
	$sql .= "'2', ";
	$sql .= "'100', ";
	$sql .= "'', ";
	$sql .= "'001', ";
	$sql .= "'4', ";
	$sql .= "'".$reg["email"]."', ";
	$sql .= "'', ";
	$sql .= "'', ";
	$sql .= "'', ";
	$sql .= "'".$status."', ";
	$sql .= "'', ";
	$sql .= "'".sprintf("%010d",$reg["id_setor"])."', ";
	$sql .= "'".$recno."', ";
	$sql .= "'0', ";
	$sql .= "'".$reg["id_funcionario"]."', ";
	$sql .= "'0') ";
	
	/*
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
	*/
	
	echo maiusculas(tiraacentos($reg["funcionario"])) . " - ".$recno."<br>";
	
	$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message());

	$i++;
}

echo $i;

$db->fecha_db();

$db->fecha_ms_db();

/*
//Insere o funcionario
$sql = "INSERT INTO DVM001 ";
$sql .= "(AE8_RECURS,R_E_C_N_O_) ";
$sql .= "VALUES (";
$sql .= "'TESTE',2) ";

$cont_requisicao = mssql_query($sql,$s) or die(mssql_get_last_message());

*/
?>
