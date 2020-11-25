<?php
/*
	Relat�rio de Medicao
	Criado por Carlos Abreu  
	
	Vers�o 0 --> VERS�O INICIAL : 21/02/2018

*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '2014M');
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2010 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2010 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.4, 2010-08-26
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));


//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(619))
{
	nao_permitido();
}

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$chars = array("'","\"",")","(","\\","/",".",":","&","%","�","`","'","?");

$mes = $_POST["mes"];

$ano = $_POST["ano"];

$array_meses = array("JANEIRO","FEVEREIRO","MAR�O","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

if (intval($mes)==1)
{
	$mes = 12;
	$ano = $ano - 1;
	$data_ini = "26/" . $mes . "/" . $ano;
}
else
{ 
	$mesant = $mes - 1;
	$data_ini = "26/" . $mesant . "/" . $ano;
}

$temp = explode("/",$data_ini);

$d = $temp[0]; //26
$m = $temp[1]; //02 //mar�o
$a = $temp[2]; //2006

$diasestampa = mktime(0,0,0,$m+1,0,$ano);

$diasarray = getdate($diasestampa);

$diasdomes = $diasarray["mday"];

// loop de dias
for($i=1;$i<=$diasdomes;$i++)
{	
	if($d==$diasdomes+1)
	{
		$d = 1;
		
		$m++;
		
		if($m==13)
		{
			$m=1;
			$a++;
		}
	}

	$data[$i]=$a."-". sprintf('%02d',$m) ."-".sprintf('%02d',$d);
	
	$d++;
}
// loop de dias

$data_ini = $data[1];

$data_fim = $data[count($data)];

//SELECIONA O COORDENADOR
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL',true);

foreach ($db->array_select as $regs1)
{
	$array_coordenadores[$regs1["PA7_ID"]] = str_replace($chars,"",$regs1["PA7_NOME"]);	
}

//filtra as OS com pedidos e itens, excluindo as excess�es
$sql = "SELECT bms_pedido.id_os, OS.id_os, OS.id_cod_coord FROM ".DATABASE.".bms_pedido, ".DATABASE.".bms_item, ".DATABASE.".OS "; 
$sql .= "WHERE bms_pedido.reg_del = 0 "; 
$sql .= "AND bms_item.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND bms_pedido.id_os = os.os ";
$sql .= "AND OS.id_os_status IN (1,2,3,7,14,15,16,17,18,19) ";
$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
$sql .= "AND (bms_pedido.data_pedido >= '2017-07-01' ";
$sql .= "OR bms_pedido.id_os IN (SELECT bms_excecoes.os FROM ".DATABASE.".bms_excecoes WHERE bms_excecoes.reg_del = 0)) ";
$sql .= "GROUP BY bms_pedido.id_os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs2)
{
	$array_os_pedidos[sprintf("%010d",$regs2["os"])] = sprintf("%010d",$regs2["os"]);
	
	//$array_medicao['id_os'][sprintf("%010d",$regs2["os"])] = $regs2["id_os"];
	
	//$array_medicao['nome_coordenador'][$regs2["id_os"]] = $array_coordenadores[sprintf("%04d",$regs2["id_cod_coord"])];
}

$array_os = implode(",",$array_os_pedidos);

/*
$array_os_medicao = implode(",",$array_os_bms);

//Obtem o total por OS
$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".bms_item, ".DATABASE.".bms_medicao, ".DATABASE.".bms_pedido ";
$sql .= "WHERE bms_item.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND bms_medicao.reg_del = 0 ";
$sql .= "AND bms_pedido.reg_del = 0 ";
$sql .= "AND bms_pedido.id_os = os.os ";
$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
$sql .= "AND bms_pedido.id_os IN (".$array_os_medicao.") ";
$sql .= "AND bms_medicao.id_bms_controle IN (1,2,3,5) "; //planejada, medido, faturado, bms gerado

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
else
{
	foreach($db->array_select as $regs)
	{
		$array_medicao['valor_medido_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
	
		$array_medicao['valor_planejado_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_planejado"];
	}
}
*/

//obtem a hh, qtd de mo na OS
$sql = "SELECT SUM( TIME_TO_SEC(hora_normal) + TIME_TO_SEC(hora_adicional) + TIME_TO_SEC(hora_adicional_noturna)) AS SEGUNDOS, funcionarios.id_funcionario, apontamento_horas.id_os, os.os, apontamento_horas.data FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".funcionarios ";
$sql .= "WHERE apontamento_horas.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND (TIME_TO_SEC(hora_normal) + TIME_TO_SEC(hora_adicional) + TIME_TO_SEC(hora_adicional_noturna)) > 0 ";
$sql .= "AND apontamento_horas.id_os = OS.id_os ";
$sql .= "AND OS > 3000 ";
$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND DATE_FORMAT(data, '%Y-%m-%d') BETWEEN '".$data_ini."' AND '".$data_fim."' ";
$sql .= "GROUP BY funcionarios.id_funcionario, apontamento_horas.id_os ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
else
{
	foreach($db->array_select as $regs)
	{
		$array_medicao['id_os'][sprintf("%010d",$regs["os"])] = $regs["id_os"];
			
		$array_medicao['num_func'][$regs["id_os"]] += 1;
		
		$array_medicao['hh_os'][$regs["id_os"]] += $regs["SEGUNDOS"];
		
		$horas_calc = 0;
		
		$horas_calc = $regs["SEGUNDOS"]/3600;
		
		//Obtem o valor do salario na data
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $regs["id_funcionario"] . "' ";
		$sql .= "AND DATE_FORMAT(data , '%Y-%m-%d' ) <= '".$regs["data"]."' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
				
		$regs4 = $db->array_select[0];
		
  
		switch ($regs4[" tipo_contrato"])
		{
			case 'SC':
			case 'SC+CLT':
			
				$array_medicao['custo'][$regs["id_os"]] += round($regs4["salario_hora"]*$horas_calc,2);
				
			break;
			
			case 'CLT':
			case 'EST':
			
				$array_medicao['custo'][$regs["id_os"]] += round((($regs4["salario_clt"]/176)*1.84*$horas_calc),2);
				
			break;
			
			case 'SC+MENS':
			case 'SC+CLT+MENS':
			
  				$array_medicao['custo'][$regs["id_os"]] += round((($regs4["salario_mensalista"]/176)*$horas_calc),2);
				
			break;
	   }				
	}
}

