<?php
/*
	 Relatório Acumulado Funções
	 
	 Criado por Carlos Abreu  
	 
	 Versão 0 --> VERSÃO INICIAL : 10/06/2017
	 Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

var $revisao_orc = '1'; //revisão de orçamento

var $revisao_rel = ''; //versao_documento de apontamentos

//Page header
function Header()
{    
	$this->Image(DIR_IMAGENS.'logo_pb.png',11,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(254,19.5,280,19.5);
	$this->Cell(240,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(254,23.5,280,23.5);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'FOLHA:',0,0,'L',0);
	$this->Cell(13,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(254,27.5,280,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(270,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(175,4,'',0,0,'R',0);
	$this->Cell(55,4,'PERÍODO ',0,0,'R',0);
	$this->Cell(40,4,$this->Revisao(),0,1,'C',0);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(10,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(10,43);	
	$this->SetDrawColor(0,0,0);
	$this->SetLineWidth(0.3);
	$this->Cell(120,5,"ORÇAMENTO (PREVISTO) Rev.:".$this->revisao_orc,1,0,'C',0);
	$this->Cell(50,5,"REALIZADO Rev.:".$this->revisao_rel,1,1,'C',0);	
	$this->Cell(100,5,"FUNÇÃO",1,0,'C',0);
	$this->Cell(20,5,"HORAS",1,0,'C',0);
	$this->Cell(25,5,"AVANÇO",1,0,'C',0);
	$this->Cell(25,5,"HORAS",1,1,'C',0);
	$this->SetFont('Arial','',8);
	$this->SetDrawColor(0,0,0);
	$this->SetLineWidth(0.3);
	$this->Line(10,$this->GetY(),280,$this->GetY());	
	$this->Ln(5);	
}

//Page footer
function Footer()
{
	
}
}

$db = new banco_dados();


$pdf = new PDF('L','mm',A4);

$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);

if($_POST["intervalo"]=='1')
{
	$filtro1 = "AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
	$pdf->versao_documento="DE: ".$_POST["dataini"] . " A " . $_POST["datafim"];
}
else
{
	$pdf->versao_documento="TOTAL";
	$filtro_desp = "";
	$filtro1 = "";
}

if($_POST["escolhaos"]=='-1')
{
	$filtro .= '';
}
else
{
	$filtro .= " AND ordem_servico.id_os = '". $_POST["escolhaos"] . "' ";
}

//N�o tem acesso ao combo coordenador
if($_POST["escolhacoord"]=='')
{
	$filtro .= " AND (ordem_servico.id_cod_coord = '".$_SESSION["id_funcionario"]."' OR ordem_servico.id_coord_aux = '".$_SESSION["id_funcionario"]."') ";
}
else
{
	//Todos os coordenadores
	if($_POST["escolhacoord"]!='-1')
	{
		$filtro .= " AND (ordem_servico.id_cod_coord = '".$_POST["escolhacoord"]."' OR ordem_servico.id_coord_aux = '".$_POST["escolhacoord"]."') ";
	}
}

//Seta o cabe�alho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="ACOMPANHAMENTO DE OS - FUNÇÃO - PROTHEUS";
$pdf->setor="COR";
$pdf->codigodoc="201"; //"00"; //"02";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();

//Percorre as OSs
$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
$sql .= "LEFT JOIN ".DATABASE.".apontamento_horas ON (ordem_servico.id_os = apontamento_horas.id_os ".$filtro1." AND apontamento_horas.reg_del = 0) ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= $filtro;
$sql .= "GROUP BY ordem_servico.id_os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

$array_os = $db->array_select;

foreach($array_os as $cont_os_coord)
{
	//Zera sub-total por EDT
	$sub_total_horas_exec = 0;
	$sub_total_horas_orc = 0;
	
	$pdf->SetFont('Arial','',8);
	$pdf->SetDrawColor(0,0,0);
	$pdf->SetLineWidth(0.3);
	
	/*
	//PEGA O PROJETO NA REVIS�O ATUAL
	$sql = "SELECT AF8_PROJET, AF8_REVISA FROM AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont_os_coord["os"])."' ";
	$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA ";
	$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";
	
	$db->select($sql, 'MSSQL', true);
	
	$regs_os = $db->array_select[0];	
	
	//PEGA A ULTIMA REVIS�O DA FASE 01 (OR�AMENTO)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".trim($regs_os["AF8_PROJET"])."' ";
	$sql .= "AND AFE010.AFE_FASE = '01' ";
	
	$db->select($sql, 'MSSQL', true);
	
	$regs_ult_rev = $db->array_select[0];
	
	$pdf->revisao_orc = $regs_ult_rev["ULT_REVISA"]; //imprime a ultima revis�o orcamento
	$pdf->revisao_rel = $regs_os["AF8_REVISA"]; //imprime a ultima revis�o
	*/	
	
	$pdf->AddPage();	

	$sql = "SELECT funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$cont_os_coord["id_cod_coord"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$coordenador = $db->array_select[0];
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(225,3,sprintf("%010d",$cont_os_coord["os"]) . " - " . substr($cont_os_coord["descricao"],0,100),0,1,'L',0);
	
	$pdf->Cell(225,3,"CLIENTE: ". $cont_os_coord["abreviacao"] ,0,1,'L',0);
	$pdf->Cell(225,3,"COORD.: ".$coordenador["funcionario"] ,0,1,'L',0);
	$pdf->Cell(225,3,$cont_os_coord["os_status"] ,0,1,'L',0);
	
	$pdf->SetFont('Arial','',8);	
	
	/*
	//Percorre a tabela de RECURSOS DO PROJETO (ORCAMENTO)
	$sql = "SELECT AE8_FUNCAO, AN1_DESCRI FROM AFA010 WITH(NOLOCK), AE8010 WITH(NOLOCK), AN1010 WITH(NOLOCK) ";
	$sql .= "WHERE AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AN1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.AFA_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AFA010.AFA_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
	$sql .= "AND AE8010.AE8_RECURS = AFA010.AFA_RECURS ";
	$sql .= "AND AE8010.AE8_FUNCAO = AN1010.AN1_CODIGO ";
	$sql .= "GROUP BY AE8_FUNCAO, AN1_DESCRI ";
	
	$db->select($sql, 'MSSQL', true);
	
	foreach($db->array_select as $cont)
	{
		$array_funcao[trim($cont["AE8_FUNCAO"])] = trim($cont["AE8_FUNCAO"])."-".trim($cont["AN1_DESCRI"]);
	}
	
	//Percorre a tabela de RECURSOS DO PROJETO (REALIZADO)
	$sql = "SELECT AE8_FUNCAO, AN1_DESCRI FROM AFU010 WITH(NOLOCK), AE8010 WITH(NOLOCK), AN1010 WITH(NOLOCK) ";
	$sql .= "WHERE AFU010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AN1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFU010.AFU_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AFU010.AFU_REVISA = '".$regs_os["AF8_REVISA"]."' ";
	$sql .= "AND AE8010.AE8_RECURS = AFU010.AFU_RECURS ";
	$sql .= "AND AE8010.AE8_FUNCAO = AN1010.AN1_CODIGO ";
	$sql .= "GROUP BY AE8_FUNCAO, AN1_DESCRI ";
	
	$db->select($sql, 'MSSQL', true);
	
	foreach($db->array_select as $cont)
	{
		$array_funcao[trim($cont["AE8_FUNCAO"])] = trim($cont["AE8_FUNCAO"])."-".trim($cont["AN1_DESCRI"]);
	}
	
	//PERCORRE O ARRAY DE FUN��ES
	foreach ($array_funcao as $codigo=>$funcao)
	{
		$pdf->Cell(10,3,'',0,0,'C',0);
		$pdf->HCell(90,5,$funcao,0,0,'L',0);
		
		//ORCAMENTO
		
		//Trazendo os dados de recursos DO ORCAMENTO
		$sql = "SELECT SUM(AF3_QUANT) AS QUANT FROM	AF3010 WITH(NOLOCK), AE8010 WITH(NOLOCK) "; 
		$sql .= "WHERE AF3010.D_E_L_E_T_ = '' ";
		$sql .= "AND AE8010.D_E_L_E_T_ = '' "; 
		$sql .= "AND AF3010.AF3_ORCAME = '".$regs_os["AF8_PROJET"]."' ";
		$sql .= "AND AE8010.AE8_FUNCAO = '".$codigo."' ";
		$sql .= "AND AE8010.AE8_RECURS = AF3010.AF3_RECURS "; 
		$sql .= "GROUP BY AE8_FUNCAO ";
		
		$db->select($sql, 'MSSQL', true);
		
		$cont_rec = $db->array_select[0];
		
		//REALIZADO		
		//OBTEM AS HORAS APONTADAS DOS RECURSOS (APROVADAS)
		$sql = "SELECT SUM(AFU_HQUANT) AS horas_apontadas FROM AFU010 WITH(NOLOCK), AE8010 WITH(NOLOCK) ";
		$sql .= "WHERE AFU010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFU010.AFU_CTRRVS = '1' ";
		$sql .= "AND AE8010.D_E_L_E_T_ = '' "; 
		$sql .= "AND AFU_PROJET = '".$regs_os["AF8_PROJET"]."' "; 
		$sql .= "AND AFU_REVISA = '".$regs_os["AF8_REVISA"]."' ";
		$sql .= "AND AE8010.AE8_FUNCAO = '".$codigo."' ";
		$sql .= "AND AE8_RECURS = AFU_RECURS ";
		$sql .= "GROUP BY AE8_FUNCAO ";
		
		$db->select($sql, 'MSSQL', true);
		
		$regs_horas = $db->array_select[0];
		
		//OBTEM O AVAN�O F�SICO DA TAREFA/FUNCAO		
		$sql = "SELECT SUM(AFF010.AFF_QUANT) AS AFFQUANT, SUM(AF9010.AF9_QUANT) AS AF9QUANT FROM AFF010 WITH(NOLOCK), AFA010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AE8010 WITH(NOLOCK) ";
		$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
		$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AE8010.AE8_FUNCAO = '".$codigo."' ";
		$sql .= "AND AFF010.AFF_PROJET = '".$regs_os["AF8_PROJET"]."' ";
		$sql .= "AND AFF010.AFF_REVISA = '".$regs_os["AF8_REVISA"]."' ";
		$sql .= "AND AFA010.AFA_RECURS = AE8010.AE8_RECURS ";
		$sql .= "AND AFA010.AFA_TAREFA = AFF010.AFF_TAREFA ";
		$sql .= "AND AFA010.AFA_PROJET = AFF010.AFF_PROJET ";
		$sql .= "AND AFA010.AFA_REVISA = AFF010.AFF_REVISA ";
		$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
		$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
		$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
		$sql .= "GROUP BY AE8010.AE8_FUNCAO ";		
		
		$db->select($sql, 'MSSQL', true);
		
		$regs_tarefa = $db->array_select[0];
		
		$percentual_avanco = ($regs_horas["horas_apontadas"]/$regs_tarefa["AF9QUANT"])*100;
				
		//SUB-TOTALIZA AS HORAS
		$sub_total_horas_orc += $cont_rec["QUANT"];
		
		$sub_total_horas_exec += $regs_horas["horas_apontadas"];
		
		$pdf->Cell(20,5,number_format($cont_rec["QUANT"],2,",",""),0,0,'R',0);
		
		$pdf->Cell(25,5,number_format($percentual_avanco,2,",","")." %",0,0,'R',0);
		
		$pdf->Cell(25,5,number_format($regs_horas["horas_apontadas"],2,",",""),0,1,'R',0);
	}
	
	//Totaliza horas da EDT para o projeto
	$total_horas_exec += $sub_total_horas_exec;
	$total_horas_orc += $sub_total_horas_orc;
	
	$pdf->Ln(5);
	
	//OBTEM O AVAN�O F�SICO DO PROJETO
	$sql = "SELECT AFQ010.AFQ_QUANT FROM AFQ010 WITH(NOLOCK) ";
	$sql .= "WHERE AFQ010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFQ010.AFQ_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AFQ010.AFQ_REVISA = '".$regs_os["AF8_REVISA"]."' ";
	$sql .= "AND AFQ010.AFQ_EDT = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "ORDER BY AFQ_DATA DESC ";
	
	$db->select($sql, 'MSSQL', true);
	
	$regs_avc_proj = $db->array_select[0];
	*/
	
	//Imprime os sub-totais
	$pdf->Cell(10,3,"",0,0,'L',0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(90,3,"SUBTOTAL: ",0,0,'L',0);

	$pdf->Cell(20,3,number_format($sub_total_horas_orc,2,",",""),0,0,'R',0);
	
	//$pdf->Cell(25,3,number_format(($regs_avc_proj["AFQ_QUANT"]/$total_horas_orc)*100,2,",","") . " %",0,0,'R',0);
	
	$pdf->Cell(25,3,number_format($sub_total_horas_exec,2,",",""),0,1,'R',0);
	
	$pdf->Ln(5);
}

$pdf->Output('ACUMULADO_OS_FUNCAO_'.date('dmYhis').'.pdf', 'D');

?> 