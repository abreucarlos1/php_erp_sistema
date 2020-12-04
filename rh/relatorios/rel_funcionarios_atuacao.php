<?php
/*
		Relatorio segmentos funcionarios
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/relatorios/rel_funcionarios.atuacao.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2006
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
		Versão 2 --> Inclusão dos campos tipo de contrato e Tarifa - 01/02/2018 - Eduardo
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados;

$sql_filtro = "";

for($i=1;$i<=7;$i++)//loop de itens de checkbox
{
	if($_POST["chk_".$i]!="")
	{
		$filtro[] = " funcionarios.nivel_atuacao = '".$_POST["chk_".$i]."' ";
		
		if($i==6)//Executante
		{
			$filtro[] = " funcionarios.nivel_atuacao = 'CA' "; //Coord. Aux
		}
			
	}
}

if(count($filtro)>0)
{
	$sql_filtro .= "AND (".implode("OR",$filtro).") ";
}

if(!empty($_POST["situacao"]))
{
	$sql_filtro .= "AND funcionarios.situacao = '".$_POST["situacao"]."' ";
}

if (!empty($_POST['tipo_contrato']))
{
    $sql_filtro .= "AND salarios. tipo_contrato = '".$_POST["tipo_contrato"]."' ";
}

$sql  = "SELECT *, rh_funcoes.descricao AS descricao, funcionarios.id_funcionario AS id_funcionario
FROM ".DATABASE.".rh_funcoes, ".DATABASE.".setores, ".DATABASE.".salarios, ".DATABASE.".funcionarios
LEFT JOIN ".DATABASE.".empresa_funcionarios ON (funcionarios.id_empfunc = empresa_funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0)
LEFT JOIN (SELECT id_local, descricao as localDesc FROM ".DATABASE.".local WHERE local.reg_del = 0) l ON l.id_local = id_local
WHERE funcionarios.id_setor = setores.id_setor
AND salarios.id_salario = funcionarios.id_salario
AND rh_funcoes.reg_del = 0
AND setores.reg_del = 0
AND salarios.reg_del = 0
AND funcionarios.reg_del = 0
AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= $sql_filtro;
$sql .= "ORDER BY funcionarios.situacao, funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

$array_func = $db->array_select;	

$total = 0;

$array_situacao = array();
$array_outros = array();
$array_setor = array();
$array_desc = array();
$array_emp = array();
$array_cnpj = array();
$array_tarifa = array();
$array_tipo_contrato = array();
$array_tarifa_tp = array();

foreach ($array_func as $regs)
{
    if ($regs['salario_clt'] > 0.00)
    {
        $tarifa = $regs['salario_clt'];
        $tipo = 'Mes';
    }
    else if ($regs['salario_hora'] > 0.00)
    {
        $tarifa = $regs['salario_hora'];
        $tipo = 'Hora';
    }
    else
    {
        $tarifa = $regs['salario_mensalista'];
        $tipo = 'Mes';
    }
    
    $array_situacao[$regs["situacao"]][$regs["id_funcionario"]] = $regs["funcionario"];
	$array_outros[$regs["situacao"]][$regs["id_funcionario"]] = array('admissao' => $regs["clt_admissao"], 'local' => $regs['localDesc']);
	$array_setor[$regs["situacao"]][$regs["id_funcionario"]] = $regs["setor"];
	$array_desc[$regs["situacao"]][$regs["id_funcionario"]] = $regs["descricao"];
	$array_emp[$regs["situacao"]][$regs["id_funcionario"]] = $regs["empresa_func"];
	$array_cnpj[$regs["situacao"]][$regs["id_funcionario"]] = $regs["empresa_cnpj"];
	$array_tarifa[$regs["situacao"]][$regs["id_funcionario"]] = number_format($tarifa, 2, ',', '.');
	$array_tarifa_tp[$regs["situacao"]][$regs["id_funcionario"]] = $tipo;
	$array_tipo_contrato[$regs["situacao"]][$regs["id_funcionario"]] = $regs['tipo_empresa'] == 0 ? 'CLT' : 'PJ';
	
	$total++;
}

if($_POST["tipo_arquivo"]==1)
{	

	class PDF extends FPDF
	{	
		//Page header
		function Header()
		{
			$this->Image(DIR_IMAGENS.'logo_pb.png',10,16,40);
			
			$this->Ln(1);
			$this->SetFont('Arial','',6);
			$this->SetFont('Arial','B',11);
			$this->Cell(271,8,$this->Titulo(),0,1,'C',0);
			
			$this->SetFont('Arial','B',6);
			$this->Cell(15,5,"SITUAÇÃO",1,0,'C',0);
			$this->Cell(40,5,"FUNCIONÁRIO",1,0,'C',0);
			$this->Cell(15,5,"ADMISSÃO",1,0,'C',0);
			$this->Cell(35,5,"SETOR",1,0,'C',0);
			$this->Cell(40,5,"FUNÇÃO",1,0,'C',0);
			$this->Cell(50,5,"EMPRESA",1,0,'C',0);
			$this->Cell(25,5,"CNPJ",1,0,'C',0);
			$this->Cell(35,5,"LOCAL TRABALHO",1,0,'C',0);
			$this->Cell(10,5,"CLT/PJ",1,0,'C',0);
			$this->Cell(15,5,"TARIFA",1,1,'C',0);
			
			$this->SetXY(10,35);
		}
		
		//Page footer
		function Footer()
		{
		
		}
	}	

	$pdf=new PDF('l','mm',A4);
	$pdf->SetAutoPageBreak(true,25);
	$pdf->SetMargins(10,20);
	$pdf->SetLineWidth(0.2);
	$pdf->SetDrawColor(0,0,0);
	
	$pdf->titulo="FUNCIONÁRIOS POR NIVEL DE ATUAÇÃO";
	
	$pdf->AliasNbPages();
	
	$pdf->SetFont('Arial','',6);
	
	foreach($array_situacao as $situacao=>$valores)
	{
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',6);
		$pdf->Cell(25,5,$situacao,0,1,'L',0);
		
		foreach($valores as $codfuncionarios=>$funcionarios)
		{
			$pdf->Cell(15,5,"",0,0,'C',0);
			$pdf->SetFont('Arial','',5);	
			$pdf->HCell(40,5,$funcionarios,0,0,'L',0);
			$pdf->SetFont('Arial','',6);
			$pdf->HCell(15,5,mysql_php($array_outros[$situacao][$codfuncionarios]['admissao']),0,0,'L',0);
			$pdf->SetFont('Arial','',5);
			$pdf->HCell(35,5,$array_setor[$situacao][$codfuncionarios],0,0,'L',0);
			$pdf->HCell(40,5,$array_desc[$situacao][$codfuncionarios],0,0,'L',0);
			$pdf->HCell(50,5,$array_emp[$situacao][$codfuncionarios],0,0,'L',0);
			$pdf->HCell(25,5,$array_cnpj[$situacao][$codfuncionarios],0,0,'L',0);
			$pdf->HCell(35,5,$array_outros[$situacao][$codfuncionarios]['local'],0,0,'L',0);
			$pdf->SetFont('Arial','',6);
			$pdf->HCell(15,5,$array_tipo_contrato[$situacao][$codfuncionarios],0,0,'L',0);
			$pdf->HCell(15,5,$array_tarifa[$situacao][$codfuncionarios].' ('.$array_tarifa_tp[$situacao][$codfuncionarios].')',0,1,'L',0);
		}
	}
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(25,5,"TOTAL",0,0,'L',0);
	$pdf->SetFont('Arial','',8);	
	$pdf->Cell(30,5,$total,0,1,'L',0);
	$pdf->Ln(5);
		
	$pdf->Output('RELATORIO_FUNCIONARIOS_ATUACAO_'.date('dmYhis').'.pdf', 'D');
}
else
{
	
	$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/atuacao_funcionario_modelo.xls");
	
	$locale = 'pt_br';
	
	$validlocale = PHPExcel_Settings::setlocale($locale);
	
	if (!$validlocale) 
	{
		echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
	}
	
	// Redirect output to a client's web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="atuacao_funcionario_'.date('d-m-Y').'.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$objPHPExcel->setActiveSheetIndex(0);
	
	//COLUNA A EXCELL
	$coluna = 0;
	
	$linha = 4;
	
	foreach($array_situacao as $situacao=>$valores)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, $situacao);
		
		foreach($valores as $codfuncionarios=>$funcionarios)
		{
			$linha++;

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, $funcionarios);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, $array_setor[$situacao][$codfuncionarios]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, mysql_php($array_outros[$situacao][$codfuncionarios]['admissao']));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, $array_desc[$situacao][$codfuncionarios]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $array_emp[$situacao][$codfuncionarios]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+12, $linha, $array_cnpj[$situacao][$codfuncionarios]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+14, $linha, $array_outros[$situacao][$codfuncionarios]['local']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+16, $linha, $array_tipo_contrato[$situacao][$codfuncionarios]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+18, $linha, $array_tarifa[$situacao][$codfuncionarios]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+20, $linha, $array_tarifa_tp[$situacao][$codfuncionarios]);
		}
		
		$linha++;			
	}
	
	$linha++;
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, "TOTAL: ".$total);

	$linha+=2;
	
	$objWriter->save('php://output');
	
	exit;
}
?>