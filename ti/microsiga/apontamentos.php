<?php
// Dados da conexão com banco de dados

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

//error_reporting(E_ERROR);

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$db->db_ms = 'DADOSOFI';
$db->conexao_ms_db();

/*

$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS THN, TIME_TO_SEC(hora_adicional) AS THA FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".atividades ";
$sql .= "WHERE apontamento_horas.data >= '2011-05-26' ";
$sql .= "AND apontamento_horas.data <= '2011-06-25' ";
$sql .= "AND apontamento_horas.id_os = OS.id_os ";
$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
$sql .= "AND apontamento_horas.id_apontamento_horas NOT IN ('".$dvm002_id."') ";
$sql .= "ORDER BY data ASC, OS, apontamento_horas.id_atividade ";

$reg = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção. ERRO: " . mysql_error($db->conexao));	

$inclusao = '';
$erro_tarefas = '';
$erro_projetos = '';
$tar = 0;
$err = 0;
$inc = 0;

while($cont = mysql_fetch_array($reg))
{
	$projeto = sprintf("%010d",$cont["os"]); //PROJETOS
	
	$sql = "SELECT ID FROM DVM002 ";
	$sql .= "WHERE ID = '".$cont["id_apontamento_horas"]."' ";
	
	$regis = mssql_query($sql,$db->conexao_ms) or die("Não foi possível a seleção dos dados".$sql);

	if(mssql_num_rows($regis)<=0)
	{
		//OBTEM O PROJETO
		$sql = "SELECT TOP 1 AF8_PROJET, AF8_REVISA FROM AF8010 ";
		$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8010.AF8_PROJET = '". $projeto. "' ";
		$sql .= "ORDER BY AF8010.AF8_REVISA DESC ";	
		
		$regis0 = mssql_query($sql,$db->conexao_ms) or die("Não foi possível a seleção dos dados".$sql);
	
		$regs0 = mssql_fetch_array($regis0);	
	
		$sql = "SELECT TOP 1 R_E_C_N_O_ FROM DVM002 ";
		
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";
		
		$regis = mssql_query($sql,$db->conexao_ms) or die("Não foi possível a seleção dos dados".$sql);
	
		$regs = mssql_fetch_array($regis);
		
		$recno = $regs["R_E_C_N_O_"] + 1;		
		
		$segundos_hn = $cont["THN"]; 
		
		$segundos_ha = $cont["THA"];
		
		$h_quant = $segundos_hn+$segundos_ha;
	
		$h_quant /= 3600;
	
		$hi = explode(":",$cont["hora_inicial"]);
		
		$hf = explode(":",$cont["hora_final"]);
		
		$isql = "INSERT INTO DVM002 ";
		$isql .= "(AFU_PROJET, AFU_VERSAO, AFU_TAREFA, AFU_RECURS, AFU_DATA, AFU_HORAI, AFU_HORAF, AFU_OBS, AFU_CTRRVS, AFU_CUSTO1, AFU_TPREAL, AFU_HQUANT, AFU_ADIC, ID, OPERACAO, D_E_L_E_T_, R_E_C_N_O_) ";
		$isql .= "VALUES ('" . $regs0["AF8_PROJET"] . "', ";
		$isql .= "'" . $regs0["AF8_REVISA"] . "', ";
		$isql .= "'" . $cont["tarefa"] . "', ";
		$isql .= "'FUN_".sprintf("%011d",$cont["id_funcionario"])."', ";
		$isql .= "'" . str_replace("-","",$cont["data"]) . "', ";
		$isql .= "'" . sprintf("%02d",$hi[0]) .":".sprintf("%02d",$hi[1]). "', ";
		$isql .= "'" . sprintf("%02d",$hf[0]) .":".sprintf("%02d",$hf[1]) . "', ";
		$isql .= "'" . strip_tags($cont["complemento"]) . "', ";
		$isql .= "1, ";
		$isql .= "1, ";
		$isql .= "1, ";
		$isql .= "" . $h_quant . ", ";
		$isql .= "" . 0 . ", ";
		$isql .= "'".$cont["id_apontamento_horas"]."', ";
		$isql .= "'I', "; //OPERAÇÃO I- INCLUSAO / E = EXCLUSAO / A - ALTERAÇÃO
		$isql .= "'', ";
		$isql .= "'".$recno."') ";
		//Carrega os registros
		$registros = mssql_query($isql,$db->conexao_ms) or die("Não foi possível a inserção dos dados".$isql.mssql_get_last_message());
	
		$inc++;
		
		$inclusao .= $isql . "TOTAL Hr: ".$h_quant. " Total regs: ".$num_regs."<br>";
		
	}	

}

echo "<b>Inclusão ".$inc."</b><BR>";
echo $inclusao."<BR>";

echo "<b>ERROS</b><BR>";
echo "<H5><b>PROJETOS ".$err."</b></H5><BR>";
echo $erro_projetos."<BR>";
echo "<H5><B>TAREFAS ".$tar."</B></H5><BR>";
echo $erro_tarefas."<BR>";

*/

