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
	
	$this->Cell(140,5,"DISCIPLINAS - TAREFAS - RECURSOS ",1,0,'C',0);
	
	$this->Cell(20,5,"AVANÇO",1,0,'C',0);
	$this->HCell(25,5,"HORAS PREV.",1,0,'C',0);	
	$this->HCell(25,5,"HORAS APONT.",1,0,'C',0);
	$this->Cell(25,5,"SALDO",1,0,'C',0);
	$this->Cell(20,5,"QUANT.",1,0,'C',0);
	$this->Cell(15,5,"UN.",1,0,'C',0);	
	
	$this->SetFont('Arial','',8);
	$this->SetDrawColor(0,0,0);
	$this->SetLineWidth(0.3);
	$this->Line(10,$this->GetY(),280,$this->GetY());
	
	$this->Ln(5);	
}

function Footer()
{

}
}

$db = new banco_dados();

$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);


if($_POST["intervalo"]=='1')
{
	$filtro1 = "AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["data_ini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
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

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="ACOMPANHAMENTO DE SALDO - TAREFAS - PROTHEUS";
$pdf->setor="COR";
$pdf->codigodoc="202"; //"00"; //"02";
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
$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= $filtro1;
$sql .= $filtro;
$sql .= "GROUP BY ordem_servico.id_os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

$array_os = $db->array_select;

foreach($array_os as $cont_os_coord)
{	
	//Zera total Horas do Projeto
	$total_horas_exec = 0;
	$total_horas_orc = 0;
	$total_qtd_avc = 0;
	$total_qtd_exec = 0;
	$total_saldo = 0;
	
	$array_edt = NULL;
	
	$array_edtpai = NULL;
	
	$array_aloc = NULL;
	
	$pdf->SetFont('Arial','',8);
	$pdf->SetDrawColor(0,0,0);
	$pdf->SetLineWidth(0.3);
	
	/*
	//PEGA A ULTIMA REVIS�O DA FASE 01 (OR�AMENTO)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH (NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$cont_os_coord["os"])."' ";
	$sql .= "AND (AFE010.AFE_FASE = '03' ";
	$sql .= "OR AFE010.AFE_FASE = '07') ";
	
	$db->select($sql, 'MSSQL',true);
	
	$regs_ult_rev = $db->array_select[0];
	
	//PEGA A REVIS�O DO PROJETO
	$sql = "SELECT AF8_PROJET, AF8_REVISA FROM AF8010 WITH (NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont_os_coord["os"])."' ";
	$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA ";
	$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";
	
	$db->select($sql, 'MSSQL',true);
	
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
	$pdf->Cell(225,3,$cont_os_coord["os_status"],0,1,'L',0);
	
	$pdf->SetFont('Arial','',8);	
	
	/*
	//Percorre a tabela de EDT PAI (REALIZADO)
	$sql = "SELECT AFC_PROJET, AFC_REVISA, AFC_EDT, AFC_DESCRI FROM AFC010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
	$sql .= "WHERE AFC010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFC010.AFC_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AFC010.AFC_REVISA = '".$regs_os["AF8_REVISA"]."' ";
	$sql .= "AND AFC010.AFC_NIVEL > 001 ";	
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = AFC010.AFC_PROJET ";
	$sql .= "AND AF9010.AF9_REVISA = AFC010.AFC_REVISA ";
	$sql .= "AND AF9010.AF9_EDTPAI = AFC010.AFC_EDT ";
	
	if(count($setor_d)>0)
	{
		$sql .= "AND AF9010.AF9_GRPCOM IN (".$filtro_setor.") ";
	}
	
	$sql .= "GROUP BY AFC_PROJET, AFC_REVISA, AFC_EDT, AFC_DESCRI ";	
	$sql .= "ORDER BY AFC010.AFC_EDT, AFC010.AFC_DESCRI ";
	
	$db->select($sql,'MSSQL',true);
	
	$array_tar = $db->array_select;
	
	foreach($array_tar as $cont_pai)
	{
		$array_edt[trim($cont_pai["AFC_EDT"])] = trim($cont_pai["AFC_DESCRI"]);		
		
		//Percorre a tabela de EDT FILHAS
		$sql = "SELECT AFC_EDT, AFC_DESCRI FROM AFC010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
		$sql .= "WHERE AFC010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFC010.AFC_PROJET = '".$cont_pai["AFC_PROJET"]."' ";
		$sql .= "AND AFC010.AFC_REVISA = '".$cont_pai["AFC_REVISA"]."' ";
		$sql .= "AND AFC010.AFC_EDTPAI = '".$cont_pai["AFC_EDT"]."' ";		
		$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = AFC010.AFC_PROJET ";
		$sql .= "AND AF9010.AF9_REVISA = AFC010.AFC_REVISA ";
		$sql .= "AND AF9010.AF9_EDTPAI = AFC010.AFC_EDT ";
		
		if(count($setor_d)>0)
		{
			$sql .= "AND AF9010.AF9_GRPCOM IN (".$filtro_setor.") ";
		}
		
		$sql .= "GROUP BY AFC_EDT, AFC_DESCRI ";
		$sql .= "ORDER BY AFC010.AFC_EDT, AFC010.AFC_DESCRI ";
		
		$db->select($sql,'MSSQL',true);
		
		foreach($db->array_select as $cont_filha)
		{
			$array_edt[trim($cont_filha["AFC_EDT"])] = trim($cont_filha["AFC_DESCRI"]);
		}			
	}
	
	//Monta as atividades (REALIZADO)
	$sql = "SELECT AF9_TAREFA, AF9_DESCRI, AF9_EDTPAI FROM AF9010 WITH (NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AF9010.AF9_REVISA = '".$regs_os["AF8_REVISA"]."' ";
	
	if(count($setor_d)>0)
	{
		$sql .= "AND AF9010.AF9_GRPCOM IN (".$filtro_setor.") ";
	}
	
	$sql .= "GROUP BY AF9_TAREFA, AF9_DESCRI, AF9_EDTPAI ";	
	$sql .= "ORDER BY AF9010.AF9_TAREFA ";
	
	$db->select($sql,'MSSQL',true);
	
	foreach($db->array_select as $cont_atv)
	{
		$array_tarefa[trim($cont_atv["AF9_TAREFA"])] = trim($cont_atv["AF9_DESCRI"]);
		
		$array_edtpai[trim($cont_atv["AF9_TAREFA"])] = trim($cont_atv["AF9_EDTPAI"]);		
	}
	
	//Ordena pela chave
	ksort($array_edt);
	
	//PERCORRE O ARRAY DE EDTS
	foreach ($array_edt as $codigo=>$edt)
	{
		//Imprime EDT
		$pdf->SetFont('Arial','B',8);
		
		//pega o tamanho do codigo para fazer a identa��o
		$espaco = strlen(str_replace('.','',trim($codigo)));
		
		if(($espaco/2)>=2)
		{
			$pdf->Cell($espaco,3,'',0,0,'L',0);
		}
		
		$pdf->HCell(225,3,$codigo." - ".$edt,0,1,'L',0);
		
		//Zera sub-total por EDT
		$sub_total_horas_exec = 0;
		$sub_total_horas_orc = 0;
		$sub_total_saldo = 0;
				
		//percorre o array de tarefas e pega a edt pai
		foreach($array_edtpai as $tarefa=>$edtpai)
		{			
			if($edtpai==$codigo)
			{				
				//OBTEM OS HORAS (ULTIMA REVIS�O OR�AMENTO)
				//OBTEM A SOMA DAS HORAS DOS RECURSOS ALOCADOS NA TAREFA (ULTIMA REVIS�O)				
				$sql = "SELECT SUM(AFA_QUANT) AS AF9_HESF FROM AFA010 WITH (NOLOCK), AE8010 WITH (NOLOCK) ";
				$sql .= "WHERE AFA010.D_E_L_E_T_ = '' ";
				$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFA010.AFA_PROJET = '".$regs_os["AF8_PROJET"]."' ";
				$sql .= "AND AFA010.AFA_REVISA = '".$regs_os["AF8_REVISA"]."' ";
				$sql .= "AND AFA010.AFA_TAREFA = '".$tarefa."' ";
				$sql .= "AND AFA010.AFA_RECURS = AE8010.AE8_RECURS ";
				
				$db->select($sql,'MSSQL',true);
				
				$cont_custo_prev = $db->array_select[0];
				
				//OBTEM OS HORAS (ULTIMA REVIS�O)
				$sql = "SELECT * FROM AF9010 ";
				$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF9010.AF9_PROJET = '".$regs_os["AF8_PROJET"]."' ";
				$sql .= "AND AF9010.AF9_REVISA = '".$regs_os["AF8_REVISA"]."' ";
				$sql .= "AND AF9010.AF9_TAREFA = '".$tarefa."' ";
				
				if(count($setor)>0)
				{
					$sql .= "AND AF9010.AF9_GRPCOM IN (".$filtro_setor.") ";
				}
				
				$db->select($sql,'MSSQL',true);
				
				$cont_custo_rel = $db->array_select[0];
				
				//OBTEM O AVAN�O F�SICO DA TAREFA (REALIZADO)
				$sql = "SELECT AFF010.AFF_QUANT FROM AFF010 WITH (NOLOCK) ";
				$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFF010.AFF_PROJET = '".$cont_custo_rel["AF9_PROJET"]."' ";
				$sql .= "AND AFF010.AFF_REVISA = '".$cont_custo_rel["AF9_REVISA"]."' ";
				$sql .= "AND AFF010.AFF_TAREFA = '".$cont_custo_rel["AF9_TAREFA"]."' ";
				$sql .= "ORDER BY AFF_DATA DESC ";
				
				$db->select($sql, 'MSSQL',true);
				
				$cont_avn = $db->array_select[0];
				
				//OBTEM AS HORAS APONTADAS NAS TAREFAS (APONTADAS)
				$sql = "SELECT SUM(AJK010.AJK_HQUANT) AS horas_apontadas FROM AJK010 WITH (NOLOCK) ";
				$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
				$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
				$sql .= "AND AJK010.AJK_PROJET = '".$cont_custo_rel["AF9_PROJET"]."' ";
				$sql .= "AND AJK010.AJK_REVISA = '".$cont_custo_rel["AF9_REVISA"]."' ";
				$sql .= "AND AJK010.AJK_TAREFA = '".$cont_custo_rel["AF9_TAREFA"]."' ";
				
				$db->select($sql, 'MSSQL',true);
				
				$cont_horas_rel = $db->array_select[0];			
								
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(5+$espaco,3,'',0,0,'L',0);
				$pdf->HCell(135-$espaco,3,trim($array_tarefa[$tarefa]),0,0,'L',0);
				
				//% avan�o				
				$percentual_avanco = ($cont_avn["AFF_QUANT"]/$cont_custo_rel["AF9_QUANT"])*100;
				
				$pdf->HCell(20,3,number_format($percentual_avanco,2,",","")." %",0,0,'R',0);
				
				//HORAS ESFOR�O
				$pdf->HCell(25,3,number_format($cont_custo_prev["AF9_HESF"],2,",",""),0,0,'R',0);
				
					
				//Horas executadas
				$pdf->HCell(25,3,number_format($cont_horas_rel["horas_apontadas"],2,",",""),0,0,'R',0);

				//muda cor caso negativo
				if($cont_custo_prev["AF9_HESF"]-$cont_horas_rel["horas_apontadas"]<0)
				{
					$pdf->SetTextColor(255,0,0);	
				}

				//Saldo Horas
				$pdf->HCell(25,3,number_format($cont_custo_prev["AF9_HESF"]-$cont_horas_rel["horas_apontadas"],2,",",""),0,0,'R',0);
				
				//Quantidades
				$pdf->HCell(20,3,number_format($cont_custo_rel["AF9_QUANT"],2,",",""),0,0,'R',0);
				
				//FORMATOS
				$pdf->HCell(15,3,$cont_custo_rel["AF9_UM"],0,1,'R',0);
				
				$pdf->SetTextColor(0,0,0);
				
				$pdf->SetFont('Arial','',8);
				
				//OBTEM OS RECURSOS ALOCADOS NA TAREFA (ULTIMA REVIS�O)				
				$sql = "SELECT * FROM AFA010 WITH (NOLOCK), AE8010 WITH (NOLOCK) ";
				$sql .= "WHERE AFA010.D_E_L_E_T_ = '' ";
				$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFA010.AFA_PROJET = '".$cont_custo_rel["AF9_PROJET"]."' ";
				$sql .= "AND AFA010.AFA_REVISA = '".$cont_custo_rel["AF9_REVISA"]."' ";
				$sql .= "AND AFA010.AFA_TAREFA = '".$cont_custo_rel["AF9_TAREFA"]."' ";
				$sql .= "AND AFA010.AFA_RECURS = AE8010.AE8_RECURS ";
				
				$db->select($sql,'MSSQL',true);
				
				$array_rec = $db->array_select;
				
				foreach($array_rec as $cont_recurs)
				{
					$pdf->Cell(10+$espaco,3,'',0,0,'L',0);
					$pdf->HCell(130-$espaco,3,trim($cont_recurs["AFA_RECURS"])." - ".trim($cont_recurs["AE8_DESCRI"]),0,0,'L',0);
					
					$pdf->HCell(20,3,"",0,0,'R',0);
					
					//HORAS ALOCADAS
					$pdf->HCell(25,3,number_format($cont_recurs["AFA_QUANT"],2,",",""),0,0,'R',0);
					
					//OBTEM AS HORAS APONTADAS NAS TAREFAS (APONTADAS)
					$sql = "SELECT SUM(AJK010.AJK_HQUANT) AS horas_apontadas FROM AJK010 WITH (NOLOCK) ";
					$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
					$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
					$sql .= "AND AJK010.AJK_PROJET = '".$cont_recurs["AFA_PROJET"]."' ";
					$sql .= "AND AJK010.AJK_REVISA = '".$cont_recurs["AFA_REVISA"]."' ";
					$sql .= "AND AJK010.AJK_TAREFA = '".$cont_recurs["AFA_TAREFA"]."' ";
					$sql .= "AND AJK010.AJK_RECURS = '".$cont_recurs["AFA_RECURS"]."' ";
					
					$db->select($sql,'MSSQL',true);
					
					$cont_horas_aloc = $db->array_select[0];
					
					//Horas executadas
					$pdf->HCell(25,3,number_format($cont_horas_aloc["horas_apontadas"],2,",",""),0,0,'R',0);
					
					if($cont_recurs["AFA_QUANT"]-$cont_horas_aloc["horas_apontadas"]<0)
					{
						$pdf->SetTextColor(255,0,0);	
					}
					
					//Saldo Horas
					$pdf->HCell(25,3,number_format($cont_recurs["AFA_QUANT"]-$cont_horas_aloc["horas_apontadas"],2,",",""),0,1,'R',0);
	
					$pdf->SetTextColor(0,0,0);
					
					$pdf->Ln(2);
				}
				
				//SUB_TOTAL
				$sub_total_horas_exec += $cont_horas_rel["horas_apontadas"];
				//$sub_total_horas_orc += $cont_custo_rel["AF9_HESF"];
				$sub_total_horas_orc += $cont_custo_prev["AF9_HESF"];
				//$sub_total_saldo += ($cont_custo_rel["AF9_HESF"]-$cont_horas_rel["horas_apontadas"]);
				$sub_total_saldo += ($cont_custo_prev["AF9_HESF"]-$cont_horas_rel["horas_apontadas"]);
			}
		}
		
		//Totaliza horas da EDT para o projeto
		$total_horas_exec += $sub_total_horas_exec;
		$total_horas_orc += $sub_total_horas_orc;
		$total_saldo += $sub_total_saldo;					

	}
	
	
	//OBTEM O AVAN�O F�SICO DO PROJETO
	$sql = "SELECT AFQ010.AFQ_QUANT FROM AFQ010 WITH (NOLOCK) ";
	$sql .= "WHERE AFQ010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFQ010.AFQ_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AFQ010.AFQ_REVISA = '".$regs_os["AF8_REVISA"]."' ";
	$sql .= "AND AFQ010.AFQ_EDT = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "ORDER BY AFQ_DATA DESC ";
	
	$db->select($sql, 'MSSQL',true);
	
	$regs_avc_proj = $db->array_select[0];

	*/
	
	//Imprime os totais por projeto
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(140,5,"TOTAIS: ",0,0,'L',0);
	$pdf->HCell(20,3,number_format(($regs_avc_proj["AFQ_QUANT"]/$total_horas_orc)*100,2,",","") . " %",0,0,'R',0);
	$pdf->HCell(25,3,number_format($total_horas_orc,2,",",""),0,0,'R',0);
	$pdf->HCell(25,3,number_format($total_horas_exec,2,",",""),0,0,'R',0);
	$pdf->HCell(25,3,number_format($total_saldo,2,",",""),0,1,'R',0);
	
	$pdf->ln(5);

}

$pdf->Output('SALDO_TAREFAS_PROTHEUS_'.date('dmYhis').'.pdf', 'D');
?>