//Seleciona os Projetos
$sql = "SELECT AF1_ORCAME, AF1_DESCRI, A1_NOME, AF8_COORD1 FROM AF8010 WITH (NOLOCK), SA1010 WITH (NOLOCK), AEA010 WITH (NOLOCK), AJK010 WITH (NOLOCK), AF1010 WITH (NOLOCK) ";
$sql .= "LEFT JOIN AF5010 WITH (NOLOCK) ON (AF5_ORCAME = AF1_ORCAME AND AF5010.D_E_L_E_T_ = '' AND AF5_NIVEL = '001') ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AEA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AJK010.D_E_L_E_T_ = '' ";
$sql .= "AND AJK_HQUANT > 0 ";
$sql .= "AND AJK_CTRRVS = '1' ";
$sql .= "AND AJK_PROJET = AF8_PROJET ";
$sql .= "AND AJK_REVISA = AF8_REVISA ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_CLIENT = A1_COD ";
$sql .= "AND AF1_LOJA = A1_LOJA ";
$sql .= "AND AF8_FASE = AEA_COD ";
$sql .= "AND AF1_TPORC = 2 ";
$sql .= "AND AF8_FASE <> 09 ";
$sql .= "AND AF1_ORCAME > '0000003000' ";
$sql .= "AND AJK_DATA BETWEEN '".mysql_protheus($data_ini)."' AND '".mysql_protheus($data_fim)."' ";

/*
if(substr($data_ini,5,2)<substr($data_fim,5,2))
{
	$sql .= "AND MONTH(AFU_DATA) BETWEEN ".substr($data_ini,5,2)." AND ".substr($data_fim,5,2)."
				AND 
			 YEAR(AFU_DATA) BETWEEN ".substr($data_ini,0,4)." AND ".substr($data_fim,0,4)." ";	
}
else
{
	$sql .= "AND MONTH(AFU_DATA) BETWEEN ".substr($data_fim,5,2)." AND ".substr($data_ini,5,2)."
				AND 
			 YEAR(AFU_DATA) BETWEEN ".substr($data_ini,0,4)." AND ".substr($data_fim,0,4)." ";
}
*/

$sql .= "GROUP BY AF1_ORCAME, AF1_DESCRI, A1_NOME, AF8_COORD1 ";		
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_proj = $db->array_select;

foreach($array_proj as $regs0)
{	
	$os = intval($regs0["AF1_ORCAME"]);
	
	//Obtem os periodos e os valores (medido/planejado/%)
	$sql = "SELECT * FROM ".DATABASE.".bms_item, ".DATABASE.".bms_medicao, ".DATABASE.".bms_pedido ";
	$sql .= "WHERE bms_item.reg_del = 0 ";
	$sql .= "AND bms_medicao.reg_del = 0 ";
	$sql .= "AND bms_pedido.reg_del = 0 ";
	$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
	$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
	$sql .= "AND bms_pedido.id_os = '".$os."' ";
	$sql .= "AND DATE_FORMAT(data, '%Y-%m') BETWEEN '".$data_ini."' AND '".$data_fim."' ";
	$sql .= "AND bms_medicao.id_bms_controle IN (1,2,3,5) "; //planejada, medido, faturado, bms gerado
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{		
			$array_medicao['valor_medido'][$regs0["AF1_ORCAME"]] += $regs["valor_medido"];			
		}			
	}
	
	$array_medicao['projeto'][$regs0["AF1_ORCAME"]] = str_replace($chars,"",trim($regs0["AF1_DESCRI"]));
	
	$array_medicao['cliente'][$regs0["AF1_ORCAME"]] = str_replace($chars,"",trim($regs0["A1_NOME"]));
	
	$array_medicao['coordenador'][$regs0["AF1_ORCAME"]] = str_replace($chars,"",$array_coordenadores[$regs0["AF8_COORD1"]]);
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/medicao_os_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="planilha_os_medicao_'.date('Ymd-His').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;

$linha = 2;

foreach($array_medicao['projeto'] as $projeto=>$descricao)
{
	/*
	if (trim($descricao) == '')
	{
		continue;
	}
	*/
	
	$time = sec_to_time($array_medicao['hh_os'][$array_medicao['id_os'][$projeto]]);
	
	$array_time = explode(":",$time);
	
	$horas = $array_time[0].':'.$array_time[1];
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, $projeto);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['cliente'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['coordenador'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, iconv('ISO-8859-1', 'UTF-8',$array_meses[intval($_POST["mes"])-1].'/'.$_POST["ano"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, iconv('ISO-8859-1', 'UTF-8',$horas));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['num_func'][$array_medicao['id_os'][$projeto]]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['custo'][$array_medicao['id_os'][$projeto]]));
	
	$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['valor_medido'][$projeto]));

	$objPHPExcel->getActiveSheet()->getStyle('I'.$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");

	$linha++;
}

$objWriter->save('php://output');

exit;
?>
