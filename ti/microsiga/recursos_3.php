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

$sql = "SELECT * FROM ".DATABASE.".Bancos ";

$cont = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel selecionar os dados.".$sql);

while($reg = mysql_fetch_array($cont))
{
	$bancos[$reg["id_banco"]] = $reg["dv"];
} 

$sql = "SELECT *, Cargos.descricao AS descricao, Funcionarios.id_funcionario AS id_funcionario FROM ".DATABASE.".setores, ".DATABASE.".Cargos, ".DATABASE.".Funcionarios ";
$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (Funcionarios.id_empfunc = empresa_funcionarios.id_empfunc) ";
//$sql .= "LEFT JOIN ".DATABASE.".salarios ON (Funcionarios.id_salario = salarios.id_salario) ";
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
	$cust_fix = 0;
	$cust_men = 0;
	
	if($reg["situacao"]=="DESLIGADO")
	{
		$status = 2;
	}
	else
	{
		$status = 1;
	}
	
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '".$reg["id_funcionario"]."' ";
	$sql .= "ORDER BY data DESC LIMIT 1";
	
	$cont1 = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel selecionar os dados.".$sql);
	
	$reg1 = mysql_fetch_array($cont1);
	
	if($reg1[" tipo_contrato"]=="EST" || $reg1[" tipo_contrato"]=="CLT")
	{
		//FOLHA
		$tp_real = 3;		
	}
	else
	{
		if($reg1[" tipo_contrato"]=="SC")
		{
			$tp_real = 1; //FIFO
			$cust_fix = $reg1["salario_hora"];
		}
		else
		{
		
			if($reg1[" tipo_contrato"]=="SC+MENS")
			{
				$tp_real = 5; //MENSAL
				$cust_men = $reg1["salario_mensalista"];
			}
			else
			{
				if($reg1[" tipo_contrato"]=="SC+CLT+MENS")
				{
					$tp_real = 1;//fifo
					$cust_fix = $reg1["salario_mensalista"]-$reg1["salario_clt"];				
				}
				else
				{
					if($reg1[" tipo_contrato"]=="SC+CLT")
					{
						$tp_real = 1;//fifo
						$cust_fix = ($reg1["salario_hora"]*176)-$reg1["salario_clt"];
					}
				
				}
			}
		}
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
	
	$sql = "SELECT * FROM ".DATABASE.".cargos_grupos ";
	$sql .= "WHERE cargos_grupos.id_cargo_grupo =  '".$reg["id_cargo_grupo"]."' ";
	$cont1 = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel selecionar os dados.".$sql);
	
	$reg1 = mysql_fetch_array($cont1);
	
	
	$texto = explode(" ",$reg1["grupo"]);
	
	switch ($texto[0]) 
	{
		case 'ENGENHEIRO':
			//$valor = '50.00';
			$espec = '01';
		break;
		
		case 'SUPERVISOR':
			//$valor = '40.00';
			$espec = '01';
		break;
		
		case 'COORDENADOR':
			//$valor = '30.00';
			$espec = '01';
		break;
		
		case 'PROJETISTA':
			//$valor = '20.00';
			$espec = '02';
		break;
		
		case 'DESENHISTA':
			//$valor = '10.00';
			$espec = '03';
		break;
		
		default: $valor = 0;
			     $espec = '';				
	}
	
	
	//$funcao = explode(" ",$reg[""]);
	
	$empresa = '';
	
	if($reg["empresa_cnpj"]!='')
	{
	
		$char = array(".","/","-" );
		
		$sql = "SELECT * FROM SA2010 ";
		$sql .= "WHERE A2_CGC = '".str_replace($char,"",$reg["empresa_cnpj"])."' ";
		$sql .= "AND D_E_L_E_T_ <> '*' ";
		
		$con1 = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
		
		$regs1 = mssql_fetch_array($con1);
		
		$empresa = $regs1["A2_COD"];
	}
	
	
	$sql = "SELECT R_E_C_N_O_ FROM AE8010 ";
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$regs = mssql_fetch_array($con);

	$recno = $regs["R_E_C_N_O_"] + 1;	
	
	//Insere o funcionario no banco microsiga
	$sql = "INSERT INTO AE8010 ";
	$sql .= "(AE8_RECURS, AE8_DESCRI, AE8_TIPO, AE8_UMAX, AE8_PRODUT, AE8_CALEND, AE8_TPREAL, AE8_EMAIL, ";
	$sql .= "AE8_ESPEC, AE8_CUSFIX, AE8_CUSMEN, AE8_FORNEC, AE8_VALOR, AE8_PRDREA, AE8_ATIVO, AE8_CODFUN, AE8_EQUIP, AE8_XFUNC, R_E_C_N_O_, R_E_C_D_E_L_, AE8_ID, AE8_ID_CAR) ";
	//$sql .= "A2_MUN, A2_TIPO, RA_MAT, RA_NOME, RA_NATURAL, RA_NACIONA, RA_SEXO, RA_ESTCIVI, RA_NASC, ";
	//$sql .= "RA_CC, RA_ADMISSA, RA_OPCAO, RA_BCDPFGT, RA_CTDPFGT, RA_HRSMES, RA_HRSEMAN, RA_CODFUNC,  ";
	//$sql .= "RA_CATFUNC, RA_TIPOPGT, RA_TIPOADM, RA_VIEMRAI, RA_GRINRAI, RA_NUMCP, RA_SERCP, RA_ADTPOSE, RA_TNOTRAB, ID) ";
	$sql .= "VALUES ( ";
	$sql .= "'FUN_".sprintf("%011d",$reg["id_funcionario"])."', ";
	$sql .= "'".maiusculas(tiraacentos($reg["funcionario"]))."', ";
	$sql .= "'2', ";
	$sql .= "'100', ";
	$sql .= "'".$reg["id_produto"]."', ";
	$sql .= "'001', ";
	$sql .= "'".$tp_real."', ";
	$sql .= "'".$reg["email"]."', ";
	$sql .= "'".$espec."', ";
	$sql .= "'".$cust_fix."', ";
	$sql .= "'".$cust_men."', ";
	$sql .= "'".$empresa."', ";
	$sql .= "'".$valor."', ";
	$sql .= "'".$reg["id_produto"]."', ";
	$sql .= "'".$status."', ";
	$sql .= "'".$reg["clt_matricula"]."', ";
	$sql .= "'".sprintf("%010d",$reg["id_setor"])."', ";
	$sql .= "'".$reg["descricao"]."', ";
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
	
	echo maiusculas(tiraacentos($reg["funcionario"])) . " - ".$texto[0]." - ".$valor." - ".$empresa."<br>";
	
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
