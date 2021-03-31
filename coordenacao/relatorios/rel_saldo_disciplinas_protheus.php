<?php
/*
	 Relatório Saldo de Disciplinas
	 
	 Criado por Carlos Abreu  
	 
	 Versão 0 --> VERSÃO INICIAL : 10/06/2014
	 Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

var $permit_cust  = false; //PERMITE VISUALIZAÇÃO DE CUSTO

var $revisao_orc = '1'; //revisão de orçamento

var $revisao_rel = ''; //versao_documento de apontamentos

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
	$this->Cell(55,4,'',0,0,'R',0);
	$this->Cell(40,4,$this->Revisao(),0,1,'C',0);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(10,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(10,43);
	
	$this->SetDrawColor(0,0,0);
	$this->SetLineWidth(0.3);
	
	$this->Cell(195,5,"DISCIPLINAS - TAREFAS ",1,0,'C',0);
	$this->HCell(25,5,"HORAS PREV.",1,0,'C',0);	
	$this->HCell(25,5,"HORAS APONT.",1,0,'C',0);
	$this->Cell(25,5,"SALDO",1,0,'C',0);
	
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

$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);

if($_POST["escolhaos"]=='-1')
{
	$filtro .= '';
}
else
{
	$filtro .= " AND ordem_servico.id_os = '". $_POST["escolhaos"] . "' ";
}

//Não tem acesso ao combo coordenador
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

$pdf->departamento="PLANEJAMENTO";
$pdf->titulo="ACOMPANHAMENTO DE SALDO - DISCIPLINAS - PROTHEUS";
$pdf->setor="PLN";
$pdf->codigodoc="301"; //"00"; //"02";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();

$sql = "SELECT id_setor, abreviacao, setor FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	if($_POST["chk_".$regs["id_setor"]]==1)
	{
		$setor[] = "'".$regs["abreviacao"]."'";
		$setor_d[] = "'".tiraacentos($regs["setor"])."'";
		$setor_desc[$regs["abreviacao"]] = tiraacentos($regs["setor"]);
	}
}

$filtro_setor = implode(",",$setor);

$filtro_setor_d = implode(",",$setor_d);

$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
$sql .= "LEFT JOIN ".DATABASE.".apontamento_horas ON (ordem_servico.id_os = apontamento_horas.id_os AND apontamento_horas.reg_del = 0) ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.id_os_status NOT IN (3,8,9,12) ";
$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= $filtro1;
$sql .= $filtro;
$sql .= "GROUP BY ordem_servico.id_os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

$array_os = $db->array_select;

foreach($array_os as $cont_os_coord)
{	
	$array_qtd_prev = NULL;
	
	$array_qtd_apont = NULL;
	
	$array_tarefas = NULL;
	
	$array_saldo_tarefa = NULL;	
	
	$array_saldo_disc = NULL;
	
	$array_total_prev = NULL;
	
	$array_total_apont = NULL;
	
	$total_prev = 0;
	
	$total_apont = 0;
	
	$total_saldo = 0;  
 	
	$pdf->SetFont('Arial','',8);
	$pdf->SetDrawColor(0,0,0);
	$pdf->SetLineWidth(0.3);
	
	/*
	//PEGA A ULTIMA REVISÃO DA FASE 01 (ORÇAMENTO)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$cont_os_coord["os"])."' ";
	
	//ALTERAÇÃO DE ACORDO COM SOLICITAÇÃO MARCOS
	//CHAMADO #1837
	//22/04/2013
	$sql .= "AND AFE010.AFE_FASE = '02' ";

	$db->select($sql,'MSSQL', true);
	
	$regs_ult_rev = $db->array_select[0];	

	//PEGA O PROJETO
	$sql = "SELECT AF8_PROJET, AF8_REVISA FROM AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont_os_coord["os"])."' ";
	$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA ";
	$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";
	
	$db->select($sql,'MSSQL', true);
	
	$regs_os = $db->array_select[0];

	$pdf->revisao_rel = $regs_os["AF8_REVISA"];
	
	$pdf->revisao_orc = $regs_ult_rev["ULT_REVISA"];
	
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
	//OBTEM AS TAREFAS
	$sql = "SELECT * FROM AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AF9010.AF9_REVISA = '".$regs_os["AF8_REVISA"]."' ";
	
	if(count($setor_d)>0)
	{
		$sql .= "AND AF9010.AF9_GRPCOM IN (".$filtro_setor.") ";
	}
	
	$sql .= "ORDER BY AF9010.AF9_GRPCOM, AF9010.AF9_TAREFA ";
	
	$db->select($sql,'MSSQL', true);
	
	foreach($db->array_select as $cont_tarefas)
	{
		$array_tarefas[trim($cont_tarefas["AF9_GRPCOM"])][trim($cont_tarefas["AF9_TAREFA"])] = trim($cont_tarefas["AF9_DESCRI"]);
		
		//SOMA AS QUANTIDADES DE HORAS PELA TAREFA - PREVISTO
		
		$sql = 
		"SELECT
			SUM(AF3010.AF3_QUANT) AS HORAS_PREV
		FROM 
			AF3010 WITH(NOLOCK)
			JOIN (SELECT AF2_ORCAME, AF2_CODIGO, AF2_TAREFA, AF2_NIVEL, AF2_COMPOS FROM AF2010 WITH(NOLOCK) WHERE D_E_L_E_T_ = '') AF2010
				ON AF2_ORCAME = AF3_ORCAME
				AND AF2_TAREFA = AF3_TAREFA
			JOIN (SELECT AF9_PROJET, AF9_CODIGO, AF9_TAREFA, AF9_NIVEL, AF9_COMPOS FROM AF9010 WITH(NOLOCK) WHERE D_E_L_E_T_ = '' AND AF9_REVISA = '".$cont_tarefas["AF9_REVISA"]."') AF9010
			ON AF9_PROJET = AF3_ORCAME	
				AND AF2_CODIGO = AF9_CODIGO
				AND AF2_NIVEL = AF9_NIVEL
		WHERE 
			AF3010.D_E_L_E_T_ = ''
		AND AF3_ORCAME = '".$cont_tarefas["AF9_PROJET"]."'
		AND AF9_TAREFA = '".$cont_tarefas["AF9_TAREFA"]."'
		";
		
		$db->select($sql,'MSSQL', true);
		
		$cont_recurs = $db->array_select[0];
		
		$array_qtd_prev[trim($cont_tarefas["AF9_GRPCOM"])][trim($cont_tarefas["AF9_TAREFA"])] += $cont_recurs["HORAS_PREV"];
		
		//OBTEM AS HORAS APONTADAS NAS TAREFAS (APONTADAS)
		$sql = "SELECT SUM(AJK010.AJK_HQUANT) AS horas_apontadas FROM AJK010 WITH(NOLOCK) ";
		$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
		$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
		$sql .= "AND AJK010.AJK_PROJET = '".$cont_tarefas["AF9_PROJET"]."' ";
		$sql .= "AND AJK010.AJK_REVISA = '".$cont_tarefas["AF9_REVISA"]."' ";
		$sql .= "AND AJK010.AJK_TAREFA = '".$cont_tarefas["AF9_TAREFA"]."' ";
		
		$db->select($sql,'MSSQL', true);
		
		$cont_horas_rel = $db->array_select[0];
		
		$array_qtd_apont[trim($cont_tarefas["AF9_GRPCOM"])][trim($cont_tarefas["AF9_TAREFA"])] += $cont_horas_rel["horas_apontadas"];
			
		$array_saldo_tarefa[trim($cont_tarefas["AF9_GRPCOM"])][trim($cont_tarefas["AF9_TAREFA"])] += $cont_recurs["HORAS_PREV"]-$cont_horas_rel["horas_apontadas"];
	
		$array_saldo_disc[trim($cont_tarefas["AF9_GRPCOM"])] += $array_saldo_tarefa[trim($cont_tarefas["AF9_GRPCOM"])][trim($cont_tarefas["AF9_TAREFA"])];
	
		$array_total_prev[trim($cont_tarefas["AF9_GRPCOM"])] += $array_qtd_prev[trim($cont_tarefas["AF9_GRPCOM"])][trim($cont_tarefas["AF9_TAREFA"])];
	
		$array_total_apont[trim($cont_tarefas["AF9_GRPCOM"])] += $array_qtd_apont[trim($cont_tarefas["AF9_GRPCOM"])][trim($cont_tarefas["AF9_TAREFA"])];
	
	}

	*/
	
	foreach($array_tarefas as $chave=>$valor)
	{
		$pdf->SetFont('Arial','B',8);
		
		$pdf->HCell(225,3,$setor_desc[$chave],0,1,'L',0);
		
		$pdf->SetFont('Arial','',8);
		
		foreach($valor as $tarefa=>$descricao)
		{
			$pdf->HCell(45,3,"",0,0,'L',0);
			
			$pdf->HCell(150,3,$tarefa." - ".$descricao,0,0,'L',0);
			
			$pdf->HCell(25,3,number_format($array_qtd_prev[$chave][$tarefa],2,",",""),0,0,'R',0);
			
			$pdf->HCell(25,3,number_format($array_qtd_apont[$chave][$tarefa],2,",",""),0,0,'R',0);
			
			$pdf->HCell(25,3,number_format($array_saldo_tarefa[$chave][$tarefa],2,",",""),0,1,'R',0);			
			
			$total_prev += $array_qtd_prev[$chave][$tarefa];
			
			$total_apont += $array_qtd_apont[$chave][$tarefa];
			
			$total_saldo += $array_saldo_tarefa[$chave][$tarefa];  
		
		}
		
		$pdf->ln(5);
		
		$pdf->SetFont('Arial','B',8);
		
		$pdf->HCell(195,3,"SUB-TOTAL: ",0,0,'R',0);
		
		$pdf->SetFont('Arial','',8);
		
		$pdf->HCell(25,3,number_format($array_total_prev[$chave],2,",",""),0,0,'R',0);
		
		$pdf->HCell(25,3,number_format($array_total_apont[$chave],2,",",""),0,0,'R',0);
		
		$pdf->HCell(25,3,number_format($array_saldo_disc[$chave],2,",",""),0,1,'R',0);
		
		$pdf->ln(5);
	}
	
	$pdf->SetFont('Arial','B',8);
	
	$pdf->HCell(195,3,"TOTAL: ",0,0,'R',0);
	
	$pdf->SetFont('Arial','',8);
	
	$pdf->HCell(25,3,number_format($total_prev,2,",",""),0,0,'R',0);
	
	$pdf->HCell(25,3,number_format($total_apont,2,",",""),0,0,'R',0);
	
	$pdf->HCell(25,3,number_format($total_saldo,2,",",""),0,1,'R',0);
	
	$pdf->ln(5);
	
	$pdf->ln(5);

}

$pdf->Output('SALDO_DISCIPLINA_PROTHEUS_'.date('dmYhis').'.pdf', 'D');

?> 