//RECURSOS
$sql = "SELECT * FROM DVM001 ORDER BY R_E_C_N_O_ ";

$cont_ms = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);

while($cont = mssql_fetch_array($cont_ms))
{
/*
		$sql .= "(AE8_RECURS, AE8_DESCRI, AE8_TIPO, AE8_UMAX, AE8_PRODUT, AE8_CALEND, AE8_TPREAL, AE8_EMAIL, ";
		$sql .= "AE8_CUSFIX, AE8_CUSMEN, AE8_VALOR, AE8_PRDREA, AE8_ATIVO1, AE8_CODFUN, AE8_EQUIP, AE8_FORNEC, AE8_XFUNC, AE8_FUNCAO, AE8_MCONTR, ";
		$sql .= "RA_MAT, RA_NOME, RA_NATURAL, RA_NACIONA, RA_SEXO, RA_ESTCIVI, RA_NASC, ";
		$sql .= "RA_CC, RA_ADMISSA, RA_OPCAO, RA_BCDPFGT, RA_CTDPFGT, RA_HRSMES, RA_HRSEMAN, RA_CODFUNC,  ";
		$sql .= "RA_CATFUNC, RA_TIPOPGT, RA_TIPOADM, RA_VIEMRAI, RA_GRINRAI, RA_NUMCP, RA_SERCP, RA_ADTPOSE, RA_TNOTRAB, RA_SITE, ID) ";

*/
	$sql = "SELECT TOP 1 R_E_C_N_O_ FROM PA8010 ";
	
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$regis = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

	$regs = mssql_fetch_array($regis);
	
	$recno_pa8 = $regs["R_E_C_N_O_"] + 1;

	//Insere o funcionario no banco microsiga
	$sql = "INSERT INTO PA8010 ";
	$sql .= "(PA8_RECURS, PA8_DESCRI, PA8_TIPO, PA8_UMAX, PA8_CALEND, PA8_TPREAL, PA8_EMAIL, ";
	$sql .= "PA8_CUSFIX, PA8_CUSMEN, PA8_VALOR, PA8_PRDREA, PA8_ATIVO, PA8_CODFUN, PA8_EQUIP, PA8_FORNEC, PA8_XFUNC, PA8_FUNCAO, PA8_MCONTR, ";
	$sql .= "PA8_MAT, PA8_NATURA, PA8_NACION, PA8_SEXO, PA8_ESTCIV, PA8_NASC, ";
	$sql .= "PA8_CC, PA8_ADMISS, PA8_OPCAO, PA8_HRSMES, PA8_HRSEMA, ";
	$sql .= "PA8_CATFUN, PA8_TIPOPG, PA8_TIPOAD, PA8_VIEMRA, PA8_GRINRA, PA8_NUMCP, PA8_SERCP, PA8_ADTPOS, PA8_TNOTTR, PA8_SITE, PA8_FLAG, PA8_ID_DVM, R_E_C_N_O_) ";
	$sql .= "VALUES ( ";
	$sql .= "'".$cont["AE8_RECURS"]."', "; 										//RECURSO
	$sql .= "'".$cont["AE8_DESCRI"]."', ";				//DESCRICAO
	$sql .= "'".$cont["AE8_TIPO"]."', "; 																	//TIPO RECURSO - TRABALHO		
	$sql .= "'".$cont["AE8_UMAX"]."', ";																	//UNIDADE MAX.		100%
	$sql .= "'".$cont["AE8_CALEND"]."', ";																	//CALENDARIO
	$sql .= "'".$cont["AE8_TPREAL"]."', ";															//TIPO APURAÇÃO - 4 - NAO CALCULA
	$sql .= "'".$cont["AE8_EMAIL"]."', ";									//E-MAIL
	$sql .= "'".$cont["AE8_CUSFIX"]."', ";														//CUSTO FIXO
	$sql .= "'".$cont["AE8_CUSMEN"]."', ";														//CUSTO MENSAL
	$sql .= "'".$cont["AE8_VALOR"]."', ";														//VALOR UNITARIO
	$sql .= "'".$cont["AE8_PRDREA"]."', ";										//CODIGO PRODUTO
	$sql .= "'".$cont["AE8_ATIVO1"]."', ";															//STATUS: 1- ATIVO / 2 - INATIVO
	$sql .= "'".$cont["AE8_CODFUN"]."', ";														//CODIGO FUNCIONARIO -- ?
	$sql .= "'".$cont["AE8_EQUIP"]."', ";							//EQUIPE
	$sql .= "'".$cont["AE8_FORNEC"]."', ";														//FORNECEDOR
	$sql .= "'".$cont["AE8_XFUNC"]."', ";						//FUNÇÃO
	$sql .= "'".$cont["AE8_FUNCAO"]."', ";										//COD FUNÇÃO													
	$sql .= "'".$cont["AE8_MCONTR"]."', ";													//TIPO CONTRATO
	$sql .= "'".$cont["RA_MAT"]."', ";														//MATRICULA
	$sql .= "'".$cont["RA_NATURAL"]."', ";								//ESTADO
	$sql .= "'".$cont["RA_NACIONA"]."', ";									//NACIONALIDADE
	$sql .= "'".$cont["RA_SEXO"]."', ";												//SEXO
	$sql .= "'".$cont["RA_ESTCIVI"]."', ";									//ESTADO CIVIL
	$sql .= "'".$cont["RA_NASC"]."', "; 				//DATA DE NASCIMENTO
	$sql .= "'".$cont["RA_CC"]."', ";									//CENTRO DE CUSTO
	$sql .= "'".$cont["RA_ADMISSA"]."', ";									//ADMISSÃO
	$sql .= "'".$cont["RA_OPCAO"]."', ";														//valor_fgts DATA
	$sql .= "'".$cont["RA_HRSMES"]."', ";														//HORAS MES
	$sql .= "'".$cont["RA_HRSEMAN"]."', ";														//HORAS SEMANA
	$sql .= "'".$cont["RA_CATFUNC"]."', ";														//CATEGORIA FUNCIONAL
	$sql .= "'".$cont["RA_TIPOPGT"]."', ";									//TIPO PAGAMENTO
	$sql .= "'".$cont["RA_TIPOADM"]."', ";														//TIPO ADMISSÃO
	$sql .= "'".$cont["RA_VIEMRAI"]."', ";														//VINCULO EMPREGATICIO RAIS
	$sql .= "'".$cont["RA_GRINRAI"]."', ";									//GRAU DE INSTRUÇÃO
	$sql .= "'".$cont["RA_NUMCP"]."', ";														//Nº CARTEIRA TRABALHO
	$sql .= "'".$cont["RA_SERCP"]."', ";														//SERIE CARTEIRA TRABALHO
	$sql .= "'".$cont["RA_ADTPOSE"]."', ";																//ADICIONAL TEMPO SERVIÇO
	$sql .= "'".$cont["RA_TNOTRAB"]."', ";
	$sql .= "'".$cont["RA_SITE"]."', ";															//SITE
	$sql .= "'".$cont["FLAG"]."', ";																		//FLAG													
	$sql .= "'".$cont["ID"]."', ";
	$sql .= "'".$recno_pa8."') ";																//ID_DVM										//
					
	//$cont_ms1 = mssql_query($sql,$db->conexao_ms) or die(mssql_get_last_message().$sql);

}

