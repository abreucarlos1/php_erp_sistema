<?php
/*
		Formulário de Gráfico do Consumo de Horas
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../coordenacao/grafico_protheus.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> Atualização Layout 2014
		Versão 3 --> atualização layout - Carlos Abreu - 24/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

include(INCLUDE_DIR."phplot/phplot.php");

$db = new banco_dados;

$semanas = NULL;

$horas_prev = NULL;

$horas_temp = 0;

$sql = "SELECT ordem_servico.os FROM ".DATABASE.".ordem_servico ";
$sql .= "WHERE ordem_servico.id_os = '".$_GET["id_os"]."' ";
$sql .= "AND ordem_servico.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$regsos = $db->array_select[0];

/*
//PEGA A ULTIMA REVISÃO DA FASE 01 (ORÇAMENTO)
$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 ";
$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$regsos["os"])."' ";
$sql .= "AND AFE010.AFE_FASE = '01' ";

$db->select($sql,'MSSQL', true);

$regs_ult_rev = $db->array_select[0];

//Obtem o total de horas do projeto(previsto)
$sql = "SELECT SUM(AFA_QUANT) AS HorasPrev FROM AFA010, AF8010 ";
$sql .= "WHERE AFA_PROJET = AF8_PROJET ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_PROJET = '" . sprintf("%010d",$regsos["os"]) . "' ";
$sql .= "AND AFA_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
$sql .= "GROUP BY AFA_PROJET ";

$db->select($sql,'MSSQL', true);

$regs = $db->array_select[0];

//Obtem a data de inicio e fim do projeto (previsto) 
$sql = "SELECT MIN(AF9_START) AS START, MAX(AF9_FINISH) AS FINISH FROM AF9010, AF8010 ";
$sql .= "WHERE AF9_PROJET = AF8_PROJET ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_PROJET = '" . sprintf("%010d",$regsos["os"]) . "' ";
$sql .= "AND AF9_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
$sql .= "GROUP BY AF9_PROJET ";

$db->select($sql,'MSSQL', true);

$dataprojet = $db->array_select[0];

//Obtem a data do 1ª e ultimo apontamento confirmado
$sql = "SELECT MIN(AFU_DATA) AS DATAINI, MAX(AFU_DATA) AS DATAFIM, SUM(AFU_HQUANT) AS HorasApont FROM AFU010, AF8010 ";
$sql .= "WHERE AFU_PROJET = AF8_PROJET ";
$sql .= "AND AFU_REVISA = AF8_REVISA ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFU010.D_E_L_E_T_ = '' ";
$sql .= "AND AFU010.AFU_CTRRVS = '1' ";
$sql .= "AND AF8_PROJET = '" . sprintf("%010d",$regsos["os"]) . "' ";
$sql .= "GROUP BY AFU_PROJET ";

$db->select($sql,'MSSQL',true);

$dataapont = $db->array_select[0];

//pega a menor data entre o previsto e o realizado
if($dataprojet["START"]<=$dataapont["DATAINI"])
{
	$start = $dataprojet["START"];
}
else
{
	$start = $dataapont["DATAINI"];
}

//pega a maior data entre o previsto e o realizado
if($dataprojet["FINISH"]>=$dataapont["DATAFIM"])
{
	$finish = $dataprojet["FINISH"];
}
else
{
	$finish = $dataapont["DATAFIM"];
}
*/


$semanas = montasemana(mysql_php(protheus_mysql($start)),mysql_php(protheus_mysql($finish)),0);

//percorre o array de datas, a fim de somar as horas do periodo contido no array
for($i=0;$i<=count($semanas);$i++)
{
	//data_per[0] --> data inicio
	//data_per[1] --> data fim
	$data_per = explode("#",$semanas[$i]);
	
	//SOMA AS HORAS NO PERIODO (previsto)
	/*
	$sql = "SELECT SUM(AFA_QUANT) AS HorasPeriodo FROM AF8010, AFA010 ";
	$sql .= "WHERE AF8_PROJET = AFA_PROJET ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = '" . sprintf("%010d",$regsos["os"]) . "' ";
	$sql .= "AND AFA_FINISH <= '" . mysql_protheus(php_mysql($data_per[1])) . "' ";
	$sql .= "AND AFA_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
	$sql .= "GROUP BY AFA_PROJET ";
	
	$db->select($sql,'MSSQL', true);
	
	$reg_horas_prev = $db->array_select[0];
	
	$horas_temp = $reg_horas_prev["HorasPeriodo"];	
	
	//SOMA AS HORAS NO PERIODO (realizado)
	$sql = "SELECT SUM(AFU_HQUANT) AS HorasApont FROM AF8010, AFU010 ";
	$sql .= "WHERE AF8_PROJET = AFU_PROJET ";
	$sql .= "AND AF8_REVISA = AFU_REVISA ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFU010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFU010.AFU_CTRRVS = '1' ";
	$sql .= "AND AF8_PROJET = '" . sprintf("%010d",$regsos["os"]) . "' ";
	$sql .= "AND AFU_DATA <= '" . mysql_protheus(php_mysql($data_per[1])) . "' ";
	$sql .= "GROUP BY AFU_PROJET ";
	
	$db->select($sql,'MSSQL', true);
	
	$reg_horas_apont = $db->array_select[0];
	
	$horas_temp_apont = $reg_horas_apont["HorasApont"];
	
	if($dataapont["DATAFIM"]>=mysql_protheus(php_mysql($data_per[1])))
	{
		$horas_prev[] = array($data_per[1],$horas_temp,$horas_temp_apont);
	}
	else
	{
		$horas_prev[] = array($data_per[1],$horas_temp);
	}
	*/	
}

$graph = new PHPlot(1000,600,'teste_jpg');

$graph->SetFileFormat('jpg');

$graph->SetDataType("text-data");  // Must be first thing

$graph->SetDataValues($horas_prev);

$relacao = 0;

if($regs["HorasPrev"]>0)
{
	$relacao = ($dataapont["HorasApont"]/$regs["HorasPrev"])*100;
}

$graph->SetTitle("Informações da OS:". sprintf("%05d",$regsos["os"])
. "\n\r Data prevista: " . mysql_php(protheus_mysql($dataprojet["START"])). " a " . mysql_php(protheus_mysql($dataprojet["FINISH"]))
. "\n\r Data realizada: " . mysql_php(protheus_mysql($dataapont["DATAINI"])). " a " . mysql_php(protheus_mysql($dataapont["DATAFIM"]))
. "\n\r Horas Contr.: " . number_format($regs["HorasPrev"],2,',','.'). " Horas Consumidas.: " . number_format($dataapont["HorasApont"],2,',','.'). " Saldo : " . number_format(($regs["HorasPrev"] - $dataapont["HorasApont"]),2,',','.')
. "\n\r Relação Realizada x Prevista: " . number_format($relacao,2,',','.') . " %"
);

//$graph->SetTitle("Consumo de Horas da OS");
$graph->SetXLabelAngle(90);

$graph->SetXTitle("Datas","plotdown");
$graph->SetYTitle("Horas","plotleft");

$graph->SetXDataLabelPos("plotdown");

$graph->SetYTickIncrement(0);
$graph->SetXTickIncrement(0.5);

$graph->SetXAxisPosition(0);
$graph->SetYAxisPosition(0);

$graph->SetDrawXGrid(FALSE);
$graph->SetDrawYGrid(TRUE);

$legend1 = array('Previsto', 'Realizado');

$graph->SetLegend($legend1);

$graph->SetLegendPixels(50, 30);

$graph->SetPlotType("linepoints");

$graph->DrawGraph();

?>