//APONTAMENTOS
//$sql1 = "SELECT * FROM DVM002 WHERE ID BETWEEN 157000 AND 167000 ORDER BY DVM002.ID ";
//$sql1 = "SELECT * FROM DVM002 WHERE ID BETWEEN 167001 AND 177000 ORDER BY DVM002.ID ";
//$sql1 = "SELECT * FROM DVM002 WHERE ID BETWEEN 177001 AND 187000 ORDER BY DVM002.ID ";
//$sql1 = "SELECT * FROM DVM002 WHERE ID BETWEEN 187001 AND 197000 ORDER BY DVM002.ID ";
//$sql1 = "SELECT * FROM DVM002 WHERE ID BETWEEN 197001 AND 207000 ORDER BY DVM002.ID ";

$cont_ms = mssql_query($sql1,$db->conexao_ms) or die(mssql_get_last_message().$sql);

echo "Nº regs ".mssql_num_rows($cont_ms)."<br>";

$I = 1;

while($cont = mssql_fetch_array($cont_ms))
{
	//$isql .= "(AFU_PROJET, AFU_VERSAO, AFU_TAREFA, AFU_RECURS, AFU_DATA, AFU_HORAI, AFU_HORAF, AFU_OBS, AFU_CTRRVS, AFU_CUSTO1, AFU_TPREAL, AFU_HQUANT, AFU_ADIC, ID, OPERACAO, D_E_L_E_T_, R_E_C_N_O_) ";
	
	$sql = "SELECT TOP 1 R_E_C_N_O_ FROM PA9010 ";	
	
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$regis = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

	$regs = mssql_fetch_array($regis);
	
	$recno_pa9 = $regs["R_E_C_N_O_"] + 1;
	
	$isql = "INSERT INTO PA9010 ";
	$isql .= "(PA9_PROJET, PA9_REVISA, PA9_TAREFA, PA9_RECURS, PA9_DATA, PA9_HORAI, PA9_HORAF, PA9_OBS, PA9_CTRRVS, PA9_TPREAL, PA9_HQUANT, PA9_ID_DVM, PA9_OPERAC, PA9_FLAG, R_E_C_N_O_) ";
	$isql .= "VALUES ('" . $cont["AFU_PROJET"] . "', ";
	$isql .= "'" . $cont["AFU_VERSAO"] . "', ";
	$isql .= "'" . $cont["AFU_TAREFA"] . "', ";
	$isql .= "'" . $cont["AFU_RECURS"]."', ";
	$isql .= "'" . $cont["AFU_DATA"] . "', ";
	$isql .= "'" . $cont["AFU_HORAI"]. "', ";
	$isql .= "'" . $cont["AFU_HORAF"] . "', ";
	$isql .= "'" . $cont["AFU_OBS"] . "', ";
	$isql .= "'" . $cont["AFU_CTRRVS"] . "', ";
	$isql .= "'" . $cont["AFU_TPREAL"] . "', ";
	$isql .= "'" . $cont["AFU_HQUANT"] . "', ";
	$isql .= "'".$cont["ID"]."', ";
	$isql .= "'".$cont["OPERACAO"]."', "; //OPERAÇÃO I- INCLUSAO / E = EXCLUSAO / A - ALTERAÇÃO
	$isql .= "'".$cont["FLAG"]."', ";
	$isql .= "'".$recno_pa9."') ";
	
	//$cont_ms1 = mssql_query($isql,$db->conexao_ms) or die(mssql_get_last_message().$isql);
	
	echo $cont["ID"] . " ------- ".$I." -> ".$isql."<br>";
	
	$I++;
	
}



$db->fecha_ms_db();


?>
